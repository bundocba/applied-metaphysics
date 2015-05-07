<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined( '_JEXEC' ) or die( 'Restricted access' ); 
jimport( 'joomla.application.component.view');

class rsmailViewRsmail extends JViewLegacy
{
	protected $code;
	
	public function display($tpl = null) {
		$this->code = rsmailHelper::getConfig('registration_code');
		
		$this->addToolBar();
		parent::display($tpl);
	}
	
	protected function addToolBar() {
		JToolBarHelper::title(JText::_('RSM_RSMAIL'),'rsmail');
		
		if (JFactory::getUser()->authorise('core.admin', 'com_rsmail'))
			JToolBarHelper::preferences('com_rsmail');
	}
}