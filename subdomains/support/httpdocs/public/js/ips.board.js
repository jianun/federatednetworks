/************************************************/
/* IPB3 Javascript								*/
/* -------------------------------------------- */
/* ips.board.js - Board index code				*/
/* (c) IPS, Inc 2008							*/
/* -------------------------------------------- */
/* Author: Rikki Tissier						*/
/************************************************/

var _idx = window.IPBoard;

_idx.prototype.board = {
	_statusClick: 0,
	_statusDefaultValue: '',
	
	/*------------------------------*/
	/* Constructor 					*/
	init: function()
	{
		Debug.write("Initializing ips.board.js");
		
		document.observe("dom:loaded", function(){
			ipb.board.setUpForumTables();
			ipb.board.initSidebar();
			
			ipb.board._statusDefaultValue = ( $('hookStatusTextField') ) ? $F('hookStatusTextField') : '';
			if( $('updateStatusForm') ){
				$('updateStatusForm').observe( 'submit', ipb.board.statusHookSubmit );
			}
			
		});
	},
	
	/* ------------------------------ */
	/**
	 * Hook: Status update
	*/
	statusHookClick: function()
	{
		if ( ! ipb.board._statusClick )
		{
			if( $('hookStatusTextField') )
			{
				$('hookStatusTextField').value = '';
				//$('hookStatusTextField').setStyle( { color: 'black' } );
			}
			
			ipb.board._statusClick = 1;
		}
	},
	
	/* ------------------------------ */
	/**
	 * Hook: Status update
	*/
	statusHookSubmit: function(e)
	{
		Event.stop(e);
		
		if ( ipb.board._statusClick && $F('hookStatusTextField') && $F('hookStatusTextField') != ipb.board._statusDefaultValue )
		{ 
			$('updateStatusForm').submit();
		}
		else
		{
			return false;
		}
	},

	/* ------------------------------ */
	/**
	 * Inits the forum tables ready for collapsing
	*/
	setUpForumTables: function()
	{
		$$('.ipb_table').each( function(tab){
			var tmp = $( tab ).wrap( 'div' );
			$( tmp ).addClassName('table_wrap');
		});
		
		$$('.category_block').each( function(cat){
			$(cat).select('.toggle')[0].observe( 'click', ipb.board.toggleCat );			
		});	
		
		cookie			= ipb.Cookie.get('toggleCats');
		
		if( cookie )
		{
			var cookies		= cookie.split( ',' );
			var newCookies	= new Array();
			var resetCookie	= false;
			
			//-------------------------
			// Little fun for you...
			//-------------------------
			
			for( var abcdefg=0; abcdefg < cookies.length; abcdefg++ )
			{
				if( cookies[ abcdefg ] )
				{
					if( $( 'category_' + cookies[ abcdefg ] ) )
					{
						var wrapper	= $( 'category_' + cookies[ abcdefg ] ).up('.category_block').down('.table_wrap');
						
						wrapper.hide();
						$( 'category_' + cookies[ abcdefg ] ).addClassName('collapsed');
						
						newCookies.push( cookies[ abcdefg ] );
					}
					else
					{
						resetCookie	= true;
					}
				}
			}
			
			if( resetCookie )
			{
				ipb.Cookie.set( 'toggleCats', newCookies.join( ',' ) );
			}
		}
	},
	
	/* ------------------------------ */
	/**
	 * Show/hide a category
	 * 
	 * @var		{event}		e	The event
	*/
	toggleCat: function(e)
	{
		if( ipb.board.animating ){ return false; }
		
		
		var click = Event.element(e);
		var remove = $A();
		var wrapper = $( click ).up('.category_block').down('.table_wrap');
		Debug.write( wrapper );
		$( wrapper ).identify(); // IE8 fix
		catname = $( click ).up('h3');
		var catid = catname.id.replace('category_', '');
		
		ipb.board.animating = true;
		
		// Get cookie
		cookie = ipb.Cookie.get('toggleCats');
		if( cookie == null ){
			cookie = $A();
		} else {
			cookie = cookie.split(',');
		}
		
		Effect.toggle( wrapper, 'blind', {duration: 0.4, afterFinish: function(){ ipb.board.animating = false; } } );
		
		if( catname.hasClassName('collapsed') )
		{
			catname.removeClassName('collapsed');
			remove.push( catid );
		}
		else
		{
			new Effect.Morph( $(catname), {style: 'collapsed', duration: 0.4, afterFinish: function(){
				$( catname ).addClassName('collapsed');
				ipb.board.animating = false;
			} });
			cookie.push( catid );
		}
		
		cookie = "," + cookie.uniq().without( remove ).join(',') + ",";
		
		ipb.Cookie.set('toggleCats', cookie, 1);
		
		Event.stop( e );
	},
	
	/* ------------------------------ */
	/**
	 * Sets up the sidebar
	*/
	initSidebar: function()
	{
		if( !$('index_stats') )
		{
			return false;
		}

		if( $('index_stats').visible() )
		{
			Debug.write("Stats are visible");
			$('open_sidebar').hide();
			$('close_sidebar').show();
			
			ipb.board.setUpSideBarBlocks();
		}
		else
		{
			Debug.write("Stats aren't visible");
			$('open_sidebar').show();
			$('close_sidebar').hide();
		}
		
		ipb.board.animating = false;
		
		if( $('close_sidebar') )
		{
			$('close_sidebar').observe('click', function(e){
				if( ipb.board.animating ){ Event.stop(e); return; }
				
				ipb.board.animating = true;		
				new Effect.Fade( $('index_stats'), {duration: 0.4, afterFinish: function(){
					new Effect.Morph( $('categories'), { style: 'no_sidebar', duration: 0.4, afterFinish: function(){
						ipb.board.animating = false;
					 } } );
				} } );
				
				Event.stop(e);
				$('close_sidebar').toggle();
				$('open_sidebar').toggle();
				ipb.Cookie.set('hide_sidebar', '1', 1);
			});
		}
		if( $('open_sidebar') )
		{
			$('open_sidebar').observe('click', function(e){
				if( ipb.board.animating ){ Event.stop(e); return; }
				
				ipb.board.animating = true;
				
				new Effect.Morph( $('categories'), { style: 'with_sidebar', duration: 0.4, afterFinish: function(){
					$('categories').removeClassName('with_sidebar').removeClassName('no_sidebar');
					new Effect.Appear( $('index_stats'), { duration: 0.4, queue: 'end', afterFinish: function(){
						ipb.board.animating = false; $('index_stats').show(); ipb.board.setUpSideBarBlocks();
				 	} } );
				} } );
				
				Event.stop(e);
				$('close_sidebar').toggle();
				$('open_sidebar').toggle();
				
				/* Bug fix */
				if ( Prototype.Browser.Chrome )
				{
					setTimeout( "\$('index_stats').show()", 300 );
				}
				
				ipb.Cookie.set('hide_sidebar', '0', 1);
			});
		}
	},
	
	/**
	 * Add in collapsable icons
	 */
	setUpSideBarBlocks: function()
	{
		if( $('index_stats').visible() && ! $$('.index_stats_collapse').length )
		{
			$$('#index_stats h3').each( function(h3)
			{
				$( h3 ).identify();
				
				/* Set title - fugly 
				   Here we run through escape first to change foreign chars to %xx with xx being latin, and then we remove % after */
				title = escape( $( h3 ).innerHTML.replace( /<(\/)?([^>]+?)>/g, '' ) ).replace( /%/g, '' );
				title = title.replace( / /g, '' ).replace( /[^a-zA-Z0-9]/, '' ).toLowerCase();

				$( h3 ).up('div').addClassName( '__xX' + title );
				
				/* insert the image */
				$( h3 ).update( '<a href="#" class="index_stats_collapse open">-</a>' + h3.innerHTML );
			});
			
			cookie = ipb.Cookie.get('toggleSBlocks');
		
			if( cookie )
			{
				var cookies = cookie.split( ',' );
				
				for( var abcdefg=0; abcdefg < cookies.length; abcdefg++ )
				{
					if( cookies[ abcdefg ] )
					{
						var top     = $('index_stats').down('.__xX' + cookies[ abcdefg ] );
						
						if ( top )
						{
							var wrapper	= top.down('._sbcollapsable');
							
							if ( ! wrapper )
							{
								wrapper = top.down('ul');
							}
							
							if ( ! wrapper )
							{
								wrapper = top.down('ol');
							}
							
							if ( ! wrapper )
							{
								wrapper = top.down('div');
							}
							
							if ( ! wrapper )
							{
								wrapper = top.down('table');
							}

							if ( wrapper )
							{
								if ( top.hasClassName('alt') )
								{
									top._isAlt = true;
									top.removeClassName('alt');
								}
					
								wrapper.hide();
							}
							
							top.down('.index_stats_collapse').removeClassName('open').addClassName('close');
						}
					}
				}
			}
		}
		
		ipb.delegate.register(".index_stats_collapse", ipb.board.toggleSideBarBlock);
		
	},
	
	/**
	 * Toggle the block
	 */
	toggleSideBarBlock: function( e, elem )
	{
		Event.stop(e);
		elem.identify();
		
		var remove = $A();
		cookie = ipb.Cookie.get('toggleSBlocks');
		
		if( cookie == null )
		{
			cookie = $A();
		} 
		else 
		{
			cookie = cookie.split(',');
		}
		
		/* Test for known class name */
		var top   = elem.up('div');
		
		moo   = top.className.match('__xX([0-9A-Za-z]+)');
		topId = moo[1]; 
		
		block = top.down('._sbcollapsable');
		
		if ( ! $( block ) )
		{
			block = elem.up('div').down('ul');
		}
		
		if ( ! $( block ) )
		{
			block = elem.up('div').down('ol');
		}
		
		if ( ! $( block ) )
		{
			block = elem.up('div').down('div');
		}
		
		if ( ! $( block ) )
		{
			block = elem.up('div').down('table');
		}
		
		if ( $( block ) )
		{
			$( block ).identify();
			
			ipb.board.animating = true;
			
			if ( $( block ).visible() )
			{
				if ( $( top ).hasClassName('alt') )
				{
					$( top )._isAlt = true;
					$( top ).removeClassName('alt');
				}
				
				top.down('.index_stats_collapse').removeClassName('open').addClassName('close');
				
				Debug.write( "Adding " + topId );
				cookie.push( topId );
			}
			else
			{
				if ( $( top )._isAlt )
				{
					$( top ).addClassName('alt');
				}
				
				top.down('.index_stats_collapse').removeClassName('close').addClassName('open');
				
				Debug.write( "Removing " + topId );
				remove.push( topId );
			}
		
			Effect.toggle( block, 'blind', {duration: 0.4, afterFinish: function(){ ipb.board.animating = false; } } );
		}
		
		cookie = "," + cookie.uniq().without( remove ).join(',') + ",";
		
		ipb.Cookie.set('toggleSBlocks', cookie, 1);

	},
	
	/**
	 * Check for DST
	 */
	checkDST: function()
	{
		var memberHasDst	= ipb.vars['dst_on'];
		var dstInEffect		= new Date().getDST();

		if( memberHasDst - dstInEffect != 0 )
		{
			var url = ipb.vars['base_url'] + 'app=members&module=ajax&section=dst&md5check='+ipb.vars['secure_hash'];
			
			new Ajax.Request(	url,
								{
									method: 'get',
									onSuccess: function(t)
									{
										// We don't need to do anything about this..
										return true;
									}
								}
							);
		}
	}
}

ipb.board.init();