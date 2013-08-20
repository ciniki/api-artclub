<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:			The ID of the business to add the member to.
//
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_artclub_memberAdd(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'first'=>array('required'=>'no', 'default'=>'', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'First Name'),
		'last'=>array('required'=>'no', 'default'=>'', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'Last Name'),
		'company'=>array('required'=>'no', 'default'=>'', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'Company'),
		'permalink'=>array('required'=>'no', 'default'=>'', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'Permalink'),
		'webflags'=>array('required'=>'no', 'default'=>'0', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'Webflags'),
		'email'=>array('required'=>'no', 'default'=>'', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'Email'),
		'phone_home'=>array('required'=>'no', 'default'=>'', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'Home Phone'),
		'phone_work'=>array('required'=>'no', 'default'=>'', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'Work phone'),
		'phone_cell'=>array('required'=>'no', 'default'=>'', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'Cell'),
		'phone_fax'=>array('required'=>'no', 'default'=>'', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'Fax'),
		'url'=>array('required'=>'no', 'default'=>'', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'Website'),
		'primary_image_id'=>array('required'=>'no', 'default'=>'', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'Image'),
		'short_description'=>array('required'=>'no', 'default'=>'', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'Short Description'),
		'description'=>array('required'=>'no', 'default'=>'', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'Description'),
		'notes'=>array('required'=>'no', 'default'=>'', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'Notes'),
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];

	if( $args['first'] == '' && $args['last'] == '' && $args['company'] == '' ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'928', 'msg'=>'You must specify a name or company'));
	}

	if( !isset($args['permalink']) || $args['permalink'] == '' ) {
		if( $args['company'] != '' ) {
			$args['permalink'] = preg_replace('/ /', '-', preg_replace('/[^a-z0-9 ]/', '', strtolower($args['company'])));
		} else {
			if( $args['first'] != '' && $args['last'] != '' ) {
				$args['permalink'] = preg_replace('/ /', '-', preg_replace('/[^a-z0-9 ]/', '', strtolower($args['first'] . '-' . $args['last'])));
			} elseif( $args['first'] != '' ) {
				$args['permalink'] = preg_replace('/ /', '-', preg_replace('/[^a-z0-9 ]/', '', strtolower($args['first'])));
			} elseif( $args['last'] != '' ) {
				$args['permalink'] = preg_replace('/ /', '-', preg_replace('/[^a-z0-9 ]/', '', strtolower($args['last'])));
			}
		}
	}

    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'artclub', 'private', 'checkAccess');
    $rc = ciniki_artclub_checkAccess($ciniki, $args['business_id'], 'ciniki.artclub.memberAdd', 0); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

	//
	// Check the permalink doesn't already exist
	//
	$strsql = "SELECT id, first, last, company, permalink FROM ciniki_artclub_members "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artclub', 'member');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( $rc['num_rows'] > 0 ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'929', 'msg'=>'You already have a member with this name, please choose another name.'));
	}

	//  
	// Turn off autocommit
	//  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbInsert');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
	$rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.artclub');
	if( $rc['stat'] != 'ok' ) { 
		return $rc;
	}   

	//
	// Get a new UUID
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUUID');
	$rc = ciniki_core_dbUUID($ciniki, 'ciniki.artclub');
	if( $rc['stat'] != 'ok' ) {
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artclub');
		return $rc;
	}
	$args['uuid'] = $rc['uuid'];

	//
	// Add the artclub to the database
	//
	$strsql = "INSERT INTO ciniki_artclub_members (uuid, business_id, "
		. "first, last, company, permalink, webflags, email, "
		. "phone_home, phone_work, phone_cell, phone_fax, url, "
		. "primary_image_id, short_description, description, notes, "
		. "date_added, last_updated) VALUES ("
		. "'" . ciniki_core_dbQuote($ciniki, $args['uuid']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $args['first']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $args['last']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $args['company']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $args['webflags']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $args['email']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $args['phone_home']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $args['phone_work']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $args['phone_cell']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $args['phone_fax']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $args['url']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $args['primary_image_id']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $args['short_description']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $args['description']) . "', "
		. "'" . ciniki_core_dbQuote($ciniki, $args['notes']) . "', "
		. "UTC_TIMESTAMP(), UTC_TIMESTAMP())";
	$rc = ciniki_core_dbInsert($ciniki, $strsql, 'ciniki.artclub');
	if( $rc['stat'] != 'ok' ) { 
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artclub');
		return $rc;
	}
	if( !isset($rc['insert_id']) || $rc['insert_id'] < 1 ) {
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artclub');
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'927', 'msg'=>'Unable to add member'));
	}
	$member_id = $rc['insert_id'];

	//
	// Add all the fields to the change log
	//
	$changelog_fields = array(
		'uuid',
		'first',
		'last',
		'company',
		'permalink',
		'webflags',
		'email',
		'phome_home',
		'phone_work',
		'phone_cell',
		'phone_fax',
		'url',
		'primary_image_id',
		'short_description',
		'description',
		'notes',
		);
	foreach($changelog_fields as $field) {
		if( isset($args[$field]) && $args[$field] != '' ) {
			$rc = ciniki_core_dbAddModuleHistory($ciniki, 'ciniki.artclub', 
				'ciniki_artclub_history', $args['business_id'], 
				1, 'ciniki_artclub_members', $member_id, $field, $args[$field]);
		}
	}

	//
	// Add image reference
	//
	if( $args['primary_image_id'] != '' && $args['primary_image_id'] != '0' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'images', 'private', 'refAdd');
		$rc = ciniki_images_refAdd($ciniki, $args['business_id'], array(
			'image_id'=>$args['primary_image_id'], 
			'object'=>'ciniki.artclub.member', 
			'object_id'=>$member_id,
			'object_field'=>'primary_image_id'));
		if( $rc['stat'] != 'ok' ) {
			ciniki_core_dbTransactionRollback($ciniki, 'ciniki.artclub');
			return $rc;
		}
	}

	//
	// Commit the database changes
	//
    $rc = ciniki_core_dbTransactionCommit($ciniki, 'ciniki.artclub');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	//
	// Update the last_change date in the business modules
	// Ignore the result, as we don't want to stop user updates if this fails.
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'updateModuleChangeDate');
	ciniki_businesses_updateModuleChangeDate($ciniki, $args['business_id'], 'ciniki', 'artclub');

	$ciniki['syncqueue'][] = array('push'=>'ciniki.artclub.member', 
		'args'=>array('id'=>$member_id));

	return array('stat'=>'ok', 'id'=>$member_id);
}
?>
