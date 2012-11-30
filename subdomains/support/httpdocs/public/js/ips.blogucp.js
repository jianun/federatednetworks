/************************************************/
/* IPB3 Javascript								*/
/* -------------------------------------------- */
/* ips.blogucp.js - Blog javascript				*/
/* (c) IPS, Inc 2008							*/
/* -------------------------------------------- */
/* Author: Rikki Tissier						*/
/************************************************/

var _blogucp = window.IPBoard;

_blogucp.prototype.blogucp = {
	currentCats: {},
	_newCats: $H(),
	blogs: {},
	imgs: {},
	popups: [],
	/*------------------------------*/
	/* Constructor 					*/
	init: function(){
		
		Debug.write("Initializing ips.blogucp.js");
		
		document.observe("dom:loaded", function()
		{
			if ( $('isBlogUcpForm') )
			{
				ipb.blogucp.updateForm();
				
				$$('.blogform').each( function(elem)
				{
					Debug.write( elem );
					switch( elem.tagName )
					{
						case 'INPUT':
						case 'LABEL':
							$( elem ).observe('click', ipb.blogucp.updateForm);
						break;
						case 'SELECT':
							$( elem ).observe('change', ipb.blogucp.updateForm);
						break;
					}
				});
			}
			
			/* Blog list */
			if ( $('blogListTable') )
			{
				ipb.delegate.register(".__delete", ipb.blogucp.deleteDialogue );
			}			
		});
	},
	
	/**
	 * Stop importing RSS feed
	 */
	stopRssFeed: function( blogId )
	{
		if( confirm( ipb.lang['delete_confirm'] ) )
		{
			window.location = ipb.vars['base_url'] + "app=core&module=usercp&tab=blog&area=stopRssImport&do=custom&blogid=" + blogId + '&secure_key=' + ipb.vars['secure_hash'];
		}
	},
	
	/**
	* Delete pop-up
	*/
	deleteDialogue: function(e, elem)
	{
		Event.stop(e);
		
		var id = elem.id.replace( 'blogDelete_', '' );
		
		if ( ipb.blogucp.popups[ 'del_' + id ] )
		{
			ipb.blogucp.popups[ 'del_' + id ].show(e);
		}
		else
		{
			ipb.blogucp.popups[ 'del_' + id ] = new ipb.Popup( 'd_e_l__' + id, {
																			type: 'balloon',
																			initial: ipb.templates['deleteDialogue'].evaluate( { 'id' : id } ),
																			stem: true,
																			hideAtStart: false,
																			defer: false,
																			attach: { target: $('blogLink_' + id ), position: 'auto', 'event': 'click' },
																			w: '400px'
																		});
																		
			/* Populate select box */
			if ( ipb.blogucp.blogs.size() )
			{
				ipb.blogucp.blogs.each( function( b )
				{
					if ( b.key != id )
					{
						var _o = new Element( 'option' );
						_o.value  = b.key;
						_o.text   = b.value.replace( '&#39;', "'" ).replace( '&quot;', '"' );
						
						$('delselect_' + id).insert( _o );
					}
				} );
			}
				
			$('delButton_' + id).observe('click', ipb.blogucp.ohJustDeleteItAlready.bindAsEventListener( this, id ) );
			$('delMove_' + id).observe('change', ipb.blogucp.deleteMoveCheck.bindAsEventListener( this, id ) );
		}
	},
	
	/**
	* YeAh
	*/
	ohJustDeleteItAlready: function( e, id )
	{
		Event.stop(e);
		if ( ! $('delConfirm_' + id ).checked )
		{
			alert( 'If you want to delete this blog, you must check the box' );
			return false;
		}
		
		var url = $('blogDelete_' + id ).href;
		
		if ( $('delMove_' + id ) && $('delMove_' + id ).checked )
		{
			url += '&moveTo=' + $('delselect_' + id ).options[ $('delselect_' + id ).selectedIndex ].value;
		}
		
		/* OG */
		window.location = url;
	},
	
	/**
	 * <insert stuff>
	 */
	deleteMoveCheck: function( e, id )
	{
		Event.stop(e);
		
		if ( $('delMove_' + id).checked )
		{
			$('delMore_' + id).hide();
		}
		else
		{
			$('delMore_' + id).show();
		}
		
		return true;
	},
	
	/**
	 * Cat form
	 */
	initCatForm: function()
	{
		var _c = 0;
		$('main_comment_wrap').update('');
		
		if ( ipb.blogucp.currentCats.size() )
		{
			ipb.blogucp.currentCats.each( function( c )
			{
				var html = ipb.templates['cat_entry'].evaluate( { 'cid': c.key, 'cat': c.value['category_title'], 'count' : c.value['count'], 'img' : ( c.key != 0 ) ? ipb.blogucp.imgs['normal'] : ipb.blogucp.imgs['locked'] } );
				$('main_comment_wrap').insert( html );
				
				if ( c.key == 0 )
				{
					$('cat_' + c.key ).disabled = true;
				}
				else
				{
					_c++;
				}
				
				$('catClicker_' + c.key).observe('click', ipb.blogucp.editPop.bindAsEventListener( this, c.key ) );
			} );
		}
	},
	
	/**
	 * Edit Pop
	 */
	editPop: function(e, key)
	{
		Event.stop(e);
	
		var cat     = ipb.blogucp.currentCats.get( key );
		var content = '';
		
		if ( key == 0 )
		{
			var content = ipb.templates['editCant'].evaluate( { 'cid': key, 'cat': cat.category_title, 'cidwq': key } );
		}
		else
		{ 
			var content = ipb.templates['editCatMeow'].evaluate( { 'cid': key, 'cat': cat.category_title, 'cidwq': key } );
		}
		
		if ( ipb.blogucp.popups[ key ] )
		{
			ipb.blogucp.popups[ key ].show(e);
		}
		else
		{
			ipb.blogucp.popups[ key ] = new ipb.Popup( 'c_a_t__' + key, {
																			type: 'balloon',
																			initial: content,
																			stem: true,
																			hideAtStart: false,
																			defer: false,
																			attach: { target: $('catClicker_' + key ), position: 'auto', 'event': 'click' },
																			w: '400px'
																		});
																		
			$('catSubmit_' + key).observe('click', ipb.blogucp.editSave.bindAsEventListener( this, key ) );
		}
	},
	
	/**
	 * Edit save
	 */
	editSave: function(e, key)
	{
		var cat = ipb.blogucp.currentCats.get( key );
		
		if ( cat.category_title && $F('catEditBox_' + key) && key != 0 )
		{
			var url = ipb.vars['base_url'] + "app=blog&amp;module=ajax&amp;section=categories&amp;blogid=" + ipb.blogucp.blogID + '&amp;category_id=' + key;
			
			new Ajax.Request( url.replace(/&amp;/g, '&'),
							{
								method: 'post',
								parameters: {
									category_title: $F('catEditBox_' + key).encodeParam(),
									md5check: ipb.vars['secure_hash']
								},
								evalJSON: 'force',
								onSuccess: function(t, e)
								{
									if ( Object.isUndefined( t.responseJSON ) )
									{
										alert( ipb.lang['action_failed'] + ": " + t.responseJSON['error'] );
										return;
									}
									
									ipb.blogucp.popups[ key ].hide(e);
									
									$( 'catName_' + key ).update( $F('catEditBox_' + key ).replace( "'", '&#039;' ) );
								}
							});
		}
	},
	
	/**
	 * Add category
	 */
	addCat: function(e)
	{
		$('catAddInputForm').value = $F('catAddInput');
		
		if ( $('catAddInputForm').value )
		{
	  		$('catAddForm').submit();
	  	}  
	},
	
	/**
	 * Settings form
	 */
	updateForm: function(e)
	{
		if( $F('blog_type') == 'external' ){
			ipb.blogucp.hide( $('blog_local_settings') );
			ipb.blogucp.hide( $('list_blog_view_level') );
			ipb.blogucp.hide( $('blog_rss_settings') );
			ipb.blogucp.hide( $('blog_customize_settings') );
			ipb.blogucp.hide( $('blog_private_club') );
			ipb.blogucp.hide( $('blog_editors') );
			ipb.blogucp.show( $('blog_local_settings_hidden') );
			ipb.blogucp.show( $('blog_external_settings') );
		} else {
			ipb.blogucp.show( $('blog_local_settings') );
			ipb.blogucp.show( $('list_blog_view_level') );
			ipb.blogucp.show( $('blog_rss_settings') );
			ipb.blogucp.show( $('blog_customize_settings') );
			ipb.blogucp.show( $('blog_private_club') );
			ipb.blogucp.show( $('blog_editors') );
			ipb.blogucp.hide( $('blog_local_settings_hidden') );
			ipb.blogucp.hide( $('blog_external_settings') );
		}
		
		if( $F('blog_view_level') == 'private' || $F('blog_view_level') == 'privateclub' ){
			ipb.blogucp.hide( $('list_allowguest') );
			ipb.blogucp.hide( $('list_allowguestcomments') );
		} else {
			ipb.blogucp.show( $('list_allowguest') );
			ipb.blogucp.show( $('list_allowguestcomments') );
		}
		
		if( $F('blog_view_level') == 'privateclub' ){
			ipb.blogucp.show( $('blog_private_club' ) );
		} else {
			ipb.blogucp.hide( $('blog_private_club' ) );
		}
		
		if( $('blog_allowguest').checked ){
			ipb.blogucp.show( $('list_allowguestcomments') );
		} else {
			ipb.blogucp.hide( $('list_allowguestcomments') );
		}
		
	},
	
	hide: function(elem)
	{
		if( $( elem ) ){ $( elem ).hide(); }
	},
	
	show: function(elem)
	{
		if( $( elem ) ){ $( elem ).show(); }
	}	
}

ipb.blogucp.init();