<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die();
jimport( 'joomla.application.component.model' );

class rsmailModelSessions extends JModelList
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
				'IdSession', 's.IdSession'
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
		parent::populateState('s.IdSession', 'desc');
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
		$query->select($db->qn('s.IdSession'));
		$query->select($db->qn('s.IdMessage'));
		$query->select($db->qn('s.Date'));
		$query->select($db->qn('s.Lists'));
		$query->select($db->qn('s.Position'));
		$query->select($db->qn('s.Status'));
		$query->select($db->qn('s.MaxEmails'));
		$query->select($db->qn('s.Delivery'));
		$query->select($db->qn('s.paused'));
		$query->select($db->qn('m.MessageName'));
		$query->select($db->qn('m.MessageSubject'));
		
		// Select from table
		$query->from($db->qn('#__rsmail_sessions','s'));
		
		// Join over the messages table
		$query->join('LEFT',$db->qn('#__rsmail_messages','m').' ON '.$db->qn('m.IdMessage').' = '.$db->qn('s.IdMessage'));
		
		// Filter by search in name or description
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			$search = $db->q('%'.$db->escape($search, true).'%');
			$query->where('('.$db->qn('m.MessageName').' LIKE '.$search.' OR '.$db->qn('m.MessageSubject').' LIKE '.$search.')');
		}
		
		// Add the list ordering clause
		$listOrdering = $this->getState('list.ordering', 's.IdSession');
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
				$name = empty($item->MessageSubject) ? JText::_('RSM_NO_SESSION_NAME') : $item->MessageSubject;
				$items[$i]->name = empty($item->MessageName) ? $name : $item->MessageName;
				
				if($item->Status == 0 ) {
					$status = '<font color="red">'.JText::_('RSM_NONE').'</font>'; 
				} elseif ($item->Status == 1) {
					$status = '<font color="blue">'.JText::_('RSM_INCOMPLETE').'</font>'; 
				} elseif ($item->Status == 2) {
					$status = '<font color="green">'.JText::_('RSM_COMPLETE').'</font>';
				} else $status = '';
				
				$items[$i]->status = $status;
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
		$options['listOrder'] = $this->getState('list.ordering', 's.IdSession');
		$options['sortFields'] = array(
			JHtml::_('select.option', 's.IdSession', JText::_('JGRID_HEADING_ID'))
		);
		$options['limitBox']   = $this->getPagination()->getLimitBox();
		
		$bar = new RSFilterBar($options);
		return $bar;
	}
	
	/**
	 * Method to delete session.
	 */
	public function delete($pks) {
		if (!empty($pks)) {
			$db		= JFactory::getDbo();
			$query	= $db->getQuery(true);
			
			JArrayHelper::toInteger($pks);
			
			foreach ($pks as $id) {
				// Delete subscriber clicks
				$query->clear()->select($db->qn('IdReport'))->from($db->qn('#__rsmail_reports'))->where($db->qn('IdSession').' = '.$id);
				$db->setQuery($query);
				if ($reports = $db->loadColumn()) {
					foreach($reports as $report) {
						$query->clear()->delete()->from($db->qn('#__rsmail_subscribers_clicks'))->where($db->qn('IdReport').' = '.(int) $report);
						$db->setQuery($query);
						$db->execute();
					}
				}

				// Delete filters
				$query->clear()->select($db->qn('IdFilter'))->from($db->qn('#__rsmail_sessions'))->where($db->qn('IdSession').' = '.$id);
				$db->setQuery($query);
				if ($filter = (int) $db->loadResult()) {
					$query->clear()->delete()->from($db->qn('#__rsmail_session_filters'))->where($db->qn('IdFilter').' = '.$filter);
					$db->setQuery($query);
					$db->execute();
				}
				
				$query->clear()->delete()->from($db->qn('#__rsmail_subscribers_opens'))->where($db->qn('IdSession').' = '.$id);
				$db->setQuery($query);
				$db->execute();
				
				$query->clear()->delete()->from($db->qn('#__rsmail_reports'))->where($db->qn('IdSession').' = '.$id);
				$db->setQuery($query);
				$db->execute();
				
				$query->clear()->delete()->from($db->qn('#__rsmail_session_details'))->where($db->qn('IdSession').' = '.$id);
				$db->setQuery($query);
				$db->execute();
				
				$query->clear()->delete()->from($db->qn('#__rsmail_sessions'))->where($db->qn('IdSession').' = '.$id);
				$db->setQuery($query);
				$db->execute();
				
				$query->clear()->delete()->from($db->qn('#__rsmail_bounce_emails'))->where($db->qn('IdSession').' = '.$id);
				$db->setQuery($query);
				$db->execute();
				
				$query->clear()->delete()->from($db->qn('#__rsmail_errors'))->where($db->qn('IdSession').' = '.$id);
				$db->setQuery($query);
				$db->execute();
				
				$query->clear()->delete()->from($db->qn('#__rsmail_cron_logs'))->where($db->qn('IdSession').' = '.$id);
				$db->setQuery($query);
				$db->execute();
				
				$query->clear()->delete()->from($db->qn('#__rsmail_cron_logs_emails'))->where($db->qn('IdSession').' = '.$id);
				$db->setQuery($query);
				$db->execute();
				
				$query->clear()->delete()->from($db->qn('#__rsmail_log'))->where($db->qn('IdSession').' = '.$id);
				$db->setQuery($query);
				$db->execute();
			}
			
			return true;
		} else {
			$this->setError(JText::_('RSM_SESSION_SELECT'));
			return false;
		}
	}
	
	/**
	 * Method to pause/resume cron sessions.
	 */
	public function croncontrol($id, $value) {
		if ($id) {
			$db		= JFactory::getDbo();
			$query	= $db->getQuery(true);
			
			$query->clear()
				->update($db->qn('#__rsmail_sessions'))
				->set($db->qn('paused').' = '.(int) $value)
				->where($db->qn('IdSession').' = '.$id);
			$db->setQuery($query);
			$db->execute();
			
			return true;
		}
	}
	
}