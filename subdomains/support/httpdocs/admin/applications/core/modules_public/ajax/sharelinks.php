<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Login handler abstraction : AJAX login
 * Last Updated: $Date: 2010-01-15 15:18:44 +0000 (Fri, 15 Jan 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Core
 * @link		http://www.invisionpower.com
 * @since		Tuesday 1st March 2005 (11:52)
 * @version		$Revision: 5713 $
 *
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class public_core_ajax_sharelinks extends ipsAjaxCommand 
{
	/**
	 * Class entry point
	 *
	 * @access	public
	 * @param	object		Registry reference
	 * @return	void		[Outputs to screen]
	 */
	public function doExecute( ipsRegistry $registry ) 
	{
    	/* load language */
    	$this->registry->getClass('class_localization')->loadLanguageFile( array( 'public_emails' ), 'core' );
    	
    	/* Do it */
    	switch( $this->request['do'] )
    	{
    		case 'twitterForm':
    			return $this->_twitterForm();
    		break;
    		case 'twitterGo':
    			return $this->_twitterGo();
    		break;
    		case 'facebookForm':
    			return $this->_facebookForm();
    		break;
    		case 'facebookGo':
    			return $this->_facebookGo();
    		break;
    	}
	}
	
	/**
	 * Displays a form of facebook stuff. It's really that exciting.
	 *
	 * @access	private
	 * @return	void		[Outputs HTML to browser AJAX call]
	 */
	private function _facebookForm()
	{
		/* Ensure we have a twitter account and that */
		if ( $this->memberData['member_id'] AND $this->memberData['fb_uid'] AND $this->memberData['fb_token'] )
		{
			/* Connect to the Facebook */
			require_once( IPS_ROOT_PATH . 'sources/classes/facebook/connect.php' );
			$connect = new facebook_connect( $this->registry );
			
			try
			{
				$userData = $connect->fetchUserData();
				
				$this->returnHtml( $this->registry->output->getTemplate('global_other')->facebookPop( $userData ) );
			}
			catch( Exception $e )
			{
				$this->returnHtml( '.' );
			}
		}
		else
		{
			/* Bog off @todo probably grab the URL and doc title and forward to normal twitter.com/submit link */
			$this->returnHtml( 'x' );
		}
	}
	
	/**
	 * Go go Facebook go
	 *
	 * @access	private
	 */
	private function _facebookGo()
	{
		/* INIT */
		$comment = trim( urldecode( $_POST['comment'] ) );
		$url     = trim( urldecode( $_POST['url'] ) );
		$title   = trim( urldecode( $_POST['title'] ) );
		
		/* Ensure title is correctly de-html-ized */
		$title = IPSText::UNhtmlspecialchars( $title );
		
		/* Ensure we have a twitter account and that */
		if ( $this->memberData['member_id'] AND $this->memberData['fb_uid'] AND $this->memberData['fb_token'] )
		{
			/* Connect to the Facebook */
			require_once( IPS_ROOT_PATH . 'sources/classes/facebook/connect.php' );
			$connect = new facebook_connect( $this->registry );
			
			try
			{
				$userData = $connect->fetchUserData();				
				
				if ( $userData['first_name'] )
				{
					/* Log it */
					require_once( IPS_ROOT_PATH . 'sources/classes/share/links.php' );
					$share = new share_links( $this->registry, 'facebook' );
					$share->log( $url, $title );
					
					$connect->postLinkToWall( $url, $comment );
					
					$this->returnHtml( $this->registry->output->getTemplate('global_other')->facebookDone( $userData ) );
				}
				else
				{
					$this->returnHtml( 'finchersaysno' );
				}
				
			}
			catch( Exception $e )
			{
				$this->returnHtml( 'finchersaysno' );
			}
		}
		else
		{
			/* Bog off */
			$this->returnString( 'finchersaysno' );
		}
	}

		
	/**
	 * Go go twitter go
	 *
	 * @access	private
	 */
	private function _twitterGo()
	{
		/* INIT */
		$tweet = trim( urldecode( $_POST['tweet'] ) );
		$url   = trim( urldecode( $_POST['url'] ) );
		$title = trim( urldecode( $_POST['title'] ) );
		
		/* Ensure title is correctly de-html-ized */
		$title = IPSText::UNhtmlspecialchars( $title );
		
		/* Ensure we have a twitter account and that */
		if ( $this->memberData['member_id'] AND $this->memberData['twitter_id'] AND $this->memberData['twitter_token'] AND $this->memberData['twitter_secret'] )
		{
			/* Connect to the twitter */
			require_once( IPS_ROOT_PATH . 'sources/classes/twitter/connect.php' );
			$connect = new twitter_connect( $this->registry, $this->memberData['twitter_token'], $this->memberData['twitter_secret'] );
			$user    = $connect->fetchUserData();
			
			if ( $user['id'] )
			{
				$sid = $connect->updateStatusWithUrl( $tweet, $url );
				
				if ( $sid )
				{	
					/* Log it */
					require_once( IPS_ROOT_PATH . 'sources/classes/share/links.php' );
					$share = new share_links( $this->registry, 'twitter' );
					$share->log( $url, $title );
					
					$user['status']['id'] = $sid;
					$this->returnHtml( $this->registry->output->getTemplate('global_other')->twitterDone( $user ) );
				}
				else
				{
					/* Bog off */
					$this->returnString( 'failwhale' );
				}
			}
			else
			{
				/* Bog off */
				$this->returnString( 'failwhale' );
			}
		}
		else
		{
			/* Bog off */
			$this->returnString( 'failwhale' );
		}
	}
	
	/**
	 * Displays a form of twitter stuff. It's really that exciting.
	 *
	 * @access	private
	 * @return	void		[Outputs HTML to browser AJAX call]
	 */
	private function _twitterForm()
	{
		/* Ensure we have a twitter account and that */
		if ( $this->memberData['member_id'] AND $this->memberData['twitter_id'] AND $this->memberData['twitter_token'] AND $this->memberData['twitter_secret'] )
		{
			/* Connect to the twitter */
			require_once( IPS_ROOT_PATH . 'sources/classes/twitter/connect.php' );
			$connect = new twitter_connect( $this->registry, $this->memberData['twitter_token'], $this->memberData['twitter_secret'] );
			$user    = $connect->fetchUserData();
			
			if ( $user['id'] )
			{
				$this->returnHtml( $this->registry->output->getTemplate('global_other')->twitterPop( $user ) );
			}
			else
			{
				/* Bog off @todo probably grab the URL and doc title and forward to normal twitter.com/submit link */
				$this->returnHtml( '.' );
			}
		}
		else
		{
			/* Bog off @todo probably grab the URL and doc title and forward to normal twitter.com/submit link */
			$this->returnHtml( 'x' );
		}
		
	}
}