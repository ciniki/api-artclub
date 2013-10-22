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
function ciniki_artclub_objects($ciniki) {
	
	$objects = array();
	$objects['member'] = array(
		'name'=>'Member',
		'sync'=>'yes',
		'table'=>'ciniki_artclub_members',
		'fields'=>array(
			'first'=>array(),
			'last'=>array(),
			'company'=>array(),
			'category'=>array(),
			'permalink'=>array(),
			'webflags'=>array(),
			'email'=>array(),
			'passcode'=>array(),
			'phone_home'=>array(),
			'phone_work'=>array(),
			'phone_cell'=>array(),
			'phone_fax'=>array(),
			'url'=>array(),
			'primary_image_id'=>array('ref'=>'ciniki.images.image'),
			'short_description'=>array(),
			'description'=>array(),
			'notes'=>array(),
			),
		'history_table'=>'ciniki_artclub_history',
		);
	$objects['member_image'] = array(
		'name'=>'Member Image',
		'sync'=>'yes',
		'table'=>'ciniki_artclub_member_images',
		'fields'=>array(
			'member_id'=>array('ref'=>'ciniki.artclub.member'),
			'name'=>array(),
			'permalink'=>array(),
			'webflags'=>array(),
			'image_id'=>array('ref'=>'ciniki.images.image'),
			'description'=>array(),
			'url'=>array(),
			),
		'history_table'=>'ciniki_artclub_history',
		);
	$objects['file'] = array(
		'name'=>'File',
		'sync'=>'yes',
		'table'=>'ciniki_artclub_files',
		'fields'=>array(
			'type'=>array(),
			'extension'=>array(),
			'status'=>array(),
			'name'=>array(),
			'permalink'=>array(),
			'webflags'=>array(),
			'description'=>array(),
			'org_filename'=>array(),
			'publish_date'=>array(),
			'binary_content'=>array('history'=>'no'),
			),
		'history_table'=>'ciniki_artclub_history',
		);
	$objects['sponsor'] = array(
		'name'=>'Sponsor',
		'sync'=>'yes',
		'table'=>'ciniki_artclub_sponsors',
		'fields'=>array(
			'first'=>array(),
			'last'=>array(),
			'company'=>array(),
			'category'=>array(),
			'permalink'=>array(),
			'webflags'=>array(),
			'email'=>array(),
			'passcode'=>array(),
			'phone_home'=>array(),
			'phone_work'=>array(),
			'phone_cell'=>array(),
			'phone_fax'=>array(),
			'url'=>array(),
			'primary_image_id'=>array('ref'=>'ciniki.images.image'),
			'short_description'=>array(),
			'description'=>array(),
			'notes'=>array(),
			),
		'history_table'=>'ciniki_artclub_history',
		);
	$objects['setting'] = array(
		'type'=>'settings',
		'name'=>'Artclub Settings',
		'table'=>'ciniki_artclub_settings',
		'history_table'=>'ciniki_artclub_history',
		);
	
	return array('stat'=>'ok', 'objects'=>$objects);
}
?>
