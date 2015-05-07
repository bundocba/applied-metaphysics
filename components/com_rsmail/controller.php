<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.controller');
jimport('joomla.mail.helper');

class RSMailController extends JControllerLegacy
{
	public function __construct() {
		parent::__construct();
	}

	/*
	*	Function subscribe()
	*	Add subscribers to DB
	*/

	public function subscribe() {
		$input		= JFactory::getApplication()->input;
		$jconfig	= JFactory::getConfig();
		$config		= rsmailHelper::getConfig();
		$url 		= JURI::getInstance();
		$mid		= $input->getInt('mid',0);
		$IdList 	= $input->getInt('IdList'.$mid,0);
		$fields 	= $input->get('FieldName',array(),'array');
		$email		= $input->getString('rsm_email');
		
		if(trim($config->redirect_subscribe) != '') 
			$url = $config->redirect_subscribe;
		
		// Include the mail helper
		require_once JPATH_SITE.'/components/com_rsmail/helpers/actions.php';
		// Get a new instance of the helper
		$mailHelper = new rsmHelper();
		
		// Check if the subscriber already exists
		if (!$mailHelper->isSubscribed($email,$IdList)) {
			// Set the list
			$list = $mailHelper->setList($IdList,$fields);
			// Get confirmation message
			$confirmation = rsmailHelper::getMessage('confirmation');
			// Get he state of the current subscriber
			$state = $confirmation->enable == 1 ? 0 : 1;
			// Subscribe user
			$idsubscriber = $mailHelper->subscribe($email, $list, $state);
			
			// The user was subscribed
			if ($idsubscriber) {
				// The user must confirm his subscription
				if(!$state) {
					$hash	= md5($IdList.$idsubscriber.$email);
					$mailHelper->confirmation($IdList, $email, $hash);
					$msg = JText::_('RSM_ACTIVATION_EMAIL_MESSAGE');
				}
				
				$msg = JText::_('RSM_SUBSCRIBER_ADDED');
				$_SESSION['showTM'] = 1;
				
				// Send notifications
				$mailHelper->notifications($IdList, $email, $fields);
			} else 
				$msg = JText::_('RSM_SUBSCRIBE_ERROR');
			
		} else {
			return $this->setRedirect($url, JText::_('RSM_ALREADY_SUBSCRIBED'));
		}

		return $this->setRedirect($url,$msg);
	}
	
	/*
	*	Function unsubscribe()
	*	Unsubscribe function and counter for unsubscribers 
	*/

	public function unsubscribe() {
		$db			= JFactory::getDBO();
		$query		= $db->getQuery(true);
		$input		= JFactory::getApplication()->input;
		$vid 		= $input->getString('vid','');
		$IdSession 	= $input->getInt('IdSession',0);
		$uemail		= $input->getString('rsm_unsub_email','');
		$lists		= $input->get('lists',array(),'array');
		
		if(empty($lists))
			return $this->setRedirect(JRoute::_('index.php?option=com_rsmail&view=unsubscribe&vid='.$vid.'&IdSession='.$IdSession.$Itemid,false), JText::_('RSM_NO_LISTS_SELECTED'));

		// Get the user email address
		$query->clear()
			->select($db->qn('SubscriberEmail'))
			->from($db->qn('#__rsmail_subscribers'))
			->where('MD5(CONCAT('.$db->qn('SubscriberEmail').','.$db->qn('IdSubscriber').')) = '.$db->q($vid));
		
		$db->setQuery($query);
		$email =  $db->loadResult();

		// Get all available sessions
		$query->clear()->select($db->qn('IdSession'))->from($db->qn('#__rsmail_sessions'));
		$db->setQuery($query);
		$sessionIds = $db->loadColumn();
		$sessionIds[] =  '9999999';

		// Do we have a valid email session
		if(!in_array($IdSession,$sessionIds)) 
			return $this->setRedirect('index.php' , JText::_('RSM_WRONG_DETAILS'));

		if($email == $uemail) {
			// Include the mail helper
			require_once JPATH_SITE.'/components/com_rsmail/helpers/actions.php';
			// Get a new instance of the helper
			$mailHelper = new rsmHelper();
			
			// Unsubscribe user
			$status = $mailHelper->usubscribe($uemail, $lists);			

			// Update unsubscribers counter
			if ($status) {
				$query->clear()
					->update($db->qn('#__rsmail_sessions'))
					->set($db->qn('UnsubscribeCounter').' = '.$db->qn('UnsubscribeCounter').' + 1')
					->where($db->qn('IdSession').' = '.(int) $IdSession);
				
				$db->setQuery($query);
				$db->execute();
			}
			
			$unsubscribe = rsmailHelper::getMessage('unsubscribe');
			
			if($unsubscribe->enable) {
				$secret				= md5($uemail);
				$unsubscribelists	= !empty($lists) ? implode(',',$lists) : '';
				$activation			= '<a href="'.JURI::root().'index.php?option=com_rsmail&task=activatesubscribe&secret='.$secret.'&lists='.base64_encode($unsubscribelists).'&sid='.$IdSession.'">'.JText::_('RSM_CLICK_TO_ACTIVATE').'</a>';
				
				$query->clear()->select($db->qn('ListName'))->from($db->qn('#__rsmail_lists'))->where($db->qn('IdList').' IN ('.implode(',',$lists).')');
				$db->setQuery($query);
				$list_names = $db->loadColumn();
				$ulists = !empty($list_names) ? implode(', ',$list_names) : '';
				
				$bad		= array('{newsletter}','{email}','{activatesubscription}');
				$good		= array($ulists,$uemail,$activation);
				
				$subject 	= str_replace($bad,$good,$unsubscribe->subject);
				$body 		= str_replace($bad,$good,$unsubscribe->text);
				
				$mailer	= JFactory::getMailer();
				$mailer->sendMail($unsubscribe->from, $unsubscribe->fromname, $uemail, $subject, $body, $unsubscribe->mode);
			}

			if($status) 
				return $this->setRedirect('index.php',JText::_('RSM_SUBSCRIBER_REMOVE'));
			else 
				return $this->setRedirect('index.php',JText::_('RSM_SUBSCRIBER_REMOVE_ALREADY'));

		} else $this->setRedirect('index.php?option=com_rsmail&view=unsubscribe&vid='.$vid.'&IdSession='.$IdSession , JText::_('RSM_WRONG_EMAIL')); 
	}
	
	/*
	*	Function activatesubscribe()
	*	Enables the subscriber after he already unsubscribe 
	*/
	
	public function activatesubscribe() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$input	= JFactory::getApplication()->input;
		$secret = $input->getString('secret','');
		$sid 	= $input->getInt('sid',0);
		$lists	= $input->getVar('lists','');
		$lists	= base64_decode($lists);
		$lists	= !empty($lists) ? explode(',',$lists) : array();
		JArrayHelper::toInteger($lists);	
		
		$query->clear()
			->update($db->qn('#__rsmail_subscribers'))
			->set($db->qn('published').' = 1')
			->where('MD5('.$db->qn('SubscriberEmail').') = '.$db->q($secret))
			->where($db->qn('IdList').' IN ('.implode(',',$lists).')');
		
		$db->setQuery($query);
		if ($db->execute()) {
			if ($sid) {
				$query->clear()->update($db->qn('#__rsmail_sessions'))->set($db->qn('UnsubscribeCounter').' = '.$db->qn('UnsubscribeCounter').' + 1')->where($db->qn('IdSession').' = '.$sid);
				$db->setQuery($query);
				$db->execute();
			}
			$msg = JText::_('RSM_EMAIL_ACTIVATED');
		} else $msg = JText::_('RSM_EMAIL_NOT_FOUND');
		
		$this->setRedirect('index.php',$msg);
	}
	
	/*
	*	Function activate()
	*	Enables the subscriber 
	*/
	public function activate() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$secret = JFactory::getApplication()->input->getString('secret','');
		
		$query->clear()
			->select($db->qn('IdSubscriber'))->select($db->qn('published'))
			->from($db->qn('#__rsmail_subscribers'))
			->where('MD5(CONCAT('.$db->qn('IdList').','.$db->qn('IdSubscriber').','.$db->qn('SubscriberEmail').')) = '.$db->q($secret));
		
		$db->setQuery($query);
		$details = $db->loadObject();
		
		if(!empty($details)) {
			if($details->published == 1) {
				$msg = JText::_('RSM_SUBSCRIBER_ALREADY_ACTIVATED');
			} else {
				$query->clear()->update($db->qn('#__rsmail_subscribers'))->set($db->qn('published').' = 1')->where($db->qn('IdSubscriber').' = '.(int) $details->IdSubscriber);
				$db->setQuery($query);
				$db->execute();
				$msg = JText::_('RSM_ACTIVATE_SUCCESS');
			}
		}
		else $msg = JText::_('RSM_NO_EMAIL_WITH_ACTIVATION');
		
		$redirect = rsmailHelper::getConfig('redirect_activate');
		$url = (trim($redirect) != '') ? $redirect : 'index.php';
		$this->setRedirect($url,$msg);
	}
	
	/*
	*	Function redirecturl()
	*	Redirects links that are included in the email 
	*/
	
	public function redirecturl() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(treu);
		$input	= JFactory::getApplication()->input;
		$link	= $input->getString('url');
		$link	= base64_decode($link);
		$cid	= $input->getInt('cid',0);
		$sid	= $input->getInt('sid',0);
		$ip		= $_SERVER['REMOTE_ADDR'];
		$date	= JFactory::getDate();
		$url	= strpos($link,'http') !== FALSE ? $link : 'http://'.$link;
		
		// Check for link history
		$query->clear()->select($db->qn('LinkHistory'))->from($db->qn('#__rsmail_sessions'))->where($db->qn('IdSession').' = '.$cid);
		$db->setQuery($query);
		if ($linkhist = $db->loadResult()) {
			// Add subscriber to the history links table
			$query->clear()
				->select($db->qn('IdReport'))
				->from($db->qn('#__rsmail_reports'))
				->where($db->qn('IdSession').' = '.$cid)
				->where($db->qn('Url').' = '.$db->q($link));
			
			$db->setQuery($query);
			if ($IdReport = $db->loadResult()) {
				$query->clear()
					->insert($db->qn('#__rsmail_subscribers_clicks'))
					->set($db->qn('IdSubscriber').' = '.$sid)
					->set($db->qn('IdReport').' = '.$IdReport)
					->set($db->qn('date').' = '.$db->q($date->toSql()))
					->set($db->qn('ip').' = '.$db->q($ip));
				
				$db->setQuery($query);
				$db->execute();
			}
		}
		
		// Get URL counter
		$query->clear()
			->select($db->qn('UrlCounter'))
			->from($db->qn('#__rsmail_reports'))
			->where($db->qn('IdSession').' = '.$cid)
			->where($db->qn('Url').' = '.$db->q($link));
		
		$db->setQuery($query);
		$counter = $db->loadResult();
		
		// Get the unique counter
		$query->clear()
			->select($db->qn('IdReport'))->select($db->qn('UniqueUrlCounter'))
			->from($db->qn('#__rsmail_reports'))
			->where($db->qn('IdSession').' = '.$cid)
			->where($db->qn('Url').' = '.$db->q($link));
		
		$db->setQuery($query);
		$udetails = $db->loadObject();
		
		$counter = $counter + 1;
		
		// Update the clicks counter
		$query->clear()
			->update($db->qn('#__rsmail_reports'))
			->set($db->qn('UrlCounter').' = '.(int) $counter)
			->where($db->qn('IdSession').' = '.(int) $cid)
			->where($db->qn('Url').' = '.$db->q($link));
		
		$db->setQuery($query);
		$db->execute();
		
		if(!isset($_COOKIE['RSMAILLINK'.$udetails->IdReport])) {
			$ucounter = $udetails->UniqueUrlCounter +1;
		
			// Set the new unique counter
			$query->clear()
				->update($db->qn('#__rsmail_reports'))
				->set($db->qn('UniqueUrlCounter').' = '.(int) $ucounter)
				->where($db->qn('IdSession').' = '.(int) $cid)
				->where($db->qn('Url').' = '.$db->q($link));
				
			$db->setQuery($query);
			$db->execute();
		}		
		
		setcookie('RSMAILLINK'.$udetails->IdReport,'1',time()+60*60*24*30);
		
		//redirect
		if (headers_sent())
			$this->setRedirect($url);
		else 
			header("Location: ".html_entity_decode($url));
	}
	
	/*
	*	Function openmail()
	*	Counter for email opens 
	*/
	public function openmail() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$input	= JFactory::getApplication()->input;
		$cid	= $input->getInt('cid',0);
		$ids	= $input->getInt('IdSubscriber',0);
		$ip		= $_SERVER['REMOTE_ADDR'];
		$now	= JFactory::getDate()->toSql();
		
		// Get session params
		$query->clear()
			->select($db->qn('Counter'))->select($db->qn('OpensHistory'))
			->from($db->qn('#__rsmail_sessions'))
			->where($db->qn('IdSession').' = '.$cid);
			
		$db->setQuery($query);
		$session = $db->loadObject();
		
		$counter	= $session->Counter;
		$openshits	= $session->OpensHistory;
		
		// Check if the subscriber has already opened the email
		$query->clear()
			->select($db->qn('IdSubscriberOpen'))
			->from($db->qn('#__rsmail_subscribers_opens'))
			->where($db->qn('IdSession').' = '.$cid)
			->where($db->qn('IdSubscriber').' = '.$ids);
		
		$db->setQuery($query);
		$result = $db->loadResult(); 
		
		if ($openshits) {
			$query->clear()
				->insert($db->qn('#__rsmail_subscribers_opens'))
				->set($db->qn('IdSubscriber').' = '.$ids)
				->set($db->qn('IdSession').' = '.$cid)
				->set($db->qn('date').' = '.$db->q($now))
				->set($db->qn('ip').' = '.$db->q($ip));
			
			$db->setQuery($query);
			$db->execute();
		}
		
		if (!$openshits && empty($result)) {
			$query->clear()
				->insert($db->qn('#__rsmail_subscribers_opens'))
				->set($db->qn('IdSubscriber').' = '.$ids)
				->set($db->qn('IdSession').' = '.$cid)
				->set($db->qn('date').' = '.$db->q($now))
				->set($db->qn('ip').' = '.$db->q($ip));
			
			
			$db->setQuery($query);
			$db->execute();
		}
		
		$counter = $counter +1;
		
		// Set counter
		$query->clear()
			->update($db->qn('#__rsmail_sessions'))
			->set($db->qn('Counter').' = '.(int) $counter)
			->where($db->qn('IdSession').' = '.$cid);
		
		$db->setQuery($query);
		$db->execute();
		
		header('Location: '.JURI::root().'components/com_rsmail/images/dot.gif');
		exit();
	}
	
	public function unsetsession() { 
		if(isset($_SESSION['showTM'])) 
			unset($_SESSION['showTM']);
		
		$this->setRedirect('index.php');
	}
	
	public function details() {
		$db 	= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$input 	= JFactory::getApplication()->input;
		$id 	= $input->getInt('id',0);
		$fields = $input->get('fields',array(),'array');
		
		if(!empty($fields)) {
			foreach ($fields as $ids => $properties) {
				foreach ($properties as $key => $value) {
					$query->clear()
						->select($db->qn('IdList'))
						->from($db->qn('#__rsmail_subscribers'))
						->where($db->qn('IdSubscriber').' = '.$ids);
					
					$db->setQuery($query);
					$idlist = (int) $db->loadResult();
					
					$query->clear()
						->select($db->qn('IdSubscriberDetails'))
						->from($db->qn('#__rsmail_subscriber_details'))
						->where($db->qn('IdSubscriber').' = '.$ids)
						->where($db->qn('FieldName').' = '.$db->q($key));
					
					$db->setQuery($query);
					if ($res = $db->loadResult()) {
						$query->clear()
							->insert($db->qn('#__rsmail_subscriber_details'))
							->set($db->qn('IdList').' = '.$idlist)
							->set($db->qn('IdSubscriber').' = '.$ids)
							->set($db->qn('FieldName').' = '.$db->q($key))
							->set($db->qn('FieldValue').' = '.$db->q($value));
						
						$db->setQuery($query);
						$db->execute();
					} else {
						$query->clear()
							->update($db->qn('#__rsmail_subscriber_details'))
							->set($db->qn('FieldValue').' = '.$db->q($value))
							->where($db->qn('IdList').' = '.$idlist)
							->where($db->qn('IdSubscriber').' = '.$ids)
							->where($db->qn('FieldName').' = '.$db->q($key));
						
						$db->setQuery($query);
						$db->execute();
					}
					
				}
			}
		}
		
		return $this->setRedirect('index.php?option=com_rsmail&view=details&id='.$id,JText::_('RSM_DETAILS_SAVED'));		
	}

	public function bounce() {
		require_once JPATH_SITE.'/components/com_rsmail/helpers/bounce.php';
		$connect = rsmailBounce::getInstance();
		$connect->parse();
	}
	
	public function captcha() {
		$db 	= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$id 	= JFactory::getApplication()->input->getInt('id',0);
		
		$query->clear()->select($db->qn('params'))->from($db->qn('#__modules'))->where($db->qn('id').' = '.$id);
		$db->setQuery($query);
		$moduleParams = $db->loadResult();

		$registry = new JRegistry();
		$registry->loadString($moduleParams);
		$captcha_enable = $registry->get('captcha_enable',0);

		if (!$captcha_enable)
			return false;

		ob_end_clean();
		if ($captcha_enable == 1) {
			$captcha = new RSMJSecurImage();

			$captcha_lines = $registry->get('captcha_lines');
			if ($captcha_lines)
				$captcha->num_lines = 8;
			else
				$captcha->num_lines = 0;

			$captcha_characters 	= $registry->get('captcha_characters');
			$captcha->code_length 	= $captcha_characters;
			$captcha->image_width 	= 30*$captcha_characters + 50;
			$captcha->mid 			= $id;
			$captcha->show();
		}
		die();
	}
	
	public function checkcaptcha() {
		$db 	= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$input 	= JFactory::getApplication()->input;
		$id 	= $input->getInt('id',0);
		
		$query->clear()->select($db->qn('params'))->from($db->qn('#__modules'))->where($db->qn('id').' = '.$id);
		$db->setQuery($query);
		$moduleParams = $db->loadResult();
		
		$registry = new JRegistry();
		$registry->loadString($moduleParams);

		//code sent with captcha 
		$captcha 		 		= $input->getString('captcha'.$id);
		//code sent with recaptcha 
		$recaptcha_challenge	= $input->getString('recaptcha_challenge_field');
		$recaptcha_response 	= $input->getString('recaptcha_response_field');

		//check to see if capthca enabled in the module configuration params
		$captcha_enabled = $registry->get('captcha_enable');
		$use_captcha 	 = $captcha_enabled != 0 ? true : false;

		$captcha_response = false;

		if (($use_captcha && $captcha_enabled) || (!$use_captcha)) {
			if(!$use_captcha) $captcha_response = true;

			if ($captcha_enabled == 1) {
				$captcha_image = new RSMJSecurImage();
				$captcha_image->mid = $id;
				$valid = $captcha_image->check($captcha);

				if (!$valid) {
					JError::raiseNotice(500, JText::_('RSM_CAPTCHA_ERROR'));
					$captcha_response = false;
				} else $captcha_response = true;
			} elseif ($captcha_enabled == 2) {
				$privatekey = $registry->get('recaptcha_private_key');
				$response 	= RSMReCAPTCHA::checkAnswer($privatekey, @$_SERVER['REMOTE_ADDR'], $recaptcha_challenge, $recaptcha_response);

				if ($response === false || !$response->is_valid) {
					JError::raiseNotice(500, JText::_('RSM_CAPTCHA_ERROR'));
					$captcha_response = false;
				} else $captcha_response = true;
			}
		}
		
		echo (int) $captcha_response;
		exit();
	}
	
	public function sendunsubscribelink() {
		$db 		= JFactory::getDbo();
		$query		= $db->getQuery(true);
		$input		= JFactory::getApplication()->input;
		$email		= $input->getString('unsubscriber_email', '');
		$lists		= '';
		$Itemid		= $input->getInt('Itemid',0);
		$Itemid		= $Itemid ? '&Itemid='.$Itemid : '';
		$unsubscribe= rsmailHelper::getMessage('unsubscribelink');

		// Get subscriber
		$query->clear()
			->select($db->qn('IdSubscriber'))->select($db->qn('SubscriberEmail'))
			->from($db->qn('#__rsmail_subscribers'))
			->where($db->qn('SubscriberEmail').' = '.$db->q($email))
			->where($db->qn('published').' = 1');
		
		$db->setQuery($query,0,1);
		$subscriber = $db->loadObject();

		if(empty($subscriber)) 
			return $this->setRedirect('index.php?option=com_rsmail&view=unsubscribe' , JText::_('RSM_UNSUBSCRIBE_EMAIL_NOT_FOUND_ERR')); 

		// Generate unsubscribe link
		$secret = md5($subscriber->SubscriberEmail.$subscriber->IdSubscriber);
		$unsubscribelink = '<a href="'.JURI::root().'index.php?option=com_rsmail&view=unsubscribe&vid='.$secret.'&IdSession=9999999'.$Itemid.'">'.JText::_('RSM_UNSUBSCRIBE_LINK').'</a>';
		
		// Get user lists
		$query->clear()
			->select($db->qn('l.ListName'))
			->from($db->qn('#__rsmail_lists','l'))
			->join('LEFT',$db->qn('#__rsmail_subscribers','s').' ON '.$db->qn('s.IdList').' = '.$db->qn('l.IdList'))
			->where($db->qn('SubscriberEmail').' = '.$db->q($email))
			->where($db->qn('published').' = 1');
		
		$db->setQuery($query);
		if ($subscriber_lists = $db->loadColumn()) {
			$lists .= '<ul>';
			foreach($subscriber_lists as $list) 
				$lists .= '<li>'.$list.'</li>';
			$lists .= '</ul>';
		}
		
		// Get the site name
		$sitename = JFactory::getConfig()->get('sitename');
		$replace = array('{unsubscribelink}', '{lists}', '{site}');
		$with 	 = array($unsubscribelink, $lists, $sitename);

		$subject = str_replace($replace, $with, $unsubscribe->subject);
		$body	 = str_replace($replace, $with, $unsubscribe->text);

		$mailer	= JFactory::getMailer();
		if ($mailer->sendMail($unsubscribe->from, $unsubscribe->fromname, $email, $subject, $body, $unsubscribe->mode))
			return $this->setRedirect('index.php',JText::_('RSM_UNSUBSCRIBE_LINK_EMAIL_MESSAGE_OK'));
		else 
			return $this->setRedirect('index.php',JText::_('RSM_UNSUBSCRIBE_LINK_EMAIL_MESSAGE_NOT_SENT'));
	}
	
	/*
	*  Get the fields needed for the module 
	*
	*/
	public function getfields() {
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$id		= JFactory::getApplication()->input->getInt('cid',0);
		$fields = JFactory::getApplication()->input->getString('fields','');
		
		if ($fields != '') {
			$fields = explode(',',$fields);
			
			if (!empty($fields)) {
				JArrayHelper::toInteger($fields);
			
				foreach($fields as $field) {
					$query->clear()
						->select($db->qn('FieldName'))
						->from($db->qn('#__rsmail_list_fields'))
						->where($db->qn('IdList').' = '.$id)
						->where($db->qn('IdListFields').' = '.(int) $field);
					
					$db->setQuery($query);
					$fieldname = $db->loadResult();
					
					if (empty($fieldname))
						continue;
					
					echo '<label for="'.$fieldname.'">'.$fieldname.'</label><input type="text" name="FieldName['.$fieldname.']" id="'.$fieldname.'" value="" />';
				}
			}
		}
		exit();
	}
	
	/*
	*  Download attachements
	*/
	public function download() {
		$db 	= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$user	= JFactory::getUser();
		$input	= JFactory::getApplication()->input;
		$id		= $input->getInt('id',0);
		$code	= $input->getString('code','');
		
		// Get details
		$query->clear()
			->select($db->qn('IdMessage'))->select($db->qn('FileName'))
			->from($db->qn('#__rsmail_files'))
			->where($db->qn('IdFile').' = '.(int) $id);
		$db->setQuery($query);
		$file = $db->loadObject();
		
		if (empty($file)) 
			return $this->setRedirect('index.php');
		
		$query->clear()
			->select($db->qn('s.IdMessage'))
			->from($db->qn('#__rsmail_sessions','s'))
			->join('LEFT',$db->qn('#__rsmail_subscribers','sb').' ON '.$db->qn('sb.IdList').' IN ('.$db->qn('s.Lists').')');
		
		if (!empty($code))
			$query->where('MD5(CONCAT('.$db->qn('s.IdMessage').','.$db->qn('s.IdSession').','.$db->qn('sb.SubscriberEmail').')) = '.$db->q($code));
		else 
			$query->where($db->qn('sb.SubscriberEmail').' = '.$db->q($user->get('email')));
		
		$db->setQuery($query);
		$messages = $db->loadColumn();
		
		if (in_array($file->IdMessage,$messages)) {
			$fullpath = JPATH_ADMINISTRATOR.'/components/com_rsmail/files/'.$file->FileName;
			@ob_end_clean();
			$filename = basename($fullpath);
			header("Cache-Control: public, must-revalidate");
			header('Cache-Control: pre-check=0, post-check=0, max-age=0');
			
			if (!preg_match('#MSIE#', $_SERVER['HTTP_USER_AGENT']))
				header("Pragma: no-cache");
			
			header("Expires: 0"); 
			header("Content-Description: File Transfer");
			header("Expires: Sat, 01 Jan 2000 01:00:00 GMT");
			if (preg_match('#Opera#', $_SERVER['HTTP_USER_AGENT']))
				header("Content-Type: application/octetstream");
			else 
				header("Content-Type: application/octet-stream");

			header("Content-Length: ".(string) filesize($fullpath));
			header('Content-Disposition: attachment; filename="'.$filename.'"');
			header("Content-Transfer-Encoding: binary\n");
			rsmailHelper::readfile($fullpath);
			exit();
		} else {
			return $this->setRedirect('index.php');
		}
	}
	
	/*
	*	Function cron() 
	*	Set cron jobs for sending out emails
	*/
	public function cron() {
		jimport('joomla.mail.helper');

		$db 		= JFactory::getDbo();
		$query		= $db->getQuery(true);
		$date 		= JFactory::getDate();
		$config		= rsmailHelper::getConfig();

		// Set bounce email
		$bounce_email = empty($config->bounce_email) ? null : $config->bounce_email;
		
		// Can we run the cron ?
		if ($config->cron_interval_last_check + ($config->cron_interval_check * 60) > $date->toUnix())
			return;

		$query->clear()
			->update($db->qn('#__rsmail_config'))
			->set($db->qn('ConfigValue').' = '.$db->q($date->toUnix()))
			->where($db->qn('ConfigName').' = '.$db->q('cron_interval_last_check'));
		
		$db->setQuery($query);
		$db->execute();

		// Total number of emails to send in the selected period of time
		$max_emails_to_send = $config->cron_emails;
		
		// Total number of emails to be sent in the check interval
		$minutes = $config->cron_period == 0 ? 60 : 1440;
		$number_of_emails_per_batch = ceil($max_emails_to_send/($minutes / $config->cron_interval_check));
		
		// Get sessions
		$query->clear()
			->select('*')
			->from($db->qn('#__rsmail_sessions'))
			->where($db->qn('Status').' <> 2')
			->where($db->qn('Delivery').' = 1')
			->where($db->qn('paused').' = 0')
			->where($db->qn('DeliverDate').' <= '.$db->q($date->toSql()));
		
		$db->setQuery($query);
		$sessions = $db->loadObjectList();
		
		// If there are no session exit cron
		if (empty($sessions)) return;
		
		// Number of emails to be sent in a session
		$number_of_email_per_session = ceil($number_of_emails_per_batch / count($sessions));
		
		if ($config->cron_period == 0) {
			// 1 hour
			$start = gmmktime(gmdate('H'),0,0,gmdate('n'),gmdate('j'),gmdate('Y'));
			$end = gmmktime(gmdate('H'),59,59,gmdate('n'),gmdate('j'),gmdate('Y'));
		} else {
			// 1 day
			$start = gmmktime(0,0,0,gmdate('n'),gmdate('j'),gmdate('Y'));
			$end = gmmktime(23,59,59,gmdate('n'),gmdate('j'),gmdate('Y'));
		}
		
		$query->clear()
			->select('SUM('.$db->qn('emails').')')
			->from($db->qn('#__rsmail_log'))
			->where($db->qn('date').' >= '.$db->q(JFactory::getDate($start)->toSql()))
			->where($db->qn('date').' <= '.$db->q(JFactory::getDate($end)->toSql()));
		
		$db->setQuery($query);
		$sent_emails = $db->loadResult();
		if ($sent_emails >= $max_emails_to_send)
			return;
		
		$batch_counter = 0;

		//parse sessions
		foreach($sessions as $session) {
			
			// We should change the status of the current session to started
			$query->clear()
				->update($db->qn('#__rsmail_sessions'))
				->set($db->qn('Status').' = 1')
				->where($db->qn('IdSession').' = '.(int) $session->IdSession);
			
			$db->setQuery($query);
			$db->execute();

			$query->clear()
				->select('COUNT('.$db->qn('Id').')')
				->from($db->qn('#__rsmail_cron_logs'))
				->where($db->qn('IdSession').' = '.(int) $session->IdSession);
				
			$db->setQuery($query);
			$check_session_cron_log = $db->loadResult();

			// Insert session into cron logs if it does not exist
			if (!$check_session_cron_log){
				$query->clear()
					->insert($db->qn('#__rsmail_cron_logs'))
					->set($db->qn('IdSession').' = '.(int) $session->IdSession)
					->set($db->qn('DateAccessed').' = '.$db->q($date->toSql()))
					->set($db->qn('TotalSentEmails').' = 0');
				
				$db->setQuery($query);
				$db->execute();
			}

			// Get message sent counter
			$query->clear()
				->select($db->qn('MessageCounterSent'))
				->from($db->qn('#__rsmail_sessions'))
				->where($db->qn('IdSession').' = '.(int) $session->IdSession);
			
			$db->setQuery($query);
			$counter = $db->loadResult();

			// Get message details
			$query->clear()
				->select('*')
				->from($db->qn('#__rsmail_messages'))
				->where($db->qn('IdMessage').' = '.(int) $session->IdMessage);
			
			$db->setQuery($query);
			$message = $db->loadObject();

			// Message details
			$from			= $message->MessageSenderEmail;
			$fromName		= $message->MessageSenderName;
			$mode			= $message->MessageType;
			$replyto		= $message->MessageReplyTo;
			$replytoname	= $message->MessageReplyToName;
			$subject		= $message->MessageSubject;
			$textonly		= ($mode == 1) ? $message->MessageBodyNoHTML : '';
			
			// Get attachments
			$attachments = rsmailHelper::attachments($session->IdMessage,0);
			
			// Prepare the body to be html valid
			if(strpos($message->MessageBody,strtolower("<html")) !== FALSE)
				$body = $message->MessageBody;
			else 
				$body = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html><body>'.$message->MessageBody;
			
			if($mode == 0)
				$body = $message->MessageBody;
			
			// Get embeded files
			$query->clear()
				->select('COUNT('.$db->qn('IdFile').')')
				->from($db->qn('#__rsmail_files'))
				->where($db->qn('Embeded').' = 1')
				->where($db->qn('IdMessage').' = '.(int) $session->IdMessage);
			
			$db->setQuery($query);
			$isEmbedOn = $db->loadResult();
			
			$embeds = rsmailHelper::cron_embed_files($session->IdMessage,$isEmbedOn);
			
			// Set the subscribers limit
			$slimit = $number_of_email_per_session;

			// if the destination lists are filtered
			if (!empty($session->IdFilter)) {
				$query->clear()
					->select($db->qn('Filters'))
					->from($db->qn('#__rsmail_session_filters'))
					->where($db->qn('IdFilter').' = '.(int) $session->IdFilter);
				
				$db->setQuery($query);
				$filters = $db->loadResult();
				$filters = unserialize($filters);
				
				if(isset($filters['filters'])) {
					//get the emails if the lists are filtered
					if(!empty($filters['filters']['lists'])) {
						// if all filtered results are selected
						$where				= rsmailHelper::getFilterCondition($filters['filters']);
						$condition_length	= isset($filters['filters']['condition']) ? strlen($filters['filters']['condition']) : 2;
						
						$mquery = 'SELECT COUNT(DISTINCT '.$db->qn('s.SubscriberEmail').') FROM '.$db->qn('#__rsmail_subscribers','s').' WHERE '.$db->qn('s.IdSubscriber').' <= '.(int) $session->IdMaxSubscriber;
						if (!empty($where) && count($filters['filters']['published']) > 1)
							$mquery .= ' AND ('.substr($where, 0, -$condition_length).')';
						else 
							$mquery .= ' AND '.substr($where, 0, -$condition_length);
						
						$db->setQuery($mquery);
						$maxmails = $db->loadResult();
						
						// Update the maximum emails
						$query->clear()
							->update($db->qn('#__rsmail_sessions'))
							->set($db->qn('MaxEmails').' = '.(int) $maxmails)
							->where($db->qn('IdSession').' = '.(int) $session->IdSession);
						
						$db->setQuery($query);
						$db->execute();
						
						$equery = 'SELECT '.$db->qn('s.SubscriberEmail').', '.$db->qn('s.IdList').', '.$db->qn('s.IdSubscriber').' FROM '.$db->qn('#__rsmail_subscribers','s').' LEFT JOIN '.$db->qn('#__rsmail_subscriber_details','sd').' ON '.$db->qn('s.IdSubscriber').' = '.$db->qn('sd.IdSubscriber').' WHERE '.$db->qn('s.IdSubscriber').' <= '.(int) $session->IdMaxSubscriber.' ';
							
						if (!empty($where) && count($filters['filters']['published']) > 1)
							$equery .= ' AND ('.substr($where, 0, -$condition_length).')';
						else $equery .= ' AND '.substr($where, 0, -$condition_length);
						
						$equery .= ' GROUP BY '.$db->qn('s.SubscriberEmail').' ORDER BY '.$db->qn('s.IdSubscriber').' ASC';
					} else {
						// If filters are empty select all results 
						$equery = 'SELECT DISTINCT '.$db->qn('SubscriberEmail').', '.$db->qn('IdList').', '.$db->qn('IdSubscriber').' FROM '.$db->qn('#__rsmail_subscribers').' WHERE '.$db->qn('IdSubscriber').' <= '.(int) $session->IdMaxSubscriber.' AND '.$db->qn('published').' = 1 GROUP BY '.$db->qn('SubscriberEmail').' ORDER BY '.$db->qn('IdSubscriber').' ASC';
					}
				} else {
					$equery = 'SELECT DISTINCT '.$db->qn('SubscriberEmail').', '.$db->qn('IdList').', '.$db->qn('IdSubscriber').' FROM '.$db->qn('#__rsmail_subscribers').' WHERE '.$db->qn('IdSubscriber').' IN ('.implode(',',$filters['cids']).') AND '.$db->qn('IdSubscriber').' <= '.(int) $session->IdMaxSubscriber.' AND '.$db->qn('published').' = 1 GROUP BY '.$db->qn('SubscriberEmail').' ORDER BY '.$db->qn('IdSubscriber').' ASC';
				}
			} else {
				$query->clear()	
					->select('COUNT(DISTINCT '.$db->qn('SubscriberEmail').')')
					->from($db->qn('#__rsmail_subscribers'))
					->where($db->qn('IdList').' IN ('.$session->Lists.')')
					->where($db->qn('IdSubscriber').' <= '.(int) $session->IdMaxSubscriber)
					->where($db->qn('published').' = 1');
				
				$db->setQuery($query);
				$maxmails = $db->loadResult();

				// Update the maximum emails
				$query->clear()
					->update($db->qn('#__rsmail_sessions'))
					->set($db->qn('MaxEmails').' = '.(int) $maxmails)
					->where($db->qn('IdSession').' = '.(int) $session->IdSession);
					
				$db->setQuery($query);
				$db->execute();
				
				$equery = 'SELECT DISTINCT '.$db->qn('SubscriberEmail').', '.$db->qn('IdList').', '.$db->qn('IdSubscriber').' FROM '.$db->qn('#__rsmail_subscribers').' WHERE '.$db->qn('IdList').' IN ('.$session->Lists.') AND '.$db->qn('IdSubscriber').' <= '.(int) $session->IdMaxSubscriber.' AND '.$db->qn('published').' = 1 GROUP BY '.$db->qn('SubscriberEmail').' ORDER BY '.$db->qn('IdSubscriber').' ASC';
			}

			// Get subscribers
			$db->setQuery($equery,$session->Position,$slimit);
			$emails = $db->loadObjectList();

			//load the session details
			$replacer = array();
			$query->clear()
				->select('*')
				->from($db->qn('#__rsmail_session_details'))
				->where($db->qn('IdSession').' = '.(int) $session->IdSession)
				->where($db->qn('IdList').' IN ('.$session->Lists.')');
			
			$db->setQuery($query);
			if ($sessiondetailsobj = $db->loadObjectList())
				foreach($sessiondetailsobj as $sd)
					$replacer[$sd->IdList][$sd->ToSearch] = $sd->ToReplace;
			
			// Send emails
			foreach($emails as $email) {
				$to = $email->SubscriberEmail;
				$to = trim($to);
				
				if (!JMailHelper::isEmailAddress($to)) {
					$db->setQuery('INSERT INTO '.$db->qn('#__rsmail_errors').' SET '.$db->qn('IdSession').' = '.(int) $session->IdSession.', '.$db->qn('IdList').' = '.(int) $email->IdList.', '.$db->qn('message').' = '.$db->q(JText::_('RSM_INVALID_EMAIL')).', '.$db->qn('IdSubscriber').' = '.(int) $email->IdSubscriber.'');
					$db->execute();
					continue;
				}
				
				// Load subscriber details
				$db->setQuery('SELECT * FROM '.$db->qn('#__rsmail_subscriber_details').' WHERE '.$db->qn('IdList').' = '.(int) $email->IdList.' AND '.$db->qn('IdSubscriber').' = '.(int) $email->IdSubscriber.'');
				$SubscriberDetails = $db->loadObjectList();
				
				$array_replace = array();
				$array_search = array();
				if(!empty($replacer))
				foreach($replacer[$email->IdList] as $search=>$replace) {
					// Search for the replace in subscriber details
					$replace_with = '';
					if($replace == JText::_('RSM_DO_NOT_REPLACE')) $replace_with = '{'.$search.'}';
					foreach($SubscriberDetails as $Detail) {
						if($replace == $Detail->FieldName) $replace_with = $Detail->FieldValue;
						if($replace == JText::_('RSM_IGNORE')) $replace_with = '';
						if($replace == JText::_('RSM_EMAIL')) $replace_with = $email->SubscriberEmail;
					}	
					$array_search[] = '{'.$search.'}';
					$array_replace[] = $replace_with;
				}
				
				// Replace the placeholders
				$send_body     = str_replace($array_search,$array_replace,$body);
				$send_subject  = str_replace($array_search,$array_replace,$subject);
				$send_textonly = str_replace($array_search,$array_replace,$textonly); 
				
				// Replace template placeholders
				$send_body		= rsmailHelper::cron_replace_articles($send_body,$session->IdMessage,$email->IdSubscriber,$session->IdSession,$to,$email->IdList);
				$send_textonly	= rsmailHelper::cron_replace_articles($send_textonly,$session->IdMessage,$email->IdSubscriber,$session->IdSession,$to,$email->IdList);
				
				// Replace url`s
				$send_body		= rsmailHelper::cron_url_replacement($send_body,$session->IdSession,$email->IdSubscriber);
				
				// Replace the embeded placeholders
				$send_body		= rsmailHelper::cron_replace_embeded_placeholders($send_body,$session->IdMessage,$isEmbedOn);
				
				// Check for opened email
				if ($mode == 1)
					$send_body .= '<img src="'.JURI::root().'index.php?option=com_rsmail&task=openmail&tmpl=component&cid='.$session->IdSession.'&IdSubscriber='.$email->IdSubscriber.'" border="0" height="1" width="1" />'; 
				
				if(strpos($message->MessageBody,strtolower("<html")) === FALSE && $mode == 1)
					$send_body .= '</body></html>';
				
				// Send email
				$mailok = rsmailHelper::sendMail($from, $fromName, $to, $send_subject, $send_body, $mode, null, null, $attachments, $replyto, $replytoname, $send_textonly, $isEmbedOn, $embeds, $bounce_email, $session->IdSession);
				
				// Update position
				$query->clear()
					->update($db->qn('#__rsmail_sessions'))
					->set($db->qn('Position').' = '.$db->qn('Position').' + 1')
					->where($db->qn('IdSession').' = '.(int) $session->IdSession);
				
				$db->setQuery($query);
				$db->execute();
				
				if(!is_object($mailok) && $mailok == true) {
					$batch_counter 	+= 1;
					$counter 		= $counter +1;

					// Update TotalSentEmails if the message was sent
					$query->clear()
						->update($db->qn('#__rsmail_cron_logs'))
						->set($db->qn('TotalSentEmails').' = '.$db->qn('TotalSentEmails').' + 1')
						->where($db->qn('IdSession').' = '.(int) $session->IdSession);
					
					$db->setQuery($query);
					$db->execute();

					// Inser email into the logs table
					$query->clear()
						->insert($db->qn('#__rsmail_cron_logs_emails'))
						->set($db->qn('IdSession').' = '.(int) $session->IdSession)
						->set($db->qn('SubscriberEmail').' = '.$db->q($to))
						->set($db->qn('DateSent').' = '.$db->q($date->toSql()));
					
					$db->setQuery($query);
					$db->execute();
				}
				
				// Add error
				if (is_object($mailok)) {
					$query->clear()
						->insert($db->qn('#__rsmail_errors'))
						->set($db->qn('IdSession').' = '.(int) $session->IdSession)
						->set($db->qn('IdList').' = '.(int) $email->IdList)
						->set($db->qn('message').' = '.$db->q($mailok->getMessage()))
						->set($db->qn('IdSubscriber').' = '.(int) $email->IdSubscriber);
					
					$db->setQuery($query);
					$db->execute();
				}
				
				$query->clear()
					->update($db->qn('#__rsmail_sessions'))
					->set($db->qn('MessageCounterSent').' = '.(int) $counter)
					->where($db->qn('IdSession').' = '.(int) $session->IdSession);
				
				$db->setQuery($query);
				$db->execute();
			}
			
			// Get Position and MaxEmails
			$query->clear()
				->select($db->qn('Position'))->select($db->qn('MaxEmails'))
				->from($db->qn('#__rsmail_sessions'))
				->where($db->qn('IdSession').' = '.(int) $session->IdSession);
			
			$db->setQuery($query);
			$sessionDetails = $db->loadObject();
			
			$position	= $sessionDetails->Position;
			$maxemails	= $sessionDetails->MaxEmails;
			
			// If all the emails were sent then we close the session
			if ($position == $maxemails && $position != 0 && $maxemails != 0) {
				$query->clear()
					->update($db->qn('#__rsmail_sessions'))
					->set($db->qn('Status').' = 2')
					->where($db->qn('IdSession').' = '.(int) $session->IdSession);
				
				$db->setQuery($query);
				$db->execute();
				
				$query->clear()->delete()->from($db->qn('#__rsmail_log'))->where($db->qn('IdSession').' = '.(int) $session->IdSession);
				$db->setQuery($query);
				$db->execute();
			} else {
				$query->clear()
					->insert($db->qn('#__rsmail_log'))
					->set($db->qn('IdSession').' = '.(int) $session->IdSession)
					->set($db->qn('date').' = '.$db->q($date->toSql()))
					->set($db->qn('emails').' = '.(int) $batch_counter);
				
				$db->setQuery($query);
				$db->execute();
			}
		}
	}

	/*
	*	Function autoresponders()
	*	Cron for autoresponders
	*/
	public function autoresponders() {
		$db 		= JFactory::getDbo();
		$date 		= JFactory::getDate();
		$config		= rsmailHelper::getConfig();
		$curr_time	= $date->toUnix();
		
		//Set the counter
		$counter = $config->ar_counter ? $config->ar_counter : 0;
		
		//1. get all autoresponders
		$db->setQuery('SELECT '.$db->qn('IdAutoresponder').', '.$db->qn('AutoresponderType').', '.$db->qn('AutoresponderDate').', '.$db->qn('IdLists').', '.$db->qn('DateCreated').' FROM '.$db->qn('#__rsmail_autoresponders').'');
		$autoresponders = $db->loadObjectList();
		
		if (empty($autoresponders))
			return;
		if($curr_time > $config->cron_date) {
			foreach($autoresponders as $autoresponder) {
				//2. get all autoresponder messages
				$db->setQuery('SELECT '.$db->qn('IdAutoresponderMessage').', '.$db->qn('IdMessage').', '.$db->qn('DelayPeriod').', '.$db->qn('ordering').' FROM '.$db->qn('#__rsmail_ar_messages').' WHERE '.$db->qn('IdAutoresponder').' = '.(int) $autoresponder->IdAutoresponder.' ORDER BY '.$db->qn('ordering').' ASC ');
				$ar_messages = $db->loadObjectList();
				
				//no messages => exit 
				if(empty($ar_messages)) continue;
				//3. while counter < $config->cron_emails , get next subscriber
				while($counter < $config->cron_emails) {
					if($autoresponder->AutoresponderType == 0) $where = ' AND '.$db->qn('DateSubscribed').' > '.$db->q($autoresponder->DateCreated).' '; else $where = '';
					if($autoresponder->AutoresponderType == 0)
						$and = ' AND DATE_ADD('.$db->q($autoresponder->DateCreated).', INTERVAL '.$db->escape($ar_messages[0]->DelayPeriod).') < '.$db->q($date->toSql()).' ';
					else 
						$and = ' AND DATE_ADD('.$db->q($autoresponder->AutoresponderDate).', INTERVAL '.$db->escape($ar_messages[0]->DelayPeriod).') < '.$db->q($date->toSql()).' ';
					
					$and .= ' AND DATE_ADD('.$db->qn('DateSubscribed').', INTERVAL '.$db->escape($ar_messages[0]->DelayPeriod).') < '.$db->q($date->toSql()).' ';
					
					//a. select the subscriber
					$db->setQuery('SELECT DISTINCT '.$db->qn('SubscriberEmail').', '.$db->qn('IdList').', '.$db->qn('IdSubscriber').', '.$db->qn('DateSubscribed').' FROM '.$db->qn('#__rsmail_subscribers').' WHERE '.$db->qn('IdList').' IN ('.$db->q($autoresponder->IdLists).') AND '.$db->qn('published').' = 1 '.$where.$and.' AND '.$db->qn('IdSubscriber').' NOT IN (SELECT '.$db->qn('IdSubscriber').' FROM '.$db->qn('#__rsmail_ar_details').' WHERE '.$db->qn('IdAutoresponder').' = '.$autoresponder->IdAutoresponder.' ) LIMIT 1');
					$subscriber = $db->loadObject();
					
					//no subscriber -> exit;
					if(empty($subscriber)) break;
					
					if($autoresponder->AutoresponderType == 1) {
						//send the message only if the user has subscribed before the message sentDate, but also keep track of the SentDate for the next message
						$subscriberDate = JFactory::getDate($subscriber->DateSubscribed)->toUnix();
						$arDate			= JFactory::getDate($autoresponder->AutoresponderDate);
						$arDate->modify('+'.$ar_messages[0]->DelayPeriod);
						
						if($subscriberDate < $arDate->toUnix()) {
							rsmailHelper::mailHelper($ar_messages[0],$subscriber);
						}
					} else {
						rsmailHelper::mailHelper($ar_messages[0],$subscriber);
					}

					// update the db
					$db->setQuery('INSERT INTO '.$db->qn('#__rsmail_ar_details').' SET '.$db->qn('IdAutoresponderMessage').' = '.(int) $ar_messages[0]->IdAutoresponderMessage.', '.$db->qn('IdAutoresponder').' = '.(int) $autoresponder->IdAutoresponder.', '.$db->qn('IdSubscriber').' = '.(int) $subscriber->IdSubscriber.', '.$db->qn('SentDate').' = '.$db->q($date->toSql()));
					$db->execute();

					//increment the counter
					$counter++;
				}
				
				if($counter < $config->cron_emails)
				foreach($ar_messages as $i=>$message) {
					if(isset($ar_messages[$i+1])) { 
						//get subscribers
						$db->setQuery('SELECT DISTINCT('.$db->qn('s.SubscriberEmail').'), '.$db->qn('ard.IdSubscriber').', '.$db->qn('s.IdList').', '.$db->qn('s.DateSubscribed').', '.$db->qn('ard.SentDate').' FROM '.$db->qn('#__rsmail_ar_details','ard').' LEFT JOIN '.$db->qn('#__rsmail_subscribers','s').' ON '.$db->qn('s.IdSubscriber').' = '.$db->qn('ard.IdSubscriber').' WHERE '.$db->qn('ard.IdAutoresponderMessage').' = '.(int) $message->IdAutoresponderMessage.' AND DATE_ADD('.$db->qn('ard.SentDate').', INTERVAL '.$db->escape($ar_messages[$i+1]->DelayPeriod).') < '.$db->q($date->toSql()).' LIMIT '.$config->cron_emails);
						$subscribers = $db->loadObjectList();
						
						if(empty($subscribers)) continue;
						
						foreach($subscribers as $subscriber) {
							if ($counter < $config->cron_emails) {
								//send the message only if the user has subscribed before the message date, but also keep track of the SentDate for the next message
								$subscriberDate = JFactory::getDate($subscriber->DateSubscribed)->toUnix();
								$sentDate = JFactory::getDate($subscriber->SentDate);
								$sentDate->modify('+'.$ar_messages[$i+1]->DelayPeriod);
								
								if ($subscriberDate < $sentDate->toUnix()) {
									rsmailHelper::mailHelper($ar_messages[$i+1],$subscriber);
									
									//update the ar_details with the current IdAutoresponderMessage and time
									$db->setQuery('UPDATE '.$db->qn('#__rsmail_ar_details').' SET '.$db->qn('IdAutoresponderMessage').' = '.(int) $ar_messages[$i+1]->IdAutoresponderMessage.', '.$db->qn('SentDate').' = '.$db->q($date->toSql()).' WHERE '.$db->qn('IdSubscriber').' = '.(int) $subscriber->IdSubscriber);
									$db->execute();
								}
							}
							
							//increment the counter
							$counter++;
						}
					}
				}
			}

			//update counter
			if ($counter >= $config->cron_emails) {
				$db->setQuery('UPDATE '.$db->qn('#__rsmail_config').' SET '.$db->qn('ConfigValue').' = 0 WHERE '.$db->qn('ConfigName').' = '.$db->q('ar_counter').' ');
				$db->execute();
			} else {
				$db->setQuery('UPDATE '.$db->qn('#__rsmail_config').' SET '.$db->qn('ConfigValue').' = '.(int) $counter.' WHERE '.$db->qn('ConfigName').' = '.$db->q('ar_counter').' ');
				$db->execute();
			}
			
			if ($counter >= $config->cron_emails) {
				$extratime = ($config->cron_period == 0) ? 3600 : 86400;
				if($config->cron_date == 0)
					$db->setQuery('UPDATE '.$db->qn('#__rsmail_config').' SET '.$db->qn('ConfigValue').' = '.$db->q($curr_time).' WHERE '.$db->qn('ConfigName').' = '.$db->q('cron_date').' ');
				else
					$db->setQuery('UPDATE '.$db->qn('#__rsmail_config').' SET '.$db->qn('ConfigValue').' = '.$db->qn('ConfigValue').' + '.$db->q($extratime).' WHERE '.$db->qn('ConfigName').' = '.$db->q('cron_date').' ');
				$db->execute();
			}
		}
	}
}