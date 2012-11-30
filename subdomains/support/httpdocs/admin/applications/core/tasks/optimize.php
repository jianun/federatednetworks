<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Task: Optimizes database tables daily
 * Last Updated: $LastChangedDate: 2010-01-15 10:18:44 -0500 (Fri, 15 Jan 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Core
 * @link		http://www.invisionpower.com
 * @since		27th January 2004
 * @version		$Rev: 5713 $
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
	 * Tables to optimize.  Initially I was just going to run
	 * optimize tables query against all tables, but Charles
	 * prefers this static list of tables, and so it shall be.
	 *
	 * @access	protected
	 * @var		array
	 */
	protected $charlesTables	= array(
										'core_item_markers',
										'core_item_markers_storage',
										'inline_notifications',
										'cache_store',
										'content_cache_posts',
										);
	
	/**#@+
	 * Registry Object Shortcuts
	 *
	 * @access	protected
	 * @var		object
	 */
	protected $registry;
	protected $DB;
	protected $settings;
	protected $request;
	protected $lang;
	protected $member;
	protected $cache;
	/**#@-*/
	
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
		$this->registry	  =  $registry;
		$this->DB		  =  $this->registry->DB();
		$this->settings   =& $this->registry->fetchSettings();
		$this->request    =& $this->registry->fetchRequest();
		$this->lang		  =  $this->registry->getClass('class_localization');
		$this->member	  =  $this->registry->member();
		$this->memberData =& $this->registry->member()->fetchMemberData();
		$this->cache	  =  $this->registry->cache();
		$this->caches     =& $this->registry->cache()->fetchCaches();

		$this->class	= $class;
		$this->task		= $task;
		
		$this->registry->getClass('class_localization')->loadLanguageFile( array( 'public_global' ), 'core' );
	}
	
	/**
	 * Run this task
	 * 
	 * @access	public
	 * @return	void
	 */
	public function runTask()
	{
		/* Not needed for mssql as per #23105 bug */
		if ( ipsRegistry::dbFunctions()->getDriverType() != 'mysql' )
		{
			$this->class->unlockTask( $this->task );
			return;
		}
		
		//$_tables	= $this->DB->getTableNames();
		
		//if( is_array($_tables) AND count($_tables) )
		//{
		//	foreach( $_tables as $_table )
		//	{
		//		$this->DB->query( "OPTIMIZE TABLE {$_table}" );
		//		$this->DB->query( "ANALYZE TABLE {$_table}" );
		//	}
		//}
		
		$_tables	= $this->charlesTables;
		
		if( is_array($_tables) AND count($_tables) )
		{
			$PRE = ipsRegistry::dbFunctions()->getPrefix();
			ipsRegistry::dbFunctions()->prefix_changed = true;
			
			foreach( $_tables as $_table )
			{
				$this->DB->query( "OPTIMIZE TABLE {$PRE}{$_table}" );
				$this->DB->query( "ANALYZE TABLE {$PRE}{$_table}" );
			}
			
			ipsRegistry::dbFunctions()->prefix_changed = false;
		}

		$this->class->appendTaskLog( $this->task, sprintf( $this->lang->words['task__optimizedtables'], count($_tables) ) );
	
		$this->class->unlockTask( $this->task );
	}

}