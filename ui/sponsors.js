//
// The sponsors app to manage sponsors for an artclub
//
function ciniki_artclub_sponsors() {
	this.webFlags = {'1':{'name':'Hidden'}};
	this.init = function() {
		//
		// Setup the main panel to list the sponsors 
		//
		this.menu = new M.panel('Sponsors',
			'ciniki_artclub_sponsors', 'menu',
			'mc', 'medium', 'sectioned', 'ciniki.artclub.sponsors.menu');
		this.menu.data = {};
		this.menu.sections = {
			'_':{'label':'', 'type':'simplegrid', 'num_cols':1,
				'headerValues':null,
				'cellClasses':['multiline'],
				'noData':'No sponsors',
				'addTxt':'Add Sponsor',
				'addFn':'M.ciniki_artclub_sponsors.showEdit(\'M.ciniki_artclub_sponsors.showMenu();\',0);',
				},
			};
		this.menu.sectionData = function(s) { return this.data; }
		this.menu.cellValue = function(s, i, j, d) {
			if( d.sponsor.company != null && d.sponsor.company != '' ) {
				return '<span class="maintext">' + d.sponsor.first + ' ' + d.sponsor.last + '</span><span class="subtext">' + d.sponsor.company + '</span>';
			} 
			return '<span class="maintext">' + d.sponsor.first + ' ' + d.sponsor.last + '</span>';
		};
		this.menu.rowFn = function(s, i, d) { 
			return 'M.ciniki_artclub_sponsors.showSponsor(\'M.ciniki_artclub_sponsors.showMenu();\',\'' + d.sponsor.id + '\');'; 
		};
		this.menu.addButton('add', 'Add', 'M.ciniki_artclub_sponsors.showEdit(\'M.ciniki_artclub_sponsors.showMenu();\',0);');
		this.menu.addClose('Back');

		//
		// The sponsor panel will show the information for a sponsor/sponsor/organizer
		//
		this.sponsor = new M.panel('Sponsor',
			'ciniki_artclub_sponsors', 'sponsor',
			'mc', 'medium mediumaside', 'sectioned', 'ciniki.artclub.sponsors.sponsor');
		this.sponsor.data = {};
		this.sponsor.sponsor_id = 0;
		this.sponsor.sections = {
			'_image':{'label':'', 'aside':'yes', 'fields':{
				'primary_image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'history':'no'},
				}},
			'info':{'label':'', 'list':{
				'name':{'label':'Name'},
				'company':{'label':'Company', 'visible':'no'},
				'email':{'label':'Email', 'visible':'no'},
				'phone_home':{'label':'Home Phone', 'visible':'no'},
				'phone_work':{'label':'Work Phone', 'visible':'no'},
				'phone_cell':{'label':'Cell Phone', 'visible':'no'},
				'phone_fax':{'label':'Fax', 'visible':'no'},
				'url':{'label':'Website', 'visible':'no'},
				'webvisible':{'label':'Web Settings'},
				}},
			'short_description':{'label':'Brief Description', 'type':'htmlcontent'},
			'notes':{'label':'Notes', 'type':'htmlcontent'},
			'_buttons':{'label':'', 'buttons':{
				'edit':{'label':'Edit', 'fn':'M.ciniki_artclub_sponsors.showEdit(\'M.ciniki_artclub_sponsors.showSponsor();\',M.ciniki_artclub_sponsors.sponsor.sponsor_id);'},
				}},
		};
		this.sponsor.sectionData = function(s) {
			if( s == 'short_description' || s == 'notes' ) { return this.data[s]; }
			return this.sections[s].list;
			};
		this.sponsor.listLabel = function(s, i, d) {
			if( s == 'info' ) { 
				return d.label; 
			}
			return null;
		};
		this.sponsor.listValue = function(s, i, d) {
			if( i == 'url' && this.data[i] != '' ) {
				return '<a target="_blank" href="http://' + this.data[i] + '">' + this.data[i] + '</a>';
			}
			if( i == 'name' ) {
				return this.data.first + ' '  + this.data.last;
			}
			return this.data[i];
		};
		this.sponsor.fieldValue = function(s, i, d) {
			if( i == 'description' || i == 'notes' ) { 
				return this.data[i].replace(/\n/g, '<br/>');
			}
			return this.data[i];
		};
		this.sponsor.addButton('edit', 'Edit', 'M.ciniki_artclub_sponsors.showEdit(\'M.ciniki_artclub_sponsors.showSponsor();\',M.ciniki_artclub_sponsors.sponsor.sponsor_id);');
		this.sponsor.addClose('Back');

		//
		// The edit panel for sponsor
		//
		this.edit = new M.panel('Edit',
			'ciniki_artclub_sponsors', 'edit',
			'mc', 'medium mediumaside', 'sectioned', 'ciniki.artclub.sponsors.edit');
		this.edit.data = {};
		this.edit.sponsor_id = 0;
		this.edit.sections = {
			'_image':{'label':'', 'aside':'yes', 'fields':{
				'primary_image_id':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no'},
			}},
			'name':{'label':'', 'fields':{
				'first':{'label':'First Name', 'type':'text'},
				'last':{'label':'Last Name', 'type':'text'},
				'company':{'label':'Business', 'type':'text'},
				'webflags':{'label':'Website', 'type':'flags', 'toggle':'no', 'join':'yes', 'flags':this.webFlags},
				}},
			'contact':{'label':'Contact Info', 'fields':{
				'email':{'label':'Email', 'type':'text'},
				'phone_home':{'label':'Home Phone', 'type':'text'},
				'phone_work':{'label':'Work Phone', 'type':'text'},
				'phone_cell':{'label':'Cell Phone', 'type':'text'},
				'phone_fax':{'label':'Fax Phone', 'type':'text'},
				'url':{'label':'Website', 'type':'text'},
				}},
			'_short_description':{'label':'Brief Description', 'fields':{
				'short_description':{'label':'', 'hidelabel':'yes', 'size':'small', 'type':'textarea'},
				}},
//			'_description':{'label':'Bio', 'fields':{
//				'description':{'label':'', 'hidelabel':'yes', 'type':'textarea'},
//				}},
			'_notes':{'label':'Notes', 'fields':{
				'notes':{'label':'', 'hidelabel':'yes', 'type':'textarea'},
				}},
			'_buttons':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_artclub_sponsors.saveSponsor();'},
				'delete':{'label':'Delete', 'fn':'M.ciniki_artclub_sponsors.deleteSponsor();'},
				}},
		};
		this.edit.fieldValue = function(s, i, d) {
			if( this.data[i] != null ) { return this.data[i]; }
			return '';
		};
		this.edit.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.artclub.sponsorHistory', 'args':{'business_id':M.curBusinessID, 
				'sponsor_id':this.sponsor_id, 'field':i}};
		};
		this.edit.addDropImage = function(iid) {
			M.ciniki_artclub_sponsors.edit.setFieldValue('primary_image_id', iid);
			return true;
		};
		this.edit.deleteImage = function(fid) {
			this.setFieldValue(fid, 0);
			return true;
		};
		this.edit.addButton('save', 'Save', 'M.ciniki_artclub_sponsors.saveSponsor();');
		this.edit.addClose('Cancel');
	}
	
	this.start = function(cb, appPrefix, aG) {
		args = {};
		if( aG != null ) {
			args = eval(aG);
		}

		//
		// Create container
		//
		var appContainer = M.createContainer(appPrefix, 'ciniki_artclub_sponsors', 'yes');
		if( appContainer == null ) {
			alert('App Error');
			return false;
		}
	
		this.showMenu(cb);
	}

	this.showMenu = function(cb) {
		// Get the list of existing artclub
		var rsp = M.api.getJSONCb('ciniki.artclub.sponsorList', 
			{'business_id':M.curBusinessID}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				M.ciniki_artclub_sponsors.menu.data = rsp.sponsors;
				M.ciniki_artclub_sponsors.menu.refresh();
				M.ciniki_artclub_sponsors.menu.show(cb);
			});	
	};

	//
	// The edit form takes care of editing existing, or add new.
	// It can also be used to add the same person to an artclub
	// as an sponsor and sponsor and organizer, etc.
	//
	this.showEdit = function(cb, mid) {
		if( mid != null ) {
			this.edit.sponsor_id = mid;
		}
		if( this.edit.sponsor_id > 0 ) {
			var rsp = M.api.getJSONCb('ciniki.artclub.sponsorGet',
				{'business_id':M.curBusinessID, 'sponsor_id':this.edit.sponsor_id}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					M.ciniki_artclub_sponsors.edit.data = rsp.sponsor;
					M.ciniki_artclub_sponsors.edit.refresh();
					M.ciniki_artclub_sponsors.edit.show(cb);
				});
		} else {
			this.edit.reset();
			this.edit.data = {};
			this.edit.refresh();
			this.edit.show(cb);
		}
	};

	this.showSponsor = function(cb, mid) {
		if( mid != null ) {
			this.sponsor.sponsor_id = mid;
		}
		var rsp = M.api.getJSONCb('ciniki.artclub.sponsorGet',
			{'business_id':M.curBusinessID, 'sponsor_id':this.sponsor.sponsor_id, 'images':'yes'}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				var p = M.ciniki_artclub_sponsors.sponsor;
				p.data = rsp.sponsor;

				if( rsp.sponsor.notes != null && rsp.sponsor.notes != '' ) {
					p.sections.notes.visible = 'yes';
				} else {
					p.sections.notes.visible = 'no';
				}
				if( rsp.sponsor.short_description != null && rsp.sponsor.short_description != '' ) {
					p.sections.short_description.visible = 'yes';
				} else {
					p.sections.short_description.visible = 'no';
				}

				var fields = ['company','email','phone_home','phone_work','phone_cell','phone_fax','url'];
				for(i in fields) {
					if( rsp.sponsor[fields[i]] != null && rsp.sponsor[fields[i]] != '' ) {
						p.sections.info.list[fields[i]].visible = 'yes';
					} else {
						p.sections.info.list[fields[i]].visible = 'no';
					}
				}
				p.refresh();
				p.show(cb);
			});
	};

	this.saveSponsor = function() {
		//
		// Depending on if there was a contact loaded, or sponsor
		// loaded for editing, determine what should be sent back
		// to the server
		//
		if( this.edit.sponsor_id > 0 ) {
			// Update contact
			var c = this.edit.serializeForm('no');
			if( c != '' ) {
				var rsp = M.api.postJSONCb('ciniki.artclub.sponsorUpdate', 
					{'business_id':M.curBusinessID, 'sponsor_id':this.edit.sponsor_id}, c, function(rsp) {
						if( rsp.stat != 'ok' ) {
							M.api.err(rsp);
							return false;
						} 
					M.ciniki_artclub_sponsors.edit.close();
					});
			} else {
				M.ciniki_artclub_sponsors.edit.close();
			}
		} else {
			// Add contact
			var c = this.edit.serializeForm('yes');
			var rsp = M.api.postJSONCb('ciniki.artclub.sponsorAdd', 
				{'business_id':M.curBusinessID}, c, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					} 
					M.ciniki_artclub_sponsors.edit.close();
				});
		}
	};

	this.deleteSponsor = function() {
		if( confirm('Are you sure you want to delete this sponsor?') ) {
			var rsp = M.api.getJSONCb('ciniki.artclub.sponsorDelete', {'business_id':M.curBusinessID, 
				'sponsor_id':this.edit.sponsor_id}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					M.ciniki_artclub_sponsors.sponsor.close();
				});
		}
	};
}
