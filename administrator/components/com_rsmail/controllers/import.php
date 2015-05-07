<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controller');
jimport('joomla.filesystem.file');

class rsmailControllerImport extends JControllerLegacy
{
	public function __construct() {
		parent::__construct();
	}
	
	public function upload() {
		$input	= JFactory::getApplication()->input;
		$file	= $input->files->get('file');
		$list	= $input->getInt('IdList',0);
		$del	= $input->getString('rsm_delimiter',',');
		$path	= JPATH_ADMINISTRATOR.'/components/com_rsmail/assets/tmp_file.csv';
		$sess	= JFactory::getSession();
		
		if (empty($list)) {
			return $this->setRedirect('index.php?option=com_rsmail&view=import',JText::_('RSM_PLEASE_SELECT_LIST'),'error');
		}
		
		if (JFile::getExt($file['name']) != 'csv') {
			return $this->setRedirect('index.php?option=com_rsmail&view=import',JText::_('RSM_ONLY_CSV'),'error');
		}
		
		if (!JFile::upload($file['tmp_name'],$path))
			return $this->setRedirect('index.php?option=com_rsmail&view=import',JText::_('RSM_ERROR_UPLOADING_IMPORT_FILE'),'error');
		
		$sess->set('rsm_delimiter', $del);
		return $this->setRedirect('index.php?option=com_rsmail&view=import&layout=import&id='.$list);
	}
	

	public function save() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$input	= JFactory::getApplication()->input;
		$session= JFactory::getSession();
		$IdList = $input->getInt('IdList',0);
		$bytes	= $input->getString('bytes',0);
		$path	= JPATH_ADMINISTRATOR.'/components/com_rsmail/assets/tmp_file.csv';
		$querys	= rsmailHelper::getConfig('import_querys_nr');
		$cols	= $input->get('FieldName',array(),'array');
		$now	= JFactory::getDate()->toSql();
		
		$delimiter = $session->get('rsm_delimiter');
		if(empty($delimiter)) $delimiter = ',';
		
		$cols = array_flip($cols);
		unset($cols[JText::_('RSM_IGNORE')]);
		
		if($querys != 0 || $querys != '')
			$limit = $querys;
		else
			$limit = 1000;
		
		setlocale(LC_ALL, 'en_US.UTF-8');
		$content = array();
		$h = fopen($path, 'r');
		
		if (!empty($h)) {
			fseek($h, $bytes);
			for ($i=0;$i<$limit;$i++) {
				$data = fgetcsv($h, 4096,$delimiter);
				if (count($data) == 1 && $data[0] == '') continue;
				
				if ($data !== false)
					$content[] = $data;
				else
					break;
			}
		}
		
		$max = count($content);
		
		for ($i=0;$i<$max;$i++) {
		 	foreach($cols as $fieldname => $col) {
				$email = false;
				if ($fieldname == JText::_('RSM_EMAIL'))
					$email = @$content[$i][$col];
				
				if (!empty($email)) {
					$query->clear()
						->insert($db->qn('#__rsmail_subscribers'))
						->set($db->qn('SubscriberEmail').' = '.$db->q($email))
						->set($db->qn('DateSubscribed').' = '.$db->q($now))
						->set($db->qn('IdList').' = '.$db->q($IdList))
						->set($db->qn('published').' = 1');
					
					$db->setQuery($query);
					$db->execute();
					$IdSubscriber = $db->insertid();
				}
			}
			
			
			foreach ($cols as $fieldname => $col) {
				if (!isset($content[$i][$col]))
					$content[$i][$col] = '';
					
				if ($fieldname == JText::_('RSM_EMAIL')) continue;
				
				$query->clear()
					->insert($db->qn('#__rsmail_subscriber_details'))
					->set($db->qn('IdSubscriber').' = '.(int) $IdSubscriber)
					->set($db->qn('FieldName').' = '.$db->q($fieldname))
					->set($db->qn('FieldValue').' = '.$db->q($content[$i][$col]))
					->set($db->qn('IdList').' = '.$db->q($IdList));
				
				$db->setQuery($query);
				$db->execute();
			}
		}
		
		$offset = ftell($h);
		
		if (feof($h) || $offset === false)
			echo 'END';
		else
			echo $offset;
		echo "\n".filesize($path);
		fclose($h);
		exit();
	}
}