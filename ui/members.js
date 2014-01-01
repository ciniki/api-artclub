//
// The members app to manage members for an artclub
//
function ciniki_artclub_members() {
	this.webFlags = {'1':{'name':'Hidden'}};
	this.init = function() {
		//
		// Setup the main panel to list the members 
		//
		this.menu = new M.panel('Members',
			'ciniki_artclub_members', 'menu',
			'mc', 'medium', 'sectioned', 'ciniki.artclub.members.menu');
		this.menu.data = {};
		this.menu.sections = {
			'_':{'label':'', 'type':'simplegrid', 'num_cols':1,
				'headerValues':null,
				'cellClasses':['multiline'],
				'noData':'No members',
				'addTxt':'Add Member',
				'addFn':'M.ciniki_artclub_members.showEdit(\'M.ciniki_artclub_members.showMenu();\',0);',
				},
			};
		this.menu.sectionData = function(s) { return this.data; }
		this.menu.cellValue = function(s, i, j, d) {
			if( d.member.company != null && d.member.company != '' ) {
				return '<span class="maintext">' + d.member.first + ' ' + d.member.last + '</span><span class="subtext">' + d.member.company + '</span>';
			} 
			return '<span class="maintext">' + d.member.first + ' ' + d.member.last + '</span>';
		};
		this.menu.rowFn = function(s, i, d) { 
			return 'M.ciniki_artclub_members.showMember(\'M.ciniki_artclub_members.showMenu();\',\'' + d.member.id + '\');'; 
		};
		this.menu.addButton('add', 'Add', 'M.ciniki_artclub_members.showEdit(\'M.ciniki_artclub_members.showMenu();\',0);');
		this.menu.addClose('Back');

		//
		// The member panel will show the information for a member/sponsor/organizer
		//
		this.member = new M.panel('Member',
			'ciniki_artclub_members', 'member',
			'mc', 'medium mediumaside', 'sectioned', 'ciniki.artclub.members.member');
		this.member.data = {};
		this.member.member_id = 0;
		this.member.sections = {
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
			'description':{'label':'Bio', 'type':'htmlcontent'},
			'notes':{'label':'Notes', 'type':'htmlcontent'},
			'images':{'label':'Gallery', 'type':'simplethumbs'},
			'_images':{'label':'', 'type':'simplegrid', 'num_cols':1,
				'addTxt':'Add Image',
				'addFn':'M.startApp(\'ciniki.artclub.memberimages\',null,\'M.ciniki_artclub_members.showMember();\',\'mc\',{\'member_id\':M.ciniki_artclub_members.member.member_id,\'add\':\'yes\'});',
				},
			'_buttons':{'label':'', 'buttons':{
				'edit':{'label':'Edit', 'fn':'M.ciniki_artclub_members.showEdit(\'M.ciniki_artclub_members.showMember();\',M.ciniki_artclub_members.member.member_id);'},
				}},
		};
		this.member.sectionData = function(s) {
			if( s == 'images' ) { return this.data.images; }
			if( s == 'short_description' || s == 'description' || s == 'notes' ) { return this.data[s]; }
			return this.sections[s].list;
			};
		this.member.listLabel = function(s, i, d) {
			if( s == 'info' ) { 
				return d.label; 
			}
			return null;
		};
		this.member.listValue = function(s, i, d) {
			if( i == 'url' && this.data[i] != '' ) {
				return '<a target="_blank" href="http://' + this.data[i] + '">' + this.data[i] + '</a>';
			}
			if( i == 'name' ) {
				return this.data.first + ' '  + this.data.last;
			}
			return this.data[i];
		};
		this.member.fieldValue = function(s, i, d) {
			if( i == 'description' || i == 'notes' ) { 
				return this.data[i].replace(/\n/g, '<br/>');
			}
			return this.data[i];
		};
		this.member.cellValue = function(s, i, j, d) {
			if( s == 'images' && j == 0 ) { 
				if( d.image.image_id > 0 ) {
					if( d.image.image_data != null && d.image.image_data != '' ) {
						return '<img width="75px" height="75px" src=\'' + d.image.image_data + '\' />'; 
					} else {
						return '<img width="75px" height="75px" src=\'' + M.api.getBinaryURL('ciniki.artclub.getImage', {'business_id':M.curBusinessID, 'image_id':d.image.image_id, 'version':'thumbnail', 'maxwidth':'75'}) + '\' />'; 
					}
				} else {
					return '<img width="75px" height="75px" src=\'/ciniki-manage-themes/default/img/noimage_75.jpg\' />';
				}
			}
			if( s == 'images' && j == 1 ) { 
				return '<span class="maintext">' + d.image.name + '</span><span class="subtext">' + d.image.description + '</span>'; 
			}
		};
		this.member.rowFn = function(s, i, d) {
			if( s == 'images' ) {
				return 'M.startApp(\'ciniki.artclub.memberimages\',null,\'M.ciniki_artclub_members.showMember();\',\'mc\',{\'member_image_id\':\'' + d.image.id + '\'});';
			}
		};
		this.member.thumbSrc = function(s, i, d) {
			if( d.image.image_data != null && d.image.image_data != '' ) {
				return d.image.image_data;
			} else {
				return '/ciniki-manage-themes/default/img/noimage_75.jpg';
			}
		};
		this.member.thumbTitle = function(s, i, d) {
			if( d.image.name != null ) { return d.image.name; }
			return '';
		};
		this.member.thumbID = function(s, i, d) {
			if( d.image.id != null ) { return d.image.id; }
			return 0;
		};
		this.member.thumbFn = function(s, i, d) {
			return 'M.startApp(\'ciniki.artclub.memberimages\',null,\'M.ciniki_artclub_members.showMember();\',\'mc\',{\'member_image_id\':\'' + d.image.id + '\'});';
		};
		this.member.addDropImage = function(iid) {
			var rsp = M.api.getJSON('ciniki.artclub.memberImageAdd',
				{'business_id':M.curBusinessID, 'image_id':iid,
					'member_id':M.ciniki_artclub_members.member.member_id});
			if( rsp.stat != 'ok' ) {
				M.api.err(rsp);
				return false;
			}
			return true;
		};
		this.member.addDropImageRefresh = function() {
			if( M.ciniki_artclub_members.member.member_id > 0 ) {
				var rsp = M.api.getJSONCb('ciniki.artclub.memberGet', {'business_id':M.curBusinessID, 
					'member_id':M.ciniki_artclub_members.member.member_id, 'images':'yes'}, function(rsp) {
						if( rsp.stat != 'ok' ) {
							M.api.err(rsp);
							return false;
						}
						M.ciniki_artclub_members.member.data.images = rsp.member.images;
						M.ciniki_artclub_members.member.refreshSection('images');
					});
			}
		};
		this.member.addButton('edit', 'Edit', 'M.ciniki_artclub_members.showEdit(\'M.ciniki_artclub_members.showMember();\',M.ciniki_artclub_members.member.member_id);');
		this.member.addClose('Back');

		//
		// The edit panel for member
		//
		this.edit = new M.panel('Edit',
			'ciniki_artclub_members', 'edit',
			'mc', 'medium mediumaside', 'sectioned', 'ciniki.artclub.members.edit');
		this.edit.data = {};
		this.edit.member_id = 0;
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
			'_description':{'label':'Bio', 'fields':{
				'description':{'label':'', 'hidelabel':'yes', 'type':'textarea'},
				}},
			'_notes':{'label':'Notes', 'fields':{
				'notes':{'label':'', 'hidelabel':'yes', 'type':'textarea'},
				}},
			'_buttons':{'label':'', 'buttons':{
				'save':{'label':'Save', 'fn':'M.ciniki_artclub_members.saveMember();'},
				'delete':{'label':'Delete', 'fn':'M.ciniki_artclub_members.deleteMember();'},
				}},
		};
		this.edit.fieldValue = function(s, i, d) {
			if( this.data[i] != null ) { return this.data[i]; }
			return '';
		};
		this.edit.fieldHistoryArgs = function(s, i) {
			return {'method':'ciniki.artclub.memberHistory', 'args':{'business_id':M.curBusinessID, 
				'member_id':M.ciniki_artclub_members.edit.member_id, 'field':i}};
		};
		this.edit.addDropImage = function(iid) {
			M.ciniki_artclub_members.edit.setFieldValue('primary_image_id', iid);
			return true;
		};
		this.edit.deleteImage = function(fid) {
			this.setFieldValue(fid, 0);
			return true;
		};
		this.edit.addButton('save', 'Save', 'M.ciniki_artclub_members.saveMember();');
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
		var appContainer = M.createContainer(appPrefix, 'ciniki_artclub_members', 'yes');
		if( appContainer == null ) {
			alert('App Error');
			return false;
		}
	
		this.showMenu(cb);
	}

	this.showMenu = function(cb) {
		// Get the list of existing artclub
		var rsp = M.api.getJSONCb('ciniki.artclub.memberList', 
			{'business_id':M.curBusinessID}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				M.ciniki_artclub_members.menu.data = rsp.members;
				M.ciniki_artclub_members.menu.refresh();
				M.ciniki_artclub_members.menu.show(cb);
			});	
	};

	//
	// The edit form takes care of editing existing, or add new.
	// It can also be used to add the same person to an artclub
	// as an member and sponsor and organizer, etc.
	//
	this.showEdit = function(cb, mid) {
		if( mid != null ) {
			this.edit.member_id = mid;
		}
		if( this.edit.member_id > 0 ) {
			var rsp = M.api.getJSONCb('ciniki.artclub.memberGet',
				{'business_id':M.curBusinessID, 'member_id':this.edit.member_id}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					M.ciniki_artclub_members.edit.data = rsp.member;
					M.ciniki_artclub_members.edit.refresh();
					M.ciniki_artclub_members.edit.show(cb);
				});
		} else {
			this.edit.data = {};
			this.edit.refresh();
			this.edit.show(cb);
		}
	};

	this.showMember = function(cb, mid) {
		if( mid != null ) {
			this.member.member_id = mid;
		}
		var rsp = M.api.getJSONCb('ciniki.artclub.memberGet',
			{'business_id':M.curBusinessID, 'member_id':this.member.member_id, 'images':'yes'}, function(rsp) {
				if( rsp.stat != 'ok' ) {
					M.api.err(rsp);
					return false;
				}
				var p = M.ciniki_artclub_members.member;
				p.data = rsp.member;

				if( rsp.member.notes != null && rsp.member.notes != '' ) {
					p.sections.notes.visible = 'yes';
				} else {
					p.sections.notes.visible = 'no';
				}
				if( rsp.member.description != null && rsp.member.description != '' ) {
					p.sections.description.visible = 'yes';
				} else {
					p.sections.description.visible = 'no';
				}
				if( rsp.member.short_description != null && rsp.member.short_description != '' ) {
					p.sections.short_description.visible = 'yes';
				} else {
					p.sections.short_description.visible = 'no';
				}

				var fields = ['company','email','phone_home','phone_work','phone_cell','phone_fax','url'];
				for(i in fields) {
					if( rsp.member[fields[i]] != null && rsp.member[fields[i]] != '' ) {
						p.sections.info.list[fields[i]].visible = 'yes';
					} else {
						p.sections.info.list[fields[i]].visible = 'no';
					}
				}
				p.refresh();
				p.show(cb);
			});
	};

	this.saveMember = function() {
		//
		// Depending on if there was a contact loaded, or member
		// loaded for editing, determine what should be sent back
		// to the server
		//
		if( this.edit.member_id > 0 ) {
			// Update contact
			var c = this.edit.serializeForm('no');
			if( c != '' ) {
				var rsp = M.api.postJSONCb('ciniki.artclub.memberUpdate', 
					{'business_id':M.curBusinessID, 'member_id':this.edit.member_id}, c, function(rsp) {
						if( rsp.stat != 'ok' ) {
							M.api.err(rsp);
							return false;
						} 
						M.ciniki_artclub_members.edit.close();
					});
			} else {
				M.ciniki_artclub_members.edit.close();
			}
		} else {
			// Add contact
			var c = this.edit.serializeForm('yes');
			var rsp = M.api.postJSONCb('ciniki.artclub.memberAdd', 
				{'business_id':M.curBusinessID}, c, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					} 
					M.ciniki_artclub_members.edit.close();
				});
		}
	};

	this.deleteMember = function() {
		if( confirm('Are you sure you want to delete this member and all their photos?') ) {
			var rsp = M.api.getJSONCb('ciniki.artclub.memberDelete', {'business_id':M.curBusinessID, 
				'member_id':this.edit.member_id}, function(rsp) {
					if( rsp.stat != 'ok' ) {
						M.api.err(rsp);
						return false;
					}
					M.ciniki_artclub_members.member.close();
					M.ciniki_artclub_members.edit.reset();
				});
		}
	};
}
