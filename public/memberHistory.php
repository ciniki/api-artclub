<?php
//
// Description
// -----------
// This function will return the history for an element in the member.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:			The ID of the business to get the history for.
// member_id:			The ID of the artclub to get the history for.
// field:				The field to get the history for.
//
// Returns
// -------
//	<history>
//		<action date="2011/02/03 00:03:00" value="Value field set to" user_id="1" />
//		...
//	</history>
//	<users>
//		<user id="1" name="users.display_name" />
//		...
//	</users>
//
function ciniki_artclub_memberHistory($ciniki) {
	//
	// Find all the required and optional arguments
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'member_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Participant'), 
		'field'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Field'), 
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];
	
	//
	// Check access to business_id as owner, or sys admin
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'artclub', 'private', 'checkAccess');
	$rc = ciniki_artclub_checkAccess($ciniki, $args['business_id'], 'ciniki.artclub.memberHistory', 0);
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistory');
	return ciniki_core_dbGetModuleHistory($ciniki, 'ciniki.artclub', 'ciniki_artclub_history', $args['business_id'], 'ciniki_artclub_members', $args['member_id'], $args['field']);
}
?>
