<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * SQL error logs
 * Last Updated: $LastChangedDate: 2010-06-16 02:25:19 +0100 (Wed, 16 Jun 2010) $
 * </pre>
 *
 * @author 		$Author: matt $
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

class admin_core_logs_sqlerror extends ipsCommand 
{
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
		
		$this->form_code	= $this->html->form_code	= 'module=logs&amp;section=sqlerror';
		$this->form_code_js	= $this->html->form_code_js	= 'module=logs&section=sqlerror';
		
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
		$this->registry->output->core_nav[]		= array( $this->settings['base_url'] . 'module=logs&section=sqlerror', $this->lang->words['mlog_sqlerrors'] );
				
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
	 * View an SQL log
	 *
	 * @access	protected
	 * @return	void		[Outputs to screen]
	 */
	protected function _view()
	{
		/* INIT */
		$file = trim( $this->request['file'] );
		
		/* Check file name */
		if ( ! preg_match( "#^sql_error_log_(\d+)_(\d+)_(\d+).cgi$#", $file ) OR ! file_exists( IPS_CACHE_PATH . 'cache/' . $file ) )
		{
			$this->registry->output->global_message = $this->lang->words['sqllog_nofile'];
			$this->_listCurrent();
			return;
		}
		
		/* Fetch size */
		$size = @filesize( IPS_CACHE_PATH . 'cache/' . $file );
		
		/* Fetch content */
		require_once( IPS_KERNEL_PATH . 'classFileManagement.php' );
		$classFileManagement = new classFileManagement();
		
		/* Get some tail! */
		$content = $classFileManagement->tailFile( IPS_CACHE_PATH . 'cache/' . $file, 300 );
		
		/* Can't believe I typed that last comment */
		$this->registry->output->html .= $this->html->sqlLogsView( $file, $size, $content );

	}
	
	/**
	 * Remove logs by an admin
	 *
	 * @access	protected
	 * @return	void		[Outputs to screen]
	 */
	protected function _remove()
	{
		/* INIT */
		$file = trim( $this->request['file'] );
		
		/* Check file name */
		if ( ! preg_match( "#^sql_error_log_(\d+)_(\d+)_(\d+).cgi$#", $file ) OR ! file_exists( IPS_CACHE_PATH . 'cache/' . $file ) )
		{
			$this->registry->output->global_message = $this->lang->words['sqllog_nofile'];
			$this->_listCurrent();
			return;
		}
		
		@unlink( IPS_CACHE_PATH . 'cache/' . $file  );
		
		$this->registry->output->global_message = $this->lang->words['sqllog_removed'];
		$this->_listCurrent();
	}
	
	/**
	 * List the current SQL logs
	 *
	 * @access	protected
	 * @return	void		[Outputs to screen]
	 */
	protected function _listCurrent()
	{
		$rows = array();
		
		/* Got a latest? */
		if ( file_exists( IPS_CACHE_PATH . 'cache/sql_error_latest.cgi' ) )
		{
			$unix = @filemtime( IPS_CACHE_PATH . 'cache/sql_error_latest.cgi' );
			
			if ( $unix )
			{
				$mtime = gmdate( 'd-j-Y', $unix );
				$now   = gmdate( 'd-j-Y', time() );
				
				if ( $mtime == $now )
				{
					$contents = file_get_contents( IPS_CACHE_PATH . 'cache/sql_error_latest.cgi' );
					
					/* Display a message */
					$this->registry->output->global_message = sprintf( $this->lang->words['sqllog_latest'], $this->registry->class_localization->getDate( $unix, 'LONG' ), $contents );
				}
			}
		}
		
		try
		{
			foreach( new DirectoryIterator( DOC_IPS_ROOT_PATH . 'cache' ) as $file )
			{
				if ( $file->isDot() OR ! $file->isFile() )
				{
					continue;
				}
        	
				if ( preg_match( "#^sql_error_log_(\d+)_(\d+)_(\d+).cgi$#", $file->getFilename(), $matches ) )
				{
					$rows[] = array( 'name'   => $file->getFilename(),
									 'mtime'  => $file->getMTime(),
									 'size'   => $file->getSize() );
				}
			}
		} catch ( Exception $e ) {}
		
		//-----------------------------------------
		// And output
		//-----------------------------------------
		
		$this->registry->output->html .= $this->html->sqllogsWrapper( $rows );
	}
}
