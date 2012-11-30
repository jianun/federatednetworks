<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Template Pluging: JS Module
 * Last Updated: $Date: 2010-01-15 10:18:44 -0500 (Fri, 15 Jan 2010) $
 * </pre>
 *
 * @author 		$Author: bfarber $
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @link		http://www.invisionpower.com
 * @version		$Rev: 5713 $
 */
/**
* Main loader class
*/
class tp_js_module extends output implements interfaceTemplatePlugins
{
	/**
	 * Prevent our main destructor being called by this class
	 *
	 * @access	public
	 * @return	void
	 */
	public function __destruct()
	{
	}
	
	/**
	 * Run the plug-in
	 *
	 * @access	public
	 * @author	Rikki Tissier
	 * @param	string	The initial data from the tag
	 * @param	array	Array of options
	 * @return	string	Processed HTML
	 */
	public function runPlugin( $data, $options )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		$return    = '';

		if( $data )
		{
			$priority = isset( $options['important'] ) ? $options['important'] : 0;
			
			return '" . ' . "\$this->registry->getClass('output')->addJSModule(\"" . addslashes( $data ) . '", "' . addslashes( $priority ) . '" )' . ' . "';
			
			//$this->registry->output->addJSModule( $data, $priority );
		}
		
		return '';
	}
	
	/**
	 * Return information about this modifier.
	 *
	 * It MUST contain an array  of available options in 'options'. If there are no allowed options, then use an empty array.
	 * Failure to keep this up to date will most likely break your template tag.
	 *
	 * @access	public
	 * @author	Matt Mecham
	 * @return	array
	 */
	public function getPluginInfo()
	{
		//-----------------------------------------
		// Return the data, it's that simple...
		//-----------------------------------------
		
		return array( 'name'    => 'js_module',
					  'author'  => 'IPS, Inc.',
					  'usage'   => '{parse js_module="name_of_module"}',
					  'options' => array('important') );
	}
}
