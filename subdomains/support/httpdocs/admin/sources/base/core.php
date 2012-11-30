<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Static Classes for IP.Board 3
 *
 * These classes are not required as objects. We have grouped
 * together several singletons to prevent multiple file loads
 * Last Updated: $Date: 2010-07-12 07:49:13 -0400 (Mon, 12 Jul 2010) $
 * </pre>
 *
 * @author 		$Author: matt $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @since		12th March 2002
 * @version		$Revision: 6631 $
 *
 * @author	Matt
 */
 
 
/**
 * This class for legacy reasons only - should be removed in 3.2
 * Just stops skins breaking.
 */
class IPSSearchIndex
{
	/**
	 * Loads the correct search plugin based on search settings
	 *
	 * @param   string  [$force_index]  Optional, force a particular index
	 * @access	public
	 * @return	object
	 **/
	static public function getSearchPlugin( $force_index='' )
	{
		return false;
	}

	/**
	 * Determines if the application can be searched
	 *
	 * @access	public
	 * @param	string	$app	Application key
	 * @return	bool
	 **/
	static public function appIsSearchable( $app )
	{
		/* INI */
		return IPSLib::appIsInstalled( $app );
	}

	/**
	 * Returns the search display plugin for the specified app
	 *
	 * @access	public
	 * @param	string	$app	Application Key
	 * @return	object
	 **/
	static public function getSearchDisplayPlugin( $app )
	{
		return false;
	}
}


class IPSAdCodeDefault
{
	/**
	 * Ad code to overwrite the global header code
	 *
	 * @access	public
	 * @var		string
	 */
	public $headerCode = '';
	
	/**
	 * Ad code to overwrite the global footer code
	 *
	 * @access	public
	 * @var		string
	 */
	public $footerCode = '';
	
	public function __construct( ipsRegistry $registry )
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
	}
	
	/**
	 * Basic functionality
	 */
	public function userCanViewAds()
	{
		return false;
	}
}

/**
 * Deletion Log Class
 */
class IPSDeleteLog
{
	/**
	* Add entry to the delete log
	*
	* @access	public
	* @param	int			Object ID
	* @param	string		Object Type
	* @param	string		Reason for addition
	* @param	array		Array of member data of user adding log entry
	*/
	public static function addEntry( $id, $type, $reason, $memberData )
	{
		if ( $id AND $type AND is_array( $memberData ) AND $memberData['member_id'] )
		{
			ipsRegistry::DB()->replace( 'core_soft_delete_log', array( 'sdl_obj_id'        => $id,
																	   'sdl_obj_key'       => $type,
																	   'sdl_obj_reason'    => $reason,
																	   'sdl_obj_member_id' => $memberData['member_id'],
																	   'sdl_obj_date'      => time(),
																	   'sdl_locked'		   => 0 ), array( 'sdl_obj_id', 'sdl_obj_key' ) );
																	   
			return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	* Remove entres to the delete log
	*
	* @access	public
	* @param	array		Object IDs
	* @param	string		Object Type
	* @param	boolean		Force deletion (used when deleting topics/posts/etc)
	*/
	public static function removeEntries( $ids, $type, $forceDelete=false )
	{
		if ( is_array( $ids ) AND count( $ids ) AND $type )
		{
			$ids   = IPSLib::cleanIntArray( $ids );
			
			/* if we're not a global mod, then lock these not remove unless we're deleting stuff */
			if ( ! ipsRegistry::member()->getProperty('g_is_supmod') AND $forceDelete === false )
			{
				ipsRegistry::DB()->update( 'core_soft_delete_log', array( 'sdl_locked' => 1 ), 'sdl_obj_id IN (' . implode( ',', $ids ) . ') AND sdl_obj_key=\'' . $type . '\'' );
			}
			else
			{
				ipsRegistry::DB()->delete( 'core_soft_delete_log', 'sdl_obj_id IN (' . implode( ',', $ids ) . ') AND sdl_obj_key=\'' . $type . '\'' );
			}
			
			return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	* Fetch entries from the delete log
	*
	* @access	public
	* @param	array		Object IDs
	* @param	string		Object Type
	* @param	boolean		Parse Member Data
	*/
	public static function fetchEntries( $ids, $type, $parseMember=true )
	{
		$return = array();
		
		if ( is_array( $ids ) AND count( $ids ) AND $type )
		{
			$ids = IPSLib::cleanIntArray( $ids );
			
			ipsRegistry::DB()->build( array( 'select'   => 'l.*',
											 'from'     => array( 'core_soft_delete_log' => 'l' ),
											 'where'    => 'sdl_obj_id IN (' . implode( ',', $ids ) . ') AND sdl_obj_key=\'' . $type . '\'',
											 'add_join' => array( array( 'select' => 'm.*',
											 							 'from'   => array( 'members' => 'm' ),
											 							 'where'  => 'l.sdl_obj_member_id=m.member_id' ),
											 					  array( 'select' => 'p.*',
											 					  		 'from'	  => array( 'profile_portal' => 'p' ),
											 					  		 'where'  => 'l.sdl_obj_member_id=p.pp_member_id' ) ) ) );
											 					  		 
			$i = ipsRegistry::DB()->execute();
											 					  		 
			while( $row = ipsRegistry::DB()->fetch( $i ) )
			{
				if ( $parseMember )
				{
					$row['member'] = IPSMember::buildDisplayData( $row );
				}
				
				$return[ $row['sdl_obj_id'] ] = $row;
			}
			
			return $return;
		}
		
		return array();
	}
}

class IPSContentCache
{
	/**
	 * Keep track of what tables are linked to which key
	 *
	 * @access	private
	 * @var		array
	 */
	private static $_tables = array( 'post' => 'content_cache_posts',
							  		 'sig'  => 'content_cache_sigs' );
							
						
	/**
	 * Keep track of what settings are linked to which key
	 *
	 * @access	private
	 * @var		array
	 */
	private static $_settings = array( 'post' => 'cc_posts',
							    	   'sig'  => 'cc_sigs' );
							
	/**
	 * Check to see whether content caching is enabled
	 *
	 * @access	public
	 * @return	boolean
	 */
	static public function isEnabled()
	{
		return ( ipsRegistry::$settings['cc_on'] AND ( ipsRegistry::$settings['cc_posts'] OR ipsRegistry::$settings['cc_sigs'] ) ) ? TRUE : FALSE;
	}
	
	/**
	 * Check to see whether we have a valid type
	 *
	 * @access	public
	 * @param	string		Content Type (post/sig/etc)
	 * @return	boolean
	 */
	static public function isValidType( $type )
	{
		return ( in_array( $type, array_keys( self::$_tables ) ) ) ? TRUE : FALSE;
	}
	
	/**
	 * Fetch correct table name based on type
	 * Assumes isValidType has been run
	 *
	 * @access	public
	 * @param	string		Content Type (post/sig/etc)
	 * @return	boolean
	 */
	static public function fetchTableName( $type )
	{
		return self::$_tables[ $type ];
	}
	
	/**
	 * Fetch correct setting value based on type
	 * Assumes isValidType has been run
	 *
	 * @access	public
	 * @param	string		Content Type (post/sig/etc)
	 * @return	boolean
	 */
	static public function fetchSettingValue( $type )
	{
		return ipsRegistry::$settings[ self::$_settings[ $type ] ];
	}
	
	/**
	 * Monitor: See how many items are served from the cache versus ... not
	 *
	 * You need to add 'cc_monitor' => 1 into conf_global.php to enable this
	 *
	 * @access	public
	 * @param	array 		Array of data like array( 'post' => array( 'cached' => x, 'raw' => x )
	 * @return	void
	 */
	static public function updateMonitor( $array )
	{
		/* Check.. */
		if ( ! ipsRegistry::$settings['cc_monitor'] )
		{
			return FALSE;
		}
		
		/* Enabled?? */
		if ( ! self::isEnabled() )
		{
			return FALSE;
		}
		
		/* Search engine? */
		if ( ipsRegistry::member()->is_not_human === TRUE )
		{
			return FALSE;
		}
		
		$savedData = ipsRegistry::cache()->getCache( 'ccMonitor' );
		
		/* Ensure its valid */
		if ( ! is_array( $savedData ) OR ! count( $savedData ) )
		{
			$savedData = array( 'post' => array( 'cached' => 0, 'raw' => 0 ),
							 	'sig'  => array( 'cached' => 0, 'raw' => 0 ) );

		}
		
		foreach( $array as $type => $data )
		{
			if ( self::isValidType( $type ) )
			{
				$savedData[ $type ]['cached'] += intval( $data['cached'] );
				$savedData[ $type ]['raw']    += intval( $data['raw'] );
			}
		}
		
		/* Write back */
		ipsRegistry::cache()->setCache( 'ccMonitor', $savedData, array( 'array' => 1, 'donow' => 1 ) );
	}
	
	/**
	 * Add data to the cache
	 *
	 * @access	public
	 * @param	int			Content ID
	 * @param	string		Content Type (post/sig/etc)
	 * @param	string		Content
	 * @param	boolean		Already had preDb/preDisplay run. It FALSE, assumed preDb has been run and no HTML will be parsed but smilies and bbcode will be
	 * @return	bool
	 */
	static public function update( $id, $type, $content, $parsed=TRUE )
	{
		/* Enabled?? */
		if ( ! self::isEnabled() )
		{
			return FALSE;
		}
		
		/* Valid type?? */
		if ( ! self::isValidType( $type ) )
		{
			return FALSE;
		}
		
		/* Search engine? */
		if ( ipsRegistry::member()->is_not_human === TRUE )
		{
			return FALSE;
		}
		
		/* Init */
		$parsingSection = 'topics';
		
		if ( $content AND $parsed !== TRUE )
		{
			/* What are we parsing? */
			switch( $type )
			{
				case 'post':
					$parsingSection = 'topics';
				break;
				case 'sig':
					$parsingSection = 'signatures';
				break;
			}
					
			/* Set up parser */
			IPSText::getTextClass( 'bbcode' )->parse_smilies         = 1;
			IPSText::getTextClass( 'bbcode' )->parse_html    	     = 0;
			IPSText::getTextClass( 'bbcode' )->parse_nl2br		     = 1;
			IPSText::getTextClass( 'bbcode' )->parse_bbcode    	     = 1;
			IPSText::getTextClass( 'bbcode' )->parsing_section	     = $parsingSection;
			IPSText::getTextClass( 'bbcode' )->parsing_mgroup		 = ipsRegistry::member()->getProperty( 'member_group_id' );
			IPSText::getTextClass( 'bbcode' )->parsing_mgroup_others = ipsRegistry::member()->getProperty( 'mgroup_others' );

			/* Format */
			$content = IPSText::getTextClass( 'bbcode' )->preDisplayParse( IPSText::getTextClass( 'bbcode' )->preDbParse( $content ) );
		}
		
		if ( $content )
		{
			ipsRegistry::DB()->force_data_type = array( 'cache_content' => 'string' );
												
			ipsRegistry::DB()->replace( self::fetchTableName( $type ), array( 'cache_content_id' => $id,
																			  'cache_content'    => $content,
																			  'cache_updated'    => time() ), array( 'cache_content_id' ) );
		}
		else
		{
			/* No content, then drop it */
			self::drop( $type, $id );
		}
		
		return TRUE;
	}
	
	/**
	 * Drop data from the cache
	 * If no ID is passed, it'll drop all caches for the supplied 'type'
	 *
	 * @access	public
	 * @param	string		Content Type (post/sig/etc)
	 * @param	int/array	[Content ID]
	 * @return	bool
	 */
	static public function drop( $type, $id=0 )
	{
		if ( ! self::isEnabled() )
		{
			return FALSE;
		}
		
		/* Valid type?? */
		if ( ! self::isValidType( $type ) )
		{
			return FALSE;
		}
		
		if ( $id )
		{
			if ( is_array( $id ) )
			{
				$id = implode( ',', $id );
			}
			
			ipsRegistry::DB()->delete( self::fetchTableName( $type ), "cache_content_id IN (" . $id . ")" );
		}
		else
		{
			ipsRegistry::DB()->delete( self::fetchTableName( $type ) );
		}
		
		return TRUE;
	}
	
	/**
	 * Remove all "type" data from the cache
	 *
	 * @access	public
	 * @param	string		[Content Type (post/sig/etc)]
	 * @return	int			Number of rows affected
	 */
	static public function truncate( $type='' )
	{
		if ( ! self::isEnabled() )
		{
			return 0;
		}
		
		/* Valid type?? */
		if ( $type AND ! self::isValidType( $type ) )
		{
			return 0;
		}
		
		$affected = 0;
		
		if ( $type )
		{
			$count = ipsRegistry::DB()->buildAndFetch( array( 'select' => 'COUNT(*) as total', 'from' => self::fetchTableName( $type ) ) );
			
			ipsRegistry::DB()->delete( self::fetchTableName( $type ) );
			$affected = $count['total']; // ipsRegistry::DB()->getAffectedRows(); - With no where clause, mysql_affected_rows always returns 0
		}
		else
		{
			foreach( self::$_tables as $type => $name )
			{
				$count = ipsRegistry::DB()->buildAndFetch( array( 'select' => 'COUNT(*) as total', 'from' => $name ) );
				ipsRegistry::DB()->delete( $name );
				$affected += $count['total']; // $affected += ipsRegistry::DB()->getAffectedRows(); - With no where clause, mysql_affected_rows always returns 0
			}
		}
		
		return intval( $affected );
	}
	
	/**
	 * Count the number of cached items
	 *
	 * @access	public
	 * @param	string		[Content Type (post/sig/etc)]
	 * @return	int			Combined number of items
	 */
	static public function count( $type='' )
	{
		if ( ! self::isEnabled() )
		{
			return FALSE;
		}
		
		/* Valid type?? */
		if ( $type AND ! self::isValidType( $type ) )
		{
			return FALSE;
		}
		
		$count = 0;
		
		if ( $type )
		{
			$row   = ipsRegistry::DB()->buildAndFetch( array( 'select' => 'COUNT(*) as c', 'from' => self::fetchTableName( $type ) ) );
			$count = intval( $row['c'] );
		}
		else
		{
			foreach( self::$_tables as $type => $name )
			{
				$row    = ipsRegistry::DB()->buildAndFetch( array( 'select' => 'COUNT(*) as c', 'from' => $name ) );
				$count += intval( $row['c'] );
			}
		}
		
		return intval( $count );
	}
	
	/**
	 * Prune items back to X days
	 *
	 * If no type is supplied, all types are pruned
	 *
	 * @access	public
	 * @param	string		[Content Type (post/sig/etc)]
	 * @return	int			Number of rows affected
	 */
	static public function prune( $type='' )
	{
		if ( ! self::isEnabled() )
		{
			return FALSE;
		}
		
		/* Valid type?? */
		if ( $type AND ! self::isValidType( $type ) )
		{
			return FALSE;
		}
		
		$affected = 0;
		
		if ( $type )
		{
			$time = time() - ( self::fetchSettingValue( $type ) * 86400 );
			
			ipsRegistry::DB()->delete( self::fetchTableName( $type ), "cache_updated <" . $time );
			$affected = ipsRegistry::DB()->getAffectedRows();
		}
		else
		{
			foreach( self::$_tables as $type => $name )
			{
				$time = time() - ( self::fetchSettingValue( $type ) * 86400 );
				
				ipsRegistry::DB()->delete( self::fetchTableName( $type ), "cache_updated <" . $time );
				$affected += ipsRegistry::DB()->getAffectedRows();
			}
		}
		
		return intval( ipsRegistry::DB()->getAffectedRows() );
	}
	
	/**
	 * Fetch table join
	 *
	 * Cheap way of grabbing the join on the cache table so that your code
	 * doesn't have to check for whether we're using the cache or not, etc
	 *
	 * @access	public
	 * @param	string	Content Type (post/sig/etc)
	 * @param	string	Join field (eg 'p.pid')
	 * @param	string	[Table alias - default 'cca']
	 * @param	string	[Table join type - default 'left']
	 * @param	string	[Custom select so that fields can be aliased, etc]
	 * @return	bool
	 */
	static public function join( $type, $joinField, $alias='cca', $joinType='left', $customSelect='' )
	{
		if ( ! self::isEnabled() )
		{
			return FALSE;
		}
		
		/* Valid type?? */
		if ( ! self::isValidType( $type ) )
		{
			return FALSE;
		}
		
		return array( 'select' => ( $customSelect ) ? $customSelect : $alias.'.*',
					  'from'   => array( self::fetchTableName( $type ) => $alias ),
					  'where'  => $alias . '.cache_content_id=' . $joinField,
					  'type'   => $joinType );
	}
}
	
	
/**
 * Experimental class for storing options as bitwise
 *
 * @author	Matt
 */
class IPSBWOptions
{
	/**
	 * Convert a bit field into an array of options
	 *
	 * @access	public
	 * @param	int		Bitwise option
	 * @param	string	Type of options to decipher (user / groups / etc)
	 * @param	string	App
	 * @return	array
	 * <code>$options = IPSBWOptions::thawOptions( 18, 'user', 'forums' );</code>
	 */
	static public function thaw( $bitfield, $type, $app='global' )
	{
		/* INIT */
		$bitfield = intval($bitfield);
		$array    = array();
		
		/* Generate bitwise array */
		$bitArray = self::_getBitWiseArray( $type, $app );
		
		if ( ! $bitArray OR ! count( $bitArray ) )
		{
			return array();
		}
		
		/* Build options */
		foreach( $bitArray as $key => $bitvalue )
		{
			if ( $bitfield & intval( $bitvalue ) )
			{
				$array[ $key ] = 1;
			}
			else

			{
				$array[ $key ] = 0;
			}
		}
		
		return $array;
	}
	
	/**
	 * Build an SQL query bit
	 *
	 * @access	public
	 * @param	string		Field (field name as assigned by thaw)
	 * @param	string		SQL field
	 * @param	string		Type (members, groups, etc )
	 * @param	string		App (global, forums, etc)
	 * @param	string		Type of SQL query (add/remove/has)
	 * @return	string		Formatted SQL field
	 */
	static public function sql( $bitField, $sqlField, $type, $app='global', $sql='has' )
	{
		/* Generate int sign */
		switch( $sql )
		{
			default:
			case 'has':
				$_sign = '&';
			break;
			case 'remove':
				$_sign = '-';
			break;
			case 'add':
				$_sign = '+';
			break;
		}
		
		/* Generate bitwise array */
		$bitArray = self::_getBitWiseArray( $type, $app );

		/* Do it.. .*/
		if ( in_array( $bitField, array_keys( $bitArray ) ) )
		{
			return '( ' . $sqlField . ' ' . $_sign . ' ' . $bitArray[ $bitField ] . ' ) != 0';
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Freeze options
	 * Converts an array of options array( 'key' => 0 ... ) into an int for saving in a DB field
	 *
	 * @access	public
	 * @param	array 		Array of key => values to save
	 * @param	string		Type of options to save
	 * @param	string		App
	 * @return	int
	 */
	static public function freeze( $toSave, $type, $app='global' )
	{
		/* INIT */
		$int = 0;
		
		/* Generate bitwise array */
		$bitArray = self::_getBitWiseArray( $type, $app );
		
		if ( ! $bitArray OR ! count( $bitArray ) )
		{
			return 0;
		}
		
		foreach( $bitArray as $key => $value )
		{
			if ( isset( $toSave[ $key ] ) )
			{
				if ( $toSave[ $key ] == 1 )
				{
					$int += $value;
				}
			}
		}
		
		return intval( $int );
	}

	/**
	 * Fetch and build the bitwise array
	 *
	 * @access	private
	 * @param	string		Array key to return
	 * @return	array
	 */
	static private function _getBitWiseArray( $type, $app )
	{
		$bitArray   = array();
		$allOptions = ipsRegistry::fetchBitWiseOptions( $app );
		
		if ( is_array( $allOptions ) )
		{
			if ( isset( $allOptions[ $type ] ) AND is_array( $allOptions[ $type ] ) )
			{
				$n = 1;
				
				foreach( $allOptions[ $type ] as $key )
				{
					$bitArray[ $key ] = $n;
					
					$n *= 2;
				}
			}
		}
		
		return $bitArray;
	}
}

/**
 * Time Class
 *
 * Class for handling timestamps
 *
 */
class IPSTime
{
   /**
    * Current timestamp
    *
    * @access	private
    * @var		integer
    */
	private static $timestamp	= IPS_UNIX_TIME_NOW;

   /**
    * Number of seconds in a minute
    *
    * @access	private
    * @var		integer
    */
	private static $minute		= 60;

   /**
    * Number of seconds in a hour
    *
    * @access	private
    * @var		integer
    */
	private static $hour		= 3600;

   /**
    * Number of seconds in a day
    *
    * @access	private
    * @var		integer
    */
	private static $day			= 86400;

   /**
    * Number of seconds in a week
    *
    * @access	private
    * @var		integer
    */
	private static $week		= 604800;

   /**
    * Number of seconds in a year
    *
    * @access	private
    * @var		integer
    */
	private static $year		= 220752000;

   /**
    * Months with 31 days
    *
    * @access	private
    * @var		array
    */
	private static $months_31 = array( 01, 03, 05, 07, 08, 10, 12 );
	
   /**
    * Months with 30 days
    *
    * @access	private
    * @var		array
    */
	private static $months_30 = array( 04, 06, 09, 11 );

	/**
	 * time_class::dmy_format()
	 * Generates a time stamp for the day/month/year
	 *
	 * @access	public
	 * @param	integer	[$ts]	Timestamp to format, self::$timestamp used if none specified
	 * @return	void
	 **/
	static public function dmy_format( $ts=0 )
	{
		/* Set the timestamp */
		$_ts = ( $ts ) ? $ts : self::$timestamp;

		/* Break it into dmy format */
		$_ts = date( "m,d,Y", $_ts );
		$_ts = explode( ",", $_ts );

		/* Return the timestamp */
		return mktime( 0, 0, 0, $_ts[0], $_ts[1], $_ts[2] );
	}

	/**
	 * time_class::time_ago()
	 * Returns how long ago the specified time stamp was
	 *
	 * @access	public
	 * @param	integer	$ts	Timestamp to format
	 * @return	void
	 **/
	static public function time_ago( $ts )
	{
		if( $ts == time() )
		{
			return '--';
		}
		if( $ts < 60 )
		{
			$plural = ( $ts == 1 ) ? '' : 's';
			return sprintf( "%0d", $ts ) . " second$plural";
		}
		else if( $ts < self::$hour )
		{
			$plural = ( sprintf("%0d", ( $ts / self::$minute ) ) == 1 ) ? '' : 's';
			return sprintf("%0d", ( $ts / self::$minute ) ) . " minute$plural";
		}
		else if( $ts < self::$day )
		{
			$plural = ( sprintf("%0d", ( $ts / self::$hour ) ) == 1 ) ? '' : 's';
			return sprintf("%0d", ( $ts / self::$hour ) ) . " hour$plural";
		}
		else
		{
			$plural = ( sprintf("%0d", ( $ts / self::$day ) ) == 1 ) ? '' : 's';
			return sprintf("%0d", ( $ts / self::$day ) ) . " day$plural";
		}
	}

	/**
	 * Set the timestamp
	 *
	 * @access	public
	 * @param	int		New timestamp
	 * @return	void
	 */
	static public function setTimestamp( $time )
	{
		self::$timestamp = $time;
	}
	
	/**
	 * Get the timestamp
	 *
	 * @access	public
	 * @return	int		Timestamp
	 */
	static public function getTimestamp()
	{
		return self::$timestamp;
	}
	
	/**
	 * time_class::add_minutes()
	 * Adds the specified number of minutes to the timestamp
	 *
	 * @access	public
	 * @param	integer	[$num]	Number of minutes to add, 1 by default
	 * @return	void
	 **/
	static public function add_minutes( $num=1 )
	{
		self::$timestamp += self::$minute * $num;
	}

	/**
	 * time_class::add_hours()
	 * Adds the specified number of hours to the timestamp
	 *
	 * @access	public
	 * @param	integer	[$num]	Number of hours to add, 1 by default
	 * @return	void
	 **/
	static public function add_hours( $num=1 )
	{
		self::$timestamp += self::$hours * $num;
	}

	/**
	 * time_class::add_days()
	 * Adds the specified number of days to the timestamp
	 *
	 * @access	public
	 * @param	integer	[$num]	Number of days to add, 1 by default
	 * @return	void
	 **/
	static public function add_days( $num=1 )
	{
		self::$timestamp += self::$day * $num;
	}

	/**
	 * time_class::add_weeks()
	 * Adds the specified number of weeks to the timestamp
	 *
	 * @access	public
	 * @param	integer	[$num]	Number of weeks to add, 1 by default
	 * @return	void
	 **/
	static public function add_weeks( $num=1 )
	{
		self::$timestamp += self::$week * $num;
	}

	/**
	 * time_class::add_month()
	 * Adds a single month to the current timestamp, takes into account leap years
	 *
	 * @access	public
	 * @return	void
	 **/
	static public function add_month()
	{
		$daysInMonth = date( 't', self::$timestamp );
		
		self::$timestamp += self::add_days( intval( $daysInMonth ) );
	}

	/**
	 * time_class::add_months()
	 * Adds the specified number of months to the timestamp
	 *
	 * @access	public
	 * @param	integer	$num	Number of months to add, 1 by default
	 * @return	void
	 **/
	static public function add_months( $num=1 )
	{
		for( $i = 0; $i < $num; $i++ )
		{
			self::add_month();
		}
	}

	/**
	 * time_class::add_years()
	 * Adds the specified number of years to the timestamp
	 *
	 * @access	public
	 * @param	integer	$num	Number of years to add, 1 by default
	 * @return	void
	 **/
	static public function add_years( $num=1 )
	{
		for( $i = 0; $i < $num; $i++ )
		{
			self::add_months( 12 );
			self::remove_days( 1 );
		}
	}

	/**
	 * time_class::remove_days()
	 * Removes the specified number of days to the timestamp
	 *
	 * @access	public
	 * @param	integer	[$num]	Number of days to remove, 1 by default
	 * @return	void
	 **/
	static public function remove_days( $num=1 )
	{
		self::$timestamp -= self::$day * $num;
	}

	/**
	 * Convert unix timestamp into: (no leading zeros)
	 * array( 'day' => x, 'month' => x, 'year' => x, 'hour' => x, 'minute' => x );
	 * Written into separate static public function to allow for timezone to be used easily
	 *
	 * @access	public
	 * @param	integer	[$unix]	Timestamp
	 * @return	array 	Date parts
	 **/
    static public function unixstamp_to_human( $unix=0 )
    {
    	$tmp = gmdate( 'j,n,Y,G,i', $unix );

    	list( $day, $month, $year, $hour, $min ) = explode( ',', $tmp );

    	return array( 'day'    => $day,
    				  'month'  => $month,
    				  'year'   => $year,
    				  'hour'   => $hour,
    				  'minute' => $min );
    }

	/**
	 * Convert unix timestamp into mmddyyyy
	 *
	 * @access	public
	 * @param	integer	[$unix]	Timestamp
	 * @param	string	[$sep]	Separator
	 * @return	string	mm/dd/yyyy
	 **/
    static public function unixstamp_to_mmddyyyy( $unix=0, $sep='/' )
    {
    	if ( ! $unix )
    	{
    		return "";
    	}

    	$date = self::unixstamp_to_human( $unix );

    	return sprintf("%02d{$sep}%02d{$sep}%04d", $date['month'], $date['day'], $date['year'] );
    }

	/**
	 * Convert mmddyyyy into unix timestamp
	 *
	 * @access	public
	 * @param	string	[$date]			Date
	 * @param	string	[$sep]			Separator
	 * @param	bool	[$checkdate]	Whether to validate date or not
	 * @return	integer	Timestamp
	 **/
    static public function mmddyyyy_to_unixstamp( $date='', $sep='/', $checkdate=true )
    {
    	if ( ! $date )
    	{
    		return "";
    	}

    	list( $month, $day, $year ) = explode( $sep, $date );

    	if ( $checkdate )
    	{
			if ( ! checkdate( $month, $day, $year ) )
			{
				return "";
			}
    	}

    	return self::human_to_unixstamp( $day, $month, $year, 0, 0 );
    }

	/**
	 * Wrapper for gmmktime (separated for timezone management)
	 *
	 * @access	public
	 * @param	integer	$day	Day
	 * @param	integer	$month	Month
	 * @param	integer $year	Year
	 * @param	integer	$hour	Hour
	 * @param	integer	$minute	Minute
	 * @return	integer	Timestamp
	 **/
    static public function human_to_unixstamp( $day, $month, $year, $hour, $minute )
    {
    	return gmmktime( intval($hour), intval($minute), 0, intval($month), intval($day), intval($year) );
    }

    /**
	 * My gmmktime() - PHP func seems buggy
	 *
	 * @access	public
	 * @param	integer	$hour	Hour
	 * @param	integer	$min	Minute
	 * @param	integer $sec	Second
	 * @param	integer $month	Month
	 * @param	integer $day	Day
	 * @param	integer $year	Year
	 * @return	integer	Timestamp
	 * @since	2.0
	 */
	static public function date_gmmktime( $hour=0, $min=0, $sec=0, $month=0, $day=0, $year=0 )
	{
		// Calculate UTC time offset
		$offset = date( 'Z' );

		// Generate server based timestamp
		$time   = mktime( $hour, $min, $sec, $month, $day, $year );

		// Calculate DST on / off
		$dst    = intval( date( 'I', $time ) - date( 'I' ) );

		return $offset + ($dst * 3600) + $time;
	}

    /**
	 * Hand rolled GETDATE method
	 *
	 * getdate doesn't work apparently as it doesn't take into account
	 * the offset, even when fed a GMT timestamp.
	 *
	 * @access	public
	 * @param	integer	Unix date
	 * @return	array	0, seconds, minutes, hours, mday, wday, mon, year, yday, weekday, month
	 * @since	2.0
	 */
    static public function date_getgmdate( $gmt_stamp )
    {
    	//$tmp = gmdate( 'j,n,Y,G,i,s,w,z,l,F,W,M', $gmt_stamp );
    	$format	= '%e,%m,%Y,%H,%M,%S,%u,%j,%A,%B,%W,%b';
    	
    	//-----------------------------------------
    	// Some flags not available on Windows
    	// @see http://www.php.net/manual/en/function.strftime.php#53340
    	//-----------------------------------------
    	
    	if( strpos( strtolower( PHP_OS ), 'win' ) === 0 )
    	{
    		$mapping = array(
    						'%e'	=> sprintf("%' 2d", date("j", $gmt_stamp)),
    						'%u'	=> ($w = date("w", $gmt_stamp)) ? $w : 7,
    						);

			$format = str_replace( array_keys($mapping), array_values($mapping), $format );
    	}
    	
		$tmp = gmstrftime( $format, $gmt_stamp );

    	list( $day, $month, $year, $hour, $min, $seconds, $wday, $yday, $weekday, $fmon, $week, $smon ) = explode( ',', $tmp );

    	return array(  0         => $gmt_stamp,
    				   "seconds" => $seconds, //	Numeric representation of seconds	0 to 59
					   "minutes" => $min,     //	Numeric representation of minutes	0 to 59
					   "hours"	 => $hour,	  //	Numeric representation of hours	0 to 23
					   "mday"	 => trim($day),     //	Numeric representation of the day of the month	1 to 31
					   "wday"	 => $wday,    //    Numeric representation of the day of the week	0 (for Sunday) through 6 (for Saturday)
					   "mon"	 => $month,   //    Numeric representation of a month	1 through 12
					   "year"	 => $year,    //    A full numeric representation of a year, 4 digits	Examples: 1999 or 2003
					   "yday"	 => $yday,    //    Numeric representation of the day of the year	0 through 365
					   "weekday" => $weekday, //	A full textual representation of the day of the week	Sunday through Saturday
					   "month"	 => $fmon,    //    A full textual representation of a month, such as January or Mar
					   "week"    => $week,    //    Week of the year
					   "smonth"  => $smon,
					   "smon"    => $smon
					);
    }
}

/**
* IPSLib
*
* Dumping ground for functions that don't fit anywhere else
*
*/
class IPSLib
{
	/**
	 * FURL Templates
	 *
	 * @access	private
	 * @param	array
	 */
	static private $_furlTemplates = array();
	
	/**
	 * Search configs
	 *
	 * @access	private
	 * @param	array
	 */
	static private $_searchConfigs = array();
	
	/**
	 * Log in methods
	 *
	 * @access	private
	 * @param	array
	 */
	static private $_lims 		   = array();
	
	/**
	 * Returns the class name to be instantiated, the class file will already be included 
	 *
	 * @access	public
	 * @param	string 	$filePath		File location of the class
	 * @param	string	$className		Name of the class
	 * @param	string	$app			Application (defaults to 'core')
	 * @return	string	Class Name
	 */
	static public function loadLibrary( $filePath, $className, $app='core' )
	{
		/* Get the class */
		require_once( $filePath );
		
		/* Check for hooks */
		$hooksCache	= ipsRegistry::cache()->getCache('hooks');

		if( isset( $hooksCache['libraryHooks'][ $app ][ $className ] ) AND is_array( $hooksCache['libraryHooks'][ $app ][ $className ] ) AND count( $hooksCache['libraryHooks'][ $app ][ $className ] ) )
		{
			foreach( $hooksCache['libraryHooks'][ $app ][ $className ] as $classOverloader )
			{
				/* Hooks: Do we have a hook that extends this class? */
				if( file_exists( DOC_IPS_ROOT_PATH . 'hooks/' . $classOverloader['filename'] ) )
				{
					/* Hooks: Do we have the hook file? */
					require_once( DOC_IPS_ROOT_PATH . 'hooks/' . $classOverloader['filename'] );
	            
					if( class_exists( $classOverloader['className'] ) )
					{
						/* Hooks: We have the hook file and the class exists - reset the classname to load */
						$className = $classOverloader['className'];
					}
				}
			}
		}
		
		/* Return Class Name */
		return $className;
	}
	
	/**
	 * Checks if there are any data hooks to run
	 *
	 * @access	public
	 * @param	array 	$dataArray		Data to be passed into the hooks
	 * @param	string	$hookLocation	Location the data was sent from
	 * @return	void
	 */
	static public function doDataHooks( $dataArray, $hookLocation )
	{
    	/* Loop through the cache */
    	$hooksCache = ipsRegistry::cache()->getCache( 'hooks' );

		if( is_array( $hooksCache['dataHooks'][ $hookLocation ] ) AND count( $hooksCache['dataHooks'][ $hookLocation ] ) )
		{
			foreach( $hooksCache['dataHooks'][ $hookLocation ] as $r )
			{
				/* Check for hook file */
				if( file_exists( DOC_IPS_ROOT_PATH . 'hooks/' . $r['filename'] ) )
				{
					/* Check for hook class */
					require_once( DOC_IPS_ROOT_PATH . 'hooks/' . $r['filename'] );
					
					if( class_exists( $r['className'] ) )
					{
						/* Create and run the hook */
						$_hook		= new $r['className'];
						$newArray	= $_hook->handleData( $dataArray );
						
						/* Make sure the array isn't wiped out */
						if( is_array( $newArray ) && count( $newArray ) )
						{
							$dataArray = $newArray;
						}
					}
				}
			}
		}
	}
	
	/**
	 * Returns an array of data hook locations
	 *
	 * @access	public
	 * @return	array
	 */
	static public function getDataHookLocations()
	{
		$_locations = array();
		
		/* Loop all apps and get back our locations! */
		foreach( ipsRegistry::$applications as $app_dir => $application )
		{
			$dataHookLocations	= array();

			if( file_exists( IPSLib::getAppDir( $app_dir ) . '/extensions/dataHookLocations.php' ) )
			{
				require_once( IPSLib::getAppDir( $app_dir ) . '/extensions/dataHookLocations.php' );
				
				if ( count($dataHookLocations) )
				{
					$_locations = array_merge( $_locations, $dataHookLocations );
				}
			}
		}
		
		return $_locations;
	}
	
	/**
	 * Checks to see if there is a template hook installed at the specified location
	 *
	 * @access	public
	 *
	 * @param	string	$group
	 * @param	array	$id
	 * @return	bool
	 */
	static public function locationHasHooks( $group, $ids )
	{
		/* Return right away if we don't have an ids to check */
		if( ! is_array( $ids ) || ! count( $ids ) )
		{
			return false;
		}

		/* Reformat the cache on the first call, to save processing later */
		static $formattedCache	= array();

		if( !count($formattedCache) )
		{
			$hookCache = ipsRegistry::cache()->getCache( 'hooks' );

			foreach( $hookCache['templateHooks'] as $_group => $_hook )
			{
				foreach( $_hook as $hook )
				{
					$formattedCache[ $_group ][] = $hook['id'];
				}
			}
		}

		/* Use formatted cache to check */
		if( isset( $formattedCache[ $group ] ) && is_array( $formattedCache[ $group ] ) )
		{
			foreach( $ids as $id )
			{
				if( in_array( $id, $formattedCache[ $group ] ) )
				{
					return true;
				}
			}
		}

		return false;
	}
	
	/**
	 * Central setlocale method so we can adjust as needed
	 *
	 * @access	public
	 * @param	string		Locale to set
	 * @return	void
	 * @link	http://community.invisionpower.com/tracker/issue-16386-language-locale-gives-error/
	 * @link	http://community.invisionpower.com/tracker/issue-18424-change-lang-locale/
	 */
	static public function setlocale( $locale='' )
	{
		if( !$locale )
		{
			return;
		}
		
		if ( stripos( $locale, 'tr_' ) !== FALSE )
		{
			setlocale( LC_COLLATE, $locale );
			setlocale( LC_MONETARY, $locale );
			setlocale( LC_NUMERIC, $locale );
			setlocale( LC_TIME, $locale );
			setlocale( LC_MESSAGES, $locale );
		}
		else
		{
			setlocale( LC_ALL, $locale );
		}
	}
	
	/**
	 * Quickly determines if we've got FB enabled and set up
	 *
	 * @access	public
	 * @return	boolean
	 */
	static public function fbc_enabled()
	{
		return ( ipsRegistry::$settings['fbc_enable'] AND ipsRegistry::$settings['fbc_api_id'] AND ipsRegistry::$settings['fbc_secret'] ) ? TRUE : FALSE;
	}
	
	/**
	 * Quickly determines if we've got twitter enabled and set up
	 *
	 * @access	public
	 * @return	boolean
	 */
	static public function twitter_enabled()
	{
		return ( ipsRegistry::$settings['tc_enabled'] AND ipsRegistry::$settings['tc_token'] AND ipsRegistry::$settings['tc_secret'] ) ? TRUE : FALSE;
	}
	
	/**
	 * Quickly determines if we've got other log in enabled and set up
	 *
	 * @access	public
	 * @return	boolean
	 */
	static public function loginMethod_enabled( $method )
	{
		if ( ! count( self::$_lims ) )
		{
			if ( is_array( ipsRegistry::cache()->getCache('login_methods') ) )
			{
				$cache = ipsRegistry::cache()->getCache('login_methods');
				
				foreach( $cache as $lim )
				{
					self::$_lims[ $lim['login_folder_name'] ] = $lim['login_folder_name'];
				}
			}
		}
		
		switch( $method )
		{
			case 'facebook':
				return self::fbc_enabled();
			break;
			case 'twitter':
				return self::twitter_enabled();
			break;
			default:
					return in_array( $method, self::$_lims ) ? true : false;
			break;
		}
	}
	
	/**
	 * Unpack group bitwise options
	 *
	 * @access	public
	 * @param	array
	 * @param	bool		Do not warn on overwrite
	 * @return  array
	 */
	static public function unpackGroup( $group, $silence=false )
	{
		/* Unpack bitwise fields */
		$_tmp = IPSBWOptions::thaw( $group['g_bitoptions'], 'groups', 'global' );

		if ( count( $_tmp ) )
		{
			foreach( $_tmp as $k => $v )
			{
				/* Trigger notice if we have DB field */
				if ( $silence === false AND isset( $group[ $k ] ) )
				{
					trigger_error( "Thawing bitwise options for GROUPS: Bitwise field '$k' has overwritten DB field '$k'", E_USER_WARNING );
				}

				$group[ $k ] = $v;
			}
		}
		
		return $group;
	}
	
	/**
	 * Little function to return the version number data
	 *
	 * Handy to use when dealing with IN_DEV, etc
	 * Uses the constant where available
	 *
	 * @access	public
	 * @param	string  App ( Default 'core')
	 * @return	array  array( 'long' => x, 'human' => x )
	 */
	static public function fetchVersionNumber( $app='core' )
	{
		if ( ! defined( IPB_VERSION ) OR ! defined( IPB_LONG_VERSION ) )
		{
			require_once( IPS_ROOT_PATH . 'setup/sources/base/setup.php' );
			
			$XMLVersions = IPSSetUp::fetchXmlAppVersions( $app );
			$tmp         = $XMLVersions;
			krsort( $tmp );

			foreach( $tmp as $long => $human )
			{
				$return = array( 'long' => $long, 'human' => $human );
				break;
			}
		}
		else
		{
			$return = array( 'long' => IPB_LONG_VERSION, 'human' => IPB_VERSION );
		}
		
		return $return;
	}
	
	/**
	 * Cheeky little function to locate group table fields from other apps
	 *
	 * @access public
	 * @return array 	Array of fields from different apps
	 */
	static function fetchNonDefaultGroupFields()
	{
		$fields = array();
		
		foreach( array( 'gallery', 'blog', 'downloads', 'ccs', 'ipchat', 'nexus', 'subscriptions' ) as $app )
		{
			$_file = IPSLib::getAppDir( $app ) . '/setup/versions/install/sql/' . $app . '_mysql_tables.php';
			
			if ( file_exists( $_file ) )
			{
				require( $_file );
				
				foreach( $TABLE as $t )
				{
					if ( preg_match( "#^ALTER TABLE\s+?groups\s+?ADD\s+?(\S+?)\s#i", $t, $match ) )
					{
						$fields[] = $match[1];
					}
				}
			}
		}
		
		return $fields;
	}
	
	/**
	 * Update settings
	 *
	 * @param	array		array('conf_key' => 'new value')
	 * @return	true/false
	 * @author	MarkWade
	 * @access	public
	 */
	static public function updateSettings($update=array())
	{
		$fields = array_keys($update);
		ipsRegistry::DB()->build( array( 'select' => '*', 'from' => 'core_sys_conf_settings', 'where' => "conf_key IN ('" . implode( "','", $fields ) . "')" ) );
		ipsRegistry::DB()->execute();
		
		$db_fields = array();
		while ( $r = ipsRegistry::DB()->fetch() )
		{
			$db_fields[ $r['conf_key']  ] = $r;
		}
		
		if (empty($db_fields))
		{
			return false;
		}
		
		foreach( $db_fields as $key => $data )
		{
			$value = str_replace( "&#39;", "'", IPSText::safeslashes($update[ $key ]) );
			$value = $value == '' ? "{blank}" : $value;

			ipsRegistry::DB()->update( 'core_sys_conf_settings', array( 'conf_value' => $value ), 'conf_id=' . $data['conf_id'] );
		}
		
		ipsRegistry::DB()->build( array( 'select' => '*', 'from' => 'core_sys_conf_settings', 'where' => 'conf_add_cache=1' ) );
		$info = ipsRegistry::DB()->execute();
	
		while ( $r = ipsRegistry::DB()->fetch($info) )
		{	
			$value = $r['conf_value'] != "" ?  $r['conf_value'] : $r['conf_default'];
			
			if ( $value == '{blank}' )
			{
				$value = '';
			}

			$settings[ $r['conf_key'] ] = $value;
		}
		
		ipsRegistry::cache()->setCache( 'settings', $settings, array( 'array' => 1 ) );
		
		return true;
	}
	
	/**
	 * Retrieve the default language
	 *
	 * @access	public
	 * @return	string		Default language id (most likely a number)
	 */
	static public function getDefaultLanguage()
	{
		$cache	= ipsRegistry::cache()->getCache('lang_data');
		
		if( !count($cache) OR !is_array($cache) )
		{
			ipsRegistry::getClass('class_localization')->rebuildLanguagesCache();
			
			$cache	= ipsRegistry::cache()->getCache('lang_data');
		}
		
		$_default	= 1;
		
		foreach( $cache as $_lang )
		{
			if( $_lang['lang_default'] )
			{
				$_default	= $_lang['lang_id'];
				break;
			}
		}
		
		return $_default;
	}
	
	/**
	 * Build furl templates from FURL extensions
	 *
	 * @access	public
	 * @return  array
	 */
	static public function buildFurlTemplates()
	{
		/* INIT */
		$apps = array();
		
		/* Done this already? */
		if ( self::$_furlTemplates )
		{
			return self::$_furlTemplates;
		}
		
		/* Because this is called before the cache is unpacked, we need to expensively grab all app dirs */
		foreach( array( 'applications', 'applications_addon/ips', 'applications_addon/other' ) as $folder )
		{
			try
			{
				foreach( new DirectoryIterator( IPS_ROOT_PATH . $folder ) as $file )
				{
					if ( ! $file->isDot() AND $file->isDir() )
					{
						$_name = $file->getFileName();
						
						if ( substr( $_name, 0, 1 ) != '.' )
						{
							$apps[ $folder . '/' . $_name ] = $_name;
						}
					}
				}
			} catch ( Exception $e ) {}
		}
		
		/* First, add in core stuffs */
		ipsRegistry::_loadCoreVariables();
		$templates = ipsRegistry::_fetchCoreVariables('templates');
		
		if ( is_array( $templates ) )
		{
			foreach( $templates as $key => $data )
			{
				self::$_furlTemplates[ $key ] = $data;
			}
		}
		
		/* Loop over the applications and build */
		foreach( $apps as $path => $app_dir )
		{
			$appSphinxTemplate	= '';

			if ( file_exists( IPS_ROOT_PATH . $path . '/extensions/furlTemplates.php' ) )
			{
				unset( $_SEOTEMPLATES );
				
				require( IPS_ROOT_PATH . $path . '/extensions/furlTemplates.php' );
				
				if ( is_array( $_SEOTEMPLATES ) )
				{
					foreach( $_SEOTEMPLATES as $key => $data )
					{
						self::$_furlTemplates[ $key ] = $data;
					}
				}
			}
		}
		
		/* Return for anyone else */
		return self::$_furlTemplates;
	}
	
	/**
	 * Cache templates from FURL extensions
	 *
	 * @access	public
	 * @return  boolean
	 * @exceptions
	 * CANNOT_WRITE		Cannot write to cache file
	 * NO_DATA_TO_WRITE	No data to write
	 */
	static public function cacheFurlTemplates()
	{
		if ( ! count( self::$_furlTemplates ) )
		{
			self::buildFurlTemplates();
		}

		if ( count( self::$_furlTemplates ) )
		{
			$_date = gmdate( 'r', time() );
			$_var  = var_export( self::$_furlTemplates, TRUE );
			$data  = <<<EOF
<?php
/**
 * FURL Templates cache. Do not attempt to modify this file.
 * Please modify the relevant 'furlTemplates.php' file in /{app}/extensions/furlTemplates.php
 * and rebuild from the Admin CP
 *
 * Written: {$_date}
 *
 * Why? Because Matt says so.
 */
 \$templates = {$_var};

?>
EOF;
		
			if ( ! @file_put_contents( DOC_IPS_ROOT_PATH . 'cache/furlCache.php', $data ) )
			{
				throw new Exception( 'CANNOT_WRITE' );
			}
		}
		else
		{
			throw new Exception( 'NO_DATA_TO_WRITE' );
		}
		
		return TRUE;
	}
	
	/**
	 * Rebuild the Sphinx conf
	 *
	 * @access	public
	 * @return	string		Null, or the new sphinx config
	 */
	static public function rebuildSphinxConfig()
	{
		//-----------------------------------------
		// Init some sphinx vars
		//-----------------------------------------

		$sphinxTemplate	= '';
		$sphinxCompiled	= '';
		
		//-----------------------------------------
		// Got the template file?
		//-----------------------------------------
		
		if( !file_exists( IPS_ROOT_PATH . '/extensions/sphinxTemplate.php' ) )
		{
			return null;
		}

		require_once( IPS_ROOT_PATH . '/extensions/sphinxTemplate.php' );
		
		//-----------------------------------------
		// Does the template exist?
		//-----------------------------------------
		
		if( !$sphinxTemplate )
		{
			return null;
		}
		
		//-----------------------------------------
		// Replace out the SQL details
		//-----------------------------------------
		
		$sphinxTemplate	= str_replace( "<!--SPHINX_CONF_HOST-->"	, ipsRegistry::$settings['sql_host']	, $sphinxTemplate );
		$sphinxTemplate	= str_replace( "<!--SPHINX_CONF_USER-->"	, ipsRegistry::$settings['sql_user']	, $sphinxTemplate );
		$sphinxTemplate	= str_replace( "<!--SPHINX_CONF_PASS-->"	, ipsRegistry::$settings['sql_pass']	, $sphinxTemplate );
		$sphinxTemplate	= str_replace( "<!--SPHINX_CONF_DATABASE-->", ipsRegistry::$settings['sql_database'], $sphinxTemplate );
		$sphinxTemplate	= str_replace( "<!--SPHINX_CONF_PORT-->"	, ipsRegistry::$settings['sql_port'] ? ipsRegistry::$settings['sql_port'] : 3306, $sphinxTemplate );
		
		//-----------------------------------------
		// Loop over the applications and build
		//-----------------------------------------
		
		foreach( ipsRegistry::$applications as $app_dir => $application )
		{
			$appSphinxTemplate	= '';

			if( file_exists( IPSLib::getAppDir( $app_dir ) . '/extensions/sphinxTemplate.php' ) )
			{
				require_once( IPSLib::getAppDir( $app_dir ) . '/extensions/sphinxTemplate.php' );
				
				$sphinxCompiled .= $appSphinxTemplate;
			}
		}
		
		//-----------------------------------------
		// Replace DB prefix
		//-----------------------------------------
		
		$sphinxCompiled	= str_replace( "<!--SPHINX_DB_PREFIX-->"	, ipsRegistry::$settings['sql_tbl_prefix'], $sphinxCompiled );

		//-----------------------------------------
		// And replace out the content with the compilation
		//-----------------------------------------
		
		$sphinxTemplate	= str_replace( "<!--SPHINX_CONTENT-->", $sphinxCompiled, $sphinxTemplate );
		
		//-----------------------------------------
		// Replace out the /var/sphinx/ path
		//-----------------------------------------
		
		$sphinxTemplate	= str_replace( "<!--SPHINX_BASE_PATH-->", rtrim( ipsRegistry::$settings['sphinx_base_path'], '/' ), $sphinxTemplate );
		
		$sphinxTemplate = str_replace( "<!--SPHINX_PORT-->", ipsRegistry::$settings['search_sphinx_port'], $sphinxTemplate );
		
		//-----------------------------------------
		// Wildcard support on?
		//-----------------------------------------
		
		if ( ipsRegistry::$settings['sphinx_wildcard'] )
		{
			$sphinxTemplate = str_replace( '#infix_fields' , 'infix_fields', $sphinxTemplate );
			$sphinxTemplate = str_replace( '#min_infix_len', 'min_infix_len', $sphinxTemplate );
			$sphinxTemplate = str_replace( '#enable_star'  , 'enable_star', $sphinxTemplate );
		}
		
		//-----------------------------------------
		// Return the new content
		//-----------------------------------------
		
		return $sphinxTemplate;
		
	}
	
	/**
	 * Recursively cleans keys and values and
	 * inserts them into the input array
	 *
	 * @access	public
	 * @param	mixed		Input data
	 * @param	array		Storage array for cleaned data
	 * @param	integer		Current iteration
	 * @return	array 		Cleaned data
	 */
	static public function parseIncomingRecursively( &$data, $input=array(), $iteration = 0 )
	{
		// Crafty hacker could send something like &foo[][][][][][]....to kill Apache process
		// We should never have an input array deeper than 20..

		if ( $iteration >= 20 )
		{
			return $input;
		}

		foreach( $data as $k => $v )
		{
			if ( is_array( $v ) )
			{
				$input[ $k ] = self::parseIncomingRecursively( $data[ $k ], array(), $iteration + 1 );
			}
			else
			{
				$k = IPSText::parseCleanKey( $k );
				$v = IPSText::parseCleanValue( $v, false );

				$input[ $k ] = $v;
			}
		}

		return $input;
	}

	/**
	 * Recursively cleans values after settings have been loaded.
	 * Necessary for certain functions (such as whether to strip space chars or not)
	 *
	 * @access	public
	 * @param	mixed		Input data
	 * @param	integer		Current iteration
	 * @return	array 		Cleaned data
	 */
	static public function postParseIncomingRecursively( $request, $iteration = 0 )
	{
		// Crafty hacker could send something like &foo[][][][][][]....to kill Apache process
		// We should never have an input array deeper than 20..

		if ( $iteration >= 20 OR !is_array($request) )
		{
			return $request;
		}

		foreach( $request as $k => $v )
		{
			if ( is_array( $v ) )
			{
				$request[ $k ] = self::postParseIncomingRecursively( $v, ++$iteration );
			}
			else
			{
				$v = IPSText::postParseCleanValue( $v );

				$request[ $k ] = $v;
			}
		}

		return $request;
	}
	
	/**
	 * Performs basic cleaning, Null characters, etc
	 *
	 * @access	public
	 * @param	array 	Input data
	 * @return	array 	Cleaned data
	 */
	static public function cleanGlobals( &$data, $iteration = 0 )
	{
		// Crafty hacker could send something like &foo[][][][][][]....to kill Apache process
		// We should never have an input array deeper than 10..

		if ( $iteration >= 10 )
		{
			return;
		}
				
		foreach( $data as $k => $v )
		{
			if ( is_array( $v ) )
			{
				self::cleanGlobals( $data[ $k ], ++$iteration );
			}
			else
			{
				# Null byte characters
				$v = str_replace( chr('0') , '', $v );
				$v = str_replace( "\0"    , '', $v );
				$v = str_replace( "\x00"  , '', $v );

				// @link	http://community.invisionpower.com/tracker/issue-21188-post-processor-eating-characters/
				//$v = str_replace( '%00'   , '', $v );

				# File traversal
				$v = str_replace( "../", "&#46;&#46;/", $v );
				
				/* RTL override */
				$v = str_replace( '&#8238;', '', $v );
				
				$data[ $k ] = $v;
			}
		}
	}
	
	/**
	 * Determines if member is viewing images
	 * If not, unparses smilies
	 *
	 * @access	public
	 * @param 	string			Raw input text to parse
	 * @return	string			Parsed text ready to be stored in database
	 */
	public static function memberViewImages( $text )
	{
		//-----------------------------------------
		// Parse
		//-----------------------------------------
		
		if ( ! ipsRegistry::member()->getProperty('view_img') )
		{
			//-----------------------------------------
			// Second regex needed for content caching
			//-----------------------------------------
			
			$text	= self::unconvertSmilies( $text );
			$text	= preg_replace( "/<img src=['\"](.+?)[\"'](?:[^>]+?)class=['\"]bbc_img[\"'](?:[^\/>]+?)\/>/", "\\1", $text );
			
			return $text;
		}
		else
		{
			return $text;
		}
	}

	/**
	 * Unconvert smilies
	 *
	 * @access	public
	 * @param	string		Raw text
	 * @return	string		Converted text
	 */
	public static function unconvertSmilies( $txt )
	{
		//-----------------------------------------
		// Unconvert smilies
		//-----------------------------------------

		$txt = str_replace( "<#EMO_DIR#>", "&lt;#EMO_DIR&gt;", $txt );

		preg_match_all( "#(<img(?:[^>]+?)class=['\"]bbc_emoticon[\"'](?:[^>]+?)alt=['\"](.+?)[\"'](?:[^>]+?)?>)#is", $txt, $matches );

		if( is_array($matches[1]) AND count($matches[1]) )
		{
			foreach( $matches[1] as $index => $value )
			{				
				if ( count( ipsRegistry::cache()->getCache('emoticons') ) > 0 )
				{
					foreach( ipsRegistry::cache()->getCache('emoticons') as $row )
					{
						$_emoCode = str_replace( '<', '&lt;', str_replace( '>', '&gt;', $row['typed'] ) );
						
						if( $matches[2][ $index ] == $_emoCode )
						{
							/* We need to make sure emoticons are wrapped in spaces so they are parsed properly */
							//$txt = str_replace( $value, ' ' . $_emoCode . ' ', $txt );
							/* We are no longer matching opening/closing "space" so no need to add it */
							$txt = str_replace( $value, $_emoCode, $txt );
							continue 2;
						}
					}
				}
			}
		}

		$txt = str_replace( "&lt;#EMO_DIR&gt;", "<#EMO_DIR#>", $txt );
		
		return $txt;
	}
	
	/**
	 * Fetch emoticons as JSON for editors, etc
	 *
	 * @access	public
	 * @param	string		Directory for emos [optional]
	 * @param	bool		Include emoticons not marked clickable
	 * @return	string		JSON
	 */
	static public function fetchEmoticonsAsJson( $emoDir='', $nonClickable=false )
	{
		$emoDir    = ( $emoDir ) ? $emoDir : ipsRegistry::getClass('output')->skin['set_emo_dir'];
		$emoArray  = array();
		$emoString = '';
		$smilie_id = 0;

		foreach( ipsRegistry::cache()->getCache('emoticons') as $elmo )
		{
			if ( $elmo['emo_set'] != $emoDir )
			{
				continue;
			}
			
			if ( ! $elmo['clickable'] AND !$nonClickable )
			{
				continue;
			}

			$smilie_id++;
			
			//-----------------------------------------
			// Make single quotes as URL's with html entites in them
			// are parsed by the browser, so ' causes JS error :o
			//-----------------------------------------
			
			if ( strstr( $elmo['typed'], "&#39;" ) )
			{
				$in_delim  = '"';
			}
			else
			{
				$in_delim  = "'";
			}
			
			$emoArray[] = $in_delim . addslashes($elmo['typed']) . $in_delim . ' : "' . $smilie_id . ','.$elmo['image'].'"';
		
		}
		
		//-----------------------------------------
		// Finish up smilies...
		//-----------------------------------------
		
		if ( count( $emoArray ) )
		{
			$emoString = implode( ",\n", $emoArray );
		}
		
		return $emoString;
	}
	
	/**
	 * Fetch bbcode as JSON for editors, etc
	 *
	 * @access	public
	 * @return	string		JSON
	 */
	static public function fetchBbcodeAsJson()
	{
		$bbcodes			= array();
		$protectedBbcodes	= array(
									'right', 'left', 'center', 'b', 'i', 'u', 'url', 'img', 'quote', 'indent',
									'list', 'strike', 'sub', 'sup', 'email', 'background', 'color', 'size', 'font', 'media'
									);

		foreach( ipsRegistry::cache()->getCache('bbcode') as $bbcode )
		{
			if( in_array( $bbcode['bbcode_tag'], $protectedBbcodes ) )
			{
				continue;
			}

			if( $bbcode['bbcode_groups'] != 'all' )
			{
				$pass		= false;
				$groups		= array_diff( explode( ',', $bbcode['bbcode_groups'] ), array('') );
				$mygroups	= array( ipsRegistry::member()->getProperty('member_group_id') );
				$mygroups	= array_diff( array_merge( $mygroups, explode( ',', IPSText::cleanPermString( ipsRegistry::member()->getProperty('mgroup_others') ) ) ), array('') );
				
				foreach( $groups as $g_id )
				{
					if( in_array( $g_id, $mygroups ) )
					{
						$pass = true;
						break;
					}
				}
				
				if( !$pass )
				{
					continue;
				}
			}

			$bbcodes[ $bbcode['bbcode_tag'] ]	= array(
														'id'				=> $bbcode['bbcode_id'],
														'title'				=> $bbcode['bbcode_title'],
														'desc'				=> $bbcode['bbcode_desc'],
														'tag'				=> $bbcode['bbcode_tag'],
														'useoption'			=> $bbcode['bbcode_useoption'],
														'example'			=> $bbcode['bbcode_example'],
														'switch_option'		=> $bbcode['bbcode_switch_option'],
														'menu_option_text'	=> $bbcode['bbcode_menu_option_text'],
														'menu_content_text'	=> $bbcode['bbcode_menu_content_text'],
														'single_tag'		=> $bbcode['bbcode_single_tag'],
														'optional_option'	=> $bbcode['bbcode_optional_option'],
														'image'				=> $bbcode['bbcode_image'],
														);
		}
		
		return IPSText::simpleJsonEncode($bbcodes);
	}
	
	/**
	 * Create profile link
	 *
	 * @access	public
	 * @param	string		User's display name
	 * @param	integer		User's DB ID
	 * @param	string		SEO display name
	 * @return	string		Parsed a href link
	 * @since	2.0
	 */
	static public function makeProfileLink($name, $id="", $_seoName="")
	{
		$_seoName = ( $_seoName ) ? $_seoName : IPSText::makeSeoTitle( $name );
		
		if ($id > 0)
		{
			return "<a href='" . ipsRegistry::getClass('output')->buildSEOUrl( 'showuser=' . $id, 'public', $_seoName, 'showuser' ) . "'>{$name}</a>";
		}
		else
		{
			return $name;
		}
	}

	/**
	 * Runs the specified member sync module, takes a variable number of arguments.
	 *
	 * @access	public
	 * @param	string	$module		The module to run, ex: onCreateAccount, onRegisterForm, etc
	 * @param	mixed	...			Remaining params should match the module being called. ex: array of member data for onCreateAccount,
     *								or an id and email for onEmailChange
	 * @return	void
	 **/
	static public function runMemberSync( $module )
	{
		/* ipsRegistry::$applications only contains apps with a public title #15785 */
		$app_cache = ipsRegistry::cache()->getCache('app_cache');
		
		/* Params */
		$params = func_get_args();
		array_shift( $params );

		/* Loop through applications */
		foreach( $app_cache as $app_dir => $app )
		{
			/* Only if app enabled... */
			if ( $app['app_enabled'] )
			{
				/* Setup */
				$_file  = self::getAppDir( $app['app_directory'] ) . '/extensions/memberSync.php';
				$_class = $app['app_directory'] . 'MemberSync';
					
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
						if( method_exists( $_obj, $module ) )
						{
							/* Call it */
							call_user_func_array( array( $_obj, $module ), $params );
							IPSDebug::addLogMessage( $app_dir . '-' . $module, 'mem' );
						}
					}
				}
			}
		}
	}

	/**
	 * Pick the highest number from an array
	 * Used in classItemMarking.. figured it might be useful elsewhere...
	 *
	 * @access	public
	 * @param	array 		Array of numbers
	 * @return	integer		Highest number in the array
	 */
	static public function fetchHighestNumber( $array )
	{
		if ( is_array( $array ) )
		{
			$_array = array();

			foreach( $array as $number )
			{
				$_array[] = intval( $number );
			}

			sort( $_array );

			return intval( array_pop( $_array ) );
		}
		else
		{
			return 0;
		}
	}

	/**
	 * Hand-rolled 'is_writable' function to overcome
	 * annoyances with the PHP in built version
	 * Based on user notes at php.net (is_writable function comments)
	 *
	 * @access	public
	 * @param	string		Path to check
	 * @return	boolean
	 */
	static public function isWritable( $path )
	{
		if ( substr( $path, -1 ) == '/' )
		{
	        return self::isWritable( $path . uniqid( mt_rand() ) . '.tmp');
		}
	    else if ( is_dir( $path ) )
		{
			return self::isWritable( $path.'/'.uniqid( mt_rand() ) . '.tmp');
	    }

		$e = file_exists( $path );
	    $f = @fopen( $path, 'a' );

	    if ( $f === FALSE )
		{
	        return FALSE;
		}

	    fclose ($f );

	    if ( $e === FALSE )
		{
	        unlink($path);
		}

	    return TRUE;
	}

	/**
	 * Acts like PHPs next() but if the pointer is at the end of the array or it finds a false
	 * value, then it rewinds the array and starts over
	 *
	 * @access	public
	 * @param	array 		Reference to an array
	 * @return	mixed		Next value in the array
	 */
	static public function next( &$array )
	{
		if ( ! is_array( $array ) )
		{
			return FALSE;
		}

		$next = next( $array );

		if ( ! $next || $next === FALSE )
		{
			reset( $array );
			$next = next( $array );
		}

		return $next;
	}
	
	/**
	 * Function to naturally sort an array by keys
	 *
	 * @access	public
	 * @param	array 		Array to sort
	 * @return	array 		Sorted array
	 */
	static public function knatsort( $array )
	{
		$_a = array_keys( $array );
		$_b = array();
		
		natsort( $_a );
		
		foreach( $_a as $__a )
		{
			$_b[ $__a ] = $array[ $__a ];
		}
		
		return $_b;
	}
	
	/**
	 * Merges two arrays using a custom call back function (like array_merge using usort)
	 * <code>
	 * $a = array( 'red'    => 100,
	 *			  'green'  => 200,
	 *			  'blue'   => 300,
	 *			  'orange' => 600 );
	 *
	 * $b = array( 'red'   => 101,
	 * 			  'green' => 199,
	 *			  'blue'  => 305,
	 *			  'black' => 100 );
	 *
	 * Merge both arrays, finding the highest value in each
	 * print_r( array_umerge( $a, $b, create_function( '$a,$b', 'return $a < $b;' ) ) );
	 * Produces:
	 * Array
	 * (
	 *	[red]    => 101
	 *	[green]	 => 200
	 *	[blue]   => 305
	 *	[orange] => 600
	 *	[black]  => 100
	 * )
	 * </code>
	 *
	 * @access	public
	 * @param	array 		Array to merge
	 * @param	array 		Array to merge
	 * @param	string		Callback function
	 * @return	array 		Merged array
	 * @deprecated			This will likely be removed in a future version
	 */
	static public function arrayUmerge( $array1, $array2, $callback )
	{
		$return = array();

		if ( ! is_array( $array1 ) AND ! is_array( $array2 ) )
		{
			return FALSE;
		}

		foreach( $array1 as $_k1 => $_v1 )
		{
			$return[ $_k1 ] = $_v1;

			/* Key exists in second array - so compare */
			if ( isset( $array2[$_k1] ) )
			{
				if ( call_user_func( $callback, $_v1, $array2[$_k1] ) )
				{
					$return[ $_k1 ] = $array2[$_k1];
				}
			}
		}

		/* Now check for keys in the second array that aren't in the first */
		foreach( $array2 as $_k2 => $_v2 )
		{
			if ( ! isset( $array1[ $_k2 ] ) )
			{
				$return[ $_k2 ] = $_v2;
			}
		}

		return $return;
	}
	
	/**
	 * Merges arrays like array_merge_recursive but replaces indentical keys
	 *
	 * @access	public
	 */
	static public function arrayMergeRecursive()
	{
	    $arrays = func_get_args();
  		$base   = array_shift( $arrays );
  		
  		if ( ! is_array($base) )
  		{
  			$base = empty($base) ? array() : array($base);
  		}
  		
  		foreach( $arrays as $append )
  		{
    		if ( ! is_array( $append ) )
    		{
    			$append = array( $append );
    		}
    		
    		foreach( $append as $key => $value)
    		{
      			if ( ! array_key_exists( $key, $base ) and ! is_numeric( $key ) )
      			{
        			$base[$key] = $append[$key];
        			continue;
      			}
      			
      			if ( is_array($value) or is_array( $base[$key] ) )
      			{
        			$base[$key] = self::arrayMergeRecursive( $base[$key], $append[$key] );
      			}
      			else if ( is_numeric( $key ) )
      			{
        			if ( ! in_array( $value, $base ) )
        			{
        				$base[] = $value;
        			}
      			}
      			else
      			{
       				$base[$key] = $value;
      			}
    		}
  		}
  		
 		return $base;
	}
	
	/**
	 * arraySearchLoose
	 *
	 * @access	protected
	 * @param	string		"Needle"
	 * @param	array 		Array of text to search
	 * @return	mixed		Key of array, or false on failure
	 */
	static public function arraySearchLoose( $needle, $haystack )
	{
		if( !is_array( $haystack ) OR !count($haystack) OR ! $needle )
		{
			return false;
		}
		
		foreach( $haystack as $k => $v )
		{
			if( $v AND stripos( $v, $needle ) !== false )
			{
				return $k;
			}
		}
		
		return false;
	}
	
	/**
	 * Get the application title.  Uses lang file keys if present.
	 *
	 * @access	public
	 * @param	string		application
	 * @return	string		Text to show for application title
	 */
	static public function getAppTitle( $app )
	{
		if ( ! $app )
		{
			return '';
		}

		return isset( ipsRegistry::getClass('class_localization')->words[ $app . '_display_title' ] ) ? 
				ipsRegistry::getClass('class_localization')->words[ $app . '_display_title' ] :
				( IN_ACP ? ipsRegistry::$applications[ $app ]['app_title'] : ipsRegistry::$applications[ $app ]['app_public_title'] );
	}

	/**
	 * Generates the app [ -> module ] path. The module is optional, if module is not
	 * specified then just the app dir is returned. If this is called from the ACP and module
	 * is present, then it'll return modules_admin, otherwise modules_public
	 *
	 * @access	public
	 * @param	string		application
	 * @param	string		module (optional)
	 * @return	mixed		Directory to app or module (or false if error)
	 */
	static public function getAppDir( $app, $module='' )
	{
		$location = '';

		if ( ! $app OR !is_string($app) )
		{
			return FALSE;
		}

		/* Ok, chicken and egg scenario. Applications has not been set up - most likely because
		   we're using this function before the caches have been loaded and unpacked.
		   So we guess based on folder names.... */
		if ( ! is_array( ipsRegistry::$applications ) OR ! count( ipsRegistry::$applications ) OR ! isset( ipsRegistry::$applications[ $app ] ) )
		{
			$location = self::extractAppLocationKey( $app );
		}
		else
		{
			$location = ipsRegistry::$applications[ $app ]['app_location'];
		}

		$pathBit       = IPS_ROOT_PATH . 'applications';
		$modulesFolder = ( IPS_AREA != 'admin' ) ? 'modules_public' : 'modules_admin';

		switch ( $location )
		{
			default:
			case 'root':
				$pathBit .= '/' . $app;
			break;
			case 'ips':
				$pathBit .= '_addon/ips/' . $app;
			break;
			case 'other':
				$pathBit .= '_addon/other/' . $app;
			break;
		}

		if ( $module )
		{
			return $pathBit . "/" . $modulesFolder . "/" . $module;
		}
		else
		{
			return $pathBit;
		}
	}
	
	/**
	 * Extracts app_location from app key
	 *
	 * @access	public
	 * @param	string		File path
	 * @return	string		root, ips, other
	 */
	static public function extractAppLocationKey( $app )
	{
		/* Test core apps first... */
		if ( is_dir( IPS_ROOT_PATH . 'applications/' . $app ) )
		{
			$location = 'root';
		}
		else if ( is_dir( IPS_ROOT_PATH . 'applications_addon/ips/' . $app ) )
		{
			$location = 'ips';
		}
		else
		{
			$location = 'other';
		}
		
		return $location;
	}

	/**
	 * Generates the app folder name, either "applications" or "applications_addon"
	 *
	 * @access	public
	 * @param	string		application
	 * @return	mixed		Directory to app or module (or false if error)
	 */
	static public function getAppFolder( $app )
	{
		if ( ! $app OR ! isset(ipsRegistry::$applications[ $app ]) )
		{
			return FALSE;
		}

		switch ( ipsRegistry::$applications[ $app ]['app_location'] )
		{
			default:
			case 'root':
				$pathBit = 'applications';
			break;
			case 'ips':
				$pathBit = 'applications_addon/ips';
			break;
			case 'other':
				$pathBit = 'applications_addon/other';
			break;
		}

		return $pathBit;
	}
	
	/**
	 * Determines if the application can be searched
	 *
	 * @access	public
	 * @param	string	$app	Application key
	 * @return	bool
	 **/
	static public function appIsSearchable( $app, $type='search' )
	{
		/* Init */
		$_ck   = '';
		
		/* map config */
		switch( strtolower( $type ) )
		{
			default:
			case 'search':
				$_ck = 'can_search';
			break;
			case 'vnc':
			case 'newcontent':
			case 'viewnewcontent':
				$_ck = 'can_viewNewContent';
			break;
			case 'active':
			case 'activecontent':
				$_ck = 'can_activeContent';
			break;
			case 'usercontent':
			case 'users':
			case 'user':
				$_ck = 'can_userContent';
			break;
		}
			
		/* got anything? */
		if ( ! is_array( self::$_searchConfigs ) OR ! count( self::$_searchConfigs ) )
		{
			foreach( ipsRegistry::$applications as $_app => $data )
			{
				/* use the cache if we can */
				if ( ! IN_DEV AND isset( ipsRegistry::$applications[$_app]['search'] ) AND is_array( ipsRegistry::$applications[$_app]['search'] ) AND count( ipsRegistry::$applications[$_app]['search'] ) )
				{
					self::$_searchConfigs[ $_app ] = ipsRegistry::$applications[$_app]['search'];
				}
				else
				{
					$_file = IPSLib::getAppDir( $_app ) . '/extensions/search/config.php';
					
					if ( IPSLib::appIsInstalled( $_app ) AND file_exists( $_file ) )
					{
						require( $_file );
						
						if ( is_array( $CONFIG ) AND count( $CONFIG ) )
						{
							self::$_searchConfigs[ $_app ] = $CONFIG;
						
							unset( $CONFIG );
						}
					}
				}
			}
		}
			
		/* return */
		if ( is_array( self::$_searchConfigs[ $app ] ) AND count( self::$_searchConfigs[ $app ] ) )
		{
			return ( self::$_searchConfigs[ $app ][ $_ck ] ) ? true : false;
		}
		
		return false;
	}

	/**
     * Checks to see if the given application is currently installed and enabled
     *
     * @access	public
     * @param	string	$app
     * @return	bool
     */
    static public function appIsInstalled( $app, $checkEnabled=true )
    {
    	if ( isset( ipsRegistry::$applications[$app] ) )
    	{
    		if( $checkEnabled )
    		{
    			if( ipsRegistry::$applications[$app]['app_enabled'] )
    			{
    				return TRUE;
    			}
    		}
    		else
    		{
    			return TRUE;
			}
    	}

    	return FALSE;
    }
    
    /**
     * Check to see if the givem module is currently installed and enabled
     *
     * @access	public
     * @param	string	$module	module_key
     * @param	string	[$app]	app_key, current application by default
     * @return	bool
     **/
    static public function moduleIsEnabled( $module, $app='' )
    {
    	$app = $app ? $app : ipsRegistry::$current_application;
    	
    	foreach( ipsRegistry::$modules[$app] as $_m )
    	{
    		if ( $_m['sys_module_key'] == $module )
    		{
    			return $_m['sys_module_visible'] == 1;
    		}
    	}
    	
    	return FALSE;
    }

	/**
	 * Grab max post upload
	 *
	 * @access	public
	 * @return	integer	Max post size
	 */
	static public function getMaxPostSize()
	{
		$max_file_size = 16777216;
		$tmp           = 0;

		$_post   = @ini_get('post_max_size');
		$_upload = @ini_get('upload_max_filesize');

		if ( $_upload > $_post )
		{
			$tmp = $_post;
		}
		else
		{
			$tmp = $_upload;
		}

		if ( $tmp )
		{
			$max_file_size = $tmp;
			unset($tmp);

			preg_match( "#^(\d+)(\w+)$#", strtolower($max_file_size), $match );
			
			if( $match[2] == 'g' )
			{
				$max_file_size = intval( $max_file_size ) * 1024 * 1024 * 1024;
			}
			else if ( $match[2] == 'm' )
			{
				$max_file_size = intval( $max_file_size ) * 1024 * 1024;
			}
			else if ( $match[2] == 'k' )
			{
				$max_file_size = intval( $max_file_size ) * 1024;
			}
			else
			{
				$max_file_size = intval( $max_file_size );
			}
		}

		return $max_file_size;
	}

    /**
	 * Convert strlen to bytes
	 *
	 * @access	public
	 * @param	integer		string length (no chars)
	 * @return	integer		Bytes
	 * @since	2.0
	 */
	static public function strlenToBytes( $strlen=0 )
    {
		$dh = pow(10, 0);

        return round( $strlen / ( pow(1024, 0) / $dh ) ) / $dh;
    }

	/**
	 * Takes a number of bytes and formats in k or MB as required
	 *
	 * @access	public
	 * @param	string 		Size, in bytes
	 * @param	boolean		TRUE = no language class avaiable (during start up, debug, etc)
	 * @return	string		Size, in MB, KB or bytes, whichever is closest
	 */
	static public function sizeFormat($bytes="", $noLang=FALSE)
	{
		$retval = "";
		
		if ( $noLang === FALSE )
		{
			$lang['sf_gb']    = ipsRegistry::getClass('class_localization')->words['sf_gb']    ? ipsRegistry::getClass('class_localization')->words['sf_gb']    : 'gb';
			$lang['sf_mb']    = ipsRegistry::getClass('class_localization')->words['sf_mb']    ? ipsRegistry::getClass('class_localization')->words['sf_mb']    : 'mb';
			$lang['sf_k']     = ipsRegistry::getClass('class_localization')->words['sf_k']     ? ipsRegistry::getClass('class_localization')->words['sf_k']     : 'kb';
			$lang['sf_bytes'] = ipsRegistry::getClass('class_localization')->words['sf_bytes'] ? ipsRegistry::getClass('class_localization')->words['sf_bytes'] : 'b';
		}
		else
		{
			$lang['sf_gb']    = 'gb';
			$lang['sf_mb']    = 'mb';
			$lang['sf_k']     = 'kb';
			$lang['sf_bytes'] = 'b';
		}
		
		if ( $bytes >= 1073741824 )
		{
			$retval = round($bytes / 1073741824 * 100 ) / 100 . $lang['sf_gb'];
		}
		else if ($bytes >= 1048576)
		{
			$retval = round($bytes / 1048576 * 100 ) / 100 . $lang['sf_mb'];
		}
		else if ($bytes  >= 1024)
		{
			$retval = round($bytes / 1024 * 100 ) / 100 . $lang['sf_k'];
		}
		else
		{
			$retval = $bytes . $lang['sf_bytes'];
		}

		return $retval;
	}

    /**
	 * Makes int based arrays safe
	 * XSS Fix: Ticket: 24360 (Problem with cookies allowing SQL code in keys)
	 *
	 * @access	public
	 * @param	array		Array
	 * @return	array		Array (Cleaned)
	 * @since	2.1.4(A)
	 */
    static public function cleanIntArray( $array=array() )
    {
		$return = array();

		if ( is_array( $array ) and count( $array ) )
		{
			foreach( $array as $k => $v )
			{
				$return[ intval($k) ] = intval($v);
			}
		}

		return $return;
	}

	/**
	 * Loads an interface. Abstracted incase we change location / method
	 * of loading an interface
	 *
	 * @access	public
	 * @param	string		File name
	 * @return	void
	 * @since	3.0.0
	 */
	static public function loadInterface( $filename )
	{
		//-----------------------------------------
		// Very simple, currently.
		//-----------------------------------------

		include_once( IPS_ROOT_PATH . 'sources/interfaces/' . $filename );
	}

	/**
	 * Create a random 15 character password
	 *
	 * @access	public
	 * @return	string	Password
	 * @since	2.0
	 */
	public static function makePassword()
	{
		$pass = "";

		// Want it random you say, eh?
		// (enter evil laugh)

		$unique_id 	= uniqid( mt_rand(), TRUE );
		$prefix		= IPSMember::generatePasswordSalt();
		$unique_id .= md5( $prefix );

		usleep( mt_rand(15000,1000000) );
		// Hmm, wonder how long we slept for

		$new_uniqueid = uniqid( mt_rand(), TRUE );

		$final_rand = md5( $unique_id . $new_uniqueid );

		for ($i = 0; $i < 15; $i++)
		{
			$pass .= $final_rand{ mt_rand(0, 31) };
		}

		return $pass;
	}
	
	/**
	 * Scale a remote image
	 *
	 * @access	public
	 * @param	string		URL
	 * @param	int			Max width
	 * @param	int			Max height
	 * @return	string		width='#' height='#' string
	 */
	static public function getTemplateDimensions( $image, $width, $height )
	{
		if( empty( $width ) AND empty( $height ) )
		{
			return;
		}
		
		if( !$image )
		{
			return;
		}

		//-----------------------------------------
		// Dimensions
		// If set maxwidth and no maxheight, then we want the script to
		//	reduce based on width only.  And vice-versa.
		//-----------------------------------------
		
		$maxWidth	= ( $width ) ? intval($width) : 1000000000;
		$maxHeight	= ( $height ) ? intval($height) : 1000000000;
		
		//-----------------------------------------
		// Existing dims
		//-----------------------------------------
		
		$_dims		= @getimagesize( $image );

		if( !$_dims[0] )
		{
			return;
		}
		
		$_newDims	= IPSLib::scaleImage( array( 
												'cur_width'		=> $_dims[0],
												'cur_height'	=> $_dims[1],
												'max_width'		=> $maxWidth,
												'max_height'	=> $maxHeight,
										)		);

		//-----------------------------------------
		// Process the tag and return the data
		//-----------------------------------------

		return " width='{$_newDims['img_width']}' height='{$_newDims['img_height']}'";
	}

	/**
	 * Given current dimensions + max dimensions, return scaled image dimensions constrained to maximums
	 *
	 * @access	public
	 * @param	array	Current dimensions + max dimensions
	 * @return	array	New image dimensions
	 * @since	2.0
	 * @todo	[Future] We may want to consider moving this to kernel classImage.php.  Method exists there, protected, already.
	 */
	public static function scaleImage($arg)
	{
		// max_width, max_height, cur_width, cur_height

		$ret = array(
					  'img_width'  => $arg['cur_width'],
					  'img_height' => $arg['cur_height']
					);

		if ( $arg['cur_width'] > $arg['max_width'] )
		{
			$ret['img_width']  = $arg['max_width'];
			$ret['img_height'] = ceil( ( $arg['cur_height'] * ( ( $arg['max_width'] * 100 ) / $arg['cur_width'] ) ) / 100 );
			$arg['cur_height'] = $ret['img_height'];
			$arg['cur_width']  = $ret['img_width'];
		}

		if ( $arg['cur_height'] > $arg['max_height'] )
		{
			$ret['img_height']  = $arg['max_height'];
			$ret['img_width']   = ceil( ( $arg['cur_width'] * ( ( $arg['max_height'] * 100 ) / $arg['cur_height'] ) ) / 100 );
		}

		return $ret;
	}

	/**
	 * Format name based on group suffix/prefix
	 *
	 * @access	public
	 * @param	string		User's display name
	 * @param	integer		User's group ID
	 * @param	string  	Optional prefix override (uses group setting if not provided)
	 * @param	string  	Optional suffix override (uses group setting if not provided)
	 * @return	string		Formatted name
	 * @since	2.2
	 */
	public static function makeNameFormatted($name='', $group_id="", $prefix="", $suffix="")
	{
		if ( ipsRegistry::$settings['ipb_disable_group_psformat'] )
		{
			return $name;
		}

		if ( ! $group_id )
		{
			$group_id = 0;
		}

		$groupCache = ipsRegistry::cache()->getCache('group_cache');

		if ( ! $prefix )
		{
			if( $groupCache[ $group_id ]['prefix'] )
			{
				$prefix = $groupCache[ $group_id ]['prefix'];
			}
		}

		if( ! $suffix )
		{
			if( $groupCache[ $group_id ]['suffix'] )
			{
				$suffix = $groupCache[ $group_id ]['suffix'];
			}
		}
		
		if ( ! $name )
		{
			if( $groupCache[ $group_id ]['g_title'] )
			{
				$name = $groupCache[ $group_id ]['g_title'];
			}
		}

		return $prefix.$name.$suffix;
	}
	
	/**
	 * Retrieve all IP addresses a user (or multiple users) have used
	 *
	 * @access	public
	 * @param 	string		Where clause for ip address
	 * @param	string		Defaults to 'All', otherwise specify which tables to check (comma separated)
	 * @return	array		Multi-dimensional array of found IP addresses in which sections
	 */
	static public function findIPAddresses( $ip_where, $tables_to_check='all' )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------

		$ip_addresses 	= array();
		$tables			= array(
							'admin_logs'			=> array( 'member_id', 'ip_address', 'ctime' ),
							'dnames_change'			=> array( 'dname_member_id', 'dname_ip_address', 'dname_date' ),
							'email_logs'			=> array( 'from_member_id', 'from_ip_address', 'email_date' ),
							'members'				=> array( 'member_id', 'ip_address', 'joined' ),
							'message_posts'			=> array( 'msg_author_id', 'msg_ip_address', 'msg_date' ),
							'moderator_logs'		=> array( 'member_id', 'ip_address', 'ctime' ),
							'posts'					=> array( 'author_id', 'ip_address', 'post_date' ),
							'profile_comments'		=> array( 'comment_by_member_id', 'comment_ip_address', 'comment_date' ),
							'profile_ratings'		=> array( 'rating_by_member_id', 'rating_ip_address', 'rating_added' ),
							//'sessions'				=> array( 'member_id', 'ip_address', 'running_time' ),
							'topic_ratings'			=> array( 'rating_member_id', 'rating_ip_address', '' ),
							'validating'			=> array( 'member_id', 'ip_address', 'entry_date' ),
							'voters'				=> array( 'member_id', 'ip_address', 'vote_date' ),
							'error_logs'			=> array( 'log_member', 'log_ip_address', 'log_date' ),
							);

		//-----------------------------------------
		// Check apps
		// @see http://forums.invisionpower.com/tracker/issue-16966-members-download-manag/
		//-----------------------------------------
		
		foreach( ipsRegistry::$applications as $app_dir => $data )
		{
			if( file_exists( IPSLib::getAppDir( $app_dir ) . "/extensions/coreExtensions.php") )
			{
				require_once( IPSLib::getAppDir( $app_dir ) . "/extensions/coreExtensions.php" );
				
				if( class_exists( $app_dir . '_findIpAddress' ) )
				{
					$classX 	= $app_dir . '_findIpAddress';

					$ipLookup	= new $classX( ipsRegistry::instance() );
					
					if( method_exists( $ipLookup, 'getTables' ) )
					{
						$tables		= array_merge( $tables, $ipLookup->getTables() );
					}
				}
			}
		}

		//-----------------------------------------
		// Got tables?
		//-----------------------------------------

		$_tables = explode( ',', $tables_to_check );

		if( !is_array($_tables) OR !count($_tables) )
		{
			return array();
		}

		//-----------------------------------------
		// Loop through them and grab the IPs
		//-----------------------------------------

		foreach( $tables as $tablename => $fields )
		{
			if( $tables_to_check == 'all' OR in_array( $tablename, $_tables ) )
			{
				$extra = '';
				$ids   = array();
				
				if( $tablename == 'members' )
				{
					if( $fields[2] )
					{
						$extra = ',' . $fields[2] . ' as date';
					}
				
					ipsRegistry::DB()->build( array(
													'select'	=> $fields[1] . $extra . ', member_id', 
													'from'		=> $tablename, 
													'where'		=> $fields[1] . $ip_where,
													'group'		=> 'member_id, ip_address, joined',
													'order'		=> 'joined DESC',
													'limit'		=> array( 250 ),
											)		);
				}
				else
				{
					if( $fields[2] )
					{
						$extra = ', c.' . $fields[2] . ' as date';
					}
					
					$extra .= ', c.' . $fields[1] . ' as ip_address';
				
					ipsRegistry::DB()->build( array(
													'select'	=> 'c.' . $fields[1] . $extra, 
													'from'		=> array( $tablename => 'c' ), 
													'where'		=> 'c.' . $fields[1] . $ip_where,
													'order'		=> $fields[2] ? 'c.' . $fields[2] . ' DESC' : 'c.' . $fields[0] . ' DESC',
													'group'		=> 'c.' . $fields[0],
													'limit'		=> array( 250 ),
													'add_join'	=> array(
																		array(
																			'select'	=> 'm.member_id, m.members_display_name, m.email, m.posts, m.joined',
																			'from'		=> array( 'members' => 'm' ),
																			'where'		=> 'm.member_id=c.' . $fields[0],
																			'type'		=> 'left',
																			)
																		)
											)		);
				}

				ipsRegistry::DB()->execute();
				
				$i = 0;
				
				while( $r = ipsRegistry::DB()->fetch() )
				{
					if ( $r[ $fields[0] ] )
					{
						$ids[] = $r[ $fields[0] ];
					}
					
					if( $r[ $fields[1] ] )
					{
						$rawData[ ++$i ]	= $r;
					}
				}
				
				/* Get members */
				$members = IPSMember::load( $ids, 'core' );
				
				if ( is_array( $rawData ) and count ( $rawData ) )
				{
					foreach( $rawData as $idx => $data )
					{
						if ( $data[ $fields[0] ] && is_array( $members[ $data[ $fields[0] ] ] ) )
						{
							$ip_addresses[ $tablename ][ $idx ] = array_merge( $data, $members[ $data[ $fields[0] ] ] );
						}
					}
				}
			}
		}

		//-----------------------------------------
		// Here are your IPs kind sir.  kthxbai
		//-----------------------------------------

		return $ip_addresses;
	}
	
	/**
	 * Display a strip of share links
	 *
	 * @access	public
	 * @param	string		Document title (can be left blank and it will attempt to self-discover)
	 * @param	array		Addition params: url, cssClass, group [string template group], bit [string template bit], skip [array of share_keys to skip]
	 * @return	string		HAITHTEEEMEL
	 */
	static public function shareLinks( $title='', $params=array() )
	{
		$url      = ( isset( $params['url'] ) )      ? $params['url']      : '';
		$cssClass = ( isset( $params['cssClass'] ) ) ? $params['cssClass'] : 'topic_share left';
		$group    = ( isset( $params['group'] ) )    ? $params['group']    : 'global';
		$bit      = ( isset( $params['bit'] ) )      ? $params['bit']      : 'shareLinks';
		$skip     = ( isset( $params['skip'] ) )     ? $params['skip']     : array();
		
		/* Disabled? */
		if ( ! ipsRegistry::$settings['sl_enable'] )
		{
			return '';
		}
		
		$canon  = ipsRegistry::getClass('output')->fetchRootDocUrl();
		$url    = ( $url ) ? $url : ipsRegistry::$settings['this_url'];
		$canon  = IPSText::base64_encode_urlSafe( ( $canon ) ? $canon : $url );
		$title  = IPSText::base64_encode_urlSafe( $title );
		$url    = IPSText::base64_encode_urlSafe( $url );
		
		$cache = ipsRegistry::cache()->getCache('sharelinks');
	
		if ( ! $cache OR ! is_array( $cache ) )
		{
			ipsRegistry::cache()->rebuildCache('sharelinks', 'global' );
			$cache = ipsRegistry::cache()->getCache('sharelinks');
		}
		
		/* Check for required canonical urls or not */
		foreach( $cache as $key => $data )
		{
			if ( is_array( $skip ) AND in_array( $key, $skip ) )
			{
				unset( $cache[ $key ] );
			}
			else
			{
				$cache[ $key ]['_url'] = ( $data['share_canonical'] ) ? $canon : $url;
			}
		}
		
		return ipsRegistry::getClass('output')->getTemplate( $group )->$bit( $cache, $title, $canon, $cssClass );
	}
	
	/**
	 * Checks to see if the logged in user can recieve mobile notifications
	 *
	 * @param	array 		$memberData		Optional, logged in user will be used if this is not passed in
	 * @access	public
	 * @return	BOOL
	 */
	static public function canReceiveMobileNotifications( $memberData=array() )
	{
		/* INIT */
		$memberData = ( is_array( $memberData ) && count( $memberData ) ) ? $memberData : ipsRegistry::member()->fetchMemberData();

		/* Check to see if notifications are enabled */
		if( ! ipsRegistry::$settings['iphone_notifications_enabled'] )
		{
			return false;
		}
		
		/* Check to see if the user has permission to get notifications */
		if( ipsRegistry::$settings['iphone_notifications_groups'] )
		{
			if( ipsMember::isInGroup( $memberData, explode( ',', ipsRegistry::$settings['iphone_notifications_groups'] ) ) )
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		
		return true;
	}
}

/**
* IPSDebug
*
* Only useful when developing
*/
class IPSDebug
{
	/**
	 * Memory debug array
	 *
	 * @access	public
	 * @var		array 		Memory debug info
	 */
	static public $memory_debug = array();

	/**
	 * Messages
	 *
	 * @access	public
	 * @var		array 		Messages
	 */
	static private $_messages = array();

	/**
	 * Turn off constructor
	 *
	 * @access	private
	 * @return	void
	 */
	private function __construct() {}

	/**
	 * Start time
	 *
	 * @access	private
	 * @var		integer		Start time
	 */
	static private $_starttime;

	/**
	 * Add message
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	static public function addMessage( $message )
	{
		self::$_messages[] = $message;
	}
	
	/**
	 * Send a FirePHP message
	 *
	 * @access	public
	 * @param	string	$method		Method to call
	 * @param	string	$vars		Parameters to pass
	 * @return	void
	 * @link	http://www.firephp.org/HQ/
	 */
	static public function fireBug( $method, $parameters=array() )
	{
		if( IN_DEV )
		{
			if( !class_exists( 'FB' ) )
			{
				require_once( IPS_KERNEL_PATH . '/FirePHPCore/fb.php' );
			}
			
			if( $method == 'registerExceptionHandler' )
			{
				$firephp = FirePHP::getInstance(true);
				$firephp->registerExceptionHandler();
			}
			
			if( $method == 'registerErrorHandler' )
			{
				$firephp = FirePHP::getInstance(true);
				$firephp->registerErrorHandler();
			}

			if( method_exists( 'FB', $method ) )
			{
				$function	= 'FB::' . $method;

				call_user_func_array( $function, $parameters );
			}
		}
	}
	
	/**
	 * Custom error handler
	 *
	 * @param	integer	Error number
	 * @param	string	Error string
	 * @param	string	Error file
	 * @param	string	Error line number
	 * @return	void
	 */
	static public function errorHandler( $errno, $errstr, $errfile, $errline )
	{
		/* Did we turn off errors with @? */
		if ( ! error_reporting() )
		{
			return;
		}
		
		/* Are we truly debugging? */
		if ( IPS_ERROR_CAPTURE === FALSE )
		{
			return;
		}

		$errfile = str_replace( @getcwd(), "", $errfile );
		$log	 = false;
		$message = "> [$errno] $errstr\n> > Line: $errline\n> > File: $errfile";
		
		/* What do we have? */
		switch ($errno)
		{
	  		case E_ERROR:
				$log = true;
	   			echo "<b>IPB ERROR</b> [$errno] $errstr (Line: $errline of $errfile)<br />\n";
	   			exit(1);
	   		break;
	  		case E_WARNING:
				$log = true;
	   			echo "<b>IPB WARNING</b> [$errno] $errstr (Line: $errline of $errfile)<br />\n";
	   		break;
			case E_NOTICE:
	   			$log = true;
	   		break;
	 		default:
				return FALSE;
	   			//Do nothing
	   		break;
		}
		
		/* Logging? */
		if ( $log )
		{
			if ( IPS_ERROR_CAPTURE === TRUE )
			{
				self::addLogMessage( $message, "phpNotices", false, true );
			}
			else
			{
				foreach( explode( ',', IPS_ERROR_CAPTURE ) as $class )
				{
					if ( preg_match( "#/" . preg_quote( $class, '#' ) . "\.#", $errfile ) )
					{
						self::addLogMessage( $message, "phpNotices", false, true );
						break;
					}
				}
			}
		}
	}
	
	/**
	 * Add a message to the log file
	 * Handy for __destruct stuff, etc
	 *
	 * @access	public
	 * @param	string	Message to add
	 * @param	string	Which file to add it to
	 * @param	mixed	False, or an array of vars to include in log
	 * @param	bool	Force log even if IPS_LOG_ALL is off - handy for on-the-fly debugging
	 * @param	bool	Unlink file before writing
	 * @return	void
	 */
	static public function addLogMessage( $message, $file='debugLog', $array=FALSE, $force=FALSE, $unlink=FALSE )
	{
		/* Make sure IN_DEV is on to prevent logs filling up where people forget to turn it off... */
		if ( ( defined( 'IPS_LOG_ALL' ) AND IPS_LOG_ALL === TRUE ) OR $force === TRUE )
		{
			if ( $unlink === TRUE )
			{
				@unlink( DOC_IPS_ROOT_PATH . 'cache/' . $file . '.cgi' );
			}
			
			/* Array to dump? */
			if ( is_array( $array ) )
			{
				$message .= "\n" . var_export( $array, TRUE );
			}
			
			$message = "\n" . str_repeat( '-', 80 ) . "\n> Time: " . time() . ' / ' . gmdate( 'r' ) . "\n> URL: " . $_SERVER['REQUEST_URI'] . "\n> " . $message;
			@file_put_contents( DOC_IPS_ROOT_PATH . 'cache/' . $file . '.cgi', $message, FILE_APPEND );
		}
	}

	/**
	 * Return messages
	 *
	 * @access	public
	 * @return 	array 		Stored messages
	 */
	static public function getMessages()
	{
		return self::$_messages;
	}

	/**
	 * Displays a templating error
	 * Only used when IN_DEV is on
	 *
	 * @access 	public
	 * @param	string		Complete PHP error string
	 * @param	string		Text evaluated by PHP
	 * @return	void
	 */
	static public function showTemplateError( $errorText, $evalCode )
	{
		$output     = array();
		$count      = 0;
		$openDiv    = '<div style="width:95%;text-align:left; margin-auto; padding:10px; white-space:pre;border:1px solid black; background:#eee;font-family:\'Courier New\', Courier, Geneva;font-size:0.8em">';
		$lineNumber = 0;

		/* Convert text into lines */
		$evalCode = preg_replace( "#\r#", "\n", $evalCode );
		$lines    = explode( "\n", $evalCode );

		if ( count( $lines ) )
		{
			foreach( $lines as $l )
			{
				$count++;
				$output[ $count ] = htmlspecialchars($l);
			}
		}

		/* Anything we can deal with? */
		if ( strstr( $errorText, "eval()'d code" ) )
		{
			preg_match( "#eval\(\)'d code</b> on line <b>(\d+?)</b>#", $errorText, $matches );

			if ( $matches[1] )
			{
				$lineNumber = $matches[1];
				$output[ $lineNumber ] = "<span style='background:yellow;color:red;font-weight:bold'>" . $output[ $lineNumber ] . "</span>";

				if ( $lineNumber > 20 )
				{
					$_lineNumber = $lineNumber - 20;
					$output[ $_lineNumber ] = "<a name='line{$lineNumber}'></a>" . $output[ $_lineNumber ];
				}
			}
		}

		if ( count( $output ) )
		{
			if ( $lineNumber )
			{
				print "<h4>Parse Error on line: <a href='#line{$lineNumber}'>" . $lineNumber . "</a></h4>";
			}
			else
			{
				print "<h4>" . $errorText . "</h4>";
			}

			print $openDiv;

			foreach( $output as $number => $data )
			{
				print "<span style='color:#BBB'>".$number."</span>" . ' : ' . $data . "<br />";
			}

			print "</div>";

			exit();
		}

		/* Still here? */
		print "<h4>" . $errorText . "</h4>";
		print htmlspecialchars( $evalCode );
		exit();
	}

	/**
	 * Get current memory usage
	 *
	 * @access 	public
	 * @return	integer		Current memory usage
	 */
	static public function getMemoryDebugFlag()
	{
		if ( IPS_MEMORY_START AND function_exists( 'memory_get_usage' ) )
		{
			return memory_get_usage();
		}
	}

	/**
	 * Set a memory debug flag
	 *
	 * @access 	public
	 * @param 	string		Comment to set
	 * @param	integer		Memory usage to compare against
	 * @return	int			Memory used
	 */
	static public function setMemoryDebugFlag( $comment, $init_usage=0 )
	{
		if ( IPS_MEMORY_START AND function_exists( 'memory_get_usage' ) )
		{
			$_END  = memory_get_usage();
			$_USED = $_END - $init_usage;
			self::$memory_debug[] = array( $comment, $_USED );
			return $_USED;
		}
	}

	/**
	 * Start a timer
	 *
	 * @access 	public
	 * @return	void
	 */
	static public function startTimer()
    {
        $mtime = microtime ();
        $mtime = explode (' ', $mtime);
        $mtime = $mtime[1] + $mtime[0];
        self::$_starttime = $mtime;
    }

	/**
	 * Stop the timer
	 *
	 * @access 	public
	 * @return	integer		Length of time
	 */
    static public function endTimer()
    {
        $mtime = microtime ();
        $mtime = explode (' ', $mtime);
        $mtime = $mtime[1] + $mtime[0];
        $endtime = $mtime;
        $totaltime = round (($endtime - self::$_starttime), 5);
        return $totaltime;
    }

	/**
	 * Start a timer (return value instead of storing locally)
	 *
	 * @access 	public
	 * @return	integer		Time
	 */
	static public function startTimerInstance()
    {
        $mtime = microtime ( true );
        $mtime = explode (' ', $mtime);
        $mtime = isset( $mtime[1] ) ? $mtime[1] + $mtime[0] : $mtime[0];
        return $mtime;
    }

	/**
	 * Stop the timer (compare against provided time instead of stored time)
	 *
	 * @access 	public
	 * @param	integer		Start time
	 * @return	integer		Length of time
	 */
    static public function endTimerInstance( $startTime=0 )
    {
        $mtime = microtime ( true );
        $mtime = explode (' ', $mtime);
        $mtime = isset( $mtime[1] ) ? $mtime[1] + $mtime[0] : $mtime[0];
        $endtime = $mtime;

        $totaltime = round (($endtime - $startTime), 5);
        return $totaltime;
    }

	/**
	 * Retrieve server load and update cache if appropriate
	 *
	 * @access	public
	 * @return	string	Server load
	 */
	static public function getServerLoad()
	{
		$load_limit			= "--";
		
		//-----------------------------------------
		// Check cache first...
		//-----------------------------------------
        
        $cache	= ipsRegistry::instance()->cache()->getCache('systemvars');

        if( $cache['loadlimit'] )
        {
	        $loadinfo	= explode( "-", $cache['loadlimit'] );
	        
	        if ( intval($loadinfo[1]) > (time() - 30) )
	        {
				//-----------------------------------------
				// Cache is less than 30 secs old, use it
				//-----------------------------------------

		        $server_load_found	= 1;
		        $load_limit			= $loadinfo[0];
			}
		}
	        
		//-----------------------------------------
		// No cache or it's old, check real time
		//-----------------------------------------
		
		if( !$server_load_found )
		{
	        # @ supressor stops warning in > 4.3.2 with open_basedir restrictions
	        
        	if ( @file_exists('/proc/loadavg') )
        	{
        		if ( $fh = @fopen( '/proc/loadavg', 'r' ) )
        		{
        			$data = @fread( $fh, 6 );

        			@fclose( $fh );
        			
        			$load_avg	= explode( " ", $data );
        			$load_limit	= trim($load_avg[0]);
        		}
        	}
        	else if( strpos( strtolower( PHP_OS ), 'win' ) === 0 )
        	{
		        /*---------------------------------------------------------------
		        | typeperf is an exe program that is included with Win NT,
		        |	XP Pro, and 2K3 Server.  It can be installed on 2K from the
		        |	2K Resource kit.  It will return the real time processor
		        |	Percentage, but will take 1 second processing time to do so.
		        |	This is why we shall cache it, and check only every 2 mins.
		        |
		        |	Can also be obtained from COM, but it's extremely slow...
		        ---------------------------------------------------------------*/
	        	
	        	$serverstats = @shell_exec("typeperf \"Processor(_Total)\% Processor Time\" -sc 1");
	        	
	        	if( $serverstats )
	        	{
					$server_reply	= explode( "\n", str_replace( "\r", "", $serverstats ) );
					$serverstats	= array_slice( $server_reply, 2, 1 );
					$statline		= explode( ",", str_replace( '"', '', $serverstats[0] ) );
					$load_limit		= round( $statline[1], 4 );
				}
			}
        	else
        	{
				if ( $serverstats = @exec("uptime") )
				{
					preg_match( "/(?:averages)?\: ([0-9\.]+)(,|)[\s]+([0-9\.]+)(,|)[\s]+([0-9\.]+)/", $serverstats, $load );

					$load_limit = $load[1];
				}
			}
			
			$cache['loadlimit']	= $load_limit . "-" . time();
			
			if( $load_limit )
			{
				ipsRegistry::instance()->cache()->setCache( 'systemvars', $cache, array( 'array' => 1 ) );
			}
		}
		
		return $load_limit;
	}
}

/**
* IPSCookie
*
* This deals with saving and writing cookies
*/
class IPSCookie
{
	/**
	 * Sensitive cookies
	 *
	 * @access	public
	 * @var		array 		Sensitive cookies
	 */
	static public $sensitive_cookies = array();
	
	/**
	 * Handle cookies internally
	 * so that when you SET one it is available to GET in the same process
	 *
	 * @access	private
	 * @var		array
	 */
	static private $_cookiesSet = array();

    /**
	 * Set a cookie.
	 *
	 * Abstract layer allows us to do some checking, etc
	 *
	 * @access	public
	 * @param	string		Cookie name
	 * @param	string		Cookie value
	 * @param	integer		Is sticky flag
	 * @param	integer		Number of days to expire cookie in
	 * @return	void
	 * @since	2.0
	 */
    static public function set( $name, $value="", $sticky=1, $expires_x_days=0 )
    {
		//-----------------------------------------
		// Check
		//-----------------------------------------

        if ( isset( ipsRegistry::$settings['no_print_header'] ) AND ipsRegistry::$settings['no_print_header'] )
        {
        	return;
        }
		
		/* Update internal array */
		self::$_cookiesSet[ $name ] = $value;
		
		//-----------------------------------------
		// Auto serialize arrays
		//-----------------------------------------

		if ( is_array( $value ) )
		{
			$value = serialize( $value );
		}

		//-----------------------------------------
		// Set vars
		//-----------------------------------------

        if ( $sticky == 1 )
        {
        	$expires = time() + 60*60*24*365;
        }
		else if ( $expires_x_days )
		{
			$expires = time() + ( $expires_x_days * 86400 );
		}
		else
		{
			$expires = FALSE;
		}

		//-----------------------------------------
		// Finish up...
		//-----------------------------------------

        ipsRegistry::$settings['cookie_domain'] =  ipsRegistry::$settings['cookie_domain'] == "" ? ""  : ipsRegistry::$settings['cookie_domain'] ;
        ipsRegistry::$settings['cookie_path'] =  ipsRegistry::$settings['cookie_path']   == "" ? "/" : ipsRegistry::$settings['cookie_path'] ;

		//-----------------------------------------
		// Set the cookie
		//-----------------------------------------

		if ( in_array( $name, self::$sensitive_cookies ) )
		{
			if ( PHP_VERSION < 5.2 )
			{
				if ( ipsRegistry::$settings['cookie_domain'] )
				{
					@setcookie( ipsRegistry::$settings['cookie_id'].$name, $value, $expires, ipsRegistry::$settings['cookie_path'], ipsRegistry::$settings['cookie_domain'] . '; HttpOnly' );
				}
				else
				{
					@setcookie( ipsRegistry::$settings['cookie_id'].$name, $value, $expires, ipsRegistry::$settings['cookie_path'] );
				}
			}
			else
			{
				@setcookie( ipsRegistry::$settings['cookie_id'].$name, $value, $expires, ipsRegistry::$settings['cookie_path'], ipsRegistry::$settings['cookie_domain'], NULL, TRUE );
			}
		}
		else
		{
			@setcookie( ipsRegistry::$settings['cookie_id'].$name, $value, $expires, ipsRegistry::$settings['cookie_path'], ipsRegistry::$settings['cookie_domain']);
		}
    }

    /**
	 * Get a cookie.
	 * Abstract layer allows us to do some checking, etc
	 *
	 * @access	public
	 * @param	string		Cookie name
	 * @return	mixed
	 * @since	2.0
	 */
    static public function get($name)
    {
		/* Check internal data first */
		if ( isset( self::$_cookiesSet[ $name ] ) )
		{
			return self::$_cookiesSet[ $name ];
		}
    	else if ( isset( $_COOKIE[ipsRegistry::$settings['cookie_id'].$name] ) )
    	{
			$_value = $_COOKIE[ ipsRegistry::$settings['cookie_id'].$name ];

    		if ( substr( $_value, 0, 2 ) == 'a:' )
    		{
				return unserialize( stripslashes( urldecode( $_value ) ) );
    		}
    		else
    		{
				return IPSText::parseCleanValue( urldecode( $_value ) );
    		}
    	}
    	else
    	{
    		return FALSE;
    	}
    }
}

/**
* IPSText
*
* This deals with cleaning and parsing text items.
*/
class IPSText
{
	/**
	 * Class Convert Object
	 *
	 * @access	private
	 * @var		object
	 */
	static private $classConvertCharset;

	/**
	 * Default document character set
	 *
	 * @access	public
	 * @var		string		Character set
	 */
	static public $gb_char_set = 'UTF-8';

	/**
	 * Remove dodgy control characters?
	 *
	 * @access	public
	 * @var		boolean		Remove emulated spaces (e.g. alt+160)
	 */
	static public $strip_space_chr = true;

	/**
	 * Classes
	 *
	 * @access	private
	 * @var		array
	 */
	static private $_internalClasses = array();

	/**
	 * Ensure no one can create this as an object
	 *
	 * @access	private
	 * @return	void
	 */
	private function __construct() {}

	/**
	 * Simple JSON encode for when its not possible to convert data
	 * into UTF-8 (for example polls that display the contents, etc)
	 * This should only used for light lifting.
	 *
	 * @access	public
	 * @param	array   Simple array
	 * @return	object
	 */
	static public function simpleJsonEncode( $array )
	{
		$final = array();
		
		if ( is_array( $array ) )
		{
			foreach( $array as $k => $v )
			{
				$k = str_replace( '"', '\"', $k );
				
				if ( is_array( $v ) )
				{
					$v = self::simpleJsonEncode( $v );
				}
				else
				{
					$v = str_replace( '"', '\"', $v );
					$v = str_replace( "\n", '\n', str_replace( "\r", '', $v ) );
					$v = '"' . $v . '"';
				}
				
				$final[] = '"' . $k . '":' . $v . '';
			}
			
			return '{' . implode( ",", $final ) . '}';
		}
	}
	
	/**
	 * Get helper classes
	 * Used here to allow classes to be loaded and used as-and-when they're needed
	 *
	 * @access	public
	 * @param	mixed		Name of item requested
	 * @return	object
	 */
	static public function getTextClass( $name )
	{
		if ( isset( self::$_internalClasses[ $name ] ) && is_object( self::$_internalClasses[ $name ] ) )
		{
			return self::$_internalClasses[ $name ];
		}
		else
		{
			switch( $name )
			{
				default:
				case 'bbcode':
					$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . "sources/handlers/han_parse_bbcode.php", 'parseBbcode' );
			        $_class                      =  new $classToLoad( ipsRegistry::instance() );
			        $_class->allow_update_caches = 1;
			        $_class->bypass_badwords     = ipsRegistry::instance()->member() ? intval( ipsRegistry::instance()->member()->getProperty('g_bypass_badwords') ) : 0;
				break;
				case 'editor':
					$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . "sources/handlers/han_editor.php", 'hanEditor' );
					$_class = new $classToLoad( ipsRegistry::instance() );
			        $_class->init();
				break;
				case 'email':
					$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . "sources/handlers/han_email.php", 'hanEmail' );
					$_class = new $classToLoad( ipsRegistry::instance() );
			        $_class->init();
				break;
			}

			if ( is_object( $_class ) )
			{
				self::$_internalClasses[ $name ] = $_class;

				return self::$_internalClasses[ $name ];
			}
		}
	}
	
	/**
	 * Encode for saving data into the DB that will be exported to XML
	 *
	 * Mostly used to ensure that data designed for UTF-8 XML files is actually stored as UTF-8 from
	 * 'flat' files that may not be saved as UTF-8.
	 * Most likely this will have to be expanded to include different char sets eventually.
	 *
	 * @access	public
	 * @param	string		Data in
	 * @return	string		Data out
	 */
	static public function encodeForXml( $string )
	{
		if ( strtolower( IPS_DOC_CHAR_SET ) == 'utf-8' )
		{
			$string = utf8_encode( $string );
		}
		
		return $string;
	}
	
	/**
	 * SEO Clean up
	 *
	 * @access	public
	 * @param	string		Raw SEO title or text
	 * @return	string		Cleaned up SEO title
	 * @deprecated			Will most likely be removed in a future version
	 */
	static public function seoClean( $text )
	{
		$text = str_replace( " ", "-", $text );
		/* Ensure we don't have /_/ anywhere in the URL */
		$text = str_replace( "_", "-", $text );

		$text = utf8_encode( $text );

		return $text;
	}

	/**
	 * Make an SEO title for use in the URL
	 * We parse them even if friendly urls are off so that the data is there when you do switch it on
	 *
	 * @access	public
	 * @param	string		Raw SEO title or text
	 * @return	string		Cleaned up SEO title
	 */
	static public function makeSeoTitle( $text )
	{
		if ( ! $text )
		{
			return '';
		}

		/* Strip all HTML tags first */
		$text = strip_tags($text);
			
		/* Preserve %data */
		$text = preg_replace('#%([a-fA-F0-9][a-fA-F0-9])#', '-xx-$1-xx-', $text);
		$text = str_replace( array( '%', '`' ), '', $text);
		$text = preg_replace('#-xx-([a-fA-F0-9][a-fA-F0-9])-xx-#', '%$1', $text);

		/* Convert accented chars */
		$text = self::convertAccents($text);
		
		/* Convert it */
		if ( self::isUTF8( $text )  )
		{
			if ( function_exists('mb_strtolower') )
			{
				$text = mb_strtolower($text, 'UTF-8');
			}

			$text = self::utf8Encode( $text, 250 );
		}

		/* Finish off */
		$text = strtolower($text);
		
		if ( strtolower( IPS_DOC_CHAR_SET ) == 'utf-8' )
		{
			$text = preg_replace( '#&.+?;#'        , '', $text );
			$text = preg_replace( '#[^%a-z0-9 _-]#', '', $text );
		}
		else
		{
			/* Remove &#xx; and &#xxx; but keep &#xxxx; */
			$text = preg_replace( '/&#(\d){2,3};/', '', $text );
			$text = preg_replace( '#[^%&\#;a-z0-9 _-]#', '', $text );
			$text = str_replace( array( '&quot;', '&amp;'), '', $text );
		}
		
		$text = str_replace( array( '`', ' ', '+', '.', '?', '_', '#' ), '-', $text );
		$text = preg_replace( "#-{2,}#", '-', $text );
		$text = trim($text, '-');

		IPSDebug::addMessage( "<span style='color:red'>makeSeoTitle ($text) called</span>" );
		
		return ( $text ) ? $text : '-';
	}

	/**
	 * Seems like UTF-8?
	 * hmdker at gmail dot com {@link php.net/utf8_encode}
	 *
	 * @access	public
	 * @param	string		Raw text
	 * @return	boolean
	 */
	static public function isUTF8($str) {
	    $c=0; $b=0;
	    $bits=0;
	    $len=strlen($str);
	    for($i=0; $i<$len; $i++)
	    {
	        $c=ord($str[$i]);

	        if($c > 128)
	        {
	            if(($c >= 254)) return false;
	            elseif($c >= 252) $bits=6;
	            elseif($c >= 248) $bits=5;
	            elseif($c >= 240) $bits=4;
	            elseif($c >= 224) $bits=3;
	            elseif($c >= 192) $bits=2;
	            else return false;

	            if(($i+$bits) > $len) return false;

	            while( $bits > 1 )
	            {
	                $i++;
	                $b = ord($str[$i]);
	                if($b < 128 || $b > 191) return false;
	                $bits--;
	            }
	        }
	    }

	    return true;
	}

	/**
	 * Converts accented characters into their plain alphabetic counterparts
	 *
	 * @access	public
	 * @param	string		Raw text
	 * @return	string		Cleaned text
	 */
	static public function convertAccents($string)
	{
		if ( ! preg_match('/[\x80-\xff]/', $string) )
		{
			return $string;
		}

		if ( self::isUTF8( $string) )
		{
			$_chr = array(
							/* Latin-1 Supplement */
							chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
							chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
							chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
							chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
							chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
							chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
							chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
							chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
							chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
							chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
							chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
							chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
							chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
							chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
							chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
							chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
							chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
							chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
							chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
							chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
							chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
							chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
							chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
							chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
							chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
							chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
							chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
							chr(195).chr(191) => 'y',
							/* Latin Extended-A */
							chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
							chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
							chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
							chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
							chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
							chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
							chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
							chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
							chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
							chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
							chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
							chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
							chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
							chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
							chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
							chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
							chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
							chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
							chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
							chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
							chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
							chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
							chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
							chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
							chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
							chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
							chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
							chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
							chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
							chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
							chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
							chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
							chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
							chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
							chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
							chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
							chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
							chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
							chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
							chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
							chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
							chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
							chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
							chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
							chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
							chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
							chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
							chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
							chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
							chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
							chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
							chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
							chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
							chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
							chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
							chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
							chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
							chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
							chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
							chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
							chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
							chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
							chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
							chr(197).chr(190) => 'z', chr(197).chr(191) => 's',
							/* Euro Sign */
							chr(226).chr(130).chr(172) => 'E',
							/* GBP (Pound) Sign */
							chr(194).chr(163) => '' );

			$string = strtr($string, $_chr);
		}
		else
		{
			$_chr      = array();
			$_dblChars = array();
			
			/* We assume ISO-8859-1 if not UTF-8 */
			$_chr['in'] =   chr(128).chr(131).chr(138).chr(142).chr(154).chr(158)
							.chr(159).chr(162).chr(165).chr(181).chr(192).chr(193).chr(194)
							.chr(195).chr(199).chr(200).chr(201).chr(202)
							.chr(203).chr(204).chr(205).chr(206).chr(207).chr(209).chr(210)
							.chr(211).chr(212).chr(213).chr(217).chr(218)
							.chr(219).chr(220).chr(221).chr(224).chr(225).chr(226).chr(227)
							.chr(231).chr(232).chr(233).chr(234).chr(235)
							.chr(236).chr(237).chr(238).chr(239).chr(241).chr(242).chr(243)
							.chr(244).chr(245).chr(249).chr(250).chr(251)
							.chr(252).chr(253).chr(255).chr(191).chr(182).chr(179).chr(166)
							.chr(230).chr(198).chr(175).chr(172).chr(188)
							.chr(163).chr(161).chr(177);

			$_chr['out'] = "EfSZszYcYuAAAACEEEEIIIINOOOOUUUUYaaaaceeeeiiiinoooouuuuyyzslScCZZzLAa";

			$string           = strtr( $string, $_chr['in'], $_chr['out'] );
			$_dblChars['in']  = array( chr(140), chr(156), chr(196), chr(197), chr(198), chr(208), chr(214), chr(216), chr(222), chr(223), chr(228), chr(229), chr(230), chr(240), chr(246), chr(248), chr(254));
			$_dblChars['out'] = array('Oe', 'oe', 'Ae', 'Aa', 'Ae', 'DH', 'Oe', 'Oe', 'TH', 'ss', 'ae', 'aa', 'ae', 'dh', 'oe', 'oe', 'th');
			$string           = str_replace($_dblChars['in'], $_dblChars['out'], $string);
		}
				
		return $string;
	}

	/**
	 * Manually utf8 encode to a specific length
	 * Based on notes found at php.net
	 *
	 * @access	public
	 * @param	string		Raw text
	 * @param	int			Length
	 * @return	string
	 */
	static public function utf8Encode( $string, $len=0 )
	{
		$_unicode       = '';
		$_values        = array();
		$_nOctets       = 1;
		$_unicodeLength = 0;
 		$stringLength   = strlen( $string );

		for ( $i = 0 ; $i < $stringLength ; $i++ )
		{
			$value = ord( $string[ $i ] );

			if ( $value < 128 )
			{
				if ( $len && ( $_unicodeLength >= $len ) )
				{
					break;
				}

				$_unicode .= chr($value);
				$_unicodeLength++;
			}
			else
			{
				if ( count( $_values ) == 0 )
				{
					$_nOctets = ( $value < 224 ) ? 2 : 3;
				}

				$_values[] = $value;

				if ( $len && ( $_unicodeLength + ($_nOctets * 3) ) > $len )
				{
					break;
				}

				if ( count( $_values ) == $_nOctets )
				{
					if ( $_nOctets == 3 )
					{
						$_unicode .= '%' . dechex($_values[0]) . '%' . dechex($_values[1]) . '%' . dechex($_values[2]);
						$_unicodeLength += 9;
					}
					else
					{
						$_unicode .= '%' . dechex($_values[0]) . '%' . dechex($_values[1]);
						$_unicodeLength += 6;
					}

					$_values  = array();
					$_nOctets = 1;
				}
			}
		}

		return $_unicode;
	}
	
	/**
	 * Converts UFT-8 into HTML entities (&#1xxx;) for correct display in browsers
	 *
	 * @access	 public
	 * @param	 string 		UTF8 Encoded string
	 * @return	 string 		..converted into HTML entities (similar to what a browser does with POST)
	 */
	public static function utf8ToEntities($string)
	{ 
		/*
 		 * @see http://en.wikipedia.org/wiki/UTF-8#Description
 		 * @link http://community.invisionpower.com/tracker/issue-23681-possible-addition/
 		 */
		# Four-byte chars
		$string = preg_replace( "/([\360-\364])([\200-\277])([\200-\277])([\200-\277])/e",  "'&#' . ( ( ord('\\1') - 240 ) * 262144 + ( ord('\\2') - 128 ) * 4096 + ( ord('\\3') - 128 ) * 64 + ( ord('\\4') - 128 ) ) . ';'", $string );
        
    	/* Three byte chars */
		$string = preg_replace( "/([\340-\357])([\200-\277])([\200-\277])/e", "'&#'.((ord('\\1')-224)*4096 + (ord('\\2')-128)*64 + (ord('\\3')-128)).';'", $string ); 

    	/* Two byte chars */
		$string = preg_replace("/([\300-\337])([\200-\277])/e", "'&#'.((ord('\\1')-192)*64+(ord('\\2')-128)).';'", $string); 

    	return $string; 
	}
	
	/**
	 * Returns an MD5 hash of content which has whitespace stripped.
	 * This is used in some classes to determine if content has changed without
	 * whitespace changes triggering it.
	 *
	 * @access	public
	 * @param	string 		Incoming text
	 * @return	string		MD5 hash of whitespace stripped content
	 */
	public static function contentToMd5( $t )
	{
		return md5( trim( preg_replace( "#[\s\t\n\r]#", "", $t ) ) );
	}

	/**
	 * Replace Recursively
	 *
	 * @access	public
	 * @param	string		Text to search in
	 * @param	string		Opening text to search for. (Example: <a href=)
	 * @param	string		Closing text to search for. (Example: >)
	 * @param	mixed		Call back function that handles the replacement. If using a class, pass array( $classname, $function ) THIS MUST BE A STATIC FUNCTION
	 * @return	string		Replaced text
	 * <code>
	 * # We want to replace all instances of <a href="http://www.domain.com"> with <a href="javascript:goLoad('domain.com')">
	 * $text = IPSText::replaceRecursively( $text, "<a href=", ">", array( 'myClass', 'replaceIt' ) );
	 * class myClass {
	 *	static function replaceIt( $text, $openText, $closeText )
	 *	{
	 *		# $text contains the matched text between the tags, eg: "http://www.domain.com"
	 *		# $openText contains the searched for opening, eg: <a href
	 *		# $closeText contains the searched for closing, eg: >
	 *		# Remove http...
	 *		$text = str_replace( 'http://', '', $text )
	 *		# Remove quotes
	 * 		$text = str_replace( array( '"', "'" ), '', $text );
	 *		return '"javascript:goLoad(\'' . $text . '\')"';
	 *	}
	 * }
	 * </code>
	 */
	public static function replaceRecursively( $text, $textOpen, $textClose, $callBackFunction )
	{
		//----------------------------------------
		// INIT
		//----------------------------------------

		# Tag specifics
		$foundOpenText_pointer  = 0;
		$foundCloseText_pointer = 0;
		$foundOpenTextRecurse_pointer = 0;

		//----------------------------------------
		// Keep the server busy for a while
		//----------------------------------------

		while ( 1 == 1 )
		{
			# Reset pointer
			$startOfTextAfterOpenText_pointer = 0;

			# See if we have any 'textOpen' at all
			$foundOpenText_pointer = strpos( $text, $textOpen, $foundCloseText_pointer );

			# No?
			if ( $foundOpenText_pointer === FALSE )
			{
				break;
			}

			# Do we have any close text?
			$foundCloseText_pointer = strpos( $text, $textClose, $foundOpenText_pointer );

			# No?
			if ( $foundCloseText_pointer === FALSE )
			{
				return $text;
			}

			# Reset pointer for text between the open and close text
			$startOfTextAfterOpenText_pointer = $foundOpenText_pointer + strlen( $textOpen );

			# Check recursively
			$foundOpenTextRecurse_pointer = $startOfTextAfterOpenText_pointer;

			while ( 1 == 1 )
			{
				# Got any open text again?
				$foundOpenTextRecurse_pointer = strpos( $text, $textOpen, $foundOpenTextRecurse_pointer );

				# No?
				if ( $foundOpenTextRecurse_pointer === FALSE OR $foundOpenTextRecurse_pointer >= $foundCloseText_pointer )
				{
					break;
				}

				# Yes! Reset recursive pointer
				$foundCloseTextRecurse_pointer = $foundCloseText_pointer + strlen( $textClose );

				# Yes! Reset close normal pointer to next close tag FROM the last found close point
				$foundCloseText_pointer = strpos( $text, $textClose, $foundCloseTextRecurse_pointer );

				# Make sure we have a closing text
				if ( $foundCloseText_pointer === FALSE )
				{
					return $text;
				}

				$foundOpenTextRecurse_pointer += strlen( $textOpen );
			}

			# This is the text between the open text and close text
			$foundText  = substr( $text, $startOfTextAfterOpenText_pointer, $foundCloseText_pointer - $startOfTextAfterOpenText_pointer );

			# Recurse
			if ( strpos( $foundText, $textOpen ) !== FALSE )
			{
				$foundText = IPSText::replaceRecursively( $foundText, $textOpen, $textClose, $callBackFunction );
			}

			# Run the call back...
			$_newText  = call_user_func( $callBackFunction, $foundText, $textOpen, $textClose );

			# Run the replacement
			$text = substr_replace( $text, $_newText, $foundOpenText_pointer, ( $foundCloseText_pointer - $foundOpenText_pointer ) + strlen( $textClose )  );

			# Reset pointer
			$foundCloseText_pointer = $foundOpenText_pointer + strlen($_newText);
		}

		return $text;
	}

	/**
	 * Reset Text Classes
	 *
	 * @access	public
	 * @param	string		Classname to search for
	 * @return	boolean		True if successful, false if not
	 */
	static public function resetTextClass( $name )
	{
		if ( ! is_object( self::$_internalClasses[ $name ] ) )
		{
			return false;
		}

		switch( $name )
		{
			default:
			case 'bbcode':
				self::$_internalClasses[ $name ]->allow_cache_updates	= 1;
				self::$_internalClasses[ $name ]->bypass_badwords		= intval( ipsRegistry::instance()->member()->getProperty('g_bypass_badwords') );
				self::$_internalClasses[ $name ]->parse_smilies			= 1;
				self::$_internalClasses[ $name ]->parse_nl2br			= 1;
				self::$_internalClasses[ $name ]->parse_html			= 0;
				self::$_internalClasses[ $name ]->parse_bbcode			= 1;
				self::$_internalClasses[ $name ]->parsing_section		= 'post';
				self::$_internalClasses[ $name ]->error					= '';
				self::$_internalClasses[ $name ]->parsing_mgroup		= '';
				self::$_internalClasses[ $name ]->parsing_mgroup_others	= '';
			break;
			case 'editor':
				self::$_internalClasses[ $name ]->error = '';
			break;
		}

		return true;
	}

	/**
	 * Clean _GET _POST key
	 *
	 * @access	public
	 * @param	string		Key name
	 * @return	string		Cleaned key name
	 * @since	2.1
	 */
    static public function parseCleanKey($key)
    {
    	if ( $key == "" )
    	{
    		return "";
    	}

    	$key = htmlspecialchars( urldecode($key) );
    	$key = str_replace( ".."           , ""  , $key );
    	$key = preg_replace( "/\_\_(.+?)\_\_/"  , ""  , $key );
    	$key = preg_replace( "/^([\w\.\-\_]+)$/", "$1", $key );

    	return $key;
    }

    /**
	 * Clean _GET _POST value
	 *
	 * @access	public
	 * @param	string		Input
	 * @param	bool		Also run postParseCleanValue
	 * @return	string		Cleaned Input
	 * @since	2.1
	 */
    static public function parseCleanValue( $val, $postParse=true )
    {
    	if ( $val == "" )
    	{
    		return "";
    	}

    	$val = str_replace( "&#032;", " ", IPSText::stripslashes($val) );

		# Convert all carriage return combos
		$val = str_replace( array( "\r\n", "\n\r", "\r" ), "\n", $val );

    	$val = str_replace( "&"				, "&amp;"         , $val );
    	$val = str_replace( "<!--"			, "&#60;&#33;--"  , $val );
    	$val = str_replace( "-->"			, "--&#62;"       , $val );
    	$val = str_ireplace( "<script"	    , "&#60;script"   , $val );
    	$val = str_replace( ">"				, "&gt;"          , $val );
    	$val = str_replace( "<"				, "&lt;"          , $val );
    	$val = str_replace( '"'				, "&quot;"        , $val );
    	$val = str_replace( "\n"			, "<br />"        , $val ); // Convert literal newlines
    	$val = str_replace( "$"				, "&#036;"        , $val );
    	$val = str_replace( "!"				, "&#33;"         , $val );
    	$val = str_replace( "'"				, "&#39;"         , $val ); // IMPORTANT: It helps to increase sql query safety.

    	if ( IPS_ALLOW_UNICODE )
		{
			$val = preg_replace("/&amp;#([0-9]+);/s", "&#\\1;", $val );

			//-----------------------------------------
			// Try and fix up HTML entities with missing ;
			//-----------------------------------------

			$val = preg_replace( "/&#(\d+?)([^\d;])/i", "&#\\1;\\2", $val );
		}
		
		//-----------------------------------------
		// Shortcut to auto run other cleaning
		//-----------------------------------------
		
		if( $postParse )
		{
			$val	= IPSText::postParseCleanValue( $val );
		}

    	return $val;
    }
    
    /**
	 * Clean _GET _POST value after settings loaded
	 *
	 * @access	public
	 * @param	string		Input
	 * @return	string		Cleaned Input
	 * @since	2.1
	 */
    static public function postParseCleanValue($val)
    {
    	if ( $val == "" )
    	{
    		return "";
    	}

		/* This looks wrong but it's correct. During FURL set up in registry this function is called before settings are loaded
		 * and we want to strip hidden chars in this instance, so.. */
    	if ( ! isset( ipsRegistry::$settings['strip_space_chr'] ) OR ipsRegistry::$settings['strip_space_chr'] )
    	{
			$val = IPSText::removeControlCharacters( $val );
    	}

    	return $val;
    }

	/**
	 * Check email address to see if it seems valid
	 *
	 * @access	public
	 * @param	string		Email address
	 * @return	boolean
	 * @since	2.0
	 */
	static public function checkEmailAddress( $email = "" )
	{
		$email = trim($email);

		$email = str_replace( " ", "", $email );

		//-----------------------------------------
		// Check for more than 1 @ symbol
		//-----------------------------------------

		if ( substr_count( $email, '@' ) > 1 )
		{
			return FALSE;
		}

    	if ( preg_match( "#[\;\#\n\r\*\'\"<>&\%\!\(\)\{\}\[\]\?\\/\s]#", $email ) )
		{
			return FALSE;
		}
    	else if ( preg_match( "/^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,4})(\]?)$/", $email) )
    	{
    		return TRUE;
    	}
    	else
    	{
    		return FALSE;
    	}
	}
	
	/**
	 * Function to trim text around a word or phrase
	 *
	 * @access	private
	 * @param	string	$haystack	Text
	 * @param	string	$needle		Phrase
	 * @return	string
	 **/
	static public function truncateTextAroundPhrase( $haystack, $needle )
	{
		/* Base on words */
		$haystack = explode( " ", $haystack );

		if( count( $haystack ) > 21 )
		{
			$_term_at = IPSLib::arraySearchLoose( $needle, $haystack );

			if( $_term_at - 11 > 0 )
			{
				$begin = array_splice( $haystack, 0, $_term_at - 11 );
				
				/* The term position will have changed now */
				$_term_at = IPSLib::arraySearchLoose( $needle, $haystack );
			}

			if( $_term_at + 11 < count( $haystack ) )
			{
				$end   = array_splice( $haystack, $_term_at + 11, count( $haystack ) );
			}
		}
		else
		{
			$begin = array();
			$end   = array();
		}

		$haystack = implode( " ", $haystack );
		
		if( is_array( $begin ) && count( $begin ) )
		{
			$haystack = '...' . $haystack;
		}
		
		if( is_array( $end ) && count( $end ) )
		{
			$haystack = $haystack . '...';
		}
		
		return $haystack;
	}
	
	/**
	 * Replaces text with highlighted blocks
	 *
	 * @access	public
	 * @param	string		Incoming Content
	 * @param	string		HL attribute
	 * @return	string		Formatted text
	 * @since	2.2.0
	 */
	static public function searchHighlight( $text, $highlight )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------

		$highlight  = self::parseCleanValue( urldecode( $highlight ) );
		$loosematch = 1;//strstr( $highlight, '*' ) ? 1 : 0;
		$keywords   = str_replace( '*', '', str_replace( "+", " ", str_replace( "++", "+", str_replace( '-', '', trim($highlight) ) ) ) );
		$keywords	= str_replace( '&quot;', '', str_replace( '\\', '&#092;', str_replace( '&amp;quot;', '', $keywords ) ) );
		$word_array = array();
		$endmatch   = "(.)?";
		$beginmatch = "(.)?";

		//-----------------------------------------
		// Get rid of links first...
		//-----------------------------------------
		
		$_storedUrls = array();
		
		preg_match_all( "/<a href=['\"](.+?)[\"']([^>]*?)>/is", $text, $_urls );

		for ( $i = 0; $i < count($_urls[0]); $i++ )
		{
			$_bleh	= md5( uniqid( microtime(), true ) );
			
			$text	= str_replace( $_urls[0][$i], "--URL::{$_bleh}-- ", $text );
			
			$_storedUrls[ $_bleh ]	= $_urls[0][$i];
		}

		//-----------------------------------------
		// Go!
		//-----------------------------------------

		if ( $keywords )
		{
			if ( preg_match("/,(and|or),/i", $keywords) )
			{
				while ( preg_match("/\s+(and|or)\s+/i", $keywords, $match) )
				{
					$word_array = explode( " ".$match[1]." ",	$keywords );
					$keywords   = str_replace( $match[0], '',	$keywords );
				}
			}
			else if ( strstr( $keywords, ' ' ) )
			{
				$word_array = explode( ' ', str_replace( '  ', ' ', $keywords ) );
			}
			else
			{
				$word_array[] = $keywords;
			}

			if ( ! $loosematch )
			{
				$beginmatch = "(^|\s|\>|;|\])";
				$endmatch   = "(\s|,|\.|!|<br|&|$)";
			}

			if ( is_array($word_array) )
			{
				/* We'll use this to match against, so we don't break images with the term in the image name */
				$textForMatch = strip_tags( IPSText::getTextClass( 'bbcode' )->stripAllTags( $text ) );
				
				foreach ( $word_array as $keywords )
				{
					/* We don't want to highlight small words, they're usually noise and it can produce memory errors with single chars being highlighted 
					 * Correction: We don't want to highlight them unless user used double quotes in search term */
					if( strpos( $highlight, '&amp;quot;' ) === false AND strlen( $keywords ) < ipsRegistry::$settings['min_search_word'] )
					{
						continue;
					}
					
					/* Make sure we're not trying to process an empty keyword */
					if( ! $keywords )
					{
						continue;
					}

					preg_match_all( "/{$beginmatch}(".preg_quote($keywords, '/')."){$endmatch}/is", $textForMatch, $matches );

					for ( $i = 0; $i < count($matches[0]); $i++ )
					{
						$text = str_ireplace( $matches[0][$i], $matches[1][$i] . "<span class='searchlite'>" . $matches[2][$i] . "</span>" . $matches[3][$i], $text );
					}
				}
			}
		}

		//-----------------------------------------
		// Fix links
		//-----------------------------------------
		
		if( count($_storedUrls) )
		{
			foreach( $_storedUrls as $k => $v )
			{
				$text	= str_replace( "--URL::{$k}-- ", $v, $text );
			}
		}

		return $text;
	}

	/**
	 * Check a URL to make sure it's not all hacky
	 *
	 * @access	public
	 * @param	string		Input String
	 * @return	boolean
	 * @since	2.1.0
	 */
	static public function xssCheckUrl( $url )
	{
		// This causes problems if people submit bbcode with urlencoded items that are valid
		// e.g.: http://www.google.com/search?q=site%3Aipb3preview.ipslink.com+-%22Viewing+Profile%22
		// %22 gets changed into " and then this fails, even though this is a valid url
		// $url = trim( urldecode( $url ) );
		$url	= trim( $url );

		/* Test for http://%XX */
		if ( stristr( $url, 'http://%' ) )
		{
			return FALSE;
		}
		
		/* Test for http://&XX */
		if ( stristr( $url, 'http://&' ) )
		{
			return FALSE;
		}

		if ( ! preg_match( "#^(http|https|news|ftp)://(?:[^<>\"]+|[a-z0-9/\._\- !&\#;,%\+\?:=]+)$#iU", $url ) )
		{
			return FALSE;
		}

		return TRUE;
	}
	
	/**
	 * Strip URLs from stuff
	 *
	 * @access	public
	 * @param	string		Input string
	 * @return	string		Output string
	 */
	static public function stripUrls( $txt )
	{
		/* Start off by attempting to strip <a href=""></a> */
		$txt = preg_replace( "#<a(?:[^\"']+?)href\s{0,}=\s{0,}(\"|'|&quot;|&\#34;|&\#39;|&\#034;|&\#039;)([^<]+?)</a>#i", "", $txt );
		
		/* Now grab any non linked items */
		$txt = preg_replace( "#(http|https|news|ftp)://(?:[^<>\[\"\s]+|[a-z0-9/\._\-!&\#;,%\+\?:=]+)#i", "", $txt );
		
		return $txt;
	}
	
	/**
	 * Returns a cleaned MD5 hash
	 *
	 * @access	public
	 * @param	string		Input String
	 * @return	string		Parsed string
	 * @since	2.1
	 */
	static public function md5Clean( $text )
	{
		return preg_replace( "/[^a-zA-Z0-9]/", "" , substr( $text, 0, 32 ) );
    }
	
	/**
	 * Convert unicode entities
	 *
	 * @access	public
	 * @param	string		Input to convert (in the form of %u00E9 (example))
	 * @param	bool		Force to utf-8 (useful if you want to run convertCharsets() on it after)
	 * @return	string		UTF-8 (or html entity) encoded content
	 */
	static public function convertUnicode( $t, $forceUtf8=false )
	{
		$t = rawurldecode( $t );
		
		/* Need this function? */
		if ( ! strstr( $t, '%u' ) )
		{
			return $t;
		}

		if ( strtolower(IPS_DOC_CHAR_SET) == 'utf-8' || $forceUtf8 )
		{
			return preg_replace_callback( '#%u([0-9A-F]{1,4})#i', array( self, '_convertHexToUtf8' ), utf8_encode($t) );
		}
		else
		{
			return preg_replace_callback( '#%u([0-9A-F]{1,4})#i', create_function( '$matches', "return '&#' . hexdec(\$matches[1]) . ';';" ), $t );
		}
	}
	
	/**
	 * Convert decimal character code to utf-8
	 *
	 * @access	private
	 * @param	integer		Character code
	 * @return	string		Character
	 */
	static private function _convertToUtf8( $int=0 )
	{
		$return = '';

		if ( $int < 0 )
		{
			return chr(0);
		}
		else if ( $int <= 0x007f )
		{
			$return .= chr($int);
		}
		else if ( $int <= 0x07ff )
		{
			$return .= chr(0xc0 | ($int >> 6));
			$return .= chr(0x80 | ($int & 0x003f));
		}
		else if ( $int <= 0xffff )
		{
			$return .= chr(0xe0 | ($int  >> 12));
			$return .= chr(0x80 | (($int >> 6) & 0x003f));
			$return .= chr(0x80 | ($int  & 0x003f));
		}
		else if ( $int <= 0x10ffff )
		{
			$return .= chr(0xf0 | ($int  >> 18));
			$return .= chr(0x80 | (($int >> 12) & 0x3f));
			$return .= chr(0x80 | (($int >> 6) & 0x3f));
			$return .= chr(0x80 | ($int  &  0x3f));
		}
		else
		{ 
			return chr(0);
		}
		
		return $return;
	}

	/**
	 * Wrapper for dec_char_ref_to_utf8
	 *
	 * @access	private
	 * @param	array		Hex character code
	 * @return	string		Character
	 */
	static private function _convertHexToUtf8( $matches )
	{
		return self::_convertToUtf8( hexdec( $matches[1] ) );
	}
	
	/**
	 * Convert a string between charsets
	 *
	 * @access	public
	 * @param	string		Input String
	 * @param	string		Current char set
	 * @param	string		Destination char set
	 * @return	string		Parsed string
	 * @since	2.1.0
	 * @todo 	[Future] If an error is set in classConvertCharset, show it or log it somehow
	 */
	static public function convertCharsets( $text, $original_cset, $destination_cset="UTF-8" )
	{
		$original_cset    = strtolower($original_cset);
		$destination_cset = strtolower( $destination_cset );
		$t                = $text;

		//-----------------------------------------
		// Not the same?
		//-----------------------------------------

		if ( $destination_cset == $original_cset )
		{
			return $t;
		}
		
		if ( ! is_object( self::$classConvertCharset ) )
		{
			require_once( IPS_KERNEL_PATH.'/classConvertCharset.php' );
			self::$classConvertCharset = new classConvertCharset();
			
			if ( ipsRegistry::$settings['charset_conv_method'] == 'mb' AND function_exists( 'mb_convert_encoding' ) )
			{
				self::$classConvertCharset->method = 'mb';
			}
			else if ( ipsRegistry::$settings['charset_conv_method'] == 'iconv' AND function_exists( 'iconv' ) )
			{
				self::$classConvertCharset->method = 'iconv';
			}
			else if ( ipsRegistry::$settings['charset_conv_method'] == 'recode' AND function_exists( 'recode_string' ) )
			{
				self::$classConvertCharset->method = 'recode';
			}
			else
			{
				self::$classConvertCharset->method = 'internal';
			}
		}

		$text = self::$classConvertCharset->convertEncoding( $text, $original_cset, $destination_cset );
		
		return $text ? $text : $t;
	}

	/**
	 * Truncate a HTML string without breaking HTML entites
	 *
	 * @access	public
	 * @param	string		Input String
	 * @param	integer		Desired min. length
	 * @return	string		Parsed string
	 * @since	2.0
	 */
	static public function truncate($text, $limit=30)
	{
		$orig = $text;
		$text = str_replace( '&amp;' , '&#38;', $text );
		$text = str_replace( '&quot;', '&#34;', $text );
		$text = str_replace( '&gt;', '&#62;', $text );
		$text = str_replace( '&lt;', '&#60;', $text );

		$string_length = self::mbstrlen( $text );

		if ( $string_length > $limit)
		{
			$text = self::mbsubstr( $text, 0, $limit - 3 ) . '...';
		}
		else
		{
			$text = preg_replace( "/&(#{0,}([a-zA-Z0-9]+?)?)?$/", '', $text );
		}

		// Let's just use the original string if the truncated one is longer or same length
		return ( self::mbstrlen( $text ) >= $string_length ) ? $orig : $text;
	}
	
	/**
	 * MB strtolower
	 *
	 * @param	string	Input String
	 * @return	string	Parsed string
	 */
	static public function mbstrtolower( $text )
	{
		if ( function_exists('mb_list_encodings') AND function_exists('mb_strtolower') )
		{
			$valid_encodings = array();
			$valid_encodings = mb_list_encodings();

			if ( count($valid_encodings) )
			{
				if ( in_array( strtoupper(IPS_DOC_CHAR_SET), $valid_encodings ) )
				{
					$text = mb_strtolower( $text, strtoupper(IPS_DOC_CHAR_SET) );
				}
			}
			else
			{
				$text = strtolower( $text );
			}
		}
		else
		{
			$text = strtolower( $text );
		}
		
		return $text;
	}
	
	/**
	 * Substr support for this without mb_substr
	 *
	 * @param	string	Input String
	 * @param	integer	Desired min. length
	 * @return	string	Parsed string
	 * @since	2.0
	 */
	static public function mbsubstr( $text, $start=0, $limit=30 )
	{
		$text = str_replace( '&amp;' , '&#38;', $text );
		$text = str_replace( '&quot;', '&#34;', $text );
		$text = str_replace( '&gt;', '&#62;', $text );
		$text = str_replace( '&lt;', '&#60;', $text );

		//-----------------------------------------
		// Got multibyte?
		//-----------------------------------------

		if( function_exists('mb_list_encodings') )
		{
			$valid_encodings = array();
			$valid_encodings = mb_list_encodings();

			if( count($valid_encodings) )
			{
				if( in_array( strtoupper(IPS_DOC_CHAR_SET), $valid_encodings ) )
				{
					$text	= mb_substr( $text, $start, $limit, strtoupper(IPS_DOC_CHAR_SET) );
					$text	= preg_replace( "/&(#{0,}([a-zA-Z0-9]+?)?)?$/", '', $text );
					
					return $text;
				}
			}
		}

		//-----------------------------------------
		// Handrolled method
		//-----------------------------------------
		
		$string_length = self::mbstrlen( $text );
		
		//-----------------------------------------
		// Negative start?
		//-----------------------------------------

		if( $start < 0 )
		{
			$start	= $string_length + $start;
		}

		//-----------------------------------------
		// Do it!
		//-----------------------------------------
		
		if ( $string_length > $limit )
		{
			if( strtoupper(IPS_DOC_CHAR_SET) == 'UTF-8' )
			{
				// Multi-byte support
				//$text = preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,0}'.
	            //           '((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){'.intval($start).','.intval($limit).'}).*#s',
	            //           '$1',$text);
	            
	            /**
	             * @link	http://www.php.net/manual/en/function.substr.php#55107
	             */
	            preg_match_all( "/./su", $text, $ar );
	            $text	= implode( "", array_slice( $ar[0], $start, $limit ) );
            }
            else
            {
            	$text = substr( $text, $start, $limit );
            }

			$text = preg_replace( "/&(#{0,}([a-zA-Z0-9]+?)?)?$/", '', $text );
		}
		else
		{
			$text = preg_replace( "/&(#{0,}([a-zA-Z0-9]+?)?)?$/", '', $text );
		}

		return $text;
	}
	
	/**
	 * mb_stripos - uses mb functions if available
	 *
	 * @param	string	Input String
	 * @param	integer	Desired min. length
	 * @return	string	Parsed string
	 * @since	2.0
	 */
	static public function mbstripos( $text, $string, $start=0 )
	{
		// Do we have multi-byte functions?

		if( function_exists('mb_list_encodings') AND function_exists('mb_stripos') )
		{
			$valid_encodings = array();
			$valid_encodings = mb_list_encodings();

			if( count($valid_encodings) )
			{
				if( in_array( strtoupper(IPS_DOC_CHAR_SET), $valid_encodings ) )
				{
					return @mb_stripos( $text, $string, $start, strtoupper(IPS_DOC_CHAR_SET) );
				}
			}
		}

		// No?  Fallback to normal stripos
		return stripos( $text, $string, $start );
	}

	/**
	 * Clean a string to remove all non alphanumeric characters
	 *
	 * @access	public
	 * @param	string		Input String
	 * @param	string		Additional tags
	 * @return	string		Parsed string
	 * @since	2.1
	 */
	static public function alphanumericalClean( $text, $additional_tags="" )
	{
		if ( $additional_tags )
		{
			$additional_tags = preg_quote( $additional_tags, "/" );
		}

		return preg_replace( "/[^a-zA-Z0-9\-\_" . $additional_tags . "]/", "" , $text );
    }

	/**
	 * Get the true length of a multi-byte character string
	 *
	 * @access	public
	 * @param	string		Input String
	 * @return	integer		String length
	 * @since	2.1
	 */
	static public function mbstrlen( $t )
	{
		if( function_exists( 'mb_list_encodings' ) )
		{
			$encodings	= mb_list_encodings();

			if( in_array( strtoupper(IPS_DOC_CHAR_SET), array_map( 'strtoupper', $encodings ) ) )
			{
				return mb_strlen( $t, IPS_DOC_CHAR_SET );
			}
		}

		return strlen( preg_replace("/&#([0-9]+);/", "-", self::stripslashes( $t ) ) );
    }

	/**
	 * Convert text for use in a textarea
	 *
	 * @access	public
	 * @param	string		Input String
	 * @return	string		Parsed string
	 * @since	2.0
	 */
	static public function textToForm( $t="" )
	{
		// Use forward look up to only convert & not &#123;
		//$t = preg_replace("/&(?!#[0-9]+;)/s", '&#38;', $t );

		$t = str_replace( "&" , "&#38;"  , $t );
		$t = str_replace( "<" , "&#60;"  , $t );
		$t = str_replace( ">" , "&#62;"  , $t );
		$t = str_replace( '"' , "&#34;"  , $t );
		$t = str_replace( "'" , '&#039;' , $t );

		if ( IN_ACP )
		{
			$t = str_replace( "\\", "&#092;" , $t );
		}

		return $t; // A nice cup of?
	}

	/**
	 * Cleaned form data back to text
	 *
	 * @access	public
	 * @param	string	Input String
	 * @return	string	Parsed string
	 * @since	2.0
	 */
	static public function formToText($t="")
	{
		$t = str_replace( "&#38;"  , "&", $t );
		$t = str_replace( "&#60;"  , "<", $t );
		$t = str_replace( "&#62;"  , ">", $t );
		$t = str_replace( "&#34;"  , '"', $t );
		$t = str_replace( "&#039;" , "'", $t );
		$t = str_replace( "&#46;&#46;/" , "../", $t );

		if ( IN_ACP )
		{
			//$t = str_replace( '\\'     , '\\\\', $t );
			$t = str_replace( '&#092;' ,'\\', $t );
		}

		return $t;
	}

	/**
	 * Attempt to make slashes safe for us in DB (not really needed now?)
	 *
	 * @access	public
	 * @param	string	Input String
	 * @return	string	Parsed string
	 * @since	2.0
	 */
	static public function safeslashes($t="")
	{
		return str_replace( '\\', "\\\\", self::stripslashes($t) );
	}

	/**
	 * Remove slashes if magic_quotes enabled
	 *
	 * @access	public
	 * @param	string		Input String
	 * @return	string		Parsed string
	 * @since	2.0
	 */
	static public function stripslashes($t)
	{
		if ( IPS_MAGIC_QUOTES )
		{
    		$t = stripslashes($t);
    		$t = preg_replace( "/\\\(?!&amp;#|\?#)/", "&#092;", $t );
    	}

    	return $t;
    }

	/**
	 * Strip the attachment tag from data
	 *
	 * @access	public
	 * @param	string		Incoming text
	 * @return	string		Text with any attach tags removed
	 * @todo 	[Future] Move this to bbcode library?
	 */
	static public function stripAttachTag( $text )
	{
		return preg_replace( "#\[attachment=(\d+?)\:(?:[^\]]+?)\]#is", '', $text );
	}

	/**
	 * Convert text for use in a textarea
	 *
	 * @access	public
	 * @param	string		Input String
	 * @return	string		Parsed string
	 * @since	2.0
	 */
	static public function raw2form($t="")
	{
		$t = str_replace( '$', "&#036;", $t);

		if ( IPS_MAGIC_QUOTES )
		{
			$t = stripslashes($t);
		}

		$t = preg_replace( "/\\\(?!&amp;#|\?#)/", "&#092;", $t );

		//---------------------------------------
		// Make sure macros aren't converted
		//---------------------------------------

		$t = preg_replace( "/<{(.+?)}>/", "&lt;{\\1}&gt;", $t );

		return $t;
	}

	/**
	 * Converts slashes into HTML entities
	 *
	 * @access	public
	 * @param	string		Input String
	 * @return	string		Parsed string
	 * @since	2.0
	 */
	static public function makeSlashesSafe($t)
	{
		if ( IPS_MAGIC_QUOTES )
		{
			$t = stripslashes($t);
		}

		$t = preg_replace( "/\\\/", "&#092;", $t );

		return $t;
	}

	/**
	 * htmlspecialchars including entities
	 *
	 * @access	public
	 * @param	string		Input String
	 * @return	string		Parsed string
	 * @since	2.0
	 */
	static public function htmlspecialchars($t="")
	{
		// Use forward look up to only convert & not &#123;
		$t = preg_replace("/&(?!#[0-9]+;)/s", '&amp;', $t );
		$t = str_replace( "<", "&lt;"  , $t );
		$t = str_replace( ">", "&gt;"  , $t );
		$t = str_replace( '"', "&quot;", $t );
		$t = str_replace( "'", '&#039;', $t );

		return $t; // A nice cup of?
	}

	/**
	 * unhtmlspecialchars including multi-byte characters
	 *
	 * @access	public
	 * @param	string		Input String
	 * @return	string		Parsed string
	 * @since	2.0
	 */
	static public function UNhtmlspecialchars($t="")
	{
		$t = str_replace( "&amp;" , "&", $t );
		$t = str_replace( "&lt;"  , "<", $t );
		$t = str_replace( "&gt;"  , ">", $t );
		$t = str_replace( "&quot;", '"', $t );
		$t = str_replace( "&#039;", "'", $t );
		$t = str_replace( "&#39;" , "'", $t );
		$t = str_replace( "&#33;" , "!", $t );
		
		return $t;
	}

	/**
	 * Remove leading comma from comma delim string
	 *
	 * @access	public
	 * @param	string	Input String
	 * @return	string	Parsed string
	 * @since	2.0
	 */
	static public function trimLeadingComma($t)
	{
		return ltrim( $t, ',' );
	}

	/**
	 * Remove trailing comma from comma delim string
	 *
	 * @access	public
	 * @param	string	Input String
	 * @return	string	Parsed string
	 * @since	2.0
	 */
	static public function trimTrailingComma($t)
	{
		return rtrim( $t, ',' );
	}

	/**
	 * Remove dupe commas from comma delim string
	 *
	 * @access	public
	 * @param	string	Input String
	 * @return	string	Parsed string
	 * @since	2.0
	 */
	static public function cleanComma($t)
	{
		return preg_replace( "/,{2,}/", ",", $t );
	}

	/**
	 * Clean perm string (wrapper for comma cleaners)
	 *
	 * @access	public
	 * @param	string	Input String
	 * @return	string	Parsed string
	 * @since	2.0
	 */
	static public function cleanPermString($t)
	{
		$t = self::cleanComma($t);
		$t = self::trimLeadingComma($t);
		$t = self::trimTrailingComma($t);

		return $t;
	}

	/**
	 * Convert HTML line break tags to \n
	 *
	 * @access	public
	 * @param	string	Input text
	 * @return	string	Parsed text
	 * @since	2.0
	 */
	static public function br2nl($t="")
	{
		//print nl2br(htmlspecialchars($t)).'<br>--------------------------------<br>';
		$t	= str_replace( array( "\r", "\n" ), '', $t );
		$t	= str_replace( array( "<br />", "<br>" ), "\n", $t );
		//$t = preg_replace( "#(?:\n|\r)?<br />(?:\n|\r)?#", "\n", $t );
		//$t = preg_replace( "#(?:\n|\r)?<br>(?:\n|\r)?#"  , "\n", $t );
		//print nl2br(htmlspecialchars($t)).'<br>--------------------------------<br>';
		return $t;
	}

	/**
	 * Removes control characters (hidden spaces)
	 *
	 * @access	public
	 * @param	string	Input String
	 * @return	intger	String length
	 * @since	2.1
	 */
	static public function removeControlCharacters( $t )
	{
		/* This looks wrong but it's correct. During FURL set up in registry this function is called before settings are loaded
		 * and we want to strip hidden chars in this instance, so.. */
    	if ( ! isset( ipsRegistry::$settings['strip_space_chr'] ) OR ipsRegistry::$settings['strip_space_chr'] )
    	{
			/**
    		 * @see	http://en.wikipedia.org/wiki/Space_(punctuation)
    		 * @see http://www.ascii.cl/htmlcodes.htm
    		 */
			$t = str_replace( chr(160), ' ', $t );
			$t = str_replace( chr(173), ' ', $t );
			
			//$t = str_replace( chr(240), ' ', $t );	-> latin small letter eth

    		//$t = str_replace( chr(0xA0), "", $t );  //Remove sneaky spaces	Same as chr 160
    		//$t = str_replace( chr(0x2004), "", $t );  //Remove sneaky spaces
    		//$t = str_replace( chr(0x2005), "", $t );  //Remove sneaky spaces
    		//$t = str_replace( chr(0x2006), "", $t );  //Remove sneaky spaces
    		//$t = str_replace( chr(0x2009), "", $t );  //Remove sneaky spaces
    		//$t = str_replace( chr(0x200A), "", $t );  //Remove sneaky spaces
    		//$t = str_replace( chr(0x200B), "", $t );  //Remove sneaky spaces
    		//$t = str_replace( chr(0x200C), "", $t );  //Remove sneaky spaces
    		//$t = str_replace( chr(0x200D), " ", $t );  //Remove sneaky spaces
    		//$t = str_replace( chr(0x202F), " ", $t );  //Remove sneaky spaces
    		//$t = str_replace( chr(0x205F), " ", $t );  //Remove sneaky spaces
    		//$t = str_replace( chr(0x2060), "", $t );  //Remove sneaky spaces
    		//$t = str_replace( chr(0xFEFF), "", $t );  //Remove sneaky spaces
		}

		return $t;
    }

	/**
	* Base64 encode for URLs
	*
	* @access	public
	* @param	string		Data
	* @return	string		Data
	*/
	static public function base64_encode_urlSafe( $data )
	{
		return strtr( base64_encode( $data ), '+/=', '-_,' );;
	}
	
	/**
	* Base64 decode for URLs
	*
	* @access	public
	* @param	string		Data
	* @return	string		Data
	*/
	static public function base64_decode_urlSafe( $data )
	{
		return base64_decode( strtr( $data, '-_,', '+/=' ) );
	}
}

/**
* IPSMember
*
* This deals with member data and some member functions
*/
class IPSMember
{
	/**
	 * Custom fields class
	 *
	 * @access	private
	 * @var		object
	 */
	static private $custom_fields_class;

	/**
	 * Member cache
	 *
	 * @access	private
	 * @var		array
	 */
	static private $memberCache = array();

	/**
	 * Ignore cache
	 *
	 * @access	private
	 * @var		boolean
	 */
	static private $ignoreCache = FALSE;

	/**
	 * Debug data
	 *
	 * @access	public
	 * @var		array
	 */
	static public $debugData = array();

	/**
	 * memberFunctions object reference
	 *
	 * @access	private
	 * @var		object
	 */
	static private $_memberFunctions;

	/**
	 * Parsed signatures to save resources
	 *
	 * @access	private
	 * @var		array
	 */
	static private $_parsedSignatures	= array();
	
	/**
	 * Parsed custom fields to save resources
	 *
	 * @access	private
	 * @var		array
	 */
	static private $_parsedCustomFields	= array();
	
	/**
	 * Parsed custom fields to save resources
	 *
	 * @access	private
	 * @var		array
	 */
	static private $_parsedCustomGroups	= array();

	/**
	 * Ban filters cache
	 *
	 * @access	public
	 * @var		array
	 */
	static public $_banFiltersCache = NULL;

	/**
	 * Member data array
	 *
	 * @access	public
	 * @var		array
	 */
	static public $data = array( 'member_id'            => 0,
								 'name'                 => "",
								 'members_display_name' => "",
								 'member_group_id'      => 0,
								 'member_forum_markers' => array() );

	/**
	 * Remapped table array used in load and save
	 *
	 * @access	private
	 * @var		array
	 */
	static private $remap = array( 'core'               => 'members',
							       'extendedProfile'    => 'profile_portal',
							       'customFields'       => 'pfields_content',
							       'itemMarkingStorage' => 'core_item_markers_storage' );

	
	/**
	 * Create new member
	 * Very basic functionality at this point.
	 *
	 * @access	public
	 * @param	array 	Fields to save in the following format: array( 'members'      => array( 'email'     => 'test@test.com',
	 *																				         'joined'   => time() ),
	 *															   'extendedProfile' => array( 'signature' => 'My signature' ) );
	 *					Tables: members, pfields_content, profile_portal.
	 *					You can also use the aliases: 'core [members]', 'extendedProfile [profile_portal]', and 'customFields [pfields_content]'
	 * @param	bool	Flag to attempt to auto create a name if the desired is taken
	 * @param	bool	Bypass custom field saving (if using the sso session integration this is required as member object isn't ready yet)
	 * @param	bool	Whether or not to recache the stats so as to update the board's last member data
	 * @return	array 	Final member Data including member_id
	 *
	 * EXCEPTION CODES
	 * CUSTOM_FIELDS_EMPTY    - Custom fields were not populated
	 * CUSTOM_FIELDS_INVALID  - Custom fields were invalid
	 * CUSTOM_FIELDS_TOOBIG   - Custom fields too big
	 */
	static public function create( $tables=array(), $autoCreateName=FALSE, $bypassCfields=FALSE, $doStatsRecache=TRUE )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$finalTables	= array();
		$password		= '';
		$plainPassword	= '';
		$bitWiseFields  = ipsRegistry::fetchBitWiseOptions( 'global' );
		
		//-----------------------------------------
		// Remap tables if required
		//-----------------------------------------
		
		foreach( $tables as $table => $data )
		{
			$_name = ( isset( self::$remap[ $table ] ) ) ? self::$remap[ $table ] : $table;
			
			if ( $_name == 'members' )
			{
				/* Magic password field */
				$password		= ( isset( $data['password'] ) ) ? trim( $data['password'] ) : IPSLib::makePassword();
				$plainPassword	= $password;
				$md_5_password	= md5( $password );
				
				unset( $data['password'] );
			}
			
			$finalTables[ $_name ] = $data;
		}
		
		//-----------------------------------------
		// Custom profile field stuff
		//-----------------------------------------

		if( !$bypassCfields )
		{
			$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/customfields/profileFields.php', 'customProfileFields' );
	    	$fields = new $classToLoad();
	    	
	    	if ( is_array( $finalTables['pfields_content'] ) AND count( $finalTables['pfields_content'] ) )
			{
				$fields->member_data = $finalTables['pfields_content'];
			}
			
			$_cfieldMode	= 'normal';
			
	    	$fields->initData( 'edit' );
	    	$fields->parseToSave( $finalTables['pfields_content'], 'register' );

			/* Check */
			if( count( $fields->error_fields['empty'] ) )
			{
				//throw new Exception( 'CUSTOM_FIELDS_EMPTY' );
			}
			
			if( count( $fields->error_fields['invalid'] ) )
			{
				//throw new Exception( 'CUSTOM_FIELDS_INVALID' );
			}
			
			if( count( $fields->error_fields['toobig'] ) )
			{
				//throw new Exception( 'CUSTOM_FIELDS_TOOBIG' );
			}
		}

    	//-----------------------------------------
    	// Make sure the account doesn't exist
    	//-----------------------------------------
    	
    	if( $finalTables['members']['email'] )
    	{
    		$existing	= IPSMember::load( $finalTables['members']['email'], 'all' );
    		
    		if( $existing['member_id'] )
    		{
    			$existing['full']		= true;
    			$existing['timenow']	= time();
    			
    			return $existing;
    		}
    	}
    	
		//-----------------------------------------
		// Fix up usernames and display names
		//-----------------------------------------
		
		/* Ensure we have a display name */
		if( $autoCreateName AND $finalTables['members']['members_display_name'] !== FALSE )
		{
			$finalTables['members']['members_display_name'] = ( $finalTables['members']['members_display_name'] ) ? $finalTables['members']['members_display_name'] : $finalTables['members']['name'];
		}
		
		//-----------------------------------------
		// Remove some basic HTML tags
		//-----------------------------------------
		
		$finalTables['members']['members_display_name'] = str_replace( array( '<', '>', '"' ), '', $finalTables['members']['members_display_name'] );
		$finalTables['members']['name'] 				= str_replace( array( '<', '>', '"' ), '', $finalTables['members']['name'] );
		
		//-----------------------------------------
		// Make sure the names are unique
		//-----------------------------------------
		
		/* Can specify display name of FALSE to force no entry to force partial member */
		if( $finalTables['members']['members_display_name'] !== FALSE )
		{
			try
			{
				if( IPSMember::getFunction()->checkNameExists( $finalTables['members']['members_display_name'], array(), 'members_display_name', true ) === true )
				{
					if ( $autoCreateName === TRUE )
					{
						/* Now, make sure we have a unique display name */
						$max = ipsRegistry::DB()->buildAndFetch( array( 'select' => 'MAX(member_id) as max',
														 				'from'   => 'members',
														 				'where'  => "members_l_display_name LIKE '" . ipsRegistry::DB()->addSlashes( strtolower( $finalTables['members']['members_display_name'] ) ) . "%'" ) );


						if ( $max['max'] )
						{
							$_num = $max['max'] + 1;
							$finalTables['members']['members_display_name'] = $finalTables['members']['members_display_name'] . '_' . $_num;
						}
					}
					else
					{
						$finalTables['members']['members_display_name']		= '';
					}
				}
			}
			catch( Exception $e )
			{}
		}
		
		if( $finalTables['members']['name'] )
		{
			try
			{
				if( IPSMember::getFunction()->checkNameExists( $finalTables['members']['name'], array(), 'name', true ) === true )
				{
					if ( $autoCreateName === TRUE )
					{
						/* Now, make sure we have a unique username */
						$max = ipsRegistry::DB()->buildAndFetch( array( 'select' => 'MAX(member_id) as max',
														 				'from'   => 'members',
														 				'where'  => "members_l_username LIKE '" . ipsRegistry::DB()->addSlashes( strtolower( $finalTables['members']['name'] ) ) . "%'" ) );


						if ( $max['max'] )
						{
							$_num = $max['max'] + 1;
							$finalTables['members']['name'] = $finalTables['members']['name'] . '_' . $_num;
						}
					}
					else
					{
						$finalTables['members']['name'] = '';
					}
				}
			}
			catch( Exception $e )
			{}
		}

		//-----------------------------------------
		// Clean up characters
		//-----------------------------------------
		
		if( $finalTables['members']['name'] )
		{
			$userName		= IPSMember::getFunction()->cleanAndCheckName( $finalTables['members']['name'], array(), 'name' );
			
			if( $userName['errors'] )
			{
				$finalTables['members']['name']	= $finalTables['members']['email'];
			}
			else
			{
				$finalTables['members']['name']	= $userName['username'];
			}
		}
		
		if( $finalTables['members']['members_display_name'] )
		{
			$displayName	= IPSMember::getFunction()->cleanAndCheckName( $finalTables['members']['members_display_name'] );
			
			if( $displayName['errors'] )
			{
				$finalTables['members']['members_display_name']	= '';
			}
			else
			{
				$finalTables['members']['members_display_name']	= $displayName['members_display_name'];
			}
		}
			
		//-----------------------------------------
		// Populate member table(s)
		//-----------------------------------------

		$finalTables['members']['members_l_username']		= isset($finalTables['members']['name']) ? strtolower($finalTables['members']['name']) : '';
		$finalTables['members']['joined']					= $finalTables['members']['joined'] ? $finalTables['members']['joined'] : time();
		$finalTables['members']['email']					= $finalTables['members']['email'] ? $finalTables['members']['email'] : $finalTables['members']['name'] . '@' . $finalTables['members']['joined'];
		$finalTables['members']['member_group_id']			= $finalTables['members']['member_group_id'] ? $finalTables['members']['member_group_id'] : ipsRegistry::$settings['member_group'];
		$finalTables['members']['ip_address']				= $finalTables['members']['ip_address'] ? $finalTables['members']['ip_address'] : ipsRegistry::member()->ip_address;
		$finalTables['members']['members_created_remote']	= intval( $finalTables['members']['members_created_remote'] );
		$finalTables['members']['member_login_key']			= IPSMember::generateAutoLoginKey();
		$finalTables['members']['member_login_key_expire']	= ( ipsRegistry::$settings['login_key_expire'] ) ? ( time() + ( intval( ipsRegistry::$settings['login_key_expire'] ) * 86400 ) ) : 0;
		$finalTables['members']['view_sigs']				= 1;
		$finalTables['members']['view_img']					= 1;
		$finalTables['members']['view_avs']					= 1;
		$finalTables['members']['bday_day']					= intval( $finalTables['members']['bday_day'] );
		$finalTables['members']['bday_month']				= intval( $finalTables['members']['bday_month'] );
		$finalTables['members']['bday_year']				= intval( $finalTables['members']['bday_year'] );
		$finalTables['members']['restrict_post']			= intval( $finalTables['members']['restrict_post'] );
		$finalTables['members']['msg_count_total']			= 0;
		$finalTables['members']['msg_count_new']			= 0;
		$finalTables['members']['msg_show_notification']	= 1;
		$finalTables['members']['coppa_user']				= 0;
		$finalTables['members']['auto_track']				= intval( $finalTables['members']['auto_track'] );
		$finalTables['members']['last_visit']				= $finalTables['members']['last_visit'] ? $finalTables['members']['last_visit'] : time();
		$finalTables['members']['last_activity']			= $finalTables['members']['last_activity'] ? $finalTables['members']['last_activity'] : time();
		$finalTables['members']['language']					= IPSLib::getDefaultLanguage();
		$finalTables['members']['members_editor_choice']	= ipsRegistry::$settings['ips_default_editor'];
		$finalTables['members']['members_pass_salt']		= IPSMember::generatePasswordSalt(5);
		$finalTables['members']['members_pass_hash']		= IPSMember::generateCompiledPasshash( $finalTables['members']['members_pass_salt'], $md_5_password );
		$finalTables['members']['members_display_name']		= isset($finalTables['members']['members_display_name']) ? $finalTables['members']['members_display_name'] : '';
		$finalTables['members']['members_l_display_name']	= isset($finalTables['members']['members_display_name']) ? strtolower($finalTables['members']['members_display_name']) : '';
		$finalTables['members']['fb_uid']	 	            = isset($finalTables['members']['fb_uid']) ? $finalTables['members']['fb_uid'] : 0;
		$finalTables['members']['fb_emailhash']	            = isset($finalTables['members']['fb_emailhash']) ? strtolower($finalTables['members']['fb_emailhash']) : '';
		$finalTables['members']['members_seo_name']         = IPSText::makeSeoTitle( $finalTables['members']['members_display_name'] );
		$finalTables['members']['bw_is_spammer']            = intval( $finalTables['members']['bw_is_spammer'] );
		
		//-----------------------------------------
		// Insert: MEMBERS
		//-----------------------------------------
		
		ipsRegistry::DB()->force_data_type = array( 'name'						=> 'string',
													'members_l_username'		=> 'string',
												    'members_display_name'		=> 'string',
												    'members_l_display_name'	=> 'string',
												    'members_seo_name'			=> 'string',
												    'email'						=> 'string' );
													
		/* Bitwise options */
		if ( is_array( $bitWiseFields['members'] ) )
		{
			$_freeze = array();
			
			foreach( $bitWiseFields['members'] as $field )
			{
				if ( isset( $finalTables['members'][ $field ] ) )
				{
					/* Add to freezeable array */
					$_freeze[ $field ] = $finalTables['members'][ $field ];
					
					/* Remove it from the fields to save to DB */
					unset( $finalTables['members'][ $field ] );
				}
			}
			
			if ( count( $_freeze ) )
			{
				$finalTables['members']['members_bitoptions'] = IPSBWOptions::freeze( $_freeze, 'members', 'global' );
			}
		}
			
		ipsRegistry::DB()->insert( 'members', $finalTables['members'] );
	
		//-----------------------------------------
		// Get the member id
		//-----------------------------------------
		
		$finalTables['members']['member_id'] = ipsRegistry::DB()->getInsertId();

		//-----------------------------------------
		// Insert: PROFILE PORTAL
		//-----------------------------------------

		$finalTables['profile_portal']['pp_member_id']              = $finalTables['members']['member_id'];
		$finalTables['profile_portal']['pp_setting_count_friends']  = 1;
		$finalTables['profile_portal']['pp_setting_count_comments'] = 1;
		$finalTables['profile_portal']['pp_customization']			= serialize( array() );
		
		foreach( array( 'pp_bio_content', 'pp_last_visitors', 'pp_about_me', 'notes', 'links', 'bio', 'signature', 'fb_photo', 'fb_photo_thumb', 'pp_status', 'pconversation_filters',
					    'ta_size', 'avatar_location', 'avatar_type', 'avatar_size' ) as $f )
		{
			$finalTables['profile_portal'][ $f ] = ( $finalTables['profile_portal'][ $f ] ) ? $finalTables['profile_portal'][ $f ] : '';
		}
		
		ipsRegistry::DB()->insert( 'profile_portal', $finalTables['profile_portal'] );
		
		//-----------------------------------------
		// Insert into the custom profile fields DB
		//-----------------------------------------
		
		if( !$bypassCfields )
		{
			$fields->out_fields['member_id'] = $finalTables['members']['member_id'];
			
			ipsRegistry::DB()->delete( 'pfields_content', 'member_id=' . $finalTables['members']['member_id'] );
			ipsRegistry::DB()->insert( 'pfields_content', $fields->out_fields );
		}
		else
		{
			ipsRegistry::DB()->delete( 'pfields_content', 'member_id=' . $finalTables['members']['member_id'] );
			ipsRegistry::DB()->insert( 'pfields_content', array( 'member_id' => $finalTables['members']['member_id'] ) );
		}

		//-----------------------------------------
		// Insert into partial ID table
		//-----------------------------------------
		
		$full_account 	= false;
		
		if( $finalTables['members']['members_display_name'] AND $finalTables['members']['name'] AND $finalTables['members']['email'] AND $finalTables['members']['email'] != $finalTables['members']['name'] . '@' . $finalTables['members']['joined'] )
		{
			$full_account	= true;
		}
		
		if ( ! $full_account )
		{
			ipsRegistry::DB()->insert( 'members_partial', array( 'partial_member_id' => $finalTables['members']['member_id'],
														 		 'partial_date'      => $finalTables['members']['joined'],
														 		 'partial_email_ok'  => ( $finalTables['members']['email'] == $finalTables['members']['name'] . '@' . $finalTables['members']['joined'] ) ? 0 : 1,
								) 						);
		}
		
		/* Add plain password and run sync */
		$finalTables['members']['plainPassword'] = $plainPassword;
		
		IPSLib::runMemberSync( 'onCreateAccount', $finalTables['members'] );
		
		/* Remove plain password */
		unset( $finalTables['members']['plainPassword'] );
		
		//-----------------------------------------
		// Recache our stats (Ticket 627608)
		//-----------------------------------------
		
		if ( $doStatsRecache == TRUE )
		{
			ipsRegistry::cache()->rebuildCache( 'stats', 'global' );
		}
															
		return array_merge( $finalTables['members'], $finalTables['profile_portal'], !$bypassCfields ? $fields->out_fields : array(), array( 'timenow' => $finalTables['members']['joined'], 'full' => $full_account ) );
	}

	/**
	 * Save member
	 *
	 * @access	public
	 * @param 	int		Member key: Either Array, ID or email address. If it's an array, it must be in the format:
	 *					 array( 'core' => array( 'field' => 'member_id', 'value' => 1 ) ) - useful for passing custom fields through
	 * @param 	array 	Fields to save in the following format: array( 'members'      => array( 'email'     => 'test@test.com',
	 *																				         'joined'   => time() ),
	 *															   'extendedProfile' => array( 'signature' => 'My signature' ) );
	 *					Tables: members, pfields_content, profile_portal.
	 *					You can also use the aliases: 'core [members]', 'extendedProfile [profile_portal]', and 'customFields [pfields_content]'
	 * @return	boolean	True if the save was successful
	 *
	 * Exception Error Codes:
	 * NO_DATA 		  : No data to save
	 * NO_VALID_KEY    : No valid key to save
	 * NO_AUTO_LOAD    : Could not autoload the member as she does not exist
	 * INCORRECT_TABLE : Table one is attempting to save to does not exist
	 * NO_MEMBER_GROUP_ID: Member group ID is in the array but blank
	 */
	static public function save( $member_key, $save=array() )
	{
		$member_id      = 0;
		$member_email   = '';
		$member_field   = '';
		$_updated       = 0;
		$bitWiseFields  = ipsRegistry::fetchBitWiseOptions( 'global' );
		$member_k_array = array( 'members' => array(), 'pfields_content' => array(),  'profile_portal' => array() );
		$_tables        = array_keys( $save );
		$_MEMBERKEY     = 'member_id';
		$_MEMBERVALUE   = $member_key;
		
		//-----------------------------------------
		// Test...
		//-----------------------------------------

		if ( ! is_array( $save ) OR ! count( $save ) )
		{
			throw new Exception( 'NO_DATA' );
		}

		//-----------------------------------------
		// ID or email?
		//-----------------------------------------

		if ( ! is_array( $member_key ) )
		{
			if ( strstr( $member_key, '@' ) )
			{
				$_MEMBERKEY    = 'email';
				
				$member_k_array['members'] = array( 'field' => 'email',
				 									'value' => "'" . ipsRegistry::instance()->DB()->addSlashes( strtolower( $member_key ) ) . "'" );

				//-----------------------------------------
				// Check to see if we've got more than the core
				// table to save on.
				//-----------------------------------------

				$_got_more_than_core = FALSE;

				foreach( $_tables as $table )
				{
					if ( isset( self::$remap[ $table ] ) )
					{
						$table = self::$remap[ $table ];
					}

					if ( $table != 'members' )
					{
						$_got_more_than_core = TRUE;
						break;
					}
				}

				if ( $_got_more_than_core === TRUE )
				{
					/* Get the ID */
					$_memberTmp = self::load( $member_key, 'core' );
				
					if ( $_memberTmp['member_id'] )
					{
						$member_k_array['pfields_content'] = array( 'field' => 'member_id'   , 'value' => $_memberTmp['member_id'] );
						$member_k_array['profile_portal']  = array( 'field' => 'pp_member_id', 'value' => $_memberTmp['member_id'] );
					}
					else
					{
						throw new Exception( "NO_AUTO_LOAD" );
					}
				}
			}
			else
			{
				$member_k_array['members']         = array( 'field' => 'member_id'    , 'value' => intval( $member_key ) );
				$member_k_array['pfields_content'] = array( 'field' => 'member_id'    , 'value' => intval( $member_key ) );
				$member_k_array['profile_portal']  = array( 'field' => 'pp_member_id' , 'value' => intval( $member_key ) );

				self::_updateCache( $member_key, $save );
			}
		}
		else
		{
			$_member_k_array = $member_k_array;

			foreach( $member_key as $table => $data )
			{
				if ( isset( self::$remap[ $table ] ) )
				{
					$table = self::$remap[ $table ];
				}

				if ( ! in_array( $table, array_keys( $_member_k_array ) ) )
				{
					throw new Exception( 'INCORRECT_TABLE' );
				}

				$member_k_array[ $table ] = $data;
			}
		}

		//-----------------------------------------
		// Test...
		//-----------------------------------------

		if ( ! is_array( $member_k_array ) OR ! count( $member_k_array ) )
		{
			throw new Exception( 'NO_DATA' );
		}

		//-----------------------------------------
		// Now save...
		//-----------------------------------------

		foreach( $save as $table => $data )
		{
			if ( isset( self::$remap[ $table ] ) )
			{
				$table = self::$remap[ $table ];
			}

			if ( $table == 'profile_portal' )
			{
				$data[ $member_k_array[ $table ]['field'] ] = $member_k_array[ $table ]['value'];

				//-----------------------------------------
				// Does row exist?
				//-----------------------------------------

				$check = ipsRegistry::DB()->buildAndFetch( array( 'select' => 'pp_member_id', 'from' => 'profile_portal', 'where' => 'pp_member_id=' . $data['pp_member_id'] ) );

				if( !$check['pp_member_id'] )
				{
					ipsRegistry::DB()->insert( $table, $data );
				}
				else
				{
					ipsRegistry::DB()->update( $table, $data, 'pp_member_id=' . $data['pp_member_id'] );
				}
			}
			else if ( $table == 'pfields_content' )
			{
				$data[ $member_k_array[ $table ]['field'] ] = $member_k_array[ $table ]['value'];

				//-----------------------------------------
				// Does row exist?
				//-----------------------------------------

				$check = ipsRegistry::DB()->buildAndFetch( array( 'select' => 'member_id', 'from' => 'pfields_content', 'where' => 'member_id=' . $data['member_id'] ) );
				
				foreach( $data as $_k => $_v )
				{
					ipsRegistry::DB()->force_data_type[ $_k ] = 'string';
				}

				if( !$check['member_id'] )
				{
					ipsRegistry::DB()->insert( $table, $data );
				}
				else
				{
					ipsRegistry::DB()->update( $table, $data, 'member_id=' . $data['member_id'] );
				}
			}
			else
			{
				if ( $table == 'members' )
				{
					/* Make sure we have a value for member_group_id if passed */
					if ( isset( $data['member_group_id'] ) AND ! $data['member_group_id'] )
					{
						throw new Exception( "NO_MEMBER_GROUP_ID" );
					}
					
					/* Some stuff that can end up  here */
					unset( $data['_canBeIgnored'] );
					
					/* Bitwise options */
					if ( is_array( $bitWiseFields['members'] ) )
					{
						$_freeze = array();
						
						foreach( $bitWiseFields['members'] as $field )
						{
							if ( isset( $data[ $field ] ) )
							{
								/* Add to freezeable array */
								$_freeze[ $field ] = $data[ $field ];
								
								/* Remove it from the fields to save to DB */
								unset( $data[ $field ] );
							}
						}
						
						if ( count( $_freeze ) )
						{
							$data['members_bitoptions'] = IPSBWOptions::freeze( $_freeze, 'members', 'global' );
						}
					}
					
					ipsRegistry::DB()->force_data_type = array(
																'name'						=> 'string',
																'title'						=> 'string',
																'members_l_username'		=> 'string',
																'members_display_name'		=> 'string',
																'members_l_display_name'	=> 'string',
																'members_seo_name'			=> 'string',
																'msg_count_total'			=> 'int',
																'msg_count_new'				=> 'int',
																'members_bitoptions'		=> 'int',
															);
				}

				ipsRegistry::DB()->update( $table, $data, $member_k_array[ $table ]['field'] . '=' . $member_k_array[ $table ]['value'] );
			}

			$_updated += ipsRegistry::instance()->DB()->getAffectedRows();
		}

		//-----------------------------------------
		// If member login key is updated during
		// session creation, this causes fatal error
		//-----------------------------------------
		
		if( is_object( ipsRegistry::member() ) )
		{
			$save[ $_MEMBERKEY ] = $_MEMBERVALUE;
			IPSLib::runMemberSync( 'onProfileUpdate', $save );
		}

		return ( $_updated > 0 ) ? TRUE : FALSE;
	}

	/**
	 * Load member
	 *
	 * @access	public
	 * @param 	string	Member key: Either ID or email address OR array of IDs when $key_type is either ID or not set OR a list of $key_type strings (email address, name, etc)
	 * @param 	string	Extra tables to load(all, none or comma delisted tables) Tables: members, pfields_content, profile_portal, groups, sessions, core_item_markers_storage.
	 *					You can also use the aliases: 'extendedProfile', 'customFields' and 'itemMarkingStorage'
	 * @param	string  Key type. Leave it blank to auto-detect or specify "id", "email", "username", "displayname".
	 * @return	array   Array containing member data
	 * <code>
	 * # Single member
	 * $member = IPSMember::load( 1, 'extendedProfile,groups' );
	 * $member = IPSMember::load( 'matt@email.com', 'all' );
	 * $member = IPSMember::load( 'MattM', 'all', 'displayname' ); // Can also use 'username', 'email' or 'id'
	 * # Multiple members
	 * $members = IPSMember::load( array( 1, 2, 10 ), 'all' );
	 * $members = IPSMember::load( array( 'MattM, 'JoeD', 'DaveP' ), 'all', 'displayname' );
	 * </code>
	 */
	static public function load( $member_key, $extra_tables='all', $key_type='' )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------

		$member_value    = 0;
		$members         = array();
		$multiple_ids    = array();
		$member_field    = '';
		$joins           = array();
		$tables          = array( 'pfields_content' => 0, 'profile_portal' => 0, 'core_item_markers_storage' => 0, 'groups' => 0, 'sessions' => 0 );
		$remap           = array( 'extendedProfile'    => 'profile_portal',
							      'customFields'       => 'pfields_content',
							      'itemMarkingStorage' => 'core_item_markers_storage' );

		//-----------------------------------------
		// ID or email?
		//-----------------------------------------

		if ( ! $key_type )
		{
			if ( is_array( $member_key ) )
			{
				$multiple_ids = array_map( 'intval', $member_key ); // Bug #20908
				$member_field = 'member_id';
			}
			else
			{
				if ( strstr( $member_key, '@' ) )
				{
					$member_value = "'" . ipsRegistry::DB()->addSlashes( strtolower( $member_key ) ) . "'";
					$member_field = 'email';
				}
				else
				{
					$member_value = intval( $member_key );
					$member_field = 'member_id';
				}
			}
		}
		else
		{
			switch( $key_type )
			{
				default:
				case 'id':
					if ( is_array( $member_key ) )
					{
						$multiple_ids = $member_key;
					}
					else
					{
						$member_value = intval( $member_key );
					}
					$member_field = 'member_id';
				break;
				case 'fb_uid':
					if ( is_array( $member_key ) )
					{
						$multiple_ids = $member_key;
					}
					else
					{
						$member_value = is_numeric( $member_key ) ? $member_key : 0;
					}
					$member_field = 'fb_uid';
					
					if ( $member_value == 0 )
					{
						return array();
					}
				break;
				case 'twitter_id':
					if ( is_array( $member_key ) )
					{
						$multiple_ids = $member_key;
					}
					else
					{
						$member_value = is_numeric( $member_key ) ? $member_key : 0;
					}
					$member_field = 'twitter_id';
					
					if ( $member_value == 0 )
					{
						return array();
					}
				break;
				case 'email':
					if ( is_array( $member_key ) )
					{
						array_walk( $member_key, create_function( '&$v,$k', '$v="\'".ipsRegistry::DB()->addSlashes( strtolower( $v ) ) . "\'";' ) );
						$multiple_ids = $member_key;
					}
					else
					{
						$member_value = "'" . ipsRegistry::DB()->addSlashes( strtolower( $member_key ) ) . "'";
					}
					$member_field = 'email';
				break;
				case 'username':
					if ( is_array( $member_key ) )
					{
						array_walk( $member_key, create_function( '&$v,$k', '$v="\'".ipsRegistry::DB()->addSlashes( strtolower( $v ) ) . "\'";' ) );
						$multiple_ids = $member_key;
					}
					else
					{
						$member_value = "'" . ipsRegistry::DB()->addSlashes( strtolower( $member_key ) ) . "'";
					}
					$member_field = 'members_l_username';
				break;
				case 'displayname':
					if ( is_array( $member_key ) )
					{
						array_walk( $member_key, create_function( '&$v,$k', '$v="\'".ipsRegistry::DB()->addSlashes( strtolower( $v ) ) . "\'";' ) );
						$multiple_ids = $member_key;
					}
					else
					{
						$member_value = "'" . ipsRegistry::DB()->addSlashes( strtolower( $member_key ) ) . "'";
					}
					$member_field = 'members_l_display_name';
				break;
			}
		}
		
		//-----------------------------------------
		// Protected against member_id=0
		//-----------------------------------------
		
		if( !count($multiple_ids) OR !is_array($multiple_ids) )
		{
			if( $member_field == 'member_id' AND !$member_value )
			{
				return array();
			}
		}

		//-----------------------------------------
		// Sort out joins...
		//-----------------------------------------

		if ( $extra_tables == 'all' )
		{
			foreach( $tables as $_table => $_val )
			{
				/* Let's not load sessions unless specifically requested */
				if ( $_table == 'sessions' )
				{
					continue;
				}
				
				/* Same deal with item marking */
				if ( $_table == 'core_item_markers_storage' )
				{
					continue;
				}

				$tables[ $_table ] = 1;
			}
		}
		else if ( $extra_tables )
		{
			$_tables = explode( ",", $extra_tables );

			foreach( $_tables as $_t )
			{
				$_t = trim( $_t );
				
				if ( isset( $tables[ $_t ] ) )
				{
					$tables[ $_t ] = 1;
				}
				else if ( isset( self::$remap[ $_t ] ) )
				{
					if ( strstr( $tables[ self::$remap[ $_t ] ], ',' ) )
					{
						$__tables = explode( ',', $tables[ self::$remap[ $_t ] ] );

						foreach( $__tables as $__t )
						{
							$tables[ $__t ] = 1;
						}
					}
					else
					{
						$tables[ self::$remap[ $_t ] ] = 1;
					}
				}
			}
		}

		//-----------------------------------------
		// Grab used tables
		//-----------------------------------------

		$_usedTables = array();

		foreach( $tables as $_name => $_use )
		{
			if ( $_use )
			{
				$_usedTables[] = $_name;
			}
		}

		//-----------------------------------------
		// Check the cache first...
		//-----------------------------------------
		
		if ( $member_field == 'member_id' AND $member_value )
		{
			$member = self::_fetchFromCache( $member_value, $_usedTables );

			if ( $member !== FALSE )
			{
				return $member;
			}
		}
		else if( count($multiple_ids) AND is_array($multiple_ids) )
		{
			$_totalUsers	= count($multiple_ids);
			$_gotFromCache	= 0;
			$_fromCache		= array();
			
			foreach( $multiple_ids as $_memberValue )
			{
				$member = self::_fetchFromCache( $_memberValue, $_usedTables );
				
				if ( $member !== FALSE )
				{
					$_fromCache[ $member['member_id'] ]	= $member;
					$_gotFromCache++;
				}
			}

			//-----------------------------------------
			// Did we find all the members in cache?
			//-----------------------------------------
			
			if( $_gotFromCache == $_totalUsers )
			{
				return $_fromCache;
			}
		}

		self::$ignoreCache = FALSE;

		//-----------------------------------------
		// Fix up joins...
		//-----------------------------------------

		if ( $tables['pfields_content'] )
		{
			$joins[] = array( 'select' => 'p.*',
						  	  'from'   => array( 'pfields_content' => 'p' ),
						  	  'where'  => 'p.member_id=m.member_id',
						  	  'type'   => 'left' );
		}

		if ( $tables['profile_portal'] )
		{
			$joins[] = array( 'select' => 'pp.*',
						  	  'from'   => array( 'profile_portal' => 'pp' ),
							  'where'  => 'pp.pp_member_id=m.member_id',
							  'type'   => 'left' );
		}

		if ( $tables['groups'] )
		{
			$joins[] = array( 'select' => 'g.*',
			 				  'from'   => array( 'groups' => 'g' ),
							  'where'  => 'g.g_id=m.member_group_id',
						      'type'   => 'left' );
		}

		if ( $tables['sessions'] )
		{
			$joins[] = array( 'select' => 's.*',
			 				  'from'   => array( 'sessions' => 's' ),
							  'where'  => 's.member_id=m.member_id',
						      'type'   => 'left' );
		}
		
		if ( $tables['core_item_markers_storage'] )
		{
			$joins[] = array( 'select' => 'im.*',
			 				  'from'   => array( 'core_item_markers_storage' => 'im' ),
							  'where'  => 'im.item_member_id=m.member_id',
						      'type'   => 'left' );
		}
		
		if ( IPSContentCache::isEnabled() )
		{
			if ( IPSContentCache::fetchSettingValue( 'sig' ) )
			{
				$joins[] = IPSContentCache::join( 'sig' , 'm.member_id', 'ccb', 'left', 'ccb.cache_content' );
			}
		}

		//-----------------------------------------
		// Do eeet
		//-----------------------------------------

		if ( count( $joins ) )
		{
			ipsRegistry::DB()->build( array( 'select'   => 'm.*, m.member_id as my_member_id',
											 'from'     => array( 'members' => 'm' ),
											 'where'    => ( is_array( $multiple_ids ) AND count( $multiple_ids ) ) ?  'm.'. $member_field . ' IN (' . implode( ',', $multiple_ids ) . ')' : 'm.'. $member_field . '=' . $member_value,
											 'add_join' => $joins ) );
		}
		else
		{
			ipsRegistry::DB()->build( array( 'select'   => '*',
											 'from'     => 'members',
											 'where'    => ( is_array( $multiple_ids ) AND count( $multiple_ids ) ) ?  $member_field . ' IN (' . implode( ',', $multiple_ids ) . ')' : $member_field . '=' . $member_value ) );
		}

		//-----------------------------------------
		// Execute
		//-----------------------------------------

		ipsRegistry::DB()->execute();

		while( $mem = ipsRegistry::DB()->fetch() )
		{
			if ( isset( $mem['my_member_id'] ) )
			{
				$mem['member_id'] = $mem['my_member_id'];
			}
			
			$mem['full']		= true;
			
			if( !$mem['email'] OR !$mem['members_display_name'] OR $mem['email'] == $mem['name'] . '@' . $mem['joined'] )
			{
				$mem['full']	= false;
				$mem['timenow']	= $mem['joined'];
			}

			//-----------------------------------------
			// Be sure we properly apply secondary permissions
			//-----------------------------------------

			if ( $tables['groups'] AND is_object( ipsRegistry::member() ) )
			{
				$mem = ipsRegistry::member()->setUpSecondaryGroups( $mem );
				
				/* Unpack groups */
				$mem = IPSLib::unpackGroup( $mem, TRUE );
			}

			//-----------------------------------------
			// Unblockable
			//-----------------------------------------

			$mem['_canBeIgnored'] = self::isIgnorable( $mem['member_group_id'], $mem['mgroup_others'] );
			
			/* Bitwise Options */
			$mem = self::buildBitWiseOptions( $mem );
			
			/* Add to array */
			$members[ $mem['member_id'] ] = $mem;

			//-----------------------------------------
			// Add to cache
			//-----------------------------------------

			self::_addToCache( $mem, $_usedTables );
		}

		//-----------------------------------------
		// Return just a single if we only sent one id
		//-----------------------------------------

		return ( is_array( $multiple_ids ) AND count( $multiple_ids ) ) ? $members : array_shift( $members );
	}

	/**
	 * Delete member(s)
	 *
	 * @access	public
	 * @param 	mixed		[Integer] member ID or [Array] array of member ids
	 * @param	boolean		Check if request is from an admin
	 * @return	boolean		Action completed successfully
	 */
	static public function remove( $id, $check_admin=true )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------

		$tmp_mids 	= array();
		$emails		= array();

		//-----------------------------------------
		// Sort out thingie
		//-----------------------------------------

		if ( is_array( $id ) )
		{
			$id = IPSLib::cleanIntArray( $id );

			$mids = ' IN (' . implode( ",", $id ) . ')';
		}
		else
		{
			$mids = ' = ' . intval($id);
		}

		//-----------------------------------------
		// Get accounts and check IDS
		//-----------------------------------------

		ipsRegistry::DB()->build( array(
										'select'	=> 'm.member_id, m.name, m.member_group_id, m.email', 
										'from'		=> array( 'members' => 'm' ),
										'where'		=> 'm.member_id' . $mids,
										'add_join'	=> array(
															array(
																'select'	=> 'g.g_access_cp',
																'from'		=> array( 'groups' => 'g' ),
																'where'		=> 'g.g_id=m.member_group_id',
																'type'		=> 'left',
																)
															)
								)		);
		ipsRegistry::DB()->execute();

		while ( $r = ipsRegistry::DB()->fetch() )
		{
			//-----------------------------------------
			// Non root admin attempting to edit root admin?
			//-----------------------------------------

			if( $check_admin )
			{
				if ( !ipsRegistry::member()->getProperty('g_access_cp') )
				{
					if ( $r['g_access_cp'] )
					{
						continue;
					}
				}
			}

			$tmp_mids[]	= $r['member_id'];
			$emails[]	= $r['email'];

			self::_removeFromCache( $r['member_id'] );
		}

		if ( ! count( $tmp_mids ) )
		{
			return false;
		}

		$mids = ' IN (' . implode( ",", $tmp_mids ) . ')';

		//-----------------------------------------
		// Get avatars / photo
		//-----------------------------------------

		$delete_files = array();

		ipsRegistry::DB()->build( array( 'select' => '*', 'from' => 'profile_portal', 'where' => 'pp_member_id' . $mids ) );
		ipsRegistry::DB()->execute();

		while( $r = ipsRegistry::DB()->fetch() )
		{
			if ( $r['pp_main_photo']  )
			{
				$delete_files[] = $r['pp_main_photo'];
			}

			if ( $r['pp_thumb_photo']  )
			{
				$delete_files[] = $r['pp_thumb_photo'];
			}
			
			if ( $r['avatar_type'] == 'upload' and $r['avatar_location'] )
			{
				$delete_files[] = $r['avatar_location'];
			}
		}

		//-----------------------------------------
		// Take care of forum stuff
		//-----------------------------------------

		ipsRegistry::DB()->update( 'posts'					, array( 'author_id'  => 0 ), "author_id" . $mids );
		ipsRegistry::DB()->update( 'topics'					, array( 'starter_id' => 0 ), "starter_id" . $mids );
		ipsRegistry::DB()->update( 'announcements'			, array( 'announce_member_id' => 0 ), "announce_member_id" . $mids );
		ipsRegistry::DB()->update( 'attachments'			, array( 'attach_member_id' => 0 ), "attach_member_id" . $mids );
		ipsRegistry::DB()->update( 'polls'					, array( 'starter_id' => 0 ), "starter_id" . $mids );
		//ipsRegistry::DB()->update( 'topic_ratings'			, array( 'rating_member_id' => 0 ), "rating_member_id" . $mids );
		ipsRegistry::DB()->update( 'voters'					, array( 'member_id' => 0 ), "member_id" . $mids );
		ipsRegistry::DB()->update( 'forums'					, array( 'last_poster_name' => '' ), "last_poster_id" . $mids );
		ipsRegistry::DB()->update( 'forums'					, array( 'seo_last_name' => '' ), "last_poster_id" . $mids );
		ipsRegistry::DB()->update( 'forums'					, array( 'last_poster_id' => 0 ), "last_poster_id" . $mids );

		//-----------------------------------------
		// Clean up profile stuff
		//-----------------------------------------

		ipsRegistry::DB()->update( 'profile_comments'		, array( 'comment_by_member_id' => 0 ), "comment_by_member_id" . $mids );
		ipsRegistry::DB()->update( 'profile_ratings'		, array( 'rating_by_member_id' => 0 ), "rating_by_member_id" . $mids );

		ipsRegistry::DB()->delete( 'profile_comments'		, "comment_for_member_id" . $mids );
		ipsRegistry::DB()->delete( 'profile_ratings'		, "rating_for_member_id" . $mids );

		ipsRegistry::DB()->delete( 'profile_portal'			, "pp_member_id" . $mids );
		ipsRegistry::DB()->delete( 'profile_portal_views'	, "views_member_id" . $mids );
		ipsRegistry::DB()->delete( 'profile_friends'		, "friends_member_id" . $mids );
		ipsRegistry::DB()->delete( 'profile_friends'		, "friends_friend_id" . $mids );

		ipsRegistry::DB()->delete( 'dnames_change'			, "dname_member_id" . $mids );

		//-----------------------------------------
		// Delete member...
		//-----------------------------------------

		ipsRegistry::DB()->delete( 'pfields_content'		, "member_id" . $mids );
		ipsRegistry::DB()->delete( 'members_partial'		, "partial_member_id" . $mids );
		ipsRegistry::DB()->delete( 'moderators'				, "member_id" . $mids );
		ipsRegistry::DB()->delete( 'sessions'				, "member_id" . $mids );
		ipsRegistry::DB()->delete( 'warn_logs'				, "wlog_mid" . $mids );
		ipsRegistry::DB()->update( 'warn_logs'				, array( 'wlog_addedby' => 0 ), "wlog_addedby" . $mids );

		//-----------------------------------------
		// Update admin stuff
		//-----------------------------------------

		ipsRegistry::DB()->delete( 'admin_permission_rows'	, "row_id_type='member' AND row_id" . $mids );
		ipsRegistry::DB()->delete( 'core_sys_cp_sessions' 	, 'session_member_id' . $mids );
		ipsRegistry::DB()->update( 'upgrade_history'		, array( 'upgrade_mid' => 0 ), "upgrade_mid" . $mids );

		//-----------------------------------------
		// Fix up member messages...
		//-----------------------------------------
		
		ipsRegistry::DB()->delete( 'message_topic_user_map'	, 'map_user_id' . $mids );
		ipsRegistry::DB()->update( 'message_posts'			, array( 'msg_author_id' => 0 ), 'msg_author_id' . $mids );
		ipsRegistry::DB()->update( 'message_topics'			, array( 'mt_starter_id' => 0 ), 'mt_starter_id' . $mids );
		
		ipsRegistry::DB()->delete( 'ignored_users'			, "ignore_owner_id" . $mids . " or ignore_ignore_id" . $mids );

		//-----------------------------------------
		// Delete subs, views, markers
		//-----------------------------------------

		ipsRegistry::DB()->delete( 'tracker'				, "member_id" . $mids );
		ipsRegistry::DB()->delete( 'forum_tracker'			, "member_id" . $mids );
		ipsRegistry::DB()->delete( 'core_item_markers'		, "item_member_id" . $mids );

		//-----------------------------------------
		// Delete from validating..
		//-----------------------------------------

		ipsRegistry::DB()->delete( 'validating'				, "member_id" . $mids );
		ipsRegistry::DB()->delete( 'members'				, "member_id" . $mids );

		//-----------------------------------------
		// Delete avatars / photos
		//-----------------------------------------

		if ( count($delete_files) )
		{
			foreach( $delete_files as $file )
			{
				@unlink( ipsRegistry::$settings['upload_dir'] . "/" . $file );
			}
		}

		//-----------------------------------------
		// Member Sync
		//-----------------------------------------

		IPSLib::runMemberSync( 'onDelete', $mids );
		
		/* Remove from cache */
		IPSContentCache::drop( 'sig', $tmp_mids );
		
		//-----------------------------------------
		// Get current stats...
		//-----------------------------------------

		ipsRegistry::cache()->rebuildCache( 'stats', 'global' );
		ipsRegistry::cache()->rebuildCache( 'moderators', 'global' );
	}

	/**
	 * Set up moderator, populate moderator functions
	 *
	 * @access	public
	 * @param	array 		Array of member data
	 * @return	array 		Array of member data populated with moderator details
	 */
	static public function setUpModerator( $member )
	{
		$other_mgroups = array();
		
		if ( $member['member_group_id'] != ipsRegistry::$settings['guest_group'] )
		{
			//-----------------------------------------
			// Sprinkle on some moderator stuff...
			//-----------------------------------------

			if ( $member['g_is_supmod'] == 1 )
			{
				$member['is_mod'] = 1;
			}
			else if ( is_array(ipsRegistry::cache()->getCache('moderators')) AND count(ipsRegistry::cache()->getCache('moderators')) )
			{
				$other_mgroups = array();

				if ( IPSText::cleanPermString( $member['mgroup_others'] ) )
				{
					$other_mgroups = explode( ",", IPSText::cleanPermString( $member['mgroup_others'] ) );
				}
			}
			
			if( is_array(ipsRegistry::cache()->getCache('moderators')) AND count(ipsRegistry::cache()->getCache('moderators')) )
			{
				$_mod_forums = isset( $member['forumsModeratorData'] ) && is_array( $member['forumsModeratorData'] ) ? $member['forumsModeratorData'] : array();
				
				foreach( ipsRegistry::cache()->getCache('moderators') as $r )
				{
					$modForumIds = explode( ',', IPSText::cleanPermString( $r['forum_id'] ) );
					
					if ( $r['member_id'] == $member['member_id'] )
					{
						foreach( $modForumIds as $modForumId )
						{
							$_mod_forums[ $modForumId ] = $r;
						}

						$member['is_mod'] = 1;
					}
					else if( $r['group_id'] == $member['member_group_id'] )
					{
						// Individual mods override group mod settings
						// If array is set, don't override it

						foreach( $modForumIds as $modForumId )
						{
							if( !is_array($_mod_forums[ $modForumId ]) OR !count($_mod_forums[ $modForumId ]) )
							{
								$_mod_forums[ $modForumId ] = $r;
							}
						}

						$member['is_mod'] = 1;
					}
					else if( count( $other_mgroups ) AND in_array( $r['group_id'], $other_mgroups ) )
					{
						// Individual mods override group mod settings
						// If array is set, don't override it
	
						if( !is_array($_mod_forums[ $r['forum_id'] ]) OR !count($_mod_forums[ $r['forum_id'] ]) )
						{
							$_mod_forums[ $r['forum_id'] ] = $r;
						}
	
						$member['is_mod'] = 1;
					}						
				}
				
				$member['forumsModeratorData'] = $_mod_forums;
			}
		}

		return $member;
	}
	
	/**
	 * Fetches SEO name, updating the table if required
	 *
	 * @access	public
	 * @param	array		Member data
	 * @return	string		SEO Name
	 */
	static public function fetchSeoName( $memberData )
	{
		if ( ! is_array( $memberData ) OR ! $memberData['member_id'] )
		{
			return;
		}
		
		if ( isset( $memberData['members_seo_name'] ) and ( $memberData['members_seo_name'] ) )
		{
			return $memberData['members_seo_name'];
		}
		else if ( isset( $memberData['members_display_name'] ) and ( $memberData['members_display_name'] ) )
		{
			$_seoName = IPSText::makeSeoTitle( $memberData['members_display_name'] );

			ipsRegistry::DB()->update( 'members', array( 'members_seo_name' => $_seoName ), 'member_id=' . $memberData['member_id'] );
			
			return $_seoName;
		}
		else
		{
			return '-';
		}
	}
	
	/**
	 * Fetches Ignore user data
	 *
	 * @access	public
	 * @param	array		Member data
	 * @return	array		Array of ignored users
	 */
	static public function fetchIgnoredUsers( $memberData )
	{
		/* INIT */
		$ignore_users = array();
		
		if ( $memberData['member_id'] )
		{
			/* < 3.0.0 used comma delisted string. 3.0.0+ uses serialized array */
			if ( strstr( $memberData['ignored_users'], 'a:' ) )
			{
				$data = unserialize( $memberData['ignored_users'] );
				
				return ( is_array( $data ) ) ? $data : array();
			}
			else
			{
				if ( $memberData['ignored_users'] )
				{
					$_data = explode( ",", $memberData['ignored_users'] );
				
					foreach( $_data as $id )
					{
						if ( $id )
						{
							$ignore_users[ $id ] = array( 'ignore_ignore_id' => $id,
														  'ignore_messages'  => 0,
														  'ignore_topics'    => 1 );
						}
					}
				}
				
				/* Now fetch them from the DB */
				ipsRegistry::DB()->build( array( 'select' => '*', 'from' => 'ignored_users', 'where' => "ignore_owner_id=" . $memberData['member_id'] ) );
				ipsRegistry::DB()->execute();

				while( $r = ipsRegistry::DB()->fetch() )
				{
					$ignore_users[ $r['ignore_ignore_id'] ] = array( 'ignore_ignore_id' => $r['ignore_ignore_id'],
												  					 'ignore_messages'  => $r['ignore_messages'],
																	 'ignore_topics'    => $r['ignore_topics'] );
				}
				
				/* Update.... */
				self::save( $memberData['member_id'], array( 'core' => array( 'ignored_users' => serialize( $ignore_users ) ) ) );
			}
		}
		
		return $ignore_users;
	}
	
	/**
	 * Updates member.ignored_users
	 *
	 * @access	public
	 * @param	mixed		Member ID or Member data
	 * @return	array		Array of ignored users
	 */
	static public function rebuildIgnoredUsersCache( $member )
	{
		/* INIT */
		$ignore_users = array();
		
		$memberData = ( ! is_array( $member ) ) ? self::load( $member, 'all' ) : $member;
		
		/* Continue */
		if ( $memberData['member_id'] )
		{
			/* Fetch from DB */
			ipsRegistry::DB()->build( array( 'select' => '*', 'from' => 'ignored_users', 'where' => "ignore_owner_id=" . $memberData['member_id'] ) );
			ipsRegistry::DB()->execute();

			while( $r = ipsRegistry::DB()->fetch() )
			{
				$ignore_users[ $r['ignore_ignore_id'] ] = array( 'ignore_ignore_id' => $r['ignore_ignore_id'],
											  					 'ignore_messages'  => $r['ignore_messages'],
																 'ignore_topics'    => $r['ignore_topics'] );
			}
		
			/* Update.... */
			self::save( $memberData['member_id'], array( 'core' => array( 'ignored_users' => serialize( $ignore_users ) ) ) );
		}
	}

	/**
	 * Retrieve the member's location
	 *
	 * @access	public
	 * @author	Brandon Farber
	 * @param	array 		Member information (including session info!)
	 * @return	array 		Member info with session info parsed
	 * @since	IPB 3.0
	 **/
	static public function getLocation( $member )
	{
		$member['online_extra'] = "";

		//-----------------------------------------
		// Grab 'where' info
		//-----------------------------------------

		if( $member['current_appcomponent'] )
		{
			$filename = IPSLib::getAppDir(  IPSText::alphanumericalClean($member['current_appcomponent']) ) . '/extensions/coreExtensions.php';

			if ( file_exists( $filename ) )
			{
				require_once( $filename );
				$toload           = 'publicSessions__' . IPSText::alphanumericalClean($member['current_appcomponent']);
				
				if( class_exists( $toload ) )
				{
					$loader           = new $toload;
	
					if( method_exists( $loader, 'parseOnlineEntries' ) )
					{
						$tmp = $loader->parseOnlineEntries( array( $member['id'] => $member ) );
	
						// Yes, this is really id - it's session id, not member id
						if( isset( $tmp[ $member['id'] ] ) && is_array( $tmp[ $member['id'] ] ) && count( $tmp[ $member['id'] ] ) )
						{
							if ( isset( $tmp[ $member['id'] ]['_whereLinkSeo']) )
							{
								$member['online_extra'] = "{$tmp[ $member['id'] ]['where_line']} <a href='" . $tmp[ $member['id'] ]['_whereLinkSeo'] . "' title='" . $tmp[ $member['id'] ]['where_line'] . ' ' . $tmp[ $member['id'] ]['where_line_more'] . "'>" . IPSText::truncate( $tmp[ $member['id'] ]['where_line_more'], 35 ) . "</a>";
							}
							/* @link	http://community.invisionpower.com/tracker/issue-20598-where-link-not-taken-into-account-on-profile-page-if-no-where-line-more-specified/ */
							else if ( isset($tmp[ $member['id'] ]['where_link']) AND $tmp[ $member['id'] ]['where_line_more'] )
							{
								$member['online_extra'] = "{$tmp[ $member['id'] ]['where_line']} <a href='" . ipsRegistry::$settings['base_url'] . "{$tmp[ $member['id'] ]['where_link']}' title='" . $tmp[ $member['id'] ]['where_line'] . ' ' . $tmp[ $member['id'] ]['where_line_more'] . "'>" . IPSText::truncate( $tmp[ $member['id'] ]['where_line_more'], 35 ) . "</a>";
							}
							else if ( isset($tmp[ $member['id'] ]['where_link']) )
							{
								$member['online_extra'] = "<a href='" . ipsRegistry::$settings['base_url'] . "{$tmp[ $member['id'] ]['where_link']}' title='" . $tmp[ $member['id'] ]['where_line'] . "'>" . IPSText::truncate( $tmp[ $member['id'] ]['where_line'], 35 ) . "</a>";
							}
							else
							{
								$member['online_extra'] = $tmp[ $member['id'] ]['where_line'];
							}
						}
					}
				}
			}
		}

		if ( ! $member['online_extra'] )
		{
			$member['online_extra'] = $member['id'] ? ipsRegistry::getClass('class_localization')->words['board_index'] 
													: ipsRegistry::getClass('class_localization')->words['not_online'];
		}

		return $member;
	}

	/**
	 * Determine if two members are friends
	 *
	 * @access	public
	 * @author	Brandon Farber
	 * @param	integer		Member ID to check for
	 * @param	integer 	Member ID to check against (defaults to current member id)
	 * @return	boolean		Whether they are friends or not
	 * @since	IPB 3.0
	 **/
	static public function checkFriendStatus( $memberId, $checkAgainst=0 )
	{
		/**
		 * If no member id, obviously not friends
		 */
		if( !$memberId )
		{
			return false;
		}

		/**
		 * Get member data
		 */
		$memberData	= array();

		if( !$checkAgainst )
		{
			$memberData	= ipsRegistry::instance()->member()->getProperty('_cache');
		}
		else
		{
			$member		= self::load( $checkAgainst, 'extendedProfile' );
			$memberData	= $member['_cache'];
		}

		/**
		 * Do we have a friends cache array?
		 */
		if( !$memberData['friends'] OR !is_array($memberData['friends']) OR !count($memberData['friends']) )
		{
			return false;
		}

		/**
		 * If there is, then check it..
		 */
		return in_array( $memberId, array_keys( $memberData['friends'] ) );
	}
	
	/**
	 * Determine if a member is ignoring another member
	 *
	 * @access	public
	 * @author	Brandon Farber
	 * @param	integer		Member ID to check for
	 * @param	integer 	Member ID to check against (defaults to current member id)
	 * @param	string		Type of ignoring to check [messages|topics].  Omit to check any type.
	 * @return	boolean		Whether the member id to check for is being ignored by the member id to check against
	 * @since	IPB 3.0
	 **/
	static public function checkIgnoredStatus( $memberId, $checkAgainst=0, $type=false )
	{
		/**
		 * If no member id, obviously not ignored
		 */
		if( !$memberId )
		{
			return false;
		}

		/**
		 * Get member data
		 */
		$memberData	= array();

		if( !$checkAgainst )
		{
			/**
			 * Ignored users loaded at runtime and stored in an array...loop
			 */
			foreach( ipsRegistry::instance()->member()->ignored_users as $ignoredUser )
			{
				/**
				 * We found the user?
				 */
				if( $ignoredUser['ignore_ignore_id'] )
				{
					/**
					 * If not specifying a type, then just return
					 */
					if( !$type )
					{
						return true;
					}
					/**
					 * Otherwise verify we are ignoring that type
					 */
					else if( $ignoredUser[ 'ignore_' . $type ] )
					{
						return true;
					}
				}
			}
		}
		else
		{
			/**
			 * See if checkAgainst is ignoring memberId
			 */
			$checkAgainst	= intval($checkAgainst);
			$ignoredUser	= ipsRegistry::instance()->member()->DB()->buildAndFetch( array( 'select' => '*', 'from' => 'ignored_users', 'where' => 'ignore_owner_id=' . $checkAgainst . ' AND ignore_ignore_id=' . $memberId ) );
			
			/**
			 * No?
			 */
			if( !$ignoredUser['ignore_id'] )
			{
				return false;
			}
			/**
			 * He is?
			 */
			else
			{
				/**
				 * If not specifying a type, then just return
				 */
				if( !$type )
				{
					return true;
				}
				/**
				 * Otherwise verify we are ignoring that type
				 */
				else if( $ignoredUser[ 'ignore_' . $type ] )
				{
					return true;
				}
			}
		}

		/**
		 * If we're here (which we shouldn't be) just return false
		 */
		return false;
	}

	/**
	 * Retrieve all IP addresses a user (or multiple users) have used
	 *
	 * @access	public
	 * @param 	mixed		[Integer] member ID or [Array] array of member ids
	 * @param	string		Defaults to 'All', otherwise specify which tables to check (comma separated)
	 * @return	array		Multi-dimensional array of found IP addresses in which sections
	 */
	static public function findIPAddresses( $id, $tables_to_check='all' )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------

		$ip_addresses 	= array();
		$tables			= array(
							'admin_logs'			=> array( 'member_id', 'ip_address', 'ctime' ),
							'dnames_change'			=> array( 'dname_member_id', 'dname_ip_address', 'dname_date' ),
							'email_logs'			=> array( 'from_member_id', 'from_ip_address', 'email_date' ),
							'members'				=> array( 'member_id', 'ip_address', 'joined' ),
							'message_posts'			=> array( 'msg_author_id', 'msg_ip_address', 'msg_date' ),
							'moderator_logs'		=> array( 'member_id', 'ip_address', 'ctime' ),
							'posts'					=> array( 'author_id', 'ip_address', 'post_date' ),
							'profile_comments'		=> array( 'comment_by_member_id', 'comment_ip_address', 'comment_date' ),
							'profile_ratings'		=> array( 'rating_by_member_id', 'rating_ip_address', 'rating_added' ),
							'sessions'				=> array( 'member_id', 'ip_address', 'running_time' ),
							'topic_ratings'			=> array( 'rating_member_id', 'rating_ip_address', '' ),
							'validating'			=> array( 'member_id', 'ip_address', 'entry_date' ),
							'voters'				=> array( 'member_id', 'ip_address', 'vote_date' ),
							'error_logs'			=> array( 'log_member', 'log_ip_address', 'log_date' ),
							);

		//-----------------------------------------
		// Check apps
		// @see http://forums.invisionpower.com/tracker/issue-16966-members-download-manag/
		//-----------------------------------------
		
		foreach( ipsRegistry::$applications as $appDir => $data )
		{
			if( file_exists( IPSLib::getAppDir( $appDir ) . "/extensions/coreExtensions.php") )
			{
				require_once( IPSLib::getAppDir( $appDir ) . "/extensions/coreExtensions.php" );
				$app_dir	= IPSLib::getAppDir( $appDir );

				if( class_exists( $app_dir . '_findIpAddress' ) )
				{
					$classX 	= $app_dir . '_findIpAddress';

					$ipLookup	= new $classX( ipsRegistry::instance() );
					
					if( method_exists( $ipLookup, 'getTables' ) )
					{
						$tables		= array_merge( $tables, $ipLookup->getTables() );
					}
				}
			}
		}

		//-----------------------------------------
		// Sort out thingie
		//-----------------------------------------

		if ( is_array( $id ) )
		{
			$id = IPSLib::cleanIntArray( $id );

			$mids = ' IN (' . implode( ",", $id ) . ')';
		}
		else
		{
			$mids = ' = ' . intval($id);
		}

		//-----------------------------------------
		// Got tables?
		//-----------------------------------------

		$_tables = explode( ',', $tables_to_check );

		if( !is_array($_tables) OR !count($_tables) )
		{
			return array();
		}

		//-----------------------------------------
		// Loop through them and grab the IPs
		//-----------------------------------------

		foreach( $tables as $tablename => $fields )
		{
			if( $tables_to_check == 'all' OR in_array( $tablename, $_tables ) )
			{
				$extra = '';

				if( $fields[2] )
				{
					$extra = ', ' . $fields[2] . ' as date';
				}

				ipsRegistry::DB()->build( array( 'select' => $fields[1] . $extra, 'from' => $tablename, 'where' => $fields[0] . $mids ) );
				ipsRegistry::DB()->execute();

				while( $r = ipsRegistry::DB()->fetch() )
				{
					if( $r[ $fields[1] ] )
					{
						$r['date']	= $r['date'] > $ip_addresses[ $r[ $fields[1] ] ][1] ? $r['date'] : ( $ip_addresses[ $r[ $fields[1] ] ][1] ? $ip_addresses[ $r[ $fields[1] ] ][1] : 0 );

						$ip_addresses[ $r[ $fields[1] ] ]	= array( intval($ip_addresses[ $r[ $fields[1] ] ][0]) + 1, $r['date'] );
					}
				}
			}
		}

		//-----------------------------------------
		// Here are your IPs kind sir.  kthxbai
		//-----------------------------------------

		return $ip_addresses;
	}

	/**
	 * Get / set member's ban info
	 *
	 * @access	public
	 * @param	array	Ban info (unit, timespan, date_end, date_start)
	 * @return	mixed
	 * @since	2.0
	 */
	static public function processBanEntry( $bline )
	{
		if ( is_array( $bline ) )
		{
			// Set ( 'timespan' 'unit' )

			$factor = $bline['unit'] == 'd' ? 86400 : 3600;

			$date_end = time() + ( $bline['timespan'] * $factor );

			return time() . ':' . $date_end . ':' . $bline['timespan'] . ':' . $bline['unit'];
		}
		else
		{
			$arr = array();

			list( $arr['date_start'], $arr['date_end'], $arr['timespan'], $arr['unit'] ) = explode( ":", $bline );

			return $arr;
		}
	}

	/**
	 * Unpacks a member's cache.
	 * Left as a function for any other processing
	 *
	 * @access	public
	 * @param	string	Serialized cache array
	 * @return	array	Unpacked array
	 */
	static public function unpackMemberCache( $cache_serialized_array="" )
	{
		return unserialize( $cache_serialized_array );
	}

	/**
	 * Packs up member's cache
	 *
	 * Takes an existing array and updates member's DB row
	 * This will overwrite any existing entries by the same
	 * key and create new entries for non-existing rows
	 *
	 * @access	public
	 * @param	integer		Member ID
	 * @param	array		New array
	 * @param	array		Current Array (optional)
	 * @return	boolean
	 */
	static public function packMemberCache( $member_id, $new_cache_array, $current_cache_array='' )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------

		$member_id = intval( $member_id );

		//-----------------------------------------
		// Got a member ID?
		//-----------------------------------------

		if ( ! $member_id )
		{
			return FALSE;
		}

		//-----------------------------------------
		// Got anything to update?
		//-----------------------------------------

		if ( ! is_array( $new_cache_array ) )
		{
			return FALSE;
		}

		//-----------------------------------------
		// Got a current cache?
		//-----------------------------------------

		if ( ! is_array( $current_cache_array ) )
		{
			$member = ipsRegistry::DB()->buildAndFetch( array( 'select' => "members_cache", 'from' => 'members', 'where' => 'member_id='.$member_id ) );

			$member['members_cache'] = $member['members_cache'] ? $member['members_cache'] : array();

			$current_cache_array = @unserialize( $member['members_cache'] );
		}

		//-----------------------------------------
		// Overwrite...
		//-----------------------------------------

		foreach( $new_cache_array as $k => $v )
		{
			$current_cache_array[ $k ] = $v;
		}

		//-----------------------------------------
		// Update...
		//-----------------------------------------

		ipsRegistry::DB()->update( 'members', array( 'members_cache' => serialize( $current_cache_array ) ), 'member_id='.$member_id );

		//-----------------------------------------
		// Set member array right...
		//-----------------------------------------
		
		if ( self::$data['member_id'] == $member_id )
		{
			self::$data['_cache']			= $current_cache_array;
			self::$data['members_cache']	= serialize( $current_cache_array );
		}
	}

	/**
	 * Check forum permissions
	 *
	 * @access	public
	 * @param	string		Permission type
	 * @param	int			Forum ID to check against
	 * @return	boolean
	 * @since	2.0
	 */
	static public function checkPermissions( $perm="", $forumID=0 )
	{
		/* Bit of a hack here, ugly */
		if ( ipsRegistry::isClassLoaded('class_forums') !== TRUE )
		{
			$classToLoad = IPSLib::loadLibrary( IPSLib::getAppDir( 'forums' ) . "/sources/classes/forums/class_forums.php", 'class_forums' );
			ipsRegistry::setClass( 'class_forums', new $classToLoad( ipsRegistry::instance() ) );

			ipsRegistry::getClass('class_forums')->strip_invisible = 1;
			ipsRegistry::getClass('class_forums')->forumsInit();
		}

		return ipsRegistry::getClass( 'permissions' )->check( $perm, ipsRegistry::getClass('class_forums')->forum_by_id[ $forumID ] );
	}

	/**
	 * Check forum permissions
	 *
	 * @access	public
	 * @param	string	Comma delim. of group IDs (2,4,5,6)
	 * @return	string  Comma delim of PERM MASK ids
	 * @since	2.1.1
	 * @deprecated		Will be removed in a future version
	 */
	static public function createPermsFromGroup( $in_group_ids )
    {
    	$out   = "";
    	$cache = ipsRegistry::cache()->getCache('group_cache');

    	if ( $in_group_ids == '*' )
    	{
    		foreach( $cache as $data )
			{
				if ( ! $data['g_id'] )
				{
					continue;
				}

				//-----------------------------------------
				// Got a perm mask?
				//-----------------------------------------

				if ( $data['g_perm_id'] )
				{
					$out .= ',' . $data['g_perm_id'];
				}
			}
    	}
    	else if ( $in_group_ids )
		{
			$groups_id = explode( ',', $in_group_ids );

			if ( count( $groups_id ) )
			{
				foreach( $groups_id as $pid )
				{
					if ( ! $cache[ $pid ]['g_id'] )
					{
						continue;
					}

					//-----------------------------------------
					// Got a perm mask?
					//-----------------------------------------

					if ( $cache[ $pid ]['g_perm_id'] )
					{
						$out .= ',' . $cache[ $pid ]['g_perm_id'];
					}
				}
			}
		}

		//-----------------------------------------
		// Tidy perms_id
		//-----------------------------------------

		$out = IPSText::cleanPermString( $out );

		return $out;
	}

	/**
	 * Set up defaults for a guest user
	 *
	 * @access	public
	 * @param	string	Guest name
	 * @return	array 	Guest record
	 * @since	2.0
	 * @todo		[Future] We may want to move this into the session class at some point
	 */
    static public function setUpGuest( $name="" )
    {
		$cache = ipsRegistry::cache()->getCache('group_cache');
		$name  = $name ? $name : ( ipsRegistry::isClassLoaded( 'class_localization' ) ? ipsRegistry::getClass('class_localization')->words['global_guestname'] : 'Guest' );

    	$array = array(   'name'          		 	=> $name,
    				   	  'members_display_name' 	=> $name,
	    				  '_members_display_name' 	=> $name,
						  'members_seo_name'		=> IPSText::makeSeoTitle( $name ),
	    				  'member_id'      		 	=> 0,
	    				  'password'      		 	=> '',
	    				  'email'         		 	=> '',
	    				  'title'         		 	=> '',
	    				  'posts'					=> 0,
	    				  'member_group_id'		 	=> ipsRegistry::$settings['guest_group'],
	    				  'view_sigs'     		 	=> ipsRegistry::$settings['guests_sig'],
	    				  'view_img'      		 	=> ipsRegistry::$settings['guests_img'],
	    				  'view_avs'     		 	=> ipsRegistry::$settings['guests_ava'],
	    				  'member_forum_markers' 	=> array(),
	    				  'avatar'				 	=> '',
	    				  'member_posts'		 	=> '',
	    				  'g_title'		 			=> $cache[ ipsRegistry::$settings['guest_group'] ]['g_title'],
	    				  'member_rank_img'	 	 	=> '',
	    				  'member_joined'		 	=> '',
	    				  'member_location'		 	=> '',
	    				  'member_number'		 	=> '',
	    				  'members_auto_dst'	 	=> 0,
	    				  'has_blog'			 	=> 0,
	    				  'has_gallery'			 	=> 0,
	    				  'is_mod'				 	=> 0,
	    				  'last_visit'			 	=> time(),
	    				  'login_anonymous'		 	=> '',
	    				  'mgroup_others'		 	=> '',
	    				  'org_perm_id'			 	=> '',
	    				  '_cache'				 	=> array( 'qr_open' => 0 ),
	    				  'auto_track'			 	=> 0,
	    				  'ignored_users'		 	=> NULL,
	    				  'members_editor_choice' 	=> 'std',
						  '_cache'                	=> array( 'friends' => array() ),
						  '_group_formatted'		=> IPSLib::makeNameFormatted( $cache[ ipsRegistry::$settings['guest_group'] ]['g_title'], ipsRegistry::$settings['guest_group'] ),
	    				);
	    
	    /* Add in the group image, if we have one */
		$member['member_rank_img']		= '';
		$member['member_rank_img_i']	= '';

		if ( $cache[ $array['member_group_id'] ]['g_icon'] )
		{
			$_img = $cache[ $array['member_group_id'] ]['g_icon'];
			
			if ( substr( $_img, 0, 4 ) != 'http' )
			{
				$_img = ipsRegistry::$settings['_original_base_url'] . '/' . ltrim( $_img, '/' );
			}
			
			$array['member_rank_img_i']	= 'img';
			$array['member_rank_img']		= $_img;
		}

		return is_array( $cache[ ipsRegistry::$settings['guest_group'] ] ) ? array_merge( $array, $cache[ ipsRegistry::$settings['guest_group'] ] ) : $array;
    }

	/**
	 * Parse a member's profile photo
	 *
	 * @access	public
	 * @param	mixed	Either array of member data, or member ID to self load
	 * @return	array 	Member's photo details
	 */
    static public function buildProfilePhoto( $member )
    {
		//-----------------------------------------
		// Load the member?
		//-----------------------------------------

		if ( ! is_array( $member ) AND ( $member == intval( $member ) ) AND $member > 0 )
		{
			$member = self::load( $member, 'extendedProfile' );
		}
		else if ( $member == 0 )
		{
			$member = array();
		}
		
		//-----------------------------------------
		// Facebook Sync
		//-----------------------------------------
	
		if ( IPSLib::fbc_enabled() === TRUE )
		{ 
			if ( $member['fb_uid'] and $member['fb_bwoptions'] )
			{
				$_sync   = time() - 86400;
				$_active = time() - ( 86400 * 90 );
				
				/* We have a linked member and options, so check if they haven't sync'd in 24 hours and have been active in the past 90 days... */
				if ( ( $member['fb_lastsync'] < $_sync ) AND ( $member['last_visit'] > $_active ) )
				{
					try
					{
						require_once( IPS_ROOT_PATH . 'sources/classes/facebook/connect.php' );
						$facebook = new facebook_connect( ipsRegistry::instance(), null, null, true );
					
						$_member = $facebook->syncMember( $member, $member['fb_token'], $member['fb_uid'] );
						
						if ( $_member AND is_array( $_member ) )
						{
							$member = $_member;
							unset( $_member );
						}
					}
					catch( Exception $error )
					{
						$msg = $error->getMessage();

						switch( $msg )
						{
							case 'NOT_LINKED':
							case 'NO_MEMBER':
							case 'FACEBOOK_NO_APP_ID':
							break;
						}
					}
				}
			}
		}
		
		//-----------------------------------------
		// Twitter Sync
		//-----------------------------------------
	
		if ( IPSLib::twitter_enabled() === TRUE )
		{ 
			if ( $member['twitter_id'] and $member['tc_bwoptions'] )
			{
				$_sync   = time() - 10800;
				$_active = time() - ( 86400 * 90 );
				
				/* We have a linked member and options, so check if they haven't sync'd in 3 hours and have been active in the past 90 days... */
				if ( ( $member['tc_lastsync'] < $_sync ) AND ( $member['last_visit'] > $_active ) )
				{
					require_once( IPS_ROOT_PATH . 'sources/classes/twitter/connect.php' );
					$twitter = new twitter_connect( ipsRegistry::instance() );
					
					try
					{
						$_member = $twitter->syncMember( $member );
						
						if ( $_member AND is_array( $_member ) )
						{
							$member = $_member;
							unset( $_member );
						}
					}
					catch( Exception $error )
					{
						$msg = $error->getMessage();

						switch( $msg )
						{
							case 'NOT_LINKED':
							case 'NO_MEMBER':
							break;
						}
					}
				}
			}
		}

		//-----------------------------------------
		// Facebook?
		//-----------------------------------------
		
		if ( $member['fb_photo'] AND ipsRegistry::member()->getProperty('g_mem_info') )
		{
			$member['_has_photo']     = 1;
			
			/* Main... */
			$member['pp_main_photo']  = $member['fb_photo'];
			$member['pp_main_width']  = '*';
			$member['pp_main_height'] = '*';
			
			/* Thumb */
			$member['pp_thumb_photo']  = $member['fb_photo_thumb'];
			$member['pp_thumb_width']  = 50;
			$member['pp_thumb_height'] = 50;
			
			/* Mini */
			$member['pp_mini_photo']  = $member['fb_photo_thumb'];
			$member['pp_mini_width']  = 25;
			$member['pp_mini_height'] = 25;
		}
		else if ( $member['tc_photo']  AND ipsRegistry::member()->getProperty('g_mem_info') )
		{
			$member['_has_photo']     = 1;
			
			/* Main... */
			$member['pp_main_photo']  = $member['tc_photo'];
			$member['pp_main_width']  = '*';
			$member['pp_main_height'] = '*';
			
			/* Thumb */
			$member['pp_thumb_photo']  = $member['tc_photo'];
			$member['pp_thumb_width']  = 50;
			$member['pp_thumb_height'] = 50;
			
			/* Mini */
			$member['pp_mini_photo']  = $member['tc_photo'];
			$member['pp_mini_width']  = 25;
			$member['pp_mini_height'] = 25;
		}
		else
		{
			//-----------------------------------------
			// Main photo
			//-----------------------------------------

			if ( ! $member['pp_main_photo'] OR ! ipsRegistry::member()->getProperty('g_mem_info') )
			{
				$member['pp_main_photo']  = ipsRegistry::$settings['img_url'] . '/profile/default_large.png';
				$member['pp_main_width']  = 150;
				$member['pp_main_height'] = 150;
				$member['_has_photo']     = 0;
			}
			else
			{
				$member['pp_main_photo'] = ipsRegistry::$settings['upload_url'] . '/' . $member['pp_main_photo'];
				$member['_has_photo']    = 1;
			}

			//-----------------------------------------
			// Thumbie
			//-----------------------------------------

			if ( ! $member['pp_thumb_photo'] OR $member['pp_thumb_photo'] == 'profile/' )
			{
				if( $member['_has_photo'] )
				{
					$member['pp_thumb_photo']  = $member['pp_main_photo'];
				}
				else
				{
					$member['pp_thumb_photo']  = ipsRegistry::$settings['img_url'] . '/profile/default_thumb.png';
				}

				$member['pp_thumb_width']  = 50;
				$member['pp_thumb_height'] = 50;
			}
			else
			{
				if( $member['_has_photo'] )
				{
					$member['pp_thumb_photo'] = ipsRegistry::$settings['upload_url'] . '/' . $member['pp_thumb_photo'];
				}
				else
				{
					$member['pp_thumb_photo']  = ipsRegistry::$settings['img_url'] . '/profile/default_thumb.png';
				}
			}
			
			//-----------------------------------------
			// Try not to distort the image
			//-----------------------------------------
			
			if ( !ipsRegistry::member()->getProperty('g_mem_info') )
			{
				$member['pp_thumb_width']  = 50;
				$member['pp_thumb_height'] = 50;
			}

			//-----------------------------------------
			// Mini
			//-----------------------------------------

			$_data = IPSLib::scaleImage( array( 'max_height' => 25, 'max_width' => 25, 'cur_width' => $member['pp_thumb_width'], 'cur_height' => $member['pp_thumb_height'] ) );

			$member['pp_mini_photo']  = $member['pp_thumb_photo'];
			$member['pp_mini_width']  = $_data['img_width'];
			$member['pp_mini_height'] = $_data['img_height'];
		}
		
		return $member;
    }

	/**
	 * Parse a member for display
	 *
	 * @access	public
	 * @param	mixed	Either array of member data, or member ID to self load
	 * @param	array 	Array of flags to parse: 'signature', 'customFields', 'avatar', 'warn'
	 * @return	array 	Parsed member data
	 */
	static public function buildDisplayData( $member, $_parseFlags=array() )
	{
		$_NOW   = IPSDebug::getMemoryDebugFlag();
		
		//-----------------------------------------
		// Figure out parse flags
		//-----------------------------------------

		$parseFlags = array( 'signature'		=> isset( $_parseFlags['signature'] )    ? $_parseFlags['signature']    : 0,
							 'customFields'		=> isset( $_parseFlags['customFields'] ) ? $_parseFlags['customFields'] : 0,
							 'reputation'		=> isset( $_parseFlags['reputation'] )   ? $_parseFlags['reputation']   : 1,
							 'avatar'			=> isset( $_parseFlags['avatar'] )       ? $_parseFlags['avatar']       : 1,
							 'warn'				=> isset( $_parseFlags['warn'] )         ? $_parseFlags['warn']         : 1,
							 'cfSkinGroup'		=> isset( $_parseFlags['cfSkinGroup'] )  ? $_parseFlags['cfSkinGroup']  : '',
							 'cfGetGroupData'	=> isset( $_parseFlags['cfGetGroupData'] )  ? $_parseFlags['cfGetGroupData']  : '',
							 'cfLocation'		=> isset( $_parseFlags['cfLocation'] )  ? $_parseFlags['cfLocation']  : '',
							 'checkFormat'		=> isset( $_parseFlags['checkFormat'] )  ? $_parseFlags['checkFormat']  : 0,
							 'spamStatus'		=> isset( $_parseFlags['spamStatus'] )   ? $_parseFlags['spamStatus']  : 0 );

		if ( isset( $_parseFlags['__all__'] ) )
		{
			foreach( $parseFlags as $k => $v )
			{
				if( in_array( $k, array( 'cfSkinGroup', 'cfGetGroupData' ) ) )
				{
					continue;
				}
				
				$parseFlags[ $k ] = 1;
			}

			$parseFlags['spamStatus']  = ( isset( $parseFlags['spamStatus'] )  AND ( $parseFlags['spamStatus'] ) ) ? 1 : 0;
		}

		//-----------------------------------------
		// Load the member?
		//-----------------------------------------

		if ( ! is_array( $member ) AND ( $member == intval( $member ) AND $member > 0 ) )
		{
			$member = self::load( $member, 'all' );
		}
		
		//-----------------------------------------
		// Caching
		//-----------------------------------------
		
		static $buildMembers	= array();
		
		$_key	= $member['member_id'];
		$_arr   = serialize( $member );
		
		foreach( $parseFlags as $_flag => $_value )
		{
			$_key .= $_flag . $_value;
		}
		
		$_key	= md5($_key.$_arr);
		
		if( array_key_exists( $_key, $buildMembers ) )
		{
			IPSDebug::setMemoryDebugFlag( "IPSMember::buildDisplayData: ".$member['member_id']. " - CACHED", $_NOW );
			
			return $buildMembers[ $_key ];
		}

		//-----------------------------------------
		// Basics
		//-----------------------------------------
		
		if ( ! $member['member_group_id'] )
		{
			$member['member_group_id'] = ipsRegistry::$settings['guest_group'];
		}
		
		/* Unpack bitwise if required */
		if ( ! isset( $member['bw_is_spammer'] ) )
		{
			$member = self::buildBitWiseOptions( $member );
		}

		//-----------------------------------------
		// INIT
		//-----------------------------------------

		$rank_cache                = ipsRegistry::cache()->getCache( 'ranks' );
		$group_cache			   = ipsRegistry::cache()->getCache( 'group_cache' );
		$group_name                = IPSLib::makeNameFormatted( $group_cache[ $member['member_group_id'] ]['g_title'], $member['member_group_id'] );
		$pips                      = 0;
		$topic_id				   = intval( isset( ipsRegistry::$request[ 't' ] ) ? ipsRegistry::$request[ 't' ] : 0 );
		$forum_id				   = intval( isset( ipsRegistry::$request[ 'f' ] ) ? ipsRegistry::$request[ 'f' ] : 0 );
		
		//-----------------------------------------
		// SEO Name
		//-----------------------------------------
	
		$member['members_seo_name'] = self::fetchSeoName( $member );

		//-----------------------------------------
		// Avatar
		//-----------------------------------------

		if ( $parseFlags['avatar'] )
		{
			$member['avatar'] = self::buildAvatar( $member );
		}

		$member['_group_formatted'] = $group_name;

		//-----------------------------------------
		// Ranks
		//-----------------------------------------

		if ( is_array( $rank_cache ) AND count( $rank_cache ) )
		{
			foreach( $rank_cache as $k => $v)
			{
				if ($member['posts'] >= $v['POSTS'])
				{
					if( ! isset( $member['title'] ) || $member['title'] === '' || is_null($member['title']) )
					{
						$member['title'] = $v['TITLE'];
					}

					$pips = $v['PIPS'];
					break;
				}
			}
		}

		//-----------------------------------------
		// Group image
		//-----------------------------------------

		$member['member_rank_img']		= '';
		$member['member_rank_img_i']	= '';

		if ( $group_cache[ $member['member_group_id'] ]['g_icon'] )
		{
			$_img = $group_cache[ $member['member_group_id'] ]['g_icon'];
			
			if ( substr( $_img, 0, 4 ) != 'http' )
			{
				$_img = ipsRegistry::$settings['_original_base_url'] . '/' . ltrim( $_img, '/' );
			}
			
			$member['member_rank_img_i']	= 'img';
			$member['member_rank_img']		= $_img;
		}
		else if ( $pips )
		{
			if ( is_numeric( $pips ) )
			{
				for ($i = 1; $i <= $pips; ++$i)
				{
					$member['member_rank_img_i']	= 'pips';
					$member['member_rank_img']		.= ipsRegistry::getClass('output')->getReplacement('pip_pip');
				}
			}
			else
			{
				$member['member_rank_img_i']	= 'img';
				$member['member_rank_img']		= ipsRegistry::$settings['public_dir'] . 'style_extra/team_icons/' . $pips;
			}
		}

		//-----------------------------------------
		// Moderator data
		//-----------------------------------------
		
		if( ( $parseFlags['spamStatus'] OR $parseFlags['warn'] ) AND $member['member_id'] )
		{
			/* Possible forums class isn't init at this point */
			if ( ! ipsRegistry::isClassLoaded('class_forums' ) )
			{
				try
				{
					$viewingMember = IPSMember::setUpModerator( ipsRegistry::member()->fetchMemberData() );
					
					ipsRegistry::member()->setProperty('forumsModeratorData', $viewingMember['forumsModeratorData'] );
				}
				catch( Exception $error )
				{
					IPS_exception_error( $error );
				}
			}
			
			$moderator					= ipsRegistry::member()->getProperty('forumsModeratorData');
		}
		
		$forum_id					= intval( ipsRegistry::$request['f'] );

		//-----------------------------------------
		// Spammer status
		//-----------------------------------------

		if ( $parseFlags['spamStatus'] AND $member['member_id'] AND ipsRegistry::member()->getProperty('member_id') )
		{
			/* Defaults */
			$member['spamStatus']		= NULL;
			$member['spamImage']		= NULL;
			
			if ( isset( $moderator[ $forum_id ]['bw_flag_spammers'] ) AND ( $moderator[ $forum_id ]['bw_flag_spammers'] ) OR ipsRegistry::member()->getProperty('g_is_supmod') == 1 )
			{
				if ( !ipsRegistry::$settings['warn_on'] OR ( ! strstr( ','.ipsRegistry::$settings['warn_protected'].',', ','.$member['member_group_id'].',' ) ) )
				{
					if ( $member['bw_is_spammer'] )
					{
						$member['spamStatus'] = TRUE;
					}
					else
					{
						$member['spamStatus'] = FALSE;
					}
				}
			}
		}
				
		//-----------------------------------------
		// Warny porny?
		//-----------------------------------------

		if ( $parseFlags['warn'] AND $member['member_id'] )
		{
			$member['warn_percent']		= NULL;
			$member['can_edit_warn']	= false;

			$member['warn_img']			= NULL;
			
			if ( ipsRegistry::$settings['warn_on'] and ( ! strstr( ','.ipsRegistry::$settings['warn_protected'].',', ','.$member['member_group_id'].',' ) ) )
			{	
				/* Warnings */
				if ( ( isset($moderator[ $forum_id ]['allow_warn'])
					AND $moderator[ $forum_id ]['allow_warn'] )
					OR ( ipsRegistry::member()->getProperty('g_is_supmod') == 1 )
					OR ( ipsRegistry::$settings['warn_show_own'] and ( ipsRegistry::member()->getProperty('member_id') == $member['member_id'] ) )
				   )
				{
					// Work out which image to show.
					if ( $member['warn_level'] <= ipsRegistry::$settings['warn_min'] )
					{
						$member['warn_img']		= '{parse replacement="warn_0"}';
						$member['warn_percent']	= 0;
					}
					else if ( $member['warn_level'] >= ipsRegistry::$settings['warn_max'] )
					{
						$member['warn_img']		= '{parse replacement="warn_5"}';
						$member['warn_percent']	= 100;
					}
					else
					{
						$member['warn_percent']	= $member['warn_level'] ? sprintf( "%.0f", ( ($member['warn_level'] / ipsRegistry::$settings['warn_max']) * 100) ) : 0;

						if ( $member['warn_percent'] > 100 )
						{
							$member['warn_percent']	= 100;
						}

						if ( $member['warn_percent'] >= 81 )
						{
							$member['warn_img']	= '{parse replacement="warn_5"}';
						}
						else if ( $member['warn_percent'] >= 61 )
						{
							$member['warn_img']	= '{parse replacement="warn_4"}';
						}
						else if ( $member['warn_percent'] >= 41 )
						{
							$member['warn_img']	= '{parse replacement="warn_3"}';
						}
						else if ( $member['warn_percent'] >= 21 )
						{
							$member['warn_img']	= '{parse replacement="warn_2"}';
						}
						else if ( $member['warn_percent'] >= 1 )
						{
							$member['warn_img']	= '{parse replacement="warn_1"}';
						}
						else
						{
							$member['warn_img']	= '{parse replacement="warn_0"}';
						}
					}

					if ( $member['warn_percent'] < 1 )
					{
						$member['warn_percent']	= 0;
					}

					/* Bug 14770 - Change so you can't warn yourself */
					if ( (( isset($moderator[ $forum_id ]['allow_warn']) AND $moderator[ $forum_id ]['allow_warn'] ) or ipsRegistry::member()->getProperty('g_is_supmod') == 1) AND $member['member_id'] != ipsRegistry::member()->getProperty('member_id') )
					{
						$member['can_edit_warn']	= true;
					}
				}
			}
		}
		
		//-----------------------------------------
		// Profile fields stuff
		//-----------------------------------------

		$member['custom_fields'] = "";

		if( $parseFlags['customFields'] == 1 AND $member['member_id'] )
		{
			if ( isset( self::$_parsedCustomFields[ $member['member_id'] ] ) )
			{
				$member['custom_fields'] = self::$_parsedCustomFields[ $member['member_id'] ];
				
				if ( $parseFlags['cfGetGroupData'] AND isset( self::$_parsedCustomGroups[ $member['member_id'] ] ) AND is_array( self::$_parsedCustomGroups[ $member['member_id'] ] ) )
				{
					$member['custom_field_groups'] = self::$_parsedCustomGroups[ $member['member_id'] ];
				}
				else if( $parseFlags['cfGetGroupData'] )
				{
					$member['custom_field_groups']						= self::$custom_fields_class->fetchGroupTitles();
					self::$_parsedCustomGroups[ $member['member_id'] ]	= $member['custom_field_groups'];
				}
			}
			else
			{
				if ( !is_object( self::$custom_fields_class ) )
				{
					$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/customfields/profileFields.php', 'customProfileFields' );
					self::$custom_fields_class	= new $classToLoad();
				}
	
				if ( self::$custom_fields_class )
				{
					self::$custom_fields_class->member_data	= $member;
					self::$custom_fields_class->skinGroup	= $parseFlags['cfSkinGroup'];
					self::$custom_fields_class->initData();
					self::$custom_fields_class->parseToView( $parseFlags['checkFormat'], $parseFlags['cfLocation'] );

					$member['custom_fields']							= self::$custom_fields_class->out_fields;
					self::$_parsedCustomFields[ $member['member_id'] ]	= $member['custom_fields'];
					
					if ( $parseFlags['cfGetGroupData'] )
					{
						$member['custom_field_groups']						= self::$custom_fields_class->fetchGroupTitles();
						self::$_parsedCustomGroups[ $member['member_id'] ]	= $member['custom_field_groups'];
					}
				}
			}
		}

		//-----------------------------------------
		// Profile photo
		//-----------------------------------------

		$member = self::buildProfilePhoto( $member );

		//-----------------------------------------
		// Personal statement 'bbcode'
		//-----------------------------------------

		if( stripos( $member['pp_bio_content'], '[b]' ) !== false )
		{
			if( stripos( $member['pp_bio_content'], '[/b]' ) > stripos( $member['pp_bio_content'], '[b]' ) )
			{
				$member['pp_bio_content'] = str_ireplace( '[b]', '<strong>', $member['pp_bio_content'] );
				$member['pp_bio_content'] = str_ireplace( '[/b]', '</strong>', $member['pp_bio_content'] );
			}
		}

		if( stripos( $member['pp_bio_content'], '[i]' ) !== false )
		{
			if( stripos( $member['pp_bio_content'], '[/i]' ) > stripos( $member['pp_bio_content'], '[i]' ) )
			{
				$member['pp_bio_content'] = str_ireplace( '[i]', '<em>', $member['pp_bio_content'] );
				$member['pp_bio_content'] = str_ireplace( '[/i]', '</em>', $member['pp_bio_content'] );
			}
		}

		if( stripos( $member['pp_bio_content'], '[u]' ) !== false )
		{
			if( stripos( $member['pp_bio_content'], '[/u]' ) > stripos( $member['pp_bio_content'], '[u]' ) )
			{
				$member['pp_bio_content'] = str_ireplace( '[u]', '<span class="underscore">', $member['pp_bio_content'] );
				$member['pp_bio_content'] = str_ireplace( '[/u]', '</span>', $member['pp_bio_content'] );
			}
		}

		//-----------------------------------------
		// Signature bbcode
		//-----------------------------------------

		if ( isset( $member['signature'] ) AND $member['signature'] AND $parseFlags['signature'] )
		{
			if( isset(self::$_parsedSignatures[ $member['member_id'] ]) )
			{
				$member['signature'] = self::$_parsedSignatures[ $member['member_id'] ];
			}
			else
			{
				if ( $member['cache_content'] )
				{
					$member['signature'] = '<!--cached-' . gmdate( 'r', $member['cache_updated'] ) . '-->' . $member['cache_content'];
				}
				else
				{
					IPSText::getTextClass('bbcode')->parse_bbcode				= ipsRegistry::$settings['sig_allow_ibc'];
					IPSText::getTextClass('bbcode')->parse_smilies				= 0;
					IPSText::getTextClass('bbcode')->parse_html					= ipsRegistry::$settings['sig_allow_html'];
					IPSText::getTextClass('bbcode')->parse_nl2br				= 1;
					IPSText::getTextClass('bbcode')->parsing_section			= 'signatures';
					IPSText::getTextClass( 'bbcode' )->parsing_mgroup			= $member['member_group_id'];
					IPSText::getTextClass( 'bbcode' )->parsing_mgroup_others	= $member['mgroup_others'];				
					
					/* Work around */
					$_tmp = ipsRegistry::member()->getProperty( 'view_img' );
					ipsRegistry::member()->setProperty( 'view_img', 1 );

					$member['signature']	= IPSText::getTextClass('bbcode')->preDisplayParse( $member['signature'] );
					
					ipsRegistry::member()->setProperty( 'view_img', $_tmp );
				
					IPSContentCache::update( $member['member_id'], 'sig', $member['signature'] );
				}

				self::$_parsedSignatures[ $member['member_id'] ] = $member['signature'];
			}
		}

		//-----------------------------------------
		// If current session, reset last_activity
		//-----------------------------------------
		
		if( ! empty( $member['running_time'] ) )
		{
			$member['last_activity'] = $member['running_time'] > $member['last_activity'] ? $member['running_time'] : $member['last_activity'];
		}

		//-----------------------------------------
		// Online?
		//-----------------------------------------

		$time_limit 	   = time() - ipsRegistry::$settings['au_cutoff'] * 60;
		$member['_online'] = 0;

		if( ! ipsRegistry::$settings['disable_anonymous'] AND isset( $member['login_anonymous'] ) )
		{
			list( $be_anon, $loggedin )	= explode( '&', $member['login_anonymous'] );
		}
		else
		{
			/* Still need to get the $loggedin var from login_anonymous */
			list( $be_anon, $loggedin )	= explode( '&', $member['login_anonymous'] );
			
			/* Reset this because anonymous login is disabled */
			$be_anon  = 0;

			/* Bug Fix: #21067 */
		//	$loggedin = ! empty( $member['running_time'] ) ? 1 : 0;
//			$loggedin = $member['last_activity'] > $time_limit ? 1 : 0; 
		}

		$bypass_anon				= 0;

		if ( ipsRegistry::member()->getProperty('g_access_cp') AND !ipsRegistry::$settings['disable_admin_anon'] )
		{
			$bypass_anon	= 1;
		}

		if ( ( $member['last_visit'] > $time_limit OR $member['last_activity'] > $time_limit ) AND ( $be_anon != 1 OR $bypass_anon == 1 ) AND $loggedin == 1 )
		{
			$member['_online'] = 1;
		}

		//-----------------------------------------
		// Last Active
		//-----------------------------------------

		$member['_last_active'] = ipsRegistry::getClass('class_localization')->getDate( $member['last_activity'], 'SHORT' );

		if( $be_anon == 1 )
		{
			// Member last logged in anonymous

			if( !ipsRegistry::member()->getProperty('g_access_cp') OR ipsRegistry::$settings['disable_admin_anon'] )
			{
				$member['_last_active'] = ipsRegistry::getClass('class_localization')->words['private'];
			}
		}

		//-----------------------------------------
		// Rating
		//-----------------------------------------

		$member['_pp_rating_real'] = intval( $member['pp_rating_real'] );

		//-----------------------------------------
		// Long display names
		//-----------------------------------------

		$member['members_display_name_short'] = IPSText::truncate( $member['members_display_name'], 16 );

		//-----------------------------------------
		// Reputation
		//-----------------------------------------

		$member['pp_reputation_points'] = $member['pp_reputation_points'] ? $member['pp_reputation_points'] : 0;
		
		if( $parseFlags['reputation'] AND $member['member_id'] )
		{
			if( ! ipsRegistry::isClassLoaded( 'repCache' ) )
			{
				$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/class_reputation_cache.php', 'classReputationCache' );
				ipsRegistry::setClass( 'repCache', new $classToLoad() );
			}

			$member['author_reputation']    = ipsRegistry::getClass( 'repCache' )->getReputation( $member['pp_reputation_points'] );
		}

		//-----------------------------------------
		// Other stuff not worthy of individual comments
		//-----------------------------------------

		$member['members_profile_views']	= isset($member['members_profile_views']) ? $member['members_profile_views'] : 0;
		$member['_pp_profile_views']		= ipsRegistry::getClass('class_localization')->formatNumber( $member['members_profile_views'] );
		
		/* BG customization */
		if ( $member['pp_customization'] AND $member['gbw_allow_customization'] AND ! $member['bw_disable_customization'] )
		{ 
			$member['customization'] = unserialize( $member['pp_customization'] );
			
			if ( is_array( $member['customization'] ) )
			{
				/* Figure out BG URL */
				if ( $member['customization']['type'] == 'url' AND $member['customization']['bg_url'] AND $member['gbw_allow_url_bgimage'] )
				{
					$member['customization']['_bgUrl'] = $member['customization']['bg_url'];
				}
				else if ( $member['customization']['type'] == 'upload' AND $member['customization']['bg_url'] AND $member['gbw_allow_upload_bgimage'] )
				{
					$member['customization']['_bgUrl'] = ipsRegistry::$settings['upload_url'] . '/' . $member['customization']['bg_url'];
				}
				else if ( $member['customization']['bg_color'] )
				{
					$member['customization']['type'] = 'bgColor';
				}
			}
		}
		
		IPSDebug::setMemoryDebugFlag( "IPSMember::buildDisplayData: ".$member['member_id']. " - Completed", $_NOW );
		
		$buildMembers[ $_key ]	= $member;
		
		return $member;
	}
	
	/**
	 * Build member's bitwise field
	 *
	 * @access	public
	 * @param	mixed		Either an array of member data or a member ID
	 * @return	array
	 */
	static public function buildBitWiseOptions( $member )
	{
		//-----------------------------------------
		// Load the member?
		//-----------------------------------------

		if ( ! is_array( $member ) AND ( $member == intval( $member ) ) )
		{
			$member = self::load( $member, 'core,extendedProfile' );
		}
		
		/* Unpack bitwise fields */
		$_tmp = IPSBWOptions::thaw( $member['members_bitoptions'], 'members', 'global' );

		if ( count( $_tmp ) )
		{
			foreach( $_tmp as $k => $v )
			{
				/* Trigger notice if we have DB field */
				if ( isset( $member[ $k ] ) )
				{
					trigger_error( "Thawing bitwise options for MEMBERS: Bitwise field '$k' has overwritten DB field '$k'", E_USER_WARNING );
				}

				$member[ $k ] = $v;
			}
		}
		
		return $member;
	}
	
	/**
	 * Returns user's avatar
	 *
	 * @access	public
	 * @param	mixed		Either an array of member data or a member ID
	 * @param	bool		Whether to avoid caching
	 * @param	bool		Whether to show avatar even if view_avs is off for member
	 * @return	string		HTML
	 * @since	2.0
	 */
    static public function buildAvatar( $member, $no_cache=0, $overRide=0 )
    {
    	//-----------------------------------------
    	// No avatar?
    	//-----------------------------------------
		
		if ( ! $overRide AND ipsRegistry::member()->getProperty('view_avs') == 0 )
		{
			return "";
		}
		
		if( ! ipsRegistry::$settings['avatars_on'] )
		{
			return "";
		}

		//-----------------------------------------
		// Load the member?
		//-----------------------------------------

		if ( ! is_array( $member ) AND ( $member == intval( $member ) ) )
		{
			$member = self::load( $member, 'core,extendedProfile' );
		}

    	//-----------------------------------------
    	// Defaults...
    	//-----------------------------------------

    	$davatar_dims	= explode( "x", strtolower(ipsRegistry::$settings['avatar_dims']) );
		$default_a_dims	= explode( "x", strtolower(ipsRegistry::$settings['avatar_def']) );
    	$this_dims		= explode( "x", strtolower($member['avatar_size']) );

		if (!isset($this_dims[0])) $this_dims[0] = $davatar_dims[0];
		if (!isset($this_dims[1])) $this_dims[1] = $davatar_dims[1];
		if (!$this_dims[0]) $this_dims[0] = $davatar_dims[0];
		if (!$this_dims[1]) $this_dims[1] = $davatar_dims[1];
		
		/* Gravatar defaults to 80px, so only set size if max dims are lower than 80 to prevent upscaling */
		
		$lowestSize		= $davatar_dims[0] < $davatar_dims[1] ? $davatar_dims[0] : $davatar_dims[1];

		if( $lowestSize >= 80 )
		{
			$lowestSize	= '';
		}

		//-----------------------------------------
		// Legacy: noavatar
		//-----------------------------------------
		
		if( $member['avatar_location'] == 'noavatar' )
		{
			$member['avatar_location'] = '';
		}
		
    	//-----------------------------------------
    	// LEGACY: Determine type
    	//-----------------------------------------

		if ( ! $member['avatar_type'] )
		{
			if ( preg_match( "/^http:\/\//", $member['avatar_location'] ) )
			{
				$member['avatar_type'] = 'url';
			}
			else if ( strstr( $member['avatar_location'], "upload:" ) or ( strstr( $member['avatar_location'], 'av-' ) ) )
			{
				$member['avatar_type']   = 'upload';
				$member['avatar_location'] = str_replace( 'upload:', '', $member['avatar_location'] );
			}
			else
			{
				$member['avatar_type'] = 'local';
			}
	 	}

		//-----------------------------------------
		// No cache?
		//-----------------------------------------

		if ( $no_cache && $member['avatar_type'] != 'gravatar' )
		{
			$member['avatar_location'] .= '?_time=' . time();
		}

		//-----------------------------------------
		// URL avatar?
		//-----------------------------------------

		if ( $member['avatar_type'] == 'url' )
		{
			//-----------------------------------------
			// Hide if in ACP for security (CSRF/XSS)
			//-----------------------------------------
			
			if( IN_ACP )
			{
				return "<img src='" . ipsRegistry::$settings['skin_acp_url'] . "/_newimages/remote_avatar.png' alt='' />
						<br /><a id='MF__avatar_link' href='" . ipsRegistry::$settings['base_url'] . "app=members&amp;module=members&amp;section=members&amp;do=remoteAvatarRedirect&amp;member_id=" . $member['member_id'] . "'>" . ipsRegistry::getClass('class_localization')->words['m_remoteavatar_link'] . "</a>";
			}

			if ( substr( $member['avatar_location'], -4 ) == ".swf" )
			{
				if( ipsRegistry::$settings['disable_flash'] )
				{
					return '';
				}

				return "<object classid=\"clsid:d27cdb6e-ae6d-11cf-96b8-444553540000\" width='{$this_dims[0]}' height='{$this_dims[1]}'>
						<param name='movie' value='{$member['avatar_location']}'><param name='play' value='true'>
						<param name='loop' value='true'><param name='quality' value='high'>
						<param name='wmode' value='transparent'>
						<embed src='{$member['avatar_location']}' width='{$this_dims[0]}' height='{$this_dims[1]}' play='true' loop='true' quality='high' wmode='transparent'></embed>
						</object>";
			}
			else
			{
				return "<img src='{$member['avatar_location']}' width='{$this_dims[0]}' height='{$this_dims[1]}' alt='' />";
			}
		}
		
		/* Gravatar */
		else if( $member['avatar_type'] == 'gravatar' && ipsRegistry::$settings['allow_gravatars'] )
		{
			$av_hash = md5( $member['avatar_location'] );
			$s       = $lowestSize ? "s={$lowestSize}" : '';

			return "<img src='http://www.gravatar.com/avatar/{$av_hash}?{$s}' alt='' />";
		}
		
		/* Facebook or twitter */
		else if( $member['avatar_type'] == 'facebook' OR $member['avatar_type'] == 'twitter' )
		{
			return "<img src='{$member['avatar_location']}' alt='' />";
		}		

		//-----------------------------------------
		// Not a URL? Is it an uploaded avatar?
		//-----------------------------------------

		else if ( (ipsRegistry::$settings['avup_size_max'] > 1) and ( $member['avatar_type'] == 'upload' ) )
		{
			$member['avatar_location'] = str_replace( 'upload:', '', $member['avatar_location'] );

			if ( substr( $member['avatar_location'], -4 ) == ".swf" )
			{
				if( ipsRegistry::$settings['disable_flash'] )
				{
					return '';
				}

				return "<object classid=\"clsid:d27cdb6e-ae6d-11cf-96b8-444553540000\" width='{$this_dims[0]}' height='{$this_dims[1]}'>
						<param name='movie' value='" . ipsRegistry::$settings['upload_url'] . "/{$member['avatar_location']}'><param name='play' value='true'>
						<param name='loop' value='true'><param name='quality' value='high'>
						<param name='wmode' value='transparent'>
					    <embed src='" . ipsRegistry::$settings['upload_url'] . "/{$member['avatar_location']}' width='{$this_dims[0]}' height='{$this_dims[1]}' play='true' loop='true' quality='high' wmode='transparent'></embed>
						</object>";
			}
			else
			{
				$url = ipsRegistry::$settings['upload_url'] . "/{$member['avatar_location']}";

				return "<img src='{$url}' width='{$this_dims[0]}' height='{$this_dims[1]}' alt='' />";
			}
		}

		//-----------------------------------------
		// No, it's not a URL or an upload, must
		// be a normal avatar then
		//-----------------------------------------

		else if ($member['avatar_location'] != "")
		{
			//-----------------------------------------
			// Do we have an avatar still ?
		   	//-----------------------------------------

		   	$url = ipsRegistry::$settings['avatars_url'] . "/{$member['avatar_location']}";

			return "<img src='{$url}' alt='' />";
		}
		else if( ipsRegistry::$settings['allow_gravatars'] )
		{
			/* Try a gravatar, if all else fails */
			$av_hash  = md5( $member['email'] );
			$s        = $lowestSize ? "&amp;s={$lowestSize}" : '';
			$blank_av = urlencode(ipsRegistry::$settings['avatars_url'] . '/blank_avatar.gif' ); 

			return "<img src='http://www.gravatar.com/avatar/{$av_hash}?d={$blank_av}{$s}' alt='' />";
		}
    }

	/**
	 * Checks for a DB row that matches $email
	 *
	 * @access	public
	 * @param	string 		Email address
	 * @return	boolean		Record exists
	 */
	static public function checkByEmail( $email )
	{
		$test = self::load( $email, '' );

		if ( $test['member_id'] )
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Updates member's DB row password
	 *
	 * @access	public
	 * @param	string		Key: either member_id or email
	 * @param	string		MD5-once hash of new password
	 * @return	boolean		Update successful
	 */
	static public function updatePassword( $member_key, $new_md5_pass )
	{
		if ( ! $member_key or ! $new_md5_pass )
		{
			return false;
		}

		/* Load member */
		$member = self::load( $member_key );

		$new_pass = md5( md5( $member['members_pass_salt'] ) . $new_md5_pass );

		self::save( $member_key, array( 'core' => array( 'members_pass_hash' => $new_pass ) ) );

		return true;
	}

	/**
	 * Check supplied password with database
	 *
	 * @access	public
	 * @param	string		Key: either member_id or email
	 * @param	string		MD5 of entered password
	 * @return	boolean		Password is correct
	 */
	static public function authenticateMember( $member_key, $md5_once_password )
	{
		/* Load member */
		$member = self::load( $member_key );

		if ( ! $member['member_id'] )
		{
			return FALSE;
		}

		if ( $member['members_pass_hash'] == self::generateCompiledPasshash( $member['members_pass_salt'], $md5_once_password ) )
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Generates a compiled passhash.
	 * Returns a new MD5 hash of the supplied salt and MD5 hash of the password
	 *
	 * @access	public
	 * @param	string		User's salt (5 random chars)
	 * @param	string		User's MD5 hash of their password
	 * @return	string		MD5 hash of compiled salted password
	 */
	static public function generateCompiledPasshash( $salt, $md5_once_password )
	{
		return md5( md5( $salt ) . $md5_once_password );
	}

	/**
	 * Generates a password salt.
	 * Returns n length string of any char except backslash
	 *
	 * @access	public
	 * @param	integer		Length of desired salt, 5 by default
	 * @return	string		n character random string
	 */
	static public function generatePasswordSalt($len=5)
	{
		$salt = '';

		for ( $i = 0; $i < $len; $i++ )
		{
			$num   = mt_rand(33, 126);

			if ( $num == '92' )
			{
				$num = 93;
			}

			$salt .= chr( $num );
		}

		return $salt;
	}

	/**
	 * Generates a log in key
	 *
	 * @access	public
	 * @param	integer		Length of desired random chars to MD5
	 * @return	string		MD5 hash of random characters
	 */
	static public function generateAutoLoginKey( $len=60 )
	{
		$pass = self::generatePasswordSalt( $len );

		return md5($pass);
	}

	/**
	 * Check to see if a member is in a group or not
	 *
	 * @access	public
	 * @param	mixed		Either INT (member_id) OR Array of member data [MUST at least include member_group_id and mgroup_others]
	 * @param	mixed		Either INT (group ID) or array of group IDs
	 * @param	boolean		TRUE (default, check secondary groups also), FALSE (check primary only)
	 * @return	boolean		TRUE (is in group) - FALSE (not in group)
	 */
	static public function isInGroup( $member, $group, $checkSecondary=true )
	{
		$memberData = ( is_array( $member ) ) ? $member : self::load( $member, 'core' );
		$group      = ( is_array( $group ) )  ? $group  : array( $group );
		$others		= explode( ',', $memberData['mgroup_others'] );
		
		if ( ! $memberData['member_group_id'] OR ! count( $group ) )
		{
			return FALSE;
		}
		
		/* Loop */
		foreach( $group as $gid )
		{
			if ( $gid == $memberData['member_group_id'] )
			{
				return true;
			}
			
			if ( $checkSecondary AND is_array( $others ) AND count( $others ) )
			{
				if ( in_array( $gid, $others ) )
				{
					return true;
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Check to see if a member is banned (or not)
	 *
	 * @access	public
	 * @param	string		Type of ban check (ip/ipAddress, name, email)
	 * @param	string		String to check
	 * @return	boolean		TRUE (banned) - FALSE (not banned)
	 */
	static public function isBanned( $type, $string )
	{
		/* Try and be helpful */
		switch ( strtolower( $type ) )
		{
			case 'ip':
				$type = 'ipAddress';
			break;
			case 'emailaddress':
				$type = 'email';
			break;
			case 'username':
			case 'displayname':
				$type = 'name';
			break;
		}

		if ( $type == 'ipAddress' )
		{
			$banCache = ipsRegistry::cache()->getCache('banfilters');
		}
		else
		{
			if ( ! is_array( self::$_banFiltersCache ) )
			{
				self::$_banFiltersCache = array();

				/* Load Ban Filters */
				ipsRegistry::DB()->build( array( 'select' => '*', 'from' => 'banfilters' ) );
				ipsRegistry::DB()->execute();

				while( $r = ipsRegistry::DB()->fetch() )
				{
					self::$_banFiltersCache[ $r['ban_type'] ][] = $r['ban_content'];
				}
			}

			$banCache = self::$_banFiltersCache[ $type ];
		}

		if ( is_array( $banCache ) and count( $banCache ) )
		{
			foreach( $banCache as $entry )
			{
				$ip = str_replace( '\*', '.*', preg_quote( trim($entry), "/") );

				if ( $ip AND preg_match( "/^$ip$/", $string ) )
				{
					return TRUE;
				}
			}
		}

		return FALSE;
	}

	/**
	 * Check to see if a member is ignorable or not
	 *
	 * @access	public
	 * @param	int			Member's primary group ID
	 * @param	string		Comma delisted list of 'other' member groups
	 * @param	string		Type: 'post' or 'pm'
	 * @return	boolean		True (member is ignorable) or False (member can not be ignored)
	 */
	static public function isIgnorable( $member_group_id, $mgroup_others, $type='post' )
	{
		if( ! isset( ipsRegistry::$settings['_unblockableArray'] ) OR ! is_array( ipsRegistry::$settings['_unblockableArray'] ) )
		{
			ipsRegistry::$settings['_unblockableArray'] = ipsRegistry::$settings['cannot_ignore_groups'] ? explode( ",", ipsRegistry::$settings['cannot_ignore_groups'] ) : array();
		}

		$myGroups    = array( $member_group_id );

 		if ( $mgroup_others )
 		{
	 		$myGroups = array_merge( $myGroups, explode( ",", $mgroup_others ) );
 		}
 		
 		/* Check PMs first */
 		if ( $type == 'pm' )
 		{
 			$unblockable = explode( ",", ipsRegistry::$settings['unblockable_pm_groups'] );
 			
 			/* Override with groups */
			if ( is_array( $unblockable ) AND count( $unblockable ) )
			{
				if ( in_array( $member_group_id, $unblockable ) )
				{
					return FALSE;
				}
			}
 		}
 		
 		foreach( $myGroups as $member_group )
 		{
	 		if ( in_array( $member_group, ipsRegistry::$settings['_unblockableArray'] ) )
	 		{
		 		return FALSE;
	 		}
	 	}

		return TRUE;
	}

	/**
	 * Easy peasy way to grab a function from member/memberFunctions.php
	 * without having to bother setting it up each time.
	 *
	 * @access	public
	 * @return	object		memberFunctions object
	 * @author	MattMecham
	 */
	static public function getFunction()
	{
		if ( ! is_object( self::$_memberFunctions ) )
		{
			$classToLoad = IPSLib::loadLibrary( IPS_ROOT_PATH . 'sources/classes/member/memberFunctions.php', 'memberFunctions' );
			self::$_memberFunctions = new $classToLoad( ipsRegistry::instance() );
		}

		return self::$_memberFunctions;
	}

	/**
	 * Set the cache to ignore
	 * Works for one LOAD only! It's reset again for the next load
	 *
	 * @access	public
	 * @return	void
	 */
	static public function ignoreCache()
	{
		self::$ignoreCache = TRUE;
	}

	/**
	 * Adds a member to the cache
	 *
	 * @access	private
	 * @param	array 		Member Data
	 * @param	array 		Tables queried
	 * @return	void
	 */
	static private function _addToCache( $memberData, $tables )
	{
		if ( ! $memberData['member_id'] OR ! is_array( $tables ) )
		{
			return FALSE;
		}

		$_tables = self::__buildTableHash( $tables );

		self::$memberCache[ $memberData['member_id'] ][ $_tables ] = $memberData;
//print round( memory_get_usage() / 1048576 * 100 ) / 100 . ' mb';
		self::$debugData[] = "ADDED: Member ID: " . $memberData['member_id'] . " with tables " . implode( ",", $tables ). ' key ('.$_tables.')';
	}

	/**
	 * Removes a member from the cache
	 *
	 * @access	private
	 * @param	int 		Member ID
	 * @return	void
	 */
	static private function _removeFromCache( $memberID )
	{
		if ( is_array( self::$memberCache[ $memberID ] ) )
		{
			unset( self::$memberCache[ $memberID ] );

			self::$debugData[] = "REMOVED: Member ID: " . $memberID;
		}
	}

	/**
	 * Removes a member from the cache
	 *
	 * @access	private
	 * @param	int 		Member ID to look for
	 * @param	array 		Tables required
	 * @return	mixed		Array of data if a match is found, or FALSE if not.
	 */
	static private function _fetchFromCache( $memberID, $tables )
	{
		if ( self::$ignoreCache === TRUE )
		{
			return FALSE;
		}

		if ( ! $memberID OR ! is_array( $tables ) )
		{
			return FALSE;
		}

		$_tables = self::__buildTableHash( $tables );

		if ( isset( self::$memberCache[ $memberID ][ $_tables ] ) && is_array( self::$memberCache[ $memberID ][ $_tables ] ) )
		{
			self::$debugData[] = "FETCHED: Member ID: " . $memberID. ' key ('.$_tables.')';

			return self::$memberCache[ $memberID ][ $_tables ];
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Updates a member from the cache
	 *
	 * @access	private
	 * @param	int 		Member ID to update
	 * @param	array 		Array of data to update(eg: array( 'core' => 'member_login_key' => 'xxxxx' ) )
	 * @return	mixed		Array of data if a match is found, or FALSE if not.
	 */
	static private function _updateCache( $memberID, $data )
	{
		if ( ! $memberID OR ! is_array( $data ) )
		{
			return FALSE;
		}

		if ( is_array( self::$memberCache[ $memberID ] ) )
		{
			foreach(  self::$memberCache[ $memberID ] as $tableData => $memberData )
			{
				foreach( $data as $table => $newData )
				{
					foreach( $newData as $k => $v )
					{
						self::$memberCache[ $memberID ][ $tableData ][ $k ] = $v;
					}
				}
			}

			self::$debugData[] = "Updated: Member ID: " . $memberID;

			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Build table key.
	 * Takes an array of tables and returns an MD5 comparison hash
	 *
	 * @access	private
	 * @param	array 		Array of tables
	 * @return	string		MD5 hash
	 */
	static private function __buildTableHash( $tables )
	{
		sort( $tables );
		return md5( implode( ',', $tables ) );
	}

	/**
	 * Sends a query to the IPS Spam Service
	 *
	 * @access	public
	 * @param	string		$email		Email address to check/report
	 * @param	string		[$ip]		IP Address to check report, ipsRegistry::member()->ip_address will be used if the address is not specified
	 * @param	string		[$type]		Either register or markspam, register is default
	 * @return	string
	 */
	static public function querySpamService( $email, $ip='', $type='register', $test=0 )
	{
		/* Get the response */
		$key		= trim( ipsRegistry::$settings['ipb_reg_number'] ? ipsRegistry::$settings['ipb_reg_number'] : ipsRegistry::$settings['spam_service_api_key'] );
		$domain 	= ipsRegistry::$settings['board_url'];
		$ip			= ( $ip AND ip2long( $ip ) ) ? $ip : ipsRegistry::member()->ip_address;
		$response	= false;
		$testConn	= $test ? '&debug_mode=1' : '';
		
		/* Get the file managemnet class */
		require_once( IPS_KERNEL_PATH . 'classFileManagement.php' );
		$query = new classFileManagement();
		$query->use_sockets = 1;
		$query->timeout = ipsRegistry::$settings['spam_service_timeout'];
		
		/* Query the service */
		$response = $query->getFileContents( "http://ips-spam-service.com/new-api/index.php?key={$key}&domain={$domain}&type={$type}&email={$email}&ip={$ip}{$testConn}" );

		if( ! $response )
		{
			return 'timeout';
		}
		
		$response		= explode( "\n", $response );
		$responseCode	= $response[0];
		$responseMsg	= $response[1];
		
		if( $test )
		{
			return $responseMsg;
		}

		/* Log Request */
		if( $type == 'register' )
		{
			ipsRegistry::DB()->insert( 'spam_service_log', array(
																	'log_date'		=> time(),
																	'log_code'		=> $responseCode,
																	'log_msg'		=> $responseMsg,
																	'email_address'	=> $email,
																	'ip_address'	=> $ip
																)
									);
		}

		return intval( $responseCode );
	}
}
