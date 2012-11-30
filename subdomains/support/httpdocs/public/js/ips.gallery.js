/************************************************/
/* IPB3 Javascript								*/
/* -------------------------------------------- */
/* ips.gallery.js - Gallery javascript			*/
/* (c) IPS, Inc 2008							*/
/* -------------------------------------------- */
/* Author: Rikki Tissier & Brandon Farber		*/
/************************************************/

/* Hack to get lastDescendant
	Thanks: http://proto-scripty.wikidot.com/prototype:tip-getting-last-descendant-of-an-element
*/
Element.addMethods({
    lastDescendant: function(element) {
        element = $(element).lastChild;
        while (element && element.nodeType != 1) 
            element = element.previousSibling;
        return $(element);
    }
});



var _gallery = window.IPBoard;

_gallery.prototype.gallery = {
	
	totalChecked:	0,
	inSection: '',
	
	cur_left:	0,
	cur_right:	0,
	cur_image:	0,
	
	catPopups: [],
	popup: null,
	/*timer: [],
	blockSizes: [],*/
	
	/*------------------------------*/
	/* Constructor 					*/
	init: function()
	{
		Debug.write("Initializing ips.gallery.js");
		
		document.observe("dom:loaded", function(){
			/* Gallery meta popup */
			if( $('meta-link') )
			{
				$('meta-link').observe('click', ipb.gallery.showMeta );
			}

			if( ipb.gallery.inSection == 'image' )
			{
				ipb.gallery.preCheckComments();

				ipb.delegate.register('a[rel="bookmark"]', ipb.gallery.showLinkToComment );
				ipb.delegate.register('a[rel~=newwindow]', ipb.global.openNewWindow, { 'force': 1 } );
				ipb.delegate.register('a[rel~=popup]', ipb.gallery.openPopUp );
				ipb.delegate.register('.delete_item', ipb.gallery.confirmSingleDelete );
				ipb.delegate.register('.comment_mod', ipb.gallery.checkComment );
				ipb.delegate.register('.multiquote', ipb.gallery.toggleMultiquote);
				
				if( $('show_filters') )
				{
					$('show_filters').observe('click', ipb.gallery.toggleFilters );
					$('filter_form').hide();
				}
			}
			else if( ipb.gallery.inSection == 'category' )
			{
				ipb.gallery.preCheckImages();
			
				//ipb.delegate.register('.image_mod', ipb.gallery.checkImage );
				ipb.delegate.register('.check_all', ipb.gallery.checkAllInForm );
			}
			
			if( $('album') )
			{
				$('album').hide();
			}
			
			$$('.subcatsTrigger').each( function(elem) {

				var thisid = elem.identify();
				thisid = thisid.replace( 'subCatsDDTrigger_', '' );

				if( $('subCatsDD_' + thisid ) )
				{
					$('subCatsDD_' + thisid ).hide();
					$(elem).observe('click', ipb.gallery.showSubCats );
				}
			});
		});
	},
	
	buildSubCatPopup: function( e, id )
	{	
		Event.stop(e);
		
		if( ipb.gallery.catPopups[ id ] ){
			ipb.gallery.catPopups[ id ].show();
		}
		else
		{
			ipb.gallery.catPopups[ id ] = new ipb.Popup( id + '_popup', { 
					initial: $( id + '_subcats' ).innerHTML,
					type: 'balloon',
					stem: true,
					hideAtStart: false,
					attach: {
						target: $(id + '_subcat_link'),
						position: 'auto',
						'event': 'click'
					},
					w: '300px'							
				});		
		}
	},
	
	registerCategory: function( id, href )
	{
		if( !$('category_' + id) ){ return; }
		
		href = href.replace('&amp;', '&');
		
		$('category_' + id).down('.preview').wrap( 'a', { href: href, 'class': 'enhanced_link', title: ipb.lang['go_to_category'] } );
		
		if( $('category_' + id).down('.description') ){
			$('category_' + id).down('.description').hide();
		}
		
		var mouseOvered = true;
		
		if( $('category_' + id + '_description' ) )
		{
			$('category_' + id).observe('mouseenter', function(e){
				new Effect.Appear( $('category_' + id + '_description'), { duration: 0.3 } );
			});
			$('category_' + id).observe('mouseleave', function(e){
				new Effect.Fade( $('category_' + id + '_description'), { duration: 0.3 } );
			});
		}
	},
	
	toggleFilters: function(e)
	{
		if( $('filter_form') )
		{
			Effect.toggle( $('filter_form'), 'blind', {duration: 0.2} );
			Effect.toggle( $('show_filters'), 'blind', {duration: 0.2} );
		}
	},
	
	rotateImage: function( e, dir )
	{
		Debug.write( curnotes.size() );
		
		// If we have notes, just refresh
		if( !Object.isUndefined( curnotes ) && curnotes.size() )
		{
			return;
		}
		
		Event.stop(e);
		if( ( dir != 'left' && dir != 'right' ) || !$('image_view_' + ipb.gallery.imageID) ){ return; }
		
		new Ajax.Request( 
							ipb.vars['base_url']+'app=gallery&module=ajax&section=image&do=rotate-' + dir + '&secure_key=' + ipb.vars['secure_hash'] + '&img=' + ipb.gallery.imageID,
							{
								method: 'post',
								onSuccess: function(t)
								{
									/* No Permission */
									if( t.responseText == 'nopermission' )
									{
										alert( ipb.lang['no_permission'] );
									}
									else if( t.responseText == 'rotate_failed' )
									{
										alert( ipb.lang['gallery_rotate_failed'] );
									}
									else
									{
										var rand = Math.round( Math.random() * 100000000 );
										var img = $('image_view_' + ipb.gallery.imageID);
										var tmpSrc = img.src;
										
										tmpSrc = tmpSrc.replace(/t=[0-9]+/, '');
										 
										$( img ).src = tmpSrc + "?t=" + rand;
									}
								}
							}						
						);	
		return false;
		
	},
	
	setUpGalleryBlock: function(id)
	{
		if( ipb.Cookie.get('galleryBlock_' + id) == '1' ){
			$( id + '_title').addClassName('expanded');
			$( id + '_content').show();
		} else {
			$( id + '_title').addClassName('collapsed');
			$( id + '_content').hide();
		}
		
		$( id + '_title').observe( 'click', ipb.gallery.toggleImageViewBlock.bindAsEventListener( this, id ) );
	},
	
	toggleImageViewBlock: function( e, id )
	{
		Event.stop(e);
		
		if( $( id + '_content' ).visible() ){
			new Effect.BlindUp( $( id + '_content' ), { duration: 0.2 } );			
			$( id + '_title' ).removeClassName('expanded').addClassName('collapsed');
			ipb.Cookie.set('galleryBlock_' + id, '0', true);		
		} else {
			new Effect.BlindDown( $( id + '_content' ), { duration: 0.2 } );	
			$( id + '_title' ).removeClassName('collapsed').addClassName('expanded');
			ipb.Cookie.set('galleryBlock_' + id, '1', true);
		}
		
	},	
	
	openPopUp: function(e, link)
	{		
		window.open(link.href, "image", "status=0,toolbar=0,location=0,menubar=0,directories=0,resizable=1,scrollbars=1");
		Event.stop(e);
		return false;
	},
	
	showSubCats: function(e)
	{
		Event.stop(e);
		elem	= Event.findElement( e, 'h5' );
		var thisid = elem.identify();
		thisid = thisid.replace( 'subCatsDDTrigger_', '' );

		$('subCatsDD_' + thisid ).toggle();
	},
	
	/**
	 * Photostrip code - at the top because it'll prob be updated the most :P
	 */
	photostripInit: function()
	{
		if( ipb.gallery.cur_left > 0 )
		{
			$('slide_left').show();
			$('slide_left').observe( 'mouseover', ipb.gallery.photostripMouesover );
			$('slide_left').observe( 'mouseout', ipb.gallery.photostripMouesout );
			$('slide_left').observe( 'click', ipb.gallery.photostripSlideLeft );
		}
		else
		{
			$('slide_left').hide();
		}
		
		if( ipb.gallery.cur_right > 0 )
		{
			$('slide_right').show();
			$('slide_right').observe( 'mouseover', ipb.gallery.photostripMouesover );
			$('slide_right').observe( 'mouseout', ipb.gallery.photostripMouesout );
			$('slide_right').observe( 'click', ipb.gallery.photostripSlideRight );
		}
		else
		{
			$('slide_right').hide();
		}
	},
	
	/**
	 * Photostrip code - at the top because it'll prob be updated the most :P
	 */
	resetPhotostrip: function()
	{
		var count = 1;

		$('strip').childElements().each( function(elem){
			if( count == 1 )
			{
				ipb.gallery.cur_left	= elem.id.replace( /strip_/, '' );
			}
			
			if( count == 5 )
			{
				ipb.gallery.cur_right	= elem.id.replace( /strip_/, '' );
			}
			
			count++;
		});
	},
	
	/**
	 * Photostrip slide left
	 */
	photostripSlideLeft: function(e)
	{
		new Ajax.Request( 
							ipb.vars['base_url']+'app=gallery&module=ajax&section=photostrip&do=slide_right&secure_key=' + ipb.vars['secure_hash'] + '&img='+ipb.gallery.cur_left+'&cur_img='+ipb.gallery.cur_image,
							{
								method: 'post',
								onSuccess: function(t)
								{
									/* No Permission */
									if( t.responseText == 'nopermission' )
									{
										alert( ipb.lang['no_permission'] );
									}
									else
									{
										// Get rid of first item in the list
										$('strip').lastDescendant().remove();
										
										// Add the new item
										$('strip').insert( { top: t.responseText } );

										// And then reset
										ipb.gallery.resetPhotostrip();
										ipb.gallery.photostripInit();
									}
								}
							}						
						);	
		return false;
	},
	
	/**
	 * Photostrip slide right
	 */
	photostripSlideRight: function(e)
	{
		new Ajax.Request( 
							ipb.vars['base_url']+'app=gallery&module=ajax&section=photostrip&do=slide_left&secure_key=' + ipb.vars['secure_hash'] + '&img='+ipb.gallery.cur_right+'&cur_img='+ipb.gallery.cur_image,
							{
								method: 'post',
								onSuccess: function(t)
								{
									/* No Permission */
									if( t.responseText == 'nopermission' )
									{
										alert( ipb.lang['no_permission'] );
									}
									else
									{
										// Get rid of first item in the list
										$('strip').firstDescendant().remove();
										
										// Add the new item
										$('strip').insert( { bottom: t.responseText } );

										// And then reset
										ipb.gallery.resetPhotostrip();
										ipb.gallery.photostripInit();
									}
								}
							}						
						);	
		return false;
	},
	
	/**
	 * Photostrip slider cell mouseover
	 */
	photostripMouesover: function(e)
	{
		cell = Event.findElement( e, 'div' );
		
		$(cell.id).addClassName('post2');
		$(cell.id).addClassName('clickable');
	},
	
	/**
	 * Photostrip slider cell mouseout
	 */
	photostripMouesout: function(e)
	{
		cell = Event.findElement( e, 'div' );
		
		$(cell.id).removeClassName('post2');
		$(cell.id).removeClassName('clickable');
	},
	
	/**
	 * Show the meta information popup
	 */
	showMeta: function(e)
	{
		Event.stop(e);
		
		if( ipb.gallery.popup )
		{
			ipb.gallery.popup.show();
		}
		else
		{
			ipb.gallery.popup = new ipb.Popup( 'showmeta', { type: 'pane', modal: false, w: '600px', h: '500px', initial: $('metacontent').innerHTML, hideAtStart: false, close: 'a[rel="close"]' } );
		}
		
		return false;
	},
	
	/**
	 * Show the comment link
	 */
	showLinkToComment: function(e, elem)
	{	
		_t = prompt( ipb.lang['copy_topic_link'], $( elem ).readAttribute('href') );
		Event.stop(e);
	},
	
	/**
	 * Confirm they want to delete stuff
	 * 
	 * @var 	{event}		e	The event
	*/
	confirmSingleDelete: function(e, elem)
	{
		if( !confirm( ipb.lang['delete_post_confirm'] ) )
		{
			Event.stop(e);
		}
	},
	
	/**
	 * Do album manager dropdown change
	 * 
	 * @var 	object	select	The select box element
	*/
	goToAlbumOp: function( select )
	{
		goto_url = select.options[select.selectedIndex].value;
		
		if( goto_url == 'null' )
		{
			return false;
		}

		if( goto_url.match( /do=del/ ) )
		{
			if( confirm( deletion_confirm_lang ) )
			{
				document.location = ipb.vars['base_url'] + goto_url;
			}
		}
		else
		{
	    	document.location = ipb.vars['base_url'] + goto_url;
	 	}
	},
	
	/**
	 * Toggles the multimod buttons in posts
	 * 
	 * @param	{event}		e		The event
	 * @param	{element}	elem	The element that fired
	*/
	toggleMultiquote: function(e, elem)
	{
		Event.stop(e);
		
		// Get list of already quoted posts
		try {
			quoted = ipb.Cookie.get('gal_pids').split(',').compact();
		} catch(err){
			quoted = $A();
		}
		
		id = elem.id.replace('multiq_', '');
		
		// Hokay, are we selecting/deselecting?
		if( elem.hasClassName('selected') )
		{
			elem.removeClassName('selected');
			quoted = quoted.uniq().without( id ).join(',');
		}
		else
		{
			elem.addClassName('selected');
			quoted.push( id );
			quoted = quoted.uniq().join(',');
		}
		
		// Save cookie
		ipb.Cookie.set('gal_pids', quoted, 0);			
	},
	
	/**
	 * Check the files we've selected
	 */
	preCheckComments: function()
	{
		if( $('selectedgcids') )
		{
			var topics = $F('selectedgcids').split(',');
		}
		
		var checkboxesOnPage	= 0;
		var checkedOnPage		= 0;

		if( topics )
		{
			topics.each( function(check){
				if( check != '' )
				{
					if( $('pid_' + check ) )
					{
						checkedOnPage++;
						$('pid_' + check ).checked = true;
					}
					
					ipb.gallery.totalChecked++;
				}
			});
		}

		$$('.comment_mod').each( function(check){
			checkboxesOnPage++;
		} );
		
		if( $('comments_all') )
		{
			if( checkedOnPage == checkboxesOnPage )
			{
				$('comments_all').checked = true;
			}
		}
		
		ipb.gallery.updateModButton();
	},
	
	/**
	 * Confirm they want to delete stuff
	 * 
	 * @var 	{event}		e	The event
	*/
	checkComment: function(e, elem)
	{
		remove = new Array();
		check = elem;
		selectedTopics = $F('selectedgcids').split(',').compact();
		
		var checkboxesOnPage	= 0;
		var checkedOnPage		= 0;
		
		if( check.checked == true )
		{
			Debug.write("Checked");
			selectedTopics.push( check.id.replace('pid_', '') );
			ipb.gallery.totalChecked++;
		}
		else
		{
			remove.push( check.id.replace('pid_', '') );
			ipb.gallery.totalChecked--;
		}
		
		$$('.comment_mod').each( function(check){
			checkboxesOnPage++;
			
			if( $(check).checked == true )
			{
				checkedOnPage++;
			}
		} );
		
		if( $('comments_all') )
		{
			if( checkedOnPage == checkboxesOnPage )
			{
				$('comments_all').checked = true;
			}
			else
			{
				$('comments_all' ).checked = false;
			}
		}
		
		selectedTopics = selectedTopics.uniq().without( remove ).join(',');		
		ipb.Cookie.set('modgcids', selectedTopics, 0);
		
		$('selectedgcids').value = selectedTopics;

		ipb.gallery.updateModButton();
	},
	
	/**
	 * Check the files we've selected
	 */
	preCheckImages: function()
	{
		var topics = [];
		
		if( $('selectedimgids' ) ){
			topics = $F('selectedimgids').split(',');
		} 
		
		var checkboxesOnPage	= 0;
		var checkedOnPage		= 0;

		if( topics )
		{
			topics.each( function(check){
				if( check != '' )
				{
					if( $('img_' + check ) )
					{
						checkedOnPage++;
						$('img_' + check ).checked = true;
					}
					
					ipb.gallery.totalChecked++;
				}
			});
		}

		$$('.image_mod').each( function(check){
			checkboxesOnPage++;
		} );
		
		if( $('imgs_all') )
		{
			if( checkedOnPage == checkboxesOnPage )
			{
				$('imgs_all').checked = true;
			}
		}
		
		ipb.gallery.updateModButton();
	},
	
	/**
	 * Update the moderation button
	 */	
	updateModButton: function( )
	{
		if( $('mod_submit') )
		{
			if( ipb.gallery.totalChecked == 0 ){
				$('mod_submit').disabled = true;
			} else {
				$('mod_submit').disabled = false;
			}
		
			$('mod_submit').value = ipb.lang['with_selected'].replace('{num}', ipb.gallery.totalChecked);
		}
	},
	
	/**
	 * Check all the files in this form
	 */			
	checkAllInForm: function(e)
	{
		selectedTopics	= $F('selectedimgids').split(',').compact();
		remove			= new Array();
		
		check	= Event.findElement(e, 'input');
		toCheck	= $F(check);
		form	= check.up('form');
		
		form.select('.image_mod').each( function(check){
			if( toCheck != null )
			{
				selectedTopics.push( check.id.replace('img_', '') );
				check.checked = true;
			}
			else
			{
				remove.push( check.id.replace('img_', '') );
				check.checked = false;
			}
		});
		
		selectedTopics = selectedTopics.uniq().without( remove ).join(',');		
		ipb.Cookie.set('modimgids', selectedTopics, 0);
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
				$('unhide_post_' + id).observe('click', ipb.gallery.showHiddenComment );
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

ipb.gallery.init();