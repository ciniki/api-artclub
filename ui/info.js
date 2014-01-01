//
// The artclub app to manage an artists collection
//
function ciniki_artclub_info() {
	this.init = function() {
		//
		// Setup the main panel to list the collection
		//
		this.menu = new M.panel('Files',
			'ciniki_artclub_info', 'menu',
			'mc', 'medium', 'sectioned', 'ciniki.artclub.info.menu');
		this.menu.data = {};
		this.menu.sections = {
			'_menu':{'label':'', 'list':{
				'membership':{'label':'Membership', 'fn':'M.ciniki_artclub_info.showMembership(\'M.ciniki_artclub_info.showMenu();\');'},
				}},
			};
		this.menu.addClose('Back');

		//
		// The panel to display the add form
		//
		this.membership = new M.panel('Membership Details',
			'ciniki_artclub_info', 'membership',
			'mc', 'medium', 'sectioned', 'ciniki.artclub.info.membership');
		this.membership.data = {};	
		this.membership.sections = {
			'membership-details-html':{'label':'Membership Details', 'type':'htmlcontent'},
			'_buttons':{'label':'', 'buttons':{
				'edit':{'label':'Edit', 'fn':'M.ciniki_artclub_info.showEditMembership(\'M.ciniki_artclub_info.showMembership();\');'},
				}},
			'applications':{'label':'Application Forms',
				'type':'simplegrid', 'num_cols':1,
				'headerValues':null,
				'cellClasses':[''],
				'addTxt':'Add Application',
				'addFn':'M.ciniki_artclub_info.showAddApplication(\'M.ciniki_artclub_info.showMembership();\');',
				}
		};
		this.membership.cellValue = function(s, i, j, d) {
			if( j == 0 ) { return d.file.name; }
		};
		this.membership.rowFn = function(s, i, d) {
			return 'M.ciniki_artclub_info.showEditApplication(\'M.ciniki_artclub_info.showMembership();\', \'' + d.file.id + '\');'; 
		};
		this.membership.sectionData = function(s) { 
			return this.data[s];
		};
		this.membership.addClose('Back');

		//
		// The panel to display the edit membership details form
		//
		this.editmembership = new M.panel('File',
			'ciniki_artclub_info', 'editmembership',
			'mc', 'medium', 'sectioned', 'ciniki.artclub.info.editmembership');
		this.editmembership.file_id = 0;
		this.editmembership.data = null;
		this.editmembership.sections = {
			'_description':{'label':'Description', 'type':'simpleform', 'fields':{
				'membership-details':{'label':'', 'type':'textarea', 'size':'large', 'hidelabel':'yes'},
			}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_artclub_info.saveMembership();'},
			}},
		};
		this.editmembership.fieldValue = function(s, i, d) { 
			return this.data[i]; 
		}
		this.editmembership.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.artclub.settingsHistory', 'args':{'business_id':M.curBusinessID, 'setting':i}};
		};
		this.editmembership.addButton('save', 'Save', 'M.ciniki_artclub_info.saveMembership();');
		this.editmembership.addClose('Cancel');


		//
		// The panel to display the add form
		//
		this.addapplication = new M.panel('Add File',
			'ciniki_artclub_info', 'addapplication',
			'mc', 'medium', 'sectioned', 'ciniki.artclub.info.editapplication');
		this.addapplication.default_data = {'type':'2'};
		this.addapplication.data = {};	
// FIXME:		this.add.uploadDropFn = function() { return M.ciniki_artclub_info.uploadDropImagesAdd; };
		this.addapplication.sections = {
			'_file':{'label':'File', 'fields':{
				'uploadfile':{'label':'', 'type':'file', 'hidelabel':'yes'},
			}},
			'info':{'label':'Information', 'type':'simpleform', 'fields':{
				'name':{'label':'Title', 'type':'text'},
			}},
//			'_description':{'label':'Description', 'type':'simpleform', 'fields':{
//				'description':{'label':'', 'type':'textarea', 'size':'small', 'hidelabel':'yes'},
//			}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_artclub_info.addApplication();'},
			}},
		};
		this.addapplication.fieldValue = function(s, i, d) { 
			if( this.data[i] != null ) {
				return this.data[i]; 
			} 
			return ''; 
		};
		this.addapplication.addButton('save', 'Save', 'M.ciniki_artclub_info.addApplication();');
		this.addapplication.addClose('Cancel');

		//
		// The panel to display the edit form
		//
		this.editapplication = new M.panel('File',
			'ciniki_artclub_info', 'editapplication',
			'mc', 'medium', 'sectioned', 'ciniki.artclub.info.editapplications');
		this.editapplication.file_id = 0;
		this.editapplication.data = null;
		this.editapplication.sections = {
			'info':{'label':'Details', 'type':'simpleform', 'fields':{
				'name':{'label':'Title', 'type':'text'},
			}},
			'_save':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_artclub_info.saveApplication();'},
				'download':{'label':'Download', 'fn':'M.ciniki_artclub_info.downloadFile(M.ciniki_artclub_info.editapplication.file_id);'},
				'delete':{'label':'Delete', 'fn':'M.ciniki_artclub_info.deleteApplication();'},
			}},
		};
		this.editapplication.fieldValue = function(s, i, d) { 
			return this.data[i]; 
		}
		this.editapplication.sectionData = function(s) {
			return this.data[s];
		};
		this.editapplication.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.artclub.fileHistory', 'args':{'business_id':M.curBusinessID, 
				'file_id':this.file_id, 'field':i}};
		};
		this.editapplication.addButton('save', 'Save', 'M.ciniki_artclub_info.saveApplication();');
		this.editapplication.addClose('Cancel');
	}

	this.start = function(cb, appPrefix, aG) {
		args = {};
		if( aG != null ) {
			args = eval(aG);
		}

		//
		// Create container
		//
		var appContainer = M.createContainer(appPrefix, 'ciniki_artclub_info', 'yes');
		if( appContainer == null ) {
			alert('App Error');
			return false;
		}

		this.showMenu(cb);
	}

	this.showMenu = function(cb) {
		this.menu.refresh();
		this.menu.show(cb);
	};

	this.showMembership = function(cb) {
		this.membership.data = {};
		var rsp = M.api.getJSONCb('ciniki.artclub.settingsGet', 
			{'business_id':M.curBusinessID, 'processhtml':'yes'}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				if( rsp.settings != null && rsp.settings['membership-details'] != null ) {
					M.ciniki_artclub_info.membership.data['membership-details-html'] = rsp.settings['membership-details-html'];
				} else {
					M.ciniki_artclub_info.membership.data['membership-details-html'] = '';
				}
				var rsp = M.api.getJSONCb('ciniki.artclub.fileList', 
					{'business_id':M.curBusinessID, 'type':'1'}, function(rsp) {
						if( rsp.stat != 'ok' ) {
							M.api.err(rsp);
							return false;
						}
						M.ciniki_artclub_info.membership.data['applications'] = rsp.files;
						M.ciniki_artclub_info.membership.refresh();
						M.ciniki_artclub_info.membership.show(cb);
					});
			});
	};

	this.showEditMembership = function(cb) {
		this.editmembership.data = {};
		var rsp = M.api.getJSONCb('ciniki.artclub.settingsGet', 
			{'business_id':M.curBusinessID}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				if( rsp.settings != null && rsp.settings['membership-details'] != null ) {
					M.ciniki_artclub_info.editmembership.data['membership-details'] = rsp.settings['membership-details'];
				} else {
					M.ciniki_artclub_info.editmembership.data['membership-details'] = '';
				}
				M.ciniki_artclub_info.editmembership.refresh();
				M.ciniki_artclub_info.editmembership.show(cb);
			});
	};

	this.saveMembership = function() {
		var c = this.editmembership.serializeFormData('no');
		if( c != null ) {
			var rsp = M.api.postJSONFormData('ciniki.artclub.settingsUpdate', 
				{'business_id':M.curBusinessID}, c,
				function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					} else {
						M.ciniki_artclub_info.editmembership.close();
					}
				});
		} else {
			M.ciniki_artclub_info.editmembership.close();
		}
	};

	this.showAddApplication = function(cb) {
		this.addapplication.reset();
		this.addapplication.data = {'type':1};
		this.addapplication.refresh();
		this.addapplication.show(cb);
	};

	this.addApplication = function() {
		var c = this.addapplication.serializeFormData('yes');

		if( c != '' ) {
			var rsp = M.api.postJSONFormData('ciniki.artclub.fileAdd', 
				{'business_id':M.curBusinessID, 'type':1}, c,
					function(rsp) {
						if( rsp.stat != 'ok' ) {
							M.api.err(rsp);
							return false;
						} else {
							M.ciniki_artclub_info.addapplication.close();
						}
					});
		} else {
			M.ciniki_artclub_info.addapplication.close();
		}
	};

	this.showEditApplication = function(cb, fid) {
		if( fid != null ) {
			this.editapplication.file_id = fid;
		}
		var rsp = M.api.getJSONCb('ciniki.artclub.fileGet', 
			{'business_id':M.curBusinessID, 'file_id':this.editapplication.file_id}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				M.ciniki_artclub_info.editapplication.data = rsp.file;
				M.ciniki_artclub_info.editapplication.refresh();
				M.ciniki_artclub_info.editapplication.show(cb);
			});
	};

	this.saveApplication = function() {
		var c = this.editapplication.serializeFormData('no');

		if( c != '' ) {
			var rsp = M.api.postJSONFormData('ciniki.artclub.fileUpdate', 
				{'business_id':M.curBusinessID, 'file_id':this.editapplication.file_id}, c,
					function(rsp) {
						if( rsp.stat != 'ok' ) {
							M.api.err(rsp);
							return false;
						} else {
							M.ciniki_artclub_info.editapplication.close();
						}
					});
		}
	};

	this.deleteApplication = function() {
		if( confirm('Are you sure you want to delete \'' + this.editapplication.data.name + '\'?  All information about it will be removed and unrecoverable.') ) {
			var rsp = M.api.getJSONCb('ciniki.artclub.fileDelete', {'business_id':M.curBusinessID, 
				'file_id':M.ciniki_artclub_info.editapplication.file_id}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					} else {
						M.ciniki_artclub_info.editapplication.close();
					}
				});
		}
	};

	this.downloadFile = function(fid) {
		window.open(M.api.getUploadURL('ciniki.artclub.fileDownload', {'business_id':M.curBusinessID, 'file_id':fid}));
	};
}
