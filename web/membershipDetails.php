<?php
//
// Description
// -----------
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_artclub_web_membershipDetails($ciniki, $settings, $business_id) {

	$strsql = "SELECT detail_value "
		. "FROM ciniki_artclub_settings "
		. "WHERE ciniki_artclub_settings.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "AND detail_key = 'membership-details' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artclub', 'membership');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( isset($rc['membership']) ) {
		$membership_details = $rc['membership']['detail_value'];
	} else {
		return array('stat'=>'ok', 'membership'=>array('details'=>'', 'files'=>array()));
	}

	$strsql = "SELECT id, name, extension, permalink, description "
		. "FROM ciniki_artclub_files "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "AND type = 1 "
		. "AND (webflags&0x01) = 0 "
		. "ORDER BY name "
		. "";

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.filedepot', array(
		array('container'=>'files', 'fname'=>'name', 'name'=>'file',
			'fields'=>array('id', 'name', 'extension', 'permalink')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( isset($rc['files']) ) {
		return array('stat'=>'ok', 'membership'=>array('details'=>$membership_details, 'files'=>$rc['files']));
	}

	return array('stat'=>'ok', 'membership'=>array('details'=>$membership_details, 'files'=>array()));
}
?>
