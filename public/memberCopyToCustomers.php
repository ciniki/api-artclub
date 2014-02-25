<?php
//
// Description
// -----------
// This method will copy club members into the customers database.  It is assumed they don't 
// already exists as customers.
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
function ciniki_artclub_memberCopyToCustomers($ciniki) {
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
	// Sysadmins are allowed full access
	//
	if( ($ciniki['session']['user']['perms'] & 0x01) != 0x01 ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'999', 'msg'=>'Access denied'));
	}

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryIDTree');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuote');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
	$date_format = ciniki_users_dateFormat($ciniki);

	//
	// Get the list of members, and their history
	//
	$strsql = "SELECT ciniki_artclub_members.id, ciniki_artclub_members.uuid, "
		. "ciniki_artclub_members.business_id, "
		. "first, last, company, "
		. "ciniki_artclub_members.webflags, email, "
		. "phone_home, phone_work, phone_cell, phone_fax, "
		. "ciniki_artclub_members.url, primary_image_id, "
		. "short_description, ciniki_artclub_members.description, notes, "
		. "ciniki_artclub_members.date_added, ciniki_artclub_members.last_updated, "
		. "ciniki_artclub_member_images.id AS image_id, "
		. "ciniki_artclub_member_images.name AS image_name, "
		. "ciniki_artclub_member_images.permalink AS image_permalink, "
		. "ciniki_artclub_member_images.image_id, "
		. "ciniki_artclub_member_images.webflags AS image_webflags, "
		. "ciniki_artclub_member_images.description AS image_description "
//		. "ciniki_artclub_history.id AS history_id, "
//		. "ciniki_artclub_history.uuid AS history_uuid, "
//		. "ciniki_artclub_history.session, "
//		. "ciniki_artclub_history.action, "
//		. "ciniki_artclub_history.table_key, "
//		. "ciniki_artclub_history.table_field, "
//		. "ciniki_artclub_history.new_value, "
//		. "ciniki_artclub_history.log_date "
		. "FROM ciniki_artclub_members "
		. "LEFT JOIN ciniki_artclub_member_images ON (ciniki_artclub_members.id = ciniki_artclub_member_images.member_id "
			. "AND ciniki_artclub_member_images.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. ") "
//		. "LEFT JOIN ciniki_artclub_history ON (ciniki_artclub_members.id = ciniki_artclub_history.table_key "
//			. "AND ciniki_artclub_history.table_name = 'ciniki_artclub_members' "
//			. "AND ciniki_artclub_history.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
//			. ") "
		. "WHERE ciniki_artclub_members.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' " 
		. "";

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
	$rc = ciniki_core_dbHashQueryIDTree($ciniki, $strsql, 'ciniki.artclub', array(
		array('container'=>'members', 'fname'=>'id',
			'fields'=>array('id', 'uuid', 'first', 'last', 'company', 
				'webflags', 'email', 'phone_home', 'phone_work', 'phone_cell', 'phone_fax', 
				'url', 'primary_image_id',
				'short_description', 'description', 'notes',
				'date_added', 'last_updated')),
		array('container'=>'images', 'fname'=>'image_id',
			'fields'=>array('id'=>'image_id', 'name'=>'image_name', 'webflags'=>'image_webflags',
				'permalink'=>'image_permalink', 'image_id', 'description'=>'image_description')),
//		array('container'=>'history', 'fname'=>'history_id',
//			'fields'=>array('id'=>'history_id', 'uuid'=>'history_uuid', 'session'
//				'action', 'table_key', 'table_field', 'new_value', 'log_date')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['members']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1602', 'msg'=>'Unable to find members'));
	}
	$members = $rc['members'];

	foreach($members as $member) {
		//
		// Add the customer
		//
		$display_name = $member['first'] . ' ' . $member['last'];
		$permalink = ciniki_core_makePermalink($ciniki, $display_name);
		$oargs = array(
			'cid'=>'',
			'status'=>'1',
			'type'=>'1',
			'member_status'=>'10',
			'prefix'=>'',
			'first'=>$member['first'],
			'middle'=>'',
			'last'=>$member['last'],
			'suffix'=>'',
			'display_name'=>$display_name,
			'company'=>$member['company'],
			'department'=>'',
			'title'=>'',
			'phone_home'=>$member['phone_home'],
			'phone_work'=>$member['phone_work'],
			'phone_cell'=>$member['phone_cell'],
			'phone_fax'=>$member['phone_fax'],
			'notes'=>$member['notes'],
			'birthdate'=>'',
			'webflags'=>1,
			'permalink'=>$permalink,
			'primary_image_id'=>$member['primary_image_id'],
			'short_bio'=>$member['short_description'],
			'full_bio'=>$member['description'],
			);
		if( ($member['webflags']&0x01) == 1 ) {
			$oargs['webflags'] = 0;
		}
		$rc = ciniki_core_objectAdd($ciniki, $args['business_id'], 'ciniki.customers.customer',
			$oargs, 0x04);
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		$customer_id = $rc['id'];

		//
		// Add the images
		//
		if( isset($member['images']) ) {
			foreach($member['images'] as $image) {
				$oargs = array('customer_id'=>$customer_id,
					'name'=>$image['name'],
					'permalink'=>$image['permalink'],
					'webflags'=>0,
					'image_id'=>$image['image_id'],
					'description'=>$image['description'],
					);
				if( $image['webflags'] == 0 ) {
					$oargs['webflags'] = 1;
				}
				$rc = ciniki_core_objectAdd($ciniki, $args['business_id'], 'ciniki.customers.image',
					$oargs, 0x04);
				if( $rc['stat'] != 'ok' ) {
					return $rc;
				}
			}
		}

		//
		// Add the email address
		//
		if( isset($member['email']) && $member['email'] != '' ) {
			$oargs = array('customer_id'=>$customer_id,
				'email'=>$member['email'],
				'password'=>'',
				'temp_password'=>'',
				'temp_password_date'=>'',
				'flags'=>1,
				);
			$rc = ciniki_core_objectAdd($ciniki, $args['business_id'], 'ciniki.customers.email',
				$oargs, 0x04);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
		}

		//
		// Add the links
		//
		if( isset($member['url']) && $member['url'] != '' ) {
			$oargs = array('customer_id'=>$customer_id,
				'url'=>$member['url'],
				'name'=>'',
				'description'=>'',
				'webflags'=>1,
				);
			$rc = ciniki_core_objectAdd($ciniki, $args['business_id'], 'ciniki.customers.link',
				$oargs, 0x04);
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
		}
	}
	
	return array('stat'=>'ok');
}
?>
