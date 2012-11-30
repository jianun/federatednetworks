<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * ACP live search skin file
 * Last Updated: $Date: 2010-07-06 11:07:25 -0400 (Tue, 06 Jul 2010) $
 * </pre>
 *
 * @author 		$Author: matt $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Core
 * @link		http://www.invisionpower.com
 * @since		Friday 19th May 2006 17:33
 * @version		$Revision: 6604 $
 */
 
class cp_skin_livesearch extends output
{
	/**
	 * Currently hiding settings
	 *
	 * @access	private
	 * @var		bool
	 */
	private $startedHideSettings	= false;
	
	/**
	 * Currently hiding page links
	 *
	 * @access	private
	 * @var		bool
	 */
	private $startedHidePages		= false;
	
	/**
	 * Currently hiding members
	 *
	 * @access	private
	 * @var		bool
	 */
	private $startedHideMembers		= false;
	
	/**
	 * Entries until hidden
	 *
	 * @access	private
	 * @var		bool
	 */
	private $displayRows		   = 8;
	
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
 * Display the live search results
 *
 * @access	public
 * @param	array 		Results
 * @param	string		Current search term
 * @return	string		HTML
 */
public function liveSearchDisplay( $results, $search_term ) {
	
$IPBHTML = "";
//--starthtml--//
$IPBHTML .= <<<EOF
	<ul>
EOF;

if( count($results) )
{
	foreach( $results as $key => $output )
	{
		if( $key == 'settings' )
		{
			$text	= $this->lang->words['ls_settings'];
		}
		else if( $key == 'location' )
		{
			$text	= $this->lang->words['ls_acppages'];
		}
		else if( $key == 'members' )
		{
			$text	= $this->lang->words['ls_members'];
		}
		else if( $key == 'groupLangs' )
		{
			$text	= $this->lang->words['ls_groups'];
		}
		else if( $key == 'groups' )
		{
			$text	= $this->lang->words['ls_grouptitles'];
		}
		else if( $key == 'forums' )
		{
			$text	= $this->lang->words['ls_forums'];
		}
		
		if( !$output )
		{
			continue;
		}
		
		$IPBHTML .= <<<EOF
		<li>
			<span class='section'>{$text} <img src='{$this->settings['skin_acp_url']}/_newimages/icons_livesearch/{$key}.png' alt='{$this->lang->words['icon']}' /></span>
			<ol>
				{$output}
			</ol>
EOF;


// If we started hidding members, we need to stop hiding them now...
if( $key == 'members' AND $this->startedHideMembers )
{
	$this->startedHideMembers = false;
	$IPBHTML .= <<<EOF
	</div>
	<div style='text-align: center;' id='hideMembersShow'>
		<a href='#' onclick="$('hideMembersShow').hide();$('hideMembers').show();clearTimeout( ACPLiveSearch.searchTimer['hide'] );return false;">{$this->lang->words['ls_view_more_mem']}</a>
	</div>
EOF;
}
// If we started hiding forums, we need to stop hiding them now...
else if( $key == 'forums' AND $this->startedHideForums )
{
	$this->startedHideForums = false;
	$IPBHTML .= <<<EOF
	</div>
	<div style='text-align: center;' id='hideForumsShow'>
		<a href='#' onclick="$('hideForumsShow').hide();$('hideForums').show();clearTimeout( ACPLiveSearch.searchTimer['hide'] );return false;">{$this->lang->words['ls_view_more_forums']}</a>
	</div>
EOF;
}
// If we started hidding groups, we need to stop hiding them now...
else if( $key == 'groups' AND $this->startedHideGroups )
{
	$this->startedHideGroups = false;
	$IPBHTML .= <<<EOF
	</div>
	<div style='text-align: center;' id='hideGroupsShow'>
		<a href='#' onclick="$('hideGroupsShow').hide();$('hideGroups').show();clearTimeout( ACPLiveSearch.searchTimer['hide'] );return false;">{$this->lang->words['ls_view_more_groups']}</a>
	</div>
EOF;
}
// If we started hidding settings, we need to stop hiding them now...
else if( $key == 'settings' AND $this->startedHideSettings )
{
	$this->startedHideSettings = false;
	$IPBHTML .= <<<EOF
	</div>
	<div style='text-align: center;' id='hideSettingsShow'>
		<a href='#' onclick="$('hideSettingsShow').hide();$('hideSettings').show();clearTimeout( ACPLiveSearch.searchTimer['hide'] );return false;">{$this->lang->words['ls_view_more_settings']}</a>
	</div>
EOF;
}
// If we started hidding pages, we need to stop hiding them now...
else if( $key == 'location' AND $this->startedHidePages )
{
	$this->startedHidePages = false;
	$IPBHTML .= <<<EOF
	</div>
	<div style='text-align: center;' id='hidePagesShow'>
		<a href='#' onclick="$('hidePagesShow').hide();$('hidePages').show();clearTimeout( ACPLiveSearch.searchTimer['hide'] );return false;">{$this->lang->words['ls_view_more_acp']}</a>
	</div>
EOF;
}

$IPBHTML .= <<<EOF
		</li>
EOF;
	}
}
else
{
	$IPBHTML .= <<<EOF
	<li><em>{$this->lang->words['lsnoresults']}</em></li>
EOF;
}

$IPBHTML .= <<<EOF
	</ul>
EOF;

//--endhtml--//
return $IPBHTML;
}

/**
 * Group language results
 *
 * @access	public
 * @return	string		HTML
 */
public function searchRowGroups() {
$IPBHTML = "";

//--starthtml--//
$IPBHTML .= <<<EOF
<li><a href='{$this->settings['_base_url']}app=members&amp;module=groups&amp;section=groups&amp;do=groups_overview'>{$this->lang->words['ls_groupresults']}</a></li>
EOF;
//--endhtml--//
return $IPBHTML;
}

/**
 * Group title results
 *
 * @access	public
 * @param	array 		Result
 * @param	int			Section count
 * @return	string		HTML
 */
public function searchRowGroupsTitles( $r, $secCount ) {
$IPBHTML = "";

if( $secCount > 10 )
{
	if( !$this->startedHideGroups )
	{
		$this->startedHideGroups = true;
		
		$IPBHTML .= <<<EOF
		</ol>
		<div id='hideGroups' style='display:none;'>
		<ol style='margin-top:0px;'>
EOF;
	}
}

//--starthtml--//
$IPBHTML .= <<<EOF
<li><a href='{$this->settings['_base_url']}&amp;app=members&amp;module=groups&amp;section=groups&amp;do=edit&id={$r['g_id']}'>{$r['g_title']}</a></li>
EOF;
//--endhtml--//
return $IPBHTML;
}

/**
 * Result for member
 *
 * @access	public
 * @param	array 		Result
 * @param	int			Section count
 * @return	string		HTML
 */
public function searchRowMember( $r, $secCount ) {
$IPBHTML = "";
//--starthtml--//

if( $secCount > $this->displayRows )
{
	if( !$this->startedHideMembers )
	{
		$this->startedHideMembers = true;
		
		$IPBHTML .= <<<EOF
		</ol>
		<div id='hideMembers' style='display:none;'>
		<ol style='margin-top:0px;'>
EOF;
	}
}

$text	= $r[ $r['_matched'] ];

$IPBHTML .= <<<EOF
<li><a href='{$this->settings['_base_url']}&amp;app=members&amp;module=members&amp;section=members&amp;do=viewmember&amp;member_id={$r['member_id']}'>{$text} ({$this->lang->words['livesearchmemid']} {$r['member_id']})</a></li>
EOF;
//--endhtml--//
return $IPBHTML;
}

/**
 * Result for forums
 *
 * @access	public
 * @param	array 		Result
 * @param	int			Section count
 * @return	string		HTML
 */
public function searchRowForums( $r, $secCount ) {
$IPBHTML = "";
//--starthtml--//

if( $secCount > $this->displayRows )
{
	if( !$this->startedHideForums )
	{
		$this->startedHideForums = true;
		
		$IPBHTML .= <<<EOF
		</ol>
		<div id='hideForums' style='display:none;'>
		<ol style='margin-top:0px;'>
EOF;
	}
}
$IPBHTML .= <<<EOF
<li><a href='{$this->settings['_base_url']}&amp;app=forums&amp;module=forums&amp;section=forums&amp;do=edit&amp;f={$r['id']}'>{$r['name']}</a></li>
EOF;
//--endhtml--//
return $IPBHTML;
}
	
/**
 * Result for settings
 *
 * @access	public
 * @param	array 		Result
 * @param	int			Section count
 * @return	string		HTML
 */
public function searchRowSetting( $r, $secCount ) {
$IPBHTML = "";
//--starthtml--//

if( $secCount > $this->displayRows )
{
	if( !$this->startedHideSettings )
	{
		$this->startedHideSettings = true;
		
		$IPBHTML .= <<<EOF
		</ol>
		<div id='hideSettings' style='display:none;'>
		<ol style='margin-top:0px;'>
EOF;
	}
}
$IPBHTML .= <<<EOF
<li><a href='{$this->settings['_base_url']}&amp;app=core&amp;module=tools&amp;section=settings&amp;do=setting_view&amp;conf_group={$r['conf_group']}&amp;search={$this->request['search_term']}'>{$r['conf_title']}</a></li>
EOF;
//--endhtml--//
return $IPBHTML;
}

/**
 * Result for acp pages
 *
 * @access	public
 * @param	array 		Result
 * @param	int			Section count
 * @return	string		HTML
 */
public function searchRowLocation( $r, $secCount ) {
$IPBHTML = "";

if( $secCount > $this->displayRows )
{
	if( !$this->startedHidePages )
	{
		$this->startedHidePages = true;
		
		$IPBHTML .= <<<EOF
		</ol>
		<div id='hidePages' style='display:none;'>
		<ol style='margin-top:0px;'>
EOF;
	}
}

//--starthtml--//
$IPBHTML .= <<<EOF
<li><a href='{$this->settings['_base_url']}{$r['fullurl']}'>{$r['title']}</a></li>
EOF;
//--endhtml--//
return $IPBHTML;
}

/**
 * Live search template
 *
 * @access	public
 * @return	string		HTML
 */
public function liveSearchTemplate() {
$IPBHTML = "";
//--starthtml--//
$IPBHTML .= <<<EOF
	<div id='search_stem'></div>
	<div id='search_inner'>
		<h3 class='bar'>{$this->lang->words['ls_quick_search']}</h3>
		<div id='ajax_result'></div>
	</div>
EOF;
//--endhtml--//
return $IPBHTML;
}

}