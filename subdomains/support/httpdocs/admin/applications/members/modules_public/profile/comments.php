<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Profile View
 * Last Updated: $Date: 2010-07-01 18:13:46 -0400 (Thu, 01 Jul 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Members
 * @link		http://www.invisionpower.com
 * @since		20th February 2002
 * @version		$Revision: 6596 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class public_members_profile_comments extends ipsCommand
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
		// Get HTML and skin
		//-----------------------------------------

		$this->registry->class_localization->loadLanguageFile( array( 'public_profile' ), 'members' );

		//-----------------------------------------
		// Can we access?
		//-----------------------------------------
		
		if ( !$this->memberData['g_mem_info'] )
 		{
 			$this->registry->output->showError( 'comments_profiles', 10231 );
		}

		switch( $this->request['do'] )
		{
			default:
				die('');
			break;
			
			case 'save':
				$this->_saveComment();
			break;
			
			case 'delete':
				$this->_deleteComment();
			break;
			
			case 'approve':
				$this->_approveComment();
			break;
			
			case 'add_new_comment':
				$this->_doAddComment();
			break;
		}
	}

 	/**
	 * Approve a comment on member's profile
	 *
	 * @access	private
	 * @return	void		[Prints to screen]
	 * @since	IPB 2.2.0.2006-08-02
	 */
 	private function _approveComment()
 	{
		/* Security Check */
		if ( $this->request['secure_key'] != $this->member->form_hash )
		{
			$this->registry->output->showError( 'usercp_forums_bad_key', 103999, null, null, 403 );
		}
		
 		//-----------------------------------------
 		// INIT
 		//-----------------------------------------
		
		$member_id			= intval( $this->request['member_id'] );
		$comment_id			= intval( $this->request['comment_id'] );
		//-----------------------------------------
		// Try it. You might like it.
		//-----------------------------------------
		
		$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir( 'members' ) . '/sources/comments.php', 'profileCommentsLib', 'members' );
		$comment_lib = new $classToLoad( $this->registry );
		
		$result = $comment_lib->approveComment( $member_id, $comment_id );
		
		/* Check for error */
		if( $result )
		{
			$this->registry->output->showError( $result, 10232 );
		}
		else
		{
			$member = IPSMember::load( $member_id );
			$this->registry->output->redirectScreen( $this->lang->words['comment_was_approved'], $this->settings['base_url'] . 'showuser=' . $member_id, $member['members_seo_name'] );
		}
	}
	
 	/**
	 * Deletes a comment on member's profile
	 *
	 * @access	private
	 * @return	void		[Prints to screen]
	 * @since	IPB 2.2.0.2006-08-02
	 */
 	private function _deleteComment()
 	{
		//-----------------------------------------
		// Check form hash
		//-----------------------------------------
		
		$this->request['secure_key'] = $this->request['secure_key'] ? $this->request['secure_key'] : $this->request['md5check'];

		if( $this->request['secure_key'] != $this->member->form_hash )
		{
			$this->registry->output->showError( 'usercp_forums_bad_key', 1023222 );
		}
		
 		//-----------------------------------------
 		// INIT
 		//-----------------------------------------
		
		$member_id			= intval( $this->request['member_id'] );
		$comment_id			= intval( $this->request['comment_id'] );
		
		//-----------------------------------------
		// Try it. You might like it.
		//-----------------------------------------
		
		$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir( 'members' ) . '/sources/comments.php', 'profileCommentsLib', 'members' );
		$comment_lib = new $classToLoad( $this->registry );
		
		$result = $comment_lib->deleteComment( $member_id, $comment_id );
		
		/* Check for error */
		if( $result )
		{
			$this->registry->output->showError( $result, 10232 );
		}
		else
		{
			$member = IPSMember::load( $member_id );
			$this->registry->output->redirectScreen( $this->lang->words['comment_was_deleted'], $this->settings['base_url'] . 'showuser=' . $member_id, $member['members_seo_name'] );
		}
	}
	
 	/**
	 * Updates the comments
	 *
	 * @access	private
	 * @return	void			[Prints to screen]
	 * @since	IPB 2.2.0.2006-08-15
	 */
 	private function _saveComment()
 	{
 		//-----------------------------------------
 		// INIT
 		//-----------------------------------------
		
		$member_id		= intval( $this->request['member_id'] );
		$md5check		= IPSText::md5Clean( $this->request['md5check'] );
		$content		= '';
		$comment_ids	= array();
		$final_ids		= '';
		
		//-----------------------------------------
		// MD5 check
		//-----------------------------------------
		
		if (  $md5check != $this->member->form_hash )
    	{
    		die( '' );
    	}

		//-----------------------------------------
		// My tab?
		//-----------------------------------------
		
		if ( $member_id != $this->memberData['member_id'] AND !$this->memberData['g_is_supmod'] )
    	{
    		die( '' );
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
			die( '' );
    	}

		//-----------------------------------------
		// Grab comment_ids
		//-----------------------------------------
		
		if ( is_array( $this->request['pp-checked'] ) AND count( $this->request['pp-checked'] ) )
		{
			foreach( $this->request['pp-checked'] as $key => $value )
			{
				$key = intval( $key );
				
				if ( $value )
				{
					$comment_ids[ $key ] = $key;
				}
			}
		}
	
		//-----------------------------------------
		// Update the database...
		//-----------------------------------------
		
		if ( is_array( $comment_ids ) AND count( $comment_ids ) )
		{
			$final_ids = implode( ',', $comment_ids );
			
			//-----------------------------------------
			// Now update...
			//-----------------------------------------

			switch( $this->request['pp-moderation'] )
			{
				case 'approve':
					$this->DB->update( 'profile_comments', array( 'comment_approved' => 1 ), 'comment_id IN(' . $final_ids . ')' );
					break;
				case 'unapprove':
					$this->DB->update( 'profile_comments', array( 'comment_approved' => 0 ), 'comment_id IN(' . $final_ids . ')' );
					break;
				case 'delete':
					$this->DB->delete( 'profile_comments', 'comment_id IN(' . $final_ids . ')' );
					break;
			}
		}
		
		//-----------------------------------------
		// Bounce...
		//-----------------------------------------
		
		$this->registry->output->silentRedirect( $this->settings['base_url'] . 'app=members&section=comments&module=profile&member_id=' . $member_id . '&do=list&_saved=1&___msg=pp_comments_updated&md5check=' . $this->member->form_hash );
	}

	/**
	 * Save the new comment
	 *
	 * @access	private
	 * @return	void
	 */
	private function _doAddComment()
	{
		/* INIT */
		$member_id 	= intval( $this->request['member_id'] );
		$comment	= $this->request['comment_text'];
		$md5check	= IPSText::md5Clean( $this->request['auth_key'] );

		/* Check member has */
		if ( $md5check != $this->member->form_hash )
    	{
			$this->registry->output->showError( $this->lang->words['no_permission'], 10233 );
    	}

		/* Add Comment */
		$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir( 'members' ) . '/sources/comments.php', 'profileCommentsLib', 'members' );
		$comment_lib = new $classToLoad( $this->registry );
		
		$result = $comment_lib->addCommentToDB( $member_id, $comment );
		
		/* Check for error */
		if( $result AND $result != 'pp_comment_added_mod' )
		{
			$this->registry->output->showError( $result, 10232 );
		}
		else if ( $result == 'pp_comment_added_mod' )
		{
			$this->registry->output->redirectScreen( $this->lang->words[ $result ], $this->settings['base_url'] . 'showuser=' . $member_id );
		}
		else
		{
			$this->registry->output->silentRedirect( $this->settings['base_url'] . 'showuser=' . $member_id );
		}		
	}
}