<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Admin log skin file
 * Last Updated: $Date: 2010-06-24 05:36:05 -0400 (Thu, 24 Jun 2010) $
 * </pre>
 *
 * @author 		$Author: matt $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Core
 * @link		http://www.invisionpower.com
 * @since		Friday 19th May 2006 17:33
 * @version		$Revision: 6570 $
 */
 
class cp_skin_adminlogs extends output
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
 * Show the splash screen for the logs
 *
 * @access	public
 * @return	string		HTML
 */
public function logSplashScreen() {
$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<div class='acp-box'>
	<h3>{$this->lang->words['choose_splash']}</h3>
	
	<table width='100%' class='alternate_rows'>
		<tr><td><a href='{$this->settings['base_url']}&amp;module=logs&amp;section=errorlogs'>{$this->lang->words['error_log_thelogs']}</a></td></tr>
		<tr><td><a href='{$this->settings['base_url']}&amp;module=logs&amp;section=adminlogs'>{$this->lang->words['alog_adminlogs']}</a></td></tr>
		<tr><td><a href='{$this->settings['base_url']}&amp;module=logs&amp;section=modlogs'>{$this->lang->words['mlog_modlogs']}</a></td></tr>
		<tr><td><a href='{$this->settings['base_url']}&amp;module=logs&amp;section=emaillogs'>{$this->lang->words['elog_emaillogs']}</a></td></tr>
		<tr><td><a href='{$this->settings['base_url']}&amp;module=logs&amp;section=emailerrorlogs'>{$this->lang->words['elog_email_err_logs']}</a></td></tr>
		<tr><td><a href='{$this->settings['base_url']}&amp;module=logs&amp;section=spiderlogs'>{$this->lang->words['slog_spider_logs']}</a></td></tr>
		<tr><td><a href='{$this->settings['base_url']}&amp;module=logs&amp;section=warnlogs'>{$this->lang->words['wlog_warn_logs']}</a></td></tr>
		<tr><td><a href='{$this->settings['base_url']}&amp;module=tools&amp;section=api&amp;do=log_list'>{$this->lang->words['api_error_logs']}</a></td></tr>
		<tr><td><a href='{$this->settings['base_url']}&amp;module=system&amp;section=taskmanager&amp;do=task_logs'>{$this->lang->words['sched_error_logs']}</a></td></tr>
		<tr><td><a href='{$this->settings['base_url']}&amp;module=system&amp;section=loginlog&amp;do=show'>{$this->lang->words['al_error_logs']}</a></td></tr>
		<tr><td><a href='{$this->settings['base_url']}&amp;module=logs&amp;section=spamlogs&amp;do=show'>{$this->lang->words['slog_spamlogs']}</a></td></tr>
		<tr><td><a href='{$this->settings['base_url']}&amp;module=logs&amp;section=mobilelogs&amp;do=show'>{$this->lang->words['mlog_mobilelogs']}</a></td></tr>
		<tr><td><a href='{$this->settings['base_url']}&amp;module=logs&amp;section=sqlerror&amp;do=show'>{$this->lang->words['mlog_sqlerrors']}</a></td></tr>
	</table>
</div>
HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * Show the splash screen for the admin logs
 *
 * @access	public
 * @param	array 		Rows
 * @param	array 		Admins
 * @return	string		HTML
 */
public function adminlogsWrapper( $rows, $admins ) {

$form_array 		= array(
							0 => array( 'member_id'		, $this->lang->words['alog_id'] ),
							1 => array( 'note'			, $this->lang->words['alog_performed'] ),
							2 => array( 'ip_address'	, $this->lang->words['alog_ip']  ),
							3 => array( 'appcomponent'	, $this->lang->words['alog_app']  ),
							4 => array( 'module'		, $this->lang->words['alog_mod']  ),
							5 => array( 'section'		, $this->lang->words['alog_sec']  ),
							6 => array( 'do'			, $this->lang->words['alog_do']  ),
						);
$form				= array();

$form['search_for']	= $this->registry->output->formInput( "search_string" );
$form['search_in']	= $this->registry->output->formDropdown( "search_type", $form_array );

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<div class='section_title'>
	<h2>{$this->lang->words['alog_title']}</h2>
</div>

<div class="acp-box">
	<h3>{$this->lang->words['alog_last5']}</h3>
	<table class='alternate_rows' width='100%'>
		<tr>
			<th width='20%'>{$this->lang->words['alog_member']}</th>
			<th width='40%'>{$this->lang->words['alog_performed']}</th>
			<th width='20%'>{$this->lang->words['alog_date']}</th>
			<th width='20%'>{$this->lang->words['alog_ip']}</th>
		</tr>
HTML;

if( count($rows) AND is_array($rows) )
{
	foreach( $rows as $row )
	{
		$IPBHTML .= <<<HTML
		<tr>
			<td>{$row['members_display_name']}</td>
			<td><span style='color:{$row['color']}'>{$row['note']}</span></td>
			<td>{$row['_time']}</td>
			<td>{$row['ip_address']}</td>
		</tr>
HTML;
	}
}
else
{
	$IPBHTML .= <<<HTML
		<tr>
			<td colspan='4' align='center'>{$this->lang->words['alog_noresults']}</td>
		</tr>
HTML;
}

$IPBHTML .= <<<HTML
	</table>
</div>
<br />

<div class="acp-box">
	<h3>{$this->lang->words['alog_saved']}</h3>
	<table class='alternate_rows' width='100%'>
		<tr>
			<th width='30%'>{$this->lang->words['alog_member']}</th>
			<th width='20%'>{$this->lang->words['alog_performed']}</th>
			<th width='20%'>{$this->lang->words['alog_viewall']}</th>
			<th width='30%'>{$this->lang->words['alog_removeall']}</th>
		</tr>
HTML;

if( count($admins) AND is_array($admins) )
{
	foreach( $admins as $row )
	{
		$IPBHTML .= <<<HTML
		<tr>
			<td>{$row['members_display_name']}</td>
			<td>{$row['act_count']}</td>
			<td><a href='{$this->settings['base_url']}&amp;{$this->form_code}&amp;do=view&amp;mid={$row['member_id']}'>{$this->lang->words['alog_view']}</a></td>
			<td><a href='{$this->settings['base_url']}&amp;{$this->form_code}&amp;do=remove&amp;mid={$row['member_id']}'>{$this->lang->words['alog_remove']}</a></td>
		</tr>
HTML;
	}
}
else
{
	$IPBHTML .= <<<HTML
		<tr>
			<td colspan='4' align='center'>{$this->lang->words['alog_noresults']}</td>
		</tr>
HTML;
}

$IPBHTML .= <<<HTML
	</table>
</div>
<br />

<form action='{$this->settings['base_url']}{$this->form_code}' method='post' name='theAdminForm'  id='theAdminForm'>
	<input type='hidden' name='do' value='view' />
	<input type='hidden' name='_admin_auth_key' value='{$this->registry->adminFunctions->generated_acp_hash}' />

	<div class="acp-box">
		<h3>{$this->lang->words['alog_search']}</h3>
		<ul class="acp-form alternate_rows">
			<li>	
				<label>{$this->lang->words['alog_searchfor']}</label>
				{$form['search_for']}
			</li>
			<li>
				<label>{$this->lang->words['alog_searchin']}</label>
				{$form['search_in']}
			</li>
		</ul>
		<div class="acp-actionbar">
			<input value="{$this->lang->words['alog_searchbutton']}" class="button primary" accesskey="s" type="submit" />
		</div>
	</div>
</form>

HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * SQL log RAPPER LIKE ICE T BUT MORE LIKE COFFEE
 *
 * @access	public
 * @param	array 		Rows
 * @param	string		Page links
 * @return	string		HTML
 */
public function sqllogsWrapper( $rows ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<div class='section_title'>
	<h2>{$this->lang->words['sqllog_title']}</h2>
</div>

<div class="acp-box">
	<h3>{$this->lang->words['sqllog_title']}</h3>
	<table class="alternate_rows" width='100%'>
		<tr>
			<th width="1%">&nbsp;</th>
			<th width='49%'>{$this->lang->words['sqllog_name']}</th>
			<th width='40%'>{$this->lang->words['sqllog_date']}</th>
			<th width='10%'>{$this->lang->words['sqllog_size']}</th>
			<th width="1%">&nbsp;</th>
		</tr>
HTML;

if( count($rows) AND is_array($rows) )
{
	foreach( $rows as $row )
	{
		$size = IPSLib::sizeFormat( $row['size'] );
		$mtime = $this->registry->class_localization->getDate( $row['mtime'], 'SHORT' );
		
		$IPBHTML .= <<<HTML
		<tr>
			<td><img src='{$this->settings['skin_acp_url']}/_newimages/icons/page_red.png' /></td>
			<td><a href="{$this->settings['base_url']}&amp;{$this->form_code}&amp;do=view&amp;file={$row['name']}">{$row['name']}</a></td>
			<td>{$mtime}</td>
			<td>{$size}</td>
			<td><a href="#" onclick="acp.confirmDelete( '{$this->settings['base_url']}&amp;{$this->form_code}&amp;do=remove&amp;file={$row['name']}' )"><img src='{$this->settings['skin_acp_url']}/_newimages/icons/delete.png' /></a></td>
		</tr>
HTML;
	}
}
else
{
	$IPBHTML .= <<<HTML
		<tr>
			<td colspan='4' align='center'>{$this->lang->words['sqllog_noresults']}</td>
		</tr>
HTML;
}

$IPBHTML .= <<<HTML
	</table>
	<div class='acp-actionbar'>
		<div class="leftaction">&nbsp;</div>
	</div>
</div>
<br />
HTML;

//--endhtml--//
return $IPBHTML;
}


/**
 * View a log
 *
 * @access	public
 */
public function sqlLogsView( $file, $size, $content ) {

$tailSize = IPSLib::strlenToBytes( strlen( $content ) );

/* Display a message? */
if ( $tailSize < $size )
{
	$this->registry->output->global_message = sprintf( $this->lang->words['sqllog_more_file'], $file );
}

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<div class='section_title'>
	<h2>{$this->lang->words['sqllog_title']}</h2>
</div>

<div class="acp-box">
	<h3>{$file}</h3>
	<div class='acp-row-off' style='padding:8px'>
		<div style="width:100%; height:400px; background-color:white;font-family:monospace;white-space:pre;overflow:auto">{$content}</div>
	</div>
</div>
HTML;

//--endhtml--//
return $IPBHTML;
}

/**
 * View an individual admin's logs
 *
 * @access	public
 * @param	array 		Rows
 * @param	string		Page links
 * @return	string		HTML
 */
public function adminlogsView( $rows, $pages ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<div class='section_title'>
	<h2>{$this->lang->words['alog_title']}</h2>
</div>

<div class="acp-box">
	<h3>{$this->lang->words['alog_saved']}</h3>
	<table class="alternate_rows" width='100%'>
		<tr>
			<th width='20%'>{$this->lang->words['alog_member']}</th>
			<th width='40%'>{$this->lang->words['alog_performed']}</th>
			<th width='20%'>{$this->lang->words['alog_date']}</th>
			<th width='20%'>{$this->lang->words['alog_ip']}</th>
		</tr>
HTML;

if( count($rows) AND is_array($rows) )
{
	foreach( $rows as $row )
	{
		$IPBHTML .= <<<HTML
		<tr>
			<td>{$row['members_display_name']}</td>
			<td><span style='color:{$row['color']}'>{$row['note']}</span></td>
			<td>{$row['_time']}</td>
			<td>{$row['ip_address']}</td>
		</tr>
HTML;
	}
}
else
{
	$IPBHTML .= <<<HTML
		<tr>
			<td colspan='4' align='center'>{$this->lang->words['alog_noresults']}</td>
		</tr>
HTML;
}

$IPBHTML .= <<<HTML
	</table>
	<div class='acp-actionbar'>
		<div class="leftaction">{$pages}</div>
	</div>
</div>
<br />
HTML;

//--endhtml--//
return $IPBHTML;
}

}