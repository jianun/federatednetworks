<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Dashboard skin file
 * Last Updated: $Date: 2010-06-30 09:58:05 -0400 (Wed, 30 Jun 2010) $
 * </pre>
 *
 * @author 		$Author: mark $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Core
 * @link		http://www.invisionpower.com
 * @since		Friday 19th May 2006 17:33
 * @version		$Revision: 6590 $
 */
 
class cp_skin_mycp extends output
{

/**
 * Prevent our main destructor being called by this class
 *
 * @access	public
 * @return	void
 */
public function __destruct()
{
}

/**
 * Main dashboard template
 *
 * @access	public
 * @param	array 		Content blocks
 * @param	array 		Forums
 * @param	array 		Groups
 * @param	array 		URLs
 * @return	string		HTML
 */
public function mainTemplate( $content, $forums, $groups, $urls=array(), $nagEntries=array() ) {

$welcome = sprintf( $this->lang->words['cp_welcomeipb3'], IPB_VERSION );

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<div class='section_title'>
	<h2>{$welcome}</h2>
</div>
<!--in_dev_notes-->
<!--in_dev_check-->

<!-- Version Check -->
<div id='ips_update' style='display:none'>
	<div class='warning'>
		<h4>{$this->lang->words['cp_newversion']}: <span id='acp-update-version'></span></h4>
		{$this->lang->words['cp_newversion_info']} <a href='' id='acp-update-link' target='_blank'>{$this->lang->words['cp_newversion_link']}</a>
	</div>
	<br />
</div>

<div id='ips_bulletin' style='display:none'>
	<div class='warning'>
		<h4>{$this->lang->words['cp_ipsbulletin']}</h4>
		<p id='ips_supportbox_content'></p>
	</div>
	<br />
</div>


EOF;

if( is_array( $nagEntries ) && count( $nagEntries ) )
{
	foreach( $nagEntries as $r )
	{
$IPBHTML .= <<<EOF
		<div class='information-box'>
			<h4><img src='{$this->settings['skin_acp_url']}/_newimages/icons/bullet_error.png' />{$r[0]}</h4> 
			{$r[1]}
		</div>
		<br />
EOF;
	}
}

$IPBHTML .= <<<EOF
<div id='dashboard'>
	
	<div id='notes_and_news'>
		<!--IPS WIDGETS-->
		<div class='acp-box' style='float:left; width:49%'>
			<h3>{$this->lang->words['cp_ipslatestnews']}</h3>
			<div id='ips_news_content'></div>
		</div>

		<div class='acp-box' style='float:right; width:49%'>
			<h3>{$this->lang->words['cp_ipsblogs']}</h3>
			<div id='ips_blog_content'></div>
		</div>
		<!--IPS WIDGETS-->
	</div>
	
	<br style='clear:all' /><br />
	
	<div id='admin_boxes'>
		<div style='float:left; width:49%'>
			<div class='acp-box'>
				<h3>{$this->lang->words['cp_activeadmins']}</h3>
				{$content['acp_online']}
			</div>
			<br />
			<div class='acp-box'>
				<h3>{$this->lang->words['cp_adminnotes']}</h3>
				<form action='{$this->settings['base_url']}&amp;app=core&amp;module=mycp&amp;section=dashboard&amp;save=1' method='post'>
					<table width='100%'>
						{$content['ad_notes']}
					</table>
				</form>
			</div>

		</div>
		

		<!--acplogins-->
	</div>
</div>

<br />
<script type="text/javascript" src='{$this->settings['js_app_url']}acp.homepage.js'></script>

<!-- HIDDEN "INFORMATION" DIV -->
<div id='acp-update-info-wrapper' style='display:none'>
	<h3>{$this->lang->words['cp_noticeupdate']}</h3>
	<div class='acp-box'>
		<p style='text-align: center;padding:6px;padding-top:24px'>
			{$this->lang->words['cp_update_info']}
			<br />
			<br />
			<input type='button' value='{$this->lang->words['cp_visitcc']}' onclick='upgradeContinue()' class='button' />
		</p>
	</div>
</div>
<!-- / HIDDEN "INFORMATION" DIV -->


<script type='text/javascript'>
function upgradeMoreInfo()
{
	curPop = new ipb.Popup( 'acpVersionInfo', {
							type: 'pane',
							modal: true,
							initial: $('acp-update-info-wrapper').innerHTML,
							hideAtStart: false,
							w: '400px',
							h: '150px'
						});
						
	return false;
}

function upgradeContinue()
{
	acp.openWindow( IPSSERVER_download_link, 800, 600 );
}

/* Warning CONTINUE / CANCEL */
function resetContinue()
{
	if ( confirm( "{$this->lang->words['cp_wannareset']}" ) )
	{
		acp.redirect( ipb.vars['base_url'] + "&amp;app=core&amp;module=mycp&amp;section=dashboard&amp;reset_security_flag=1&amp;new_build=" + IPSSERVER_download_ve + "&amp;new_reason=" + IPSSERVER_download_vt, 1 );
	}
}


/* Set up global vars */
var _newsFeed     = null;
var _blogFeed     = null;
var _versionCheck = null;
var _keithFeed    = null;
/* ---------------------- */
/* ONLOAD: IPS widgets    */
/* ---------------------- */

function onload_ips_widgets()
{
	var head = $$('head')[0];
	
	/* Grab files */
	head.insert( new Element('script', { src: "{$urls['news']}", 'type': 'text/javascript' } ) );
	head.insert( new Element('script', { src: "{$urls['blogs']}", 'type': 'text/javascript' } ) );
	head.insert( new Element('script', { src: "{$urls['version_check']}", 'type': 'text/javascript' } ) );
	head.insert( new Element('script', { src: "{$urls['keiths_bits']}", 'type': 'text/javascript' } ) );
	
	/* ---------------------- */
	/* Feeds                  */
	/* ---------------------- */
	
	_newsFeed = setTimeout( '_newsFeedFunction()', 1000 );
	_blogFeed = setTimeout( '_blogFeedFunction()', 1000 );
	
	/* ---------------------- */
	/* Update boxes           */
	/* ---------------------- */
	
	_versionCheck = setTimeout( '_versionCheckFunction()', 1000 );
	
	/* ---------------------- */
	/* Load Keith             */
	/* ---------------------- */
	
	_keithFeed = setTimeout( '_keithFeedFunction()', 1000 );
}

/* ---------------------- */
/* Keith Feed YumYum      */
/* ---------------------- */

function _keithFeedFunction()
{
	if ( typeof( IPS_KEITH_CONTENT ) != 'undefined' )
	{
		clearTimeout( _keithFeed );
		
		if ( IPS_KEITH_CONTENT && IPS_KEITH_CONTENT != 'none' )
		{
			/* Show version numbers */
			$( 'ips_bulletin' ).style.display = '';
			$( 'ips_supportbox_content' ).innerHTML = IPS_KEITH_CONTENT.replace( /&#0039;/g, "'" );
		}
	}
	else
	{
		_keithFeed = setTimeout( '_keithFeedFunction()', 1000 );
	}
}

/* ---------------------- */
/* Version Check          */
/* ---------------------- */

function _versionCheckFunction()
{
	if ( typeof( IPSSERVER_update_type ) != 'undefined' )
	{
		clearTimeout( _versionCheck );
		
		if ( IPSSERVER_update_type && IPSSERVER_update_type != 'none' )
		{
			$( 'ips_update' ).style.display                = '';

			/* Show version numbers */
			$( 'acp-update-version' ).innerHTML = IPSSERVER_download_vh;
			$( 'acp-update-link' ).href = IPSSERVER_link;
		}
	}
}

/* ---------------------- */
/* BLOG FEED              */
/* ---------------------- */

function _blogFeedFunction()
{
	if ( typeof( ipsBlogFeed ) != 'undefined' )
	{
		clearTimeout( _blogFeed );
	
		eval( ipsBlogFeed );
		var finalString = '';
		var _len        = ipsBlogFeed['items'].length;
	
		if( typeof( ipsBlogFeed['error'] ) == 'undefined' )
		{
			for( i = 0; i < _len; i++ )
			{
				var _style   = ( i + 1 < _len ) ? 'padding:2px;border-bottom:1px dotted black' : 'padding:2px';
				var _title   = ( ipsBlogFeed['items'][i]['title'].length > 50 ) ? ipsBlogFeed['items'][i]['title'].substr( 0, 47 ) + '...' : ipsBlogFeed['items'][i]['title'];
				finalString += "<div style='" + _style + "'><img src='{$this->settings['skin_acp_url']}/_newimages/icons/ipsnews_item.gif' /> <a href='" + ipsBlogFeed['items'][i]['link'] + "' target='_blank' style='text-decoration:none'title='" + ipsBlogFeed['items'][i]['title'] + "'>" + _title + "</a></div>\\n";
			}
		}
	
		if ( finalString )
		{
			$( 'ips_blog_content' ).innerHTML = finalString;
		}
		else
		{
			$( 'ips_blog' ).style.display = 'none';
		}
	}
	else
	{
		_blogFeed = setTimeout( '_blogFeedFunction()', 1000 );
	}
}

/* ---------------------- */
/* NEWS FEED              */
/* ---------------------- */

function _newsFeedFunction()
{
	if ( typeof( ipsNewsFeed ) != 'undefined' )
	{
		clearTimeout( _newsFeed );
		
		eval( ipsNewsFeed );
		var finalString = '';
		var _len        = ipsNewsFeed['items'].length;

		if( typeof( ipsNewsFeed['error'] ) == 'undefined' )
		{
			for( i = 0; i < _len; i++ )
			{
				var _style   = ( i + 1 < _len ) ? 'padding:2px;border-bottom:1px dotted black' : 'padding:2px';
				var _title   = ( ipsNewsFeed['items'][i]['title'].length > 50 ) ? ipsNewsFeed['items'][i]['title'].substr( 0, 47 ) + '...' : ipsNewsFeed['items'][i]['title'];
				finalString += "<div style='" + _style + "'><img src='{$this->settings['skin_acp_url']}/_newimages/icons/ipsnews_item.gif' /> <a href='" + ipsNewsFeed['items'][i]['link'] + "' target='_blank' style='text-decoration:none' title='" + ipsNewsFeed['items'][i]['title'] + "'>" + _title + "</a></div>\\n";
			}
		}
		
		if ( finalString )
		{
			$( 'ips_news_content' ).innerHTML = finalString;
		}
		else
		{
			$( 'ips_news' ).style.display = 'none';
		}
	}
	else
	{
		_newsFeed = setTimeout( '_newsFeedFunction()', 1000 );
	}
}


/* Set up onload event */
Event.observe( window, 'load', onload_ips_widgets );
//]]>
</script>
EOF;

//--endhtml--//
return $IPBHTML;
}


/**
 * Wrapper for validating users
 *
 * @access	public
 * @param	string		Content
 * @return	string		HTML
 */
public function acp_validating_wrapper($content) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<div class='dashboard_border'>
	<div class='dashboard_header'>{$this->lang->words['cp_adminvalidationqueue']}</div>
	{$content}
	<div align='right'>
	   <a href='{$this->settings['base_url']}&amp;app=members&amp;module=members&amp;section=tools&amp;do=validating' style='text-decoration:none'>{$this->lang->words['cp_more']} {$this->lang->words['_raquo']}</a>
	 </div>
</div>
<br />
EOF;

//--endhtml--//
return $IPBHTML;
}

/**
 * Validating users row
 *
 * @access	public
 * @param	array 		Data
 * @return	string		HTML
 */
public function acp_validating_block( $data ) {

$IPBHTML = "";
//--starthtml--//

$data['url']	= $this->registry->output->buildSEOUrl( $this->settings['board_url'] . '/index.php?showuser=' . $data['member_id'], 'none', $data['members_seo_name'], 'showuser' );

$IPBHTML .= <<<EOF
<div class='dashboard_sub_row_alt'>
 <div style='float:right;'>
  <a href='{$this->settings['_base_url']}&amp;app=members&amp;module=members&amp;section=tools&amp;do=domod&amp;_admin_auth_key={$this->registry->getClass('adminFunctions')->_admin_auth_key}&amp;mid_{$data['member_id']}=1&amp;type=approve'><img src='{$this->settings['skin_acp_url']}/images/aff_tick.png' alt='{$this->lang->words['cp_yes']}' /></a>&nbsp;
  <a href='{$this->settings['_base_url']}&amp;app=members&amp;module=members&amp;section=tools&amp;do=domod&amp;_admin_auth_key={$this->registry->getClass('adminFunctions')->_admin_auth_key}&amp;mid_{$data['member_id']}=1&amp;type=delete'><img src='{$this->settings['skin_acp_url']}/images/aff_cross.png' alt='{$this->lang->words['cp_no']}' /></a>
 </div>
 <div>
  <strong><a href='{$data['url']}' target='_blank'>{$data['members_display_name']}</a></strong>{$data['_coppa']}<br />
  &nbsp;&nbsp;{$data['email']}</a><br />
  <div class='desctext'>&nbsp;&nbsp;{$this->lang->words['cp_ip']}: <a href='{$this->settings['base_url']}&amp;app=members&amp;module=members&amp;section=toolsdo=learn_ip&amp;ip={$data['ip_address']}'>{$data['ip_address']}</a></div>
  <div class='desctext'>&nbsp;&nbsp;{$this->lang->words['cp_registered']} {$data['_entry']}</div>
 </div>
</div>
EOF;

//--endhtml--//
return $IPBHTML;
}


/**
 * Show the ACP notes block
 *
 * @access	public
 * @param	string		Current notes
 * @return	string		HTML
 */
public function acp_notes($notes) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<tr>
	<td class='notes acp-row-on'>
		<textarea name='notes' class="dashboard_notes" rows='8' cols='25'>{$notes}</textarea>
	</td>
</tr>
<tr>
	<td class='acp-actionbar'>
		<input type='submit' value='{$this->lang->words['cp_savenotes']}' class='realbutton' />
	</td>
</tr>
EOF;

//--endhtml--//
return $IPBHTML;
}

/**
 * Show a latest login record
 *
 * @access	public
 * @param	array 		Record
 * @return	string		HTML
 */
public function acp_last_logins_row( $r ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<tr>
	<td width='1' valign='middle'>
		<img src='{$this->settings['skin_acp_url']}/images/{$r['_admin_img']}' alt='-' />
	</td>
 	<td class=''>
		<strong>{$r['admin_username']}</strong>
		<div class='desctext'>
			{$r['_admin_time']}
		</div>
 	</td>
 	<td class=''>
 		<a href='#' onclick="return acp.openWindow('{$this->settings['base_url']}module=system&amp;section=loginlog&amp;do=view_detail&amp;detail={$r['admin_id']}', 400, 400)" title='View Details'><img src='{$this->settings['skin_acp_url']}/images/folder_components/index/view.png' alt='-' title='{$this->lang->words['cp_view']}' /></a>
    </td>
</tr>
EOF;

//--endhtml--//
return $IPBHTML;
}

/**
 * Wrapper for latest ACP logins
 *
 * @access	public
 * @param	string		Content
 * @return	string		HTML
 */
public function acp_last_logins_wrapper($content) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<div class="acp-box" style='float:right; width:49%'>
    <h3>{$this->lang->words['cp_latestadminlogins']}</h3>
	<table width='100%'>
		{$content}
	</table>
	<div class="more">
		<a href='{$this->settings['base_url']}&amp;app=core&amp;module=system&amp;section=loginlog' style='text-decoration:none'>{$this->lang->words['cp_more']} {$this->lang->words['_raquo']}</a>
	</div>
</div>
EOF;

//--endhtml--//
return $IPBHTML;
}


/**
 * Show the admins online record
 *
 * @access	public
 * @param	array 		Record
 * @return	string		HTML
 */
public function acp_onlineadmin_row( $r ) {

$IPBHTML = "";
//--starthtml--//

$r['url']	= $this->registry->output->buildSEOUrl( $this->settings['board_url'] . '/index.php?showuser=' . $r['session_member_id'], 'none', $r['members_seo_name'], 'showuser' );

$IPBHTML .= <<<EOF
<tr>
    <td class=''>
    	<strong style='font-size:12px'><a href='{$r['url']}' target='_blank'>{$r['members_display_name']}</a></strong>
    	<div class='desctext'>{$r['session_location']} {$this->lang->words['cp_from']} {$r['session_ip_address']}</div>
    </td> 
	<td class=''>
	 	<img src='{$r['pp_thumb_photo']}' width='{$r['pp_thumb_width']}' height='{$r['pp_thumb_height']}' />
 	</td>
</tr>
EOF;

//--endhtml--//
return $IPBHTML;
}

/**
 * Admins online wrapper
 *
 * @access	public
 * @param	string		Content
 * @return	string		HTML
 */
public function acp_onlineadmin_wrapper($content) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
 <table width='100%' cellpadding='4' cellspacing='0'>
  {$content}
 </table>
EOF;

//--endhtml--//
return $IPBHTML;
}

/**
 * Show latest actions record
 *
 * @access	public
 * @param	array 		Record
 * @return	string		HTML
 */
public function acp_lastactions_row( $rowb ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<tr>
 <td width='1'>
	<img src='{$this->settings['skin_acp_url']}/images/folder_components/index/user.png' alt='-' />
 </td>
 <td>
	<b>{$rowb['members_display_name']}</b>
	<div class='desctext'>{$this->lang->words['cp_ip']}: {$rowb['ip_address']}</div>
 </td>
 <td>{$rowb['_ctime']}</td>
</tr>
EOF;

//--endhtml--//
return $IPBHTML;
}

/**
 * Latest actions wrapper
 *
 * @access	public
 * @param	string		Content
 * @return	string		HTML
 */
public function acp_lastactions_wrapper($content) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<div class='tableborder'>
 <div class='tableheaderalt'>{$this->lang->words['cp_lastacpactions']}</div>
 <table width='100%' cellpadding='4' cellspacing='0'>
 <tr>
  <td class='tablesubheader' width='1%'>&nbsp;</td>
  <td class='tablesubheader' width='44'>{$this->lang->words['cp_membername']}</td>
  <td class='tablesubheader' width='55%'>{$this->lang->words['cp_timeofaction']}</td>
 </tr>
 $content
 </table>
 <div class='tablefooter' align='right'>
   <a href='{$this->settings['base_url']}&amp;app=core&amp;module=logs&amp;section=adminlogs' style='text-decoration:none'>{$this->lang->words['cp_more']} {$this->lang->words['_raquo']}</a>
 </div>
</div>

EOF;

//--endhtml--//
return $IPBHTML;
}


/**
 * Show a warning box
 *
 * @access	public
 * @param	string		Title
 * @param	string		Content
 * @return	string		HTML
 */
public function warning_box($title, $content) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<div class='warning'>
	<h4><img src='{$this->settings['skin_acp_url']}/_newimages/icons/bullet_error.png' alt='{$this->lang->words['cp_error']}' /> {$title}</h4>
	{$content}
</div>
EOF;

//--endhtml--//
return $IPBHTML;
}

/**
 * Show a warning that an emergency skin rebuild has occurred
 *
 * @access	public
 * @return	string		HTML
 * @deprecated	Don't think this is done/called anymore
 */
public function warning_rebuild_emergency() {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
	{$this->lang->words['cp_emergency']}
EOF;

//--endhtml--//
return $IPBHTML;
}

/**
 * Show a warning that the rebuild following the upgrade hasn't been completed
 *
 * @access	public
 * @return	string		HTML
 */
public function warning_rebuild_upgrade() {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
   {$this->lang->words['cp_warning_rebuild']}
EOF;

//--endhtml--//
return $IPBHTML;
}

/**
 * Show form to change details
 *
 * @access	public
 * @return	string		HTML
 */
public function showChangeForm() {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<EOF
<form id='mainform' action='{$this->settings['base_url']}&amp;module=mycp&amp;section=details&amp;do=save' method='post'>
	<div class='acp-box'>
 		<h3>{$this->lang->words['mycp_change_details']}</h3>
		
 		<ul class='acp-form'>
 			<li class='head'><label>{$this->lang->words['change_email_details']}</label></li>
			<li class='acp-row-on'>
				<label>{$this->lang->words['change__email']}</label>
				<input class='textinput' type='text' name='email' size='30' />
			</li>
			<li class='acp-row-off'>
				<label>{$this->lang->words['change__email_confirm']}</label>
				<input class='textinput' type='text' name='email_confirm' size='30' />
			</li>
			
			<li class='head'><label>{$this->lang->words['change_pass_details']}</label></li>
			<li class='acp-row-on'>
				<label>{$this->lang->words['change__pass']}<!--<span class='desctext'>{$this->lang->words['pw_will_logout']}</span>--></label>
				<input class='textinput' type='password' name='password' size='30' />
			</li>
			<li class='acp-row-off'>
				<label>{$this->lang->words['change__pass_confirm']}</label>
				<input class='textinput' type='password' name='password_confirm' size='30' />
			</li>
		</ul>

		<div class='acp-actionbar'>
			<div class='centeraction'>
				<input type='submit' value=' {$this->lang->words['change__confirm']} ' class='button primary' />
			</div>
		</div>
	</div>
</form>
EOF;

//--endhtml--//
return $IPBHTML;
}
}