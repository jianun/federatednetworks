<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Profile AJAX Tab Loader
 * Last Updated: $Date: 2010-04-15 17:05:54 -0400 (Thu, 15 Apr 2010) $
 * </pre>
 *
 * @author 		$Author: josh $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Members
 * @link		http://www.invisionpower.com
 * @since		Tuesday 1st March 2005 (11:52)
 * @version		$Revision: 6136 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class public_members_ajax_load extends ipsAjaxCommand 
{
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
 		// INIT
 		//-----------------------------------------
		
		$member_id = intval( ipsRegistry::$request['member_id'] );
		$md5check  = IPSText::md5Clean( $this->request['md5check'] );
		
		$tab = explode( ':', ipsRegistry::$request['tab'] );
		$app = substr( IPSText::alphanumericalClean( str_replace( '..', '', trim( $tab[0] ) ) ), 0, 20 );
		$tab = substr( IPSText::alphanumericalClean( str_replace( '..', '', trim( $tab[1] ) ) ), 0, 20 );

		$this->registry->class_localization->loadLanguageFile( array( 'public_profile' ), 'members' );

		//-----------------------------------------
		// MD5 check
		//-----------------------------------------
		
		if (  $md5check != $this->member->form_hash )
    	{
			$this->returnString( 'error' );
    	}

		//-----------------------------------------
		// Load member
		//-----------------------------------------
		
		$member = IPSMember::load( $member_id );
    	
		//-----------------------------------------
		// Check
		//-----------------------------------------

    	if ( ! $member['member_id'] )
    	{
			$this->returnString( 'error' );
    	}
		
		//-----------------------------------------
		// Load config
		//-----------------------------------------

		if( !file_exists( IPSLib::getAppDir( $app ) . '/extensions/profileTabs/' . $tab . '.conf.php' ) )
		{
			$this->returnString( 'error' );
		}
		
		require( IPSLib::getAppDir( $app ) . '/extensions/profileTabs/' . $tab . '.conf.php' );
		
		//-----------------------------------------
		// Active?
		//-----------------------------------------
		
		if ( ! $CONFIG['plugin_enabled'] )
		{
			$this->returnString( 'error' );
		}
		
		//-----------------------------------------
		// Load main class...
		//-----------------------------------------
		
		if( !file_exists( IPSLib::getAppDir( $app ) . '/extensions/profileTabs/' . $tab . '.php' ) )
		{
			$this->returnString( 'error' );
		}
		
		require( IPSLib::getAppDir( 'members' ) . '/sources/tabs/pluginParentClass.php' );
		require( IPSLib::getAppDir( $app ) . '/extensions/profileTabs/' . $tab . '.php' );
		$_func_name       = 'profile_' . $tab;
		$plugin           =  new $_func_name( $this->registry );

		$html = $plugin->return_html_block( $member );
		
		//-----------------------------------------
		// Return it...
		//-----------------------------------------

		$this->returnHtml( $html );
	}
}