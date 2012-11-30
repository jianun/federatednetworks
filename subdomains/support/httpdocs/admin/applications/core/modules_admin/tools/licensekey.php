<?php
/**
 * <pre>
 * Invision Power Services
 * IP.Board v3.1.2
 * License Manager
 * Last Updated: $LastChangedDate$
 * </pre>
 *
 * @author 		$Author$
 * @copyright	(c) 2001 - 2009 Invision Power Services, Inc.
 * @license		http://www.invisionpower.com/community/board/license.html
 * @package		IP.Board
 * @subpackage	Core
 * @link		http://www.invisionpower.com
 * @version		$Rev$
 */

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Incorrect access</h1>You cannot access this file directly. If you have recently upgraded, make sure you upgraded 'admin.php'.";
	exit();
}


class admin_core_tools_licensekey extends ipsCommand
{
	/**
	 * HTML object
	 *
	 * @access	private
	 * @var		object
	 **/
	private $html;
	
	/**#@+
	 * URL bits
	 *
	 * @access	public
	 * @var		string
	 */
	public $form_code		= '';
	public $form_code_js	= '';
	/**#@-*/	
	
	/**
	 * Main entry point
	 *
	 * @access	public
	 * @param	object		ipsRegistry reference
	 * @return	void
	 */
	public function doExecute( ipsRegistry $registry )
	{
		/* Load lang and skin */
		$this->registry->class_localization->loadLanguageFile( array( 'admin_tools' ) );
		$this->html = $this->registry->output->loadTemplate( 'cp_skin_tools' );
				
		/* URLs */
		$this->form_code    = $this->html->form_code    = 'module=tools&amp;section=licensekey';
		$this->form_code_js = $this->html->form_code_js = 'module=tools&section=licensekey';

		/* What to do */
		switch( $this->request['do'] )
		{
			case 'remove':
				$this->remove();
			break;
			
			case 'activate':
				$this->activate();
			break;
			
			case 'overview':
			default:
				$this->overview();
			break;
		}
		
		/* Output */
		$this->registry->output->html_main .= $this->registry->output->global_template->global_frame_wrapper();
		$this->registry->output->sendOutput();
	}
	/**
	 * Removes a license key
	 *
	 * @access	public
	 * @return	void
	 */
	public function remove()
	{
		/* Remove the key */
		$this->DB->update( 'core_sys_conf_settings', array( 'conf_value' => '' ), "conf_key='ipb_reg_number'" );
		
		/* Rebuild the cache */
		$this->cache->rebuildCache( 'settings', 'global' );
		$this->cache->setCache( 'licenseData', array(), array( 'array' => 1 ) );
		
		/* Done */
		$this->registry->output->silentRedirect( $this->settings['base_url'] . $this->form_code );
	}
	
	/**
	 * Activates a license
	 *
	 * @access	public
	 * @return	void
	 */
	public function activate()
	{
		/* Get the file managemnet class */
		require_once( IPS_KERNEL_PATH . 'classFileManagement.php' );
		$query = new classFileManagement();
		$query->use_sockets = 1;

		/* Query the api */
		$response = $query->getFileContents( "http://licsrv.invisionpower.com/license_check/index.php?api=activateKey&key={$this->request['license_key']}&domain={$this->request['domain_key']}" );
	
		/* Decode */
		$response = json_decode( $response, true );

		if( $response['result'] == 'ok' )
		{
			$this->DB->update( 'core_sys_conf_settings', array( 'conf_value' => $this->request['license_key'] ), "conf_key='ipb_reg_number'" );
			$this->cache->rebuildCache( 'settings', 'global' );
			$this->cache->setCache( 'licenseData', array(), array( 'array' => 1 ) );
		}
		else
		{
			if( ! $response['error'] )
			{
				$response['error'] = $this->lang->words['license_key_server_error'];
			}
			
			$this->registry->output->global_message = $response['error'];
		}

		$this->registry->output->silentRedirectWithMessage( $this->settings['base_url'] . $this->form_code );
	}
	
	/**
	 * Displays license information
	 *
	 * @access	public
	 * @return	void
	 */
	public function overview()
	{
		/* Show activation form if we have no key */
		if( ! $this->settings['ipb_reg_number'] )
		{
			$this->activateForm();
			return;
		}
		
		/* Get License Data from cache */
		$licenseData = $this->cache->getCache( 'licenseData' );
		
		if( ! $licenseData || $this->request['refresh'] == 1 )
		{
			/* Get the file managemnet class */
			require_once( IPS_KERNEL_PATH . 'classFileManagement.php' );
			$query = new classFileManagement();
			$query->use_sockets = 1;

			/* Query the api */
			$response = $query->getFileContents( "http://licsrv.invisionpower.com/license_check/index.php?key={$this->settings['ipb_reg_number']}" );
		
			/* Get License Data */
			$licenseData = json_decode( $response, true );
			
			/* Save to cache */
			$licenseData['_cached_date'] = time();
			$this->cache->setCache( 'licenseData', $licenseData, array( 'array' => 1 ) );
		}
		
		/* Date */
		$licenseData['_cached_date'] = $this->lang->formatTime( $licenseData['_cached_date'] );

		/* Output */
		$this->registry->output->html .= $this->html->licenseKeyStatusScreen( $this->settings['ipb_reg_number'], $licenseData );
	}
	
	/**
	 * Displays license key status
	 *
	 * @access	public
	 * @return	void
	 */
	public function activateForm()
	{		
		/* Key Input */
		$keyInput = $this->registry->output->formInput( 'license_key', $this->request['license_key'] );
		
		/* Domain Input */
		$domainInput = $this->registry->output->formInput( 'domain_key', $this->settings['board_url'] );
		
		/* Output */
		$this->registry->output->html .= $this->html->activateForm( $keyInput, $domainInput );
	}
}