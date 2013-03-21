<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:		The ID of the business to get events for.
// type:			The type of participants to get.  Refer to participantAdd for 
//					more information on types.
//
// Returns
// -------
//
function ciniki_artclub_memberList($ciniki) {
	//
	// Find all the required and optional arguments
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];
	
    //  
    // Check access to business_id as owner, or sys admin. 
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'artclub', 'private', 'checkAccess');
    $ac = ciniki_artclub_checkAccess($ciniki, $args['business_id'], 'ciniki.artclub.memberList');
    if( $ac['stat'] != 'ok' ) { 
        return $ac;
    }   

	ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
	$date_format = ciniki_users_dateFormat($ciniki);
	
	//
	// Load the list of members for an artclub
	//
	$strsql = "SELECT ciniki_artclub_members.id, "
		. "ciniki_artclub_members.first, "
		. "ciniki_artclub_members.last, "
		. "ciniki_artclub_members.company, "
		. "ciniki_artclub_members.email, "
		. "ciniki_artclub_members.phone_home, "
		. "ciniki_artclub_members.phone_work, "
		. "ciniki_artclub_members.phone_cell, "
		. "ciniki_artclub_members.phone_fax "
		. "FROM ciniki_artclub_members "
		. "WHERE ciniki_artclub_members.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "ORDER BY first, last, company";

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artclub', array(
		array('container'=>'members', 'fname'=>'id', 'name'=>'member',
			'fields'=>array('id', 'first', 'last', 'company', 'email', 
				'phone_home', 'phone_work', 'phone_cell', 'phone_fax')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( isset($rc['members']) ) {
		return array('stat'=>'ok', 'members'=>$rc['members']);
	} 

	return array('stat'=>'ok', 'members'=>array());
}
?>
