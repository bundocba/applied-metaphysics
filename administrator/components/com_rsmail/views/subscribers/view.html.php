<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined( '_JEXEC' ) or die( 'Restricted access' ); 
jimport( 'joomla.application.component.view');

class rsmailViewSubscribers extends JViewLegacy
{
	public function display($tpl = null) {
		$app	= JFactory::getApplication();
		$layout	= $this->getLayout();
		
		if ($layout == 'remove') {
				$lists	= $this->get('lists');
				$filter	= explode(',', $app->input->getString('filtered_lists'));

				$filtered_lists = array();
				if (!empty($lists)) {
					foreach($lists as $key => $list)
						if(in_array($key, $filter))
							$filtered_lists[$key] = $list['RSMListName'];
				}

				$this->filtered_lists	= $filtered_lists;
				$this->lists			= $lists;
		} else if ($layout == 'charts') {
			JFactory::getDocument()->addScript('https://www.google.com/jsapi');
			
			$this->data			= $this->get('ChartData');
			$this->intervals	= $this->get('Intervals');
			$this->format		= $this->get('Format');
			$this->interval		= $app->input->getInt('interval','1');
			$this->lists		= rsmailHelper::lists();
			$this->selected		= $app->input->get('lists',array(),'array');
			$this->from			= $app->input->getString('from','');
			$this->to			= $app->input->getString('to','');
			$this->state		= $app->input->getInt('unsubscribers',0);
		} else {
		
			// Get filters
			$filter_published	= $app->getUserStateFromRequest('com_rsmail.subscribers.filter_published', 	'filter_published');
			$filter_lists		= $app->getUserStateFromRequest('com_rsmail.subscribers.filter_lists', 		'filter_lists');
			$filter_fields		= $app->getUserStateFromRequest('com_rsmail.subscribers.filter_fields', 	'filter_fields');
			$filter_operators 	= $app->getUserStateFromRequest('com_rsmail.subscribers.filter_operators', 	'filter_operators');
			$filter_values		= $app->getUserStateFromRequest('com_rsmail.subscribers.filter_values', 	'filter_values');				
			$filter_condition	= $app->getUserStateFromRequest('com_rsmail.subscribers.filter_condition', 	'filter_condition');				
			$listOrder			= $app->getUserStateFromRequest( "com_rsmail.subscribers.filter_order",		'filter_order',		's.SubscriberEmail', 'cmd');
			$listDirn			= $app->getUserStateFromRequest( "com_rsmail.subscribers.filter_order_Dir",	'filter_order_Dir',	'asc',	'word' );


			$this->listOrder	= $listOrder;
			$this->listDirn		= $listDirn;
			
			// Set filters
			$app->setUserState('com_rsmail.subscribers.filter_published', 	$filter_published);
			$app->setUserState('com_rsmail.subscribers.filter_lists', 		$filter_lists);
			$app->setUserState('com_rsmail.subscribers.filter_fields', 		$filter_fields);
			$app->setUserState('com_rsmail.subscribers.filter_operators', 	$filter_operators);
			$app->setUserState('com_rsmail.subscribers.filter_values', 		$filter_values);
			$app->setUserState('com_rsmail.subscribers.filter_condition', 	$filter_condition);

			$filters					= $this->get('Filters');
			$this->filter_published		= $filters[0];
			$this->filter_lists			= $filters[1];
			$this->filter_fields		= $filters[2];
			$this->filter_operators		= $filters[3];
			$this->filter_values		= $filters[4];
			$this->filter_condition		= $filters[5];
			$this->data					= $this->get('Data');
			$this->fields				= $this->get('Fields');
			$this->SubscriberLists		= rsmailHelper::lists();
			$this->pagination			= $this->get('Pagination');
			$this->sidebar				= $this->get('Sidebar');
			
			$this->addScripts();
		}
		
		$this->addToolBar($layout);
		parent::display($tpl);
	}
	
	protected function addToolBar($layout) {
		if ($layout == 'charts') {
			JToolBarHelper::title(JText::_('RSM_CHARTS') , 'rsmail');
			
			JToolBarHelper::custom('back', 'back', 'back', JText::_('RSM_BACK') , false);
			JToolBarHelper::custom('rsmail','rsmail32','rsmail32',JText::_('RSM_RS_PRODUCT'),false);
		} else {		
			JToolBarHelper::title(JText::_('RSM_SUBSCRIBERS') , 'rsmail');

			JToolBarHelper::addNew('subscriber.edit');
			JToolBarHelper::custom('subscribers.copy','copy','',JText::_('RSM_COPY_MOVE_SUBSCRIBERS'),false);
			JToolBarHelper::spacer();
			JToolBarHelper::custom('subscribers.unsubscribe','unpublish-32','unpublish-32',JText::_('RSM_UNSUBSCRIBE'),false);
			JToolBarHelper::custom('subscribers.remove','delete','delete-32',JText::_('RSM_DELETE'),false);
			JToolBarHelper::custom('subscribers.send', 'send', 'send', JText::_('RSM_SEND') , false);
			JToolBarHelper::custom('import', 'import-btn', 'import-btn', JText::_('RSM_IMPORT'), false);
			JToolBarHelper::custom('export', 'export-btn', 'export-btn', JText::_('RSM_EXPORT') , false);
			JToolBarHelper::custom('charts', 'chart', 'chart', JText::_('RSM_CHARTS') , false);
			JToolBarHelper::custom('rsmail','rsmail32','rsmail32',JText::_('RSM_RS_PRODUCT'),false);
		}
	}
	
	protected function addScripts() {
		$doc = JFactory::getDocument();
		
		$doc->addStyleSheet(JURI::root(true).'/administrator/components/com_rsmail/assets/css/jquery.superfish.css');
		$doc->addStyleSheet(JURI::root(true).'/administrator/components/com_rsmail/assets/css/jquery.ui.css');	
		if (!rsmailHelper::isJ3()) $doc->addScript(JURI::root(true).'/administrator/components/com_rsmail/assets/js/jquery.js');
		$doc->addScript(JURI::root(true).'/administrator/components/com_rsmail/assets/js/jquery-ui-min.js');
		$doc->addScript(JURI::root(true).'/administrator/components/com_rsmail/assets/js/jquery.superfish.js');
		$doc->addScript(JURI::root(true).'/administrator/components/com_rsmail/assets/js/jquery.scripts.js');
		
		$doc->addScriptDeclaration("function rsm_get_lang(id) {
			switch (id) {
				default: return id;
				case 'RSM_CLEAR_ALL_FILTERS': 			return '".JText::_('RSM_CLEAR_ALL_FILTERS', true)."'; break;
				case 'RSB_CLOSE': 						return '".JText::_('RSB_CLOSE', true)."'; break;
				case 'RSM_SELECT_ALL_RESULTS': 			return '".JText::_('RSM_SELECT_ALL_RESULTS', true)."'; break;
				case 'RSM_DESELECT_ALL_RESULTS': 		return '".JText::_('RSM_DESELECT_ALL_RESULTS', true)."'; break;
			}
		}");
	}
}