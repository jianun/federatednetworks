<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Blog API file
 * Last Updated: $Date: 2010-07-08 10:19:25 -0400 (Thu, 08 Jul 2010) $
 * </pre>
 *
 * @author 		$Author: mark $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Blog
 * @link		http://www.invisionpower.com
 * @version		$Rev: 6617 $
 * @since		2.2.0
 *
 */

if ( ! class_exists( 'apiCore' ) )
{
	require_once( IPS_ROOT_PATH . 'api/api_core.php' );
}

/**
 * apiBlog class
 * @package	IP.Blog
 */
class apiBlog extends apiCore
{
	/**
	 * Constructor.  Calls parent init() method
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct()
	{
		$this->init();
		
		if( ! ipsRegistry::isClassLoaded( 'blogFunctions' ) )
		{
			/* Load the Blog functions library */
			require_once( IPSLib::getAppDir( 'blog' ) . '/sources/classes/blogFunctions.php' );
			$this->registry->setClass( 'blogFunctions', new blogFunctions( $this->registry ) );
		}
	}
	
	/**
	* Retrieve the Blog ID of a member
	* THIS IS DEPRECATED - use fetchBlogIds
	*
	* @param	int		Member ID
	* @return 	int		Blog ID ( 0 if no Blog found )
	*/
	public function getBlogID( $member_id=0 )
	{
		$member_id = intval( $member_id );

		//-----------------------------------------
		// We entered here on member id, find blog_id and redirect them to here
		//-----------------------------------------
		$blog = $this->DB->buildAndFetch( array( 
												'select'	=> 'blog_id, blog_name',
												'from'		=> 'blog_blogs',
												'where'		=> "member_id={$member_id}" 
										)	);
		if( ! $blog['blog_id'] or ! $blog['blog_name'] )
		{
			return 0;
   		}
		else
		{
			return $blog['blog_id'];
		}
	}
	
	/**
	 * Retrieve blog IDs this member can author in
	 *
	 * @access	public
	 * @param	int		Member ID
	 * @return	array	array of ids with basic information, like: array( blog_id => array( 'blog_name', 'blog_seo_name' )
	 */
	public function fetchBlogIds( $member_id=0 )
	{
		$return    = array();
		$member_id = intval( $member_id );
		$data      = $this->registry->getClass('blogFunctions')->fetchMyBlogs( IPSMember::load( $member_id ) );
		
		if ( is_array( $data ) AND count( $data ) )
		{
			foreach( $data as $id => $blog )
			{
				if ( $blog['_canPostIn'] )
				{
					$return[ $id ] = $blog;
				}
			}
		}
		
		return $return;
	}
	
	/**
	* Retrieve the Blog ID of a member
	*
	* @param	int		Blog ID
	* @return 	string	Blog URL
	*/
	public function getBlogUrl( $blog_id=0 )
	{
		$blog_id = intval( $blog_id );

		//-----------------------------------------
		// We entered here on member id, find blog_id and redirect them to here
		//-----------------------------------------
		$blog = $this->DB->buildAndFetch( array ( 
												'select'	=> 'blog_id, blog_name, blog_seo_name',
												'from'		=> 'blog_blogs',
												'where'		=> "blog_id={$blog_id}" 
										)	);
		if( ! $blog['blog_id'] or ! $blog['blog_name'] )
		{
			return '';
   		}
		else
		{
			return $this->registry->output->buildSEOUrl( 'app=blog&amp;blogid=' . $blog_id, 'public', $blog['blog_seo_name'], 'showblog' );
		}
	}

	/**
	* Retrieve the last x entries of a Blog
	* Only public publishes entries are returned
	*
	* @param	string	Either 'member' or 'blog'
	* @param	int		Blog ID or Member ID
	* @param	int		Number of entries (to return)
	* @return 	array	Array of last X entries
	*/
	public function lastXEntries( $type, $id, $number_of_entries=10 )
	{
		/* INIT */
		$id           = intval( $id );
		$return_array = array();
		$where		  = array();
		
        /* Build permissions */
		$modData = $this->registry->blogFunctions->buildPerms();

		/* Got permission to view blog entries? */
		if ( $this->memberData['g_blog_settings']['g_blog_allowview'] )
		{						
			$this->DB->build( array( 'select'	=> "e.*, b.*",
							         'from'		=> array('blog_entries' => 'e'),
							         'where'    => ( $type == 'member' ? "b.member_id={$id}" : "b.blog_id={$id}" ) . " AND b.blog_type='local' AND e.entry_status='published'",
									 'order'	=> 'e.entry_date DESC',
									 'limit'	=> array( 0, intval( $number_of_entries ) ),
						             'add_join' => array( array( 'select' => 'b.blog_name, b.blog_seo_name',
																'from'   => array( 'blog_blogs' => 'b' ),
																'where'  => "e.blog_id=b.blog_id",
																'type'   => 'left' ) ) ) );
										 
			$outer = $this->DB->execute();
			
			while( $entry = $this->DB->fetch($outer) )
			{
				if ( !$this->memberData['member_id'] or !$modData['_blogmod']['moderate_can_view_private'] )
				{
					switch ( $entry['blog_view_level'] )
					{
						case 'public':
							if ( !$this->memberData['member_id'] and !$entry['blog_allowguests'] )
							{
								continue 2;
							}
							break;
							
						case 'private':
							if ( !$this->memberData['member_id'] or $this->memberData['member_id'] != $entry['member_id'] )
							{
								continue 2;
							}
							break;
							
						case 'privateclub':
							if ( !$this->memberData['member_id'] or !in_array( $this->memberData['member_id'], explode( ',', $entry['blog_authorized_users'] ) ) )
							{
								continue 2;
							}
							break;
					}
				}
			
				$entry['blog_url']  = $this->registry->blogFunctions->getBlogUrl( $entry['blog_id'] );
				$entry['entry_url'] = $entry['blog_url'] . 'showentry='.$entry['entry_id'];
				
				$return_array[] = $entry;
			}
			return $return_array;
	  	}
		else
		{
			return array();
		}
	}

}

?>