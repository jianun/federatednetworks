<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Returns HTML for the form (optional class, not required)
 * Last Updated: $Date: 2010-02-19 01:29:54 +0000 (Fri, 19 Feb 2010) $
 * </pre>
 *
 * @author 		$author$
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Forums
 * @link		http://www.invisionpower.com
 * @version		$Rev: 5855 $
 **/

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class search_form_forums
{
	/**
	 * Construct
	 *
	 */
	public function __construct()
	{
		/* Make object */
		$this->registry   =  ipsRegistry::instance();
		$this->DB         =  $this->registry->DB();
		$this->settings   =& $this->registry->fetchSettings();
		$this->request    =& $this->registry->fetchRequest();
		$this->lang       =  $this->registry->getClass('class_localization');
		$this->member     =  $this->registry->member();
		$this->memberData =& $this->registry->member()->fetchMemberData();
		$this->cache      =  $this->registry->cache();
		$this->caches     =& $this->registry->cache()->fetchCaches();
	}
	
	/**
	 * Return sort drop down
	 * 
	 *
	 * @access	public
	 * @return	array
	 */
	public function fetchSortDropDown()
	{
		$array = array( 'date'  => $this->lang->words['s_search_type_0'],
					    'title' => $this->lang->words['forum_sort_title'],
					    'posts' => $this->lang->words['forum_sort_posts'],
					    'views' => $this->lang->words['forum_sort_views'] );
					    
		if ( $this->settings['search_method'] == 'sphinx' )
		{
			//unset( $array['title'] );
		}
		
		return $array;
	}
	
	/**
	 * Retuns the html for displaying the forum category filter on the advanced search page
	 *
	 * @access	public
	 * @return	string	Filter HTML
	 **/
	public function getHtml()
	{
		/* Make sure class_forums is setup */
		if( ipsRegistry::isClassLoaded('class_forums') !== TRUE )
		{
			require_once( IPSLib::getAppDir( 'forums' ) . "/sources/classes/forums/class_forums.php" );
			ipsRegistry::setClass( 'class_forums', new class_forums( ipsRegistry::instance() ) );
		}
		
		ipsRegistry::getClass( 'class_forums' )->strip_invisible = 1;
		ipsRegistry::getClass( 'class_forums' )->forumsInit();

		return array( 'title' => ipsRegistry::$applications['forums']['app_public_title'], 'html' => ipsRegistry::getClass( 'output' )->getTemplate( 'search' )->forumAdvancedSearchFilters( ipsRegistry::getClass( 'class_forums' )->buildForumJump( 0, 1 ) ) );
	}

}
