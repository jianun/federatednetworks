<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Captcha
 * Last Updated: $Date: 2010-01-15 15:18:44 +0000 (Fri, 15 Jan 2010) $
 * </pre>
 *
 * @author 		$Author $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Core
 * @link		http://www.invisionpower.com
 * @since		20th February 2002
 * @version		$Rev: 5713 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class public_core_global_share extends ipsCommand
{
	/**
	 * Class entry point
	 *
	 * @access	public
	 * @param	object		Registry reference
	 * @return	void		[Outputs to screen/redirects]
	 */
	public function doExecute( ipsRegistry $registry ) 
	{
		$url   = IPSText::base64_decode_urlSafe( $this->request['url'] );
		$title = IPSText::base64_decode_urlSafe( $this->request['title'] );
		$key   = trim( $this->request['key'] );
		
		/* Get the lib */
		require_once( IPS_ROOT_PATH . 'sources/classes/share/links.php' );
		$share = new share_links( $registry, $key );
		
		/* Share! */
		$share->share( $title, $url );
	}
}