<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Parse Incoming Emails
 * Last Updated: $Date: 2010-07-14 09:39:44 -0400 (Wed, 14 Jul 2010) $
 * </pre>
 *
 * @author 		$Author: mark $
 * @copyright	(c) 2010 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Kernel
 * @link		http://www.invisionpower.com
 * @since		25th June 2010
 * @version		$Revision: 439 $
 */
 
class classIncomingEmail
{
	/**
	 * Recipient Address
	 *
	 * @var	string
	 * @see	__construct
	 */
	public $to;
	
	/**
	 * Sender Address
	 *
	 * @var	string
	 * @see	__construct
	 */
	public $from;
	
	/**
	 * Subject
	 *
	 * @var	string
	 * @see	__construct
	 */
	public $subject;
	
	/**
	 * Message Body
	 *
	 * @var	string
	 * @see	__construct
	 */
	public $message;
	
	/**
	 * Attachments
	 * This is an associative array of attachment data.
	 * The keys are IDs which are associated with <!--ATTACHMENT:{key}--> taks in $this->body.
	 * The values are an array of data to insert into the attachments database table after attach_member_id and attach_rel_module have been set.
	 *
	 * @var	array
	 * @see	__construct
	 */
	public $attachments;

	/**
	 * Constructor
	 * Sets $this->to, $this->from, $this->subject, $this->message and $this->attachments
	 *
	 * @param	string	Raw message with headers
	 */
	public function __construct( $email )
	{
		//--------------------------------------
		// Get some stuff
		//--------------------------------------
		
		if ( class_exists( 'ipsRegistry' ) )
		{
			$this->DB = ipsRegistry::DB();
			
			// Fetch upload path
			$this->upload_dir = ipsRegistry::$settings['upload_dir'];
			$this->upload_url = ipsRegistry::$settings['upload_url'];
		}
		else
		{
			$this->DB = $this->initDB();
			
			// Fetch upload path
			$this->DB->build( array( 'select' => 'conf_key,conf_value', 'from' => 'core_sys_conf_settings', 'where' => "conf_key='upload_dir' OR conf_key='upload_url'" ) );
			$this->DB->execute();
			while ( $r = $this->DB->fetch() )
			{
				if ( $r['conf_key'] == 'upload_dir' )
				{
					$this->upload_dir = $r['conf_value'];
				}
				elseif ( $r['conf_key'] == 'upload_url' )
				{
					$this->upload_url = $r['conf_value'];
				}
			}

		}
				
		// And allowed attachment types
		$this->types = array();
		$this->DB->build( array( 'select' => '*', 'from' => 'attachments_type' ) );
		$this->DB->execute();
		while ( $r = $this->DB->fetch() )
		{
			$this->types[ $r['atype_mimetype'] ][] = $r;
		}	

		//--------------------------------------
		// Pass to PEAR
		//--------------------------------------
		
		// It raises strict warnings
		@error_reporting( E_NONE );
 		@ini_set( 'display_errors', 'off' );
 		
		require_once ( IPS_KERNEL_PATH . 'PEAR/Mail/mimeDecode.php' );
		$decoder = new Mail_mimeDecode( $email );
		$mail = $decoder->decode( array(
			'include_bodies'	=> TRUE,
			'decode_bodies'		=> TRUE,
			'decode_headers'	=> TRUE,
			) );
				
		//--------------------------------------
		// Parse Headers
		//--------------------------------------
			
		/* To */
		if ( preg_match( "/.+? <(.+?)>/", $mail->headers['to'], $matches ) )
		{
			$this->to = $matches[1];
		}
		else
		{
			$this->to = $mail->headers['to'];
		}
		
		/* From */
		if ( preg_match( "/.+? <(.+?)>/", $mail->headers['from'], $matches ) )
		{
			$this->from = $matches[1];
		}
		else
		{
			$this->from = $mail->headers['from'];
		}
		
		/* Subject */
		$this->subject = $mail->headers['subject'];
		
		//--------------------------------------
		// Parse Body
		//--------------------------------------
		
		$this->message = '';
		$this->attachments = array();
		$akey = 0;
			
		/* Normal message */
		if ( $mail->ctype_primary == 'text' )
		{
			if ( $mail->ctype_secondary == 'html' )
			{
				$this->message = str_replace( array( "\n", "\r", "\r\n" ), '<br />', htmlentities( strip_tags( $mail->body ) ) );
			}
			else
			{
				$this->message = str_replace( array( "\n", "\r", "\r\n" ), '<br />', htmlentities( $mail->body ) );;
			}
		}	
		/* Normal message, sent in both HTML and normal */
		elseif ( $mail->ctype_primary == 'multipart' AND $mail->ctype_secondary == 'alternative' )
		{
			$this->message = '';
			foreach ( $mail->parts as $part )
			{
				if ( $part->ctype_secondary == 'html' )
				{
					$this->message = str_replace( array( "\n", "\r", "\r\n" ), '<br />', htmlentities( strip_tags( $part->body ) ) );;
				}
				else
				{
					$this->message = str_replace( array( "\n", "\r", "\r\n" ), '<br />', htmlentities( $part->body ) );;
					if ( $part->cpart_secondary == 'plain' )
					{
						break;
					}
				}
			}
		}
		/* Multipart message (with attachments and stuff) */
		else
		{
			foreach ( $mail->parts as $part )
			{
				if ( $part->ctype_primary == 'multipart' AND $part->ctype_secondary == 'alternative' )
				{
					$add = '';
					foreach ( $part->parts as $subpart )
					{
						$add = str_replace( array( "\n", "\r", "\r\n" ), '<br />', $subpart->body );
						if ( $subpart->cpart_secondary == 'plain' )
						{
							break;
						}
					}
					$this->message .= $add;
				}
				elseif ( $part->ctype_primary == 'text' )
				{
					if ( $part->ctype_secondary == 'html' )
					{
						$this->message .= str_replace( array( "\n", "\r", "\r\n" ), '<br />', htmlentities( strip_tags( $part->body ) ) );;
					}
					else
					{
						$this->message .= str_replace( array( "\n", "\r", "\r\n" ), '<br />', htmlentities( $part->body ) );;
					}
				}
				else
				{
					$mime = "{$part->ctype_primary}/{$part->ctype_secondary}";
					foreach ( $this->types[ $mime ] as $data )
					{
						$name_parts = explode( '.', $part->ctype_parameters['name'] );
						$ext = array_pop( $name_parts );
						if ( $data['atype_extension'] == $ext and $data['atype_post'] )
						{
							/* Create the file */
							$masked_name = md5( uniqid( 'email' ) ) . "-{$part->ctype_parameters['name']}";
							while ( file_exists( $this->upload_dir . "/{$masked_name}" ) )
							{
								$masked_name = md5( uniqid( 'email' ) . microtime() ) . "-{$part->ctype_parameters['name']}";
							}
							file_put_contents( $this->upload_dir . "/{$masked_name}", $part->body );
							
							/* Store attachment data */
							$akey++;
							$this->attachments[ $akey ] = array(
								'attach_ext'		=> $ext,
								'attach_file'		=> $part->ctype_parameters['name'],
								'attach_location'	=> $masked_name,
								'attach_is_image'	=> ( $part->ctype_primary == 'image' ) ? 1 : 0,
								'attach_date'		=> time(),
								'attach_filesize'	=> $part->d_parameters['size'],
								'attach_approved'	=> 1,
								);
							$this->message .= "<!--ATTACHMENT:{$akey}-->";
							break;
						}
					}
				}
			}
		}		
	}
	
	/**
	 * Route
	 * Uses incoming Email rules to route Email
	 */
	public function route()
	{
		$unrouted = TRUE;
		$this->DB->build( array( 'select' => '*', 'from' => 'core_incoming_emails' ) );
		$this->DB->execute();
		while ( $row = $this->DB->fetch() )
		{
			switch ( $row['rule_criteria_field'] )
			{
				case 'to':
					$analyse = $this->to;
					break;
					
				case 'from':
					$analyse = $this->from;
					break;
					
				case 'sbjt':
					$analyse = $this->subject;
					break;
					
				case 'body':
					$analyse = $this->message;
					break;
			}
			
			$match = false;
			switch ( $row['rule_criteria_type'] )
			{
				case 'ctns':
					$match = (bool) ( strpos( $analyse, $row['rule_criteria_value'] ) !== FALSE );
					break;
					
				case 'eqls':
					$match = (bool) ( $analyse == $row['rule_criteria_value'] );
					break;
					
				case 'regx':
					$match = (bool) ( preg_match( "/{$row['rule_criteria_value']}/", $analyse ) == 1 );
					break;
			}
			
			if ( $match )
			{
				$unrouted = FALSE;
				if ( $row['rule_app'] != '--' )
				{
					$appdir = IPS_ROOT_PATH . 'applications_addon/ips/' . $row['rule_app'];
					if ( !is_dir( $appdir ) )
					{
						$appdir = IPS_ROOT_PATH . 'applications_addon/other/' . $row['rule_app'];
					}
					if ( !is_dir( $appdir ) )
					{
						$appdir = IPS_ROOT_PATH . 'applications/' . $row['rule_app'];
					}
					
					if ( file_exists( $appdir . '/extensions/incomingEmails.php' ) )
					{
						$class = 'incomingEmails_' . $row['rule_app'];
						require_once $appdir . '/extensions/incomingEmails.php';
						$class = new $class;
						$class->process( $this->DB, $this->to, $this->from, $this->subject, $this->message, $this->attachments );
					}
				}
				break;
			}
		}
		
		if ( $unrouted )
		{
			$apps = array();
			foreach ( glob( IPS_ROOT_PATH . 'applications/*' ) as $f )
			{
				if ( file_exists( $f . '/extensions/incomingEmails.php' ) )
				{
					$bits = explode( '/', $f );
					$_appdir = array_pop( $bits );
					$apps[ $_appdir ] = $f;
				}
			}
			foreach ( glob( IPS_ROOT_PATH . 'applications_addon/ips/*' ) as $f )
			{
				if ( file_exists( $f . '/extensions/incomingEmails.php' ) )
				{
					$bits = explode( '/', $f );
					$_appdir = array_pop( $bits );
					$apps[ $_appdir ] = $f;
				}
			}
			foreach ( glob( IPS_ROOT_PATH . 'applications_addon/other/*' ) as $f )
			{
				if ( file_exists( $f . '/extensions/incomingEmails.php' ) )
				{
					$bits = explode( '/', $f );
					$_appdir = array_pop( $bits );
					$apps[ $_appdir ] = $f;
				}
			}
					
			foreach ( $apps as $_appdir => $appdir )
			{
				require_once $appdir . '/extensions/incomingEmails.php';
				$class = 'incomingEmails_' . $_appdir;
				$i = new $class;
				if ( $i->handleUnrouted( $this->DB, $this->to, $this->from, $this->subject, $this->message, $this->attachments ) )
				{
					break;
				}
			}
		}
	}
	
	/**
	 * Init DB
	 * Since this class may be being called when ipsRegistry
	 * is not set up, we may need to create a DB object
	 * 
	 * @see route()
	 */
	private function initDB()
	{
		require_once( DOC_IPS_ROOT_PATH . 'conf_global.php' );
		
		$this->DB_driver        = strtolower( $INFO['sql_driver'] );
		require_once ( IPS_KERNEL_PATH . 'classDb' . ucwords( $this->DB_driver ) . ".php" );
		$classname = "db_driver_" . $this->DB_driver;
			
		$this->DB = new $classname;
		$this->DB->obj['sql_database']         = $INFO['sql_database'];
		$this->DB->obj['sql_user']		     = $INFO['sql_user'];
		$this->DB->obj['sql_pass']             = $INFO['sql_pass'];
		$this->DB->obj['sql_host']             = $INFO['sql_host'];
		$this->DB->obj['sql_charset']          = $$INFO['sql_charset'];
		$this->DB->obj['sql_tbl_prefix']       = $INFO['sql_tbl_prefix'] ? $INFO['sql_tbl_prefix'] : '';
		
		$this->DB->connect();
	}

}