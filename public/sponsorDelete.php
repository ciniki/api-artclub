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
function ciniki_artclub_sponsorDelete(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'sponsor_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Member'),
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
    $rc = ciniki_artclub_checkAccess($ciniki, $args['business_id'], 'ciniki.artclub.sponsorDelete', 0); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	//
	// Get the existing sponsor information to delete
	//
	$strsql = "SELECT id, uuid FROM ciniki_artclub_sponsors "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND id = '" . ciniki_core_dbQuote($ciniki, $args['sponsor_id']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artclub', 'sponsor');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['sponsor']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1350', 'msg'=>'Member does not exist'));
	}
	$uuid = $rc['sponsor']['uuid'];

	//
	// Remove the sponsor
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
	return ciniki_core_objectDelete($ciniki, $args['business_id'], 'ciniki.artclub.sponsor',
		$args['sponsor_id'], $uuid, 0x06);
}
?>
