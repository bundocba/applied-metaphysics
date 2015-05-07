<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2010 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.model');

class RSMailModelUnsubscribe extends JModelLegacy
{
	public function __construct() {
		parent::__construct();
	}

	public function getIdSession() {
		$IdSession 	= JFactory::getApplication()->input->getInt('IdSession',0);
		$user 		= JFactory::getUser();
		
		if(empty($IdSession) && $user->get('id'))
			$IdSession = '9999999';

		return $IdSession;
	}

	public function getHash() {
		$hash	= JFactory::getApplication()->input->getString('vid','');
		$user 	= JFactory::getUser();
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);

		// set the hash when it does not exist and the user is logged in
		if(empty($hash) && $user->get('id')){
			$query->clear()
				->select($db->qn('IdSubscriber'))->select($db->qn('SubscriberEmail'))
				->from($db->qn('#__rsmail_subscribers'))
				->where($db->qn('SubscriberEmail').' = '.$db->q($user->get('email')));
			
			$db->setQuery($query,0,1);
			if ($subscriber = $db->loadObject()) {
				$hash = md5($subscriber->SubscriberEmail.$subscriber->IdSubscriber);
			}
		}

		return $hash;
	}

	public function getEmail() {
		$user 		= JFactory::getUser();
		$IdSession 	= $this->getIdSession();
		$hash	   	= $this->getHash();
		$db			= JFactory::getDbo();
		$query		= $db->getQuery(true);

		// if the user is logged in and clicked a direct link (without IdSession and hash)
		if ($user->get('id') && !$hash && !$IdSession) {
			return $user->get('email');
		} elseif ($hash) {
			$query->clear()
				->select($db->qn('SubscriberEmail'))
				->from($db->qn('#__rsmail_subscribers'))
				->where('MD5(CONCAT('.$db->qn('SubscriberEmail').','.$db->qn('IdSubscriber').')) = '.$db->q($hash));
			
			$db->setQuery($query);
			return $db->loadResult();
		} else {
			return $user->get('email');
		}

		return false;
	}

	public function getEmailLists() {
		$email 	= $this->getEmail();
		$config = rsmailHelper::getConfig();
		$lists	= $config->unsubscribe_option == 'userchoice' ? null : $config->unsubscribe_lists;

		return rsmailHelper::user_subscribed_lists($email,$lists);
	}
}