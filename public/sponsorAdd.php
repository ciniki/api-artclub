<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:			The ID of the business to add the sponsor to.
//
// Returns
// -------
// <rsp stat='ok' id='34' />
//
function ciniki_artclub_sponsorAdd(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'first'=>array('required'=>'no', 'default'=>'', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'First Name'),
		'last'=>array('required'=>'no', 'default'=>'', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'Last Name'),
		'company'=>array('required'=>'no', 'default'=>'', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'Company'),
		'category'=>array('required'=>'no', 'default'=>'', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'Category'),
		'passcode'=>array('required'=>'no', 'default'=>'', 'trimblanks'=>'yes', 'blank'=>'yes', 'name'=>'Passcode'),
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
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'947', 'msg'=>'You must specify a name or company'));
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
    $rc = ciniki_artclub_checkAccess($ciniki, $args['business_id'], 'ciniki.artclub.sponsorAdd', 0); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }

	//
	// Check the permalink doesn't already exist
	//
	$strsql = "SELECT id, first, last, company, permalink FROM ciniki_artclub_sponsors "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.artclub', 'sponsor');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( $rc['num_rows'] > 0 ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'958', 'msg'=>'You already have a sponsor with this name, please choose another name.'));
	}

	//
	// Add the sponsor
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
	return ciniki_core_objectAdd($ciniki, $args['business_id'], 'ciniki.artclub.sponsor', $args, 0x07);
}
?>
