<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Allow user to change their status
 * Last Updated: $Date: 2010-06-10 06:36:42 -0400 (Thu, 10 Jun 2010) $
 * </pre>
 *
 * @author 		$Author: danc $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Members
 * @link		http://www.invisionpower.com
 * @since		Tuesday 1st March 2005 (11:52)
 * @version		$Revision: 6502 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class public_members_profile_status extends ipsCommand 
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
		$this->request['do'] = ( $this->request['do'] ) ? $this->request['do'] : 'list';
		
		//-----------------------------------------
		// Security check
		//-----------------------------------------
		
		if ( $this->request['do'] != 'list' AND ( $this->request['k'] != $this->member->form_hash ) )
		{
			$this->registry->getClass('output')->showError( 'no_permission', 20314, null, null, 403 );
		}
 				
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
			case 'list':
				$this->_list();
			break;
			case 'new':
				$this->_new();
			break;
			case 'reply':
				$this->_reply();
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
	* Lock a status
	*
	* @access	private
	*/
	private function _lockStatus()
	{
		/* INIT */
		$status_id = intval( $this->request['status_id'] );
		
		/* Quick check? */
		if ( ! $status_id )
 		{
			$this->registry->output->showError( 'status_off', 10276, null, null, 404 );
		}

		/* Set Author */
		$this->registry->getClass('memberStatus')->setAuthor( $this->memberData );
		
		/* Set Data */
		$this->registry->getClass('memberStatus')->setStatusData( $status_id );
		
		/* Can we reply? */
		if ( ! $this->registry->getClass('memberStatus')->canLockStatus() )
 		{
			$this->registry->output->showError( 'status_off', 10277, null, null, 403 );
		}

		/* Update */
		$this->registry->getClass('memberStatus')->lockStatus();
		
		/* Got a return URL? */
		if ( $this->request['rurl'] )
		{
			$this->registry->output->redirectScreen( $this->lang->words['status_locked'], $this->settings['base_url'] . base64_decode( $this->request['rurl'] ) );
		}
		else
		{
			$this->registry->output->redirectScreen( $this->lang->words['status_locked'], $this->settings['base_url'] . 'showuser=' . $this->memberData['member_id'], $this->memberData['members_seo_name'] );
		}
	}
	
	/**
	* Lock a status
	*
	* @access	private
	*/
	private function _unlockStatus()
	{
		/* INIT */
		$status_id = intval( $this->request['status_id'] );
		
		/* Quick check? */
		if ( ! $status_id )
 		{
			$this->registry->output->showError( 'status_off', 10276, null, null, 404 );
		}

		/* Set Author */
		$this->registry->getClass('memberStatus')->setAuthor( $this->memberData );
		
		/* Set Data */
		$this->registry->getClass('memberStatus')->setStatusData( $status_id );
		
		/* Can we reply? */
		if ( ! $this->registry->getClass('memberStatus')->canUnlockStatus() )
 		{
			$this->registry->output->showError( 'status_off', 10277, null, null, 403 );
		}

		/* Update */
		$this->registry->getClass('memberStatus')->unlockStatus();
		
		/* Got a return URL? */
		if ( $this->request['rurl'] )
		{
			$this->registry->output->redirectScreen( $this->lang->words['status_unlocked'], $this->settings['base_url'] . base64_decode( $this->request['rurl'] ) );
		}
		else
		{
			$this->registry->output->redirectScreen( $this->lang->words['status_unlocked'], $this->settings['base_url'] . 'showuser=' . $this->memberData['member_id'], $this->memberData['members_seo_name'] );
		}
	}
	
	/**
	* Delete a status reply
	*
	* @access	private
	*/
	private function _list()
	{
		/* INIT */
		$filters = array( 'limit' => 15 );
		
		/* Add to the filters */
		if ( $this->request['member_id'] )
		{
			$filters['member_id'] = intval( $this->request['member_id'] );
		}
		else if ( $this->request['status_id'] )
		{
			$filters['status_id'] = intval( $this->request['status_id'] );
		}
		else if ( $this->request['type'] == 'friends' )
		{
			$filters['friends_only'] = 1;
		}
		
		/* Fetch last 20 */
		$this->registry->getClass('memberStatus')->setAuthor( $this->memberData );
		
		/* Fetch */
		$statuses = $this->registry->getClass('memberStatus')->fetch( $this->memberData, $filters );
		
		/* Fetch actions */
		$actions = $this->registry->getClass('memberStatus')->fetchActions( $this->memberData, array( 'limit' => 30, 'not_theirs' => 1, 'custom' => 0 ) );
		
		$content = $this->registry->getClass('output')->getTemplate('profile')->statusUpdatesPage( $statuses, $actions );
		
		$this->registry->output->addContent( $content );
		$this->registry->output->setTitle( $this->lang->words['status_updates_title'] . ' - ' . ipsRegistry::$settings['board_name'] );
		$this->registry->output->addNavigation( $this->lang->words['status_updates_title'], '' );
		$this->registry->output->sendOutput();
		
	}
	
	/**
	* Delete a status reply
	*
	* @access	private
	*/
	private function _deleteReply()
	{
		/* INIT */
		$status_id = intval( $this->request['status_id'] );
		$reply_id  = intval( $this->request['reply_id'] );
		
		/* Quick check? */
		if ( ! $status_id OR ! $reply_id )
 		{
			$this->registry->output->showError( 'status_off', 10276, null, null, 404 );
		}

		/* Set Author */
		$this->registry->getClass('memberStatus')->setAuthor( $this->memberData );
		
		/* Set Data */
		$this->registry->getClass('memberStatus')->setStatusData( $status_id );
		$this->registry->getClass('memberStatus')->setReplyData( $reply_id );
		
		/* Can we reply? */
		if ( ! $this->registry->getClass('memberStatus')->canDeleteReply() )
 		{
			$this->registry->output->showError( 'status_off', 10277, null, null, 403 );
		}

		/* Update */
		$this->registry->getClass('memberStatus')->deleteReply();
		
		/* Got a return URL? */
		if ( $this->request['rurl'] )
		{
			$this->registry->output->redirectScreen( $this->lang->words['status_reply_deleted'], $this->settings['base_url'] . base64_decode( $this->request['rurl'] ) );
		}
		else
		{
			$this->registry->output->redirectScreen( $this->lang->words['status_reply_deleted'], $this->settings['base_url'] . 'app=members&amp;module=profile&amp;section=status&amp;status_id=' . $status_id, 'false', 'members_status_all' );
		}
	}
	
	/**
	* Delete a status
	*
	* @access	private
	*/
	private function _deleteStatus()
	{
		/* INIT */
		$status_id = intval( $this->request['status_id'] );
		
		/* Quick check? */
		if ( ! $status_id )
 		{
			$this->registry->output->showError( 'status_off', 10278, null, null, 404 );
		}

		/* Set Author */
		$this->registry->getClass('memberStatus')->setAuthor( $this->memberData );
		
		/* Set Data */
		$this->registry->getClass('memberStatus')->setStatusData( $status_id );
		
		/* Can we reply? */
		if ( ! $this->registry->getClass('memberStatus')->canDeleteStatus() )
 		{
			$this->registry->output->showError( 'status_off', 10279, null, null, 403 );
		}

		/* Update */
		$this->registry->getClass('memberStatus')->deleteStatus();
		
		/* Got a return URL? */
		if ( $this->request['rurl'] )
		{
			$this->registry->output->redirectScreen( $this->lang->words['status_deleted'], $this->settings['base_url'] . base64_decode( $this->request['rurl'] ) );
		}
		else
		{
			$this->registry->output->redirectScreen( $this->lang->words['status_deleted'], $this->settings['base_url'] . 'showuser=' . $this->memberData['member_id'], $this->memberData['members_seo_name'] );
		}
	}
	
	/**
	* Add a reply statussesses
	*
	* @access	private
	*/
	private function _reply()
	{
		/* INIT */
		$status_id = intval( $this->request['status_id'] );
		$comment   = trim( $this->request['comment-' . $status_id ] );
		$id        = intval( $this->request['id'] );
		
		/* Quick check? */
		if ( ! $status_id OR ! $comment )
 		{
			$this->registry->output->showError( 'status_off', 10280, null, null, 404 );
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
			$this->registry->output->showError( 'status_off', 10281, null, null, 403 );
		}

		/* Update */
		$this->registry->getClass('memberStatus')->reply();
		
		/* Got a return URL? */
		if ( $this->request['rurl'] )
		{
			$this->registry->output->redirectScreen( $this->lang->words['status_reply_done'], $this->settings['base_url'] . base64_decode( $this->request['rurl'] ) );
		}
		else
		{
			$this->registry->output->redirectScreen( $this->lang->words['status_reply_done'], $this->settings['base_url'] . 'showuser=' . $id, $this->memberData['members_seo_name'] );
		}
	}
	
	/**
	* Add a new statussesses
	*
	* @access	private
	*/
	private function _new()
	{
		$id   = intval( $this->memberData['member_id'] );
		$su_Twitter  = intval( $this->request['su_Twitter'] );
		$su_Facebook = intval( $this->request['su_Facebook'] );
		
		/* Set Author */
		$this->registry->getClass('memberStatus')->setAuthor( $this->memberData );
		
		/* Set Content */
		$this->registry->getClass('memberStatus')->setContent( trim( $this->request['content'] ) );
		
		/* Can we reply? */
		if ( ! $this->registry->getClass('memberStatus')->canCreate() )
 		{
			$this->registry->output->showError( 'status_off', 10268, null, null, 403 );
		}
		
		/* Set post outs */
		$this->registry->getClass('memberStatus')->setExternalUpdates( array( 'twitter' => $su_Twitter, 'facebook' => $su_Facebook ) );

		/* Update */
		$this->registry->getClass('memberStatus')->create();
		
		/* Got a return URL? */
		if ( $this->request['rurl'] )
		{
			$this->registry->output->redirectScreen( $this->lang->words['status_was_changed'], $this->settings['base_url'] . base64_decode( $this->request['rurl'] ) );
		}
		else
		{
			$this->registry->output->redirectScreen( $this->lang->words['status_was_changed'], $this->settings['base_url'] . 'showuser=' . $id, $this->memberData['members_seo_name'] );
		}
	}
}