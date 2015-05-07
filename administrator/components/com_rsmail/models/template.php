<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die();
jimport( 'joomla.application.component.model' );

class rsmailModelTemplate extends JModelAdmin
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
	public function getTable($type = 'Template', $prefix = 'rsmailTable', $config = array()) {
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
		$form = $this->loadForm('com_rsmail.template', 'template', array('control' => 'jform', 'load_data' => $loadData));
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
		$data = JFactory::getApplication()->getUserState('com_rsmail.edit.template.data', array());

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
	 * Method to get the preview message
	 */
	public function getPreview() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$jinput	= JFactory::getApplication()->input;
		$id		= $jinput->getInt('id',0);
		$row	= $this->getItem($id);
		
		$message	= $row->TemplateBody;
		$message	= rsmailHelper::placeholders($message);
		$message	= rsmailHelper::setHeader($message, $row->MessageType);
		$message	= rsmailHelper::absolute($message);
		
		return $message;
	}
	
	public function defaults() {
		$db			= JFactory::getDbo();
		$sqlfile	= JPATH_ADMINISTRATOR.'/components/com_rsmail/templates.sql';
		$buffer		= file_get_contents($sqlfile);
		
		if ($buffer === false) {
			return false;
		}
			
		jimport('joomla.installer.helper');
		$queries = JInstallerHelper::splitSql($buffer);
		if (count($queries) == 0) {
			// No queries to process
			return false;
		}
			
		// Process each query in the $queries array (split out of sql file).
		foreach ($queries as $query) {
			$query = trim($query);
			if ($query != '' && $query{0} != '#') {
				$db->setQuery($query);
				if (!$db->execute()) {
					return false;
				}
			}
		}
		
		return true;
	}
}