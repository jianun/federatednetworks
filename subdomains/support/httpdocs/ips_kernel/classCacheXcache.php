<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * This class acts as a cache layer, allowing you to store and retrieve data in
 *	external cache sources such as memcache or APC
 * Last Updated: $Date: 2010-01-15 10:18:25 -0500 (Fri, 15 Jan 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Kernel
 * @link		http://www.invisionpower.com
 * @since		Friday 19th May 2006 17:33
 * @version		$Revision: 370 $
 *
 * Basic Usage Examples
 * <code>
 * $cache = new cache_lib( 'identifier' );
 * Update:
 * $cache->putInCache( 'key', 'value' [, 'ttl'] );
 * Remove
 * $cache->removeFromCache( 'key' );
 * Retrieve
 * $cache->getFromCache( 'key' );
 * </code>
 *
 */

class classCacheXcache implements interfaceCache
{
	/**
	 * Identifier
	 * @access	private
	 * @var		string
	 */
	private $identifier	= '';
	
    /**
	* Constructor
	 *
	 * @access	public
	 * @param	string 		Unique identifier
	 * @return	boolean		Initiation successful
	 */
	public function __construct( $identifier='' )
	{
		if( !function_exists('xcache_get') )
		{
			$this->crashed = true;
			return false;
		}
		
		$this->identifier	= $identifier;
	}
	
    /**
	 * Put data into remote cache store
	 *
	 * @access	public
	 * @param	string		Cache unique key
	 * @param	string		Cache value to add
	 * @param	integer		[Optional] Time to live
	 * @return	boolean		Cache update successful
	 */
	public function putInCache( $key, $value, $ttl=0 )
	{
		$ttl = $ttl > 0 ? intval($ttl) : '';
		
		if( $ttl )
		{
			return xcache_set( md5( $this->identifier . $key ), $value, $ttl );
		}
		else
		{
			return xcache_set( md5( $this->identifier . $key ), $value );
		}
	}
	
    /**
	 * Retrieve a value from remote cache store
	 *
	 * @access	public
	 * @param	string		Cache unique key
	 * @return	mixed		Cached value
	 */
	public function getFromCache( $key )
	{
		$return_val = "";
		
		if( xcache_isset( md5( $this->identifier . $key ) ) )
		{
			$return_val = xcache_get( md5( $this->identifier . $key ) );
		}
		
		return $return_val;
	}
	
    /**
	 * Update value in remote cache store
	 *
	 * @access	public
	 * @param	string		Cache unique key
	 * @param	string		Cache value to set
	 * @param	integer		[Optional] Time to live
	 * @return	boolean		Cache update successful
	 */
	public function updateInCache( $key, $value, $ttl=0 )
	{
		$this->removeFromCache( $key );
		return $this->putInCache( $key, $value, $ttl );
	}
	
    /**
	 * Remove a value in the remote cache store
	 *
	 * @access	public
	 * @param	string		Cache unique key
	 * @return	boolean		Cache removal successful
	 */
	public function removeFromCache( $key )
	{
		return xcache_unset( md5( $this->identifier . $key ) );
	}
	
    /**
	 * Not used by this library
	 *
	 * @return	boolean		Disconnect successful
	 */
	public function disconnect()
	{
		return true;
	}	
}