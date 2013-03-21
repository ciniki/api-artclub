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
function ciniki_artclub_web_memberList($ciniki, $settings, $business_id) {

	$strsql = "SELECT ciniki_artclub_members.id, "
		. "IF(ciniki_artclub_members.company='', CONCAT_WS(' ', ciniki_artclub_members.first, ciniki_artclub_members.last), ciniki_artclub_members.company) AS name, "
		. "ciniki_artclub_members.permalink, "
		. "ciniki_artclub_members.short_description, "
		. "ciniki_artclub_members.primary_image_id, "
		. "ciniki_artclub_members.url "
		. "FROM ciniki_artclub_members "
		. "WHERE ciniki_artclub_members.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		// Check the member is visible on the website
		. "AND (ciniki_artclub_members.webflags&0x01) = 0 "
		. "ORDER BY name ";

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artclub', array(
		array('container'=>'members', 'fname'=>'id', 'name'=>'member',
			'fields'=>array('id', 'name', 'image_id'=>'primary_image_id', 
				'permalink', 'description'=>'short_description', 'url')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['members']) ) {
		return array('stat'=>'ok', 'members'=>array());
	}
	return array('stat'=>'ok', 'members'=>$rc['members']);
}
?>
