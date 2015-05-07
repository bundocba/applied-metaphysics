<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die(); 
jimport( 'joomla.application.component.model' );

class rsmailModelAutoresponders extends JModelList
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
				'IdAutoresponder','AutoresponderName',
				'AutoresponderDate'
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
		parent::populateState('IdAutoresponder', 'desc');
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
		$query->select('*');
		
		// Select from table
		$query->from($db->qn('#__rsmail_autoresponders'));
		
		// Filter by search in name or description
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			$search = $db->q('%'.$db->escape($search, true).'%');
			$query->where($db->qn('AutoresponderName').' LIKE '.$search);
		}
		
		// Add the list ordering clause
		$listOrdering = $this->getState('list.ordering', 'IdAutoresponder');
		$listDirn = $db->escape($this->getState('list.direction', 'desc'));
		$query->order($db->qn($listOrdering).' '.$listDirn);
		
		return $query;
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
					->select($db->qn('ListName'))
					->from($db->qn('#__rsmail_lists'))
					->where($db->qn('IdList').' IN ('.$item->IdLists.')');
				$db->setQuery($query);
				$lists = $db->loadColumn();
				$items[$i]->lists = !empty($lists) ? implode(', ',$lists) : '';
			}
		}
		
		return $items;
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
		$options['listDirn']  = $this->getState('list.direction', 'desc');
		$options['listOrder'] = $this->getState('list.ordering', 'IdAutoresponder');
		$options['sortFields'] = array(
			JHtml::_('select.option', 'IdAutoresponder', JText::_('JGRID_HEADING_ID')),
			JHtml::_('select.option', 'AutoresponderName', JText::_('RSM_FOLLOWUP_NAME'))
		);
		$options['limitBox']   = $this->getPagination()->getLimitBox();
		
		$bar = new RSFilterBar($options);
		return $bar;
	}
}