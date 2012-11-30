<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Share links library.
 * Created by Matt Mecham
 * Last Updated: $Date: 2010-01-25 13:21:25 +0000 (Mon, 25 Jan 2010) $
 * </pre>
 *
 * @author 		$Author: matt $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @version		$Rev: 5746 $
 *
 */

class share_links
{
	/**#@+
	* Registry Object Shortcuts
	*
	* @access	protected
	* @var		object
	*/
	protected $DB;
	protected $settings;
	protected $lang;
	protected $member;
	protected $memberData;
	protected $cache;
	protected $caches;
	/**#@-*/
	
	/**
	 * Plug in object
	 *
	 * @access	private
	 * @var		object
	 */
	protected $_plugin;
	protected $_pluginKey = '';
	
	/**
	 * Construct.
	 * @access	public
	 * @param	object		Registry
	 * @param	string		Plug in key
	 * @return	void
	 */
	public function __construct( $registry, $plugin )
	{
		/* Make object */
		$this->registry   =  $registry;
		$this->DB         =  $this->registry->DB();
		$this->settings   =& $this->registry->fetchSettings();
		$this->request    =& $this->registry->fetchRequest();
		$this->lang       =  $this->registry->getClass('class_localization');
		$this->member     =  $this->registry->member();
		$this->memberData =& $this->registry->member()->fetchMemberData();
		$this->cache      =  $this->registry->cache();
		$this->caches     =& $this->registry->cache()->fetchCaches();
		
		/* Store */
		$this->_pluginKey = $plugin;
		
		try
		{
			$this->_initPlugin( $plugin );
		}
		catch ( Exception $e )
		{
			return $e->getMessage();
		}
	}
	
	/**
	 * Share the link.
	 *
	 * @access	public
	 * @param	string		Document title
	 * @param	string		Document URL
	 */
	public function share( $title, $url )
	{
		/* Disabled? */
		if ( ! $this->settings['sl_enable'] )
		{
			return false;
		}
		
		/* Ensure title is correctly de-html-ized */
		$title = IPSText::UNhtmlspecialchars( $title );

		if ( is_object( $this->_plugin ) )
		{
			/* Grab URL details */
			$data = $this->deconstructUrl( $url );
			
			/* Requires a permission check? */
			if ( $data['data_app'] AND method_exists( $this->_plugin, 'requiresPermissionCheck' ) )
			{
				if ( $this->_plugin->requiresPermissionCheck( $data ) !== false )
				{
					$_file   = IPSLib::getAppDir( $data['data_app'] ) . '/extensions/coreExtensions.php';
					$_class  = $data['data_app'] . 'ShareLinks';
					$_result = false;
					
					/* Check for the file */
					if ( file_exists( $_file ) )
					{
						/* Get the file */
						require_once( $_file );
						
						/* Check for the class */
						if ( class_exists( $_class ) )
						{
							/* Create an object */
							$_obj = new $_class();
	
							/* Check for the module */
							if ( method_exists( $_obj, 'permissionCheck' ) )
							{
								if ( $_obj->permissionCheck( $data ) !== false )
								{
									$_result = true;
								}
							}
						}
					}
					
					if ( $_result === false )
					{
						$this->registry->getClass('output')->showError('no_permission');
					}
				}
			}
			
			/* Log it */
			$this->log( $url, $title, $data );
			
			/* In almost all cases, there is no data to return as the plugin
			   redirects or posts an action */
			   
			$this->_plugin->share( $title, $url );
		}
		
		return false;
	}
	
	/**
	 * Init the plug in
	 *
	 * @access	private
	 * @param	string		Plug in
	 */
	private function _initPlugin( $plugin='' )
	{
		$plugin = IPSText::alphanumericalClean( $plugin );
		$parf   = IPS_ROOT_PATH . 'sources/classes/share/plugins/';
		$clarse = 'sl_' . $plugin;
		
		/* Goddit? */
		if ( file_exists( $parf . $plugin . '.php' ) )
		{
			require_once( $parf . $plugin . '.php' );
			$this->_plugin = new $clarse( $this->registry );
		}
		else
		{
			throw new Exception( 'NO_SUCH_PLUGIN' );
		}
	}
	
	/**
	 * Log a URL. Simple really.
	 *
	 * @access	public
	 * @param	string		URL
	 * @return	nufink
	 */
	public function deconstructUrl( $url )
	{
		/* init */
		$data = array( 'data_app'          => '',
					   'data_type'		   => '',
					   'data_primary_id'   => 0,
					   'data_secondary_id' => 0 );
					  
		/* Try FURL first */
		$ret = $this->_checkForFurl( $url );
		
		if ( $ret === false OR ! count( $ret ) )
		{
			$ret = $this->_checkForApps( $this->_explodeUrl( $url ) );
		}
		else if ( is_array( $ret ) AND count( $ret ) AND ! $ret['app'] )
		{
			/* Is a shorter redirect based link, so use app processing... */
			$_url = array();
			
			foreach( $ret as $k => $v )
			{
				$_url[] = $k . '=' . $v;
			}
			
			$ret = $this->_checkForApps( $this->_explodeUrl( implode( '&', $_url ) ) );
		}
		
		/* Try again */
		if ( is_array( $ret ) AND count( $ret ) AND $ret['data_app'] )
		{
			$data = $ret;
		}
		
		return $data;
	}
	
	/**
	 * Log a URL. Simple really.
	 *
	 * @access	public
	 * @param	string		URL
	 * @return	nufink
	 */
	public function log( $url, $title, $urlParts=array() )
	{
		/* If we're a 'bot, sod awf */
		if ( $this->member->is_not_human )
		{
			return;
		}
		
		/* Ensure we're not jabbing our podgy fingers on the refresh button
		   We don't check the link 'cos you can any ?param on the end. member_id + share service should do */
		if ( $this->memberData['member_id'] )
		{
			$check = $this->DB->buildAndFetch( array( 'select' => 'log_id',
													  'from'   => 'core_share_links_log',
													  'where'  => '(log_member_id=' . $this->memberData['member_id'] . ' OR log_ip_address=\'' . $this->member->ip_address . '\') AND log_share_key=\'' . $this->_pluginKey . '\' AND log_date > ' . ( time() - 60 ) ) );
		}
		else
		{
			$check = $this->DB->buildAndFetch( array( 'select' => 'log_id',
													  'from'   => 'core_share_links_log',
													  'where'  => 'log_ip_address=\'' . $this->member->ip_address . '\' AND log_share_key=\'' . $this->_pluginKey . '\' AND log_date > ' . ( time() - 60 ) ) );
		}
														  
		if ( $check['log_id'] )
		{
			return;
		}
		
		/* Log */
		$this->DB->insert( 'core_share_links_log', array( 'log_date'		      => time(),
														  'log_member_id'         => $this->memberData['member_id'],
														  'log_url'			      => $url,
														  'log_title'		      => $title,
														  'log_ip_address'        => $this->member->ip_address,
														  'log_share_key'         => $this->_pluginKey,
														  'log_data_app'	      => $urlParts['data_app'],
														  'log_data_type'         => $urlParts['data_type'],
														  'log_data_primary_id'   => $urlParts['data_primary_id'],
														  'log_data_secondary_id' => $urlParts['data_secondary_id'] ) );
														  
		/* Rebuild caches */
		$this->rebuildCaches();
	}
	
	/**
	 * Build some caches of stuff.
	 *
	 * @access	public
	 *
	 */
	public function rebuildCaches()
	{
		/* INIT */
		$caches = array( 'mostitems' => array(), 'mostrecent' => array(), 'mosttypes' => array() );
		
		/* Delete caches */
		$this->DB->delete( 'core_share_links_caches', "cache_key IN ('mostitems', 'mostrecent', 'mosttypes')" );
		
		/* Most shared */
		$this->DB->build( array( 'select' => 'COUNT(*) as count, log_data_app, log_data_type, log_data_primary_id',
								 'from'	  => 'core_share_links_log',
								 'group'  => 'log_data_app, log_data_type, log_data_primary_id',
								 'order'  => 'count DESC',
								 'limit'  => array( 0, 10 ) ) );
								 
		$this->DB->execute();
		
		while( $row = $this->DB->fetch() )
		{
			$caches['mostitems'][] = $row;
		}
		
		/* Most recent shares */
		$this->DB->build( array( 'select' => 'log_id, log_data_app, log_data_type, log_data_primary_id',
								 'from'	  => 'core_share_links_log',
								 'order'  => 'log_date DESC',
								 'limit'  => array( 0, 10 ) ) );
								 
		$this->DB->execute();
		
		while( $row = $this->DB->fetch() )
		{
			$caches['mostrecent'][] = $row;
		}
		
		/* Most used share type*/
		$this->DB->build( array( 'select' => 'COUNT(*) as count, log_share_key',
								 'from'	  => 'core_share_links_log',
								 'group'  => 'log_share_key',
								 'order'  => 'count DESC',
								 'limit'  => array( 0, 10 ) ) );
								 
		$this->DB->execute();
		
		while( $row = $this->DB->fetch() )
		{
			$caches['mosttypes'][] = $row;
		}
		
		/* Save 'em */
		foreach( $caches as $key => $data )
		{
			$this->DB->insert( 'core_share_links_caches', array( 'cache_key' => $key, 'cache_date' => time(), 'cache_data' => serialize( $data ) ) ); 
		}
	}
	
	/**
	 * Try and deconstruct the link via app specific processing
	 *
	 * @access	private
	 * @param	array		Array or URL bits
	 * @return	array		Array of request data or false
	 */
	private function _checkForApps( $url )
	{
		$app_cache = $this->cache->getCache('app_cache');
		
		/* Loop through applications */
		foreach( $app_cache as $app_dir => $app )
		{
			/* Only if app enabled... */
			if ( $app['app_enabled'] )
			{
				/* Setup */
				$_file  = IPSLib::getAppDir( $app['app_directory'] ) . '/extensions/coreExtensions.php';
				$_class = $app['app_directory'] . 'ShareLinks';
					
				/* Check for the file */
				if( file_exists( $_file ) )
				{
					/* Get the file */
					require_once( $_file );

					/* Check for the class */
					if( class_exists( $_class ) )
					{
						/* Create an object */
						$_obj = new $_class();

						/* Check for the module */
						if( method_exists( $_obj, 'deconstructUrl' ) )
						{
							/* Call it */
							$ret = $_obj->deconstructUrl( $url );
							
							if ( is_array( $ret ) AND $ret['data_app'] )
							{
								return $ret;
							}
						}
					}
				}
			}
		}
		
		return array( 'data_app' => '' );
	}
	
	/**
	 * Try and deconstruct the link if it's a FURRY FURL
	 *
	 * @access	private
	 * @param	string		Incoming URL
	 * @return	array		Array of request data or false
	 */
	private function _checkForFurl( $url )
	{
		$_urlBits = array();
		$_toTest  = $url;
		
		/* Grab FURL data... */
		if ( ! IN_DEV AND file_exists( DOC_IPS_ROOT_PATH . 'cache/furlCache.php' ) )
		{
			require( DOC_IPS_ROOT_PATH . 'cache/furlCache.php' );
			$_seoTemplates = $templates;
		}
		else
		{
			/* Attempt to write it */
			$_seoTemplates = IPSLib::buildFurlTemplates();
			
			try
			{
				IPSLib::cacheFurlTemplates();
			}
			catch( Exception $e )
			{
			}
		}

		if ( is_array( $_seoTemplates ) AND count( $_seoTemplates ) )
		{ 
			foreach( $_seoTemplates as $key => $data )
			{
				if ( ! isset( $data['in']['regex'] ) OR ! $data['in']['regex'] )
				{
					continue;
				}

				if ( preg_match( $data['in']['regex'], $_toTest, $matches ) )
				{ 
					if ( is_array( $data['in']['matches'] ) )
					{
						foreach( $data['in']['matches'] as $_replace )
						{
							$k = IPSText::parseCleanKey( $_replace[0] );

							if ( strpos( $_replace[1], '$' ) !== false )
							{
								$v = IPSText::parseCleanValue( $matches[ intval( str_replace( '$', '', $_replace[1] ) ) ] );
							}
							else
							{
								$v = IPSText::parseCleanValue( $_replace[1] );
							}
							
							$_urlBits[ $k ] = $v;
						}
					}

					if ( strpos( $_toTest, $_seoTemplates['__data__']['varBlock'] ) !== false )
					{ 
						$_parse = substr( $_toTest, strpos( $_toTest, $_seoTemplates['__data__']['varBlock'] ) + strlen( $_seoTemplates['__data__']['varBlock'] ) );

						$_data = explode( $_seoTemplates['__data__']['varSep'], $_parse );
						$_c    = 0;

						foreach( $_data as $_v )
						{
							if ( ! $_c )
							{
								$k = IPSText::parseCleanKey( $_v );
								$v = '';
								$_c++;
							}
							else
							{
								$v  = IPSText::parseCleanValue( $_v );
								$_c = 0;

								$_urlBits[ $k ] = $v;
							}
						}
					}
					
					break;
				}
			}
			
			//-----------------------------------------
			// If using query string furl, extract any
			// secondary query string.
			// Ex: http://localhost/index.php?/path/file.html?key=value
			// Will pull the key=value properly
			//-----------------------------------------
			
			$_qmCount = substr_count( $_toTest, '?' );
			
			if ( $_qmCount > 1 )
			{ 
				$_secondQueryString	= substr( $_toTest, strrpos( $_toTest, '?' ) + 1 );
				$_secondParams		= explode( '&', $_secondQueryString );
				
				if( count($_secondParams) )
				{
					foreach( $_secondParams as $_param )
					{
						list( $k, $v )	= explode( '=', $_param );
						
						$k	= IPSText::parseCleanKey( $k );
						$v	= IPSText::parseCleanValue( $v );
						
						$_urlBits[ $k ] = $v;
					}
				}
			}
			
			/* Process URL bits for extra ? in them */
			if ( is_array( $_urlBits ) AND count( $_urlBits ) )
			{
				foreach( $_urlBits as $k => $v )
				{
					if ( strstr( $v, '?') )
					{
						list( $rvalue, $more ) = explode( '?', $v );
						
						if ( $rvalue AND $more )
						{
							/* Reset key with correct value */
							$_v = $rvalue;
							
							$_urlBits[ $k ] = $_v;
							
							/* Now add in the other value */
							if ( strstr( $more, '=' ) )
							{
								list( $_k, $_v ) = explode( '=', $more );
								
								if ( $_k and $_v )
								{
									$_urlBits[ $_k ] = $_v;
								}
							}
						}
					}
				}
			}
		}
		
		return ( count($_urlBits) ) ? $_urlBits : false;
	}
	
	/**
	 * Explode a URL (foo=bar&baz=umm) into array( 'foo' => 'bar', .... )
	 *
	 * @access	private
	 * @param	string		URL
	 * @return	array		Values
	 */
	private function _explodeUrl( $url )
	{
		$url = str_replace( '&amp;', '&', $url );
		$ret = array();
		
		if ( strstr( $url, '?' ) )
		{
			list( $_u, $url ) = explode( '?', $url );
		}
		
		foreach( explode( '&', $url ) as $bit )
		{
			list($k, $v) = explode( '=', $bit );
			
			if ( $k and $v )
			{
				$ret[ trim( $k ) ] = trim( $v );
			}
		}
		
		return $ret;
	}

		
}