<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Error log skin file
 * Last Updated: $Date: 2010-02-18 20:29:54 -0500 (Thu, 18 Feb 2010) $
 * </pre>
 *
 * @author 		$Author: josh $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Core
 * @link		http://www.invisionpower.com
 * @since		Friday 19th May 2006 17:33
 * @version		$Revision: 5855 $
 */
 
class cp_skin_mobilelogs extends output
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
 * Mobile log wrapper
 *
 * @access	public
 * @param	array 		Rows
 * @param	string		Page links
 * @return	string		HTML
 */
public function mobileLogsWrapper( $rows, $links ) {

$IPBHTML = "";
//--starthtml--//

$IPBHTML .= <<<HTML
<script type='text/javascript' src='{$this->settings['js_main_url']}acp.forms.js'></script>
<form action='{$this->settings['base_url']}{$this->form_code}' method='post' name='theAdminForm' id='theAdminForm'>
<input type='hidden' name='do' value='remove' />
<input type='hidden' name='_admin_auth_key' value='{$this->registry->adminFunctions->generated_acp_hash}' />
<div class="acp-box">
	<h3>{$this->lang->words['mlog_mobilelogs']}</h3>
	<table class='alternate_rows' width='100%'>
		<tr>
			<th width='5%'>{$this->lang->words['mlog_list_id']}</th>
			<th width='20%'>{$this->lang->words['mlog_list_date']}</th>
			<th width='35%'>{$this->lang->words['mlog_list_notification']}</th>
			<th width='25%'>{$this->lang->words['mlog_list_member']}</th>
			<th width='10%'>{$this->lang->words['mlog_list_sent']}</th>
			<th width='3%'><input type='checkbox' title="{$this->lang->words['my_checkall']}" id='checkAll' /></th>
		</tr>
HTML;

if( count( $rows ) AND is_array( $rows ) )
{
	foreach( $rows as $row )
	{
		$IPBHTML .= <<<HTML
		<tr>
			<td>{$row['id']}</td>
			<td>{$row['_time']}</td>
			<td>{$row['notify_title']}</td>
			<td>{$row['members_display_name']}</td>
			<td><img src='{$this->settings['skin_acp_url']}/images/{$row['_sent']}' alt='-' /></td>
			<td><input type='checkbox' name='id_{$row['id']}' value='1' class='checkAll' /></td>
		</tr>
HTML;
	}
}
else
{
	$IPBHTML .= <<<HTML
		<tr>
			<td colspan='7' align='center'>{$this->lang->words['mlog_noresults']}</td>
		</tr>
HTML;
}

$IPBHTML .= <<<HTML
	</table>
	<div class='acp-actionbar'>
        <div class='leftaction'>
			{$links}
        </div>
        <div class='rightaction'>
			<input type="checkbox" id="checkbox" name="type" value="all" />&nbsp;{$this->lang->words['erlog_removeall']}&nbsp;<input type="submit" value="{$this->lang->words['erlog_removechecked']}" class="button primary" />
        </div>
	</div>
</div>
</form>
HTML;

//--endhtml--//
return $IPBHTML;
}

}