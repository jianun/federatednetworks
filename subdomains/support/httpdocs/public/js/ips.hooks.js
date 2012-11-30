/************************************************/
/* IPB3 Javascript								*/
/* -------------------------------------------- */
/* ips.forums.js - Forum view code				*/
/* (c) IPS, Inc 2008							*/
/* -------------------------------------------- */
/* Author: Rikki Tissier						*/
/************************************************/

var _hooks = window.IPBoard;

_hooks.prototype.hooks = {
	activeTab: 'forums',

	init: function()
	{
		Debug.write("Initializing ips.hooks.js");
		
		document.observe("dom:loaded", function(){
			ipb.hooks.initEvents();
		});
	},

	initEvents: function()
	{
		ipb.global.initTabs();
		
		if( $('more-watched-forums') )
		{
			$('more-watched-forums').observe('click', ipb.hooks.toggleWatchedForums );
		}
		
		if( $('more-watched-topics') )
		{
			$('more-watched-topics').observe('click', ipb.hooks.toggleWatchedTopics );
		}
	},
	
	toggleWatchedForums: function(e)
	{
		Event.stop(e);
		
		$('more-watched-forums-container').toggle();
	},
	
	toggleWatchedTopics: function(e)
	{
		Event.stop(e);
		
		$('more-watched-topics-container').toggle();
	}
						
	
}
ipb.hooks.init();
