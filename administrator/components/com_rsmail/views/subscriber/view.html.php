<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined( '_JEXEC' ) or die( 'Restricted access' ); 
jimport( 'joomla.application.component.view');

class rsmailViewSubscriber extends JViewLegacy
{
	protected $form;
	protected $item;
	
	public function display($tpl = null) {
		$app = JFactory::getApplication();
		
		$this->form 	= $this->get('Form');
		$this->item 	= $this->get('Item');
		$this->lists	= $this->get('Lists');
		
		if (empty($this->lists)) 
			JFactory::getApplication()->redirect('index.php?option=com_rsmail&view=subscribers',JText::_('RSM_CREATE_LIST_FIRST')); 
		
		$this->addToolbar();
		parent::display($tpl);
	}
	
	protected function addToolbar() {
		$this->item->IdSubscriber ? JToolBarHelper::title(JText::_('RSM_EDIT_DETAILS'),'rsmail') : JToolBarHelper::title(JText::_('RSM_ADD_DETAILS'),'rsmail');
		JToolBarHelper::apply('subscriber.apply');
		JToolBarHelper::save('subscriber.save');
		JToolBarHelper::save2new('subscriber.save2new');
		JToolBarHelper::cancel('subscriber.cancel');
	}
}