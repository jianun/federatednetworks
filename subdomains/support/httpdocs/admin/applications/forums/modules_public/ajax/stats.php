<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Retrieve who posted stats
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

class public_forums_ajax_stats extends ipsAjaxCommand 
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
			case 'who':
				$this->_whoPosted();
			break;
    	}
	}

	/**
	 * Retrieve posters in a given topic
	 *
	 * @access	protected
	 * @return	void		[Outputs to screen]
	 */
	protected function _whoPosted()
	{
		ipsRegistry::getClass( 'class_localization')->loadLanguageFile( array( 'public_stats' ), 'forums' );
		
		require_once( IPSLib::getAppDir('forums') . '/modules_public/extras/stats.php' );
		$stats	= new public_forums_extras_stats( $this->registry );
		$stats->makeRegistryShortcuts( $this->registry );
		
		$output	= $stats->whoPosted( true );
		
		if ( !$output )
		{
			$this->returnJsonError( $this->lang->words['ajax_nohtml_return'] );
		}
		else
		{
			$this->returnHtml( $this->registry->getClass('output')->getTemplate('stats')->whoPostedAjaxWrapper( $output ) );
		}
	}
}