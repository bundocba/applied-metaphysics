<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined( '_JEXEC' ) or die( 'Restricted access' ); 
jimport( 'joomla.application.component.view');

class rsmailViewSessions extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;
	protected $sidebar;
	protected $filterbar;
	
	public function display($tpl = null) {		
		$this->filterbar	= $this->get('Filterbar');
		$this->state 		= $this->get('State');
		$this->items 		= $this->get('Items');
		$this->pagination 	= $this->get('Pagination');
		$this->sidebar		= $this->get('Sidebar');
		
		$this->addToolBar();
		parent::display($tpl);
	}
	
	protected function addToolBar() {
		JToolBarHelper::title(JText::_('RSM_SESSIONS'),'rsmail');
		JToolBarHelper::deleteList('','sessions.delete');
		JToolBarHelper::custom('rsmail','rsmail32','rsmail32',JText::_('RSM_RS_PRODUCT'),false);
	}
}