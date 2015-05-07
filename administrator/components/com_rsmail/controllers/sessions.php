<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controller');

class rsmailControllerSessions extends JControllerLegacy
{
	public function __construct() {
		parent::__construct();
		
		$this->registerTask('pause', 'croncontrol');
		$this->registerTask('resume', 'croncontrol');
	}
	
	/**
	 *	Method to remove sessions
	 */
	public function delete() {
		$pks	= JFactory::getApplication()->input->get('cid', array(), 'array');
		$model	= $this->getModel('Sessions');
		
		if (!$model->delete($pks)) {
			$this->setMessage($model->getError());
			return $this->setRedirect('index.php?option=com_rsmail&view=sessions');
		}
		
		return $this->setRedirect('index.php?option=com_rsmail&view=sessions', JText::_('RSM_SESSION_DELETED'));
	}
	
	/**
	 *	Method to remove subscribers clicks
	 */
	public function clear() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$id		= JFactory::getApplication()->input->getInt('id',0);
		
		$query->clear()->select($db->qn('IdReport'))->from($db->qn('#__rsmail_reports'))->where($db->qn('IdSession').' = '.$id);
		$db->setQuery($query);
		if ($reports = $db->loadColumn()) {
			foreach($reports as $report) {
				$query->clear()->delete()->from($db->qn('#__rsmail_subscribers_clicks'))->where($db->qn('IdReport').' = '.(int) $report);
				$db->setQuery($query);
				$db->execute();
			}
		}
		
		$this->setRedirect('index.php?option=com_rsmail&view=reports&layout=links&id='.$id,JText::_('RSM_DATA_CLEARED'));
	}
	
	/**
	 *	Method to remove session bounce emails
	 */
	public function clearbounce() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$id		= JFactory::getApplication()->input->getInt('id',0);
		
		$query->clear()->delete()->from($db->qn('#__rsmail_bounce_emails'))->where($db->qn('IdSession').' = '.$id);
		$db->setQuery($query);
		$db->execute();
		
		$this->setRedirect('index.php?option=com_rsmail&view=reports&layout=bounce&id='.$id,JText::_('RSM_BOUNCE_DATA_CLEARED'));
	}
	
	/**
	 *	Method to remove session errors
	 */
	public function clearerrors() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$id		= JFactory::getApplication()->input->getInt('id',0);
		
		$query->clear()->delete()->from($db->qn('#__rsmail_errors'))->where($db->qn('IdSession').' = '.$id);
		$db->setQuery($query);
		$db->execute();
		
		$this->setRedirect('index.php?option=com_rsmail&view=reports&layout=errors&id='.$id,JText::_('RSM_DATA_CLEARED'));
	}
	
	/**
	 *	Method to unsubscribe user
	 */
	public function unsubscribe() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$idu	= JFactory::getApplication()->input->getInt('id',0);
		$sid	= JFactory::getApplication()->input->getInt('sid',0);
		
		// Check to see if the subscriber has already been unsubscribed
		$query->clear()
			->select($db->qn('unsubscribe'))
			->from($db->qn('#__rsmail_errors'))
			->where($db->qn('IdSubscriber').' = '.$idu)
			->where($db->qn('IdSession').' = '.$sid);
		
		$db->setQuery($query);
		$continue = $db->loadResult();
		
		if ($continue) {
			return $this->setRedirect('index.php?option=com_rsmail&view=reports&layout=errors&id='.$sid, JText::_('RSM_SUBSCRIBER_ALREADY_UNSUBSCIBED'));
		}
		
		$query->clear()
			->select($db->qn('IdList'))
			->from($db->qn('#__rsmail_errors'))
			->where($db->qn('IdSubscriber').' = '.$idu)
			->where($db->qn('IdSession').' = '.$sid);
		
		$db->setQuery($query);
		$idlist = (int) $db->loadResult();
		
		// Unsubscribe subscriber
		$query->clear()
			->update($db->qn('#__rsmail_subscribers'))
			->set($db->qn('published').' = 0')
			->where($db->qn('IdSubscriber').' = '.$idu)
			->where($db->qn('IdList').' = '.$idlist);
		
		$db->setQuery($query);
		$db->execute();
		
		// Update session table
		$query->clear()
			->update($db->qn('#__rsmail_sessions'))
			->set($db->qn('UnsubscribeCounter').' = '.$db->qn('UnsubscribeCounter').' + 1')
			->where($db->qn('IdSession').' = '.$sid);
		
		$db->setQuery($query);
		$db->execute();
		
		// Update errors tables
		$query->clear()
			->update($db->qn('#__rsmail_errors'))
			->set($db->qn('unsubscribe').' = 1')
			->where($db->qn('IdSubscriber').' = '.$idu)
			->where($db->qn('IdSession').' = '.$sid);
		
		$db->setQuery($query);
		$db->execute();
		
		$this->setRedirect('index.php?option=com_rsmail&view=reports&layout=errors&id='.$sid,JText::_('RSM_SUBSCRIBER_UNSUBSCIBED'));
	}
	
	/**
	 *	Method to remove user
	 */
	public function deletesubscriber() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$idu	= JFactory::getApplication()->input->getInt('id',0);
		$sid	= JFactory::getApplication()->input->getInt('sid',0);
		
		// Check to see if the subscriber has already been deleted
		$query->clear()
			->select($db->qn('delete'))
			->from($db->qn('#__rsmail_errors'))
			->where($db->qn('IdSubscriber').' = '.$idu)
			->where($db->qn('IdSession').' = '.$sid);
		
		$db->setQuery($query);
		$continue = $db->loadResult();
		
		if ($continue) {
			return $this->setRedirect('index.php?option=com_rsmail&view=reports&layout=errors&id='.$sid);
		}
		
		// Delete subscriber
		$query->clear()
			->delete()
			->from($db->qn('#__rsmail_subscribers'))
			->where($db->qn('IdSubscriber').' = '.$idu);
		
		$db->setQuery($query);
		$db->execute();
		
		$query->clear()
			->delete()
			->from($db->qn('#__rsmail_subscriber_details'))
			->where($db->qn('IdSubscriber').' = '.$idu);
		
		$db->setQuery($query);
		$db->execute();
		
		// Update errors tables
		$query->clear()
			->update($db->qn('#__rsmail_errors'))
			->set($db->qn('delete').' = 1')
			->where($db->qn('IdSubscriber').' = '.$idu)
			->where($db->qn('IdSession').' = '.$sid);
		
		$db->setQuery($query);
		$db->execute();
		
		$this->setRedirect('index.php?option=com_rsmail&view=reports&layout=errors&id='.$sid,JText::_('RSM_SUBSCRIBER_DELETED'));
	}
	
	/**
	 *	Method to resend email
	 */
	public function send() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$id		= JFactory::getApplication()->input->getInt('id',0);
		$array	= array();
		
		// Get details
		$query->clear()
			->select($db->qn('IdSession'))->select($db->qn('IdList'))->select($db->qn('IdSubscriber'))
			->from($db->qn('#__rsmail_errors'))
			->where($db->qn('id').' = '.$id);
		
		$db->setQuery($query);
		$details = $db->loadObject();
		
		// Get message id
		$query->clear()
			->select($db->qn('IdMessage'))
			->from($db->qn('#__rsmail_sessions'))
			->where($db->qn('IdSession').' = '.$details->IdSession);
		
		$db->setQuery($query);
		$idmessage = $db->loadResult();
		
		$query->clear()
			->select($db->qn('SubscriberEmail'))
			->from($db->qn('#__rsmail_subscribers'))
			->where($db->qn('IdSubscriber').' = '.$details->IdSubscriber);
		
		$db->setQuery($query);
		$email = $db->loadResult();
		
		$object						= new stdClass();
		$object->SubscriberEmail	= $email;
		$object->IdList				= $details->IdList;
		$object->IdSubscriber		= $details->IdSubscriber;
		$array[] = $object;
		
		require_once JPATH_ADMINISTRATOR.'/components/com_rsmail/controllers/send.php';
		rsmailControllerSend::send(1,$details->IdSession,$idmessage,$details->IdList ,$array);
	}
	
	/**
	 *	Method to pause/resume cron sessions
	 */
	public function croncontrol() {
		$id		= JFactory::getApplication()->input->getInt('id', 0);
		$data	= array('resume' => 0, 'pause' => 1);
		$task 	= $this->getTask();
		$value	= JArrayHelper::getValue($data, $task, 0, 'int');
		$model	= $this->getModel('Sessions');
		
		$model->croncontrol($id, $value);
		$this->setRedirect('index.php?option=com_rsmail&view=sessions');
	}
}