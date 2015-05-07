<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die();
jimport( 'joomla.application.component.model' );

class rsmailModelSubscriber extends JModelAdmin
{
	protected $text_prefix = 'COM_RSMAIL';

	/**
	 * Returns a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 *
	 * @return	JTable	A database object
	*/
	public function getTable($type = 'Subscriber', $prefix = 'rsmailTable', $config = array()) {
		return JTable::getInstance($type, $prefix, $config);
	}
	
	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 *
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true) {
		// Get the form.
		$form = $this->loadForm('com_rsmail.subscriber', 'subscriber', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
			return false;
		
		return $form;
	}
	
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData() {
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_rsmail.edit.subscriber.data', array());

		if (empty($data))
			$data = $this->getItem();

		return $data;
	}
	
	public function getLists() {
		$db 		= JFactory::getDbo();
		$query		= $db->getQuery(true);
		$subquery	= $db->getQuery(true);
		$jinput		= JFactory::getApplication()->input;
		$id			= $jinput->getInt('IdSubscriber',0);
		$all_lists	= array();
		
		$query->clear()
			->select($db->qn('SubscriberEmail'))
			->from($db->qn('#__rsmail_subscribers'))
			->where($db->qn('IdSubscriber').' = '.$id);
		$db->setQuery($query);
		$email = $db->loadResult();
		
		$query->clear()
			->select($db->qn('lf.IdList'))->select($db->qn('lf.FieldName'))
			->from($db->qn('#__rsmail_list_fields','lf'))
			->join('LEFT', $db->qn('#__rsmail_lists','l').' ON '.$db->qn('l.IdList').' = '.$db->qn('lf.IdList'))
			->order($db->qn('lf.ordering').' ASC');
		
		$db->setQuery($query);
		if ($fields = $db->loadObjectList()) {
			foreach($fields as $field)
				$all_lists[$field->IdList]['fields'][$field->FieldName] = '';
		}

		// Get list name
		$query->clear()->select('*')->from($db->qn('#__rsmail_lists'));
		$db->setQuery($query);
		if ($lists = $db->loadObjectList()) {
			foreach($lists as $list)
				$all_lists[$list->IdList]['RSMListName'] = $list->ListName;
		}

		// Chekc subscription to lists
		$query->clear()
			->select($db->qn('IdList'))->select($db->qn('published'))
			->from($db->qn('#__rsmail_subscribers'))
			->where($db->qn('SubscriberEmail').' = '.$db->q($email))
			->where($db->qn('published').' = 1');
		
		$db->setQuery($query);
		if ($IdLists = $db->loadObjectList()) {
			foreach($IdLists as $IdList) 
				$all_lists[$IdList->IdList]['RSMisSubscribed'] = $IdList->published;
		}

		// Populate values
		$subquery->clear()
			->select($db->qn('IdSubscriber'))
			->from($db->qn('#__rsmail_subscribers'))
			->where($db->qn('SubscriberEmail').' = '.$db->q($email));
		
		$query->clear()
			->select($db->qn('IdList'))->select($db->qn('FieldName'))->select($db->qn('FieldValue'))
			->from($db->qn('#__rsmail_subscriber_details'))
			->where($db->qn('IdSubscriber').' IN ('.$subquery.')');
		
		
		$db->setQuery($query);
		if ($subscriber_fieldslists = $db->loadObjectList()) {
			foreach($subscriber_fieldslists as $field)
				$all_lists[$field->IdList]['fields'][$field->FieldName] = $field->FieldValue;
		}

		ksort($all_lists);
		return $all_lists;
	}
	
	/**
	 * Method to save the form data.
	 *
	 * @param	array	The form data.
	 *
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	public function save($data) {
		$db 	= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$app	= JFactory::getApplication();
		$jinput	= $app->input;
		$id		= (int) $data['IdSubscriber'];
		$email	= $data['SubscriberEmail'];
		$lists	= $jinput->get('lists',array(),'array');
		$fields	= $jinput->get('fields',array(),'array');
		$now	= JFactory::getDate()->toSql();
		
		// Get old email address
		$query->clear()->select($db->qn('SubscriberEmail'))->from($db->qn('#__rsmail_subscribers'))->where($db->qn('IdSubscriber').' = '.$id);
		$db->setQuery($query);
		$oldemail = $db->loadResult();

		//check if this is new or edit
		if (!empty($id)) {
			$query->clear()
				->select($db->qn('IdList'))
				->from($db->qn('#__rsmail_subscribers'))
				->where($db->qn('SubscriberEmail').' = '.$db->q($oldemail));
			
			$db->setQuery($query);
			$lists_in_db 	= $db->loadColumn();
			$delete_lists 	= array_diff($lists_in_db, $lists);
			
			// Remove subscriber from lists
			if (!empty($delete_lists)) {
				foreach($delete_lists as $listid) {
					$query->clear()
						->select($db->qn('IdSubscriber'))
						->from($db->qn('#__rsmail_subscribers'))
						->where($db->qn('IdList').' = '.(int) $listid)
						->where($db->qn('SubscriberEmail').' = '.$db->q($oldemail));
					$db->setQuery($query);
					$IdSubscriber = (int) $db->loadResult();
					
					$query->clear()
						->update($db->qn('#__rsmail_subscribers'))
						->set($db->qn('published').' = 0')
						->where($db->qn('IdList').' = '.(int) $listid)
						->where($db->qn('IdSubscriber').' = '.$IdSubscriber);
					$db->setQuery($query);
					$db->execute();
				}
			}
			
			// Update lists
			if (!empty($lists)) {
				foreach($lists as $listid) {
					// Check to see if the subscriber already exists in this list
					$query->clear()
						->select($db->qn('IdSubscriber'))
						->from($db->qn('#__rsmail_subscribers'))
						->where($db->qn('IdList').' = '.(int) $listid)
						->where($db->qn('SubscriberEmail').' = '.$db->q($oldemail));
					$db->setQuery($query);
					$IdSubscriber = (int) $db->loadResult();
					
					if (!$IdSubscriber) {
						// Insert subscriber
						$query->clear()
							->insert($db->qn('#__rsmail_subscribers'))
							->set($db->qn('SubscriberEmail').' = '.$db->q($email))
							->set($db->qn('IdList').' = '.(int) $listid)
							->set($db->qn('DateSubscribed').' = '.$db->q($now))
							->set($db->qn('published').' = 1');
						$db->setQuery($query);
						$db->execute();
						$IdSubscriber = $db->insertid();
					} else {
						$query->clear()
							->update($db->qn('#__rsmail_subscribers'))
							->set($db->qn('published').' = 1')
							->where($db->qn('IdList').' = '.(int) $listid)
							->where($db->qn('SubscriberEmail').' = '.$db->q($oldemail));
						$db->setQuery($query);
						$db->execute();
					}

					if (!empty($fields[$listid])) {
						foreach($fields[$listid] as $FieldName => $FieldValue) {
							$query->clear()
								->select($db->qn('IdSubscriberDetails'))
								->from($db->qn('#__rsmail_subscriber_details'))
								->where($db->qn('IdList').' = '.(int) $listid)
								->where($db->qn('IdSubscriber').' = '.(int) $IdSubscriber)
								->where($db->qn('FieldName').' = '.$db->q($FieldName));
							$db->setQuery($query);
							$IdSubscriberDetails = (int) $db->loadResult();
							
							// Insert/Update subscriber details
							if ($IdSubscriberDetails == 0) {
								$query->clear()
									->insert($db->qn('#__rsmail_subscriber_details'))
									->set($db->qn('IdSubscriber').' = '.(int) $IdSubscriber)
									->set($db->qn('IdList').' = '.(int) $listid)
									->set($db->qn('FieldName').' = '.$db->q($FieldName))
									->set($db->qn('FieldValue').' = '.$db->q($FieldValue));
								$db->setQuery($query);
								$db->execute();
							} else {
								$query->clear()
									->update($db->qn('#__rsmail_subscriber_details'))
									->set($db->qn('IdList').' = '.(int) $listid)
									->set($db->qn('FieldName').' = '.$db->q($FieldName))
									->set($db->qn('FieldValue').' = '.$db->q($FieldValue))
									->where($db->qn('IdSubscriberDetails').' = '.(int) $IdSubscriberDetails);
								$db->setQuery($query);
								$db->execute();
							}
						}
					}
				}
			}
			
			// Update email address
			if ($oldemail != $email) {
				$query->clear()
					->update($db->qn('#__rsmail_subscribers'))
					->set($db->qn('SubscriberEmail').' = '.$db->q($email))
					->where($db->qn('SubscriberEmail').' = '.$db->q($oldemail));
				
				$db->setQuery($query);
				$db->execute();
			}
		} else {
			$query->clear()
				->select('COUNT('.$db->qn('SubscriberEmail').')')
				->from($db->qn('#__rsmail_subscribers'))
				->where($db->qn('SubscriberEmail').' = '.$db->q($email));
			$db->setQuery($query);
			
			if ($db->loadResult() == 0) {
				if (!empty($lists)) {
					foreach($lists as $listid) {
						$query->clear()
							->insert($db->qn('#__rsmail_subscribers'))
							->set($db->qn('SubscriberEmail').' = '.$db->q($email))
							->set($db->qn('IdList').' = '.(int) $listid)
							->set($db->qn('DateSubscribed').' = '.$db->q($now))
							->set($db->qn('SubscriberIp').' = '.$db->q($_SERVER['REMOTE_ADDR']))
							->set($db->qn('published').' = 1');
						
						$db->setQuery($query);
						if($db->execute()) {
							$IdSubscriber = $db->insertid();
							if(!empty($fields[$listid])) {
								foreach($fields[$listid] as $fieldname => $fieldvalue) {
									$query->clear()
										->insert($db->qn('#__rsmail_subscriber_details'))
										->set($db->qn('IdSubscriber').' = '.(int) $IdSubscriber)
										->set($db->qn('IdList').' = '.(int) $listid)
										->set($db->qn('FieldName').' = '.$db->q($fieldname))
										->set($db->qn('FieldValue').' = '.$db->q($fieldvalue));
									$db->setQuery($query);
									$db->execute();
								}
							}
						} else {
							$this->setError(JText::_('RSM_SUBSCRIBER_SAVED_ERROR'));
							return false;
						}
					}
				}
			} else {
				$this->setError(JText::_('RSM_SUBSCRIBER_ALREADY_EXISTS'));
				return false;
			}
		}
		
		$this->setState($this->getName() . '.id', $IdSubscriber);
		return true;
	}
}