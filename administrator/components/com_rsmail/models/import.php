<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die();
 
jimport( 'joomla.application.component.model' );
jimport('joomla.filesystem.file');

class rsmailModelImport extends JModelLegacy
{
	public function __construct() {	
		parent::__construct();
	}
	
	public function getHeaders() {
		$id		= JFactory::getApplication()->input->getInt('id',0);
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		
		$query->clear()
			->select($db->qn('IdListFields'))->select($db->qn('FieldName'))
			->from($db->qn('#__rsmail_list_fields'))
			->where($db->qn('IdList').' = '.$id)
			->order($db->qn('ordering').' ASC');
		
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	public function getContent() {
		$session	= JFactory::getSession();
		$delimiter	= $session->get('rsm_delimiter');
		$file		= JPATH_ADMINISTRATOR.'/components/com_rsmail/assets/tmp_file.csv';
		$content	= array();
		
		if(empty($delimiter)) 
			$delimiter = ',';
		
		setlocale(LC_ALL, 'en_US.UTF-8');
		$h = fopen($file, 'r');

		if (!empty($h)) {
			for ($i=0;$i<5;$i++) {
				$data = fgetcsv($h, 4096,$delimiter);					
				
				if (count($data) == 1 && $data[0] == '') 
					continue;
				
				$empty = true; 
				foreach ($data as $d) 
					if (!empty($d)) 
						$empty = false;
				if($empty == true) continue;
				
				if ($data !== false)
					$content[] = $data;
				else
					break;
			}
			fclose($h);
		}
		
		return $content;
	}
	
	public function getLists() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		
		$query->clear()
			->select($db->qn('IdList','value'))->select($db->qn('ListName','text'))
			->from($db->qn('#__rsmail_lists'));
		$db->setQuery($query);
		return $db->loadObjectList();
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
}