<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die();
 
jimport( 'joomla.application.component.model' );

class rsmailModelSubscribers extends JModelLegacy
{
	protected $_query;
	protected $_data;
	protected $_total=null;
	protected $_pagination=null;
	
	public function __construct() {
		parent::__construct();
		
		$app = JFactory::getApplication();
		
		// Set query
		$this->buildQuery();

		// Get pagination request variables
		$limit 		= $app->getUserStateFromRequest('com_rsmail.subscribers.limit', 'limit', $app->getCfg('list_limit'), 'int');
		$limitstart = $app->input->getInt('limitstart', 0);
		$total 		= $this->getTotal();

		// In case limit has been changed, adjust it
		$limitstart = $limitstart <= $total ? (floor($limitstart / $limit) * $limit) : 0;
		
		$this->setState('com_rsmail.subscribers.limit', $limit);
		$this->setState('com_rsmail.subscribers.limitstart', $limitstart);
	}
	
	
	// Build subscribers query
	protected function buildQuery() {
		$app 			= JFactory::getApplication();
		$jinput			= $app->input;
		$db 			= JFactory::getDBO();
		$where 			= '';
		$show_list		= $jinput->getInt('showlist', 0);
		$fields_filter	= false;

		if($show_list){
			$published  = $app->setUserState('com_rsmail.subscribers.filter_published',		array(1));
			$lists 		= $app->setUserState('com_rsmail.subscribers.filter_lists',			array($show_list));
			$fields		= $app->setUserState('com_rsmail.subscribers.filter_fields',		array());
			$operators 	= $app->setUserState('com_rsmail.subscribers.filter_operators',		array('is'));
			$values 	= $app->setUserState('com_rsmail.subscribers.filter_values',		array(''));
		}

		$published  = $app->getUserStateFromRequest('com_rsmail.subscribers.filter_published', 	'filter_published',	array(), 'array');
		$lists 		= $app->getUserStateFromRequest('com_rsmail.subscribers.filter_lists', 	 	'filter_lists', 	array(), 'array');
		$fields		= $app->getUserStateFromRequest('com_rsmail.subscribers.filter_fields',  	'filter_fields', 	array(), 'array');
		$operators 	= $app->getUserStateFromRequest('com_rsmail.subscribers.filter_operators',  'filter_operators', array(), 'array');
		$values 	= $app->getUserStateFromRequest('com_rsmail.subscribers.filter_values', 	'filter_values', 	array(), 'array');
		$condition 	= $app->getUserStateFromRequest('com_rsmail.subscribers.filter_condition', 	'filter_condition', 'AND');
		$sortColumn	= $app->getUserStateFromRequest('com_rsmail.subscribers.filter_order',		'filter_order',		's.SubscriberEmail',	'cmd' );
		$sortOrder	= $app->getUserStateFromRequest('com_rsmail.subscribers.filter_order_Dir',	'filter_order_Dir',	'ASC',	'word' );
		$orderby	= $sortColumn.' '.$sortOrder;

		$app->setUserState('com_rsmail.subscribers.filter_order', 		$sortColumn);
		$app->setUserState('com_rsmail.subscribers.filter_order_Dir', 	$sortOrder);

		// check if query searches the fields
		if (!empty($fields)) {
			foreach($fields as $key => $value) {
				if(!empty($value)) 
					$fields_filter = true;
			}
		}
		
		if(!empty($lists)) {
			foreach($lists as $key => $list) {
				$subscribe_state	= $published[$key] == '' ? 1 : $published[$key];
				$operator 			= $operators[$key];
				$value				= $values[$key];

				if (!isset($fields[$key])) {
					$field = '';
				} elseif ($fields[$key] == 'email')
					$field 	= $db->qn('s.SubscriberEmail');
				elseif(!empty($fields[$key])) {
					$db->setQuery('SELECT '.$db->qn('FieldName').' FROM '.$db->qn('#__rsmail_list_fields').' WHERE '.$db->qn('IdListFields').' = '.$db->q($fields[$key]));
					$field 	= $db->loadResult();
				} else {
					$field = '';
				}

				switch($operator) {
					case 'contains':
						$operator = ' LIKE';
						$value	  = (!empty($value) ? " '%".str_replace("%", "\%", $value)."%' " : " '' ");
					break;
					case 'not_contain':
						$operator = ' NOT LIKE';
						$value	  = (!empty($value) ? " '%".str_replace("%", "\%", $value)."%' " : " '' ");
					break;
					case 'is':
						$operator	= ' = ';
						$value 		= $db->q($value);
					break;
					case 'is_not':
						$operator 	= ' <> ';
						$value 		= $db->q($value);
					break;
				}
				
				switch($condition) {
					case 'OR':
						if(!empty($list)) {
							if(!empty($field)) {
								if($fields[$key] == 'email')
									$where .= ' ('.$db->qn('s.published').' = '.$db->q($subscribe_state).' AND '.$db->qn('s.IdList').' = '.$db->q($list).' AND '.$field.$operator.$value.') '.$condition;
								else {
									$where .= ' ('.$db->qn('s.published').' = '.$db->q($subscribe_state).' AND '.$db->qn('sd.IdList').' = '.$db->q($list);
									if (!empty($field)) 
										$where .= ' AND '.$db->qn('sd.FieldName').' = '.$db->q($field).' AND '.$db->qn('sd.FieldValue').' '.$operator.$value;
									$where .= ') '.$condition;
								}
							} else {
								$where .= '('.$db->qn('s.published').' = '.$db->q($subscribe_state).' AND '.$db->qn('s.IdList').' = '.$db->q($list).') '.$condition;
							}
						} else {
							$where .= ' '.$db->qn('s.published').' = '.$db->q($subscribe_state);
							if (!empty($field))
								$where .= ' AND '.$field.$operator.$value;
							$where  .= ' '.$condition;
						}
					break;
					
					case 'AND':
						if(!empty($list)) {
							if(!empty($field)) {
								if($fields[$key] == 'email') {
									$where .= ' ('.$db->qn('s.published').' = '.$db->q($subscribe_state).' AND '.$db->qn('s.IdList').' = '.$db->q($list).' AND '.$field.$operator.$value.') '.$condition;
								} else {
									$where .= ' (('.$db->qn('s.published').' = '.$db->q($subscribe_state).' AND '.$db->qn('sd.IdList').' = '.$db->q($list).' ';
									
									if (!empty($field)) {
										$where .= ' AND '.$db->qn('sd.FieldName').' = '.$db->q($field).' AND '.$db->qn('sd.FieldValue').' '.$operator.$value.') OR ('.$db->qn('s.SubscriberEmail').' IN (SELECT '.$db->qn('s.SubscriberEmail').' FROM '.$db->qn('#__rsmail_subscribers','s').' LEFT JOIN '.$db->qn('#__rsmail_subscriber_details','sd').' ON '.$db->qn('s.IdSubscriber').' = '.$db->qn('sd.IdSubscriber').' WHERE '.$db->qn('s.published').' = '.$db->q($subscribe_state).' AND '.$db->qn('sd.IdList').' = '.$db->q($list).' AND ';
										
										if ($value != "''") {
											$where .= $db->qn('sd.FieldName').' = '.$db->q($field).' AND '.$db->qn('sd.FieldValue').' '.$operator.$value.' ';
										} else {
											$where .= '(SELECT COUNT('.$db->qn('IdSubscriber').') FROM '.$db->qn('#__rsmail_subscriber_details').' WHERE '.$db->qn('FieldName').' = '.$db->q($field).' AND '.$db->qn('IdList').' = '.$db->q($list).' AND '.$db->qn('IdSubscriber').' = '.$db->qn('sd.IdSubscriber').') = 0';
										}
										
										$where .= ' ))';
									} else {
										$where .= ')';
									}
									$where .= ') '.$condition;
								}
							} else {
								$where .= ' ('.$db->qn('s.SubscriberEmail').' IN (SELECT '.$db->qn('SubscriberEmail').' FROM '.$db->qn('#__rsmail_subscribers').' WHERE '.$db->qn('IdList').' = '.$db->q($list).' AND '.$db->qn('published').' = '.$db->q($subscribe_state).')) '.$condition;
							}
						} else {
							$where .= ' '.$db->qn('s.published').' = '.$db->q($subscribe_state);
							if (!empty($field))
								$where .= ' AND '.$field.$operator.$value;
							$where .= ' '.$condition;
						}
					break;
				}
			}
			
			$this->_query = 'SELECT '.$db->qn('s.SubscriberEmail').', '.$db->qn('s.IdSubscriber').', '.$db->qn('s.DateSubscribed').', '.$db->qn('s.IdList').', '.$db->qn('s.SubscriberIp').', '.$db->qn('s.published').', '.$db->qn('u.username');
			
			if ($fields_filter)
				$this->_query .= ', '.$db->qn('sd.FieldName').', '.$db->qn('sd.FieldValue');
			
			$this->_query .= ' FROM '.$db->qn('#__rsmail_subscribers','s').' ';
			
			if ($fields_filter)
				$this->_query .= 'LEFT JOIN '.$db->qn('#__rsmail_subscriber_details','sd').' ON '.$db->qn('s.IdSubscriber').' = '.$db->qn('sd.IdSubscriber').' ';
			
			$this->_query .= 'LEFT JOIN '.$db->qn('#__users','u').' ON '.$db->qn('u.id').' = '.$db->qn('s.UserId').' WHERE 1 = 1 ';
			
			if (!empty($where) && count($published) > 1)
				$this->_query .= 'AND ('.substr($where, 0, -strlen($condition)).') ';
			else 
				$this->_query .= 'AND '.substr($where, 0, -strlen($condition)).' ';
				
			$this->_query .= 'GROUP BY '.$db->qn('s.SubscriberEmail').' ORDER BY '.$orderby;
			
		} else {
			$this->_query = 'SELECT '.$db->qn('s.SubscriberEmail').', '.$db->qn('s.IdSubscriber').', '.$db->qn('s.DateSubscribed').', '.$db->qn('s.IdList').', '.$db->qn('s.SubscriberIp').', '.$db->qn('s.published').', '.$db->qn('u.username').' FROM '.$db->qn('#__rsmail_subscribers','s').' LEFT JOIN '.$db->qn('#__users','u').' ON '.$db->qn('u.id').' = '.$db->qn('s.UserId').' WHERE '.$db->qn('s.published').' = 1 GROUP BY '.$db->qn('s.SubscriberEmail').' ORDER BY '.$orderby;
		}

		return $this->_query;
	}
	
	// Get subscribers
	public function getData() {
		if (empty($this->_data)) {	
			$db = JFactory::getDbo();
			$db->setQuery($this->_query, $this->getState('com_rsmail.subscribers.limitstart'), $this->getState('com_rsmail.subscribers.limit'));
			$this->_data = $db->loadObjectList();
		}

		return $this->_data;
	}

	public function getTotal() {
		if (empty($this->_total)) {
			$db = JFactory::getDbo();
			$db->setQuery($this->_query);
			$db->execute();
			$this->_total = $db->getNumRows();
		}

		return $this->_total;
	}

	// Get pagination
	public function getPagination() {
		// Load the content if it doesn't already exist
		if (empty($this->_pagination)) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('com_rsmail.subscribers.limitstart'), $this->getState('com_rsmail.subscribers.limit'));
		}
		return $this->_pagination;
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
	
	// Get filters
	public function getFilters() {
		$app 		= JFactory::getApplication();
		$db 		= JFactory::getDbo();
		$query		= $db->getQuery(true);
		$listnames	= array();
		$fieldnames = array();

		$published 	= $app->getUserStateFromRequest('com_rsmail.subscribers.filter_published', 	'filter_published',	array(), 'array');
		$lists 		= $app->getUserStateFromRequest('com_rsmail.subscribers.filter_lists', 		'filter_lists', 	array(), 'array');
		$fields 	= $app->getUserStateFromRequest('com_rsmail.subscribers.filter_fields',  	'filter_fields',	array(), 'array');
		$operators 	= $app->getUserStateFromRequest('com_rsmail.subscribers.filter_operators',  'filter_operators', array(), 'array');
		$values 	= $app->getUserStateFromRequest('com_rsmail.subscribers.filter_values', 	'filter_values', 	array(), 'array');
		$condition 	= $app->getUserStateFromRequest('com_rsmail.subscribers.filter_condition', 	'filter_condition', 'AND');
		
		if ($lists && $lists[0] == ''){
			$published = $lists = $fields = $operators = $values = array();
			$condition = 'AND';
		}
		
		if (!is_array($published))
			$published = array($published);

		if (!is_array($lists))
			$lists = array($lists);
			
		if (!empty($lists)) {
			$lids = trim(implode(',', $lists),',');
			
			$query->clear()
				->select($db->qn('IdList'))->select($db->qn('ListName'))
				->from($db->qn('#__rsmail_lists'))
				->where($db->qn('IdList').' IN ('.$lids.')');
			
			$db->setQuery($query);
			$dblists = $db->loadObjectList();
			
			foreach($lists as $index_list => $list) {
				if ($list == 0)  
					$listnames[$index_list] = array('IdList' => 0, 'ListName' => JText::_('RSM_SELECT_FILTER_LIST'));
				else { // if selected list filter is all lists
					foreach ($dblists as $key => $dblist)
						if($list == $dblist->IdList)
							$listnames[$index_list] = array('IdList' => $dblist->IdList, 'ListName' => $dblist->ListName);
				}
			}
		}
		
		if (!is_array($fields))
			$fields = array($fields);
		
		if (!empty($fields)) {
			$tmp = $fields;
			foreach ($tmp as $i => $tmpfield)
				if ($tmpfield == 'email')
					unset($tmp[$i]);
			
			if (!empty($tmp)) {
				$fids = trim(implode(',', $tmp), ',');
				
				if (!empty($fids)) {				
					$query->clear()
						->select($db->qn('IdListFields'))->select($db->qn('FieldName'))
						->from($db->qn('#__rsmail_list_fields'))
						->where($db->qn('IdListFields').' IN ('.$fids.')');
					
					$db->setQuery($query);
					$dbfields = $db->loadObjectList();
				} else $dbfields = array();
			} else $dbfields = array();
			
			foreach($fields as $index_field => $field){
				if (empty($field)) 
					$fieldnames[$index_field] = array('IdListFields' => 0, 'FieldName' => JText::_('RSM_NO_FILTER'));
				elseif ($field == 'email')  
					$fieldnames[$index_field] = array('IdListFields' => 'email', 'FieldName' => JText::_('RSM_EMAIL'));
				else { // if selected field filter is 'no fields filter'
					if(!empty($dbfields))
						foreach($dbfields as $key => $dbfield)
							if($field == $dbfield->IdListFields)
								$fieldnames[$index_field] = array('IdListFields' => $dbfield->IdListFields, 'FieldName' => $dbfield->FieldName);
				}
			}
		}
		
		if (!is_array($operators))
			$operators = array($operators);
		
		if (!is_array($values))
			$values = array($values);
		
		return array($published, $listnames, $fieldnames, $operators, $values, $condition);
	}
	
	// Get fields
	public function getFields() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$jinput	= JFactory::getApplication()->input;
		$lid 	= $jinput->getInt('lid', 0);
		$IdList = $jinput->getInt('IdList',0);
		$task	= $jinput->getCmd('task','');
		$id 	= 0;
		$filter = $jinput->getInt('list_filter', 0);
		
		if($task == 'getfields') $id = $lid;
		if($task == 'editsubscriber') $id = $IdList;
		if($task == 'subscribers') $id = $lid;

		if (!empty($filter))
			$id = $filter;

		if (!empty($id)) {
			$query->clear()
				->select($db->qn('FieldName'))
				->from($db->qn('#__rsmail_list_fields'))
				->where($db->qn('IdList').' = '.(int) $id)
				->order($db->qn('FieldName').' ASC');
			$db->setQuery($query);
			return $db->loadObjectList();
		}
		else
			return array();
	}
	
	// Export subscribers
	public function export() {
		$db			= JFactory::getDbo();
		$jinput		= JFactory::getApplication()->input;
		$path 		= JPATH_SITE.'/administrator/components/com_rsmail/files/tmp.csv';
		$config		= rsmailHelper::getConfig();
		$query 		= $this->buildQuery();
		$position 	= $jinput->getInt('position',0);
		$cids		= $jinput->get('cid',array(),'array');
		$filtered 	= $jinput->getInt('filtered_results', 0);
		$return		= array();

		// Total Results
		if ($filtered) {
			$db->setQuery($query);
			$max = count($db->loadObjectList());
		} else {
			$max = count($cids);
		}
		
		// set the  Limit
		$dbLimit		= ($config->export_querys_nr != 0 || $config->export_querys_nr != '') ? $config->export_querys_nr : 1000;
		$limit			= ($max < $dbLimit) ? $max : $dbLimit;
		$subscribers	= array();
		
		// if filtered results overwrite checked subscribers
		if($filtered) {
			$db->setQuery($query, $position, $limit);
			$subscribers 		= $db->loadObjectList();
			$return['Position'] = $position + $limit;
			$return['Total']	= $max;
		} else {
			if(!empty($cids)) {
				JArrayHelper::toInteger($cids);
				$db->setQuery('SELECT '.$db->qn('IdSubscriber').', '.$db->qn('SubscriberEmail').', '.$db->qn('IdList').' FROM '.$db->qn('#__rsmail_subscribers').' WHERE '.$db->qn('IdSubscriber').' IN ('.implode(',',$cids).') ORDER BY '.$db->qn('SubscriberEmail').' ASC');
				$subscribers = $db->loadObjectList();
			}
			$return['Position'] = $return['Total'] = $max;
		}

		// create the temporary file if it does not exist
		$text = '';
		if (!file_exists($path))
			$file = JFile::write($path,$text);

		if($position == 0) {
			$fopen = fopen($path,'w+');
			fclose($fopen);
		}

		if(!empty($subscribers)) {
			foreach($subscribers as $sub) {
				// get each subscriber with its details 
				$db->setQuery("SELECT CONCAT('\"',FieldValue,'\"') FROM #__rsmail_subscriber_details WHERE IdSubscriber = '".$sub->IdSubscriber."'");
				$details 	= $db->loadColumn();
				$details 	= implode(',',$details);
				$csv 		= '"'.$db->escape($sub->SubscriberEmail).'"';
				$csv		.= !empty($details) ? ','.$details."\n" : "\n";
				$fp 		= fopen($path, 'a');

				// write subscriber details to the csv file
				fwrite($fp, $csv);
				fclose($fp);
			}
		}
		
		return $return;
	}
	
	// Download export file
	public function getfile() {
		$file = JPATH_SITE.'/administrator/components/com_rsmail/files/tmp.csv';
		if (file_exists($file)) {
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename='.basename($file));
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file));
			ob_clean();
			flush();
			readfile($file);
		}
	}
	
	// Unsubscribe users
	public function unsubscribe() {
		$db 		= JFactory::getDbo();
		$query		= $db->getQuery(true);
		$jinput		= JFactory::getApplication()->input;
		$filtered 	= $jinput->getInt('filtered_results', 0);
		$cids		= $jinput->get('cid', array(), 'array');
		$filters 	= $this->getFilters();
		$sql_lists	= array();

		if (!empty($filters[1])) {
			foreach($filters[1] as $key => $lists) {
				foreach($lists as $key2 => $value) {
					if($key2 == 'IdList' && !empty($value)) {
						$sql_lists[] = $value;
					}
				}
			}
		}
		
		if ($filtered) {
			$squery = $this->buildQuery();
			$db->setQuery($squery);
			if ($subscribers = $db->loadObjectList()) {
				foreach($subscribers as $subscriber) {
					$query->clear()
						->update($db->qn('#__rsmail_subscribers'))
						->set($db->qn('published').' = 0')
						->where($db->qn('SubscriberEmail').' = '.$db->q($subscriber->SubscriberEmail));
					
					if (!empty($sql_lists)) {
						JArrayHelper::toInteger($sql_lists);
						$query->where($db->qn('IdList').' IN ('.implode(',',$sql_lists).')');
					}

					$db->setQuery($query);
					$db->execute();
				}
			}
		} else {
			if (!empty($cids)) {
				foreach($cids as $cid) {
					$query->clear()
						->select($db->qn('SubscriberEmail'))
						->from($db->qn('#__rsmail_subscribers'))
						->where($db->qn('IdSubscriber').' = '.$cid);
					
					$db->setQuery($query);
					$SubscriberEmail = $db->loadResult();
					
					$query->clear()
						->update($db->qn('#__rsmail_subscribers'))
						->set($db->qn('published').' = 0')
						->where($db->qn('SubscriberEmail').' = '.$db->q($SubscriberEmail));
					
					if (!empty($sql_lists)) {
						JArrayHelper::toInteger($sql_lists);
						$query->where($db->qn('IdList').' IN ('.implode(',',$sql_lists).')');
					}

					$db->setQuery($query);
					$db->execute();
				}
			}
		}

		return true;
	}
	
	// Load pagination
	public function ajax() {
		$return = array();
		$class	= rsmailHelper::isJ3() ? 'JViewLegacy' : 'JView';
		if ($class == 'JView') {
			jimport('joomla.application.component.view');
		}
		
		$view = new $class(array(
			'name' => 'subscribers',
			'layout' => 'ajax',
			'base_path' => JPATH_ADMINISTRATOR.'/components/com_rsmail'
		));
		
		$view->data = $this->getData();
		$return['layout'] = $view->loadTemplate();
		$return['pagination'] = $this->getPagination();
		
		return json_encode($return);
	}
	
	// Copy layout
	public function copy() {
		$class	= rsmailHelper::isJ3() ? 'JViewLegacy' : 'JView';
		if ($class == 'JView') {
			jimport('joomla.application.component.view');
		}
		
		$view = new $class(array(
			'name' => 'subscribers',
			'layout' => 'copy',
			'base_path' => JPATH_ADMINISTRATOR.'/components/com_rsmail'
		));
		
		$db					= JFactory::getDBO();
		$query				= $db->getQuery(true);
		$subquery			= $db->getQuery(true);
		$app				= JFactory::getApplication();
		$jinput				= $app->input;
		$cids				= $jinput->get('cid', array(),'array');
		$filtered_results 	= $jinput->getInt('filtered_results', 0);
		$filtered_lists 	= $app->getUserStateFromRequest('com_rsmail.subscribers.filter_lists', 'filter_lists');
		$squery				= $this->buildQuery();

		// if select filtered results is checked overwrite checked cids 
		if($filtered_results) {
			$cids = array();
			
			$db->setQuery($squery);
			if ($subscribers = $db->loadObjectList()) {
				foreach($subscribers as $subscriber)
					$cids[]	 = $subscriber->IdSubscriber;
				JArrayHelper::toInteger($cids);
			}
			
			$count	= count($cids);
			
			// in case no filters are applied
			if(!$filtered_lists || empty($filtered_lists[0])) {
				$subquery->clear()
					->select($db->qn('SubscriberEmail'))
					->from($db->qn('#__rsmail_subscribers'))
					->where($db->qn('IdSubscriber').' IN ('.implode(',',$cids).')');
				
				$query->clear()
					->select($db->qn('IdSubscriber'))
					->from($db->qn('#__rsmail_subscribers'))
					->where($db->qn('SubscriberEmail').' IN ('.$subquery.')');
				
				$db->setQuery($query);
				$cids = $db->loadColumn();
			}
		} else {
			// we get the number of the selected subscribers
			$count	= count($cids);
			
			$subquery->clear()
				->select($db->qn('SubscriberEmail'))
				->from($db->qn('#__rsmail_subscribers'))
				->where($db->qn('IdSubscriber').' IN ('.implode(',',$cids).')');
			
			$query->clear()
				->select($db->qn('IdSubscriber'))
				->from($db->qn('#__rsmail_subscribers'))
				->where($db->qn('SubscriberEmail').' IN ('.$subquery.')');
			
			$db->setQuery($query);
			$cids = $db->loadColumn();
		}
		
		$query->clear()
			->select('DISTINCT('.$db->qn('IdList').')')
			->from($db->qn('#__rsmail_subscribers'))
			->where($db->qn('IdSubscriber').' IN ('.implode(',',$cids).')');
		
		$db->setQuery($query);
		$filtered_lists = $db->loadColumn();

		$view->subscribers_count = $count;
		$view->cid = $cids;
		$view->filtered_lists = $filtered_lists;
		$view->lists	= $this->getLists();
		
		JToolBarHelper::title(JText::_('RSM_COPY_MOVE_SUBSCRIBERS_TITLE') , 'rsmail');
		JToolBarHelper::custom('subscribers.copymove','copy','', JText::_('RSM_COPY_MOVE_SUBSCRIBERS'),false);
		JToolBarHelper::custom('subscribers', 'cancel.png', 'cancel.png', JText::_('RSM_CANCEL_BTN'),false);
		
		if (!rsmailHelper::isJ3()) {
			$doc = JFactory::getDocument();
			$doc->addScript(JURI::root(true).'/administrator/components/com_rsmail/assets/js/jquery.js');
			$doc->addScriptDeclaration('jQuery.noConflict();');
		}
		
		$view->display();
	}
	
	// Get lists
	public function getLists() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$lists	= array();
		
		$query->clear()
			->select($db->qn('lf.FieldName'))->select($db->qn('lf.IdListFields'))
			->select($db->qn('l.IdList'))->select($db->qn('l.ListName'))
			->from($db->qn('#__rsmail_lists','l'))
			->join('LEFT', $db->qn('#__rsmail_list_fields','lf').' ON '.$db->qn('l.IdList').' = '.$db->qn('lf.IdList'))
			->order($db->qn('ordering').' ASC');
		
		$db->setQuery($query);
		if ($all_fields = $db->loadObjectList()) {
			foreach($all_fields as $field){
				$lists[$field->IdList]['fields'][$field->FieldName] = $field->IdListFields;
				$lists[$field->IdList]['RSMListName'] = $field->ListName;
			}
		}

		return $lists;
	}
	
	// Copy/Move subscribers
	public function copymove() {
		$db			= JFactory::getDbo();
		$query		= $db->getQuery(true);
		$app		= JFactory::getApplication();
		$jinput		= $app->input;
		$cids		= $jinput->get('cid', array(), 'array');
		
		$destination	= $jinput->get('destination_list');
		$action			= $jinput->get('action');
		$original_field = $jinput->get('original_field',array(),'array');

		if(!empty($destination)) {
			$query->clear()
				->select($db->qn('IdListFields'))->select($db->qn('FieldName'))
				->from($db->qn('#__rsmail_list_fields'))
				->where($db->qn('IdList').' = '.(int) $destination);
			
			$db->setQuery($query);
			$destination_fields = $db->loadObjectList();
			
			JArrayHelper::toInteger($cids);
			foreach($cids as $cid) {
				$query->clear()
					->select('*')
					->from($db->qn('#__rsmail_subscribers'))
					->where($db->qn('IdSubscriber').' = '.$cid);
				$db->setQuery($query);
				$subscriber = $db->loadObject();

				// check if the email already exists in that list 
				$query->clear()
					->select('COUNT('.$db->qn('IdSubscriber').')')
					->from($db->qn('#__rsmail_subscribers'))
					->where($db->qn('SubscriberEmail').' = '.$db->q($subscriber->SubscriberEmail))
					->where($db->qn('IdList').' = '.(int) $destination);
				
				$db->setQuery($query);
				$check_subscriber_list = $db->loadResult();

				if (!$check_subscriber_list) {
					// insert into subscribers 
					$query->clear()
						->insert($db->qn('#__rsmail_subscribers'))
						->set($db->qn('SubscriberEmail').' = '.$db->q($subscriber->SubscriberEmail))
						->set($db->qn('IdList').' = '.(int) $destination)
						->set($db->qn('DateSubscribed').' = '.$db->q($subscriber->DateSubscribed))
						->set($db->qn('SubscriberIp').' = '.$db->q($subscriber->SubscriberIp))
						->set($db->qn('UserId').' = '.$db->q($subscriber->UserId))
						->set($db->qn('published').' = '.$db->q($subscriber->published));
					
					$db->setQuery($query);
					$db->execute();
					// get the IdSubscriber that was inserted
					$destination_subscriber_id = $db->insertid();
				} else {
					//get the subscriber assigned to the destination list
					$query->clear()
						->select($db->qn('IdSubscriber'))
						->from($db->qn('#__rsmail_subscribers'))
						->where($db->qn('SubscriberEmail').' = '.$db->q($subscriber->SubscriberEmail))
						->where($db->qn('IdList').' = '.(int) $destination);
					
					$db->setQuery($query);
					$destination_subscriber_id = $db->loadResult();
				}

				// insert subscribers_details
				foreach($destination_fields as $dest_field) {
					
					if(!empty($original_field[$subscriber->IdList][$dest_field->IdListFields])) {
						
						$query->clear()
							->select($db->qn('FieldValue'))
							->from($db->qn('#__rsmail_subscriber_details'))
							->where($db->qn('IdSubscriber').' = '.(int) $subscriber->IdSubscriber)
							->where($db->qn('IdList').' = '.(int) $subscriber->IdList)
							->where($db->qn('FieldName').' = '.$db->q($original_field[$subscriber->IdList][$dest_field->IdListFields]));
						
						$db->setQuery($query);
						$original_field_value = $db->loadResult();
						
						$query->clear()
							->select('COUNT('.$db->qn('IdSubscriberDetails').')')
							->from($db->qn('#__rsmail_subscriber_details'))
							->where($db->qn('FieldName').' = '.$db->q($dest_field->FieldName))
							->where($db->qn('IdList').' = '.(int) $destination)
							->where($db->qn('IdSubscriber').' = '.(int) $destination_subscriber_id);
						
						$db->setQuery($query);
						$check_field_detail = $db->loadResult();

						if (!$check_field_detail) {
							$query->clear()
								->insert($db->qn('#__rsmail_subscriber_details'))
								->set($db->qn('IdSubscriber').' = '.(int) $destination_subscriber_id)
								->set($db->qn('IdList').' = '.(int) $destination)
								->set($db->qn('FieldName').' = '.$db->q($dest_field->FieldName))
								->set($db->qn('FieldValue').' = '.$db->q($original_field_value));
							
							$db->setQuery($query);
							$db->execute();
						} else {
							// overwrite field value if fieldname exists
							$query->clear()
								->update($db->qn('#__rsmail_subscriber_details'))
								->set($db->qn('FieldValue').' = '.$db->q($original_field_value))
								->where($db->qn('IdSubscriber').' = '.(int) $destination_subscriber_id)
								->where($db->qn('IdList').' = '.(int) $destination)
								->where($db->qn('FieldName').' = '.$db->q($dest_field->FieldName));
							
							$db->setQuery($query);
							$db->execute();
						}
					}
				}
				
				// for the move action delete the subscriber
				if($action == 'move') {
					// Delete From subscribers table
					$query->clear()->delete()->from($db->qn('#__rsmail_subscribers'))->where($db->qn('IdSubscriber').' = '.(int) $subscriber->IdSubscriber);
					$db->setQuery($query);
					$db->execute();

					// Delete From subscriber details table 
					$query->clear()->delete()->from($db->qn('#__rsmail_subscriber_details'))->where($db->qn('IdSubscriber').' = '.(int) $subscriber->IdSubscriber);
					$db->setQuery($query);
					$db->execute();

					// delete from subscriber clicks 
					$query->clear()->delete()->from($db->qn('#__rsmail_subscribers_clicks'))->where($db->qn('IdSubscriber').' = '.(int) $subscriber->IdSubscriber);
					$db->setQuery($query);
					$db->execute();

					// delete from subscriber opens
					$query->clear()->delete()->from($db->qn('#__rsmail_subscribers_opens'))->where($db->qn('IdSubscriber').' = '.(int) $subscriber->IdSubscriber);
					$db->setQuery($query);
					$db->execute();
				}
			}

			return $action == 'move' ? JText::sprintf('RSM_SUCCESSFULLY_MOVED') : JText::sprintf('RSM_SUCCESSFULLY_COPIED');
		}
	}
	
	// Remove subscribers
	public function delete() {
		$db 		= JFactory::getDbo();
		$query		= $db->getQuery(true);
		$subquery	= $db->getQuery(true);
		$app		= JFactory::getApplication();
		$jinput		= $app->input;
		$cids		= $jinput->get('cid', array(), 'array');
		$squery		= $this->buildQuery();
		$filtered 	= $jinput->getInt('filtered_results', 0);
		$from_lists	= $jinput->getString('from_lists', '');
		$from_lists = !empty($from_lists) ? rtrim($from_lists,',') : '';
		$from_lists = !empty($from_lists) ? explode(',',$from_lists) : array();
		$ids		= array();
		
		JArrayHelper::toInteger($from_lists);
		
		// if filtered results overwrite checked subscribers
		if($filtered) {
			$db->setQuery($squery);
			$subscribers = $db->loadObjectList();
			$cids		 = array();

			if (!empty($subscribers))
				foreach($subscribers as $subscriber)
					$cids[]	 = $subscriber->IdSubscriber;
		}

		if (!empty($cids)) {
			foreach($cids as $cid) 	{
				$subquery->clear()
					->select($db->qn('SubscriberEmail'))
					->from($db->qn('#__rsmail_subscribers'))
					->where($db->qn('IdSubscriber').' = '.(int) $cid);
				
				$query->clear()
					->select($db->qn('IdSubscriber'))
					->from($db->qn('#__rsmail_subscribers'))
					->where($db->qn('SubscriberEmail').' = ('.$subquery.')')
					->where($db->qn('IdList').' IN ('.implode(',',$from_lists).')');
				
				$db->setQuery($query);
				if ($tmp_ids = $db->loadColumn()) {
					foreach($tmp_ids as $IdSubscriber)
						$ids[] = $IdSubscriber;
				}
			}
			unset($cids);
		}

		if (!empty($ids)) {
			foreach($ids as $id) {
				// Delete From subscribers table
				$query->clear()->delete()->from($db->qn('#__rsmail_subscribers'))->where($db->qn('IdSubscriber').' = '.(int) $id);
				$db->setQuery($query);
				$db->execute();
				
				// Delete From subscriber details table
				$query->clear()->delete()->from($db->qn('#__rsmail_subscriber_details'))->where($db->qn('IdSubscriber').' = '.(int) $id);
				$db->setQuery($query);
				$db->execute();

				// delete from subscriber clicks 
				$query->clear()->delete()->from($db->qn('#__rsmail_subscribers_clicks'))->where($db->qn('IdSubscriber').' = '.(int) $id);
				$db->setQuery($query);
				$db->execute();

				// delete from subscriber opens
				$query->clear()->delete()->from($db->qn('#__rsmail_subscribers_opens'))->where($db->qn('IdSubscriber').' = '.(int) $id);
				$db->setQuery($query);
				$db->execute();
			}
			
			return JText::_('RSM_SUBSCRIBER_DELETE');
		} else {
			return JText::_('RSM_DELETE_PHP_ERR_NO_RESULTS');
		}
	}
	
	public function send() {
		$app 		= JFactory::getApplication();
		$session	= JFactory::getSession();
		$filters 	= array();
		$filtred	= $app->input->getInt('filtered_results',0);
		$cids		= $app->input->get('cid',array(),'array');

		if ($filtred) {
			$published  = $app->getUserStateFromRequest('com_rsmail.subscribers.filter_published', 	'filter_published',	array(), 'array');
			$lists 		= $app->getUserStateFromRequest('com_rsmail.subscribers.filter_lists', 	 	'filter_lists', 	array(), 'array');
			$fields		= $app->getUserStateFromRequest('com_rsmail.subscribers.filter_fields',  	'filter_fields', 	array(), 'array');
			$operators 	= $app->getUserStateFromRequest('com_rsmail.subscribers.filter_operators',  'filter_operators', array(), 'array');
			$values 	= $app->getUserStateFromRequest('com_rsmail.subscribers.filter_values', 	'filter_values', 	array(), 'array');
			$condition 	= $app->getUserStateFromRequest('com_rsmail.subscribers.filter_condition', 	'filter_condition', 'AND');

			$filters['filters']['published'] 	= $published;
			$filters['filters']['lists'] 		= $lists;
			$filters['filters']['fields'] 		= $fields;
			$filters['filters']['operators'] 	= $operators;
			$filters['filters']['values'] 		= $values;
			$filters['filters']['condition']	= $condition;
		} else {
			if(!empty($cids))
				$filters['cids'] = $cids;
		}

		$session->set('session_filters',$filters);
	}
	
	public function getChartData() {
		$db			= JFactory::getDbo();
		$query		= $db->getQuery(true);
		$input		= JFactory::getApplication()->input;
		$lists		= $input->get('lists',array(),'array');
		$from		= $input->getString('from','');
		$to			= $input->getString('to','');
		$interval	= $input->getInt('interval',1);
		$state		= $input->getInt('unsubscribers',0);
		
		$query->clear()
			->select('COUNT('.$db->qn('s.IdSubscriber').') AS total')
			->select($db->qn('l.ListName','list'))
			->from($db->qn('#__rsmail_subscribers','s'))
			->join('LEFT',$db->qn('#__rsmail_lists','l').' ON '.$db->qn('l.IdList').' = '.$db->qn('s.IdList'))
			->where($db->qn('s.DateSubscribed').' <> '.$db->q($db->getNullDate()))
			->group('date,list')
			->order('date ASC');
		
		if ($interval == 1) {
			$query->select('DATE_FORMAT('.$db->qn('s.DateSubscribed').',\'%Y-%m-01\') AS date');
		} else if ($interval == 2) {
			$query->select('DATE_FORMAT('.$db->qn('s.DateSubscribed').',\'%Y-01-01\') AS date');
		} else {
			$query->select('DATE_FORMAT('.$db->qn('s.DateSubscribed').',\'%Y-%m-%d\') AS date');
		}
		
		if ($state)
			$query->where($db->qn('s.published').' = 0');
		else 
			$query->where($db->qn('s.published').' = 1');
		
		if (!empty($lists)) {
			JArrayHelper::toInteger($lists);
			$query->where($db->qn('l.IdList').' IN ('.implode(',',$lists).')');
		}
		
		if (!empty($from)) {
			$from = JFactory::getDate($from);
			$query->where($db->qn('s.DateSubscribed').' >= '.$db->q($from->toSql()));
		}
		
		if (!empty($to)) {
			$to = JFactory::getDate($to);
			$query->where($db->qn('s.DateSubscribed').' < '.$db->q($to->toSql()));
		}
		
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	public function getIntervals() {
		return array(JHtml::_('select.option',0,JText::_('RSM_CHARTS_INTERVAL_DAY')), JHtml::_('select.option',1,JText::_('RSM_CHARTS_INTERVAL_MONTH')), JHtml::_('select.option',2,JText::_('RSM_CHARTS_INTERVAL_YEAR')));
	}
	
	public function getFormat() {
		$interval	= JFactory::getApplication()->input->getInt('interval',1);
		
		if ($interval == 1) {
			return 'F Y';
		} elseif ($interval == 2) {
			return 'Y';
		} else {
			return 'd F Y';
		}
	}
}