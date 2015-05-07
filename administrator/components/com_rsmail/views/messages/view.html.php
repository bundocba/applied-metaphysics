<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined( '_JEXEC' ) or die( 'Restricted access' ); 
jimport( 'joomla.application.component.view');

class rsmailViewMessages extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;
	protected $sidebar;
	protected $filterbar;
	
	public function display($tpl = null) {
		$layout = $this->getLayout();
		
		if ($layout == 'template') {
			$this->templates = $this->get('Templates');
		} else {
			$this->filterbar	= $this->get('Filterbar');
			$this->state 		= $this->get('State');
			$this->items 		= $this->get('Items');
			$this->pagination 	= $this->get('Pagination');
			$this->sidebar		= $this->get('Sidebar');
			
			$this->checkSession();
			$this->addToolBar();
		}
		parent::display($tpl);
	}
	
	protected function addToolBar() {
		JToolBarHelper::title(JText::_('RSM_MESSAGES'),'rsmail');
		JToolBarHelper::addNew('message.add');
		JToolBar::getInstance('toolbar')->appendButton( 'Popup', 'new', JText::_('RSM_FROM_TEMPLATE'), 'index.php?option=com_rsmail&view=messages&layout=template&tmpl=component', 750, 500 );
		JToolBarHelper::editList('message.edit');
		JToolBarHelper::deleteList(JText::_('RSM_CONFIRM_DELETE_MESSAGES'), 'messages.delete');
		JToolBarHelper::custom('send','send','send',JText::_('RSM_SEND'));
		JToolBarHelper::custom('rsmail','rsmail32','rsmail32',JText::_('RSM_RS_PRODUCT'),false);
	}
	
	protected function checkSession() {
		$session	= JFactory::getSession();
		$filter		= JFactory::getApplication()->input->getInt('filter',0);
		
		if (!$filter)
			$session->clear('session_filters');
	}
}