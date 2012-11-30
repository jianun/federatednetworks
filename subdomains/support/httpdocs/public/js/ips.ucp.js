/************************************************/
/* IPB3 Javascript								*/
/* -------------------------------------------- */
/* ips.ucp.js - Topic view code				*/
/* (c) IPS, Inc 2008							*/
/* -------------------------------------------- */
/* Author: Rikki Tissier						*/
/************************************************/


var _ucp = window.IPBoard;

_ucp.prototype.ucp = {
	
	init: function()
	{
		Debug.write("Initializing ips.ucp.js");
		
		document.observe("dom:loaded", function(){
			if( $('avatarCategory') )
			{
				ipb.ucp.initAvatarGallery();
			}
			
			if ( $('userCPForm') && $('userCPForm').action.match( "area=photo" ) )
			{
				$('userCPForm').observe( 'submit', ipb.ucp.photoFormSubmit );
			}
		} );
	},
	
	/**
	 * Photo form submit
	 */
	photoFormSubmit: function(e)
	{
		/* Have we got a form value? */
		if ( $('upload_photo') && ! $F('upload_photo') )
		{
			Event.stop(e);
			alert( ipb.lang['usercp_photo_upload'] );
			return false;
		}
	},
	
	initAvatarGallery: function()
	{
		// set event
		$('avatarCategory').observe('change', ipb.ucp.updateAvatarGallery);
	},
	
	updateAvatarGallery: function(e)
	{
		var catid = $F('avatarCategory');
		if( Object.isUndefined( catid ) || catid.blank() || catid == 'none' ){ return; }
		
		var url = ipb.vars['base_url'] + "app=forums&module=ajax&section=usercp&do=get_avatar_images&cat=" + catid + "&secure_key=" + ipb.vars['secure_hash'];
		
		new Ajax.Request( url.replace(/&amp;/, '&'),
		 				{
							method: 'get',
							onSuccess: function(t)
							{
								if( t.responseText != 'error' )
								{
									$('avatarImageContainer').update( t.responseText ).show();
								}
								
							}
						});
	},
	
	deleteAnnouncement: function(e, id)
	{
		if( !confirm(ipb.lang['delete_confirm']) )
		{
			Event.stop(e);
		}
	}
};

ipb.ucp.init();