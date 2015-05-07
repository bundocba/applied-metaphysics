<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die();
jimport( 'joomla.application.component.model' );

class rsmailModelLists extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param	array	An optional associative array of configuration settings.
	 * @see		JController
	 * @since	1.6
	 */
	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'IdList', 'ListName'
			);
		}
		
		parent::__construct($config);
	}
	
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return	void
	 * @since	1.6
	 */
	protected function populateState($ordering = null, $direction = null) {
		$this->setState('filter.search', $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search'));
		
		// List state information.
		parent::populateState('ListName', 'asc');
	}
	
	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 * @since	1.6
	 */
	protected function getListQuery() {
		$db 	= JFactory::getDBO();
		$query 	= $db->getQuery(true);
		
		// Select fields
		$query->select($db->qn('IdList').', '.$db->qn('ListName'));
		
		// Select from table
		$query->from($db->qn('#__rsmail_lists'));
		
		// Filter by search in name or description
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			$search = $db->q('%'.$db->escape($search, true).'%');
			$query->where($db->qn('ListName').' LIKE '.$search.' ');
		}
		
		// Add the list ordering clause
		$listOrdering = $this->getState('list.ordering', 'ListName');
		$listDirn = $db->escape($this->getState('list.direction', 'asc'));
		$query->order($db->qn($listOrdering).' '.$listDirn);
		
		return $query;
	}
	
	/**
	 * Method to set the side bar.
	 */
	public function getSidebar() {
		if (rsmailHelper::isJ3()) {
			return JHtmlSidebar::render();
		}
		
		return;
	}
	
	/**
	 * Method to set the filter bar.
	 */
	public function getFilterBar() {
		$options = array();
		$options['search'] = array(
			'label' => JText::_('JSEARCH_FILTER'),
			'value' => $this->getState('filter.search')
		);
		$options['listDirn']  = $this->getState('list.direction', 'asc');
		$options['listOrder'] = $this->getState('list.ordering', 'ListName');
		$options['sortFields'] = array(
			JHtml::_('select.option', 'IdList', JText::_('JGRID_HEADING_ID')),
			JHtml::_('select.option', 'ListName', JText::_('RSM_LIST_NAME'))
		);
		$options['limitBox']   = $this->getPagination()->getLimitBox();
		
		$bar = new RSFilterBar($options);
		return $bar;
	}
	
	/**
	 * Method to get a list of lists.
	 * Overridden to add a check for access levels.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 * @since   1.6.1
	 */
	public function getItems() {
		$items	= parent::getItems();
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		
		if (!empty($items)) {
			foreach ($items as $i => $item) {
				$query->clear()
					->select('COUNT('.$db->qn('IdSubscriber').')')
					->from($db->qn('#__rsmail_subscribers'))
					->where($db->qn('IdList').' = '.(int) $item->IdList)
					->where($db->qn('published').' = 1');
				$db->setQuery($query);
				$items[$i]->subscribers = (int) $db->loadResult();
			}
		}
		
		return $items;
	}
}