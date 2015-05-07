<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2010 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.model');

class rsmailModelHistory extends JModelLegacy
{	
	public function __construct() {
		parent::__construct();
		
		$app	= JFactory::getApplication();
		$user	= JFactory::getUser();
		$code	= $app->input->getString('code','');
		
		if ($user->get('guest') && empty($code)) {
			$link = JURI::getInstance();
			$link = base64_encode($link);
			$app->redirect(JRoute::_('index.php?option=com_users&view=login&return='.$link, false));
		}
	}
	
	public function getItems() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$email	= JFactory::getUser()->get('email');
		
		$query->clear()
			->select($db->qn('m.IdMessage'))->select($db->qn('m.MessageSubject'))
			->select($db->qn('s.IdSession'))->select($db->qn('s.Date'))
			->from($db->qn('#__rsmail_messages','m'))
			->join('LEFT',$db->qn('#__rsmail_sessions','s').' ON '.$db->qn('s.IdMessage').' = '.$db->qn('m.IdMessage'))
			->join('LEFT',$db->qn('#__rsmail_subscribers','sb').' ON '.$db->qn('sb.IdList').' IN ('.$db->qn('s.Lists').')')
			->where($db->qn('sb.SubscriberEmail').' = '.$db->q($email))
			->where($db->qn('s.Date').' > '.$db->qn('sb.DateSubscribed'))
			->order($db->qn('s.IdSession').' DESC');
		
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	public function getMessage() {
		$db				= JFactory::getDbo();
		$query		 	= $db->getQuery(true);
		$app			= JFactory::getApplication();
		$input			= $app->input;
		$user			= JFactory::getUser();
		$array_search	= array();
		$array_replace	= array();
		$code			= $input->getString('code','');
		$replacer		= array();
		
		$query->clear()
			->select($db->qn('m.IdMessage'))->select($db->qn('s.IdSession'))->select($db->qn('sb.SubscriberEmail'))
			->from($db->qn('#__rsmail_messages','m'))
			->join('LEFT',$db->qn('#__rsmail_sessions','s').' ON '.$db->qn('s.IdMessage').' = '.$db->qn('m.IdMessage'))
			->join('LEFT',$db->qn('#__rsmail_subscribers','sb').' ON '.$db->qn('sb.IdList').' IN ('.$db->qn('s.Lists').')')
			->where('MD5(CONCAT('.$db->qn('m.IdMessage').','.$db->qn('s.IdSession').','.$db->qn('sb.SubscriberEmail').')) = '.$db->q($code))
			->where($db->qn('s.Date').' > '.$db->qn('sb.DateSubscribed'))
			->order($db->qn('s.IdSession').' DESC');
		
		$db->setQuery($query);
		$details = $db->loadObject();
		
		if(is_null($details) && !empty($code)) 
			$app->redirect(JURI::root());
		
		$ids = empty($code) ? $input->getInt('sess',0) : $details->IdSession;
		$idm = empty($code) ? $input->getInt('cid',0) : $details->IdMessage;
		$subscriber = empty($code) ? $user->get('email') : $details->SubscriberEmail;
		
		// Get List
		$query->clear()
			->select('DISTINCT('.$db->qn('sb.IdList').')')
			->from($db->qn('#__rsmail_subscribers','sb'))
			->join('LEFT',$db->qn('#__rsmail_sessions','s').' ON '.$db->qn('sb.IdList').' IN ('.$db->qn('s.Lists').')')
			->where($db->qn('sb.SubscriberEmail').' = '.$db->q($subscriber))
			->where($db->qn('s.IdSession').' = '.(int) $ids);
		
		$db->setQuery($query,0,1);
		$idlist = $db->loadResult();
		
		$query->clear()
			->select($db->qn('IdSubscriber'))
			->from($db->qn('#__rsmail_subscribers'))
			->where($db->qn('SubscriberEmail').' = '.$db->q($subscriber))
			->where($db->qn('IdList').' = '.(int) $idlist);
		
		$db->setQuery($query);
		$idsubscriber = $db->loadResult();
		
		// Load details
		$query->clear()->select('*')->from($db->qn('#__rsmail_session_details'))->where($db->qn('IdSession').' = '.(int) $ids);
		$db->setQuery($query);
		if ($sessionDetails = $db->loadObjectList()) {
			foreach($sessionDetails as $sd)
				$replacer[$sd->IdList][$sd->ToSearch] = $sd->ToReplace;
		}
		
		// Get subscriber details
		$query->clear()
			->select('sd.*')
			->from($db->qn('#__rsmail_subscribers','s'))
			->join('LEFT',$db->qn('#__rsmail_subscriber_details','sd').' ON '.$db->qn('s.IdSubscriber').' IN ('.$db->qn('sd.IdSubscriber').')')
			->where($db->qn('sd.IdList').' = '.(int) $idlist)
			->where($db->qn('s.SubscriberEmail').' = '.$db->q($subscriber));
		
		$db->setQuery($query);
		$SubscriberDetails = $db->loadObjectList();
		
		//populate array_search and array_replace
		if(!empty($replacer)) {
			foreach($replacer[$idlist] as $search => $replace) {
				//search for the replace in subscriber details
				$replace_with = '';
				if($replace == JText::_('RSM_DO_NOT_REPLACE')) $replace_with = '{'.$search.'}';
				foreach($SubscriberDetails as $Detail) {
					if($replace == $Detail->FieldName) $replace_with = $Detail->FieldValue;
					if($replace == JText::_('RSM_IGNORE')) $replace_with = '';
					if($replace == JText::_('RSM_EMAIL')) $replace_with = $subscriber;
				}	
				$array_search[] = '{'.$search.'}';
				$array_replace[] = $replace_with;
			}
		}

		// Get message
		$query->clear()
			->select($db->qn('MessageSubject'))->select($db->qn('MessageBody'))
			->from($db->qn('#__rsmail_messages'))
			->where($db->qn('IdMessage').' = '.(int) $idm);
		
		$db->setQuery($query);
		$message = $db->loadObject();
		
		// Get attachements
		$query->clear()
			->select($db->qn('IdFile'))->select($db->qn('FileName'))
			->from($db->qn('#__rsmail_files'))
			->where($db->qn('IdMessage').' = '.(int) $idm);
		$db->setQuery($query);
		$message->attachements = $db->loadObjectList();
		
		$message->MessageSubject	= str_replace($array_search,$array_replace,$message->MessageSubject);
		$message->MessageBody		= str_replace($array_search,$array_replace,$message->MessageBody);
		$message->MessageBody		= rsmailHelper::cron_replace_articles($message->MessageBody,$idm,$idsubscriber,$ids,$subscriber,$idlist);
		$message->IdSession			= $ids;
		$message->IdMessage			= $idm;
		
		return $message;
	}
}