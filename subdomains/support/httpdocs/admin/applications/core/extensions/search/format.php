<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Formats forum search results
 * Last Updated: $Date: 2010-02-19 01:29:54 +0000 (Fri, 19 Feb 2010) $
 * </pre>
 *
 * @author 		$author$
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Forums
 * @link		http://www.invisionpower.com
 * @version		$Rev: 5855 $
 **/

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class search_format_core extends search_format
{
	/**
	 * Constructor
	 */
	public function __construct( ipsRegistry $registry )
	{
		parent::__construct( $registry );
	}
	
	/**
	 * Parse search results
	 *
	 * @access	private
	 * @param	array 	$r				Search result
	 * @return	array 	$html			Blocks of HTML
	 */
	public function parseAndFetchHtmlBlocks( $rows )
	{
		return parent::parseAndFetchHtmlBlocks( $rows );
	}
	
	/**
	 * Formats the forum search result for display
	 *
	 * @access	public
	 * @param	array   $search_row		Array of data from search_index
	 * @return	mixed	Formatted content, ready for display, or array containing a $sub section flag, and content
	 **/
	public function formatContent( $data )
	{
		return array( ipsRegistry::getClass( 'output' )->getTemplate( 'search' )->helpSearchResult( $data, IPSSearchRegistry::get('display.onlyTitles') ), 0 );
	}

	/**
	 * Formats / grabs extra data for results
	 * Takes an array of IDS (can be IDs from anything) and returns an array of expanded data.
	 *
	 * @access public
	 * @return array
	 */
	public function processResults( $ids )
	{
		$rows = array();
		
		foreach( $ids as $i => $d )
		{
			$rows[ $i ] = $this->genericizeResults( $d );
		}
		
		return $rows;	
	}
	
	/**
	 * Reassigns fields in a generic way for results output
	 *
	 * @param  array  $r
	 * @return array
	 **/
	public function genericizeResults( $r )
	{
		$r['app']                  = 'core';
		$r['content']              = $r['title'];
		$r['content_title']        = $r['description'];
		$r['updated']              = 0;
		$r['type_2']               = 'help';
		$r['type_id_2']            = $r['id'];

		return $r;
	}

}