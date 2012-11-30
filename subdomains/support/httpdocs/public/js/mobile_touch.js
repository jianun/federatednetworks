document.observe("dom:loaded", function() 
{
	$$("#user_navigation a").each( function(e) { e.setStyle( { 'display' : 'block', 'font-size': '1.4em' } ) } );	
	$$("#secondary_navigation a").each( function(e) { Event.observe(e, "click", loadUrl ); } );	
	$$(".touch-row").each(function(e) { Event.observe(e, "click", touchRowClick); addArrow(e); });
	$$(".post").each(function(e) { Event.observe(e, "click", postClick); });
	Event.observe($('options-button'), "click", openNavigation);
	if($('filter-option')) {Event.observe($('filter-option'), "click", openFilter);}
	$('options-button').setStyle({'display': 'block'});
	
	/* Set this here to 'toggle' works later */
	$('shade').setStyle({'display': 'none'});
	
	if ( $('filter-letters') )
	{
		$('filter-letters').toggleClassName('hidden');
	}
});

/**
 * Add the touch arrow */
function addArrow(e)
{
	d = e.getDimensions();
	t = ( d.height / 2 ) - 12;
	
	if ( ! e.inspect().match( '<h2' ) )
	{
		e.insert( { 'top' : new Element( 'div', { 'class': 'touch-row-arrow', 'style': 'margin-top:' + t + 'px !important' } ) } );
	}
}

function touchRowClick()
{
	$$('#' + this.id + ' a.title').each(function(e) { loadUrl( e ) });
}

function loadUrl( e )
{
	/* Show loading box */
	var content = LOADING_TEMPLATE.evaluate();
	
	$('ipbwrapper').insert( { 'after' : content } );
	positionCenter( $('loadingBox') );
	
	window.location = e.href;
}

function postClick()
{
	$(this.id + '-controls').toggleClassName('visible');
}

function openNavigation()
{
	vp = document.viewport.getDimensions();
	
	$('user_navigation').toggle();
	$('user_navigation').setStyle( { 'position': 'absolute', 'width': ( vp.width - 20 ) + 'px' } );
	$('options-button').update($('user_navigation').visible() ? '&laquo;' : '&raquo;');
	$('shade').toggle();
}

function openFilter()
{
	if ( $('filter-letters') )
	{
		$('filter-letters').toggleClassName('hidden');
	}
	
	$('filter-option').setStyle({'display': 'none'});
}

function positionCenter( elem, dir )
{
	if( !$(elem) ){ return; }
	elem_s = $(elem).getDimensions();
	window_s = document.viewport.getDimensions();
	window_offsets = document.viewport.getScrollOffsets();

	center = { 	left: ((window_s['width'] - elem_s['width']) / 2),
				 top: ((window_s['height'] - elem_s['height']) / 2)
			}

	if ( window_offsets['top'] )
	{
		center['top'] += window_offsets['top'];
	}
	
	if( typeof(dir) == 'undefined' || ( dir != 'h' && dir != 'v' ) )
	{
		$(elem).setStyle('top: ' + center['top'] + 'px; left: ' + center['left'] + 'px');
	}
	else if( dir == 'h' )
	{
		$(elem).setStyle('left: ' + center['left'] + 'px');
	}
	else if( dir == 'v' )
	{
		$(elem).setStyle('top: ' + center['top'] + 'px');
	}
	
	$(elem).setStyle('position: fixed');
}