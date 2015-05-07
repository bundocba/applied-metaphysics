<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controller');

class rsmailControllerSend extends JControllerLegacy
{
	public function __construct() {
		parent::__construct();
	}
	
	public function save() {
		$model = $this->getModel('Send');
		
		if (!$model->save()) {
			$this->setMessage($model->getError());
			$this->setRedirect('index.php?option=com_rsmail&view=messages');
			return false;
		}
		
		$delivery = $model->getState('session.delivery');
		
		if ($delivery)
			return $this->setRedirect('index.php?option=com_rsmail&view=reports&layout=view&id='.$model->getState('session.ids'));
		else 
			return $this->setRedirect('index.php?option=com_rsmail&view=send&layout=send&id='.$model->getState('session.ids'));
	}
	
	public function send($overwrite = 0, $sid = null, $idm = null, $idl = null, $eobj = null) {
		jimport('joomla.mail.helper');
		jimport('joomla.plugin.helper');
		JPluginHelper::importPlugin('rsmail');
		
		$db 		= JFactory::getDBO();
		$app 		= JFactory::getApplication();
		$input		= $app->input;
		$sessionId 	= $input->getInt('IdSession',0);
		$idMessage 	= $input->getInt('IdMessage',0);
		$config		= rsmailHelper::getConfig();
		$model		= $this->getModel('Send');
		
		if (!is_null($sid))  $sessionId = $sid;
		if (!is_null($idm))  $idMessage = $idm;
		if (!is_null($idl))  $lists = $idl;
		if (!is_null($eobj)) $emails = $eobj;

		// Set bounce email address
		$bounce_email = empty($config->bounce_email) ? null : $config->bounce_email;

		if (!$overwrite) {
			// Get position from database
			$db->setQuery('SELECT '.$db->qn('Position').' FROM '.$db->qn('#__rsmail_sessions').' WHERE '.$db->qn('IdSession').' = '.(int) $sessionId);
			$DBposition = $db->loadResult();

			if ($DBposition == 0) {
				$db->setQuery('UPDATE '.$db->qn('#__rsmail_sessions').' SET '.$db->qn('Status').' = 0 WHERE '.$db->qn('IdSession').' = '.(int) $sessionId);
				$db->execute();
			} else {
				$db->setQuery('UPDATE '.$db->qn('#__rsmail_sessions').' SET '.$db->qn('Status').' = 1 WHERE '.$db->qn('IdSession').' = '.(int) $sessionId);
				$db->execute();
			}

			// Set the limit to get the subscribers emails
			$step = ($config->step != 0 || $config->step != '') ? $config->step : 100;

			// Get session lists and MaxEmails
			$db->setQuery('SELECT '.$db->qn('Lists').', '.$db->qn('MaxEmails').' FROM '.$db->qn('#__rsmail_sessions').' WHERE '.$db->qn('IdSession').' = '.(int) $sessionId);
			$sessionDetails = $db->loadObject();
			
			$lists = $sessionDetails->Lists;
			$max = $sessionDetails->MaxEmails;

			// Update session position
			$db->setQuery('UPDATE '.$db->qn('#__rsmail_sessions').' SET '.$db->qn('Position').' = '.(int) ((($DBposition+$step) > $max) ? $max : $DBposition+$step).' WHERE '.$db->qn('IdSession').' = '.(int) $sessionId);
			$db->execute();
		}

		// Get message details
		$db->setQuery('SELECT * FROM '.$db->qn('#__rsmail_messages').' WHERE '.$db->qn('IdMessage').' = '.(int) $idMessage);
		$message = $db->loadObject();

		if (!$overwrite)
		{
			// Get max id and filter id
			$db->setQuery('SELECT '.$db->qn('IdMaxSubscriber').', '.$db->qn('IdFilter').' FROM '.$db->qn('#__rsmail_sessions').' WHERE '.$db->qn('IdSession').' = '.(int) $sessionId);
			$sessionDetails = $db->loadObject();
			
			$maxId = $sessionDetails->IdMaxSubscriber;
			$IdFilter = $sessionDetails->IdFilter;
			
			// Get emails
			if(!empty($IdFilter)) {
				$db->setQuery('SELECT '.$db->qn('Filters').' FROM '.$db->qn('#__rsmail_session_filters').' WHERE '.$db->qn('IdFilter').' = '.(int) $IdFilter);
				$filters = $db->loadResult();
				$filters = unserialize($filters);

				if(isset($filters['filters'])) {
					if(!empty($filters['filters']['lists'])) {
						$where				= $model->getFilterCondition($filters['filters']);
						$condition_length	= isset($filters['filters']['condition']) ? strlen($filters['filters']['condition']) : 2;
						
						$equery = 'SELECT '.$db->qn('s.SubscriberEmail').', '.$db->qn('s.IdList').', '.$db->qn('s.IdSubscriber').' FROM '.$db->qn('#__rsmail_subscribers','s').' LEFT JOIN '.$db->qn('#__rsmail_subscriber_details','sd').' ON '.$db->qn('s.IdSubscriber').' = '.$db->qn('sd.IdSubscriber').' WHERE '.$db->qn('s.IdSubscriber').' <= '.(int) $maxId.' ';
						
						if (!empty($where) && count($filters['filters']['published']) > 1)
							$equery .= ' AND ('.substr($where, 0, -$condition_length).')';
						else $equery .= ' AND '.substr($where, 0, -$condition_length);
						
						$equery .= ' GROUP BY '.$db->qn('s.SubscriberEmail').' ORDER BY '.$db->qn('s.IdSubscriber').' ASC';
					} else {
						$equery = 'SELECT DISTINCT '.$db->qn('SubscriberEmail').', '.$db->qn('IdList').', '.$db->qn('IdSubscriber').' FROM '.$db->qn('#__rsmail_subscribers').' WHERE '.$db->qn('IdSubscriber').' <= '.(int) $maxId.' AND '.$db->qn('published').' = 1 GROUP BY '.$db->qn('SubscriberEmail').' ORDER BY '.$db->qn('IdSubscriber').' ASC';
					}
				} else {
					$equery = 'SELECT DISTINCT '.$db->qn('SubscriberEmail').', '.$db->qn('IdList').', '.$db->qn('IdSubscriber').' FROM '.$db->qn('#__rsmail_subscribers').' WHERE '.$db->qn('IdSubscriber').' IN ('.implode(',',$filters['cids']).') AND '.$db->qn('IdSubscriber').' <= '.(int) $maxId.' AND '.$db->qn('published').' = 1 GROUP BY '.$db->qn('SubscriberEmail').' ORDER BY '.$db->qn('IdSubscriber').' ASC';
				}
			} else {
				$equery = 'SELECT DISTINCT '.$db->qn('SubscriberEmail').', '.$db->qn('IdList').', '.$db->qn('IdSubscriber').' FROM '.$db->qn('#__rsmail_subscribers').' WHERE '.$db->qn('IdList').' IN ('.$lists.') AND '.$db->qn('IdSubscriber').' <= '.(int) $maxId.' AND '.$db->qn('published').' = 1 GROUP BY '.$db->qn('SubscriberEmail').' ORDER BY '.$db->qn('IdSubscriber').' ASC';
			}

			$db->setQuery($equery, $DBposition, $step);
			$emails = $db->loadObjectList();
		}
		
		$from			= $message->MessageSenderEmail;
		$fromName		= $message->MessageSenderName;
		$mode			= $message->MessageType;
		$replyto		= $message->MessageReplyTo;
		$replytoname	= $message->MessageReplyToName;
		$subject		= $message->MessageSubject;
		$textonly		= ($mode == 1) ? $message->MessageBodyNoHTML : '';
		
		// Get attachments
		$attachments	= rsmailHelper::attachments($idMessage, 0);
		$embeds			= null;
		
		//prepare the body to be html valid
		if(strpos($message->MessageBody,strtolower("<html")) !== FALSE)
			$body = $message->MessageBody;
		else 
			$body = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html><body>'.$message->MessageBody;
		
		if($mode == 0) $body = $message->MessageBody;
		
		$replacer = array();
		$db->setQuery('SELECT * FROM '.$db->qn('#__rsmail_session_details').' WHERE '.$db->qn('IdSession').' = '.(int) $sessionId.' AND '.$db->qn('IdList').' IN ('.$lists.')');
		if ($sessiondetailsobj = $db->loadObjectList())
			foreach($sessiondetailsobj as $sd)
				$replacer[$sd->IdList][$sd->ToSearch] = $sd->ToReplace;
		
		$db->setQuery('SELECT '.$db->qn('MessageCounterSent').' FROM '.$db->qn('#__rsmail_sessions').' WHERE '.$db->qn('IdSession').' = '.(int) $sessionId);
		$counter = $db->loadResult();
		
		// Load embeded files
		$db->setQuery('SELECT COUNT('.$db->qn('IdFile').') FROM '.$db->qn('#__rsmail_files').' WHERE '.$db->qn('Embeded').' = 1 AND '.$db->qn('IdMessage').' = '.(int) $idMessage);
		if ($isEmbedOn = $db->loadResult()) {
			$db->setQuery('SELECT '.$db->qn('FileName').' FROM '.$db->qn('#__rsmail_files').' WHERE '.$db->qn('IdMessage').' = '.(int) $idMessage.' AND '.$db->qn('Embeded').' = 1');
			if ($embeds = $db->loadObjectList()) 
				foreach($embeds as $embed)
					$embed->Path = JPATH_ADMINISTRATOR.'/components/com_rsmail/files/'.$embed->FileName;
		}
		
		foreach($emails as $email) {
			$to = $email->SubscriberEmail;
			$to = trim($to);

			if (!JMailHelper::isEmailAddress($to)) {
				$db->setQuery('INSERT INTO '.$db->qn('#__rsmail_errors').' SET '.$db->qn('IdSession').' = '.(int) $sessionId.', '.$db->qn('IdList').' = '.(int) $email->IdList.', '.$db->qn('message').' = '.$db->q(JText::_('RSM_INVALID_EMAIL')).', '.$db->qn('IdSubscriber').' = '.(int) $email->IdSubscriber.'');
				$db->execute();
				continue;
			}
			
			// Load subscriber details
			$db->setQuery('SELECT * FROM '.$db->qn('#__rsmail_subscriber_details').' WHERE '.$db->qn('IdList').' = '.(int) $email->IdList.' AND '.$db->qn('IdSubscriber').' = '.(int) $email->IdSubscriber.'');
			$SubscriberDetails = $db->loadObjectList();

			$array_replace = array();
			$array_search = array();

			if(!empty($replacer))
			foreach($replacer[$email->IdList] as $search => $replace) {
				//search for the replace in subscriber details
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
			
			//replace the placeholders			
			$send_body     = str_replace($array_search,$array_replace,$body);
			$send_subject  = str_replace($array_search,$array_replace,$subject);
			$send_textonly = str_replace($array_search,$array_replace,$textonly); 
			
			// Parse RSMail! plugins
			$app->triggerEvent('rsm_parseMessageContent',array(array('message'=>&$send_body,'idmessage'=>$idMessage,'idsubscriber'=>$email->IdSubscriber,'idsession'=>$sessionId,'email'=>$to,'idlist'=>$email->IdList)));
			$app->triggerEvent('rsm_parseMessageContent',array(array('message'=>&$send_textonly,'idmessage'=>$idMessage,'idsubscriber'=>$email->IdSubscriber,'idsession'=>$sessionId,'email'=>$to,'idlist'=>$email->IdList)));
			
			$pattern = '#href="(.*?)"#i';
			$hrefs = array();
			preg_match_all($pattern, $send_body, $matches);
			$hrefs = $matches;
			
			if(!empty($hrefs[1]))
				foreach($hrefs[1] as $i => $href) {
					if (substr($href,0,1) == '#' || substr(strtolower($href),0,6) == 'mailto' || substr(strtolower($href),0,10) == 'javascript' || strpos($href,'com_rsmail&view=unsubscribe') !== FALSE || strpos($href,'com_rsmail&view=history') !== FALSE || strpos($href,'com_rsmail&view=details') !== FALSE) {
						unset($hrefs[0][$i]);
						unset($hrefs[1][$i]);
						continue;
					}
					
					if ($config->absolute_links == 1) {
						if (substr($href,0,7) != 'http://') {
							if (substr($href,0,4) == 'www.')
								$href = 'http://'.$href;
							else
								$href = JURI::root().ltrim($href,'/');
						}
					}

					$send_body = str_replace($hrefs[0][$i],'href="'.JURI::root().'index.php?option=com_rsmail&task=redirecturl&cid='.$sessionId.'&sid='.$email->IdSubscriber.'&url='.base64_encode($href).'"',$send_body);
				}

			//replace the embeded placeholders
			$pattern = '#\[img([0-9]+)\]#';
			preg_match_all($pattern,$send_body,$embedMatches);
			if(!empty($embedMatches) && $isEmbedOn) {
				$embeds_array = new stdClass();
				$count = count($embedMatches[0]);
				for($i=0;$i<$count;$i++) {
					$db->setQuery('SELECT '.$db->qn('FileName').' FROM '.$db->qn('#__rsmail_files').' WHERE '.$db->qn('IdFile').' = '.(int) $embedMatches[1][$i].' AND '.$db->qn('Embeded').' = 1 AND '.$db->qn('IdMessage').' = '.(int) $idMessage);
					$embeds_array->$embedMatches[0][$i] = $db->loadResult();
				}
			
				foreach($embeds_array as $text => $value)
					$send_body = str_replace($text,'<img src="cid:'.$value.'" />',$send_body);
			}
			
			//return if the user has open the email
			if ($mode == 1)
				$send_body .= '<p><img src="'.JURI::root().'index.php?option=com_rsmail&task=openmail&tmpl=component&cid='.$sessionId.'&IdSubscriber='.$email->IdSubscriber.'" border="0" height="1" width="1" /></p>'; 
			
			if(strpos($message->MessageBody,strtolower("<html")) === FALSE && $mode == 1)
			$send_body .= '</body>
</html>';
			
			if ($config->absolute_links == 1) {
				$pattern = '/src=[\'"]?([^\'" >]+)[\'" >]/';
				preg_match_all($pattern, $send_body, $matches); 
				if(!empty($matches[1]))
					foreach($matches[1] as $src)
					{
						if (substr($src,0,7) == 'http://' || substr($src,0,8) == 'https://' || substr($src,0,4) == 'cid:') continue;
						$send_body = str_replace('src="'.$src.'"','src="'.JURI::root().ltrim($src,'/').'"',$send_body);
					}				
			}

			// Send email
			$mailok = rsmailHelper::sendMail($from , $fromName , $to , $send_subject , $send_body , $mode , null , null , $attachments , $replyto , $replytoname ,$send_textonly,$isEmbedOn,$embeds,$bounce_email,$sessionId);

			if (!is_object($mailok) && $mailok == true)
				$counter = $counter +1;	

			// Remove email form the errors table
			if ($overwrite && $mailok === true) {
				$db->setQuery('DELETE FROM '.$db->qn('#__rsmail_errors').' WHERE '.$db->qn('IdSession').' = '.(int) $sessionId.' AND '.$db->qn('IdSubscriber').' = '.(int) $email->IdSubscriber.'');
				$db->execute();
			}

			// Add error
			if (!$overwrite && is_object($mailok)) {
				$db->setQuery('INSERT INTO '.$db->qn('#__rsmail_errors').' SET '.$db->qn('IdSession').' = '.(int) $sessionId.', '.$db->qn('IdList').' = '.$db->q($email->IdList).', '.$db->qn('message').' = '.$db->q($mailok->getMessage()).', '.$db->qn('IdSubscriber').' = '.(int) $email->IdSubscriber.'');
				$db->execute();
			}

			$db->setQuery('UPDATE '.$db->qn('#__rsmail_sessions').' SET '.$db->qn('MessageCounterSent').' = '.(int) $counter.' WHERE '.$db->qn('IdSession').' = '.(int) $sessionId);
			$db->execute();
		}

		if (!$overwrite) {
			if($DBposition == $max) {
				$db->setQuery('UPDATE '.$db->qn('#__rsmail_sessions').' SET '.$db->qn('Status').' = 2 WHERE '.$db->qn('IdSession').' = '.(int) $sessionId);
				$db->execute();
			}
			
			echo 'RSEM0'."\n";
			echo $DBposition."\n";
			echo $max."\n";
			echo 'RSEM1'."\n";
			
			exit();
		} else {
			return $this->setRedirect('index.php?option=com_rsmail&view=reports&layout=errors&id='.$sessionId,JText::_('RSM_EMAIL_SENT_MSG'));
		}
	}
	
}