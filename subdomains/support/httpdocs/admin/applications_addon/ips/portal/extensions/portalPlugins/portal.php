<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Portal plugin: portal
 * Last Updated: $Date: 2010-01-15 10:18:44 -0500 (Fri, 15 Jan 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Portal
 * @link		http://www.invisionpower.com
 * @since		1st march 2002
 * @version		$Revision: 5713 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class ppi_portal extends public_portal_portal_portal 
{
	/**
	 * Initialize module
	 *
	 * @access	public
	 * @return	void
	 */
	public function init()
 	{
 	}
 	
	/**
	 * Show the site navgiational block
	 *
	 * @access	public
	 * @return	string		HTML content to replace tag with
	 */
	public function portal_sitenav()
	{
 		if ( ! $this->settings['csite_nav_show'] )
 		{
 			return;
 		}
 		
 		$links		= array();
 		$raw_nav	= $this->settings['csite_nav_contents'];
 		
 		foreach( explode( "\n", $raw_nav ) as $l )
 		{
 			$l = str_replace( "&#039;", "'", $l );
 			$l = str_replace( "&quot;", '"', $l );
 			$l = str_replace( '{board_url}', $this->settings['base_url'], $l );
 			
 			preg_match( "#^(.+?)\[(.+?)\]$#is", trim($l), $matches );
 			
 			$matches[1] = trim($matches[1]);
 			$matches[2] = trim($matches[2]);
 			
 			if ( $matches[1] and $matches[2] )
 			{
	 			$matches[1] = str_replace( '&', '&amp;', str_replace( '&amp;', '&', $matches[1] ) );
	 			
	 			$links[] = $matches;
 			}
 		}
 		
 		if( !count($links) )
 		{
 			return;
 		}

 		return $this->registry->getClass('output')->getTemplate('portal')->siteNavigation( $links );
  	}
  	
	/**
	 * Show the affiliates block
	 *
	 * @access	public
	 * @return	string		HTML content to replace tag with
	 */
	public function portal_affiliates()
	{
 		if ( ! $this->settings['csite_fav_show'] )
 		{
 			return;
 		}
 		
		$this->settings['csite_fav_contents'] = str_replace( "&#039;", "'", $this->settings['csite_fav_contents'] );
		$this->settings['csite_fav_contents'] = str_replace( "&quot;", '"', $this->settings['csite_fav_contents'] );
		$this->settings['csite_fav_contents'] = str_replace( '{board_url}', $this->settings['base_url'], $this->settings['csite_fav_contents'] );
 		
 		return $this->registry->getClass('output')->getTemplate('portal')->affiliates();
 	}

}