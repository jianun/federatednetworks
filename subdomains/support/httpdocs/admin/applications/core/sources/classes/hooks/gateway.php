<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Subscriptions Hooks Gateway "Handler"
 * Owner: Matt "Oh Lord, why did I get assigned this?" Mecham
 * Last Updated: $Date: 2010-01-15 15:18:44 +0000 (Fri, 15 Jan 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @since		9th March 2005 11:03
 * @version		$Revision: 5713 $
 */
class core_hookGateway
{
	/**#@+
	 * Registry objects
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
	protected $memberData;
	protected $cache;
	protected $caches;
	/**#@-*/
	
	/**
	 * Method constructor
	 *
	 * @access	public
	 * @param	object		Registry Object
	 * @return	void
	 */
	function __construct( ipsRegistry $registry )
	{
	    /* Make registry objects */
		$this->registry		=  $registry;
		$this->DB			=  $this->registry->DB();
		$this->settings		=& $this->registry->fetchSettings();
		$this->request		=& $this->registry->fetchRequest();
		$this->lang			=  $this->registry->getClass('class_localization');
		$this->member		=  $this->registry->member();
		$this->memberData	=& $this->registry->member()->fetchMemberData();
		$this->cache		=  $this->registry->cache();
		$this->caches		=& $this->registry->cache()->fetchCaches();
		
	}
    

    /**
     * Shows board index recent entries
     *
     */
    public function shareLinks()
    {
    	/* System enabled? */
    	if ( ! $this->settings['sl_enable'] OR ! $this->settings['sl_publicdata'] OR ! $this->memberData['member_id'] )
    	{
    		return '';
    	}
    	
    	/* INIT */
    	$fetchData 			  = array();
    	$finalData			  = array();
    	$noThisIsFinalReally  = array();
    	
    	/* Fetch cached data */
    	$this->DB->build( array( 'select' => '*',
    							 'from'   => 'core_share_links_caches',
    							 'where'  => "cache_key IN ('mostitems', 'mostrecent', 'mosttypes')" ) );
    							 
    	$this->DB->execute();
    	
    	while( $row = $this->DB->fetch() )
    	{
    		$caches[ $row['cache_key'] ] = ( $row['cache_data'] ) ? unserialize( $row['cache_data'] ) : array();
    	}
    	
    	foreach( array( 'mostrecent', 'mostitems' ) as $items )
    	{
    		/* Build an array of data up */
    		if ( is_array( $caches[ $items ] ) AND count( $caches[ $items ] ) )
    		{
    			foreach( $caches[ $items ] as $data )
    			{
    				if ( $data['log_data_app'] AND $data['log_data_type'] AND $data['log_data_primary_id'] )
    				{
    					$key = $data['log_data_app'] . '--' . $data['log_data_type'] . '--' . $data['log_data_primary_id'];
    					
    					$fetchData[ $data['log_data_app'] ][ $key ] = $data;
    				}
    			}
    		}
    	}
    	
    	/* Now go and fetch the data for the grabbed stuff */
    	if ( is_array( $fetchData ) AND count( $fetchData ) )
    	{
    		foreach( $fetchData as $app => $data )
    		{
    			$finalData[ $app ] = $this->_fetchAppData( $app, $data );
    		}
    	}
    	
    	/* Now to mash it all back */
    	if ( is_array( $finalData ) AND count( $finalData ) )
    	{
    		foreach( array( 'mostrecent', 'mostitems' ) as $items )
	    	{
	    		/* Build an array of data up */
	    		if ( is_array( $caches[ $items ] ) AND count( $caches[ $items ] ) )
	    		{
	    			foreach( $caches[ $items ] as $data )
	    			{
	    				if ( $data['log_data_app'] AND $data['log_data_type'] AND $data['log_data_primary_id'] )
	    				{
	    					$key = $data['log_data_app'] . '--' . $data['log_data_type'] . '--' . $data['log_data_primary_id'];
	    					
	    					if ( is_array( $finalData[ $data['log_data_app'] ][ $key ] ) )
	    					{
	    						$noThisIsFinalReally[ $items ][] = array_merge( $data, $finalData[ $data['log_data_app'] ][ $key ] );
	    					}
	    				}
	    			}
	    		}
	    	}
    	}
    	
		return $this->registry->getClass('output')->getTemplate('boards')->hookBoardIndexShareLinks( $noThisIsFinalReally );
    }
    
    /**
     * Fetch application data
     *
     * @access	private
     * @param	string		App
     * @param	array		Array of incoming data
     * @return	array		Array of outgoing data
     */
    private function _fetchAppData( $appKey, $data )
    {
    	if ( $appKey )
    	{
    		$app_cache = $this->cache->getCache('app_cache');
			$app       = $app_cache[ $appKey ];
			
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
						if( method_exists( $_obj, 'processData' ) )
						{
							/* Call it */
							return $_obj->processData( $data );
						}
					}
				}
			}
    	}
    }
}
 