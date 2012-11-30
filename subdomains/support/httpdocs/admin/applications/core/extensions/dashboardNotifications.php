<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Dashboard Notifications
 * Last Updated: $Date
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Core
 * @link		http://www.invisionpower.com
 * @version		$Rev: 6593 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

/**
* Main loader class
*/
class dashboardNotifications__core
{
	public function __construct()
	{
		$this->settings	= ipsRegistry::fetchSettings();
		$this->lang		= ipsRegistry::getClass( 'class_localization' );
		$this->caches	=& ipsRegistry::cache()->fetchCaches();
		$this->cache	= ipsRegistry::cache();
	}
	
	public function get()
	{
		/* INIT */
		$entries = array();
		
		if( ! $this->settings['ipb_reg_number'] )
		{
			$entries[] = array( $this->lang->words['lc_title_nokey'], sprintf( $this->lang->words['lc_msg_nokey'], "{$this->settings['base_url']}module=tools&amp;section=licensekey" ) );
		}
		else
		{
			/* Get the file managemnet class */
			require_once( IPS_KERNEL_PATH . 'classFileManagement.php' );
			$query = new classFileManagement();
			$query->use_sockets = 1;
            
			/* Query the api */
			$response = $query->getFileContents( "http://licsrv.invisionpower.com/license_check/index.php?api=checkService&service=ipb&key={$this->settings['ipb_reg_number']}&url={$this->settings['board_url']}" );
            
			/* Get License Data */
			$response = json_decode( $response, true );
			
			if( $response['result'] != 'ok' )
			{
				if( $this->lang->words[ 'lc_title_' . $response['error'] ] && $this->lang->words[ 'lc_msg_' . $response['error'] ] )
				{
					$entries[] = array( $this->lang->words[ 'lc_title_' . $response['error'] ], sprintf( $this->lang->words[ 'lc_msg_' . $response['error'] ], $this->settings['board_url'] ) );
				}
				else
				{
					$entries[] = array( $this->lang->words['ipboardlicenseerror'], $response['error'] ? $response['error'] : sprintf( $this->lang->words['licensenocomm'], $this->settings['base_url'] . "app=core&module=diagnostics&section=diagnostics&do=connections" ) );
				}
			}
		}

		/* FURL cache OOD? */
		if ( file_exists( IPS_CACHE_PATH . 'cache/furlCache.php' ) )
		{
			$mtime = intval( @filemtime( IPS_CACHE_PATH . 'cache/furlCache.php' ) );

			/* Check mtimes on extensions.. */
			foreach( ipsRegistry::$applications as $app_dir => $application )
			{
				if ( file_exists( IPSLib::getAppDir( $app_dir ) . '/extensions/furlTemplates.php' ) )
				{
					$_mtime = intval( @filemtime( IPSLib::getAppDir( $app_dir ) . '/extensions/furlTemplates.php' ) );

					if ( $_mtime > $mtime )
					{
						$entries[] = array( $this->lang->words['furlcache_outofdate'], "<a href='" . $this->settings['base_url'] . "app=core&amp;module=applications&amp;section=applications&amp;do=seoRebuild'>{$this->lang->words['rebuild_furl_cache']}</a>" );
						break;
					}
				}
			}
		}

		/* Minify on but /cache/tmp not writeable? */
		if ( isset( $this->settings['_use_minify'] ) AND $this->settings['_use_minify'] )
		{
			$entries[] = array( $this->lang->words['minifywrite_head'], $this->lang->words['minifynot_writeable'] );
		}
		
		/* Installer Check */
		if( @file_exists( IPS_ROOT_PATH . 'install/index.php' ) )
		{
			if ( ! @file_exists( DOC_IPS_ROOT_PATH . 'cache/installer_lock.php' ) )
			{
				$this->lang->words['cp_unlocked_warning'] = sprintf( $this->lang->words['cp_unlocked_warning'], CP_DIRECTORY );
				$entries[] = array( $this->lang->words['cp_unlockedinstaller'], $this->lang->words['cp_unlocked_warning'] );
			}
		}
		else if( @is_dir( IPS_ROOT_PATH . 'applications_addon/ips/convert/' ) and !@file_exists( DOC_IPS_ROOT_PATH . 'cache/converter_lock.php' ) and $this->caches['app_cache']['convert'] )
		{
			$this->lang->words['cp_warning_converter']	= sprintf( $this->lang->words['cp_warning_converter'], $this->settings['_base_url'] );
			$entries[] = array( $this->lang->words['cp_unlocked_converter'], $this->lang->words['cp_warning_converter'] );
		}

		/* Unfinished Upgrade */
		require_once( IPS_ROOT_PATH . '/setup/sources/base/setup.php' );
		$versions	= IPSSetUp::fetchAppVersionNumbers( 'core' );

		if( $versions['current'][0] != $versions['latest'][0] )
		{
			$this->lang->words['cp_upgrade_warning'] = sprintf( $this->lang->words['cp_upgrade_warning'], $versions['current'][1], $versions['latest'][1], $this->settings['base_acp_url'] );

			$entries[] = array( $this->lang->words['cp_unfinishedupgrade'], $this->lang->words['cp_upgrade_warning'] );
		}
		
		/* PHP Version Check */
		if( PHP_VERSION < '5.1.0' )
		{
			$entries[] = array( sprintf( $this->lang->words['cp_yourphpversion'],  PHP_VERSION ), $this->lang->words['cp_php_warning'] );
		}

		/* Board Offline Check */
		if ( $this->settings['board_offline'] )
		{
			$entries[] = array( $this->lang->words['cp_boardoffline'], "{$this->lang->words['cp_boardoffline1']}<br /><br />{$this->lang->words['_raquo']} <a href='" . $this->settings['base_url'] . "&amp;module=tools&amp;section=settings&amp;do=findsetting&amp;key=boardoffline'>{$this->lang->words['cp_boardoffline2']}</a>" );
		}
		
		/* Fulltext Check */
		if( $this->settings['search_method'] == 'traditional' AND !$this->settings['use_fulltext'] AND !$this->settings['hide_ftext_note'] )
		{
			$entries[] = array( $this->lang->words['fulltext_off'], "{$this->lang->words['fulltext_turnon']}<br /><br />{$this->lang->words['_raquo']} <a href='" . $this->settings['base_url'] . "&amp;module=tools&amp;section=settings&amp;do=findsetting&amp;key=searchsetup'>{$this->lang->words['fulltext_find']}</a>" );
		}
		
		/* Make sure the profile directory is writable */
		if( ! is_dir( $this->settings['upload_dir'] . '/profile/' ) || ! is_writable( $this->settings['upload_dir'] . '/profile/' ) )
		{
			$entries[] = array( $this->lang->words['cp_profilephotoerr_title'], sprintf( $this->lang->words['cp_profilephotoerr_msg'], $this->settings['upload_dir'] . '/profile/' ) );
		}
		
		/* Check for upgrade finish folder */
		if( is_dir( IPS_ROOT_PATH . 'upgradeFinish/' ) )
		{
			$entries[] = array( $this->lang->words['cp_upgradefinishfolder'], sprintf( $this->lang->words['cp_upgradefinishfolder_msg'], IPS_ROOT_PATH . 'upgradeFinish/' ) );
		}
		
		/* Check to see if GD is intalled */
		if(! extension_loaded( 'gd' ) || ! function_exists( 'gd_info' ) )
		{
			$entries[] = array( $this->lang->words['cp_gdnotinstalled_title'], $this->lang->words['cp_gdnotinstalled_msg'] );
		}
		
		/* Performance mode check */
		$perfMode = $this->cache->getCache('performanceCache');
		
		if( is_array( $perfMode ) && count( $perfMode ) )
		{
			$entries[] = array( $this->lang->words['cp_perfmodeon_title'], $this->lang->words['cp_perfmodeon_msg'] );
		}
		
		/* Suhosin check */
		if( extension_loaded( 'suhosin' ) )
		{
			$_postMaxVars	= @ini_get('suhosin.post.max_vars');
			$_reqMaxVars	= @ini_get('suhosin.request.max_vars');
			$_postMaxLen	= @ini_get('suhosin.post.max_value_length');
			$_reqMaxLen		= @ini_get('suhosin.request.max_value_length');
			
			if( $_postMaxVars < 4096 )
			{
				$entries[] = array( $this->lang->words['suhosin_notification'], sprintf( $this->lang->words['suhosin_badvalue1'], $_postMaxVars ) );
			}
			
			if( $_reqMaxVars < 4096 )
			{
				$entries[] = array( $this->lang->words['suhosin_notification'], sprintf( $this->lang->words['suhosin_badvalue2'], $_reqMaxVars ) );
			}
			
			if( $_postMaxLen < 1000000 )
			{
				$entries[] = array( $this->lang->words['suhosin_notification'], sprintf( $this->lang->words['suhosin_badvalue3'], $_postMaxLen ) );
			}
			
			if( $_reqMaxLen < 1000000 )
			{
				$entries[] = array( $this->lang->words['suhosin_notification'], sprintf( $this->lang->words['suhosin_badvalue4'], $_reqMaxLen ) );
			}
		}
		
		/* SQL error check */
		if ( file_exists( IPS_CACHE_PATH . 'cache/sql_error_latest.cgi' ) )
		{
			$unix = @filemtime( IPS_CACHE_PATH . 'cache/sql_error_latest.cgi' );
			
			if ( $unix )
			{
				$mtime = gmdate( 'd-j-Y', $unix );
				$now   = gmdate( 'd-j-Y', time() );
				
				if ( $mtime == $now )
				{
					/* Display a message */
				}
			}
		}
		
		return $entries;
	}
}
