<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Silly toggle function(?s{0,})
 * Last Updated: $Date: 2010-06-08 20:53:45 -0400 (Tue, 08 Jun 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Forums
 * @link		http://www.invisionpower.com
 * @since		20th February 2002
 * @version		$Revision: 6485 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class public_forums_extras_toggle extends ipsCommand
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
		//-----------------------------------------
		// Lang & skin
		//-----------------------------------------
	
		ipsRegistry::getClass( 'class_localization')->loadLanguageFile( array( 'public_forums' ) );

		//-----------------------------------------
		// What to do?
		//-----------------------------------------
		
		switch( $this->request['do'] )
		{
			case 'sidepanel':
			default:
				$this->_toggleSidePanel();
			break;
		}
		
		// If we have any HTML to print, do so...
		
		$this->registry->output->addContent( $this->output );
		$this->registry->output->sendOutput();
 	}
 	
 	/**
 	 * Toggle side panel on/off without JS
 	 *
 	 * @access	public
 	 * @return	void
 	 * @see		The Dark Knight (it was an awesome movie)
 	 */
 	public function _toggleSidePanel()
 	{
		/* Security Check */
		if ( $this->request['secure_key'] != $this->member->form_hash )
		{
			$this->registry->output->showError( 'usercp_forums_bad_key', 102998, null, null, 403 );
		}
		
 		$current	= IPSCookie::get('hide_sidebar');
 		$new		= $current ? 0 : 1;
 		
 		IPSCookie::set( 'hide_sidebar', $new );
 		
 		$this->registry->getClass('output')->silentRedirect( $this->settings['base_url'] . 'act=idx', 'false' );
 	}
 
}
