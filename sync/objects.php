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
function ciniki_artclub_sync_objects($ciniki, &$sync, $business_id, $args) {
	ciniki_core_loadMethod($ciniki, 'ciniki', 'artclub', 'private', 'objects');
	return ciniki_artclub_objects($ciniki);
}
?>
