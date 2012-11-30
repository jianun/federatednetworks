/************************************************/
/* IPB3 Javascript								*/
/* -------------------------------------------- */
/* ips.status.js - Status  management code		*/
/* (c) IPS, Inc 2008							*/
/* -------------------------------------------- */
/* Author: Matt Mecham							*/
/************************************************/

var _status = window.IPBoard;

_status.prototype.status = {
	_updateClicked: false,
	maxReplies: 0,
	smallSpace: 0,
	myLatest: 0,
	init: function()
	{
		Debug.write("Initializing ips.status.js");
		
		document.observe("dom:loaded", function(){
			ipb.status.initEvents();
		});
	},
	
	/*!! initEvents */
	initEvents: function()
	{
		Debug.write( 'Status init' );
		/* Show all comments link */
		ipb.delegate.register(".__showAll", ipb.status.showAllComments);
		
		/* Links for showing reply box */
		ipb.delegate.register(".__showform", ipb.status.showForm);
		
		/* Submit button for replies */
		$$('.__submit').each( function(suBo)
		{
			id = suBo.identify();
			
			$(id).observe( 'click', ipb.status.addReply.bindAsEventListener( this, id.replace( 'statusSubmit-', '' ) ) );
		});
		
		/* Delete comment link */
		ipb.delegate.register(".__sDR", ipb.status.deleteReply);
		
		/* Delete status link */
		ipb.delegate.register(".__sD", ipb.status.deleteStatus);
		
		/* Lock status link */
		ipb.delegate.register(".__sL", ipb.status.lockStatus);
		
		/* Unlock status link */
		ipb.delegate.register(".__sU", ipb.status.unlockStatus);
		
		/* Delete status link */
		ipb.delegate.register(".__sT", ipb.status.showFeedback);
		
		/* Delete status link */
		ipb.delegate.register(".__sTO", ipb.status.hideFeedback);
		
		/* Can we update our status? */
		if ( $('statusUpdate') )
		{
			ipb.status._updateClicked = false;
			
			$('statusUpdate').stopObserving( 'click' );
			$('statusUpdate').stopObserving( 'keypress' );
			$('statusSubmit').stopObserving( 'click' );
			
			$('statusUpdate').value = ipb.lang['prof_update_default'];
			
			$('statusUpdate').addClassName('status_inactive');
			
			if ( ! ipb.status.smallSpace )
			{
				$('statusSubmit').value = ipb.lang['prof_update_button'];
				$('statusSubmit').addClassName('status_smallspace');
			}
			
			$('statusUpdate').observe( 'click', ipb.status.updateUpdateText.bindAsEventListener( this ) );
			$('statusUpdate').observe( 'keypress', ipb.global.checkForEnter.bindAsEventListener( this, ipb.status.updateSubmit ) );
			$('statusSubmit').observe( 'click', ipb.status.updateSubmit.bindAsEventListener( this ) );
		}
	},
	
	showForm: function(e, elem)
	{
		Event.stop(e);
		
		var id = $( elem ).id.replace('statusReplyFormShow-', '');
		if( Object.isUndefined( id ) || !$('statusReplyForm-' + id) ){ return; }
		
		$(elem).hide();
		$('statusReplyForm-' + id).show();
		$('statusText-' + id).focus();
	},
	
	/*!! deleteStatus */
	/* result of clicking "delete" on a status */
	updateUpdateText: function(e)
	{
		if ( ipb.status._updateClicked )
		{
			return;
		}
		else
		{
			ipb.status._updateClicked = true;
			$('statusUpdate').value = '';
			$('statusUpdate').removeClassName('status_inactive');
		}
	},
	
	/*!! updateSubmit */
	/* Add a sexy ajax status" */
	updateSubmit: function(e)
	{
		Event.stop(e);
		
		if ( $('statusUpdate' ).value.length < 2 || $('statusUpdate').value == ipb.lang['prof_update_default'] )
		{
			return false;
		}
		
		var su_Twitter  = $('su_Twitter') && $('su_Twitter').checked ? 1  : 0;
		var su_Facebook = $('su_Facebook') && $('su_Facebook').checked ? 1 : 0;
		
		new Ajax.Request( ipb.vars['base_url'] + "app=members&section=status&module=ajax&do=new&md5check=" + ipb.vars['secure_hash'] + '&smallSpace=' + ipb.status.smallSpace,
						{
							method: 'post',
							evalJSON: 'force',
							parameters: {
								content: $('statusUpdate' ).value.encodeParam(),
								su_Twitter: su_Twitter,
								su_Facebook: su_Facebook
							},
							onSuccess: function(t)
							{
								if( Object.isUndefined( t.responseJSON ) )
								{
									alert( ipb.lang['action_failed'] );
									return;
								}
								
								if( t.responseJSON['error'] )
								{
									alert( t.responseJSON['error'] );
								}
								else
								{
									try {
										$('status_wrapper').innerHTML = t.responseJSON['html'] + $('status_wrapper').innerHTML;
										
										/* Showing latest only? */
										if ( ipb.status.myLatest )
										{
											if ( $('statusWrap-' + ipb.status.myLatest ) )
											{
												$('statusWrap-' + ipb.status.myLatest ).hide();
											}
										}
										
										/* Need to blur out of field
											@link	http://community.invisionpower.com/tracker/issue-21358-small-input-field-behavior-issue-after-updating-status/
										*/
										$('statusUpdate' ).blur();
										
										/* Re-init events */
										ipb.status.initEvents();
									}
									catch(err)
									{
										Debug.error( 'Logging error: ' + err );
									}
								}
							}
						});
	},
	
	/*!! deleteStatus */
	/* result of clicking "delete" on a status */
	deleteStatus: function(e, elem)
	{
		Event.stop(e);
		
		if ( ! confirm( ipb.lang['delete_confirm'] ) )
		{
			return false;
		}
		
		var status = $( elem ).className.match('__d([0-9]+)');
		if( status == null || Object.isUndefined( status[1] ) ){ Debug.error("Error showing all comments"); return; }
		var status_id = status[1];
		
		new Ajax.Request( ipb.vars['base_url'] + "app=members&section=status&module=ajax&do=deleteStatus&status_id=" + status_id + "&md5check=" + ipb.vars['secure_hash'],
						{
							method: 'get',
							onSuccess: function(t)
							{
								if( Object.isUndefined( t.responseJSON ) || t.responseJSON.error )
								{
									alert( ipb.lang['action_failed'] );
									return;
								}
								else
								{
									$('statusWrap-' + status_id ).remove();
								}
							}
						});
	},
	
	/*!! showFeedback */
	/* result of clicking "lock" on a status */
	showFeedback: function(e, elem)
	{
		Event.stop(e);
		
		var status = $( elem ).className.match('__t([0-9]+)');
		if( status == null || Object.isUndefined( status[1] ) ){ Debug.error("Error"); return; }
		var status_id = status[1];
		
		//$('statusWrap-' + status_id ).addClassName('rowdark');
		if( $('statusReplyFormShow-' + status_id ) )
		{
			$('statusReplyFormShow-' + status_id ).hide();
		}
		
		if( $('statusReplyForm-' + status_id ) )
		{
			$('statusReplyForm-' + status_id ).show();
		}
		
		$('statusFeedback-' + status_id ).show();
		$('statusToggle-' + status_id ).hide();
		$('statusToggleOff-' + status_id ).show();
		
		if( $('statusText-' + status_id ) )
		{
			$('statusText-' + status_id ).focus();
		}
	},
	
	/*!! hideFeedback */
	/* result of clicking "lock" on a status */
	hideFeedback: function(e, elem)
	{
		Event.stop(e);
		
		var status = $( elem ).className.match('__to([0-9]+)');
		if( status == null || Object.isUndefined( status[1] ) ){ Debug.error("Error"); return; }
		var status_id = status[1];
		
		//$('statusWrap-' + status_id ).removeClassName('rowdark');
		$('statusFeedback-' + status_id ).hide();
		$('statusToggle-' + status_id ).show();
		$('statusToggleOff-' + status_id ).hide();
	},

	
	/*!! lockStatus */
	/* result of clicking "lock" on a status */
	lockStatus: function(e, elem)
	{
		Event.stop(e);
		
		if ( ! confirm( ipb.lang['delete_confirm'] ) )
		{
			return false;
		}
		
		var status = $( elem ).className.match('__l([0-9]+)');
		if( status == null || Object.isUndefined( status[1] ) ){ Debug.error("Error"); return; }
		var status_id = status[1];
		
		new Ajax.Request( ipb.vars['base_url'] + "app=members&section=status&module=ajax&do=lockStatus&status_id=" + status_id + "&md5check=" + ipb.vars['secure_hash'],
						{
							method: 'get',
							onSuccess: function(t)
							{
								if( Object.isUndefined( t.responseJSON ) || t.responseJSON.error )
								{
									alert( ipb.lang['action_failed'] );
									return;
								}
								else
								{
									$('statusUnlock-' + status_id ).show();
									$('statusLock-' + status_id ).hide();
									$('statusLockImg-' + status_id ).show();
									
								}
							}
						});
	},
	
	/*!! unlockStatus */
	/* result of clicking "unlock" on a status */
	unlockStatus: function(e, elem)
	{
		Event.stop(e);
		
		if ( ! confirm( ipb.lang['delete_confirm'] ) )
		{
			return false;
		}
		
		var status = $( elem ).className.match('__u([0-9]+)');
		if( status == null || Object.isUndefined( status[1] ) ){ Debug.error("Error"); return; }
		var status_id = status[1];
		
		new Ajax.Request( ipb.vars['base_url'] + "app=members&section=status&module=ajax&do=unlockStatus&status_id=" + status_id + "&md5check=" + ipb.vars['secure_hash'],
						{
							method: 'get',
							onSuccess: function(t)
							{
								if( Object.isUndefined( t.responseJSON ) || t.responseJSON.error )
								{
									alert( ipb.lang['action_failed'] );
									return;
								}
								else
								{
									$('statusUnlock-' + status_id ).hide();
									$('statusLock-' + status_id ).show();
									$('statusLockImg-' + status_id ).hide();
								}
							}
						});
	},
	
	/*!! deleteReply */
	/* result of clicking "delete" on a comment */
	deleteReply: function(e, elem)
	{
		Event.stop(e);
		
		if ( ! confirm( ipb.lang['delete_confirm'] ) )
		{
			return false;
		}
		
		var status = $( elem ).className.match('__dr([0-9]+)-([0-9]+)');
		if( status == null || Object.isUndefined( status[1] ) ){ Debug.error("Error showing all comments"); return; }
		var status_id = status[1];
		var reply_id  = status[2];
		
		new Ajax.Request( ipb.vars['base_url'] + "app=members&section=status&module=ajax&do=deleteReply&status_id=" + status_id + "&reply_id=" + reply_id + "&md5check=" + ipb.vars['secure_hash'],
						{
							method: 'get',
							onSuccess: function(t)
							{
								if( Object.isUndefined( t.responseJSON ) || t.responseJSON.error )
								{
									alert( ipb.lang['action_failed'] );
									return;
								}
								else
								{
									$('statusReply-' + reply_id ).remove();
								}
							}
						});
	},


	/*!! showAllComments */
	/* result of clicking "show all X comments" */
	showAllComments: function(e, elem)
	{
		Event.stop(e);
		
		var status = $( elem ).className.match('__x([0-9]+)');
		if( status == null || Object.isUndefined( status[1] ) ){ Debug.error("Error showing all comments"); return; }
		var status_id = status[1];
		
		new Ajax.Request( ipb.vars['base_url'] + "app=members&section=status&module=ajax&do=showall&status_id=" + status_id + "&md5check=" + ipb.vars['secure_hash'],
						{
							method: 'get',
							onSuccess: function(t)
							{
								if( Object.isUndefined( t.responseJSON ) )
								{
									alert( ipb.lang['action_failed'] );
									return;
								}
								
								if ( t.responseJSON['error'] )
								{
									alert( t.responseJSON['error'] );
								}
								else
								{
									try {
										$('statusMoreWrap-' + status_id ).hide();
										$('statusReplies-' + status_id ).update( t.responseJSON['html'] );
										
										if ( t.responseJSON['status_replies'] > 20 )
										{
											$('statusReplies-' + status_id ).addClassName('status_replies_many');
										}
									}
									catch(err)
									{
										Debug.error( err );
									}
								}
							}
						});
	},
	
	/*!! addReply */
	/* Add a sexy ajax reply" */
	addReply: function(e, status_id)
	{
		Event.stop(e);
		
		if ( $('statusText-' + status_id ).value.length < 2 )
		{
			return false;
		}
		
		new Ajax.Request( ipb.vars['base_url'] + "app=members&section=status&module=ajax&do=reply&status_id=" + status_id + "&md5check=" + ipb.vars['secure_hash'],
						{
							method: 'post',
							evalJSON: 'force',
							parameters: {
								content: $('statusText-' + status_id ).value.encodeParam()
							},
							onSuccess: function(t)
							{
								if( Object.isUndefined( t.responseJSON ) )
								{
									alert( ipb.lang['action_failed'] );
									return;
								}
								
								if ( t.responseJSON['error'] )
								{
									alert( t.responseJSON['error'] );
								}
								else
								{
									try {
										/* Already have replies, add them there so the overflow block looks correct */
										if ( $('statusReplies-' + status_id ) )
										{
											$('statusReplies-' + status_id ).innerHTML += t.responseJSON['html'];
										}
										else
										{
											$( 'statusReplyBlank-' + status_id ).innerHTML += t.responseJSON['html'];
										}
										
										$('statusText-' + status_id ).value = '';
										
										if ( t.responseJSON['status_replies'] && ipb.status.maxReplies && t.responseJSON['status_replies'] >= ipb.status.maxReplies )
										{
											$('statusMaxWrap-' + status_id ).show();
											$('statusReply-' + status_id).hide();
										}
										
									}
									catch(err)
									{
										Debug.error( err );
									}
								}
							}
						});
	}
}

ipb.status.init();
