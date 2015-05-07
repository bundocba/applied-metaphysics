<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined( '_JEXEC' ) or die( 'Restricted access' ); 
jimport('joomla.application.component.view');

class rsmailViewSettings extends JViewLegacy
{
	protected $form;
	protected $fieldsets;
	protected $tabs;
	protected $layouts;
	protected $config;
	
	public function display($tpl = null) {
		$this->form			= $this->get('Form');
		$this->tabs			= $this->get('Tabs');
		$this->layouts		= $this->get('Layouts');
		$this->config		= $this->get('Config');
		$this->fieldsets	= $this->form->getFieldsets();
		$this->sidebar		= rsmailHelper::isJ3() ? JHtmlSidebar::render() : '';
		
		$this->addToolBar();
		parent::display($tpl);
	}
	
	protected function addToolBar() {
		JToolBarHelper::title(JText::_('RSM_CONF_SETTINGS'), 'rsmail');
		
		JToolBarHelper::apply('settings.apply');
		JToolBarHelper::save('settings.save');
		JToolBarHelper::cancel('settings.cancel');
		JToolBarHelper::custom('rsmail','rsmail32','rsmail32',JText::_('RSM_GLOBAL_NAME'),false);
	}
}