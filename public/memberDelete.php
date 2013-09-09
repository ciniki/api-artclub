<?php
//
// Description
// ===========
//
// Arguments
// ---------
// 
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_artclub_memberDelete(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'member_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Member'),
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];

    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'artclub', 'private', 'checkAccess');
    $rc = ciniki_artclub_checkAccess($ciniki, $args['business_id'], 'ciniki.artclub.memberDelete', 0); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	//  
	// Turn off autocommit
	//  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDelete');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'refClear');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'removeImage');
	$rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.artclub');
	if( $rc['stat'] != 'ok' ) { 
		return $rc;
	}   

	//
	// Get the existing member images to delete
	//
	$strsql = "SELECT id, uuid, image_id FROM ciniki_artclub_member_images "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND member_id = '" . ciniki_core_dbQuote($ciniki, $args['member_id']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artclub', 'item');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( isset($rc['rows']) && count($rc['rows']) > 0 ) {
		$images = $rc['rows'];
		foreach($images as $image) {
			//
			// Delete the reference to the image, and remove the image if no more references
			//
			$rc = ciniki_images_refClear($ciniki, $args['business_id'], array(
				'object'=>'ciniki.artclub.member_image',
				'object_id'=>$image['id']));
			if( $rc['stat'] == 'fail' ) {
				ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artclub');
				return $rc;
			}

			//
			// Remove the image from the database
			//
			$strsql = "DELETE FROM ciniki_artclub_member_images "
				. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
				. "AND id = '" . ciniki_core_dbQuote($ciniki, $image['id']) . "' ";
			$rc = ciniki_core_dbDelete($ciniki, $strsql, 'ciniki.artclub');
			if( $rc['stat'] != 'ok' ) { 
				ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artclub');
				return $rc;
			}

			ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.artclub', 'ciniki_artclub_history', 
				$args['business_id'], 3, 'ciniki_artclub_member_images', $image['id'], '*', '');
		}
	}

	//
	// Get the existing member information to delete
	//
	$strsql = "SELECT id, uuid, primary_image_id FROM ciniki_artclub_members "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND id = '" . ciniki_core_dbQuote($ciniki, $args['member_id']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artclub', 'member');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['member']) ) {
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artclub');
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'936', 'msg'=>'Member does not exist'));
	}
	$member = $rc['member'];

	//
	// Remove the main image for the member
	//
	$rc = ciniki_images_refClear($ciniki, $args['business_id'], array(
		'object'=>'ciniki.artclub.member',
		'object_id'=>$args['member_id']));
	if( $rc['stat'] == 'fail' ) {
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artclub');
		return $rc;
	}

	//
	// Remove the image from the database
	//
	$strsql = "DELETE FROM ciniki_artclub_member_images "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND id = '" . ciniki_core_dbQuote($ciniki, $member['primary_image_id']) . "' ";
	$rc = ciniki_core_dbDelete($ciniki, $strsql, 'ciniki.artclub');
	if( $rc['stat'] != 'ok' ) { 
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artclub');
		return $rc;
	}
	ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.artclub', 'ciniki_artclub_history', 
		$args['business_id'], 3, 'ciniki_artclub_member_images', $member['primary_image_id'], '*', '');

	//
	// Remove the member from the database
	//
	$strsql = "DELETE FROM ciniki_artclub_members "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND id = '" . ciniki_core_dbQuote($ciniki, $args['member_id']) . "' ";
	$rc = ciniki_core_dbDelete($ciniki, $strsql, 'ciniki.artclub');
	if( $rc['stat'] != 'ok' ) { 
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artclub');
		return $rc;
	}
	ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.artclub', 'ciniki_artclub_history', 
		$args['business_id'], 3, 'ciniki_artclub_members', $args['member_id'], '*', '');

	//
	// Commit the database changes
	//
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'ciniki.artclub');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	//
	// Update the last_change date in the business modules
	// Ignore the result, as we don't want to stop user updates if this fails.
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'updateModuleChangeDate');
	ciniki_businesses_updateModuleChangeDate($ciniki, $args['business_id'], 'ciniki', 'artclub');

	//
	// Add to the sync queue so it will get pushed
	//
	$ciniki['syncqueue'][] = array('push'=>'ciniki.artclub.member', 
		'args'=>array('delete_uuid'=>$member['uuid'], 'delete_id'=>$member['id']));

	return array('stat'=>'ok');
}
?>
