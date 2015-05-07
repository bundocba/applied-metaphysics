<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009-2010 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');
jimport( 'joomla.application.component.view');

class rsmailViewUpdates extends JViewLegacy
{
	public function display($tpl=null) 	{
		$jversion = new JVersion();
		$this->jversion = $jversion->getShortVersion();
		$this->code		= rsmailHelper::genKeyCode();
		$this->sidebar	= rsmailHelper::isJ3() ? JHtmlSidebar::render() : '';
		
		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar() {
		JToolBarHelper::title(JText::_('RSMail!'),'rsmail');
	}
}