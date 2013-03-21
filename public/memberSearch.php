<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:			The ID of the business to search for the artclub members.
// start_needle:		The search string to use.
// limit:				(optional) The maximum number of results to return.  If not
//						specified, the maximum results will be 25.
// 
// Returns
// -------
//
function ciniki_artclub_memberSearch($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'start_needle'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Search String'), 
        'limit'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Limit'), 
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
    $rc = ciniki_artclub_checkAccess($ciniki, $args['business_id'], 'ciniki.artclub.memberSearch', 0); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	//
	// Get the number of customers in each status for the business, 
	// if no rows found, then return empty array
	//
	$strsql = "SELECT ciniki_artclub_members.id, "
		. "ciniki_artclub_members.first, "
		. "ciniki_artclub_members.last, "
		. "ciniki_artclub_members.company "
		. "FROM ciniki_artclub_members ";
		. "WHERE ciniki_artclub_members.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND (ciniki_artclub_members.first LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
			. "OR ciniki_artclub_members.last LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
			. "OR ciniki_artclub_members.company LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
			. ") "
		. "";
	if( isset($args['limit']) && is_numeric($args['limit']) && $args['limit'] > 0 ) {
		$strsql .= "LIMIT " . ciniki_core_dbQuote($ciniki, $args['limit']) . " ";	// is_numeric verified
	} else {
		$strsql .= "LIMIT 25 ";
	}

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbRspQuery');
	return ciniki_core_dbRspQuery($ciniki, $strsql, 'ciniki.artclub', 'members', 'member', array('stat'=>'ok', 'members'=>array()));
}
?>
