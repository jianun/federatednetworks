<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Member property updater (AJAX)
 * Last Updated: $Date: 2010-04-15 15:46:26 -0400 (Thu, 15 Apr 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Forums
 * @link		http://www.invisionpower.com
 * @version		$Revision: 6133 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class public_forums_ajax_usercp extends ipsAjaxCommand 
{
	/**
	* Main class entry point
	*
	* @access	public
	* @param	object		ipsRegistry reference
	* @return	void		[Outputs to screen]
	*/
	public function doExecute( ipsRegistry $registry )
	{
    	switch( $this->request['do'] )
    	{
			case 'get_avatar_images':
				$this->_getAvatarImages();
			break;
    	}
	}
	
	
	/**
	* Get avatar images in a directory
	*
	* @access	protected
	* @return	void		[Outputs to screen]
	*/
	protected function _getAvatarImages()
	{
		$dir	= urldecode( $this->request['cat'] );
		$images	= IPSMember::getFunction()->getHostedAvatarsFromCategory( $dir );
		
		if ( $images === FALSE )
		{
			$this->returnJsonError( $this->lang->words['ajax_avatar_dir'] );
			exit();
		}
		else
		{
			$output = $this->registry->getClass('output')->getTemplate('ucp')->forumsInlineAvatarImages( $images, $dir );
		
			$this->returnHtml(  $output );
		}
	}
}