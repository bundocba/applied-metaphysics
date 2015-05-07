<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die(); 
jimport( 'joomla.application.component.model' );

class rsmailModelRsmail extends JModelList
{	
	public function __construct() {	
		parent::__construct();
	}
	
	public function getData() {
		$newdata = array();
		$this->_db = JFactory::getDBO();
		$this->_db->setQuery("SELECT * FROM `#__rsmail_emails`");
		$data = $this->_db->loadObjectList();
		
		foreach($data as $object)
			$newdata[$object->type][$object->lang] = $object->text; 
		
		return $newdata;
	}
}