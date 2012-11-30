<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Ajax Functions For Topics
 * Last Updated: $Date: 2010-01-15 10:18:44 -0500 (Fri, 15 Jan 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Forums
 * @link		http://www.invisionpower.com
 * @version		$Revision: 5713 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class public_forums_ajax_markasread extends ipsAjaxCommand
{
	/**
	 * IPS command execution
	 *
	 * @access	public
	 * @param	object		ipsRegistry reference
	 * @return	void
	 */
	public function doExecute( ipsRegistry $registry )
	{
		/* Ensure cookies are sent */
		$this->settings['no_print_header'] = 0;
		
		//-----------------------------------------
		// What are we doing?
		//-----------------------------------------
		
		switch( $this->request['marktype'] )
		{
			default:
			case 'forum':
				return $this->markForumAsRead();
			break;
		}
	}
	
	/**
	 * Marks the specified forum as read
	 *
	 * @access	public
	 * @return	void
	 **/
	public function markForumAsRead()
	{
		//-----------------------------------------
    	// INIT
    	//-----------------------------------------
    	
		$forum_id      = intval( $this->request['forumid'] );
        //$return_to_id  = intval( $this->request['returntoforumid'] );
        $forum_data    = $this->registry->getClass('class_forums')->forum_by_id[ $forum_id ];
        $children      = $this->registry->getClass('class_forums')->forumsGetChildren( $forum_data['id'] );
        $save          = array();
        
        //-----------------------------------------
        // Check
        //-----------------------------------------
        
        if ( ! $forum_data['id'] )
        {
        	$this->returnJsonError( 'markread_no_id' );
        }

		/* Turn off instant updates and write back tmp markers in destructor */
		$this->registry->classItemMarking->disableInstantSave();
        
        //-----------------------------------------
        // Come from the index? Add kids
        //-----------------------------------------
       
        if ( $this->request['i'] )
        {
			if ( is_array( $children ) and count($children) )
			{
				foreach( $children as $id )
				{
					$this->registry->classItemMarking->markRead( array( 'forumID' => $id ) );
				}
			}
        }
        
        //-----------------------------------------
        // Add in the current forum...
        //-----------------------------------------
        
        $this->registry->classItemMarking->markRead( array( 'forumID' => $forum_id ) );


		$this->returnJsonArray( array( 'result' => 'success') );
	}
}