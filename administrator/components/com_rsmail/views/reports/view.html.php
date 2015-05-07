<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined( '_JEXEC' ) or die( 'Restricted access' ); 
jimport( 'joomla.application.component.view');

class rsmailViewReports extends JViewLegacy
{
	protected $state;
	protected $sidebar;
	protected $filterbar;
	
	public function display($tpl = null) {
		$layout = $this->getLayout();
		
		if ($layout == 'view') {
			JFactory::getDocument()->addScript('https://www.google.com/jsapi');
			
			$this->urls			= $this->get('UrlDetails');
			$this->details 		= $this->get('ReportDetails');
		} else if ($layout == 'errors') {
			$this->items 		= $this->get('Errors');
			$this->pagination	= $this->get('PaginationErrors');
			$this->id			= JFactory::getApplication()->input->getInt('id',0);
		} else if ($layout == 'opens') {
			$this->items		= $this->get('Opens');
			$this->pagination	= $this->get('PaginationOpens');
			$this->id			= JFactory::getApplication()->input->getInt('id',0);
		} else if ($layout == 'links') {
			$this->pagination	= $this->get('PaginationLinks');
			$this->items		= $this->get('Links');
			$this->id			= JFactory::getApplication()->input->getInt('id',0);
		} else if ($layout == 'bounce') {
			$this->items		= $this->get('Bounces');
			$this->pagination	= $this->get('PaginationBounces');
			$this->id			= JFactory::getApplication()->input->getInt('id',0);
		} else {
			$this->items		= $this->get('Data');
			$this->pagination	= $this->get('Pagination');
			$this->state 		= $this->get('State');
		}
		
		$this->filterbar	= $this->get('Filterbar');
		$this->sidebar		= $this->get('Sidebar');
		
		$this->addToolBar($layout);
		parent::display($tpl);
	}
	
	protected function addToolBar($layout) {
		if ($layout == 'view') {
			JToolBarHelper::title(JText::_('RSM_REPORTS'),'rsmail');
			JToolBarHelper::custom('back','back','back','RSM_BACK',false);
			
			if ($this->details['Delivery'])
				JToolBarHelper::custom('log','cronlogs','cronlogs','RSM_LOG',false);
			
			JToolBarHelper::custom('rsmail','rsmail32','rsmail32',JText::_('RSM_RS_PRODUCT'),false);
		} else if ($layout == 'errors') {
			JToolBarHelper::title(JText::_('RSM_ERRORS_TITLE'),'rsmail');
			JToolBarHelper::custom('back','back','back',JText::_('RSM_BACK'),false);
			JToolBarHelper::deleteList(JText::_('RSM_CLEAR_ERRORS_DATA'), 'sessions.clearerrors' ,JText::_('RSM_CLEAR_DATA'));
			JToolBarHelper::custom('rsmail','rsmail32','rsmail32',JText::_('RSM_RS_PRODUCT'),false);
		} else if ($layout == 'opens') {
			JToolBarHelper::title(JText::_('RSM_HISTORY_OPENS'),'rsmail');
			JToolBarHelper::custom('back','back','back',JText::_('RSM_BACK'),false);
			JToolBarHelper::custom('rsmail','rsmail32','rsmail32',JText::_('RSM_RS_PRODUCT'),false);
		} else if ($layout == 'links') {
			JToolBarHelper::title(JText::_('RSM_HISTORY_LINKS'),'rsmail');
			JToolBarHelper::custom('back','back','back',JText::_('RSM_BACK'),false);
			JToolBarHelper::deleteList(JText::_('RSM_CLEAR_DATA_INFO'), 'sessions.clear' ,JText::_('RSM_CLEAR_DATA'));
			JToolBarHelper::custom('rsmail','rsmail32','rsmail32',JText::_('RSM_RS_PRODUCT'),false);
		} else if ($layout == 'bounce') {
			JToolBarHelper::title(JText::_('RSM_BOUNCE_HISTORY'),'rsmail');
			JToolBarHelper::custom('back','back','back',JText::_('RSM_BACK'),false);
			JToolBarHelper::deleteList(JText::_('RSM_CLEAR_BOUNCE_DATA'), 'sessions.clearbounce' ,JText::_('RSM_CLEAR_DATA'));
			JToolBarHelper::custom('rsmail','rsmail32','rsmail32',JText::_('RSM_RS_PRODUCT'),false);
		} else {
			JToolBarHelper::title(JText::_('RSM_REPORTS'),'rsmail');
			JToolBarHelper::custom('rsmail','rsmail32','rsmail32',JText::_('RSM_RS_PRODUCT'),false);
		}
	}
}