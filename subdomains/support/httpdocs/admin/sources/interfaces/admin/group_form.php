<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Group editing form interface
 * Last Updated: $Date: 2010-01-15 10:18:44 -0500 (Fri, 15 Jan 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @since		June 10 2008
 * @version		$Revision: 5713 $
 */

interface admin_group_form
{
	/**
	 * Returns HTML tab content for the page.
	 *
	 * @access	public
	 * @author	Brandon Farber
	 * @param    array 				Group data
	 * @param	integer				Number of tabs used so far (your ids should be this + 1)
	 * @return   array 				Array of 'tabs' (HTML for the tabs), 'content' (HTML for the content), 'tabsUsed' (number of tabs you have used)
	 */
	public function getDisplayContent();
	
	/**
	 * Process the entries for saving and return
	 *
	 * @access	public
	 * @author	Brandon Farber
	 * @return   array 				Array of keys => values for saving
	 */
	public function getForSave();
}