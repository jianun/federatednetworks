/************************************************/
/* IPB3 Javascript								*/
/* -------------------------------------------- */
/* ips.blog.js - Blog javascript				*/
/* (c) IPS, Inc 2008							*/
/* -------------------------------------------- */
/* Author: Rikki Tissier						*/
/************************************************/

var _blog = window.IPBoard;

_blog.prototype.blog = {
	
	cblocks: {},
	canPostDraft: 1,
	canPublish: 1,
	defStatusGlobal: 'published',
	defStatus: 'published',
	_updating: 0,
	currentTags: {},
	currentCats: {},
	_newCats: $H(),
	goComments: 0,
	maxCats: 0,
	updateLeft: false,
	updateRight: false,
	cton: false,
	// Properties for sortable
	props:  { 	tag: 'div', 				only: 'cblock_drag',
	 			handle: 'draggable', 		containment: ['cblock_left', 'cblock_right'],
	 			constraint: '', 			dropOnEmpty: true,
	 		 	hoverclass: 'over'
	 		},
	popups: {},
	cp1: null,
	
	
	/*------------------------------*/
	/* Constructor 					*/
	init: function()
	{
		Debug.write("Initializing ips.blog.js");
		
		document.observe("dom:loaded", function(){
			if( ipb.blog.inEntry && ipb.blog.ownerID == ipb.vars['member_id'] && ipb.blog.withBlocks )
			{
				ipb.blog.setUpDrags();
				ipb.blog.setUpCloseLinks();
				
				if( $('change_header') )
				{
					$('change_header').observe('click', ipb.blog.changeHeader);
				}
				
				if( $('add_theme') )
				{
					$('add_theme').observe('click', ipb.blog.changeTheme);
				}
			}
			
			// Resize images
			$$('.entry', '.poll').each( function(elem){
				ipb.global.findImgs( $( elem ) );
			});
			
			ipb.delegate.register('a[rel="bookmark"]', ipb.blog.showLinkToEntry );
			ipb.delegate.register('.delete_entry', ipb.blog.deleteEntry);
			ipb.delegate.register('.delete_comment', ipb.blog.deleteComment );
			
			/* Jump to comments? */
			if ( ipb.blog.goComments )
			{
				$('comments').scrollTo();
			}
		});
	},
	
	/* INIT recent items menu */
	setUpRecentMenu: function()
	{
		Debug.write('setting up menu' );
		ipb.delegate.register(".__rmenu", ipb.blog.recentMenu);
	},
	
	recentMenu: function( e, elem )
	{
		Event.stop(e);
		
		var action = $( elem ).className.match('__x([a-z]+)');
		if( action == null || Object.isUndefined( action[1] ) ){ Debug.error("Error showing popup"); return; }
		
		var newTitle = $( elem ).innerHTML;
		var url = ipb.vars['base_url'] + "app=blog&amp;module=ajax&amp;section=sidebar&amp;do=" + action[1] + '&md5check=' + ipb.vars['secure_hash'];
		
		new Ajax.Request( url.replace(/&amp;/g, '&'),
						{
							method: 'get',
							evalJSON: 'force',
							onSuccess: function(t)
							{
								$('recentajaxcontent').update( t.responseText );
								$('ratitle').update( newTitle );
							}
						});
		
	},
	
	/* INIT Comment tracker button */
	setUpCommentNotification: function( memberId, trackerId, entryId )
	{
		if ( memberId )
		{
			if ( trackerId )
			{
				$('__commentTrack_on').hide();
				$('__commentTrack_off').show();
				
				ipb.blog.cton = true;
			}
			else
			{
				$('__commentTrack_on').show();
				$('__commentTrack_off').hide();
				
				ipb.blog.cton = false;
			}
			
			$('__commentTrack_off').observe('click', ipb.blog.fireCommentTrackToggle.bindAsEventListener( this, entryId ) );
			$('__commentTrack_on').observe('click', ipb.blog.fireCommentTrackToggle.bindAsEventListener( this, entryId ) );
		}
	},
	
	fireCommentTrackToggle: function( e, entryId )
	{
		Event.stop(e);
		ipb.blog.cton = ( ipb.blog.cton ) ? false : true;
		
		var url = ipb.vars['base_url'] + "app=blog&amp;module=ajax&amp;section=comments&do=toggleTracker&blogid=" + ipb.blog.blogID + "&entry_id=" + entryId + "&md5check=" + ipb.vars['secure_hash'];
		
		new Ajax.Request( url.replace(/&amp;/g, '&'),
						{
							method: 'get',
							evalJSON: 'force',
							onSuccess: function(t)
							{
								if( !Object.isUndefined( t.responseJSON ) )
								{
									alert( ipb.lang['action_failed'] + ": " + t.responseJSON['error'] );
									return;
								}
								
								if ( ipb.blog.cton )
								{
									$('__commentTrack_on').hide();
									$('__commentTrack_off').show();
								}
								else
								{
									$('__commentTrack_on').show();
									$('__commentTrack_off').hide();
								}
							}
						});
	},
	
	/* INIT blogs as table */
	setUpBlogsAsTable: function()
	{
		Debug.write('setting up popups' );
		ipb.delegate.register(".__preview", ipb.blog.previewPopup);
	},
	
	previewPopup: function( e, elem )
	{
		Event.stop(e);
		
		var sourceid = elem.identify();
		var preview = $( elem ).className.match('__id([0-9]+)');

		if( preview == null || Object.isUndefined( preview[1] ) ){ Debug.error("Error showing popup"); return; }
		var popid = 'popup_' + preview[1] + '_preview';
		
		ipb.namePops[ preview ]	 = new ipb.Popup( popid, {
			 												type: 'balloon',
			 												initial: $( '__preview_content' + preview[1] ).innerHTML,
			 												stem: true,
															hideAtStart: false,
			 												attach: { target: elem, position: 'auto' },
			 												w: '400px'
														});
	},
	
	/* FORM STUFFS */
	
	/**
	* Form: Show recent tags
	*/
	formTogglePopularTags: function( e )
	{
		Event.stop(e);
		
		$('tags_container').show();
		$('tagsToggle').hide();
		
		/* Parse tags */
		ipb.blog.currentTags.each( function( t )
		{
			var tid = t.key;
			var tag = t.value;
			
			var html = ipb.templates['tag_entry'].evaluate( { 'tid': tid, 'tag': tag } );
			$('tags_container').insert( html );
			
			$('tag_' + tid + '_wrap').observe('click', ipb.blog.formAddTag.bindAsEventListener( this, tid ) );
		} );
	},
	
	/**
	 * Add a tag into the field
	 */
	formAddTag: function( e, tid )
	{
		Event.stop(e);
		
		var tag = ipb.blog.currentTags.get( tid );
		
		if ( tag )
		{
			$('blogTags').value += ',' + tag;
		}
		
		$('blogTags').value = $('blogTags').value.replace( new RegExp( /^,(\s+?)?/ ), '' );
	},
	
	/**
	 * Init MEOW
	 */
	formInitCats: function()
	{
		var _c = 0;
		
		$('formCats').update('');
		
		/* Display new cats first, that's the LAW */
		if ( ipb.blog._newCats.size() )
		{
			ipb.blog._newCats.each( function( c )
			{
				var html = ipb.templates['cat_entry'].evaluate( { 'cid': c.key, 'cat': c.value['category_title'] } );
				$('formCats').insert( html );
				
				$('cat_' + c.key).checked = ( c.value['_selected'] == 1 ) ? true : false;
				$('cat_' + c.key).observe('click', ipb.blog.formCheckboxClicked.bindAsEventListener( this, c.key ) );
				
				_c++;
			} );
		}
	
		if ( ipb.blog.currentCats.size() )
		{
			ipb.blog.currentCats.each( function( c )
			{
				var html = ipb.templates['cat_entry'].evaluate( { 'cid': c.key, 'cat': c.value['category_title'] } );
				$('formCats').insert( html );
				
				$('cat_' + c.key ).checked = ( c.value['_selected'] == 1 ) ? true : false;
				$('cat_' + c.key).observe('click', ipb.blog.formCheckboxClicked.bindAsEventListener( this, c.key ) );
				
				_c++;
			} );
		}
		
		/* Add overflow */
		if ( _c >= 6 )
		{
			$('formCats').className = 'formCatsList';
		}
		
		/* Max cats reached */
		if ( _c >= ipb.blog.maxCats )
		{
			$('categoryAddToggle').hide();
		}
	},
	
	/**
	 * Check box handler
	 */
	formCheckboxClicked: function( e, key )
	{
		if ( key.match( /catNew/ ) )
		{
			var c = ipb.blog._newCats.get( key );
		
			ipb.blog._newCats.set( key, {'_selected' : ( $('cat_' + key).checked ) ? 1 : 0 , 'category_title': c.category_title } );
		}
		else
		{
			var c = ipb.blog.currentCats.get( key );
			
			ipb.blog.currentCats.set( key, {'_selected' : ( $('cat_' + key).checked ) ? 1 : 0 , 'category_title': c.category_title } );
		}
	},
	
	/**
	 * Add meow
	 */
	formAddCat: function( e )
	{
		Event.stop(e);
		
		var _go = true;
		
		if ( $F('formCatAddInput') )
		{
			/* Already got a meow by this name? */
			if ( ipb.blog.currentCats.size() )
			{
				ipb.blog.currentCats.each( function( c )
				{
					if ( c.value['category_title'] == $F('formCatAddInput') )
					{
						alert( ipb.lang['blog_cat_exists'] );
						
						$('formCatAddInput').value = '';
						var _go = false;
					}
				} );
			}
			
			if ( ipb.blog._newCats.size() )
			{
				ipb.blog._newCats.each( function( t )
				{
					if ( t.value == $F('formCatAddInput') )
					{
						alert( ipb.lang['blog_cat_exists'] );
						
						$('formCatAddInput').value = '';
						var _go = false;
					}
				} );
			}
			
			if ( _go == true )
			{
				var _id   = 'catNew-' + parseInt( ipb.blog._newCats.size() + 1 );
				var _name = $F('formCatAddInput');
				
				ipb.blog._newCats.set( _id, { 'category_title' : _name, '_selected' : 1 } );
				
				$('formCatAddInput').value = '';
				
				ipb.blog.formInitCats();
			}
		}
	},
	
	/* Form INIT */
	initPostForm: function()
	{
		$('bf_timeToggle').observe('click', ipb.blog.pfTimeToggle.bindAsEventListener( this ) );
		$('bf_timeCancel').observe('click', ipb.blog.pfTimeCancel.bindAsEventListener( this ) );
		
		$('bf_publish').observe('click', ipb.blog.pfTriggerSubmit.bindAsEventListener( this, 'publish' ) );
		$('bf_draft').observe('click', ipb.blog.pfTriggerSubmit.bindAsEventListener( this, 'draft' ) );
		
		ipb.blog.defStatus = ( ! ipb.blog.defStatus ) ? ipb.blog.defStatusGlobal : ipb.blog.defStatus;
		
		if ( ! ipb.blog.canPostDraft )
		{
			$('bf_draft').hide();
		}
		else
		{
			if ( ! ipb.blog.canPublish )
			{
				$('bf_publish').hide();
			}
			else
			{
				if ( $('bfs_modOptions') && $F('bfs_modOptions') == 'published' )
				{
					$('bfs_submit').value = ipb.lang['blog_publish_now'];
				}
				else
				{
				
					$('bfs_submit').value = ipb.lang['blog_save_draft'];
				}
			}
		}
		
		$('bf_modWrapper').hide();
		$('bf_timeOpts').hide();
		$('bf_timeOpts2').hide();
		
		/* Blog choose */
		if ( $('blog_chooser') )
		{
			$('blog_chooser').observe( 'change', ipb.blog.pfBlogChooser.bindAsEventListener( this ) );
		}
	},
	
	pfBlogChooser: function( e )
	{
		Event.stop(e);
		
		blogid = $('blog_chooser').options[ $('blog_chooser').selectedIndex ].value;
		
		var url = ipb.vars['base_url'] + "app=blog&amp;module=ajax&amp;section=post&do=getdata&amp;blogid=" + blogid + "&md5check=" + ipb.vars['secure_hash'];
		
		new Ajax.Request( url.replace(/&amp;/g, '&'),
						{
							method: 'get',
							evalJSON: 'force',
							onSuccess: function(t)
							{
								if ( Object.isUndefined( t.responseJSON ) )
								{
									alert( ipb.lang['action_failed'] + ": " + t.responseJSON['error'] );
									return;
								}
								
								ipb.blog.currentCats = $H( t.responseJSON.cats );
								ipb.blog.currentTags = $H( t.responseJSON.tags );
								
								ipb.blog.formInitCats(e);
								ipb.blog.formTogglePopularTags(e);
								
								if ( ipb.blog.currentTags.size() < 1 )
								{
									$('tags_container').hide();
								}
							}
						});
	},
	
	
	pfTriggerSubmit: function( e, type )
	{
		Event.stop(e);
		
		if ( ! ipb.blog.canPostDraft )
		{
			try
			{
				ipb.editors['ed-0'].update_for_form_submit();
			}
			catch(e){}
			
			$('postingform').submit();
		}
		else if ( ! ipb.blog.canPublish )
		{
			try
			{
				ipb.editors['ed-0'].update_for_form_submit();
			}
			catch(e){}
			
			$('postingform').submit();
		}
		else
		{
			var draftId = ( $('bfs_modOptions').options[1].value == 'draft' ) ? 1 : 0;
			
			if ( type == 'draft' )
			{
				$('bfs_modOptions').selectedIndex = draftId;
			}
			else
			{
				$('bfs_modOptions').selectedIndex = ( draftId == 1 ) ? 0 : 1;
			}
			
			try
			{
				ipb.editors['ed-0'].update_for_form_submit();
			}
			catch(e){}
			
			$('postingform').submit();
		}
	},
	
	pfTimeToggle: function(e)
	{
		Event.stop(e);
		$('bf_timeToggle').hide();
		$('bf_timeOpts').show();
		$('bf_timeOpts2').show();
	},
	
	pfTimeCancel: function(e)
	{
		Event.stop(e);
		$('bf_timeToggle').show();
		$('bf_timeOpts').hide();
		$('bf_timeOpts2').hide();
	},
	
	/* OTHER STUFFS */

	showLinkToEntry: function(e, elem)
	{
		_t = prompt( ipb.lang['copy_entry_link'], $( elem ).readAttribute('href') );
		Event.stop(e);
	},
	
	deleteEntry: function(e, elem)
	{
		if( !confirm( ipb.lang['delete_confirm'] ) )
		{
			Event.stop(e);
		}
	},
	
	deleteComment: function( e, elem )
	{
		if( ! confirm( ipb.lang['delete_confirm'] ) )
		{
			Event.stop(e);
		}
	},
	
	changeTheme: function(e)
	{
		Event.stop(e);
		
		if( ipb.blog.popups['themes'] )
		{
			ipb.blog.popups['themes'].show();
		}
		else
		{
			// Set up content
			var afterInit = function( popup )
			{
				/*$('color_editor').insert( { top: $('color_tmp') } );
				$('color_tmp').show();
				ipb.blog.cp1 = new Refresh.Web.ColorPicker('cp1',{startHex: 'ffcc00', startMode:'h', clientFilesPath:clientImagePath});*/
				
				$('theme_preview').observe('click', ipb.blog.previewTheme);
				$('theme_save').observe('click', ipb.blog.saveTheme);
				$('theme_color_picker').observe('click', ipb.blog.openPicker);
			}
			
			ipb.blog.popups['themes'] = new ipb.Popup('theme_editor', { type: 'pane', modal: false, hideAtStart: true, initial: ipb.templates['add_theme'] }, { afterInit: afterInit } );
			ipb.blog.popups['themes'].show();
			
			//ipb.blog.colorpickerRepos();
		}
	},
	
	openPicker: function(e)
	{
		Event.stop(e);
		window.open( ipb.vars['board_url'] + "/blog/colorpicker.html", "colorpicker", "status=0,toolbar=0,width=500,height=400,scrollbars=0");
	},
	
	saveTheme: function(e)
	{
		var url = ipb.vars['base_url'] + "app=blog&amp;module=ajax&amp;section=themes&amp;blogid=" + ipb.blog.blogID;
		var content = $F( 'themeContent' );
		
		new Ajax.Request( url.replace(/&amp;/g, '&'),
						{
							method: 'post',
							parameters: {
								content: content.encodeParam(),
								md5check: ipb.vars['secure_hash']
							},
							evalJSON: 'force',
							onSuccess: function(t)
							{
								if( !Object.isUndefined( t.responseJSON ) )
								{
									alert( ipb.lang['action_failed'] + ": " + t.responseJSON['error'] );
									return;
								}
								
								ipb.blog.popups['themes'].update( ipb.templates['theme_saved'] );
								//ipb.blog.popups['themes'].hide();
								
							}
						});
		
	},
	
	previewTheme: function(e)
	{
		for( var i=0; i < document.styleSheets.length; i++ )
		{
			if( document.styleSheets[ i ].title == 'Theme' )
			{
				document.styleSheets[ i ].disabled = true;
			}
		}

		var style = document.createElement( 'style' );
		style.type = 'text/css';

		var content = $F( 'themeContent' );

		if( ! content )
		{
			return false;
		}
		
		var h = document.getElementsByTagName("head");
		h[0].appendChild( style );
		
		Debug.write( content );
		
		try
		{
	    	style.styleSheet.cssText = content;
	  	}
	  	catch(e)
	  	{
	  		try
	  		{
	    		style.appendChild( document.createTextNode( content ) );
	    		style.innerHTML=content;
	  		}
	  		catch(e){}
	  	}

		return false;
	},
	
	/*colorpickerRepos: function()
	{
		ipb.blog.cp1.show();
		ipb.blog.cp1.updateMapVisuals();
		ipb.blog.cp1.updateSliderVisuals();
	},*/
	
	changeHeader: function(e)
	{
		Event.stop(e);
		
		if( ipb.blog.popups['header'] )
		{
			ipb.blog.popups['header'].show();
		}
		else
		{
			var html = ipb.templates['headers'];
			
			var afterInit = function( popup )
			{
				if( $('reset_header') )
				{
					$('reset_header').observe('click', function(e){
						if( !confirm( ipb.lang['blog_revert_header'] ) )
						{
							Event.stop(e);
						}
						
						window.location.href = ipb.blog.blogURL.replace(/&amp;/g, '&') + "changeHeader=0";
					});
				}
			};
			
			ipb.blog.popups['header'] = new ipb.Popup('change_header', { type: 'pane', modal: true, hideAtStart: false, w: '600px', initial: html }, { afterInit: afterInit } );
		}
	},
	
	setUpCloseLinks: function()
	{
		ipb.delegate.register('.close_link', ipb.blog.closeBlock );
		ipb.delegate.register('.configure_link', ipb.blog.configureBlock );
		ipb.delegate.register('.block_control', ipb.blog.addBlock );
		ipb.delegate.register('.delete_block', ipb.blog.deleteBlock );
	},
	
	deleteBlock: function(e, elem)
	{
		if( !confirm( ipb.lang['blog_sure_delcblock'] ) )
		{
			Event.stop(e);
		}
	},
	
	configureBlock: function(e, elem)
	{
		Event.stop(e);
		
		// Get id
		Debug.write( $(elem).id );
		var elem = $( elem ).up( '.cblock_drag' );
		var blockid = $( elem ).id.replace('cblock_', '');
		var wrapper = $( elem ).down('.cblock_inner');
		
		if( !wrapper ){ return; }
		
		// Get block
		new Ajax.Request( ipb.vars['base_url'] + "app=blog&module=ajax&section=cblocks&do=showcblockconfig&secure_key=" + ipb.vars['secure_hash'] + "&cblock_id=" + blockid + "&blogid=" + ipb.blog.blogID,
							{
								method: 'get',
								evalJSON: 'force',
								onSuccess: function(t)
								{
									if( t.responseText == 'error' )
									{
										alert( ipb.lang['action_failed'] );
										return;
									}
									else
									{
										$( elem ).replace( t.responseText );
										Sortable.create('cblock_right', ipb.blog.props );
										Sortable.create('cblock_left', ipb.blog.props );
									}
								}
							}
						);
	},
	
	addBlock: function(e, elem)
	{		
		if( $( elem ).id == 'new_cblock' )
		{
			return;
		}
		else
		{
			Event.stop(e);
			
			// Get id
			Debug.write( $(elem).id );
			var blockid = $( elem ).id.replace('enable_cblock_', '');
			
			if( $( elem ).hasClassName('enable') ){
				var req = 'doenablecblock';
			} else {
				var req = 'doaddcblock';
			}				
			
			// Get block
			new Ajax.Request( ipb.vars['base_url'] + "app=blog&module=ajax&section=cblocks&do=" + req + "&secure_key=" + ipb.vars['secure_hash'] + "&cbid=" + blockid + "&blogid=" + ipb.blog.blogID,
								{
									method: 'get',
									evalJSON: 'force',
									onSuccess: function(t)
									{
										if( Object.isUndefined( t.responseJSON ) )
										{
											alert( ipb.lang['action_failed'] );
											return;
										}
										
										if( t.responseJSON['error'] )
										{
											alert( ipb.lang['action_failed'] + ": " + t.responseJSON['error'] );
											return;
										}
										
										if( t.responseJSON['cb_html'] )
										{
											// Figure out where to put it
											if( $('cblock_right').visible() )
											{
												$('cblock_right').insert( { bottom: t.responseJSON['cb_html'] } );
												Sortable.create('cblock_right', ipb.blog.props );
												Sortable.create('cblock_left', ipb.blog.props );
											}
											else if( $('cblock_left').visible() )
											{
												$('cblock_left').insert( { bottom: t.responseJSON['cb_html'] } );
												Sortable.create('cblock_right', ipb.blog.props );
												Sortable.create('cblock_left', ipb.blog.props );
											}
											else
											{
												document.location.reload(true);
											}
											
											// Remove it from the menu
											if( $('enable_cblock_' + blockid) ){
												$('enable_cblock_' + blockid).remove();
											}
										}
									}
								} );
			
		}
	},

	closeBlock: function(e, elem)
	{
		Event.stop(e);
		
		var elem = $( elem ).up( '.cblock_drag' );
		var cblockid = $( elem ).id.replace('cblock_', '');
		
		if( Object.isUndefined( cblockid ) ){ return; }
		if( !elem ){ return; }
		
		var url = ipb.vars['base_url'] + 'app=blog&module=ajax&section=cblocks&do=doremovecblock&blogid='+ipb.blog.blogID + '&cbid=' + cblockid + "&secure_key=" + ipb.vars['secure_hash'];
		
		new Ajax.Request( url.replace(/&amp;/, '&'),
						{
							method: 'post',
							evalJSON: 'force',
							onSuccess: function(t)
							{
								if( Object.isUndefined( t.responseJSON ) || t.responseText == 'error' )
								{
									Debug.write( "Error removing block" );
								}
								else
								{
									new Effect.Parallel( [
										new Effect.BlindUp( $(elem), { sync: true } ),
										new Effect.Fade( $(elem), { sync: true } )
									], { duration: 0.5, afterFinish: function(){
										// Get the name of the item
									
										var menu_item = ipb.templates['cblock_item'].evaluate( { 'id': cblockid, 'name': t.responseJSON['name'] } );
									
										$('content_blocks_menucontent').insert( menu_item );
									
										$(elem).remove();
										ipb.blog.updatedBlocks('');
									} } );
								}
							}
						});	
	},
	
	setUpDrags: function()
	{
		Debug.write("Here");
		
		if( !$('main_column') ){
			Debug.error("No main column found, cannot create draggable blocks");
		}
		
		var height_l = null;
		var height_r = null;
		var width_c = null;
		
		if( $('cblock_left') ){
			height_l = $('cblock_left').getHeight();
			width_c = $('cblock_left').getWidth();
		}
		
		if( $('cblock_right') ){
			height_r = $('cblock_right').getHeight();
			if( width_c != null )
			{
				var n_width_c = $('cblock_right').getWidth();
				width_c = ( n_width_c > width_c ) ? n_width_c : width_c;
			}
			else
			{
				width_c = $('cblock_right').getWidth();
			}
		}
		
		// Step one: if side column doesnt exist, create it
		if( !$('cblock_left') )
		{
			var cblockleft = new Element('div', { id: 'cblock_left' } );
			cblockleft.setStyle('width: ' + width_c + 'px; height: ' + height_r + 'px;').addClassName('cblock').addClassName('temp').hide();
			$('sidebar_holder').insert( { before: cblockleft } );
			ipb.blog.updateLeft = true;
		}
		
		if( !$('cblock_right') )
		{
			var cblockright = new Element('div', { id: 'cblock_right' } );
			cblockright.setStyle('width: ' + width_c + 'px; height: ' + height_l + 'px;').addClassName('cblock').addClassName('temp').hide();
			$('sidebar_holder').insert( { after: cblockright } );
			ipb.blog.updateRight = true;
		}
		
		Sortable.create('cblock_right', ipb.blog.props );
		Sortable.create('cblock_left', ipb.blog.props );
		
		// Add observer
		Draggables.addObserver(
			{
				onStart: function( eventName, draggable, event )
				{
					$('cblock_left').show().addClassName('drop_zone');
					$('cblock_right').show().addClassName('drop_zone');
					
					if( !Prototype.Browser.IE )
					{
						$('cblock_left').setStyle('opacity: 0.3');
						$('cblock_right').setStyle('opacity: 0.3');
					}
				},
				onEnd: function( eventName, draggable, event )
				{
					$('cblock_left').removeClassName('drop_zone').setStyle('opacity: 1');
					$('cblock_right').removeClassName('drop_zone').setStyle('opacity: 1');
					
					ipb.blog._updated( draggable );
				}
			}
		);
	},
	
	_updated: function( draggable )
	{
		if( ipb.blog._updating ){ return; }
		ipb.blog._updating = true;
		
		id = 0;
		
		// Get the ID
		if( draggable.element )
		{
			id = $( draggable.element ).id.replace('cblock_', '');
		}
		
		// Update classes
		ipb.blog.updatedBlocks( id );
		
		// Update position by ajax
		ipb.blog.updatePosition( id, draggable );
	},
	
	updatePosition: function( id, draggable )
	{
		if( !$('cblock_' + id ) ){ return; }
		
		// Need to figure out which column it is in
		if( $('cblock_' + id ).descendantOf('cblock_left') ){
			var pos = 'l';
		} else {
			var pos = 'r';
		}
		
		var nextid = 0;
		
		// Which block is next to it?
		var nextelem = $('cblock_' + id).next('.cblock_drag');
		
		if( !Object.isUndefined( nextelem ) && $(nextelem).id )
		{
			nextid = $( nextelem ).id.replace('cblock_', '');
		}
		
		var url = ipb.vars['base_url'] + "app=blog&module=ajax&section=cblocks&do=savecblockpos&oldid="+id+"&newid="+nextid+"&pos="+pos+"&blogid="+ipb.blog.blogID+"&secure_key="+ipb.vars['secure_hash'];
		
		// Ok, send the infos
		new Ajax.Request( 	url.replace('&amp;', '&'),
							{
								method: 'get',
								onSuccess: function(t){
									Debug.write( t.responseText );
								}
							}
						);
		
	},
	
	updatedBlocks: function( id )
	{
		var d_l = $('cblock_left').select('.cblock_drag');
		var d_r = $('cblock_right').select('.cblock_drag');
		
		//var d_l = Sortable.sequence('cblock_left');
		//var d_r = Sortable.sequence('cblock_right');
		//Debug.dir( d_l );
		
		// Check for descendants
		if( d_l.size() > 0 ){
			$('main_blog_wrapper').addClassName('with_left');
			$('cblock_left').removeClassName('temp');
		} else {
			$('main_blog_wrapper').removeClassName('with_left');
			$('cblock_left').addClassName('temp').hide();
			$('cblock_left').innerHTML += "&nbsp;"; // Force a redraw for safari
		}
		
		if( d_r.size() > 0 ){
			$('main_blog_wrapper').addClassName('with_right');
			$('cblock_right').removeClassName('temp');
		} else {
			$('main_blog_wrapper').removeClassName('with_right');
			$('cblock_right').addClassName('temp').hide();
			$('cblock_left').innerHTML += "&nbsp;"; // Force a redraw for safari
		}
		
		if( ipb.blog.updateLeft )
		{
			//$('cblock_left').setStyle('height: auto; position: static; top: auto; left: auto;');
		}
		
		ipb.blog._updating = false;
	},
	
	saveCblock: function( e, cblock, fields )
	{
		var save_fields = '';
		
		for( var i = 0; i < fields.length; i++ )
		{
			save_fields += '&' + 'cblock_config[' + fields[i] + ']' + '=' + $F( fields[i] );
		}
		
		var url = ipb.vars['base_url'] + "app=blog&module=ajax&section=cblocks&do=savecblockconfig&cblock_id=" + cblock + "&blogid=" + ipb.blog.blogID + "&secure_key=" + ipb.vars['secure_hash'];
		
		new Ajax.Request( url.replace(/&amp;/g, '&' ) + save_fields,
							{
								method: 'get',
								onSuccess: function(t)
								{
									if( t.responseText == 'error' )
									{
										alert( ipb.lang['action_failed'] );
										return;
									}
									else if( t.responseText == 'refresh' )
									{
										document.location.reload(true);
									}
									else
									{
										$( 'cblock_' + cblock_id ).replace( t.responseText );
										Sortable.create('cblock_right', ipb.blog.props );
										Sortable.create('cblock_left', ipb.blog.props );
									}
									
								}
							}
						);
	},
	
	register: function( id, position )
	{
		if( !ipb.blog.inEntry ){ return; }
		if( !$('cblock_' + id) ){ return; }
		
		//new Draggable( $('cblock_' + id), { handle: 'draggable', revert: true } );		
	},
	
	/**
	 * Sets the supplied post to hidden
	 * 
	 * @var		{int}	id		The ID of the post to hide
	*/
	setCommentHidden: function(id)
	{
		if( $( 'comment_id_' + id ).select('.post_wrap')[0] )
		{
			$( 'comment_id_' + id ).select('.post_wrap')[0].hide();

			if( $('unhide_post_' + id ) )
			{
				$('unhide_post_' + id).observe('click', ipb.blog.showHiddenComment );
			}
		}
	},
	
	/**
	 * Unhides the supplied post
	 * 
	 * @var		{event}		e	The link event
	*/
	showHiddenComment: function(e)
	{
		link = Event.findElement(e, 'a');
		id = link.id.replace('unhide_post_', '');
		
		if( $('comment_id_' + id ).select('.post_wrap')[0] )
		{
			elem = $('comment_id_' + id ).select('.post_wrap')[0];
			new Effect.Parallel( [
				new Effect.BlindDown( elem ),
				new Effect.Appear( elem )
			], { duration: 0.5 } );
		}
		
		if( $('comment_id_' + id ).select('.post_ignore')[0] )
		{
			elem = $('comment_id_' + id ).select('.post_ignore')[0];
			/*new Effect.BlindUp( elem, {duration: 0.2} );*/
			elem.hide();
		}
		
		Event.stop(e);
	}
}

ipb.blog.init();