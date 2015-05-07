<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

class RSMailControllerRSMail extends RSMailController
{
	function __construct()
	{
		parent::__construct();
		$this->registerTask('apply' ,  'save');
	}
	
	function save()
	{	
		$db = & JFactory::getDBO();
		$app = & JFactory::getApplication();
		$post = JRequest::get('post',JREQUEST_ALLOWRAW);

		// save unsubscribe_lists as string 
		$post['rsmailConfig']['unsubscribe_lists'] = !empty($post['rsmailConfig']['unsubscribe_lists']) ? implode(',',$post['rsmailConfig']['unsubscribe_lists']) : '';

		$rsmailConfigPost 	= $post['rsmailConfig'];
		$language 			= $post['lang'];
		
		$db->setQuery("TRUNCATE TABLE `#__rsmail_emails`");
		$db->query();

		foreach($language as $type => $langs)
			foreach($langs as $tag => $text)
			{
				$db->setQuery("INSERT INTO `#__rsmail_emails` SET `lang` = '".$tag."' , `type` = '".$type."' ,  `text` = '".$db->getEscaped($text)."'");
				$db->query();
			}
		
		$db->setQuery("SELECT * FROM `#__rsmail_config`");
		$rsmailConfigDb = $db->loadObjectList();
		foreach ($rsmailConfigDb as $objConfig)
		{  
			if(isset($rsmailConfigPost[$objConfig->ConfigName]))
			{
				$db->setQuery("UPDATE #__rsmail_config SET ConfigValue='".$db->getEscaped($rsmailConfigPost[$objConfig->ConfigName])."' WHERE ConfigName='".$objConfig->ConfigName."'");
				$db->query();
				$rsmailConfig[$objConfig->ConfigName] = $rsmailConfigPost[$objConfig->ConfigName];
			}
		}
		$app->setUserState('rsmailConfig',$rsmailConfig);
		$msg = JText::_('RSM_SETTINGS_SAVE');

		$tabposition = JRequest::getInt('tabposition', 0);
		switch(JRequest::getCmd('task'))
		{
			case 'apply' :
					$link = 'index.php?option=com_rsmail&task=settings&tabposition='.$tabposition;
			break;

			case 'save' :
					$link = 'index.php?option=com_rsmail';
			break;
		}
		$this->setRedirect($link, $msg);
	}
	
	
	function cancel()
	{
		$this->setRedirect('index.php?option=com_rsmail',JText::_('RSM_CANCEL'));
	}
	
}
?>