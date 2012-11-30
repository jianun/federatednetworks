<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * RSS output plugin :: posts
 * Last Updated: $Date: 2010-07-10 01:01:06 -0400 (Sat, 10 Jul 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Forums
 * @link		http://www.invisionpower.com
 * @since		6/24/2008
 * @version		$Revision: 6628 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class furlRedirect_forums
{	
	/**
	 * Key type: Type of action (topic/forum)
	 *
	 * @access	private
	 * @var		string
	 */
	private $_type = '';
	
	/**
	 * Key ID
	 *
	 * @access	private
	 * @var		int
	 */
	private $_id = 0;
	
	/**
	* Constructor
	*
	*/
	function __construct( ipsRegistry $registry )
	{
		$this->registry =  $registry;
		$this->DB       =  $registry->DB();
		$this->settings =& $registry->fetchSettings();
		$this->cache    = $this->registry->cache();
		$this->caches   =& $this->registry->cache()->fetchCaches();
	}

	/**
	 * Set the key ID
	 * @example		furlRedirect_forums::setKey( 'topic', 12 );
	 *
	 * @access	public
	 * @param	string	Type
	 * @param	mixed	Value
	 */
	public function setKey( $name, $value )
	{
		$this->_type = $name;
		$this->_id   = $value;
	}
	
	/**
	 * Set up the key by URI
	 *
	 * @access	public
	 * @param	string		URI (example: index.php?showtopic=5&view=getlastpost)
	 * @return	void
	 */
	public function setKeyByUri( $uri )
	{
		$uri = str_replace( '&amp;', '&', $uri );
		
		if ( strstr( $uri, '?' ) )
		{
			list( $_chaff, $uri ) = explode( '?', $uri );
		}
		
		foreach( explode( '&', $uri ) as $bits )
		{
			list( $k, $v ) = explode( '=', $bits );
			
			if ( $k )
			{
				if ( $k == 'showtopic' )
				{
					$this->setKey( 'topic', intval( $v ) );
					return TRUE;
				}
				else if ( $k == 'showforum' )
				{
					$this->setKey( 'forum', intval( $v ) );
					return TRUE;
				}
				else if( $k == 'showannouncement' )
				{
					$this->setKey( 'announcement', intval( $v ) );
					return TRUE;
				}
			}
		}
		
		return FALSE;
	}
	
	/**
	* Return the SEO title
	*
	* @access	public
	* @return	string		The SEO friendly name
	*/
	public function fetchSeoTitle()
	{
		switch ( $this->_type )
		{
			default:
				return FALSE;
			break;
			case 'topic':
				return $this->_fetchSeoTitle_topic();
			break;
			case 'forum':
				return $this->_fetchSeoTitle_forum();
			break;
			case 'announcement':
				return $this->_fetchSeoTitle_announcement();
			break;
		}
	}
	
	/**
	 * Return the SEO title for a topic
	 *
	 * @access	public
	 * @return	string		The SEO friendly name
	 */
	public function _fetchSeoTitle_topic()
	{
		$topic = $this->DB->buildAndFetch( array( 'select' => 'tid, title_seo, title, forum_id',
												  'from'   => 'topics',
												  'where'  => 'tid=' . intval( $this->_id ) ) );
												
		if ( $topic['tid'] )
		{
			/* Check permission */
			if ( ! $this->registry->getClass('class_forums')->forumsCheckAccess( $topic['forum_id'], 0, 'topic', $topic, TRUE ) )
			{
				return FALSE;
			}
						
			return ( $topic['title_seo'] ) ? $topic['title_seo'] : IPSText::makeSeoTitle( $topic['title'] );
		}
		
		return FALSE;
	}
	
	/**
	 * Return the SEO title for a forum
	 *
	 * @access	public
	 * @return	string		The SEO friendly name
	 */
	public function _fetchSeoTitle_forum()
	{
		$forum = $this->DB->buildAndFetch( array( 'select' => 'id, name_seo, name',
												  'from'   => 'forums',
												  'where'  => 'id=' . intval( $this->_id ) ) );
												
		if ( $forum['id'] )
		{
			/* Check permission */
			if ( ! $this->registry->getClass('class_forums')->forumsCheckAccess( $forum['id'], 0, 'forum', array(), TRUE ) )
			{
				return FALSE;
			}
			
			return ( $forum['name_seo'] ) ? $forum['name_seo'] : IPSText::makeSeoTitle( $forum['name'] );
		}
		
		return FALSE;
	}
	
	/**
	 * Return the SEO title for an announcement
	 *
	 * @access	public
	 * @return	string		The SEO friendly name
	 */
	public function _fetchSeoTitle_announcement()
	{
		//$announce = $this->DB->buildAndFetch( array( 'select' => 'announce_id, announce_title',
		//										  'from'   => 'announcements',
		//										  'where'  => 'announce_id=' . intval( $this->_id ) ) );
		$announce	= $this->caches['announcements'][ intval( $this->_id ) ];

		if ( $announce['announce_id'] )
		{
			return $announce['announce_seo_title'] ? $announce['announce_seo_title'] : IPSText::makeSeoTitle( $announce['announce_title'] );
		}
		
		return FALSE;
	}
}