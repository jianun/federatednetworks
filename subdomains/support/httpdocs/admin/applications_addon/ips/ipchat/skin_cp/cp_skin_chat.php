<?php
/**
 * Invision Power Services
 * IP.Board v3.0.4
 * Chat skin file
 * Last Updated: $Date: 2009-09-30 10:45:37 -0400 (Wed, 30 Sep 2009) $
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		Invision Power Board
 * @subpackage	Chat
 * @link		http://www.invisionpower.com
 * @since		Friday 19th May 2006 17:33
 * @version		$Revision: 5183 $
 */
 
class cp_skin_chat extends output 
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
 * Enter IPS Chat key
 *
 * @access	public
 * @return	string		HTML
 */
public function ipschatKey() {

$IPBHTML = "";
//--starthtml--//
$IPBHTML .= <<<EOF
<div class='section_title'>
	<h2>{$this->lang->words['chat_title']}</h2>
</div>

<form action='{$this->settings['base_url']}&amp;{$this->form_code}&amp;do=chatsave' method='post'>
<table style='background:#005' width='100%'>
	<tr>
		<td width='100%'>
			<b style='color:white'>{$this->lang->words['ordered_ipchat']}</b>
			<input type='text' size=35 name='account_no' value='{$this->lang->words['enter_siteid']}' onclick="this.value='';">
			<input type='submit' class='realdarkbutton' value='{$this->lang->words['btn_continue']}'>
		</td>
	</tr>
</table>
</form>

EOF;

//--endhtml--//
return $IPBHTML;
}

/**
 * Forums member tabs
 *
 * @access	public
 * @param	array 	Member data
 * @return	string	HTML
 */
public function acp_member_form_tabs( $member ) {

$IPBHTML = "";

$IPBHTML .= <<<EOF
	<li id='tabtab-MEMBERS|22' class=''>{$this->lang->words['mem_tab_chat']}</li>
EOF;

return $IPBHTML;
}

/**
 * Main ACP member form
 *
 * @access	public
 * @param	array 	Member data
 * @return	string	HTML
 */
public function acp_member_form_main( $member ) {

$form_chat_banned		= ipsRegistry::getClass('output')->formYesNo( "chat_banned", $member['chat_banned'] );

$IPBHTML = "";

$IPBHTML .= <<<EOF
	
	<div class='tablerow1' id='tabpane-MEMBERS|22'>
		<table class='alternate_rows double_pad' cellspacing='0'>
			
			<tr>
				<th colspan='2'>{$this->lang->words['mem_form_chat_settings']}</th>
			</tr>
			<tr>
				<td><strong>{$this->lang->words['mem_form_ban_chat']}</strong></td>
				<td>
					<span id='MF__chat_banned'>{$form_chat_banned}</span>
				</td>
			</tr>
		</table>
	</div>
EOF;

return $IPBHTML;
}

/**
 * Main log view
 *
 * @access	public
 * @param	string	Page links
 * @param	array 	Logs
 * @return	string	HTML
 */
public function logs( $pages, $logs ) {

$IPBHTML = "";

$IPBHTML .= <<<EOF
<script type='text/javascript' src='{$this->settings['public_dir']}js/3rd_party/calendar_date_select/calendar_date_select.js'></script>
<style type='text/css'>
 	@import url('{$this->settings['public_dir']}/style_css/{$this->registry->output->skin['_csscacheid']}/calendar_select.css');
 	@import url('{$this->settings['skin_app_url']}/css/chatlogs.css');
</style>
<div class='section_title'>
	<h2>{$this->lang->words['chat_logs']}</h2>
	<ul class='context_menu'>
		<li>
			<a href='{$this->settings['base_url']}{$this->form_code}&amp;do=refresh' title='{$this->lang->words['chat_logs_refresh']}'>
				<img src='{$this->settings['skin_acp_url']}/_newimages/icons/arrow_refresh.png' alt='{$this->lang->words['icon']}' />
				{$this->lang->words['chat_logs_refresh']}
			</a>
		</li>
	</ul>
</div>

<div>{$pages}</div>

<div class='acp-box clear'>
	<h3>{$this->lang->words['chat_logs_sub']}</h3>
	<table class='chatlogs'>
		<tr>
			<th style='width: 20%;'>{$this->lang->words['chat_log_date']}</th>
			<th style='width: 20%;'>{$this->lang->words['chat_log_user']}</th>
			<th style='width: 60%;'>{$this->lang->words['chat_log_message']}</th>
		</tr>
EOF;

if( is_array($logs) AND count($logs) )
{
	foreach( $logs as $log )
	{
		$IPBHTML .= <<<EOF
		<tr class='{$log['_classname']}'>
			<td style='width: 20%;'>{$log['_log_date']}</td>
			<td style='width: 20%;'>{$log['log_user']}</td>
			<td style='width: 60%;'>{$log['_log_message']}</td>
		</tr>
EOF;
	}
}
else
{
	$IPBHTML .= <<<EOF
	<tr>
		<td colspan='3' style='text-align: center; font-style: italic;'>{$this->lang->words['chat_logs_none']}</td>
	</tr>
EOF;
}

$_radio_Public	= ( !$this->request['visibility'] OR $this->request['visibility'] == 'public' ) ? " checked='checked'" : '';
$_radio_Private	= ( $this->request['visibility'] == 'private' ) ? " checked='checked'" : '';
$_radio_Both	= ( $this->request['visibility'] == 'both' ) ? " checked='checked'" : '';

$IPBHTML .= <<<EOF

	</table>
	<div class="acp-actionbar">
		<form action='{$this->settings['base_url']}{$this->form_code}' method='post'>
			<label for='visibility_public' title='{$this->lang->words['chat_log__publicvisd']}'>{$this->lang->words['chat_log__publicvis']}</label> <input type='radio' name='visibility' id='visibility_public' value='public' title='{$this->lang->words['chat_log__publicvisd']}' {$_radio_Public} />
			&nbsp;<label for='visibility_private' title='{$this->lang->words['chat_log__privatevisd']}'>{$this->lang->words['chat_log__privatevis']}</label> <input type='radio' name='visibility' id='visibility_private' value='private' title='{$this->lang->words['chat_log__privatevisd']}' {$_radio_Private} />
			&nbsp;<label for='visibility_both' title='{$this->lang->words['chat_log__bothvisd']}'>{$this->lang->words['chat_log__bothvis']}</label> <input type='radio' name='visibility' id='visibility_both' value='both' title='{$this->lang->words['chat_log__bothvisd']}' {$_radio_Both} />
			&nbsp;&nbsp;&nbsp;
			{$this->lang->words['chat_log__filter']}
			<input type='text' name='keyword' length='12' value='{$this->request['keyword']}' />
			
			{$this->lang->words['chat_log__daterange']}
			<input type='text' name='date_from' id='date_from' length='10' value='{$this->request['date_from']}' /> <img src='{$this->settings['img_url']}/date.png' alt='{$this->lang->words['icon']}' id='date_from_icon' style='cursor: pointer; vertical-align: middle;' />
			{$this->lang->words['chatlogto']}
			<input type='text' name='date_to' id='date_to' length='10' value='{$this->request['date_to']}' /> <img src='{$this->settings['img_url']}/date.png' alt='{$this->lang->words['icon']}' id='date_to_icon' style='cursor: pointer; vertical-align: middle;' />

			<input value="{$this->lang->words['chat_log__submit']}" class="button primary" accesskey="s" type="submit" />
		</form>
	</div>
</div>

<br />
<div>{$pages}</div>
<script type='text/javascript'>
	$('date_from_icon').observe('click', function(e){
		var dateSelect = new CalendarDateSelect( $('date_from'), { year_range: 100, time: true, close_on_click: true } );
	});
	$('date_to_icon').observe('click', function(e){
		var dateSelect = new CalendarDateSelect( $('date_to'), { year_range: 100, time: true, close_on_click: true } );
	});
</script>
EOF;

return $IPBHTML;
}

}
