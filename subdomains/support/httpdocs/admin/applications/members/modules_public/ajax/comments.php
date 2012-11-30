<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Profile AJAX Comment Handler
 * Last Updated: $Date: 2010-07-14 20:02:58 -0400 (Wed, 14 Jul 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Members
 * @link		http://www.invisionpower.com
 * @since		Tuesday 1st March 2005 (11:52)
 * @version		$Revision: 6654 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class public_members_ajax_comments extends ipsAjaxCommand 
{
	/**
	 * Comments library
	 *
	 * @access	protected
	 * @var		object
	 */
	protected $comments;

	/**
	 * Class entry point
	 *
	 * @access	public
	 * @param	object		Registry reference
	 * @return	void		[Outputs to screen]
	 */
	public function doExecute( ipsRegistry $registry ) 
	{
		$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir( 'members' ) . '/sources/comments.php', 'profileCommentsLib', 'members' );
		$this->comments = new $classToLoad( $this->registry );
		
		switch( $this->request[ 'do' ] )
		{
			case 'view':
			default:
				$this->returnHtml( $this->comments->buildComments( IPSMember::load( intval( $this->request['member_id'] ) ) ) );
			break;

			case 'add':
				$this->_addComment();
			break;
			
			case 'delete':
				$this->_deleteComment();
			break;
			
			case 'approve':
				$this->_approveComment();
			break;
				
			case 'reload':
				$this->_reloadComment();
			break;			
		}
	}

 	/**
	 * Approve a comment on member's profile
	 *
	 * @access	protected
	 * @return	void		[Prints to screen]
	 * @since	IPB 2.2.0.2006-08-02
	 */
 	protected function _approveComment()
 	{
 		//-----------------------------------------
 		// INIT
 		//-----------------------------------------
		
		$member_id			= intval( $this->request['member_id'] );
		$md5check			= IPSText::md5Clean( $this->request['md5check'] );
		$comment_id			= intval( $this->request['comment_id'] );

		//-----------------------------------------
		// MD5 check
		//-----------------------------------------
		
		if (  $md5check != $this->member->form_hash )
    	{
    		$this->returnString( 'error' );
    	}

		//-----------------------------------------
		// Delete
		//-----------------------------------------

    	$result = $this->comments->approveComment( $member_id, $comment_id );
		
		/* Check for error */
		if( $result )
		{
			$this->returnString( $result );
		}
		else
		{
			$this->returnHtml( $this->comments->buildComments( IPSMember::load( $member_id ) ) );
		}
	}
	
 	/**
	 * Reload comments
	 *
	 * @access	protected
	 * @return	void		[Prints to screen]
	 * @since	IPB 2.2.0.2006-08-15
	 */
 	protected function _reloadComment()
 	{
 		//-----------------------------------------
 		// INIT
 		//-----------------------------------------
		
		$member_id		= intval( $this->request['member_id'] );
		$md5check		= IPSText::md5Clean( $this->request['md5check'] );

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
		// Regenerate comments...
		//-----------------------------------------
		
		$this->returnHtml( $this->comments->buildComments( $member ) );
	}
	

 	/**
	 * Deletes a comment on member's profile
	 *
	 * @access	protected
	 * @return	void		[Prints to screen]
	 * @since	IPB 2.2.0.2006-08-02
	 */
 	protected function _deleteComment()
 	{
 		//-----------------------------------------
 		// INIT
 		//-----------------------------------------
		
		$member_id			= intval( $this->request['member_id'] );
		$md5check			= IPSText::md5Clean( $this->request['md5check'] );
		$comment_id			= intval( $this->request['comment_id'] );

		//-----------------------------------------
		// MD5 check
		//-----------------------------------------
		
		if (  $md5check != $this->member->form_hash )
    	{
    		$this->returnString( 'error' );
    	}

		//-----------------------------------------
		// Delete
		//-----------------------------------------

    	$result = $this->comments->deleteComment( $member_id, $comment_id );
		
		/* Check for error */
		if( $result )
		{
			$this->returnString( $result );
		}
		else
		{
			$this->returnHtml( $this->comments->buildComments( IPSMember::load( $member_id ) ) );
		}
	}
	

 	/**
	 * Saves a comment on member's profile
	 *
	 * @access	protected
	 * @return	void		[Prints to screen]
	 * @since	IPB 2.2.0.2006-08-02
	 */
 	protected function _addComment()
 	{
		/* INIT */
		$member_id = intval( $this->request['member_id'] );

		$result = $this->comments->addCommentToDB( $member_id, IPSText::parseCleanValue( $_POST['comment'] ) );
		
		/* Check for error */
		if( $result AND $result != 'pp_comment_added_mod' )
		{
			$this->returnString( $result );
		}
		else
		{
			$this->returnHtml( $this->comments->buildComments( IPSMember::load( $member_id ), $new_id, $return_msg ) );
		}
	}
}