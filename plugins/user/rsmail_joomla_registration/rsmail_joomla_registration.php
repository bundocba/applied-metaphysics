<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.plugin.plugin' );

class plgUserRsmail_Joomla_Registration extends JPlugin
{
	
	public function __construct( &$subject, $config ) {
		parent::__construct( $subject, $config );
	}

	/**
	 * Utility method to act on a user after it has been saved.
	 *
	 * This method sends a registration email to new users created in the backend.
	 *
	 * @param   array  $user		Holds the new user data.
	 * @param   boolean		$isnew		True if a new user is stored.
	 * @param   boolean		$success	True if user was succesfully stored in the database.
	 * @param   string  $msg		Message.
	 *
	 * @return  void
	 * @since   1.6
	 */
	public function onUserAfterSave($user, $isnew, $success, $msg) {
		if (!$this->canRun()) {
			return;
		}
		
		$db 		= JFactory::getDbo();
		$query		= $db->getQuery(true);
		$lang 		= JFactory::getLanguage();
		$config		= rsmailHelper::getConfig();
		$now		= JFactory::getDate()->toSql();
		$checkbox	= JFactory::getApplication()->input->getInt('rsm_subscribe',0);
		
		if (!$config->enable_jur)
			return;
		
		$args 				= array();
		$args['username']	= $user['username'];
		$args['email'] 		= $user['email'];
		$args['fullname']	= $user['name'];
		$args['password']	= $user['password'];
		$args['id']			= $user['id'];
		
		$confirmation = rsmailHelper::getMessage('confirmation');
		
		// New user
		if ($isnew) {
			// If the auto subscribe option is enabled
			// Check if the list is configured
			if (!empty($config->jur_list)) {
				// If checkbox is displayed and the user checked OR checkbox is not displayed
				if(($config->jur_auto == 1 && $checkbox) || $config->jur_auto == 0) {
					// Set the subscribers status
					$published = 0;
					$published = ($config->jur_auto == 0 && $confirmation->enable == 0) ? 1 : 0;
					
					// Check if the user already exists
					$query->clear()
						->select($db->qn('IdSubscriber'))
						->from($db->qn('#__rsmail_subscribers'))
						->where($db->qn('SubscriberEmail').' = '.$db->q($args['email']))
						->where($db->qn('IdList').' = '.(int) $config->jur_list);
					
					$db->setQuery($query);
					$ids = (int) $db->loadResult();
					
					if (!empty($ids))
						return;
					
					$query->clear()
						->insert($db->qn('#__rsmail_subscribers'))
						->set($db->qn('SubscriberEmail').' = '.$db->q($args['email']))
						->set($db->qn('IdList').' = '.(int) $config->jur_list)
						->set($db->qn('DateSubscribed').' = '.$db->q($now))
						->set($db->qn('UserId').' = '.(int) $args['id'])
						->set($db->qn('SubscriberIp').' = '.$db->q($_SERVER['REMOTE_ADDR']))
						->set($db->qn('published').' = '.(int) $published);
					
					$db->setQuery($query);
					$db->execute();
					$subscriberId = $db->insertid();				
					
					$name		= $config->jur_name;
					$username	= $config->jur_username;
					
					if ($name != JText::_('RSM_IGNORE')) {
						$query->clear()
							->insert($db->qn('#__rsmail_subscriber_details'))
							->set($db->qn('IdSubscriber').' = '.$subscriberId)
							->set($db->qn('IdList').' = '.(int) $config->jur_list)
							->set($db->qn('FieldName').' = '.$db->q($name))
							->set($db->qn('FieldValue').' = '.$db->q($args['fullname']));
						
						$db->setQuery($query);
						$db->execute();
					}
					
					if ($username != JText::_('RSM_IGNORE')) {
						$query->clear()
							->insert($db->qn('#__rsmail_subscriber_details'))
							->set($db->qn('IdSubscriber').' = '.$subscriberId)
							->set($db->qn('IdList').' = '.(int) $config->jur_list)
							->set($db->qn('FieldName').' = '.$db->q($username))
							->set($db->qn('FieldValue').' = '.$db->q($args['username']));
						
						$db->setQuery($query);
						$db->execute();
					}
				}
			}
		} else {
			// Check if user is activating his joomla account
			if($user['block'] == '0') {
				$query->clear()
					->select($db->qn('IdSubscriber'))
					->from($db->qn('#__rsmail_subscribers'))
					->where($db->qn('SubscriberEmail').' = '.$db->q($user['email']))
					->where($db->qn('UserId').' = '.$db->q($user['id']))
					->where($db->qn('published').' = 0')
					->where($db->qn('IdList').' = '.(int) $config->jur_list);
				
				$db->setQuery($query);
				$ids = (int) $db->loadResult();
			
				// If subscriber exists and he is unpublished send the confirmation mail
				if (!empty($ids)) {
					// Send the confirmation email
					if (file_exists(JPATH_SITE.'/components/com_rsmail/helpers/actions.php')) {
						require_once JPATH_SITE.'/components/com_rsmail/helpers/actions.php';
						
						$hash   = md5($config->jur_list.$ids.$user['email']);
						$rsmail = new rsmHelper();
						$rsmail->confirmation($config->jur_list, $user['email'], $hash);
					}
				}
			}
		}
	}
	
	protected function canRun() {
		if (file_exists(JPATH_SITE.'/components/com_rsmail/helpers/rsmail.php')) {
			require_once JPATH_SITE.'/components/com_rsmail/helpers/rsmail.php';
			$lang 	= JFactory::getLanguage();
			$lang->load('com_rsmail', JPATH_ADMINISTRATOR);
			$lang->load('com_rsmail', JPATH_SITE);
			
			return true;
		}
		
		return false;
	}
}