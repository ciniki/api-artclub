<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:			The ID of the business to member belongs to.
// member_id:			The ID of the member to get.
// images:				Specify if the method should return the image thumbnails.
//
// Returns
// -------
//
function ciniki_artclub_memberGet($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'member_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Participant'),
		'images'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Images'),
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
    $rc = ciniki_artclub_checkAccess($ciniki, $args['business_id'], 'ciniki.artclub.memberGet', 0); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
	$date_format = ciniki_users_dateFormat($ciniki);

	//
	// Get the main information
	//
	$strsql = "SELECT "
		. "ciniki_artclub_members.id AS member_id, "
		. "ciniki_artclub_members.first, "
		. "ciniki_artclub_members.last, "
		. "ciniki_artclub_members.company, "
		. "ciniki_artclub_members.category, "
		. "ciniki_artclub_members.email, "
		. "ciniki_artclub_members.webflags, "
		. "IF(ciniki_artclub_members.webflags&0x01=1,'Hidden','Visible') AS webvisible, "
		. "ciniki_artclub_members.phone_home, "
		. "ciniki_artclub_members.phone_work, "
		. "ciniki_artclub_members.phone_cell, "
		. "ciniki_artclub_members.phone_fax, "
		. "ciniki_artclub_members.url, "
		. "ciniki_artclub_members.primary_image_id, "
		. "ciniki_artclub_members.short_description, "
		. "ciniki_artclub_members.description, "
		. "ciniki_artclub_members.notes ";
	if( isset($args['images']) && $args['images'] == 'yes' ) {
		$strsql .= ", "
			. "ciniki_artclub_member_images.id AS img_id, "
			. "ciniki_artclub_member_images.name AS image_name, "
			. "ciniki_artclub_member_images.webflags AS image_webflags, "
			. "ciniki_artclub_member_images.image_id, "
			. "ciniki_artclub_member_images.description AS image_description, "
			. "ciniki_artclub_member_images.url AS image_url "
			. "";
	}
	$strsql .= "FROM ciniki_artclub_members ";
	if( isset($args['images']) && $args['images'] == 'yes' ) {
		$strsql .= "LEFT JOIN ciniki_artclub_member_images ON (ciniki_artclub_members.id = ciniki_artclub_member_images.member_id "
			. "AND ciniki_artclub_member_images.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. ") ";
	}
	$strsql .= "WHERE ciniki_artclub_members.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND ciniki_artclub_members.id = '" . ciniki_core_dbQuote($ciniki, $args['member_id']) . "' "
		. "ORDER BY ciniki_artclub_members.id ASC ";

	//
	// Check if we need to include thumbnail images
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
	if( isset($args['images']) && $args['images'] == 'yes' ) {
		$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artclub', array(
			array('container'=>'members', 'fname'=>'member_id', 'name'=>'member',
				'fields'=>array('id'=>'member_id', 'category', 'webflags', 'webvisible', 
					'first', 'last', 'company', 'email', 'phone_home',
					'phone_work', 'phone_cell', 'phone_fax', 'url', 'primary_image_id',
					'short_description', 'description', 'notes')),
			array('container'=>'images', 'fname'=>'img_id', 'name'=>'image',
				'fields'=>array('id'=>'img_id', 'name'=>'image_name', 'webflags'=>'image_webflags',
					'image_id', 'description'=>'image_description', 'url'=>'image_url')),
		));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( !isset($rc['members']) || !isset($rc['members'][0]) ) {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'930', 'msg'=>'Unable to find member'));
		}
		$member = $rc['members'][0]['member'];
		ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'loadCacheThumbnail');
		if( isset($member['images']) ) {
			foreach($member['images'] as $img_id => $img) {
				if( isset($img['image']['image_id']) && $img['image']['image_id'] > 0 ) {
					$rc = ciniki_images_loadCacheThumbnail($ciniki, $img['image']['image_id'], 75);
					if( $rc['stat'] != 'ok' ) {
						return $rc;
					}
					$member['images'][$img_id]['image']['image_data'] = 'data:image/jpg;base64,' . base64_encode($rc['image']);
				}
			}
		}
	} else {
		$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artclub', array(
			array('container'=>'members', 'fname'=>'member_id', 'name'=>'member',
				'fields'=>array('id'=>'member_id', 'category', 'type', 'status',
					'webflags', 'title', 'location', 
					'member_id', 'first', 'last', 'company', 'email', 'phone_home',
					'phone_work', 'phone_cell', 'phone_fax', 'url', 'primary_image_id',
					'short_description', 'description', 'notes')),
		));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( !isset($rc['members']) || !isset($rc['members'][0]) ) {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'931', 'msg'=>'Unable to find member'));
		}
		$member = $rc['members'][0]['member'];
	}
	
	return array('stat'=>'ok', 'member'=>$member);
}
?>
