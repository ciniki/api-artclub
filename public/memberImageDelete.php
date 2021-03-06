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
function ciniki_artclub_memberImageDelete(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'member_image_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Image'),
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
    $rc = ciniki_artclub_checkAccess($ciniki, $args['business_id'], 'ciniki.artclub.memberImageDelete', 0); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	//
	// Get the existing image information
	//
	$strsql = "SELECT id, uuid, image_id FROM ciniki_artclub_member_images "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND id = '" . ciniki_core_dbQuote($ciniki, $args['member_image_id']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artclub', 'item');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['item']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'936', 'msg'=>'Contact image does not exist'));
	}
	$uuid = $rc['item']['uuid'];

	//
	// Delete the object
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
	return ciniki_core_objectDelete($ciniki, $args['business_id'], 'ciniki.artclub.member_image', $args['member_image_id'], $uuid, 0x07);
}
?>
