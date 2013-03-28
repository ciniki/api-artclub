<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:			The ID of the business to sponsor belongs to.
// sponsor_id:			The ID of the sponsor to get.
// images:				Specify if the method should return the image thumbnails.
//
// Returns
// -------
//
function ciniki_artclub_sponsorGet($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'sponsor_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Participant'),
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
    $rc = ciniki_artclub_checkAccess($ciniki, $args['business_id'], 'ciniki.artclub.sponsorGet', 0); 
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
		. "ciniki_artclub_sponsors.id AS sponsor_id, "
		. "ciniki_artclub_sponsors.first, "
		. "ciniki_artclub_sponsors.last, "
		. "ciniki_artclub_sponsors.company, "
		. "ciniki_artclub_sponsors.email, "
		. "ciniki_artclub_sponsors.category, "
		. "ciniki_artclub_sponsors.webflags, "
		. "IF(ciniki_artclub_sponsors.webflags&0x01=1,'Hidden','Visible') AS webvisible, "
		. "ciniki_artclub_sponsors.phone_home, "
		. "ciniki_artclub_sponsors.phone_work, "
		. "ciniki_artclub_sponsors.phone_cell, "
		. "ciniki_artclub_sponsors.phone_fax, "
		. "ciniki_artclub_sponsors.url, "
		. "ciniki_artclub_sponsors.primary_image_id, "
		. "ciniki_artclub_sponsors.short_description, "
		. "ciniki_artclub_sponsors.notes ";
	$strsql .= "FROM ciniki_artclub_sponsors ";
	$strsql .= "WHERE ciniki_artclub_sponsors.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND ciniki_artclub_sponsors.id = '" . ciniki_core_dbQuote($ciniki, $args['sponsor_id']) . "' "
		. "ORDER BY ciniki_artclub_sponsors.id ASC ";

	//
	// Check if we need to include thumbnail images
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artclub', array(
		array('container'=>'sponsors', 'fname'=>'sponsor_id', 'name'=>'sponsor',
			'fields'=>array('id'=>'sponsor_id', 'category', 'webflags', 'webvisible',
				'first', 'last', 'company', 'email', 'phone_home',
				'phone_work', 'phone_cell', 'phone_fax', 'url', 'primary_image_id',
				'short_description', 'notes')),
	));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['sponsors']) || !isset($rc['sponsors'][0]) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'961', 'msg'=>'Unable to find sponsor'));
	}
	$sponsor = $rc['sponsors'][0]['sponsor'];
	
	return array('stat'=>'ok', 'sponsor'=>$sponsor);
}
?>
