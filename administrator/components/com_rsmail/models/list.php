<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die();
jimport( 'joomla.application.component.model' );

class rsmailModelList extends JModelAdmin
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
	public function getTable($type = 'List', $prefix = 'rsmailTable', $config = array()) {
		return JTable::getInstance($type, $prefix, $config);
	}
	
	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 */
	public function getItem($pk = null) {
		if ($item = parent::getItem($pk)) {
			$db		= JFactory::getDbo();
			$query	= $db->getQuery(true);
			$data	= array();
			
			$query->clear()
				->select($db->qn('IdListFields'))->select($db->qn('IdList'))
				->select($db->qn('FieldName'))->select($db->qn('ordering'))
				->from($db->qn('#__rsmail_list_fields'))
				->where($db->qn('IdList').' = '.(int) $item->IdList)
				->order($db->qn('ordering').' ASC');
			
			$db->setQuery($query);
			$fields = $db->loadObjectList();
			
			if (!empty($fields)) {
				$count = count($fields);
				for ($i=0;$i<$count;$i++) {
					$field = $fields[$i];
					if (!array_key_exists($field->ordering, $data)) $data[$field->ordering] = $field;
							else $data[$i] = $field;
				}
			}
			
			$item->fields = $data;
		}
		
		return $item;
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
		$form = $this->loadForm('com_rsmail.list', 'list', array('control' => 'jform', 'load_data' => $loadData));
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
		$data = JFactory::getApplication()->getUserState('com_rsmail.edit.list.data', array());

		if (empty($data))
			$data = $this->getItem();

		return $data;
	}
	
	public function clearlist() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$id		= JFactory::getApplication()->input->getInt('id',0);
		
		$query->clear()
			->delete()
			->from($db->qn('#__rsmail_subscriber_details'))
			->where($db->qn('IdList').' = '.$id);
		
		$db->setQuery($query);
		$db->execute();
		
		$query->clear()
			->delete()
			->from($db->qn('#__rsmail_subscribers'))
			->where($db->qn('IdList').' = '.$id);
		
		$db->setQuery($query);
		$db->execute();
		
		return true;
	}
	
	public function deletefield() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$id		= JFactory::getApplication()->input->getInt('id',0);
		
		if (!empty($id)) {
			$query->clear()
				->select($db->qn('FieldName'))->select($db->qn('IdList'))
				->from($db->qn('#__rsmail_list_fields'))
				->where($db->qn('IdListFields').' = '.$id);
			$db->setQuery($query);
			if ($field = $db->loadObject()) {
				$query->clear()
					->delete()
					->from($db->qn('#__rsmail_subscriber_details'))
					->where($db->qn('FieldName').' = '.$db->q($field->FieldName))
					->where($db->qn('IdList').' = '.(int) $field->IdList);
				
				$db->setQuery($query);
				$db->execute();
			}
			
			$query->clear()
				->delete()
				->from($db->qn('#__rsmail_list_fields'))
				->where($db->qn('IdListFields').' = '.$id);
			
			$db->setQuery($query);
			$db->execute();
		}
	}
	
	public function savefield() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$id		= JFactory::getApplication()->input->getInt('id',0);
		$table 	= JTable::getInstance('Fields', 'rsmailTable');
		$data	= array();
		$idlist	= JFactory::getApplication()->input->getInt('IdList',0);
		$fname	= JFactory::getApplication()->input->getString('FieldName','');
		
		$data['IdList'] = $idlist;
		$data['FieldName'] = $fname;
		
		$table->load($id);
		$old_name = $table->FieldName;
		$data['IdListFields'] = $id;
		
		$table->bind($data);
		$table->store();
		
		if(!empty($id)) {
			$query->clear()
				->update($db->qn('#__rsmail_subscriber_details'))
				->set($db->qn('FieldName').' = '.$db->q($fname))
				->where($db->qn('FieldName').' = '.$db->q($old_name));
			
			$db->setQuery($query);
			$db->execute();
		}

		return array('FieldName' => $table->FieldName, 'IdListFields' => $table->IdListFields);
	}
	
	public function getField() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$id		= JFactory::getApplication()->input->getInt('id',0);
		
		$query->clear()
			->select($db->qn('IdList'))->select($db->qn('FieldName'))
			->from($db->qn('#__rsmail_list_fields'))
			->where($db->qn('IdListFields').' = '.$id);
		
		$db->setQuery($query);
		return $db->loadObject();
	}
}