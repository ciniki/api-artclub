//
// The app to add/edit artclub member images
//
function ciniki_artclub_memberimages() {
	this.webFlags = {
		'1':{'name':'Hidden'},
		};
	this.init = function() {
		//
		// The panel to display the edit form
		//
		this.edit = new M.panel('Edit Image',
			'ciniki_artclub_memberimages', 'edit',
			'mc', 'medium', 'sectioned', 'ciniki.artclub.memberimages.edit');
		this.edit.data = {};
		this.edit.member_id = 0;
		this.edit.sections = {
			'_image':{'label':'Photo', 'fields':{
				'image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no'},
			}},
			'info':{'label':'Information', 'type':'simpleform', 'fields':{
				'name':{'label':'Title', 'type':'text'},
				'webflags':{'label':'Website', 'type':'flags', 'join':'yes', 'flags':this.webFlags},
			}},
			'_description':{'label':'Description', 'type':'simpleform', 'fields':{
				'description':{'label':'', 'type':'textarea', 'size':'small', 'hidelabel':'yes'},
			}},
			'_buttons':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_artclub_memberimages.saveImage();'},
				'delete':{'label':'Delete', 'fn':'M.ciniki_artclub_memberimages.deleteImage();'},
			}},
		};
		this.edit.fieldValue = function(s, i, d) { 
			if( this.data[i] != null ) {
				return this.data[i]; 
			} 
			return ''; 
		};
		this.edit.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.artclub.memberImageHistory', 'args':{'business_id':M.curBusinessID, 
				'member_image_id':M.ciniki_artclub_memberimages.edit.member_image_id, 'field':i}};
		};
		this.edit.addDropImage = function(iid) {
			M.ciniki_artclub_memberimages.edit.setFieldValue('image_id', iid);
			return true;
		}
		this.edit.addButton('save', 'Save', 'M.ciniki_artclub_memberimages.saveImage();');
		this.edit.addClose('Cancel');
	};

	this.start = function(cb, appPrefix, aG) {
		args = {};
		if( aG != null ) {
			args = eval(aG);
		}

		//
		// Create container
		//
		var appContainer = M.createContainer(appPrefix, 'ciniki_artclub_memberimages', 'yes');
		if( appContainer == null ) {
			alert('App Error');
			return false;
		}

		if( args.add != null && args.add == 'yes' ) {
			this.showEdit(cb, 0, args.member_id);
		} else if( args.member_image_id != null && args.member_image_id > 0 ) {
			this.showEdit(cb, args.member_image_id);
		}
		return false;
	}

	this.showEdit = function(cb, iid, mid) {
		if( mid != null ) { this.edit.member_id = mid; }
		if( iid != null ) { this.edit.member_image_id = iid; }
		if( this.edit.member_image_id > 0 ) {
			this.edit.sections._buttons.buttons.delete.visible = 'yes';
			var rsp = M.api.getJSONCb('ciniki.artclub.memberImageGet', 
				{'business_id':M.curBusinessID, 'member_image_id':this.edit.member_image_id}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					M.ciniki_artclub_memberimages.edit.data = rsp.image;
					M.ciniki_artclub_memberimages.edit.refresh();
					M.ciniki_artclub_memberimages.edit.show(cb);
				});
		} else {
			this.edit.reset();
			this.edit.sections._buttons.buttons.delete.visible = 'no';
			this.edit.data = {};
			if( mid != null ) { this.edit.member_id = mid; }
			this.edit.refresh();
			this.edit.show(cb);
		}
	};

	this.saveImage = function() {
		if( this.edit.member_image_id > 0 ) {
			var c = this.edit.serializeFormData('no');
				if( c != '' ) {
					var rsp = M.api.postJSONFormData('ciniki.artclub.memberImageUpdate', 
						{'business_id':M.curBusinessID, 
						'member_image_id':this.edit.member_image_id}, c,
							function(rsp) {
								if( rsp.stat != 'ok' ) {
									M.api.err(rsp);
									return false;
								} else {
									M.ciniki_artclub_memberimages.edit.close();
								}
							});
				} else {
					this.edit.close();
				}
		} else {
			var c = this.edit.serializeForm('yes');
			c += '&member_id=' + encodeURIComponent(this.edit.member_id);
			var rsp = M.api.postJSONFormData('ciniki.artclub.memberImageAdd', 
				{'business_id':M.curBusinessID}, c, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					} else {
						M.ciniki_artclub_memberimages.edit.close();
					}
				});
		}
	};

	this.deleteImage = function() {
		if( confirm('Are you sure you want to delete this image?') ) {
			var rsp = M.api.getJSONCb('ciniki.artclub.memberImageDelete', {'business_id':M.curBusinessID, 
				'member_image_id':this.edit.member_image_id}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					M.ciniki_artclub_memberimages.edit.close();
				});
		}
	};
}
