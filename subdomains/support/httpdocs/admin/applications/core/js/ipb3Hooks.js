
function addAnotherFile()
{
	elementIndex = parseInt(elementIndex) + 1;

	var header		= new Element( 'div', { 'class': 'tablesubheader' } );
	header.update( ipb.lang['hookfilenumber'] + elementIndex );
	
	var list		= new Element( 'ul', { 'id': 'fileTable_' + elementIndex } );
	list.addClassName( 'acp-form' );
	list.addClassName( 'alternate_rows' );
	
	$('fileTableContainer').insert( header );
	$('fileTableContainer').insert( list );
	
	var li1		= new Element( 'li' ).update( "<label>" + ipb.lang['hook_filename'] + "</label><input type='text' name='file[" + elementIndex + "]' value='' size='50' class='inputtext' />" );
	var li2		= new Element( 'li' ).update( "<label>" + ipb.lang['hook_classname'] + "</label><input type='text' name='hook_classname[" + elementIndex + "]' value='' size='50' class='inputtext' />" );
	var li3		= new Element( 'li' ).update( "<label>" + ipb.lang['hook_filetype'] + "</label><select name='hook_type[" + elementIndex + "]' id='hook_type[" + elementIndex + "]' onchange='selectHookType(" + elementIndex + ");'><option value='0'>" + ipb.lang['hook_filetype_select'] + "</option><option value='commandHooks'>" + ipb.lang['hook_filetype_action'] + "</option><option value='skinHooks'>" + ipb.lang['hook_filetype_skin'] + "</option><option value='templateHooks'>" + ipb.lang['hook_filetype_template'] + "</option><option value='dataHooks'>" + ipb.lang['hook_filetype_data'] + "</option><option value='libraryHooks'>" + ipb.lang['hook_filetype_library'] + "</option></select>" );

	$('fileTable_' + elementIndex).insert( li1 );
	$('fileTable_' + elementIndex).insert( li2 );
	$('fileTable_' + elementIndex).insert( li3 );
	
	doStriping();
}

function selectHookType( elementIndex )
{
	var type = $F('hook_type[' + elementIndex + ']');

	if( $('tr_classToOverload[' + elementIndex + ']') != null )
	{
		$('tr_classToOverload[' + elementIndex + ']').remove();
	}
	
	if( $('tr_skinGroup[' + elementIndex + ']') != null )
	{
		$('tr_skinGroup[' + elementIndex + ']').remove();
	}
	
	if( $('tr_skinFunction[' + elementIndex + ']') != null )
	{
		$('tr_skinFunction[' + elementIndex + ']').remove();
	}
	
	if( $('tr_type[' + elementIndex + ']') != null )
	{
		$('tr_type[' + elementIndex + ']').remove();
	}
	
	if( $('tr_id[' + elementIndex + ']') != null )
	{
		$('tr_id[' + elementIndex + ']').remove();
	}
	
	if( $('tr_position[' + elementIndex + ']') != null )
	{
		$('tr_position[' + elementIndex + ']').remove();
	}
	
	if( $('tr_dataLocation[' + elementIndex + ']') != null )
	{
		$('tr_dataLocation[' + elementIndex + ']').remove();
	}
	
	
	if( $('tr_libApplication[' + elementIndex + ']') != null )
	{
		$('tr_libApplication[' + elementIndex + ']').remove();
	}
		
	if( type == 'templateHooks' )
	{
		// Show the skin dropdown now

		url = ipb.vars['base_url'] + "app=core&amp;module=ajax&amp;section=hooks&amp;do=getGroupsForAdd&amp;secure_key=" + ipb.vars['md5_hash'] + "&i=" + elementIndex;
		url = url.replace( /&amp;/g, '&' );
	
		new Ajax.Request( url,
						  {
							method: 'GET',
							onSuccess: function (t )
							{
								li1		= new Element( 'li', { 'id': 'tr_skinGroup[' + elementIndex + ']' } ).update( "<label>" + ipb.lang['hook_skingroup'] + "</label>" + t.responseText );
								$('fileTable_' + elementIndex).insert( li1 );
								doStriping();
							},
							onException: function( f,e ){ alert( "Exception: " + e ) },
							onFailure: function( t ){ alert( "Failure: " + t.responseText ) }
						  } );
	}
	else if( type == 'dataHooks' )
	{
		// Show the data location dropdown now

		url = ipb.vars['base_url'] + "app=core&amp;module=ajax&amp;section=hooks&amp;do=getDataLocationsForAdd&amp;secure_key=" + ipb.vars['md5_hash'] + "&i=" + elementIndex;
		url = url.replace( /&amp;/g, '&' );
	
		new Ajax.Request( url,
						  {
							method: 'GET',
							onSuccess: function (t )
							{
								li1		= new Element( 'li', { 'id': 'tr_dataLocation[' + elementIndex + ']' } ).update( "<label>" + ipb.lang['hook_data_location'] + "</label>" + t.responseText );
								$('fileTable_' + elementIndex).insert( li1 );
								doStriping();
							},
							onException: function( f,e ){ alert( "Exception: " + e ) },
							onFailure: function( t ){ alert( "Failure: " + t.responseText ) }
						  } );
	}
	else if( type == 'libraryHooks' )
	{
		var li1		= new Element( 'li', { 'id': 'tr_classToOverload[' + elementIndex + ']' } ).update( "<label>" + ipb.lang['hook_extends'] + "</label><input type='text' name='classToOverload[" + elementIndex + "]' value='' size='50' class='inputtext' />" );
		$('fileTable_' + elementIndex).insert( li1 );
		
		// Show the data location dropdown now

		url = ipb.vars['base_url'] + "app=core&amp;module=ajax&amp;section=hooks&amp;do=getApplications&amp;secure_key=" + ipb.vars['md5_hash'] + "&i=" + elementIndex;
		url = url.replace( /&amp;/g, '&' );
	
		new Ajax.Request( url,
						  {
							method: 'GET',
							onSuccess: function (t )
							{
								li1		= new Element( 'li', { 'id': 'tr_libApplication[' + elementIndex + ']' } ).update( "<label>" + ipb.lang['hook_application'] + "</label>" + t.responseText );
								$('fileTable_' + elementIndex).insert( li1 );
								doStriping();
							},
							onException: function( f,e ){ alert( "Exception: " + e ) },
							onFailure: function( t ){ alert( "Failure: " + t.responseText ) }
						  } );
	}
	else if( type != '0' )
	{
		// Show the classToOverload field
		
		var li1		= new Element( 'li', { 'id': 'tr_classToOverload[' + elementIndex + ']' } ).update( "<label>" + ipb.lang['hook_extends'] + "</label><input type='text' name='classToOverload[" + elementIndex + "]' value='' size='50' class='inputtext' />" );
		$('fileTable_' + elementIndex).insert( li1 );
	}
}

function getTemplatesForAdd( elementIndex )
{
	var type = $F('skinGroup[' + elementIndex + ']');
	
	if( $('tr_skinFunction[' + elementIndex + ']') != null )
	{
		$('tr_skinFunction[' + elementIndex + ']').remove();
	}
	
	if( $('tr_type[' + elementIndex + ']') != null )
	{
		$('tr_type[' + elementIndex + ']').remove();
	}
	
	if( $('tr_id[' + elementIndex + ']') != null )
	{
		$('tr_id[' + elementIndex + ']').remove();
	}
	
	if( $('tr_position[' + elementIndex + ']') != null )
	{
		$('tr_position[' + elementIndex + ']').remove();
	}
	
	url = ipb.vars['base_url'] + "app=core&amp;module=ajax&amp;section=hooks&amp;do=getTemplatesForAdd&amp;secure_key=" + ipb.vars['md5_hash'] + "&i=" + elementIndex + "&group=" + type;
	url = url.replace( /&amp;/g, '&' );

	new Ajax.Request( url,
					  {
						method: 'GET',
						onSuccess: function (t )
						{
							li1		= new Element( 'li', { 'id': 'tr_skinFunction[' + elementIndex + ']' } ).update( "<label>" + ipb.lang['hook_skinfunc'] + "</label>" + t.responseText );
							$('fileTable_' + elementIndex).insert( li1 );
							doStriping();
						},
						onException: function( f,e ){ alert( "Exception: " + e ) },
						onFailure: function( t ){ alert( "Failure: " + t.responseText ) }
					  } );
}

function getTypeOfHook( elementIndex )
{
	var type = $F('skinFunction[' + elementIndex + ']');

	if( $('tr_type[' + elementIndex + ']') != null )
	{
		$('tr_type[' + elementIndex + ']').remove();
	}
	
	if( $('tr_id[' + elementIndex + ']') != null )
	{
		$('tr_id[' + elementIndex + ']').remove();
	}
	
	if( $('tr_position[' + elementIndex + ']') != null )
	{
		$('tr_position[' + elementIndex + ']').remove();
	}

	li1		= new Element( 'li', { 'id': 'tr_type[' + elementIndex + ']' } ).update( "<label>" + ipb.lang['hook_temptype'] + "</label><select name='type[" + elementIndex + "]' id='type[" + elementIndex + "]' onchange='getHookIds(" + elementIndex + ");'><option value='0'>" + ipb.lang['hook_filetype_select'] + "</option><option value='foreach'>foreach loop</option><option value='if'>if statement</option></select>" );
	$('fileTable_' + elementIndex).insert( li1 );
	
	doStriping();
}

function getHookIds( elementIndex )
{
	var template	= $F('skinFunction[' + elementIndex + ']');
	var type		= $F('type[' + elementIndex + ']');

	if( $('tr_id[' + elementIndex + ']') != null )
	{
		$('tr_id[' + elementIndex + ']').remove();
	}
	
	if( $('tr_position[' + elementIndex + ']') != null )
	{
		$('tr_position[' + elementIndex + ']').remove();
	}
	
	url = ipb.vars['base_url'] + "app=core&amp;module=ajax&amp;section=hooks&amp;do=getHookIds&amp;secure_key=" + ipb.vars['md5_hash'] + "&i=" + elementIndex + "&type=" + type + "&template=" + template;
	url = url.replace( /&amp;/g, '&' );

	new Ajax.Request( url,
					  {
						method: 'GET',
						onSuccess: function (t )
						{
							li1		= new Element( 'li', { 'id': 'tr_id[' + elementIndex + ']' } ).update( "<label>" + ipb.lang['hook_hookid'] + "</label>" + t.responseText );
							$('fileTable_' + elementIndex).insert( li1 );
							doStriping();
						},
						onException: function( f,e ){ alert( "Exception: " + e ) },
						onFailure: function( t ){ alert( "Failure: " + t.responseText ) }
					  } );
}

function getHookEntryPoints( elementIndex )
{
	var type		= $F('type[' + elementIndex + ']');

	if( $('tr_position[' + elementIndex + ']') != null )
	{
		$('tr_position[' + elementIndex + ']').remove();
	}

	var options = '';
	options 	+= "<option value='0'>" + ipb.lang['hook_filetype_select'] + "</option>";
	
	if( type == 'foreach' )
	{
		options += "<option value='outer.pre'>(outer.pre) " + ipb.lang['a_outerpre'] + "</option>";
		options += "<option value='inner.pre'>(inner.pre) " + ipb.lang['a_innerpre'] + "</option>";
		options += "<option value='inner.post'>(inner.post) " + ipb.lang['a_innerpost']+ "</option>";
		options += "<option value='outer.post'>(outer.post) " + ipb.lang['a_outerpost']+ "</option>";
	}
	else
	{
		options += "<option value='pre.startif'>(pre.startif) " + ipb.lang['a_prestartif'] + "</option>";
		options += "<option value='post.startif'>(post.startif) " + ipb.lang['a_poststartif'] + "</option>";
		options += "<option value='pre.else'>(pre.else) " + ipb.lang['a_preelse'] + "</option>";
		options += "<option value='post.else'>(post.else) " + ipb.lang['a_postelse'] + "</option>";
		options += "<option value='pre.endif'>(pre.endif) " + ipb.lang['a_preendif'] + "</option>";
		options += "<option value='post.endif'>(post.endif) " + ipb.lang['a_postendif'] + "</option>";
	}

	var li1		= new Element( 'li', { 'id': 'tr_position[' + elementIndex + ']' } ).update( "<label>" + ipb.lang['hook_tempposi'] + "</label><select name='position[" + elementIndex + "]' id='position[" + elementIndex + "]'>" + options + "</select>" );
	$('fileTable_' + elementIndex).insert( li1 );
	
	doStriping();
}