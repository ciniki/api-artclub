<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:			The ID of the business the member image is attached to.
// member_image_id:		The ID of the member image to get.
//
// Returns
// -------
//
function ciniki_artclub_memberImageGet($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'member_image_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Contact Image'),
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
    $rc = ciniki_artclub_checkAccess($ciniki, $args['business_id'], 'ciniki.artclub.memberImageGet', 0); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
	$date_format = ciniki_users_dateFormat($ciniki);

	//
	// Get the main information
	//
	$strsql = "SELECT ciniki_artclub_member_images.id, "
		. "ciniki_artclub_member_images.name, "
		. "ciniki_artclub_member_images.permalink, "
		. "ciniki_artclub_member_images.webflags, "
		. "ciniki_artclub_member_images.image_id, "
		. "ciniki_artclub_member_images.description, "
		. "ciniki_artclub_member_images.url "
		. "FROM ciniki_artclub_member_images "
		. "WHERE ciniki_artclub_member_images.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND ciniki_artclub_member_images.id = '" . ciniki_core_dbQuote($ciniki, $args['member_image_id']) . "' "
		. "";
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artclub', array(
		array('container'=>'images', 'fname'=>'id', 'name'=>'image',
			'fields'=>array('id', 'name', 'permalink', 'webflags', 'image_id', 'description', 'url',)),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['images']) ) {
		return array('stat'=>'ok', 'err'=>array('pkg'=>'ciniki', 'code'=>'937', 'msg'=>'Unable to find image'));
	}
	$image = $rc['images'][0]['image'];
	
	return array('stat'=>'ok', 'image'=>$image);
}
?>
