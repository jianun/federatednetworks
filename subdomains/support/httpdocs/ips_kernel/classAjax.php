<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * AJAX input parsing and handling
 * Last Updated: $Date: 2010-05-07 19:47:45 -0400 (Fri, 07 May 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Kernel
 * @link		http://www.invisionpower.com
 * @since		Friday 19th May 2006 17:33
 * @version		$Revision: 404 $
 */


class classAjax
{
	/**
	 * XML output
	 *
	 * @var		string			Output
	 * @access	public
	 */
	public $xml_output;

	/**
	 * XML Header
	 *
	 * @var		string			XML doctype
	 * @access	public
	 */
	public $xml_header;
	
	/**
	 * Character sets
	 *
	 * @var		array			Charsets supported by html_entity_decode
	 * @access	private
	 */
	private $decodeCharsets = array(   'iso-8859-1'	=> 'ISO-8859-1',
									'iso8859-1' 	=> 'ISO-8859-1',
									'iso-8859-15' 	=> 'ISO-8859-15',
									'iso8859-15' 	=> 'ISO-8859-15',
									'utf-8'			=> 'UTF-8',
									'cp866'			=> 'cp866',
									'ibm866'		=> 'cp866',
									'cp1251'		=> 'windows-1251',
									'windows-1251'	=> 'windows-1251',
									'win-1251'		=> 'windows-1251',
									'cp1252'		=> 'windows-1252',
									'windows-1252'	=> 'windows-1252',
									'koi8-r'		=> 'KOI8-R',
									'koi8-ru'		=> 'KOI8-R',
									'koi8r'			=> 'KOI8-R',
									'big5'			=> 'BIG5',
									'gb2312'		=> 'GB2312',
									'big5-hkscs'	=> 'BIG5-HKSCS',
									'shift_jis'		=> 'Shift_JIS',
									'sjis'			=> 'Shift_JIS',
									'euc-jp'		=> 'EUC-JP',
									'eucjp'			=> 'EUC-JP' );
									

	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct()
	{
		if ( ! defined( 'IPS_DOC_CHAR_SET' ) )
		{
			define( 'IPS_DOC_CHAR_SET', 'UTF-8' );
		}
		
		$this->xml_header = "<?xml version=\"1.0\" encoding=\"" . IPS_DOC_CHAR_SET . "\"?".'>';

		/* Convert incoming $_POST fields */
		array_walk_recursive( $_POST, create_function( '&$value, $key', '$value = IPSText::convertCharsets( IPSText::convertUnicode($value), "utf-8", "' . IPS_DOC_CHAR_SET . '" );' ) );
		
		//-----------------------------------------
		// Using this code allows characters that ARE supported
		// within the character set to be recoded properly, instead
		// of as HTML entities when submitted via AJAX.  The problem is,
		// any characters NOT supported in the charset are corrupted. :-\
		//-----------------------------------------
		//array_walk_recursive( $_POST, create_function( '&$value, $key', '$value = IPSText::convertCharsets( IPSText::convertUnicode($value, true), "utf-8", "' . IPS_DOC_CHAR_SET . '" );' ) );

		// We use $_REQUEST in a lot of places, so we might need to do this, but based on the below comments I'll leave this
		// commented out for now...
		//array_walk_recursive( $_REQUEST, create_function( '&$value, $key', '$value = IPSText::convertUnicode($value);' ) );
		
		/* Risky converting $_GET because it calls rawurldecode which could allow crafty users to hide html entities */
		//array_walk_recursive( $_GET , create_function( '&$value, $key', '$value = IPSText::convertUnicode($value);' ) );
		//array_walk_recursive( ipsRegistry::$request, create_function( '&$value, $key', '$value = IPSText::convertUnicode($value);' ) );
	}

 	/**
	 * Convert and make safe an incoming string
	 *
	 * @access	public
	 * @param	string		Raw input string
	 * @param	boolean		Run through parse_incoming routine
	 * @return	string		Cleaned string
	 */
 	public function convertAndMakeSafe( $value, $parse_incoming=true )
 	{
 		$value = rawurldecode( $value );
   		$value = $this->convertUnicode( $value );
		$value = $this->convertHtmlEntities( $value );
		
		if( $parse_incoming )
		{
			$value = IPSText::parseCleanValue( $value );
		}
		
		return $value;
 	}
 	
 	/**
 	 * Cleans output so it is suitable for printing
 	 */
 	public function cleanOutput( $string )
 	{
 		if ( ! IN_ACP )
		{
			$string = preg_replace( "#<!--hook\.([^\>]+?)-->#", '', $string );
		}
		
		$string = ipsRegistry::getClass('output')->replaceMacros( $string );
	
		return $string;
	}
		

	/**
	 * Print an error
	 *
	 * @access	public
	 * @return	void		[Outputs XML document and exits]
	 */
	public function returnGenericError()
	{
		@header( "Content-type: text/xml" );
		$this->printNocacheHeaders();
		
		$this->xml_output = $this->xml_header . "\r\n<errors>\r\n";
		$this->xml_output .= "<error><message>You must be logged in to access this feature</message></error>\r\n";
		$this->xml_output .= "</errors>";

		print $this->xml_output;
		exit();
	}
	
	/**
	 * Return a NULL result
	 *
	 * @access	public
	 * @param	mixed		Value to send
	 * @return	void		[Outputs XML document and exits]
	 */
	public function returnNull( $val=0 )
	{
		@header( "Content-type: text/xml" );
		$this->printNocacheHeaders();
		
		$val = $this->parseAndCleanHooks( $val );
		
		print $this->xml_header . "\r\n<null>{$val}</null>";
		exit();
	}

	/**
	 * Return a string
	 *
	 * @access	public
	 * @param	string		String to output
	 * @return	void		[Outputs plaintext string]
	 */
	public function returnString($string)
	{
		@header( "Content-type: text/plain;charset=" . IPS_DOC_CHAR_SET );
		$this->printNocacheHeaders();
		
		echo $this->parseAndCleanHooks( $string );
		exit();
	}

	/**
	 * Return a JSON error message
	 *
	 * @access	public
	 * @param	string		Error message
	 * @return	void		[Outputs JSON string and exits]
	 */
	public function returnJsonError( $message )
	{
		/* Just alias it... */
		$this->returnJsonArray( array( 'error' => $message ) );
	}

	/**
	 * Return a JSON array
	 *
	 * @param	array		Single dimensional array of key => value fields
	 * @return	void		[Outputs JSON string and exits]
	 */
	public function returnJsonArray( $json=array() )
	{
		@header( "Content-type: application/json;charset=" . IPS_DOC_CHAR_SET );
		$this->printNocacheHeaders();
		
		/* Always return as UTF-8 */
		array_walk_recursive( $json, create_function( '&$value, $key', '$value = IPSText::convertCharsets($value, "' . IPS_DOC_CHAR_SET . '", "UTF-8");' ) );

		$result	= json_encode( $json );
		$result	= IPSText::convertCharsets($result, "UTF-8", IPS_DOC_CHAR_SET);
		print $result;
		
		exit();
	}
	
	/**
	 * Return HTML content
	 *
	 * @access	public
	 * @param	string		HTML to output
	 * @return	void		[Outputs HTML and exits]
	 */
	public function returnHtml( $string )
	{
		//-----------------------------------------
		// Stop one from removing cookie protection
		//-----------------------------------------

		$string = ( $string ) ? $string : '<!--nothing-->';
		$string = str_ireplace( "htmldocument.prototype", "HTMLDocument_prototype", $string );
		
		// Fix IE bugs
		$string = str_ireplace( "&sect", 	"&amp;sect", 	$string );
		
		if ( strtolower( IPS_DOC_CHAR_SET ) == 'iso-8859-1' )
		{
			$string = str_replace( "ì", "&#8220;", $string );
			$string = str_replace( "î", "&#8221;", $string );
		}

		// Other stuff
		$string = ipsRegistry::getClass('output')->replaceMacros( $string );
				
		if ( ! IN_ACP )
		{
			$string = $this->parseAndCleanHooks( $string );
		}
		
		@header( "Content-type: text/html;charset=" . IPS_DOC_CHAR_SET );
		$this->printNocacheHeaders();

		print $string;
		exit();
	}
	
	/**
	 * Run hooks and remove hook comments
	 *
	 * @access	public
	 *
	 * @param	string	$string
	 * @return	string
	 */
	public function parseAndCleanHooks( $string )
	{
		$string = ipsRegistry::getClass('output')->templateHooks( $string );
		$string = preg_replace( "#<!--hook\.([^\>]+?)-->#", '', $string );
		
		return $string;
	}
	
	/**
	 * Print nocache headers
	 *
	 * @access	public
	 * @return	void		[Outputs headers]
	 */
	public function printNocacheHeaders()
	{
		if ( isset( $_SERVER['SERVER_PROTOCOL'] ) AND strstr( $_SERVER['SERVER_PROTOCOL'], '/1.0' ) )
		{
			header( "HTTP/1.0 200 OK" );
		}
		else
		{
			header( "HTTP/1.1 200 OK" );
		}
		
		header( "Cache-Control: no-cache, must-revalidate, max-age=0" );
		header( "Expires: 0" );
		header( "Pragma: no-cache" );
	}
	
	/**
	 * Convert AJAX unicode to standard char codes
	 *
	 * @access	public
	 * @param	string		Input to convert
	 * @return	string		UTF-8 Converted content
	 */
	public function convertUnicode( $t )
	{
		/* This is called at various stages through different ajax files but its no longer required */
		/* The ajax class cleans up (converts via IPSText::convertUnicode()) the post get and input vars in the constructor now so we don't need to manually use it */
		
		return $t;
	}
	
	/**
	 * Convert HTML entities into raw characters
	 *
	 * @access	public
	 * @param	string		Input to convert
	 * @return	string		HTML entity decoded content
	 */
	public function convertHtmlEntities($t)
	{
		//-----------------------------------------
		// Try and fix up HTML entities with missing ;
		//-----------------------------------------
		
		$t = preg_replace( "/&#(\d+?)([^\d;])/i", "&#\\1;\\2", $t );

		//-----------------------------------------
		// Continue...
		//-----------------------------------------

		if ( strtolower(IPS_DOC_CHAR_SET) != 'iso-8859-1' && strtolower(IPS_DOC_CHAR_SET) != 'utf-8' )
   		{
	   		if ( isset($this->decodeCharsets[ strtolower(IPS_DOC_CHAR_SET) ]) )
	   		{
		   		$IPS_DOC_CHAR_SET = $this->decodeCharsets[strtolower(IPS_DOC_CHAR_SET)];

		   		$t = html_entity_decode( $t, ENT_NOQUOTES, IPS_DOC_CHAR_SET );
	   		}
	   		else
	   		{
		   		// Take a crack at entities in other character sets
		   		
		   		$t = str_replace( "&amp;#", "&#", $t );
		   		
		   		// If mb functions available, we can knock out html entities for a few more char sets

				if( function_exists('mb_list_encodings') )
				{
					$valid_encodings = array();
					$valid_encodings = mb_list_encodings();
					
					if( count($valid_encodings) )
					{
						if( in_array( strtoupper(IPS_DOC_CHAR_SET), $valid_encodings ) )
						{
							$t = mb_convert_encoding( $t, strtoupper(IPS_DOC_CHAR_SET), 'HTML-ENTITIES' );
						}
					}
				}
	   		}
   		}
   		
   		return $t;
	}
}
