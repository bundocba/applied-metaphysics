<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined( '_JEXEC' ) or die( 'Restricted access' ); 
jimport( 'joomla.application.component.view');

class rsmailViewSend extends JViewLegacy
{
	public function display($tpl = null) {
		$layout = $this->getLayout();
		$app	= JFactory::getApplication();
		
		if ($layout == 'send') {
			$this->name			= $this->get('MessageName');
			$this->session		= $this->get('Session');
			
			if ($this->session->Status == 2)
				$app->redirect('index.php?option=com_rsmail&view=reports&layout=view&id='.$this->session->IdSession);
			
			$this->max 			= $this->get('Max');
			$this->listnames	= $this->get('SessionLists');
			
		} else {
			$id = $app->input->getInt('id',0);
			
			if (!$id) 
				$app->redirect('index.php?option=com_rsmail&view=messages',JText::_('RSM_SELECT_MESSAGE'));

			$this->form				= $this->get('Form');
			$this->config			= rsmailHelper::getConfig();
			$this->placeholders 	= $this->get('Placeholders');
			$this->lists			= $this->get('Lists');
			$this->fields	 		= $this->get('Fields');
			$this->fieldlists		= $this->get('ListFields');
			$this->filtered_subs	= JFactory::getSession()->get('session_filters');
			$this->id				= $id;
		}
		
		$this->addToolBar($layout);
		parent::display($tpl);
	}
	
	protected function addToolBar($layout) {
		if ($layout == 'send') {
			JToolBarHelper::title(JText::_('RSM_SENDING'),'rsmail');
			JToolBarHelper::cancel('cancel');
			JToolBarHelper::custom('rsmail','rsmail32','rsmail32',JText::_('RSM_RS_PRODUCT'),false);
		} else {
			JToolBarHelper::title(JText::_('RSM_SEND'),'rsmail');
			JToolBarHelper::custom('send.save','send','send',JText::_('RSM_SEND'));
			JToolBarHelper::cancel('cancel');
			JToolBarHelper::custom('rsmail','rsmail32','rsmail32',JText::_('RSM_RS_PRODUCT'),false);
		}
	}
}