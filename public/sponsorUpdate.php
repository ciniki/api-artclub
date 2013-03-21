<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:			The ID of the business to add the artclub to.
// name:				The name of the artclub.  
//
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_artclub_sponsorUpdate(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'sponsor_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'first'=>array('required'=>'no', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'First Name'),
		'last'=>array('required'=>'no', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'Last Name'),
		'company'=>array('required'=>'no', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'Company'),
		'permalink'=>array('required'=>'no', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'Permalink'),
		'email'=>array('required'=>'no', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'Email'),
		'phone_home'=>array('required'=>'no', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'Home Phone'),
		'phone_work'=>array('required'=>'no', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'Work phone'),
		'phone_cell'=>array('required'=>'no', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'Cell'),
		'phone_fax'=>array('required'=>'no', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'Fax'),
		'url'=>array('required'=>'no', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'Website'),
		'primary_image_id'=>array('required'=>'no', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'Image'),
		'short_description'=>array('required'=>'no', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'Short Description'),
		'description'=>array('required'=>'no', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'Description'),
		'notes'=>array('required'=>'no', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'Notes'),
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];

	if( isset($args['name']) && (!isset($args['permalink']) || $args['permalink'] == '') ) {
		$args['permalink'] = preg_replace('/ /', '-', preg_replace('/[^a-z0-9 ]/', '', strtolower($args[    'name'])));
	}

    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'artclub', 'private', 'checkAccess');
    $rc = ciniki_artclub_checkAccess($ciniki, $args['business_id'], 'ciniki.artclub.sponsorUpdate', 0); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

	//
	// Check the permalink doesn't already exist
	//
	if( isset($args['permalink']) ) {
		$strsql = "SELECT id, name, permalink FROM ciniki_artclub "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
			. "";
		$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artclub', 'artclub');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( $rc['num_rows'] > 0 ) {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'942', 'msg'=>'You already have an artclub with this name, please choose another name.'));
		}
	}

	//
	// Get the existing image details
	//
	$strsql = "SELECT uuid, primary_image_id FROM ciniki_artclub_sponsors "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND id = '" . ciniki_core_dbQuote($ciniki, $args['sponsor_id']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artclub', 'item');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['item']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'943', 'msg'=>'Contact image not found'));
	}
	$item = $rc['item'];

	//  
	// Turn off autocommit
	//  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbInsert');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUpdate');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
	$rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.artclub');
	if( $rc['stat'] != 'ok' ) { 
		return $rc;
	}   

	//
	// Add all the fields to the change log
	//
	$strsql = "UPDATE ciniki_artclub_sponsors SET last_updated = UTC_TIMESTAMP()";

	$changelog_fields = array(
		'first',
		'last',
		'company',
		'permalink',
		'email',
		'phome_home',
		'phone_work',
		'phone_cell',
		'phone_fax',
		'url',
		'primary_image_id',
		'short_description',
		'description',
		'notes',
		);
	foreach($changelog_fields as $field) {
		if( isset($args[$field]) ) {
			$strsql .= ", $field = '" . ciniki_core_dbQuote($ciniki, $args[$field]) . "' ";
			$rc = ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.artclub', 
				'ciniki_artclub_history', $args['business_id'], 
				2, 'ciniki_artclub_sponsors', $args['sponsor_id'], $field, $args[$field]);
		}
	}
	$strsql .= "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND id = '" . ciniki_core_dbQuote($ciniki, $args['sponsor_id']) . "' "
		. "";
	$rc = ciniki_core_dbUpdate($ciniki, $strsql, 'ciniki.artclub');
	if( $rc['stat'] != 'ok' ) {
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artclub');
		return $rc;
	}
	if( !isset($rc['num_affected_rows']) || $rc['num_affected_rows'] != 1 ) {
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artclub');
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'945', 'msg'=>'Unable to update artclub'));	
	}

	//
	// Update image reference
	//
	if( isset($args['primary_image_id']) && $item['primary_image_id'] != $args['primary_image_id']) {
		//
		// Remove the old reference
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'refClear');
		$rc = ciniki_images_refClear($ciniki, $args['business_id'], array(
			'object'=>'ciniki.artclub.sponsor', 
			'object_id'=>$args['sponsor_id']));
		if( $rc['stat'] == 'fail' ) {
			ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artclub');
			return $rc;
		}

		//
		// Add the new reference
		//
		ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'refAdd');
		$rc = ciniki_images_refAdd($ciniki, $args['business_id'], array(
			'image_id'=>$args['primary_image_id'], 
			'object'=>'ciniki.artclub.sponsor', 
			'object_id'=>$args['sponsor_id'],
			'object_field'=>'primary_image_id'));
		if( $rc['stat'] != 'ok' ) {
			ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artclub');
			return $rc;
		}
	}

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

	$ciniki['syncqueue'][] = array('push'=>'ciniki.artclub.sponsor', 
		'args'=>array('id'=>$args['sponsor_id']));

	return array('stat'=>'ok');
}
?>
