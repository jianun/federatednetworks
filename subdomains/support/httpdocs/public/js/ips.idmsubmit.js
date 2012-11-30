/************************************************/
/* IPB3 Javascript								*/
/* -------------------------------------------- */
/* ips.idmsubmit.js - File uploads		*/
/* (c) IPS, Inc 2008							*/
/* -------------------------------------------- */
/* Author: Brandon Farber						*/
/* Original Author: Rikki Tissier				*/
/* Largely based on ips.idmsubmit.js				*/
/************************************************/
/* -TRUE- MULTIPLE ATTACHMENTS!!!				*/
/* -------------------------------------------- */

var _uploader = window.IPBoard;

_uploader.prototype.uploader = {
	uploaders: [],
	template: '',
	
	/*
	 * Generic initialization function
	 */
	init: function()
	{
		Debug.write( "Initializing ips.idmsubmit.js" );
	},

	/*
	 * Registers an upload object
	 */
	registerUploader: function( id, type, wrapper, options )
	{
		//-----------------------------------------
		// Must have an id
		//-----------------------------------------
		
		if( Object.isUndefined( id ) || id == null )
		{
			Debug.error( "ips.idmsubmit.js: No id passed" );
			return;
		}
		
		Debug.write( "ips.idmsubmit.js: Registering uploader with id " + id );
		
		//-----------------------------------------
		// Already exists?
		//-----------------------------------------
		
		if( ipb.uploader.uploaders[ id ] )
		{
			Debug.error( "ips.idmsubmit.js: This uploader has already been registered" );
		}
		
		//-----------------------------------------
		// Make object
		//-----------------------------------------
		
		if( type == 'swf' )
		{
			//-----------------------------------------
			// Filesize is passed in as KB
			//-----------------------------------------

			if( options.file_size_limit )
			{
				options.file_size_limit = options.file_size_limit + " KB";
			}

			uploader = new ipb.uploadSWF( id, options, wrapper, ipb.uploader.template );
		}
		else
		{
			uploader = new ipb.uploadTraditional( id, options, wrapper, ipb.uploader.template );
		}
		
		//-----------------------------------------
		// Store reference and hide fallbacks
		//-----------------------------------------
		
		if( uploader )
		{
			ipb.uploader.uploaders[ id ] = uploader;
			
			if( $('old_school_' + id ) )
			{
				$('old_school_' + id ).hide();
			}
		}		
	},
	
	/*
	 * Remove an uploaded file
	 */
	removeUpload: function(e)
	{
		//-----------------------------------------
		// Get the element
		//-----------------------------------------
		
		elem = Event.findElement( e, 'li' );
		elemid = elem.id.replace('cur_', '');
		elemid = elemid.match( /^ali_(.+?)_([0-9]+)$/ );
		
		if( !elemid[1] ){ return; }
		
		obj = ipb.uploader.uploaders[ elemid[1] ];
		attachid = elem.readAttribute('attachid').replace('cur_', '');
		
		//-----------------------------------------
		// Send request to remove upload
		//-----------------------------------------
		
		new Ajax.Request( ipb.vars['base_url'] + "app=downloads&module=post&section=files&do=remove&category=" + obj.options.category + "&post_key=" + 
						  obj.options['post_key'] + "&type=" + obj.id + "&record_id=" + attachid + '&secure_key=' + ipb.vars['secure_hash'],
						{
							method: 'post',
							evalJSON: 'force',
							onSuccess: function(t)
							{
								if( Object.isUndefined( t.responseJSON ) ){ alert( ipb.lang['action_failed'] ); return; }
								
								if( t.responseJSON.msg == 'attach_removed' )
								{
									ipb.uploader.uploaders[ elemid[1] ].removeUpload( elem.readAttribute('attachid'), elemid[2], t.responseJSON );
								}
								else
								{
									alert( ipb.lang['action_failed'] );
									return;
								}
							}
						});
		
		Event.stop(e);
	},
	
	/*
	 * Called when a file starts uploading
	 */
	startedUploading: function(handler)
	{
	},
	
	/*
	 * Called when a file finishes uploading
	 */
	finishedUploading: function( attachid, fileindex )
	{
	},

	/**
	 * Returns a reference to the uploader, or false if it doesn't exist
	 */
	getUploader: function( id )
	{
		if( ipb.uploader.uploaders[ id ] )
		{
			return ipb.uploader.uploaders[ id ];
		}
		else
		{
			return false;
		}
	},
	
	/**
	 * Returns a human-readable string for errors
	 */
	_determineServerError: function( msg )
	{
		if( msg.blank() ){ return ipb.lang['silly_server']; }
		
		if( !Object.isUndefined( ipb.lang[ msg ] ) )
		{
			return ipb.lang[ msg ];
		}
		else
		{
			return ipb.lang['silly_server'];
		}
	},
	
	/*
	 * Function to facilitate passing JSON to iframe
	 */
	_jsonPass: function( id, json )
	{
		ipb.uploader.uploaders[ id ].json = json;
		ipb.uploader.uploaders[ id ].isReady();
		
		Debug.write( "ips.idmsubmit.js: Got json back from iframe id " + id );
	},
	
	/*
	 * Builds boxes of attachments
	 */
	_buildBoxes: function( currentItems, obj )
	{
		//-----------------------------------------
		// Loop over each attachment
		//-----------------------------------------

		for( var i in currentItems )
		{
			Debug.write( "Templating item: " + currentItems[i][1] );
			
			id    = i;
			index = 'cur_' + currentItems[i][0];
			name  = currentItems[i][1];
			size  = currentItems[i][2];
			temp = obj.template.gsub(/\[id\]/, obj.id + '_' + index).gsub(/\[name\]/, name);

			//-----------------------------------------
			// Insert the list item
			//-----------------------------------------
		
			$( obj.wrapper ).insert( temp );

			$( 'ali_' + obj.id + '_' + index ).select('.info')[0].update( ipb.global.convertSize( size ) );
			$( 'ali_' + obj.id + '_' + index ).select('.progress_bar')[0].hide();

			new Effect.Appear( $( 'ali_' + obj.id + '_' + index ), { duration: 0.3 } );
			
			$( 'ali_' + obj.id + '_' + index ).writeAttribute( 'attachid', index );
			$( 'ali_' + obj.id + '_' + index ).select('.delete')[0].observe( 'click', ipb.uploader.removeUpload );

			['complete', 'in_progress', 'error'].each( function( cName ){ $( 'ali_' + obj.id + '_' + index ).removeClassName( cName ); }.bind( obj ) );

			$( 'ali_' + obj.id + '_' + index ).addClassName( 'complete' );
			
			if( currentItems[ i ][ 3 ] == 1 )
			{
				tmp = currentItems[ i ];

				var width = tmp[5];
				var height = tmp[6];

				if( ( tmp[5] && tmp[5] > 30 ) )
				{
					width = 30;
					factor = ( 30 / tmp[5] );
					height = tmp[6] * factor;
				}

				if( ( tmp[6] && tmp[6] > 30 ) )
				{
					height = 30;
					factor = ( 30 / tmp[5] );
					width = tmp[5] * factor;
				}

				//-----------------------------------------
				// Show thumbnail if it's an image
				//-----------------------------------------
		
				thumb = new Element('img', { src: tmp[4].replace( /&amp;/, '&' ), 'width': width, 'height': height, 'class': 'thumb_img' } ).hide();

				$( 'ali_' + obj.id + '_' + index ).select('.img_holder')[0].insert( thumb );
				new Effect.Appear( $( thumb ), { duration: 0.4 } );
			}
			
			obj.boxes[ obj.id + index ] = 'ali_' + obj.id + '_' + index;

			ipb.uploader.finishedUploading( obj.id, index );
		}
	}
}

ipb.uploader.init();

//==============================================================
// Traditional (iframe) uploads
//==============================================================

_uploader.prototype.uploadTraditional = Class.create({
	options		: [],
	boxes		: [],
	id			: '',
	wrapper		: '',
	template	: '',
	
	/*
	 * Initialization function
	 */
	initialize: function( id, options, wrapper, template )
	{
		this.id = id;
		this.wrapper = wrapper;
		this.template = template;
		this.options = options;
		
		if( !$( this.wrapper ) )
		{
			return;
		}
		
		//-----------------------------------------
		// Build the iframe
		//-----------------------------------------
		
		this.iframe = new Element('iframe', { 	id: 'iframeAttach_' + this.id,
		 										name: 'iframeAttach_' + this.id,
												scrolling: 'no',
												frameBorder: 'no',
												border: '0',
												className: '',
												allowTransparency: true,
												src: this.options.upload_url,
												tabindex: '1'
											}).setStyle({
												width: '500px',
												height: '50px',
												overflow: 'hidden',
												backgroundColor: 'transparent'
											});

		$( this.wrapper ).insert( { after: this.iframe } ).addClassName('traditional');
		
		$('add_files_' + this.id ).observe('click', this.processUpload.bindAsEventListener( this ) );		
	},

	/*
	 * Remove an uploaded file
	 */
	removeUpload: function( attachid, fileindex, fileinfo )
	{
		if( attachid.startsWith('cur_') )
		{
			fileindex = 'cur_' + fileindex;
		}
		
		//-----------------------------------------
		// Remove the box
		//-----------------------------------------
		
		new Effect.Fade( $( this.boxes[ this.id + fileindex ] ), { duration: 0.4 } );

		this.boxes[ this.id + fileindex ] = null;

		Debug.write( "ips.idmsubmit.js: (ID " + this.id + ", removeUpload) Attach ID: " + attachid + ", File index: " + fileindex );
	},
	
	/*
	 * Processes upload
	 */
	processUpload: function( e )
	{
		$( this.id + '_error_box').hide();
		var iFrameBox  = window.frames[ 'iframeAttach_' + this.id ].document.getElementById('iframeUploadBox_' + this.id );
		var iFrameForm = window.frames[ 'iframeAttach_' + this.id ].document.getElementById('iframeUploadForm_' + this.id );
		var box        = $('attach_' + this.id );
		
		iFrameForm.action = ipb.vars['base_url'] + "app=downloads&module=post&section=files&do=iframeUpload&post_key=" + 
						  	this.options['post_key'] + '&fetch_all=1&type=' + this.id + "&category=" + this.options.category;
		
		iFrameForm.submit();
	},
	
	/*
	 * Function triggered when the iframe is ready to use
	 */
	isReady: function()
	{
		if ( this.json )
		{
			if ( this.json['is_error'] )
			{
				$( this.id + '_error_box').update( ipb.lang['error'] + " <strong>" + ipb.uploader._determineServerError( this.json['msg'] ) + "</strong>" );
				$( this.id + '_error_box').show();
			}
			
			if ( this.json['current_items'] )
			{
				$( this.wrapper ).update();
				ipb.uploader._buildBoxes( this.json['current_items'], this );
			}
		}
		
		Debug.write( "ips.idmsubmit.js: iFrame is ready" );
	}
});


//==============================================================
// Flash uploader object
//==============================================================

_uploader.prototype.uploadSWF = Class.create({
	options		: [],
	boxes		: [],
	id			: '',
	template	: '',
	wrapper		: '',
	
	/*
	 * Initialization function
	 */
	initialize: function( id, options, wrapper, template )
	{
		this.id = id;
		this.wrapper = wrapper;
		this.template = template;

		//-----------------------------------------
		// Combine defaults with specified optiosn
		//-----------------------------------------
		
		this.options = Object.extend({
			upload_url: 				'',
			file_post_name: 			'FILE_UPLOAD',
			file_types:					'*.*',
			file_types_description:		ipb.lang['att_select_files'],
			file_size_limit: 			"10 MB",
			file_upload_limit: 			0,
			file_queue_limit: 			10,
			flash_color: 				ipb.vars['swf_bgcolor'] || '#FFFFFF',
			custom_settings: 			{},
			post_params: 				{ 's': ipb.vars['session_id'] }
		}, arguments[1] || {});
		
		if( this.options.upload_url.blank() ){ Debug.error( "(ID " + id + ") No upload URL" ); return false; }
		
		//-----------------------------------------
		// Update button text
		//-----------------------------------------
		
		try {
			$('add_files_' + this.id ).value = ipb.lang['swf_attach_selected' + this.id ];
		} catch(err) {
			Debug.write( err );
		}
		
		//-----------------------------------------
		// Setup SWFObject
		//-----------------------------------------
		
		try {
			var settings = {
				upload_url: 			this.options.upload_url,
				flash_url: 				ipb.vars['swfupload_swf'],
				file_post_name: 		this.options.file_post_name,
				file_types: 			this.options.file_types,
				file_types_description: this.options.file_types_description,
				file_size_limit: 		this.options.file_size_limit,
				file_upload_limit:  	this.options.file_upload_limit,
				file_queue_limit: 		this.options.file_queue_limit,
				custom_settings: 		this.options.custom_settings,
				post_params: 			this.options.post_params,
				debug: 					ipb.vars['swfupload_debug'],
				
				// ---- BUTTON SETTINGS ----
				button_placeholder_id: 			this.id + '_buttonPlaceholder',
				button_width: 					$('add_files_' + this.id ).getWidth(),
				button_height: 					30,
				button_window_mode: 			SWFUpload.WINDOW_MODE.TRANSPARENT,
				button_cursor: 					SWFUpload.CURSOR.HAND,
			
				// ---- EVENTS ---- 
				upload_error_handler: 			this._uploadError.bind(this),
				upload_start_handler: 			this._uploadStart.bind(this),
				upload_success_handler: 		this._uploadSuccess.bind(this),
				upload_complete_handler: 		this._uploadComplete.bind(this),
				upload_progress_handler: 		this._uploadProgress.bind(this),
				file_dialog_complete_handler: 	this._fileDialogComplete.bind(this),
				file_queue_error_handler: 		this._fileQueueError.bind(this),
				queue_complete_handler: 		this._queueComplete.bind(this),
				file_queued_handler: 			this._fileQueued.bind(this)
			}
			
			this.obj = new SWFUpload( settings );

			//-----------------------------------------
			// Get existing records
			//-----------------------------------------

			var getExisting	=	ipb.vars['base_url'] + "app=downloads&module=post&section=files&do=flash&category=" + this.options.category + "&post_key=" + 
				  				options['post_key'] + '&secure_key=' + ipb.vars['secure_hash'] + '&fetch_all=1&type=' + this.id;

			//-----------------------------------------
			// Send the AJAX request
			//-----------------------------------------
		
			var bindref	= this;
			
			new Ajax.Request( getExisting,
							{
								method: 'get',
								evalJSON: 'force',
								onSuccess: function(t)
								{
									if( Object.isUndefined( t.responseJSON ) ){ alert( ipb.lang['action_failed'] ); return; }

									if( t.responseJSON.current_items )
									{
										ipb.uploader._buildBoxes( t.responseJSON.current_items, this );
									}
								}.bind(bindref)
							});

			this.obj.onmouseover	= $('SWFUpload_0').focus();

			Debug.write( "ips.idmsubmit.js: (ID " + this.id + ") Created uploader");
			return true;
		}
		catch(e)
		{
			Debug.error( "ips.idmsubmit.js: (ID " + this.id + ") " + e );
			return false;
		}
	},

	/*
	 * Removes an upload from the list (note: actual removing of file is done in the wrapper object
	 */
	removeUpload: function( attachid, fileindex, fileinfo )
	{
		if( attachid.startsWith('cur_') )
		{
			fileindex = 'cur_' + fileindex;
		}
		
		//-----------------------------------------
		// Remove box
		//-----------------------------------------
		
		new Effect.Fade( $( this.boxes[ this.id + fileindex ] ), { duration: 0.4 } );

		this.boxes[ this.id + fileindex ] = null;

		Debug.write( "ips.idmsubmit.js: (ID " + this.id + ", removeUpload) Attach ID: " + attachid + ", File index: " + fileindex );
	},

	/*
	 * Sets a CSS class on the box depending on status
	 */
	_setStatus: function( file, type )
	{
		//-----------------------------------------
		// Remove any old statuses before setting new one
		//-----------------------------------------
		Debug.write( "Setting status to " + type + " for " + this.boxes[ this.id + file ] );
		['complete', 'in_progress', 'error'].each( function( cName ){ $( this.boxes[ this.id + file ] ).removeClassName( cName ); }.bind( this ) );
		
		$( this.boxes[ this.id + file ] ).addClassName( type );
	},
	
	/*
	 * Updates the info string for an upload
	 */
	_updateInfo: function( file, msg )
	{
		$( this.boxes[ this.id + file ] ).select('.info')[0].update( msg );
	},

	/*
	 * Returns a human-readable string for errors
	 */
	_determineServerError: function( msg )
	{
		if( msg.blank() ){ return ipb.lang['silly_server']; }
		
		if( !Object.isUndefined( ipb.lang[ msg ] ) )
		{
			return ipb.lang[ msg ];
		}
		else
		{
			return ipb.lang['silly_server'];
		}
	},

	/*
	 * Builds the list row for each upload
	 */
	_buildBox: function( file )
	{
		temp = this.template.gsub(/\[id\]/, this.id + '_' + file.index).gsub(/\[name\]/, file.name);
		this.boxes[ this.id + file.index ] = 'ali_' + this.id + '_' + file.index;

		$( this.wrapper ).insert( temp );
		
		new Effect.Appear( $( this.boxes[ this.id + file.index ] ), { duration: 0.3 } );
		this._updateInfo( file.index, ipb.global.convertSize( file.size ) + "bytes" );
	},
	
	/*
	 * The files in the queue finished uploading
	 */
	_queueComplete: function( numFiles )
	{
		Debug.write( "ips.idmsubmit.js: (ID " + this.id + ", " + numFiles + " finished uploading");
	},
	
	/*
	 * A file has been added to the queue
	 */
	_fileQueued: function( file )
	{
		this._buildBox( file );
		$( this.boxes[ this.id + file.index ] ).addClassName('in_progress');
		this._updateInfo( file.index, ipb.lang['pending'] );
	},

	/*
	 * Event handler for fileQueueError
	 */
	_fileQueueError: function( file, errorCode, message )
	{
		var msg;
		
		try {
			if( errorCode === SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED )
			{
				alert( ipb.lang['upload_queue'] + message );
				return false;
			}
		
			switch (errorCode)
			{
				case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
					msg = ipb.lang['upload_too_big'];
				break;
				case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
					msg = ipb.lang['upload_no_file'];
				break;
				case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
					msg = ipb.lang['invalid_mime_type'];
				break;
				default:
					if( file !== null )
					{
						msg = ipb.lang['upload_failed'] + " " + errorCode;
					}
				break;
			}
			
			this._setStatus( file.index, 'error' );
			
			Debug.write( "ips.idmsubmit.js: (ID " + this.id + ", fileQueueError) " + errorCode + ": " + message );
		}
		catch( err )
		{
			Debug.write( "ips.idmsubmit.js: (ID " + this.id + ", fileQueueError) " + errorCode + ": " + message );
		}
	},

	/*
	 * Event handler for uploadError
	*/
	_uploadError: function( file, errorCode, message )
	{
		var msg;
		
		switch( errorCode )
		{
			case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
				msg = ipb.lang['error'] + message;
			break;
			case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
				msg = message;
			break;
			case SWFUpload.UPLOAD_ERROR.IO_ERROR:
				msg = ipb.lang['error'] + " IO";
			break;
			case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
				msg = ipb.lang['error_security'];
			break;
			case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
				msg = ipb.lang['upload_limit_hit'];
			break;
			case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
				msg = ipb.lang['invalid_mime_type'];
			break;
			default:
				msg = ipb.lang['error'] + ": " + errorCode;
			break;
		}
		
		this._setStatus( file.index, 'error' );
		
		Debug.write( "ips.idmsubmit.js: (ID " + this.id + ", uploadError) " + errorCode + ": " + message );
		return false;
	},

	/*
	 * Event handler for uploadStart
	 */
	_uploadStart: function( file )
	{
		ipb.uploader.startedUploading( this.id );
		
		Debug.write( "ips.idmsubmit.js: (ID " + this.id + ", uploadStart) " );
	},
	
	/*
	 * Event handler for uploadSuccess
	 */
	_uploadSuccess: function( file, serverData )
	{
		if( !serverData.isJSON() ){ this._setStatus( file.index, 'error' ); this._updateInfo( file.index, ipb.lang['silly_server'] ); }
		returnedObj = serverData.evalJSON();
		
		if( Object.isUndefined( returnedObj ) ){ this._setStatus( file.index, 'error' ); this._updateInfo( file.index, ipb.lang['silly_server'] ); }
		
		//-----------------------------------------
		// Was there an error?
		//-----------------------------------------
		
		if( returnedObj.is_error == 1 )
		{
			msg = this._determineServerError( returnedObj.msg );
			this._setStatus( file.index, 'error' );
			this._updateInfo( file.index, msg );
			return false;
		}

		//-----------------------------------------
		// Thumbnails
		//-----------------------------------------
		
		if( returnedObj.current_items[ returnedObj.insert_id ][ 3 ] == 1 )
		{
			tmp = returnedObj.current_items[ returnedObj.insert_id ];
			
			var width = tmp[5];
			var height = tmp[6];
			
			if( ( tmp[5] && tmp[5] > 30 ) )
			{
				width = 30;
				factor = ( 30 / tmp[5] );
				height = tmp[6] * factor;
			}
			
			if( ( tmp[6] && tmp[6] > 30 ) )
			{
				height = 30;
				factor = ( 30 / tmp[5] );
				width = tmp[5] * factor;
			}
			
			thumb = new Element('img', { src: tmp[4].replace( /&amp;/, '&' ), 'width': width, 'height': height, 'class': 'thumb_img' } ).hide();
			
			$( this.boxes[ this.id + file.index ] ).select('.img_holder')[0].insert( thumb );
			new Effect.Appear( $( thumb ), { duration: 0.4 } );
		}
		
		//-----------------------------------------
		// Set status and size
		//-----------------------------------------
		
		this._setStatus( file.index, 'complete' );
		this._updateInfo( file.index, ipb.lang['upload_done'].gsub( /\[total\]/, ipb.global.convertSize( file.size ) ) );
		
		//-----------------------------------------
		// Store attachid
		//-----------------------------------------
		
		$( this.boxes[ this.id + file.index ] ).writeAttribute( 'attachid', returnedObj.insert_id );
		
		//-----------------------------------------
		// Deletion event handler
		//-----------------------------------------
		
		$( this.boxes[ this.id + file.index ] ).select('.delete')[0].observe( 'click', ipb.uploader.removeUpload );
		
		//-----------------------------------------
		// Clean up
		//-----------------------------------------
		
		ipb.uploader.finishedUploading( this.id, file.index );
		
		Debug.write( "ips.idmsubmit.js: (ID " + this.id + ", uploadSuccess) " + serverData );
	},

	/*
	 * Event handler for uploadComplete
	 */
	_uploadComplete: function( file )
	{
		ipb.uploader.finishedUploading( this.id );
		
		var progress_bar = $( this.boxes[ this.id + file.index ] ).select('.progress_bar span')[0];
		progress_bar.setStyle( "width: 100%" );
		new Effect.Fade( $( this.boxes[ this.id + file.index ] ).select('.progress_bar')[0], { duration: 0.6 } );
				
		Debug.write( "ips.idmsubmit.js: (ID " + this.id + ", uploadComplete)" );
	},

	/*
	 * Event handler for uploadProgress (build progress bar)
	 */
	_uploadProgress: function( file, bytesLoaded, bytesTotal)
	{
		var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);
		
		var progress_bar = $( this.boxes[ this.id + file.index ] ).select('.progress_bar span')[0];
		progress_bar.setStyle( "width: " + percent + "%" ).update( percent + "%" );
		
		this._setStatus( file.index, 'in_progress' );
		this._updateInfo( file.index, ipb.lang['upload_progress'].gsub( /\[done\]/, ipb.global.convertSize( bytesLoaded ) ).gsub( /\[total\]/, ipb.global.convertSize( bytesTotal ) ) )
		
		Debug.write( "ips.idmsubmit.js: (ID " + this.id + ", uploadProgress)" );
	},

	/*
	 * Event handler for fileDialogComplete (called when user finishes selecting files)
	 */
	_fileDialogComplete: function( number, queued )
	{
		Debug.write( "ips.idmsubmit.js: (ID " + this.id + ", fileDialogComplete) Number: " + number + ", Queued: " + queued );
		this.obj.startUpload();
	}
});













//==============================================================
// Flash detection script
//==============================================================

/*
Copyright (c) 2007, James Auldridge
All rights reserved.
Code licensed under the BSD License:
  http://www.jaaulde.com/license.txt

Version 1.0

Change Log:
	* 09 JAN 07 - Version 1.0 written

*/
//Preparing namespace
var jimAuld = window.jimAuld || {};
jimAuld.utils = jimAuld.utils || {};
jimAuld.utils.flashsniffer = {
	lastMajorRelease: 10,
	installed: false,
	version: null,
	detect: function()
	{
		var fp,fpd,fAX;
		if (navigator.plugins && navigator.plugins.length)
		{
			fp = navigator.plugins["Shockwave Flash"];
			if (fp)
			{
				jimAuld.utils.flashsniffer.installed = true;
				if (fp.description)
				{
					fpd = fp.description;
					jimAuld.utils.flashsniffer.version = fpd.substr( fpd.indexOf('.')-2, 2 ).strip();
					Debug.write( jimAuld.utils.flashsniffer.version );
				}
			}
			else
			{
				jimAuld.utils.flashsniffer.installed = false;
			}
			if (navigator.plugins["Shockwave Flash 2.0"]){
				jimAuld.utils.flashsniffer.installed = true;
				jimAuld.utils.flashsniffer.version = 2;
			}
		}
		else if (navigator.mimeTypes && navigator.mimeTypes.length)
		{
			fp = navigator.mimeTypes['application/x-shockwave-flash'];
			if (fp && fp.enabledPlugin)
			{
				jimAuld.utils.flashsniffer.installed = true;
			}
			else
			{
				jimAuld.utils.flashsniffer.installed = false;
			}
		}
		else
		{
			for(var i=jimAuld.utils.flashsniffer.lastMajorRelease;i>=2;i--)
			{
				try
				{
					fAX = new ActiveXObject("ShockwaveFlash.ShockwaveFlash."+i);
					jimAuld.utils.flashsniffer.installed = true;
					jimAuld.utils.flashsniffer.version = i;
					break;
				}
				catch(e)
				{
				}
			}
			if(jimAuld.utils.flashsniffer.installed == null){
				try
				{
					fAX = new ActiveXObject("ShockwaveFlash.ShockwaveFlash");
					jimAuld.utils.flashsniffer.installed = true;
					jimAuld.utils.flashsniffer.version = 2;
				}
				catch(e)
				{
				}
			}
			if(jimAuld.utils.flashsniffer.installed == null)
			{
				jimAuld.utils.flashsniffer.installed = false;
			}
			fAX = null;
		}
		
	},
	isVersion: function(exactVersion)
	{
		return (jimAuld.utils.flashsniffer.version!=null && jimAuld.utils.flashsniffer.version==exactVersion);
	},
	isLatestVersion: function()
	{
		return (jimAuld.utils.flashsniffer.version!=null && jimAuld.utils.flashsniffer.version==jimAuld.utils.flashsniffer.lastMajorRelease);
	},
	meetsMinVersion: function(minVersion)
	{
		return (jimAuld.utils.flashsniffer.version!=null && jimAuld.utils.flashsniffer.version>=minVersion);
	}
};
jimAuld.utils.flashsniffer.detect();

