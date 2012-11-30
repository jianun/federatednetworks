/************************************************/
/* IPB3 Javascript								*/
/* -------------------------------------------- */
/* ips.twitter.js - Twitter Connect code		*/
/* (c) IPS, Inc 2010							*/
/* -------------------------------------------- */
/* Author: Matt Mecham, Rikki Tissier			*/
/************************************************/

var _tw = window.IPBoard;

_tw.prototype.twitter = {
	_popUp: null,
	
	/*------------------------------*/
	/* Constructor 					*/
	init: function()
	{
		Debug.write("Initializing ips.twitter.js");
		
		//document.observe("dom:loaded", function(){
			
		//});
	},
	
	initUserCP: function()
	{
		$('tc_remove').observe( 'click', ipb.twitter.usercp_remove );
	},
	
	/**
	* Loads the URL to remove the app
	*
	*/
	usercp_remove: function()
	{
		window.location = ipb.vars['base_url'] + 'app=core&module=usercp&tab=members&area=twitterRemove&do=custom&secure_key=' + ipb.vars['secure_hash'];
	}
}

ipb.twitter.init();