<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined( '_JEXEC' ) or die( 'Restricted access' ); 
jimport('joomla.mail.helper');

class rsmHelper
{
	/*
	*	Database object
	*/
	protected $_db = null;
	
	/*
	*	User object
	*/
	protected $_user = null;
	
	
	/*
	*	Main constructor
	*/
	
	public function __construct() {
		$this->_db		= JFactory::getDbo();
		$this->_user 	= JFactory::getUser();
		
		if (file_exists(JPATH_SITE.'/components/com_rsmail/helpers/rsmail.php'))
			require_once JPATH_SITE.'/components/com_rsmail/helpers/rsmail.php';
	}
	
	/*
	*	Subscribe user function
	*
	*	$email - the email address of the subscriber
	*	$list - object list
	*	$state - the subscriber's state
	*	$userid - the subscriber's user id
	*	Example: 
	*		$list = new stdClass();
	*		$list->id = 1;
	*		$list->name = 'Subscriber Name';
	*		$list->city = 'Subscriber City';
	*
	*	returns boolean
	*/
	
	
	public function subscribe($email, $list, $state, $userid = null, $force_subscribe=false) {
		// we don't have a email address OR we don't have a valid email address OR we don't have lists attached
		if (empty($email) || !$this->isEmail($email) || empty($list))
			return false;
		
		$userid = !is_null($userid) ? $userid : $this->_user->get('id');
		$now	= JFactory::getDate()->toSql();
		
		// if the user is already subscribed
		// we need to update
		if ($idsubscriber = $this->isSubscribed($email, $list->id, true)) {
			// if we don't want the user to be subscribed & updated if already found
			if (!$force_subscribe) {
				return false;
			}
			
			$this->_db->setQuery('UPDATE '.$this->_db->qn('#__rsmail_subscribers').' SET '.$this->_db->qn('DateSubscribed').' = '.$this->_db->q($now).', '.$this->_db->qn('SubscriberIp').' = '.$this->_db->q($_SERVER['REMOTE_ADDR']).', '.$this->_db->qn('UserId').' = '.(int) $userid.', '.$this->_db->qn('published').' = '.(int) $state.' WHERE '.$this->_db->qn('IdSubscriber').' = '.(int) $idsubscriber);
			$this->_db->execute();
			$idsubscriber = $this->_db->insertid();
			
		} else { // otherwise just insert a new row
			$this->_db->setQuery('INSERT INTO '.$this->_db->qn('#__rsmail_subscribers').' SET '.$this->_db->qn('IdList').' = '.(int) $list->id.', '.$this->_db->qn('SubscriberEmail').' = '.$this->_db->q($email).', '.$this->_db->qn('DateSubscribed').' = '.$this->_db->q($now).', '.$this->_db->qn('SubscriberIp').' = '.$this->_db->q($_SERVER['REMOTE_ADDR']).', '.$this->_db->qn('UserId').' = '.(int) $userid.', '.$this->_db->qn('published').' = '.(int) $state);
			$this->_db->execute();
			$idsubscriber = $this->_db->insertid();
		}
		
		$fields = $this->getFields($list->id);
		if (!empty($fields)) {
			$this->_db->setQuery('DELETE FROM '.$this->_db->qn('#__rsmail_subscriber_details').' WHERE '.$this->_db->qn('IdSubscriber').' = '.(int) $idsubscriber.' AND '.$this->_db->qn('IdList').' = '.(int) $list->id);
			$this->_db->execute();
			
			$vars = get_object_vars($list);
			foreach ($fields as $field) {
				if (!empty($vars[$field])) {
					$this->_db->setQuery('INSERT INTO '.$this->_db->qn('#__rsmail_subscriber_details').' SET '.$this->_db->qn('IdSubscriber').' = '.(int) $idsubscriber.', '.$this->_db->qn('IdList').' = '.(int) $list->id.', '.$this->_db->qn('FieldName').' = '.$this->_db->q($field).', '.$this->_db->qn('FieldValue').' =  '.$this->_db->q($vars[$field]).' ');
					$this->_db->execute();
				}
			}
		}
		
		return $idsubscriber;
	}
	
	public function usubscribe($email, $lists=null) {
		return $this->unsubscribe($email, $lists);
	}
	
	/*
	*	Unsubscribe user function
	*	
	*	$email - the email address of the subscriber
	*	$lists - a collection of lists from where the user will be unsubscribed
	*		- leave the lists array empty to unsubscibe from all lists 
	*
	*/
	
	public function unsubscribe($email, $lists = null) {
		// we don't have a email address OR we don't have a valid email address
		if (empty($email) || !$this->isEmail($email))
			return false;
		
		if (!empty($lists)) {
			$lists = is_array($lists) ? $lists : array($lists);
			JArrayHelper::toInteger($lists);
			
			$condition = ' AND '.$this->_db->qn('IdList').' IN ('.implode(',',$lists).') ';
		} else $condition = "";
		
		$this->_db->setQuery('SELECT '.$this->_db->qn('published').' FROM '.$this->_db->qn('#__rsmail_subscribers').' WHERE '.$this->_db->qn('SubscriberEmail').' = '.$this->_db->q($email).' '.$condition.' ');
		$published = $this->_db->loadResult();
		
		if (!$published) 
			return false;
		
		$this->_db->setQuery('UPDATE '.$this->_db->qn('#__rsmail_subscribers').' SET '.$this->_db->qn('published').' = 0 WHERE '.$this->_db->qn('SubscriberEmail').' = '.$this->_db->q($email).' '.$condition.' ');
		return $this->_db->execute();
	}
	
	/*
	*	Remove user email from database
	*	$email - the email address of the subscriber
	*	returns - boolean
	*/
	
	public function delete($email) 	{
		// we don't have a email address
		if (empty($email))
			return false;
			
		$this->_db->setQuery('SELECT '.$this->_db->qn('IdSubscriber').' FROM '.$this->_db->qn('#__rsmail_subscribers').' WHERE '.$this->_db->qn('SubscriberEmail').' = '.$this->_db->q($email).' ');
		if ($subscribers = $this->_db->loadColumn()) {
			foreach ($subscribers as $subscriber) {
				$this->_db->setQuery('DELETE FROM '.$this->_db->qn('#__rsmail_subscriber_details').' WHERE '.$this->_db->qn('IdSubscriber').' = '.(int) $subscriber.' ');
				$this->_db->execute();
				$this->_db->setQuery('DELETE FROM '.$this->_db->qn('#__rsmail_subscribers').' WHERE '.$this->_db->qn('IdSubscriber').' = '.(int) $subscriber.' ');
				$this->_db->execute();
			}
			
			return true;
		}
		
		return false;
	}
	
	
	/*
	*	Check if the user is subscribed
	*	$email - the email address of the subscriber
	*	$list - the list id
	*	returns - boolean
	*/
	
	public function isSubscribed($email, $list = null, $return_id=false) {
		// we don't have a email address OR we don't have a valid email address
		if (empty($email) || !$this->isEmail($email))
			return false;
		
		$condition = !empty($list) ? ' AND IdList = '.(int) $list.' ' : '';
		
		if ($return_id) {
			$this->_db->setQuery('SELECT '.$this->_db->qn('IdSubscriber').' FROM '.$this->_db->qn('#__rsmail_subscribers').' WHERE '.$this->_db->qn('SubscriberEmail').' = '.$this->_db->q($email).' '.$condition);
			return $this->_db->loadResult();
		} else {
			$this->_db->setQuery('SELECT COUNT('.$this->_db->qn('IdSubscriber').') FROM '.$this->_db->qn('#__rsmail_subscribers').' WHERE '.$this->_db->qn('SubscriberEmail').' = '.$this->_db->q($email).' '.$condition);
			return (bool) $this->_db->loadResult();
		}
	}
	
	
	/*
	*	Check if a string is a valid email address
	*	returns - boolean 
	*/
	
	public function isEmail($email) {
		return JMailHelper::isEmailAddress($email);
	}
	
	
	/*
	*	Get available list
	*/
	public function getLists() {
		$this->_db->setQuery('SELECT * FROM '.$this->_db->qn('#__rsmail_lists'));
		return $this->_db->loadObjectList();
	}
	
	
	/*
	*	Get list fields
	*/
	public function getFields($list) {
		// we don't have a valid list
		if (empty($list))
			return false;
			
		$this->_db->setQuery('SELECT '.$this->_db->qn('FieldName').' FROM '.$this->_db->qn('#__rsmail_list_fields').' WHERE '.$this->_db->qn('IdList').' = '.(int) $list);
		return $this->_db->loadColumn();
	}
	
	
	/*
	*	Prepare list
	*/
	
	public function setList($id, $fields) {
		if (empty($id))
			return false;
		
		$list		= new stdClass();
		$listFields = $this->getFields($id);
		$listFields = array_flip($listFields);
		$list->id	= (int) $id;
		
		if (!empty($fields)) {
			// convert object to array
			if (is_object($fields))
				$fields = get_object_vars($fields);
			
			if (is_array($fields)) {
				foreach($fields as $k => $v) {					
					if (isset($listFields[$k]))
						$list->{$k} = $v;
				}
			}
		}
		
		return $list;
	}
	
	/*
	*	Confirmation email
	*/
	
	public function confirmation($id, $email, $hash) {
		JFactory::getLanguage()->load('com_rsmail',JPATH_SITE);
		
		// Get message
		$message = rsmailHelper::getMessage('confirmation');
		
		$this->_db->setQuery('SELECT '.$this->_db->qn('ListName').' FROM '.$this->_db->qn('#__rsmail_lists').' WHERE '.$this->_db->qn('IdList').' = '.(int) $id);
		$listname = $this->_db->loadResult();
		
		$activation = '<a href="'.JURI::root().'index.php?option=com_rsmail&task=activate&secret='.$hash.'">'.JText::_('RSM_CLICK_TO_ACTIVATE').'</a>';
		$bad	= array('{newsletter}','{email}','{activationlink}');
		$good	= array($listname,$email,$activation);
		
		$subject = str_replace($bad,$good,$message->subject);
		$body	 = str_replace($bad,$good,$message->text);
		
		if ($message->enable) {
			$mailer	= JFactory::getMailer();
			$mailer->sendMail($message->from , $message->fromname , $email , $subject , $body , $message->mode);
		}
		
		return true;
	}
	
	
	/*
	*	Notification emails
	*/
	
	public function notifications($id, $email, $merge_vars) {
		$config		= rsmailHelper::getConfig();
		$jconfig	= JFactory::getConfig();
		
		if ($config->enable_notifications) {
			JFactory::getLanguage()->load('com_rsmail',JPATH_SITE);
			
			$emails = trim($config->notification_emails);
			if (!empty($emails)) {
				$emails = explode(',',$emails);
				if (!empty($emails)) {
					
					$this->_db->setQuery('SELECT '.$this->_db->qn('ListName').' FROM '.$this->_db->qn('#__rsmail_lists').' WHERE '.$this->_db->qn('IdList').' = '.(int) $id);
					$listname = $this->_db->loadResult();
					
					$from	  = $jconfig->get('mailfrom');
					$fromName = $jconfig->get('fromname');
					$subject  = JText::sprintf('RSM_NOTIFICATION_SUBJECT',$listname);
					
					foreach ($emails as $e) {
						if ($this->isEmail($e)) {
							$user_details[] = JText::_('RSM_EMAIL').': '.$email;
							if(!empty($merge_vars)) {
								foreach($merge_vars as $prop => $value) {
									if (empty($value)) continue;
									$user_details[] = $prop.': '.$value;
								}
							}
							
							$details = implode('<br />',$user_details);
							$body = JText::sprintf('RSM_NOTIFICATION_MESSAGE',$listname,$details);
							
							$mailer	= JFactory::getMailer();
							$mailer->sendMail($from, $fromName, $e, $subject, $body, 1);
						}
					}
				}
			}
			
			return true;
		}
		
		return false;
	}
	
	/**
	 *	Unsubscribe email 
	 */
	public function unsubscribeMessage($email,$idlist) {
		$unsubscribe = rsmailHelper::getMessage('unsubscribe');
		
		if($unsubscribe->enable) {
			$secret				= md5($email);
			$unsubscribelists	= !empty($lists) ? implode(',',$lists) : '';
			$activation			= '<a href="'.JURI::root().'index.php?option=com_rsmail&task=activatesubscribe&secret='.$secret.'&lists='.base64_encode($idlist).'">'.JText::_('RSM_CLICK_TO_ACTIVATE').'</a>';
			
			$this->_db->setQuery("SELECT ListName FROM #__rsmail_lists WHERE IdList = ".(int) $idlist);
			$list = $this->_db->loadResult();
			
			$bad		= array('{newsletter}','{email}','{activatesubscription}');
			$good		= array($list,$email,$activation);
			
			$subject 	= str_replace($bad,$good,$unsubscribe->subject);
			$body 		= str_replace($bad,$good,$unsubscribe->text);
			
			$mailer	= JFactory::getMailer();
			$mailer->sendMail($unsubscribe->from, $unsubscribe->fromname, $email, $subject, $body, $unsubscribe->mode);
		}
	}
	
	/**
	 *	Unsubscribe email link
	 */
	public function unsubscribeLink($email, $idlist) {
		$unsubscribe = rsmailHelper::getMessage('unsubscribelink');
		
		// Get subscriber
		$this->_db->setQuery('SELECT '.$this->_db->qn('IdSubscriber').', '.$this->_db->qn('SubscriberEmail').' FROM '.$this->_db->qn('#__rsmail_subscribers').' WHERE '.$this->_db->qn('IdList').' = '.(int) $idlist.' AND '.$this->_db->qn('SubscriberEmail').' = '.$this->_db->q($email).' AND '.$this->_db->qn('published').' = 1');
		$subscriber = $this->_db->loadObject();
		
		if (!$subscriber)
			return false;
		
		// Generate unsubscribe link
		$secret = md5($subscriber->SubscriberEmail.$subscriber->IdSubscriber);
		$unsubscribelink = '<a href="'.JURI::root().'index.php?option=com_rsmail&view=unsubscribe&vid='.$secret.'&IdSession=9999999">'.JText::_('RSM_UNSUBSCRIBE_LINK').'</a>';
		
		// Get user lists
		$this->_db->setQuery("SELECT ListName FROM #__rsmail_lists WHERE IdList = ".(int) $idlist);
		$list = $this->_db->loadResult();
		
		// Get the site name
		$sitename = JFactory::getConfig()->get('sitename');
		$replace = array('{unsubscribelink}', '{lists}', '{site}');
		$with 	 = array($unsubscribelink, '<ul><li>'.$list.'</li></ul>', $sitename);

		$subject = str_replace($replace, $with, $unsubscribe->subject);
		$body	 = str_replace($replace, $with, $unsubscribe->text);

		$mailer	= JFactory::getMailer();
		$mailer->sendMail($unsubscribe->from, $unsubscribe->fromname, $email, $subject, $body, $unsubscribe->mode);
	}
	
	/**
	 *	Get user state
	 */
	public function getState() {
		// Get message
		$message = rsmailHelper::getMessage('confirmation');
		return $message->enable == 1 ? 0 : 1;
	}
}