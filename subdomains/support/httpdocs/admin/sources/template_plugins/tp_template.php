<?php

/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * Template Pluging: Template Include
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
class tp_template extends output implements interfaceTemplatePlugins
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
	 * @author	Matt Mecham
	 * @param	string	The initial data from the tag
	 * @param	array	Array of options
	 * @return	string	Processed HTML
	 */
	public function runPlugin( $data, $options )
	{
		//-----------------------------------------
		// INIT
		//-----------------------------------------
		
		if( $options['group'] == 'editors' && ipsRegistry::isClassLoaded( 'class_localization' ) )
		{
			ipsRegistry::getClass('class_localization')->loadLanguageFile( array( 'public_editors' ), 'core' );
		}
		
		$return		= '';
		$_group		= str_replace( 'skin_', '', $options['group'] );
		$_group		= str_replace( '{current_app}', "'.\$this->registry->getCurrentApplication().'", $_group );

		$_params	= $options['params'];
		
		$return		= "\$this->registry->getClass('output')->getTemplate('" . $_group . "')->".$data."(".$_params.")";
	
		return '" . ' . $return . ' . "';
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
		
		return array( 'name'    => 'template',
					  'author'  => 'IPS, Inc.',
					  'usage'   => '{parse template="myTemplate" group="global" params="$data, $moreData"}',
					  'options' => array( 'group', 'params' ) );
	}
}