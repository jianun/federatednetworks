<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Login handler abstraction : AJAX Find Names functions
 * Last Updated: $Date: 2010-02-22 08:06:40 -0500 (Mon, 22 Feb 2010) $
 * </pre>
 *
 * @author 		$Author: josh $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Core
 * @link		http://www.invisionpower.com
 * @since		Tuesday 1st March 2005 (11:52)
 * @version		$Revision: 5857 $
 * @see			public_core_ajax_findnames
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class admin_core_ajax_findnames extends ipsAjaxCommand 
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
    	switch( $this->request['do'] )
    	{
			case 'get-member-names':
    			$this->_getMemberNames();
    		break;
    	}
	}
	
	/**
	 * Returns possible matches for the string input
	 *
	 * @access	private
	 * @return	void		Outputs to screen
	 */
	private function _getMemberNames()
	{
    	//-----------------------------------------
    	// INIT
    	//-----------------------------------------

    	$name = IPSText::convertUnicode( $this->convertAndMakeSafe( ipsRegistry::$request['name'], 0 ) );

    	//-----------------------------------------
    	// Check length
    	//-----------------------------------------

    	if ( IPSText::mbstrlen( $name ) < 3 )
    	{
    		$this->returnJsonError( 'requestTooShort' );
    	}

    	//-----------------------------------------
    	// Try query...
    	//-----------------------------------------

    	$this->DB->build( array( 'select'	=> 'm.members_display_name, m.name, m.member_id, m.member_group_id',
    							 'from'	    => array( 'members' => 'm' ),
    							 'where'	=> "m.members_l_display_name LIKE '" . $this->DB->addSlashes( strtolower( $name ) ) . "%'",
    							 'order'	=> $this->DB->buildLength( 'm.members_display_name' ) . ' ASC',
    							 'limit'	=> array( 0, 15 ),
 								 'add_join' => array( array( 'select' => 'p.*',
														     'from'   => array( 'profile_portal' => 'p' ),
														     'where'  => 'p.pp_member_id=m.member_id',
														     'type'   => 'left' ) ) ) );
		$this->DB->execute();

    	//-----------------------------------------
    	// Got any results?
    	//-----------------------------------------

    	if ( ! $this->DB->getTotalRows() )
 		{
    		$this->returnJsonArray( array( ) );
    	}

    	$return = array();

		while( $r = $this->DB->fetch() )
		{
			$photo = IPSMember::buildProfilePhoto( $r );
			$group = IPSLib::makeNameFormatted( '' , $r['member_group_id'] );
			$return[ $r['member_id'] ] = array( 'name' 	=> $r['members_display_name'],
												'showas'=> '<strong>' . $r['members_display_name'] . '</strong> (' . $group . ')',
												'img'	=> $photo['pp_thumb_photo'],
												'img_w'	=> $photo['pp_mini_width'],
												'img_h'	=> $photo['pp_mini_height']
											);
		}

		$this->returnJsonArray( $return );
	}
}