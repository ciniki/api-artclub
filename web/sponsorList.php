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
function ciniki_artclub_web_sponsorList($ciniki, $settings, $business_id) {

	$strsql = "SELECT ciniki_artclub_sponsors.id, "
		. "ciniki_artclub_sponsors.category, "
		. "IF(ciniki_artclub_sponsors.company='', CONCAT_WS(' ', ciniki_artclub_sponsors.first, ciniki_artclub_sponsors.last), ciniki_artclub_sponsors.company) AS name, "
		. "ciniki_artclub_sponsors.permalink, "
		. "ciniki_artclub_sponsors.short_description, "
		. "ciniki_artclub_sponsors.primary_image_id, "
		. "ciniki_artclub_sponsors.url "
		. "FROM ciniki_artclub_sponsors "
		. "WHERE ciniki_artclub_sponsors.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		// Check the sponsor is visible on the website
		. "AND (ciniki_artclub_sponsors.webflags&0x01) = 0 "
		. "ORDER BY category, name ";

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artclub', array(
		array('container'=>'categories', 'fname'=>'category', 'name'=>'category',
			'fields'=>array('name'=>'category')),
		array('container'=>'sponsors', 'fname'=>'id', 'name'=>'sponsor',
			'fields'=>array('id', 'name', 'image_id'=>'primary_image_id', 
				'permalink', 'description'=>'short_description', 'url')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['categories']) ) {
		return array('stat'=>'ok', 'categories'=>array());
	}
	return array('stat'=>'ok', 'categories'=>$rc['categories']);
}
?>
