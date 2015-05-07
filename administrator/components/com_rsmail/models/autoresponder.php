<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die();
jimport( 'joomla.application.component.model' );

class rsmailModelAutoresponder extends JModelAdmin
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
	public function getTable($type = 'Autoresponder', $prefix = 'rsmailTable', $config = array()) {
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
		$form = $this->loadForm('com_rsmail.autoresponder', 'autoresponder', array('control' => 'jform', 'load_data' => $loadData));
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
		$data = JFactory::getApplication()->getUserState('com_rsmail.edit.autoresponder.data', array());

		if (empty($data))
			$data = $this->getItem();

		return $data;
	}
	
	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 */
	public function getItem($pk = null) {
		$item = parent::getItem($pk);
		return $item;
	}
	
	/**
	 * Method to get autoresponder messages
	 */
	public function getMessages() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$id		= JFactory::getApplication()->input->getInt('IdAutoresponder',0);
		
		$query->clear()
			->select($db->qn('arm.IdMessage'))->select($db->qn('arm.IdAutoresponderMessage'))
			->select($db->qn('arm.ordering'))->select($db->qn('m.MessageSubject'))->select($db->qn('arm.DelayPeriod'))
			->from($db->qn('#__rsmail_ar_messages','arm'))
			->join('LEFT',$db->qn('#__rsmail_messages','m').' ON '.$db->qn('m.IdMessage').' = '.$db->qn('arm.IdMessage'))
			->where($db->qn('arm.IdAutoresponder').' = '.$id)
			->order($db->qn('arm.ordering').' ASC');
		
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	/**
	 * Method to get messages
	 */
	public function getSubjects() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		
		$query->clear()
			->select($db->qn('IdMessage','value'))->select($db->qn('MessageSubject','text'))
			->from($db->qn('#__rsmail_messages'))
			->order($db->qn('MessageSubject').' DESC');
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	/**
	 * Method to get message placeholders
	 */
	public function getPlaceholders() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$id		= JFactory::getApplication()->input->getInt('id',0);
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
	 * Method to get list fields
	 */
	public function getListFields() {
		$db				= JFactory::getDbo();
		$query			= $db->getQuery(true);
		$lists			= rsmailHelper::lists();
		$fields			= $this->getFields();
		$placeholders	= $this->getPlaceholders();
		$content		= array();
		$return			= array();
		$idamessage		= JFactory::getApplication()->input->getInt('IdAutoresponderMessage',0);
		$default_lists	= array(JHTML::_('select.option', 0, JText::_('RSM_IGNORE'), 'FieldName', 'FieldName'), JHTML::_('select.option', 0, JText::_('RSM_DO_NOT_REPLACE'), 'FieldName', 'FieldName'), JHTML::_('select.option', 0, JText::_('RSM_EMAIL'), 'FieldName', 'FieldName'));
		
		if ($lists) {
			foreach($lists as $i => $list) {
				$fields_of_list = $fields[$list->IdList];
				$content		= array_merge($default_lists , $fields_of_list[0]);
				
				for ($j=0;$j<count($placeholders);$j++) {
					if (!empty($idamessage)) {
						$query->clear()
							->select($db->qn('ToReplace'))
							->from($db->qn('#__rsmail_ar_message_details'))
							->where($db->qn('IdList').' = '.$db->q($list->IdList))
							->where($db->qn('ToSearch').' = '.$db->q($placeholders[$j]))
							->where($db->qn('IdAutoresponderMessage').' = '.$idamessage);
						
						$db->setQuery($query);
						$default = $db->loadResult();
					} else {
						$default = 0;
					}
					
					$return['fields'][$list->IdList][$j] = JHTML::_('select.genericlist', $content, 'FieldName['.$list->IdList.']['.$placeholders[$j].']','size="1"','FieldName','FieldName',$default);
				}
			}
		}
		
		return $return;
	}
	
	/**
	 * Method to get fields
	 */
	protected function getFields() {
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
	 * Method to get frequency
	 */
	public function getFrequency() {
		return array(JHTML::_('select.option', 'HOUR' ,JText::_('RSM_HOURS')), JHTML::_('select.option', 'DAY' ,JText::_('RSM_DAYS')), JHTML::_('select.option', 'MONTH' ,JText::_('RSM_MONTHS')));
	}
	
	/**
	 * Method to get autoresponder lists
	 */
	public function getAutoresponderLists() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$id		= JFactory::getApplication()->input->getInt('IdAutoresponder',0);
		
		$query->clear()->select($db->qn('IdLists'))->from($db->qn('#__rsmail_autoresponders'))->where($db->qn('IdAutoresponder').' = '.$id);
		$db->setQuery($query);
		$idlists = $db->loadResult();
		
		$query->clear()->select($db->qn('IdList'))->select($db->qn('ListName'))->from($db->qn('#__rsmail_lists'))->where($db->qn('IdList').' IN ('.$idlists.')');
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	/**
	 * Method to get period
	 */
	public function getPeriod() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$id		= JFactory::getApplication()->input->getInt('IdAutoresponderMessage',0);
		
		$query->clear()
			->select($db->qn('DelayPeriod'))
			->from($db->qn('#__rsmail_ar_messages'))
			->where($db->qn('IdAutoresponderMessage').' = '.$id);
		
		$db->setQuery($query);
		$result = $db->loadResult();
		return explode(" ",$result);
	}
	
	/**
	 * Method to get save placeholders
	 */
	public function saveplaceholders() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$input	= JFactory::getApplication()->input;
		$fields = $input->get('FieldName', array(), 'array');
		$period = $input->getInt('DelayPeriod').' '.$input->get('DelayType');
		
		$IdAutoresponderMessage = $input->getInt('IdAutoresponderMessage',0);
		$IdAutoresponder = $input->getInt('IdAutoresponder',0);
		$IdMessage = $input->getInt('IdMessage',0);
		

		if (empty($IdAutoresponderMessage)) {
			$query->clear()
				->select('MAX('.$db->qn('ordering').')')
				->from($db->qn('#__rsmail_ar_messages'))
				->where($db->qn('IdAutoresponder').' = '.$IdAutoresponder);
			
			$db->setQuery($query);
			$ordering = $db->loadResult() + 1 ;
			
			$query->clear()
				->insert($db->qn('#__rsmail_ar_messages'))
				->set($db->qn('IdAutoresponder').' = '.$IdAutoresponder)
				->set($db->qn('IdMessage').' = '.$IdMessage)
				->set($db->qn('DelayPeriod').' = '.$db->q($period))
				->set($db->qn('ordering').' = '.(int) $ordering);
			
			$db->setQuery($query);
			$db->execute();
			$IdAutoresponderMessage = $db->insertid();
		} else {
			$query->clear()
				->update($db->qn('#__rsmail_ar_messages'))
				->set($db->qn('DelayPeriod').' = '.$db->q($period))
				->where($db->qn('IdAutoresponderMessage').' = '.$IdAutoresponderMessage);
			
			$db->setQuery($query);
			$db->execute();
		}
		
		if (!empty($fields)) {
			foreach($fields as $i => $field) {
				$query->clear()
					->delete()
					->from($db->qn('#__rsmail_ar_message_details'))
					->where($db->qn('IdAutoresponderMessage').' = '.$IdAutoresponderMessage)
					->where($db->qn('IdList').' = '.(int) $i);
				
				$db->setQuery($query);
				$db->execute();
			
				if (!empty($fields[$i])) {
					foreach($fields[$i] as $placeholder => $fieldname) {
						$query->clear()
							->insert($db->qn('#__rsmail_ar_message_details'))
							->set($db->qn('IdAutoresponderMessage').' = '.$IdAutoresponderMessage)
							->set($db->qn('IdList').' = '.(int) $i)
							->set($db->qn('ToSearch').' = '.$db->q($placeholder))
							->set($db->qn('ToReplace').' = '.$db->q($fieldname));
						
						$db->setQuery($query);
						$db->execute();
					}
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Method to delete a message
	 */
	public function deletemessage() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$id		= JFactory::getApplication()->input->getInt('id',0);
		
		$query->clear()->delete()->from($db->qn('#__rsmail_ar_message_details'))->where($db->qn('IdAutoresponderMessage').' = '.$id);
		$db->setQuery($query);
		$db->execute();
		
		$query->clear()->delete()->from($db->qn('#__rsmail_ar_messages'))->where($db->qn('IdAutoresponderMessage').' = '.$id);
		$db->setQuery($query);
		$db->execute();

		return $id;
	}
}