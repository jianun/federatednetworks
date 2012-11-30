<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Editor Library: Standard Class
 * Last Updated: $Date: 2010-05-25 10:50:21 -0400 (Tue, 25 May 2010) $
 * </pre>
 *
 * @author 		$Author: matt $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @since		9th March 2005 11:03
 * @version		$Revision: 6387 $
 */

if ( ! defined( 'IN_IPB' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded all the relevant files.";
	exit();
}

class class_editor_module extends class_editor
{

	/**
	 * Process the content before showing it in the form
	 *
	 * @access	public
	 * @param	string		Raw text
	 * @return	string		Text ready for editor
	 */
	public function processBeforeForm( $t )
	{
		$t = str_replace( '<', '&lt;', $t );
		$t = str_replace( '>', '&gt;', $t );
		
		return $t;
	}
	
	/**
	 * Process the content before passing off to the bbcode library
	 *
	 * @access	public
	 * @param	string		Form field name OR Raw text
	 * @return	string		Text ready for editor
	 */
	public function processAfterForm( $form_field, $isDefinitelyFullText=false )
	{
		$content	= ( $isDefinitelyFullText ) ? $form_field : ( isset( $_POST[ $form_field ] ) ? $_POST[ $form_field ] : $form_field );
		
		return $this->_cleanPost( trim($content) );
	}
	
	
	
}