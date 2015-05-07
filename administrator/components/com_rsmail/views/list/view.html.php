<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined( '_JEXEC' ) or die( 'Restricted access' ); 
jimport( 'joomla.application.component.view');

class rsmailViewList extends JViewLegacy
{
	protected $form;
	protected $item;
	
	public function display($tpl = null) {
		$layout			= $this->getLayout();
		
		if ($layout == 'field') {
			$this->action 	= JFactory::getApplication()->input->getCmd('action','');
			$this->id 		= JFactory::getApplication()->input->getInt('id',0);
			$this->field	= $this->get('Field');
		} else {
			$this->form 	= $this->get('Form');
			$this->item 	= $this->get('Item');
			$this->doc		= JFactory::getDocument();
		
			$this->addToolBar();
		}
		parent::display($tpl);
	}
	
	protected function addToolBar() {
		JToolBarHelper::apply('list.apply');
		
		if ($this->item->IdList) {
			JToolBarHelper::title(JText::_('RSM_EDIT_LIST'),'rsmail');
			JToolBarHelper::save('list.save');
		} else {
			JToolBarHelper::title(JText::_('RSM_ADD_LIST'));
		}
		
		JToolBarHelper::cancel('list.cancel');
		
		if (!rsmailHelper::isJ3()) {
			$this->doc->addScript(JURI::root(true).'/administrator/components/com_rsmail/assets/js/jquery.js');
			$this->doc->addScriptDeclaration('jQuery.noConflict();');
		}
		$this->doc->addScript(JURI::root(true).'/administrator/components/com_rsmail/assets/js/jquery.tablednd.js');
	}
}