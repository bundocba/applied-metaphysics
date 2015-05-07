<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined( '_JEXEC' ) or die( 'Restricted access' ); 
jimport( 'joomla.application.component.view');

class rsmailViewUnsubscribe extends JViewLegacy
{
	public function display($tpl = null) {
		$this->cid			= $this->get('IdSession');
		$this->vid			= $this->get('Hash');
		$this->email		= $this->get('Email');
		$this->is_logged	= JFactory::getUser()->get('id');
		$this->config		= rsmailHelper::getConfig();
		$this->params		= JFactory::getApplication()->getParams();
		$this->lists		= $this->get('EmailLists');

		parent::display($tpl);
	}
}