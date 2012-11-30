<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * RSS output plugin :: posts
 * Last Updated: $Date: 2010-01-15 10:18:44 -0500 (Fri, 15 Jan 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Forums
 * @link		http://www.invisionpower.com
 * @since		6/24/2008
 * @version		$Revision: 5713 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class furlRedirect_members
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
	}

	/**
	 * Set the key ID
	 * <code>furlRedirect_forums::setKey( 'topic', 12 );</code>
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
				if ( $k == 'showuser' )
				{
					$this->setKey( 'user', intval( $v ) );
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
			case 'user':
				return $this->_fetchSeoTitle_user();
			break;
		}
	}

	/**
	* Return the SEO title for a user
	*
	* @access	public
	* @return	string		The SEO friendly name
	*/
	public function _fetchSeoTitle_user()
	{
		$member = IPSMember::load( intval( $this->_id ), 'core' );
												
		if ( $member['member_id'] )
		{
			return ( $member['members_seo_name'] ) ? $member['members_seo_name'] : IPSText::makeSeoTitle( $member['members_display_name'] );
		}
		
		return FALSE;
	}
}