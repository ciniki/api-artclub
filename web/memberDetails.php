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
function ciniki_artclub_web_memberDetails($ciniki, $settings, $business_id, $permalink) {

	$strsql = "SELECT ciniki_artclub_members.id, "
		. "CONCAT_WS(' ', ciniki_artclub_members.first, ciniki_artclub_members.last) AS member, "
		. "ciniki_artclub_members.company, "
		. "ciniki_artclub_members.permalink, "
		. "ciniki_artclub_members.url, "
		. "ciniki_artclub_members.description, "
		. "ciniki_artclub_members.primary_image_id, "
		. "ciniki_artclub_member_images.image_id, "
		. "ciniki_artclub_member_images.name AS image_name, "
		. "ciniki_artclub_member_images.permalink AS image_permalink, "
		. "ciniki_artclub_member_images.description AS image_description, "
		. "ciniki_artclub_member_images.url AS image_url, "
		. "UNIX_TIMESTAMP(ciniki_artclub_member_images.last_updated) AS image_last_updated "
		. "FROM ciniki_artclub_members "
		. "LEFT JOIN ciniki_artclub_member_images ON ("
			. "ciniki_artclub_members.id = ciniki_artclub_member_images.member_id "
			. "AND (ciniki_artclub_member_images.webflags&0x01) = 0 "
			. ") "
		. "WHERE ciniki_artclub_members.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "AND ciniki_artclub_members.permalink = '" . ciniki_core_dbQuote($ciniki, $permalink) . "' "
		// Check the member is visible on the website
		. "AND (ciniki_artclub_members.webflags&0x01) = 0 "
		. "";
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
	$rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.artclub', array(
		array('container'=>'members', 'fname'=>'id', 
			'fields'=>array('id', 'permalink', 'member', 'company', 'image_id'=>'primary_image_id', 
				'url', 'description')),
		array('container'=>'images', 'fname'=>'image_id', 
			'fields'=>array('image_id', 'title'=>'image_name', 'permalink'=>'image_permalink',
				'description'=>'image_description', 'url'=>'image_url',
				'last_updated'=>'image_last_updated')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['members']) || count($rc['members']) < 1 ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'938', 'msg'=>'Unable to find member'));
	}
	$member = array_pop($rc['members']);

	if( isset($member['company']) && $member['company'] != '' ) {
		$member['name'] = $member['company'];
	} else {
		$member['name'] = $member['member'];
	}

	return array('stat'=>'ok', 'member'=>$member);
}
?>
