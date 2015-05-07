<?php
/**
 * @version		$Id: controller.php 9820 2008-01-03 00:52:19Z eddieajau $
 * @package		Joomla
 * @subpackage	Config
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.controller' );

/**
 * Plugins Component Controller
 *
 * @package		Joomla
 * @subpackage	Plugins
 * @since 1.5
 */
class CpanelController extends JCKController
{
	protected $canDo = false;
	
	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct( $default );
		
		$this->canDo = JCKHelper::getActions();
	}

	function check()
	{
		$this->display();
	}
	
	function sync()
	{
		if(!$this->canDo->get('jckman.sync') )
		{
			$this->setRedirect( JRoute::_( 'index.php?option=com_jckman&view=cpanel', false ), JText::_( 'COM_JCKMAN_PLUGIN_PERM_NO_SYNC' ), 'error' );
			$this->setError(JText::_( 'COM_JCKMAN_PLUGIN_PERM_NO_SYNC' ));
			return false;
		}
		$this->display();
	}
	
	function export()
	{
	}
	
}