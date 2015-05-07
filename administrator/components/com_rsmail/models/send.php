<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die();
jimport( 'joomla.application.component.model' );

class rsmailModelSend extends JModelLegacy
{
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * Method to get message placeholders
	 */
	public function getPlaceholders() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$id		= JFactory::getApplication()->input->getInt('id');
		$return = array();
		
		$query->clear()
			->select($db->qn('MessageSubject'))->select($db->qn('MessageBody'))->select($db->qn('MessageBodyNoHTML'))
			->from($db->qn('#__rsmail_messages'))
			->where($db->qn('IdMessage').' = '.$id);
		
		$db->setQuery($query);
		$result = $db->loadObject();
		
		$pattern = '#\{(.*?)\}#i';
		preg_match_all($pattern, $result->MessageBody, $matches1);
		preg_match_all($pattern, $result->MessageSubject, $matches2);
		preg_match_all($pattern, $result->MessageBodyNoHTML, $matches3);
		$matches = array_merge($matches1[1],$matches2[1],$matches3[1]);
		
		$tmp = array_flip($matches);
		foreach($tmp as $field => $val)
			$return[] = $field;
		
		return $return;
	}
	
	/**
	 * Method to get fields
	 */
	public function getFields() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$fields = array();
		
		$query->clear()->select($db->qn('IdList'))->from($db->qn('#__rsmail_lists'));
		$db->setQuery($query);
		if ($ids = $db->loadColumn()) {
			JArrayHelper::toInteger($ids);
			foreach ($ids as $id) {
				$query->clear()->select($db->qn('FieldName'))->from($db->qn('#__rsmail_list_fields'))->where($db->qn('IdList').' = '.$id);
				$db->setQuery($query);
				$fields[$id][] = $db->loadObjectList();
			}
		}
		
		return $fields;
	}
	
	/**
	 * Method to get lists
	 */
	public function getLists() {
		$db 		= JFactory::getDbo();
		$query		= $db->getQuery(true);
		$session 	= JFactory::getSession();
		$filters	= $session->get('session_filters');
		
		if(isset($filters)) {
			if(isset($filters['filters'])) { 
				if(!empty($filters['filters']['lists'])) {
					$where				= $this->getFilterCondition($filters['filters']);
					$condition_length	= isset($filters['filters']['condition']) ? strlen($filters['filters']['condition']) : 2;
					
					
					$fquery = 'SELECT DISTINCT('.$db->qn('s.IdList').') FROM '.$db->qn('#__rsmail_subscribers','s').' LEFT JOIN '.$db->qn('#__rsmail_subscriber_details','sd').' ON '.$db->qn('s.IdSubscriber').' = '.$db->qn('sd.IdSubscriber').' WHERE 1 ';
					
					if (!empty($where) && count($filters['filters']['published']) > 1)
						$fquery .= ' AND ('.substr($where, 0, -$condition_length).')';
					else $fquery .= ' AND '.substr($where, 0, -$condition_length);
					
					$fquery .= ' GROUP BY '.$db->qn('s.SubscriberEmail').' ORDER BY '.$db->qn('s.IdSubscriber').' ASC';
					
					$db->setQuery($fquery);
					$lists = $db->loadColumn();
				} else {
					$query->clear()->select($db->qn('IdList'))->from($db->qn('#__rsmail_lists'))->order($db->qn('IdList'));
					$db->setQuery($query);
					$lists = $db->loadColumn();
				}
			} else {
				$query->clear()
					->select('DISTINCT('.$db->qn('IdList').')')
					->from($db->qn('#__rsmail_subscribers'))
					->where($db->qn('IdSubscriber').' IN ('.implode(',',$filters['cids']).')');
				
				$db->setQuery($query);
				$lists = $db->loadColumn();
			}
			
			$query->clear()
				->select($db->qn('IdList'))->select($db->qn('ListName'))
				->from($db->qn('#__rsmail_lists'))
				->where($db->qn('IdList').' IN ('.implode(',',$lists).')');
			$db->setQuery($query);
			return $db->loadObjectList();
		} else {
			$query->clear()->select($db->qn('IdList'))->select($db->qn('ListName'))->from($db->qn('#__rsmail_lists'));
			$db->setQuery($query);
			return $db->loadObjectList();
		}
	}
	
	/**
	 * Method to get list fields
	 */
	public function getListFields() {
		$lists			= $this->getLists();
		$fields			= $this->getFields();
		$placeholders	= $this->getPlaceholders();
		$content		= array();
		$return			= array();
		$default_lists	= array(JHTML::_('select.option', 0, JText::_('RSM_IGNORE'), 'FieldName', 'FieldName'), JHTML::_('select.option', 0, JText::_('RSM_DO_NOT_REPLACE'), 'FieldName', 'FieldName'), JHTML::_('select.option', 0, JText::_('RSM_EMAIL'), 'FieldName', 'FieldName'));
		
		if ($lists) {
			foreach($lists as $i => $list) {
				$fields_of_list = $fields[$list->IdList];
				$content		= array_merge($default_lists , $fields_of_list[0]);
				
				for ($j=0;$j<count($placeholders);$j++) {
					$return['fields'][$list->IdList][$j] = JHTML::_('select.genericlist', $content, 'FieldName['.$list->IdList.']['.$placeholders[$j].']','size="1"','FieldName','FieldName');
				}
			}
		}
		
		return $return;
	}
	
	/**
	 * Method to get filtering condition
	 */
	public function getFilterCondition($filters) {
		$db		= JFactory::getDbo();
		$where	= '';
		
		if (empty($filters['lists']))
			return $where;
		
		foreach($filters['lists'] as $key => $list) {
			$subscribe_state	= 1;
			$operator 			= $filters['operators'][$key];
			$value				= $filters['values'][$key];
			$condition			= $filters['condition'];

			if (!isset($filters['fields'][$key])) {
				$field = '';
			} elseif ($filters['fields'][$key] == 'email')
				$field 	= $db->qn('s.SubscriberEmail');
			elseif(!empty($filters['fields'][$key])) {
				$db->setQuery('SELECT '.$db->qn('FieldName').' FROM '.$db->qn('#__rsmail_list_fields').' WHERE '.$db->qn('IdListFields').' = '.$db->q($filters['fields'][$key]));
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
							if($filters['fields'][$key] == 'email')
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
							if($filters['fields'][$key] == 'email') {
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

		return $where;
	}
	
	/**
	 * Method to get the xml form file
	 */
	public function getForm() {
		jimport('joomla.form.form');
		
		JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
		JForm::addFieldPath(JPATH_COMPONENT . '/models/fields');
		
		$form = JForm::getInstance('com_rsmail.send', 'send');
		return $form;
	}
	
	/**
	 * Method to get the message name
	 */
	public function getMessageName() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$id		= JFactory::getApplication()->input->getInt('id',0);
		
		$query->clear()
			->select($db->qn('m.MessageSubject'))
			->from($db->qn('#__rsmail_messages','m'))
			->join('LEFT', $db->qn('#__rsmail_sessions','s').' ON '.$db->qn('s.IdMessage').' = '.$db->qn('m.IdMessage'))
			->where($db->qn('s.IdSession').' = '.$id);
		
		$db->setQuery($query);
		return $db->loadResult();
	}
	
	/**
	 * Method to get session details
	 */
	public function getSession() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$id		= JFactory::getApplication()->input->getInt('id',0);
		
		$query->clear()
			->select($db->qn('IdSession'))->select($db->qn('IdMessage'))->select($db->qn('Position'))
			->select($db->qn('Lists'))->select($db->qn('MaxEmails'))->select($db->qn('Status'))
			->from($db->qn('#__rsmail_sessions'))
			->where($db->qn('IdSession').' = '.$id);
		
		$db->setQuery($query);
		return $db->loadObject();
	}
	
	/**
	 * Method to get session lists
	 */
	public function getSessionLists() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$id		= JFactory::getApplication()->input->getInt('id',0);
		
		$query->clear()
			->select($db->qn('Lists'))
			->from($db->qn('#__rsmail_sessions'))
			->where($db->qn('IdSession').' = '.$id);
		
		$db->setQuery($query);
		if ($lists = $db->loadResult()) {
			$lists = explode(',',$lists);
			JArrayHelper::toInteger($lists);
			
			$query->clear()
				->select($db->qn('ListName'))
				->from($db->qn('#__rsmail_lists'))
				->where($db->qn('IdList').' IN ('.implode(',',$lists).')');
			
			$db->setQuery($query);
			if ($listnames = $db->loadColumn())
				return implode(', ',$listnames);
		}
		
		return '';
	}
	
	/**
	 * Method to get maximum number of emails
	 */
	public function getMax() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$id		= JFactory::getApplication()->input->getInt('id',0);
		
		$query->clear()
			->select($db->qn('Lists'))->select($db->qn('MaxEmails'))
			->from($db->qn('#__rsmail_sessions'))
			->where($db->qn('IdSession').' = '.$id);
		
		$db->setQuery($query);
		$session = $db->loadObject();
		
		$query->clear()
			->select('COUNT(DISTINCT '.$db->qn('SubscriberEmail').')')
			->from($db->qn('#__rsmail_subscribers'))
			->where($db->qn('IdList').' IN ('.$session->Lists.')');
		
		$db->setQuery($query);
		$max = $db->loadResult();
		
		return empty($session->MaxEmails) ? $max : $session->MaxEmails;
	}
	
	
	/**
	 * Method to save session
	 */
	public function save() {
		jimport('joomla.plugin.helper');
		JPluginHelper::importPlugin('rsmail');
		
		$db				= JFactory::getDbo();
		$query			= $db->getQuery(true);
		$app			= JFactory::getApplication();
		$input			= $app->input;
		$cids			= $input->get('cid',array(),'array');
		$IdMessage		= $input->getInt('IdMessage',0);
		$field			= $input->get('FieldName',array(),'array');
		$now			= JFactory::getDate()->toSql();
		$time			= $input->getString('DeliverDate');
		$delivery		= $input->getInt('Delivery',0);
		$session		= JFactory::getSession();
		$filters		= $session->get('session_filters');
		$LinkHistory	= $input->getInt('LinkHistory',0);
		$OpensHistory	= $input->getInt('OpensHistory',0);
		
		// Select MAX(IdSubscriber)
		if(isset($filters)) {
			// Select from the filtered results
			if(isset($filters['filters'])) {
				if(!empty($filters['filters']['lists'])) {
					$where				= $this->getFilterCondition($filters['filters']);
					$condition_length	= isset($filters['filters']['condition']) ? strlen($filters['filters']['condition']) : 2;

					$mquery = 'SELECT MAX('.$db->qn('s.IdSubscriber').') FROM '.$db->qn('#__rsmail_subscribers','s').' LEFT JOIN '.$db->qn('#__rsmail_subscriber_details','sd').' ON '.$db->qn('s.IdSubscriber').' = '.$db->qn('sd.IdSubscriber').' WHERE 1 ';
					
					if (!empty($where) && count($filters['filters']['published']) > 1) 
						$mquery .= 'AND ('.substr($where, 0, -$condition_length).')';
					else $mquery .= 'AND '.substr($where, 0, -$condition_length);
				} else {
					$mquery = 'SELECT MAX('.$db->qn('IdSubscriber').') FROM '.$db->qn('#__rsmail_subscribers').' WHERE '.$db->qn('published').' = 1';
				}
			} else {
				// Select the max IdSubscriber from the checked emails 
				$mquery = 'SELECT MAX('.$db->qn('IdSubscriber').') FROM '.$db->qn('#__rsmail_subscribers').' WHERE '.$db->qn('IdSubscriber').' IN ('.implode(',', $filters['cids']).') AND '.$db->qn('published').' = 1';
			}
		} else {
			$mquery = 'SELECT MAX('.$db->qn('IdSubscriber').') FROM '.$db->qn('#__rsmail_subscribers').' WHERE '.$db->qn('IdList').' IN ('.implode(',',$cids).') AND '.$db->qn('published').' = 1';
		}

		$db->setQuery($mquery);
		$maxId = $db->loadResult();
		
		if (empty($maxId)) {
			$this->setError(JText::_('RSM_EMPTY_LISTS'));
			return false;
		}
		
		
		// Set the maxEmails limit
		if(isset($filters)) {
			if(isset($filters['filters'])) {
				if(!empty($filters['filters']['lists'])) {
					$where				= $this->getFilterCondition($filters['filters']);
					$condition_length	= isset($filters['filters']['condition']) ? strlen($filters['filters']['condition']) : 2;
					
					$mequery = 'SELECT COUNT(DISTINCT '.$db->qn('s.SubscriberEmail').') FROM '.$db->qn('#__rsmail_subscribers','s').' LEFT JOIN '.$db->qn('#__rsmail_subscriber_details','sd').' ON '.$db->qn('s.IdSubscriber').' = '.$db->qn('sd.IdSubscriber').' WHERE '.$db->qn('s.IdSubscriber').' <= '.(int) $maxId.' ';
					
					if (!empty($where) && count($filters['filters']['published']) > 1)
						$mequery .= 'AND ('.substr($where, 0, -$condition_length).')';
					else $mequery .= 'AND '.substr($where, 0, -$condition_length);
					
					$mequery .= ' ORDER BY '.$db->qn('s.IdSubscriber').' ASC';
				} else {
					$mequery =  'SELECT COUNT(DISTINCT '.$db->qn('SubscriberEmail').') FROM '.$db->qn('#__rsmail_subscribers').' WHERE '.$db->qn('IdSubscriber').' <= '.(int) $maxId.' AND '.$db->qn('published').' = 1 ORDER BY '.$db->qn('IdSubscriber').' ASC';
				}
			} else {
				$mequery = 'SELECT COUNT(DISTINCT '.$db->qn('SubscriberEmail').') FROM '.$db->qn('#__rsmail_subscribers').' WHERE '.$db->qn('IdSubscriber').' IN ('.implode(',',$filters['cids']).') AND '.$db->qn('IdSubscriber').' <= '.(int) $maxId.' AND '.$db->qn('published').' = 1 ORDER BY '.$db->qn('IdSubscriber').' ASC';
			}
		} else {
			$mequery = 'SELECT COUNT(DISTINCT '.$db->qn('SubscriberEmail').') FROM '.$db->qn('#__rsmail_subscribers').' WHERE '.$db->qn('IdList').' IN ('.implode(',',$cids).') AND '.$db->qn('IdSubscriber').' <= '.(int) $maxId.' AND '.$db->qn('published').' = 1';
		}
		
		$db->setQuery($mequery);
		$maxEmails = $db->loadResult();

		// Store filters
		if(isset($filters)) {
			$db->setQuery('INSERT INTO '.$db->qn('#__rsmail_session_filters').' SET '.$db->qn('Filters').' = '.$db->q(serialize($filters)).'');
			
			if($db->execute()) {
				$session->clear('session_filters');
				$IdFilter = $db->insertid();
			}
		}
		
		$squery = 'INSERT INTO '.$db->qn('#__rsmail_sessions').' SET '.$db->qn('Lists').' = '.$db->q(implode(',',$cids)).', '.$db->qn('Position').' = 0, '.$db->qn('IdMessage').' = '.$db->q($IdMessage).', '.$db->qn('Date').' = '.$db->q($now).', '.$db->qn('Status').' = 0, '.$db->qn('IdMaxSubscriber').'  = '.$db->q($maxId).', '.$db->qn('MaxEmails').' = '.$db->q($maxEmails).', '.$db->qn('Delivery').' = '.$db->q($delivery).', '.$db->qn('DeliverDate').' = '.$db->q($time).', '.$db->qn('LinkHistory').' = '.$db->q($LinkHistory).', '.$db->qn('OpensHistory').' = '.$db->q($OpensHistory).'';
		
		if (isset($IdFilter))
			$squery .= ', '.$db->qn('IdFilter').' = '.$db->q($IdFilter).'';
		
		$db->setQuery($squery);
		$db->execute();
		$lastSessionId = $db->insertid();

		// Add placeholder values
		if (!empty($cids)) {		
			foreach($cids as $cid) {
				if (!empty($field)) {
					foreach ($field[$cid] as $placeholder => $fieldname) {
						$db->setQuery('INSERT INTO '.$db->qn('#__rsmail_session_details').' SET '.$db->qn('IdSession').' = '.(int) $lastSessionId.', '.$db->qn('IdList').' = '.(int) $cid.', '.$db->qn('ToSearch').' = '.$db->q($placeholder).', '.$db->qn('ToReplace').' = '.$db->q($fieldname).''); 
						$db->execute();
					}
				}
			}
		}
		
		$db->setQuery('SELECT '.$db->qn('MessageBody').', '.$db->qn('MessageBodyNoHTML').', '.$db->qn('MessageType').' FROM '.$db->qn('#__rsmail_messages').' WHERE '.$db->qn('IdMessage').' = '.(int) $IdMessage.'');
		$message = $db->loadObject();
		
		$body = $message->MessageType ? $message->MessageBody : $message->MessageBodyNoHTML;
		$app->triggerEvent('rsm_parseMessageContent',array(array('message'=>&$body,'idmessage'=>$IdMessage,'idsubscriber'=>0,'idsession'=>$lastSessionId,'email'=>'','idlist'=>'')));
		$body  = rsmailHelper::absolute($body);
		
		$pattern = '#href="(.*?)"#i';
		preg_match_all($pattern, $body, $matches);
		if(!empty($matches[1])) {
			foreach($matches[1] as $i => $match) {
				if (substr($match,0,1) == '#' || substr(strtolower($match),0,6) == 'mailto' || substr(strtolower($match),0,10) == 'javascript' || strpos($match,'com_rsmail&view=unsubscribe') !== FALSE || strpos($match,'com_rsmail&view=history') !== FALSE || strpos($match,'com_rsmail&view=details') !== FALSE) {
					unset($matches[1][$i]);
					continue;
				}
			}

			$urls = array_flip($matches[1]);
			$urls = array_flip($urls);
		
			if (!empty($urls)) {
				foreach($urls as $url) {
					$db->setQuery('INSERT INTO '.$db->qn('#__rsmail_reports').' SET '.$db->qn('IdSession').' = '.(int) $lastSessionId.', '.$db->qn('Url').' = '.$db->q($url).'');
					$db->execute();
				}
			}
		}
		
		$this->setState('session.ids', $lastSessionId);
		$this->setState('session.delivery', $delivery);
		return true;
	}
}