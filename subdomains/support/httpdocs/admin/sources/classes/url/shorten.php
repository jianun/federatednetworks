<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * URL Shortener
 * Owner: Matt Mecham
 * Last Updated: $Date: 2010-01-15 10:18:44 -0500 (Fri, 15 Jan 2010) $
 * </pre>
 *
 * @author 		Matt Mecham
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @since		24th November 2009
 * @version		$Revision: 5713 $
 */

/*
 Example: Shorten URL
 require_once( IPS_ROOT_PATH . 'sources/classes/url/shorten.php' );
 $shorten = new urlShorten();
 $data    = $shorten->shorten( 'http://www.invisionpower.com', 'bitly' );
 
 print $data['url'];
 
 Example: List available shorteners
 require_once( IPS_ROOT_PATH . 'sources/classes/url/shorten.php' );
 $shorten = new urlShorten();
 
 print_r( $shorten->fetchAvailableApis() );
*/

class urlShorten
{	
	/**
	 * Main API classes directory
	 *
	 * @access	public
	 * @var		object
	 */
	private $_mainDir;
	
	/**
	 * API class
	 *
	 * @access	public
	 * @var		object
	 */
	private $_api;
	
	/**
	 * Additional error messages
	 *
	 * @access	public
	 * @var		array
	 */
	public $errors = array();
	
	/**
	 * Method constructor
	 *
	 * @access	public
	 * @return	void
	 *
	 * EXCEPTION MESSAGES
	 * NO_METHOD		API class could not be found
	 */
	public function __construct()
	{
		$this->_mainDir = IPS_ROOT_PATH . 'sources/classes/url/apis';
	}
	
	/**
	 * Shorten URL
	 *
	 * @access	public
	 * @param	string		URL to shorten
	 * @param	string		Method key to use (If left blank, we'll just grab the first one)
	 * @return	array
	 *
	 * EXCEPTION MESSAGES
	 * NO_METHOD		API class could not be found
	 * BAD_FORMAT		URL is not in the correct format
	 * FAILED			URL shorten failed
	 */
	public function shorten( $url, $apiKey )
	{
		/* Check URL */
		if ( ! strstr( $url, 'http://' ) AND ! strstr( $url, 'https://' ) )
		{
			throw new Exception( 'BAD_FORMAT' );
		}
		
		if ( $apiKey )
		{
			$_thisDir = $this->_mainDir . '/' . $apiKey;
			
			if ( @file_exists( $_thisDir . '/api.php' ) )
			{
				$this->_setMethod( $apiKey );
			}
		}
		else
		{
			/* We don't care */
			$apis = $this->fetchAvailableApis();
			
			if ( is_array( $apis ) AND count( $apis ) )
			{
				$apiKey = array_shift( $apis );
				
				if ( $apiKey )
				{
					$this->_setMethod( $apiKey );
				}
			}
		}
		
		if ( ! is_object( $this->_api ) )
		{
			throw new Exception( 'NO_METHOD' );
		}
		
		/* still here? */
		$data = $this->_api->apiShorten( $url );
		
		if ( $data['status'] == 'ok' )
		{
			return $data;
		}
		else
		{
			/* could do something more useful here */
			throw new Exception( 'FAILED' );
		}
	}
	
	/**
	 * Set method
	 * Assumes that the folder and files exist
	 *
	 * @access	private
	 * @param	string
	 */
	private function _setMethod( $apiKey )
	{
		$config = array();
		
		if ( file_exists( $this->_mainDir . '/' . $apiKey . '/conf.php' ) )
		{
			require( $this->_mainDir . '/' . $apiKey . '/conf.php' );
		}
		
		require_once( $this->_mainDir . '/' . $apiKey . '/api.php' );
		
		$this->_api = new $apiKey( $config );
	}
	
	/**
	 * Fetch all available APIs
	 *
	 * @access	public
	 * @return	array
	 */
	public function fetchAvailableApis()
	{
		$apis = array();
		
		try
		{
			foreach( new DirectoryIterator( $this->_mainDir ) as $file )
			{
				if ( ! $file->isDot() AND $file->isDir() )
				{
					$_name = $file->getFileName();
					
					if ( substr( $_name, 0, 1 ) != '.' )
					{
						$apis[] = $_name;
					}
				}
			}
		} catch ( Exception $e ) {}
		
		return $apis;
	}
	
	
		
}