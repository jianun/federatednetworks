<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * URL shortener
 * Owner: Matt Mecham
 * Last Updated: $Date: 2010-01-22 10:10:04 -0500 (Fri, 22 Jan 2010) $
 * </pre>
 *
 * @author 		Matt Mecham
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @since		24th November 2009
 * @version		$Revision: 5742 $
 */

	 
/* Class name must match shortener directory name */
class bitly extends urlShorten
{
	private $_config = array();
	
	private $_cfm;
	
	/**
	 * Method constructor
	 *
	 * If you pass false as the key, it will not save out the imported GUIDs
	 * @access	public
	 * @return	void
	 * 
	 */
	public function __construct( $config=array() )
	{
		$this->_config = $config;
		
		require_once( IPS_KERNEL_PATH . 'classFileManagement.php' );
		$this->_cfm              = new classFileManagement();
		$this->_cfm->use_sockets = 1;
		$this->_cfm->timeout     = 30;
	}
	
	/**
	 * Shorten URL
	 *
	 * @access	public
	 * @param	string	URL to shorten
	 * @return	array ( 'status' => [ok/failed], 'url' => [shortened url], 'method' => [api used], 'raw' => [any raw data] )
	 * Exception codes:
	 */
	public function apiShorten( $url )
	{
		/* Query the service */
		$response = $this->_cfm->getFileContents( "http://api.bit.ly/shorten?version=2.0.1&longUrl=" . urlencode( $url ) . "&login={$this->_config['login']}&apiKey={$this->_config['apiKey']}" );
		
		if ( ! $response )
		{
			return array( 'status' => 'failed' );
		}
		
		$obj = json_decode( $response, TRUE );
		
		return array( 'status' => 'ok',
					  'url'    => $obj['results'][$url]['shortUrl'],
					  'method' => 'bitly',
					  'raw'    => $obj['results'] );
	}
	
}