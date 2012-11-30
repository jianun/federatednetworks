/************************************************/
/* IPB3 Javascript								*/
/* -------------------------------------------- */
/* ips.facebook.js - Facebook Connect code		*/
/* (c) IPS, Inc 2008							*/
/* -------------------------------------------- */
/* Author: Matt Mecham, Rikki Tissier			*/
/************************************************/

var _fb = window.IPBoard;

_fb.prototype.facebook = {
	api: '',
	linkedMember: {},
	mem_fb_uid: 0,
	
	/*------------------------------*/
	/* Constructor 					*/
	init: function()
	{
		Debug.write("Initializing ips.facebook.js");
		
		//document.observe("dom:loaded", function(){
			
		//});
	},
	
	/**
	* Loads the URL to remove the app
	*
	*/
	usercp_remove: function()
	{
		window.location = ipb.vars['base_url'] + 'app=core&module=usercp&tab=members&area=facebookRemove&do=custom&secure_key=' + ipb.vars['secure_hash'];
	}
	
}

ipb.facebook.init();