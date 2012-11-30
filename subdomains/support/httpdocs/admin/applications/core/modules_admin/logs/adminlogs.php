<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Admin logs
 * Last Updated: $LastChangedDate: 2010-06-15 21:25:19 -0400 (Tue, 15 Jun 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Core
 * @link		http://www.invisionpower.com
 * @since		27th January 2004
 * @version		$Rev: 6539 $
 */

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}

class admin_core_logs_adminlogs extends ipsCommand 
{
	/**
	 * Generate some pretty colors for the logs
	 *
	 * @access	protected
	 * @var		array			Different colors for different actions
	 */
	protected $colours			= array();
	
	/**
	 * Skin object
	 *
	 * @access	protected
	 * @var		object			Skin templates
	 */
	protected $html;
	
	/**
	 * Main class entry point
	 *
	 * @access	public
	 * @param	object		ipsRegistry reference
	 * @return	void		[Outputs to screen]
	 */
	public function doExecute( ipsRegistry $registry )
	{
		//-----------------------------------------
		// Load skin
		//-----------------------------------------
		
		$this->html			= $this->registry->output->loadTemplate('cp_skin_adminlogs');
		
		//-----------------------------------------
		// Set up stuff
		//-----------------------------------------
		
		$this->form_code	= $this->html->form_code	= 'module=logs&amp;section=adminlogs';
		$this->form_code_js	= $this->html->form_code_js	= 'module=logs&section=adminlogs';
		
		//-----------------------------------------
		// Load lang
		//-----------------------------------------
				
		$this->registry->getClass('class_localization')->loadLanguageFile( array( 'admin_logs' ) );
		
		//-----------------------------------------
		// Fix up navigation bar
		//-----------------------------------------
		
		$this->registry->output->core_nav		= array();
		$this->registry->output->ignoreCoreNav	= true;
		$this->registry->output->core_nav[]		= array( $this->settings['base_url'] . 'module=tools', $this->lang->words['nav_toolsmodule'] );
		$this->registry->output->core_nav[]		= array( $this->settings['base_url'] . 'module=tools&section=logsSplash', $this->lang->words['nav_logssplash'] );
		$this->registry->output->core_nav[]		= array( $this->settings['base_url'] . 'module=logs&section=adminlogs', $this->lang->words['alog_adminlogs'] );
		
		//-----------------------------------------
		// Add some colors from the paintbucket
		//-----------------------------------------
		
		$this->colours  = array(
								"cat"		=> "green",
								"forum"		=> "darkgreen",
								"mem"		=> "red",
								'group'		=> "purple",
								'mod'		=> 'orange',
								'op'		=> 'darkred',
								'help'		=> 'darkorange',
								'modlog'	=> 'steelblue',
				   			   );
		
		///----------------------------------------
		// What to do...
		//-----------------------------------------
		
		switch( $this->request['do'] )
		{
			case 'view':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'adminlogs_view' );
				$this->_view();
			break;
				
			case 'remove':
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'adminlogs_delete' );
				$this->_remove();
			break;

			default:
				$this->registry->getClass('class_permissions')->checkPermissionAutoMsg( 'adminlogs_view' );
				$this->_listCurrent();
			break;
		}
		
		/* Output */
		$this->registry->output->html_main .= $this->registry->output->global_template->global_frame_wrapper();
		$this->registry->output->sendOutput();	
	}
	
	/**
	 * View all logs for a given admin
	 *
	 * @access	protected
	 * @return	void		[Outputs to screen]
	 */
	protected function _view()
	{
		///----------------------------------------
		// Basic init
		//-----------------------------------------
		
		$start = intval($this->request['st']) >= 0 ? intval($this->request['st']) : 0;

		///----------------------------------------
		// No mid or search string?
		//-----------------------------------------
				
		if ( !$this->request['search_string'] AND !$this->request['mid'] )
		{
			$this->registry->output->global_message = $this->lang->words['alog_nostring'];
			$this->_listCurrent();
			return;
		}
		
		///----------------------------------------
		// mid?
		//-----------------------------------------
		
		if ( !$this->request['search_string'] )
		{
			$row = $this->DB->buildAndFetch( array( 'select' => 'COUNT(id) as count', 'from' => 'admin_logs', 'where' => "member_id=" . intval($this->request['mid']) ) );

			$row_count = $row['count'];
			
			$query = "&amp;{$this->form_code}&amp;mid=" . $this->request['mid'] . "&amp;do=view";
			
			$this->DB->build( array( 'select'		=> 'm.*',
											'from'		=> array( 'admin_logs' => 'm' ),
											'where'		=> 'm.member_id=' . intval($this->request['mid']),
											'order'		=> 'm.ctime DESC',
											'limit'		=> array( $start, 20 ),
											'add_join'	=> array(
																array( 'select'	=> 'mem.member_id, mem.members_display_name',
																		'from'	=> array( 'members' => 'mem' ),
																		'where'	=> 'mem.member_id=m.member_id',
																		'type'	=> 'left'
																	)
																)
								)		);
			$this->DB->execute();
		}
		
		///----------------------------------------
		// search string?
		//-----------------------------------------
		
		else
		{
			$this->request[ 'search_string'] = IPSText::parseCleanValue( urldecode($this->request['search_string'] ) );
			
			if( !$this->DB->checkForField( $this->request['search_type'], 'admin_logs' ) )
			{
				$this->registry->output->showError( $this->lang->words['alog_whatfield'], 4110, true );
			}
			
			if( $this->request['search_type'] == 'member_id' )
			{
				$dbq = "m." . $this->request['search_type'] . "='" . $this->request['search_string'] . "'";
			}
			else
			{
				$dbq = "m." . $this->request['search_type'] . " LIKE '%" . $this->request['search_string'] . "%'";
			}
			
			$row = $this->DB->buildAndFetch( array( 'select' => 'COUNT(m.member_id) as count', 'from' => 'admin_logs m', 'where' => $dbq ) );
			
			$row_count = $row['count'];
			
			$query = "&amp;{$this->form_code}&amp;do=view&amp;search_type=" . $this->request['search_type'] . "&amp;search_string=" . urlencode($this->request['search_string']);
			
			$this->DB->build( array( 'select'		=> 'm.*',
											'from'		=> array( 'admin_logs' => 'm' ),
											'where'		=> $dbq,
											'order'		=> 'm.ctime DESC',
											'limit'		=> array( $start, 20 ),
											'add_join'	=> array(
																array( 'select'	=> 'mem.member_id, mem.members_display_name',
																		'from'	=> array( 'members' => 'mem' ),
																		'where'	=> 'mem.member_id=m.member_id',
																		'type'	=> 'left'
																	)
																)
								)		);
			$this->DB->execute();
		}
		
		///----------------------------------------
		// Page links
		//-----------------------------------------
		
		$links = $this->registry->output->generatePagination( array( 'totalItems'			=> $row_count,
																	 'itemsPerPage'			=> 20,
																	 'currentStartValue'	=> $start,
																	 'baseUrl'				=> $this->settings['base_url'] . $query,
														)
												 );

		///----------------------------------------
		// Get db results
		//-----------------------------------------
		
		while ( $row = $this->DB->fetch() )
		{
			$row['_time']	= $this->registry->class_localization->getDate( $row['ctime'], 'LONG' );
			$row['color']	= $this->colours[ $row['act'] ];

			$rows[]			= $row;
		}

		///----------------------------------------
		// And output
		//-----------------------------------------
		
		$this->registry->output->html .= $this->html->adminlogsView( $rows, $links );

	}
	
	/**
	 * Remove logs by an admin
	 *
	 * @access	protected
	 * @return	void		[Outputs to screen]
	 */
	protected function _remove()
	{
		if ( $this->request['mid'] == "" )
		{
			$this->registry->output->showError( $this->lang->words['alog_whoselog'], 11114 );
		}
		
		$this->DB->delete( 'admin_logs', "member_id=" . intval($this->request['mid']) );
		
		$this->registry->output->silentRedirect( $this->settings['base_url']."&{$this->form_code}" );
	}
	
	/**
	 * List the current logs with links to view per-admin
	 *
	 * @access	protected
	 * @return	void		[Outputs to screen]
	 */
	protected function _listCurrent()
	{
		$rows			= array();
		$admins			= array();

		//-----------------------------------------
		// LAST FIVE ACTIONS
		//-----------------------------------------
		
		$this->DB->build( array( 'select'		=> 'm.*',
										'from'		=> array( 'admin_logs' => 'm' ),
										'order'		=> 'm.ctime DESC',
										'limit'		=> array( 5 ),
										'add_join'	=> array(
															array( 'select'	=> 'mem.member_id, mem.members_display_name',
																	'from'	=> array( 'members' => 'mem' ),
																	'where'	=> 'mem.member_id=m.member_id',
																	'type'	=> 'left'
																)
															)
							)		);
		$this->DB->execute();

		while ( $row = $this->DB->fetch() )
		{
			$row['_time']	= $this->registry->class_localization->getDate( $row['ctime'], 'LONG' );
			$row['color']	= $this->colours[ $row['act'] ];

			$rows[]			= $row;
		}

		//-----------------------------------------
		// All admins
		//-----------------------------------------
		
		$this->DB->build( array( 
			'select'	=> 'member_id, count(member_id) as act_count',
			'from'		=> 'admin_logs',
			'group'		=> 'member_id',
			'order'		=> 'act_count DESC'
		) );
		
		$this->DB->execute();
        
        $_members = array();
        $_store = array();
        while ( $r = $this->DB->fetch() )
        {
			$_members[] = $r['member_id'];
			$admins[ $r['member_id'] ] = $r;
        }
        
		if ( !empty( $_members ) )
		{
			$this->DB->build( array( 'select'	=> 'member_id, members_display_name',
									'from'		=> 'members',
									'where'     => 'member_id IN (' . implode( ',', $_members ) . ')',
								)		);
			$this->DB->execute();
			
			while ( $r = $this->DB->fetch() )
			{
				$admins[ $r['member_id'] ] = array_merge( $r, $admins[ $r['member_id'] ] );
			}
		}

		//-----------------------------------------
		// And output
		//-----------------------------------------
		
		$this->registry->output->html .= $this->html->adminlogsWrapper( $rows, $admins );
	}
}