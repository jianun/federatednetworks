<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Task: Prunes subscribed topics
 * Last Updated: $LastChangedDate: 2010-07-07 08:09:48 -0400 (Wed, 07 Jul 2010) $
 * </pre>
 *
 * @author 		$Author: matt $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Forums
 * @link		http://www.invisionpower.com
 * @since		27th January 2004
 * @version		$Rev: 6609 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class task_item
{
	/**
	* Parent task manager class
	*
	* @access	protected
	* @var		object
	*/
	protected $class;

	/**
	* This task data
	*
	* @access	protected
	* @var		array
	*/
	protected $task			= array();

	/**
	* Prevent logging
	*
	* @access	protected
	* @var		boolean
	*/
	protected $restrict_log	= false;
	
	/**
	* Registry Object Shortcuts
	*/
	protected $registry;
	protected $DB;
	protected $settings;
	protected $request;
	protected $lang;
	protected $member;
	protected $cache;
	
	/**
	* Constructor
	*
	* @access	public
	* @param 	object		ipsRegistry reference
	* @param 	object		Parent task class
	* @param	array 		This task data
	* @return	void
	*/
	public function __construct( ipsRegistry $registry, $class, $task )
	{
		/* Make registry objects */
		$this->registry	= $registry;
		$this->DB		= $this->registry->DB();
		$this->settings =& $this->registry->fetchSettings();
		$this->request  =& $this->registry->fetchRequest();
		$this->lang		= $this->registry->getClass('class_localization');
		$this->member	= $this->registry->member();
		$this->memberData =& $this->registry->member()->fetchMemberData();
		$this->cache	= $this->registry->cache();
		$this->caches   =& $this->registry->cache()->fetchCaches();

		$this->class	= $class;
		$this->task		= $task;
	}
	
	/**
	* Run this task
	*
	* @access	public
	* @return	void
	*/
	public function runTask()
	{
		$this->registry->getClass('class_localization')->loadLanguageFile( array( 'public_global' ), 'core' );
		
		//-----------------------------------------
		// Delete old subscriptions
		//-----------------------------------------
		
		$deleted	= 0;
		$trids		= array();
		
		if ( $this->settings['subs_autoprune'] > 0 )
 		{
			$time = time() - ($this->settings['subs_autoprune'] * 86400);
			
			$this->DB->build( array( 'select'		=> 'tr.trid',
											'from'		=> array( 'tracker' => 'tr' ),
											'where'		=> 't.last_post < ' . $time,
											'add_join'	=> array( 
																array( 'from'	=> array( 'topics' => 't' ),
																		'where'	=> 't.tid=tr.topic_id',
																		'type'	=> 'left'
																	)
																)
								)		);
			$this->DB->execute();
			
			while ( $r = $this->DB->fetch() )
			{
				$trids[] = $r['trid'];
			}
			
			if (count($trids) > 0)
			{
				$this->DB->delete( 'tracker', "trid IN (" . implode( ",", $trids ) . ")" );
			}
			
			$deleted = intval( count($trids) );
 		}
 		
		//-----------------------------------------
		// Delete old unattached uploads
		//-----------------------------------------
		
		$time_cutoff	= time() - 7200;
		$deadid			= array();
		
		$this->DB->build( array( "select" => '*', 'from' => 'attachments',  'where' => "attach_rel_id=0 AND attach_date < $time_cutoff") );
		$this->DB->execute();
		
		while( $killmeh = $this->DB->fetch() )
		{
			if ( $killmeh['attach_location'] )
			{
				@unlink( $this->settings['upload_dir'] . "/" . $killmeh['attach_location'] );
			}
			if ( $killmeh['attach_thumb_location'] )
			{
				@unlink( $this->settings['upload_dir'] . "/" . $killmeh['attach_thumb_location'] );
			}
			
			$deadid[] = $killmeh['attach_id'];
		}
		
		$_attach_count = count( $deadid );
		
		if ( $_attach_count )
		{
			$this->DB->delete( 'attachments', "attach_id IN(" . implode( ",", $deadid ) . ")" );
		}
		
		//-----------------------------------------
		// Delete old topic redirects
		//-----------------------------------------
		
		if ( intval( $this->settings['topic_redirect_prune'] ) > 0 )
		{
			$time = time() - ( $this->settings['topic_redirect_prune'] * 86400 );
			$tids = array();
			$fids = array();
			
			/* Grab topics ensuring we use the index */
			$this->DB->build( array( 'select' => 'tid, moved_to, forum_id',
									 'from'   => 'topics',
									 'where'  => 'forum_id > 0 AND pinned=0 AND last_post < ' . $time . " AND state='link'",
									 'limit'  => array( 0, 150 ) ) );
									 
			$this->DB->execute();
			
			while ( $row = $this->DB->fetch() )
			{
				/* ensure it's a moved topic */
				if ( $row['moved_to'] )
				{
					$tids[] = $row['tid'];
					$fids[ $row['forum_id'] ]++;
				}
			}
			
			if ( count( $tids ) > 0 )
			{
				$this->DB->delete( 'topics', "tid IN (" . implode( ",", $tids ) . ")" );
				
				if ( count( $fids ) )
				{
					foreach( $fids as $f => $count )
					{
						/* remove the count */
						$this->DB->force_data_type = array( 'topics' => 'int' );
						$this->DB->update( 'forums', array( 'topics' => 'minus:' . $count ), 'id=' . intval( $f ) );
					}
				}
			}
											
			$redirectsDeleted = intval( count( $tids ) );
		}
		
		//-----------------------------------------
		// Remove old XML-RPC logs...
		//-----------------------------------------
		
		if ( $this->settings['xmlrpc_log_expire'] > 0 )
		{
			$time = time() - ( $this->settings['xmlrpc_log_expire'] * 86400 );
 			
 			$this->DB->delete( 'api_log', "api_log_date < {$time}" );
 			
 			$xmlrpc_logs_deleted = $this->DB->getAffectedRows();
		}
		
		//-----------------------------------------
		// Log to log table - modify but dont delete
		//-----------------------------------------
		
		$this->class->appendTaskLog( $this->task, sprintf( $this->lang->words['task_dailycleanout'], $xmlrpc_logs_deleted, $_attach_count, $deleted, $redirectsDeleted ) );
		
		//-----------------------------------------
		// Unlock Task: DO NOT MODIFY!
		//-----------------------------------------
		
		$this->class->unlockTask( $this->task );
	}

}