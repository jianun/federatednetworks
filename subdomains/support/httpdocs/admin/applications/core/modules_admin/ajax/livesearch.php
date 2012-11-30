<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Live Search
 * Last Updated: $Date: 2010-07-15 21:39:42 -0400 (Thu, 15 Jul 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Core
 * @link		http://www.invisionpower.com
 * @version		$Rev: 6662 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class admin_core_ajax_livesearch extends ipsAjaxCommand 
{
	/**
	 * HTML to output
	 *
	 * @access	private
	 * @var		string
	 */	
	private $output;

	/**
	 * Skin object
	 *
	 * @access	private
	 * @var		object			Skin templates
	 */
	private $html;
	
	/**
	 * Class entry point
	 *
	 * @access	public
	 * @param	object		Registry reference
	 * @return	void		[Outputs to screen]
	 */
	public function doExecute( ipsRegistry $registry ) 
	{
		//-----------------------------------------
		// Load skin
		//-----------------------------------------
		$this->registry->class_localization->loadLanguageFile( array( 'admin_ajax' ) );
		$this->html = $this->registry->output->loadTemplate('cp_skin_livesearch');
		
		/* What to do */
		switch( $this->request['do'] )
		{
			case 'search':
				$this->doSearchRequest();
			break;
			
			case 'template':
			default:
				$this->getTemplate();
			break;
		}
		
		/* Output */
		$this->returnHtml( $this->output );		
	}
	
	/**
	 * Fetches the live search template
	 *
	 * @access	public
	 * @return	void
	 */
	public function getTemplate()
	{
		$this->output .= $this->html->liveSearchTemplate();
	}
	
	/**
	 * Handles the live search
	 *
	 * @access	public
	 * @return	void
	 */
	public function doSearchRequest()
	{
		/* INI */
		$search_term = $this->request['search_term'];
		
		$results	= array();
		$return		= array( 'members' => null, 'groups' => false, 'settings' => null, 'forums' => null, 'location' => null );
		
		/* Do search here */
		$results	= $this->_getSettings( $search_term, $results );
		$results	= $this->_getFromXML( $search_term, $results );
		$results	= $this->_getMembers( $search_term, $results );
		$results	= $this->_checkGroups( $search_term, $results );
		$results	= $this->_checkForums( $search_term, $results );
		
		//-----------------------------------------
		// Members
		//-----------------------------------------
		
		$secCount = 0;
		
		if( isset( $results['members'] ) AND is_array( $results['members'] ) AND count($results['members']))
		{
			foreach( $results['members'] as $members )
			{
				$secCount++;
				$return['members'] .= $this->html->searchRowMember( $members, $secCount );
			}
		}
		
		//-----------------------------------------
		// Group settings?
		//-----------------------------------------
		
		$secCount = 0;
		
		if( $results['groups'] )
		{
			foreach( $results['groups'] as $group )
			{
				$secCount++;
				$return['groups'] .= $this->html->searchRowGroupsTitles( $group, $secCount );
			}
		}
		
		if( $results['groupLangs'] )
		{
			$return['groups'] .= $this->html->searchRowGroups();
		}
		
		//-----------------------------------------
		// Settings
		//-----------------------------------------
		
		$secCount	= 0;
		
		if( isset( $results['settings'] ) AND is_array( $results['settings'] ) AND count($results['settings']) )
		{
			foreach( $results['settings'] as $setting )
			{
				$secCount++;
				$return['settings'] .= $this->html->searchRowSetting( $setting, $secCount );
			}
		}
		
		//-----------------------------------------
		// Forums
		//-----------------------------------------
		
		$secCount = 0;
		
		if( isset( $results['forums'] ) AND is_array( $results['forums'] ) AND count($results['forums']))
		{
			foreach( $results['forums'] as $forum )
			{
				$secCount++;
				$return['forums'] .= $this->html->searchRowForums( $forum, $secCount );
			}
		}
		
		//-----------------------------------------
		// Locations
		//-----------------------------------------
		
		$secCount = 0;
		
		if( isset( $results['location'] ) AND is_array( $results['location'] ) AND count($results['location']))
		{
			foreach( $results['location'] as $location )
			{
				$secCount++;
				$return['location'] .= $this->html->searchRowLocation( $location, $secCount );
			}
		}
		
		/* Output */
		$this->output .= $this->html->liveSearchDisplay( $return, $search_term );
	}
	
	/**
	 * Searches for matching members
	 *
	 * @access	private
	 * @param	string		Search term
	 * @param	array 		Existing search results
	 * @return	array 		New search results
	 */
	private function _getMembers( $term, $results )
	{
		$term	= strtolower($term);
		
		$this->DB->build( array(
									'select'	=> 'member_id, members_display_name, name, members_l_username, members_l_display_name, email',
									'from'		=> 'members',
									'where'		=> "members_l_username LIKE '%{$term}%' OR members_l_display_name LIKE '%{$term}%' OR " . $this->DB->buildLower('email') . " LIKE '%{$term}%'",
							)		);
		
		$this->DB->execute();
		
		while( $r = $this->DB->fetch() )
		{
			$_matched	= '';
			
			if( $r['members_l_display_name'] AND strpos( $term, $r['members_l_display_name'] ) !== false )
			{
				$r['_matched']	= 'members_display_name';
			}
			else if( $r['members_l_username'] AND strpos( $term, $r['members_l_username'] ) !== false )
			{
				$r['_matched']	= 'name';
			}
			else
			{
				$r['_matched']	= 'email';
			}

			$results['members'][] = $r;
		}
	
		return $results;
	}
	
	/**
	 * Check if search term is found in groups language file or in the group_cache.g_title
	 *
	 * @access	private
	 * @param	string		Search term
	 * @param	array 		Existing search results
	 * @return	array 		New search results
	 */
	private function _checkGroups( $term, $results )
	{
		$term				= strtolower($term);
		$results['groups']	= false;
		
		$this->registry->class_localization->loadLanguageFile( array( 'admin_groups' ), 'members' );
		$this->registry->class_localization->loadLanguageFile( array( 'admin_forums' ), 'forums' );
		$this->registry->class_localization->loadLanguageFile( array( 'admin_gallery' ), 'gallery' );
		$this->registry->class_localization->loadLanguageFile( array( 'admin_blog' ), 'blog' );
		$this->registry->class_localization->loadLanguageFile( array( 'admin_downloads' ), 'downloads' );
		
		foreach( $this->lang->words as $k => $v )
		{
			if( strpos( $k, 'gf_' ) !== false AND strpos( $v, $term ) !== false )
			{
				$results['groupLangs']	= true;
				break;
			}
		}
		
		/* Now check group names */
		$groups = $this->cache->getCache('group_cache');
		
		if ( is_array( $groups ) AND count( $groups ) )
		{
			foreach( $groups as $id => $data )
			{
				$_term = preg_quote( $term, '#' );
				
				if ( preg_match( "#" . $_term . "#i", $data['g_title'] ) )
				{
					$results['groups'][] = $data; 
				}
			}
		}
	
		return $results;
	}
	
	/**
	 * Check if search term is found in groups language file
	 *
	 * @access	private
	 * @param	string		Search term
	 * @param	array 		Existing search results
	 * @return	array 		New search results
	 */
	private function _checkForums( $term, $results )
	{
		$term				= strtolower($term);
		$results['forums']	= false;
		
		/* Fetch forums lib */
		$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir( 'forums' ) . "/sources/classes/forums/class_forums.php", 'class_forums', 'forums' );
		$forums = new $classToLoad( $this->registry );
		$forums->strip_invisible = 1;
		$forums->forumsInit();
	
		/* Now check forum titles names */
		foreach( $forums->forum_by_id as $id => $data )
		{
			$_term = preg_quote( $term, '#' );
			
			if ( preg_match( "#" . $_term . "#i", $data['name'] ) )
			{
				$results['forums'][] = $data; 
			}
		}
		
		return $results;
	}
	
	/**
	 * Searches the settings table
	 *
	 * @access	private
	 * @param	string		Search term
	 * @param	array 		Existing search results
	 * @return	array 		New search results
	 */
	private function _getSettings( $term, $results )
	{
		$term	= strtolower($term);
		
		if( !IN_DEV )
		{
			$this->DB->build( array(
										'select'	=> 'c.conf_group, c.conf_title, c.conf_description, c.conf_keywords',
										'from'		=> array( 'core_sys_conf_settings' => 'c' ),
										'where'		=> 't.conf_title_noshow=0 AND (' . $this->DB->buildLower('c.conf_title') . " LIKE '%{$term}%' OR ". $this->DB->buildLower('c.conf_description') . " LIKE '%{$term}%' OR " . $this->DB->buildLower('c.conf_keywords') . " LIKE '%{$term}%')",
										'add_join'	=> array(
															array(
																'from'	=> array( 'core_sys_settings_titles' => 't' ),
																'where'	=> 't.conf_title_id=c.conf_group',
																'type'	=> 'left'
																)
															)
								)		);
		}
		else
		{
			$this->DB->build( array(
										'select'	=> 'conf_group, conf_title, conf_description, conf_keywords',
										'from'		=> 'core_sys_conf_settings',
										'where'		=> $this->DB->buildLower('conf_title') . " LIKE '%{$term}%' OR ". $this->DB->buildLower('conf_description') . " LIKE '%{$term}%' OR " . $this->DB->buildLower('conf_keywords') . " LIKE '%{$term}%'",
								)		);
		}
		
		$this->DB->execute();
		
		while( $r = $this->DB->fetch() )
		{
			$results['settings'][] = $r;
		}
	
		return $results;
	}
	
	/**
	 * Searches the XML Files
	 *
	 * @access	private
	 * @param	string		Search term
	 * @param	array 		Existing search results
	 * @return	array 		New search results
	 */
	private function _getFromXML( $term, $results )
	{
		foreach( $this->cache->getCache('app_menu_cache') as $app => $cache )
		{
			foreach( $cache as $entry )
			{
				if( count($entry['items']) )
				{
					foreach( $entry['items'] as $item )
					{
						if( $item['section'] )
						{
							$item['url']	= "section={$item['section']}&amp;" . $item['url'];
						}

						if( isset($item['keywords']) AND stripos( $item['keywords'], $term ) !== false )
						{
							$item['url'] = "&amp;app={$app}&amp;module={$item['module']}&amp;{$item['url']}";
							$results['location'][] = $item;
						}
						else if( stripos( $item['title'], $term ) !== false )
						{
							$item['fullurl'] = "&amp;app={$app}&amp;module={$item['module']}&amp;{$item['url']}";
							$results['location'][] = $item;
						}
					}
				}
			}
		}
		
		return $results;
	}
}