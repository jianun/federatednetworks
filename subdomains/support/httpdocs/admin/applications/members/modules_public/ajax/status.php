<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Allow user to change their status
 * Last Updated: $Date: 2010-07-14 23:29:48 -0400 (Wed, 14 Jul 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Members
 * @link		http://www.invisionpower.com
 * @since		Tuesday 1st March 2005 (11:52)
 * @version		$Revision: 6655 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class public_members_ajax_status extends ipsAjaxCommand 
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
		
		$info = array();
 		$id   = intval( $this->memberData['member_id'] );
 				
		//-----------------------------------------
		// Get HTML and skin
		//-----------------------------------------

		$this->registry->class_localization->loadLanguageFile( array( 'public_profile' ), 'members' );
		
		/* Load status class */
		if ( ! $this->registry->isClassLoaded( 'memberStatus' ) )
		{
			$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/member/status.php', 'memberStatus' );
			$this->registry->setClass( 'memberStatus', new $classToLoad( ipsRegistry::instance() ) );
		}
		
		/* WHAT R WE DOING? */
		switch( $this->request['do'] )
		{
			default: 
			case 'new':
				$this->_new();
			break;
			case 'reply':
				$this->_reply();
			break;
			case 'showall':
				$this->_showAll();
			break;
			case 'deleteStatus':
				$this->_deleteStatus();
			break;
			case 'deleteReply':
				$this->_deleteReply();
			break;
			case 'lockStatus':
				$this->_lockStatus();
			break;
			case 'unlockStatus':
				$this->_unlockStatus();
			break;
		}
	}
	
	/**
	 * Add a reply statussesses
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function _showAll()
	{
		/* INIT */
		$status_id = intval( $this->request['status_id'] );
		$st        = intval( $this->request['st'] );
		
		/* Quick check? */
		if ( ! $status_id )
 		{
			$this->returnJsonError( $this->lang->words['status_off'] );
		}
		
		/* Set Author */
		$this->registry->getClass('memberStatus')->setAuthor( $this->memberData );
		
		/* Set Data */
		$this->registry->getClass('memberStatus')->setStatusData( $status_id );
		
		/* And the number of replies */
		$statusData = $this->registry->getClass('memberStatus')->getStatusData();
		
		/* Fetch */
		$this->returnJsonArray( array( 'status' => 'success', 'status_replies' => $statusData['status_replies'] + 1, 'html' => $this->cleanOutput( $this->registry->getClass('output')->getTemplate('profile')->statusReplies( $this->registry->getClass('memberStatus')->fetchAllReplies() ) ) ) );
	}
	
	/**
	 * Lock a status
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function _lockStatus()
	{
		/* INIT */
		$status_id = intval( $this->request['status_id'] );
		
		/* Quick check? */
		if ( ! $status_id )
 		{
			$this->returnJsonError( $this->lang->words['status_off'] );
		}

		/* Set Author */
		$this->registry->getClass('memberStatus')->setAuthor( $this->memberData );
		
		/* Set Data */
		$this->registry->getClass('memberStatus')->setStatusData( $status_id );
		
		/* Can we reply? */
		if ( ! $this->registry->getClass('memberStatus')->canLockStatus() )
 		{
			$this->returnJsonError( $this->lang->words['status_off'] );
		}

		/* Update */
		$this->registry->getClass('memberStatus')->lockStatus();
		
		/* Success? */
		$this->returnJsonArray( array( 'status' => 'success' ) );
	}
	
	/**
	 * Lock a status
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function _unlockStatus()
	{
		/* INIT */
		$status_id = intval( $this->request['status_id'] );
		
		/* Quick check? */
		if ( ! $status_id )
 		{
			$this->returnJsonError( $this->lang->words['status_off'] );
		}

		/* Set Author */
		$this->registry->getClass('memberStatus')->setAuthor( $this->memberData );
		
		/* Set Data */
		$this->registry->getClass('memberStatus')->setStatusData( $status_id );
		
		/* Can we reply? */
		if ( ! $this->registry->getClass('memberStatus')->canUnlockStatus() )
 		{
			$this->returnJsonError( $this->lang->words['status_off'] );
		}

		/* Update */
		$this->registry->getClass('memberStatus')->unlockStatus();
		
		/* Success? */
		$this->returnJsonArray( array( 'status' => 'success' ) );
	}
	
	/**
	 * Delete a status
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function _deleteStatus()
	{
		/* INIT */
		$status_id = intval( $this->request['status_id'] );
		
		/* Quick check? */
		if ( ! $status_id )
 		{
			$this->returnJsonError( $this->lang->words['status_off'] );
		}

		/* Set Author */
		$this->registry->getClass('memberStatus')->setAuthor( $this->memberData );
		
		/* Set Data */
		$this->registry->getClass('memberStatus')->setStatusData( $status_id );
		
		/* Can we reply? */
		if ( ! $this->registry->getClass('memberStatus')->canDeleteStatus() )
 		{
			$this->returnJsonError( $this->lang->words['status_off'] );
		}

		/* Update */
		$this->registry->getClass('memberStatus')->deleteStatus();
		
		/* Success? */
		$this->returnJsonArray( array( 'status' => 'success' ) );
	}
	
	/**
	 * Delete a status reply
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function _deleteReply()
	{
		/* INIT */
		$status_id = intval( $this->request['status_id'] );
		$reply_id  = intval( $this->request['reply_id'] );
		
		/* Quick check? */
		if ( ! $status_id OR ! $reply_id )
 		{
			$this->returnJsonError( $this->lang->words['status_off'] );
		}

		/* Set Author */
		$this->registry->getClass('memberStatus')->setAuthor( $this->memberData );
		
		/* Set Data */
		$this->registry->getClass('memberStatus')->setStatusData( $status_id );
		$this->registry->getClass('memberStatus')->setReplyData( $reply_id );
		
		/* Can we reply? */
		if ( ! $this->registry->getClass('memberStatus')->canDeleteReply() )
 		{
			$this->returnJsonError( $this->lang->words['status_off'] );
		}

		/* Update */
		$this->registry->getClass('memberStatus')->deleteReply();
		
		/* Success? */
		$this->returnJsonArray( array( 'status' => 'success' ) );
	}
	
	/**
	 * Add a reply statussesses
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function _reply()
	{
		/* INIT */
		$status_id = intval( $this->request['status_id'] );
		$comment   = $this->convertAndMakeSafe( $_POST['content'] );
		$id        = intval( $this->request['id'] );
		
		/* Quick check? */
		if ( ! $status_id OR ! $comment )
 		{
			$this->returnJsonError( $this->lang->words['status_no_reply'] );
		}

		/* Set Author */
		$this->registry->getClass('memberStatus')->setAuthor( $this->memberData );
		
		/* Set Content */
		$this->registry->getClass('memberStatus')->setContent( $comment );
		
		/* Set Data */
		$this->registry->getClass('memberStatus')->setStatusData( $status_id );
		
		/* Can we reply? */
		if ( ! $this->registry->getClass('memberStatus')->canReply() )
 		{
			$this->returnJsonError( $this->lang->words['status_no_reply'] );
		}

		/* Update */
		$this->registry->getClass('memberStatus')->reply();
		
		/* Now grab the reply and return it */
		$reply = $this->registry->getClass('output')->getTemplate('profile')->statusReplies( $this->registry->getClass('memberStatus')->fetchAllReplies( $status_id, array( 'sort_dir' => 'desc', 'limit' => 1 ) ) );
		
		/* And the number of replies */
		$statusData = $this->registry->getClass('memberStatus')->getStatusData();
		
		$this->returnJsonArray( array( 'status' => 'success', 'html' => $this->cleanOutput( $reply ), 'status_replies' => $statusData['status_replies'] + 1 ) );
	}
	
	/**
	 * Add a new statussesses
	 *
	 * @access	protected
	 * @return	void
	 */
	protected function _new()
	{
		/* INIT */
		$smallSpace  = intval( $this->request['smallSpace'] );
		$su_Twitter  = intval( $this->request['su_Twitter'] );
		$su_Facebook = intval( $this->request['su_Facebook'] );
		
		/* Got content? */
		if( !trim( $this->convertAndMakeSafe( $_POST['content'] ) ) )
		{
			$this->returnJsonError( $this->lang->words['no_status_sent'] );
		}
		
		/* Set Author */
		$this->registry->getClass('memberStatus')->setAuthor( $this->memberData );
		
		/* Set Content */
		$this->registry->getClass('memberStatus')->setContent( trim( $this->convertAndMakeSafe( $_POST['content'] ) ) );
		
		/* Set post outs */
		$this->registry->getClass('memberStatus')->setExternalUpdates( array( 'twitter' => $su_Twitter, 'facebook' => $su_Facebook ) );
		
		/* Can we reply? */
		if ( ! $this->registry->getClass('memberStatus')->canCreate() )
 		{
			$this->returnJsonError( $this->lang->words['status_off'] );
		}

		/* Update */
		$newStatus = $this->registry->getClass('memberStatus')->create();
		
		/* Now grab the reply and return it */
		$new = $this->registry->getClass('output')->getTemplate('profile')->statusUpdates( $this->registry->getClass('memberStatus')->fetch( $this->memberData['member_id'], array( 'member_id' => $this->memberData['member_id'], 'sort_dir' => 'desc', 'limit' => 1 ) ), $smallSpace );
		
		$this->returnJsonArray( array( 'status' => 'success', 'html' => $this->cleanOutput( $new ) ) );

		exit;
	}
}