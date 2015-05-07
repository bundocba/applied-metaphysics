<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once JPATH_SITE.'/components/com_rsmail/helpers/version.php';

$version = new RSMailVersion();
define('RSM_RS_PRODUCT',	'RSMail!');
define('RSM_RS_VERSION',	$version->long);
define('RSM_RS_REVISION',	$version->revision);
define('RSM_RS_KEY',		$version->key);

abstract class rsmailHelper {
	
	// Load Config
	public static function getConfig($what = null) {
		static $config;
		
		if (!is_object($config)) {
			$db		= JFactory::getDbo();
			$query	= $db->getQuery(true);
			$config	= new stdClass();
			
			$query->clear()
				->select($db->qn('ConfigName'))->select($db->qn('ConfigValue'))
				->from($db->qn('#__rsmail_config'));
			$db->setQuery($query);
			if ($configuration = $db->loadObjectList()) {
				foreach ($configuration as $option) {
					if (empty($option->ConfigName)) continue;
					$config->{$option->ConfigName} = $option->ConfigValue;
				}
			}
		}
		
		if ($what != null) {
			if (isset($config->{$what})) 
				return $config->{$what};
			else return false;
		} else {
			return $config;
		}
	}
	
	// Get key hash
	public static function genKeyCode() {
		$code = self::getConfig('registration_code');
		return md5($code.RSM_RS_KEY);
	}
	
	// Check joomla version
	public static function isJ3() {
		return version_compare(JVERSION, '3.0', '>=');
	}
	
	// Load scripts 
	public static function initialize($from = 'admin') {
		$doc	= JFactory::getDocument();
		$task	= JFactory::getApplication()->input->get('task');
		
		if ($from == 'admin') {
			$doc->addScript(JURI::root(true).'/administrator/components/com_rsmail/assets/js/rsmail.js?v='.RSM_RS_REVISION);
			$doc->addStyleSheet(JURI::root(true).'/administrator/components/com_rsmail/assets/css/style.css?v='.RSM_RS_REVISION);
			
			if (self::isJ3()) {
				if ($task != 'subscribers.copy')
					JHtml::_('formbehavior.chosen', 'select');
				
				$doc->addStyleSheet(JURI::root(true).'/administrator/components/com_rsmail/assets/css/j3.css?v='.RSM_RS_REVISION);
			} else {
				$doc->addStyleSheet(JURI::root(true).'/administrator/components/com_rsmail/assets/css/j2.css?v='.RSM_RS_REVISION);
			}
		} else {
			$doc->addStyleSheet(JURI::root(true).'/components/com_rsmail/assets/style.css?v='.RSM_RS_REVISION);
		}
	}
	
	// Prepare submenu
	public static function subMenu() {
		$jinput = JFactory::getApplication()->input;
		$view   = $jinput->getCmd('view');
		$layout = $jinput->getCmd('layout');
		$views  = array('lists','subscribers','messages','autoresponders','templates','sessions','reports','cronlogs','import','settings','updates');
		
		JHtmlSidebar::addEntry(JText::_('RSM_SUBMENU_DASHBOARD'), 'index.php?option=com_rsmail',(empty($view) && empty($layout)));
		
		foreach ($views as $theview) {
			JHtmlSidebar::addEntry(JText::_('RSM_SUBMENU_'.strtoupper($theview)), 'index.php?option=com_rsmail&view='.$theview, ($theview == $view));
		}
	}
	
	// Bounce handling
	public static function bounce() {
		$config = self::getConfig();
		$task	= JFactory::getApplication()->input->get('task');
		$view	= JFactory::getApplication()->input->get('view');
		$tasks	= array('sendmessages','cron','autoresponders','unsetsession','openemail','subscribe','unsubscribe','activatesubscribe','activate','redirecturl');
		$views	= array('unsubscribe');
		
		// REMOVE
		return false;
		
		if (($config->bounce_handle == 0 || $config->bounce_handle == 2) && (!in_array($task,$tasks) || !in_array($view,$views))) {
			require_once JPATH_SITE.'/components/com_rsmail/helpers/bounce.php';
			
			$connect = rsmailBounce::getInstance();
			$connect->parse();
		}
	}
	
	// Close modal
	public static function modalClose($script = true) {
		$html = array();
		
		if ($script) $html[] = '<script type="text/javascript">';
		$html[] = 'window.parent.SqueezeBox.close();';
		if ($script) $html[] = '</script>';
		
		return implode("\n",$html);
	}
	
	// Get lists
	public static function lists() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		
		$query->clear()
			->select($db->qn('IdList'))->select($db->qn('ListName'))
			->from($db->qn('#__rsmail_lists'));
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	// Get user lists
	public static function userlists($email) {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		
		$query->clear()
			->select($db->qn('l.ListName'))
			->from($db->qn('#__rsmail_lists','l'))
			->join('LEFT', $db->qn('#__rsmail_subscribers','s').' ON '.$db->qn('s.IdList').' = '.$db->qn('l.IdList'))
			->where($db->qn('s.SubscriberEmail').' = '.$db->q($email))
			->where($db->qn('s.published').' = 1');
		
		$db->setQuery($query);
		if ($lists = $db->loadColumn())
			return JText::_('RSM_SUBSCRIBED_TO').'::'.implode('<br />',$lists);
		else
			return JText::_('RSM_USER_NOT_SUBSCRIBED');
	}
	
	// Show date
	public static function showDate($date, $utc = false) {
		$date_format = self::getConfig('global_dateformat');
		return $utc ? JHTML::date($date, $date_format, 'UTC') : JHTML::date($date, $date_format);
	}
	
	// Replace placeholders
	public static function placeholders($message) {
		// Parse RSMail! plugins
		jimport('joomla.plugin.helper');
		JPluginHelper::importPlugin('rsmail');
		JFactory::getApplication()->triggerEvent('rsm_parseMessageContent',array(array('message'=>&$message)));
		
		return $message;
	}
	
	// Send email
	public static function sendMail($from, $fromname, $recipient, $subject, $body, $mode=0, $cc=null, $bcc=null, $attachment=null, $replyto=null, $replytoname=null , $textonly , $embeded=null , $embeds=null , $bounce_email = null,$idsession = null) {
	 	// Get a JMail instance
		$mail = JFactory::getMailer();
		
		$mail->ClearReplyTos();
		$mail->setSender(array($from, $fromname));
		$mail->setSubject($subject);
		$mail->setBody($body);
		
		if (!is_null($bounce_email))
			$mail->Sender = $bounce_email;

		if (!is_null($idsession))
			$mail->addCustomHeader('RSMIdSession:'.$idsession);
		
		// Are we sending the email as HTML?
		if ($mode) {
			$mail->IsHTML(true);
			$mail->AltBody  = $textonly;
		}

		//embed images
		if ($embeded) {
			if(is_array($embeds)) {
				foreach($embeds as $embed) {
					$mail->AddEmbeddedImage($embed->Path,$embed->FileName);
				}
			}
		}

		$mail->addRecipient($recipient);
		$mail->addCC($cc);
		$mail->addBCC($bcc);
		$mail->addAttachment($attachment);

		// Take care of reply email addresses
		if (is_array($replyto)) {
			$mail->ClearReplyTos();
			$numReplyTo = count($replyto);
			for ($i=0; $i < $numReplyTo; $i++) {
				$mail->addReplyTo( array($replyto[$i], $replytoname[$i]) );
			}
		} elseif (!empty($replyto)) {
			$mail->ClearReplyTos();
			$mail->addReplyTo( array( $replyto, $replytoname ) );
		}
		
		return $mail->Send();
	}
	
	// Convert relative links to absolute links
	public static function absolute($message) {
		$config	= rsmailHelper::getConfig();
		
		if ($config->absolute_links == 1) {
			$pattern = '/src=[\'"]?([^\'" >]+)[\'" >]/'; 
			preg_match_all($pattern, $message, $imgmatches); 
			
			if(!empty($imgmatches[1])) {
				foreach($imgmatches[1] as $src) {
					if (substr($src,0,7) == 'http://') continue;
					$message = str_replace('src="'.$src.'"','src="'.JURI::root().ltrim($src,'/').'"',$message);
				}
			}
			
			$patternhref = '#href="(.*?)"#i';
			preg_match_all($patternhref, $message, $hrefmatches);
			
			if(!empty($hrefmatches[1])) {
				foreach($hrefmatches[1] as $i => $match) {
					if (substr($match,0,1) == '#' || substr(strtolower($match),0,6) == 'mailto' || substr(strtolower($match),0,10) == 'javascript' || strpos(strtolower($match),'com_rsmail&view=unsubscribe') !== FALSE || strpos(strtolower($match),'com_rsmail&view=history') !== FALSE || strpos(strtolower($match),'com_rsmail&view=details') !== FALSE) continue;
					
					if (substr($match,0,7) != 'http://') {
						if (substr($match,0,4) == 'www.')
							$match = 'http://'.$match;
						else
							$match = JURI::root().ltrim($match,'/');
							$message = str_replace($hrefmatches[0][$i],'href="'.$match.'"',$message);
					}
				}
			}
		}
		
		return $message;
	}
	
	// Add HTML header
	public static function setHeader($message, $mode) {
		if (strpos($message, strtolower("<html")) !== FALSE) {
			return $message;
		} else {
			$return = '';
			
			if ($mode == 1){
				$return .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'."\n";
				$return .= '<html>'."\n";
				$return .= '<body>'."\n";
			}
			
			$return .= $message;
			
			if ($mode == 1) {
				$return .= "\n".'</body>'."\n";
				$return .= '</html>';
			}
			
			return $return;
		}
	}
	
	// Get message attachments
	public static function attachments($id, $embeded = null) {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$return = array();
		
		$query->clear()
			->select($db->qn('FileName'))
			->from($db->qn('#__rsmail_files'))
			->where($db->qn('IdMessage').' = '.$id);
		
		if (!is_null($embeded))
			$query->where($db->qn('Embeded').' = '.(int) $embeded);
		
		$db->setQuery($query);
		if ($filenames = $db->loadObjectList()) {
			foreach($filenames as $filename)
				$return[] = JPATH_SITE.'/administrator/components/com_rsmail/files/'.$filename->FileName;
		}
		
		return $return;
	}
	
	// Get email details
	public static function getMessage($type) {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$lang	= JFactory::getLanguage()->getTag();
		
		$query->clear()
			->select('*')
			->from($db->qn('#__rsmail_emails'))
			->where($db->qn('type').' = '.$db->q($type))
			->where($db->qn('lang').' = '.$db->q($lang));
		
		$db->setQuery($query);
		if (!$message = $db->loadObject()) {
			$query->clear()
				->select('*')
				->from($db->qn('#__rsmail_emails'))
				->where($db->qn('type').' = '.$db->q($type))
				->where($db->qn('lang').' = '.$db->q('en-GB'));
			$db->setQuery($query);
			$message = $db->loadObject();
		}
		
		return $message;
	}
	
	// Get user lists
	public static function user_subscribed_lists($email, $lists = null) {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		
		$query->clear()
			->select($db->qn('s.IdList'))->select($db->qn('l.ListName'))
			->from($db->qn('#__rsmail_subscribers','s'))
			->join('LEFT',$db->qn('#__rsmail_lists','l').' ON '.$db->qn('s.IdList').' = '.$db->qn('l.IdList'))
			->where($db->qn('s.SubscriberEmail').' = '.$db->q($email))
			->where($db->qn('s.published').' = 1');
		
		if($lists != null)
			$query->where($db->qn('s.IdList').' IN ('.$lists.')');
		
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	// Get embeded files
	public static function cron_embed_files($id,$on) {
		if ($on) {
			$db		= JFactory::getDbo();
			$query	= $db->getQuery(true);
			
			$query->clear()
				->select($db->qn('FileName'))
				->from($db->qn('#__rsmail_files'))
				->where($db->qn('IdMessage').' = '.$id)
				->where($db->qn('Embeded').' = 1');
			
			$db->setQuery($query);
			if ($embeds = $db->loadObjectList())
				foreach($embeds as $embed)
					$embed->Path = JPATH_SITE.'/administrator/components/com_rsmail/files/'.$embed->FileName;
			
			return !empty($embeds) ? $embeds : null;
		} else return null;
	}
	
	// Parse RSMail! plugins
	public static function cron_replace_articles($message,$idm,$idsubscriber,$ids,$subscriber,$idlist,$ar = null) {
		jimport('joomla.plugin.helper');
		JPluginHelper::importPlugin('rsmail');
		
		if (!is_null($ar))
			JFactory::getApplication()->triggerEvent('rsm_parseMessageContent',array(array('message'=>&$message,'idmessage'=>$idm,'idsubscriber'=>$idsubscriber,'idsession'=>$ids,'email'=>$subscriber,'idlist'=>$idlist,'ar'=>1)));
		else
			JFactory::getApplication()->triggerEvent('rsm_parseMessageContent',array(array('message'=>&$message,'idmessage'=>$idm,'idsubscriber'=>$idsubscriber,'idsession'=>$ids,'email'=>$subscriber,'idlist'=>$idlist)));
		
		return $message;
	}
	
	// URL replacement
	public static function cron_url_replacement($message,$idsession,$idsubscriber) {
		// Transform relative to absolute paths
		$message = rsmailHelper::absolute($message);
		
		$pattern = '#href="(.*?)"#i';
		preg_match_all($pattern, $message, $hrefs);
		
		if(!empty($hrefs[1]))
			foreach($hrefs[1] as $i => $href) {
				if (substr($href,0,1) == '#' || substr(strtolower($href),0,6) == 'mailto' || substr(strtolower($href),0,10) == 'javascript' || strpos($href,'com_rsmail&view=unsubscribe') !== FALSE || strpos($href,'com_rsmail&view=history') !== FALSE || strpos($href,'com_rsmail&view=details') !== FALSE) {
					unset($hrefs[0][$i]);
					unset($hrefs[1][$i]);
					continue;
				}
				
				$message = str_replace($hrefs[0][$i],'href="'.JURI::root().'index.php?option=com_rsmail&task=redirecturl&cid='.$idsession.'&sid='.$idsubscriber.'&url='.base64_encode($href).'"',$message);
			}
		
		return $message;
	}
	
	// Replace embeded placeholders
	public static function cron_replace_embeded_placeholders($content,$idmessage,$on) {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		
		$pattern = '#\[img([0-9]+)\]#';
		preg_match_all($pattern,$content,$embedMatches);
		if(!empty($embedMatches) && $on) {
			$embeds_array = new stdClass();
			$count = count($embedMatches[0]);
			
			for($i=0;$i<$count;$i++) {
				$query->clear()
					->select($db->qn('FileName'))
					->from($db->qn('#__rsmail_files'))
					->where($db->qn('IdFile').' = '.(int) $embedMatches[1][$i])
					->where($db->qn('Embeded').' = 1')
					->where($db->qn('IdMessage').' = '.(int) $idmessage);
				
				$db->setQuery($query);
				$embeds_array->$embedMatches[0][$i] = $db->loadResult();
			}
		
			if (!empty($embeds_array))
				foreach($embeds_array as $text => $value)
					$content = str_replace($text,'<img src="cid:'.$value.'" />',$content);
		}
		
		return $content;
	}
	
	
	/**
	 * Method to get filtering condition
	 */
	public function getFilterCondition($filters) {
		$db		= JFactory::getDbo();
		$where	= '';
		
		if (empty($filters['lists']))
			return $where;
		
		foreach($filters['lists'] as $key => $list) {
			$subscribe_state	= 1;
			$operator 			= $filters['operators'][$key];
			$value				= $filters['values'][$key];
			$condition			= $filters['condition'];

			if (!isset($filters['fields'][$key])) {
				$field = '';
			} elseif ($filters['fields'][$key] == 'email')
				$field 	= $db->qn('s.SubscriberEmail');
			elseif(!empty($filters['fields'][$key])) {
				$db->setQuery('SELECT '.$db->qn('FieldName').' FROM '.$db->qn('#__rsmail_list_fields').' WHERE '.$db->qn('IdListFields').' = '.$db->q($filters['fields'][$key]));
				$field 	= $db->loadResult();
			} else {
				$field = '';
			}
			
			switch($operator) {
				case 'contains':
					$operator = ' LIKE';
					$value	  = (!empty($value) ? " '%".str_replace("%", "\%", $value)."%' " : " '' ");
				break;
				case 'not_contain':
					$operator = ' NOT LIKE';
					$value	  = (!empty($value) ? " '%".str_replace("%", "\%", $value)."%' " : " '' ");
				break;
				case 'is':
					$operator	= ' = ';
					$value 		= $db->q($value);
				break;
				case 'is_not':
					$operator 	= ' <> ';
					$value 		= $db->q($value);
				break;
			}
			
			switch($condition) {
				case 'OR':
					if(!empty($list)) {
						if(!empty($field)) {
							if($filters['fields'][$key] == 'email')
								$where .= ' ('.$db->qn('s.published').' = '.$db->q($subscribe_state).' AND '.$db->qn('s.IdList').' = '.$db->q($list).' AND '.$field.$operator.$value.') '.$condition;
							else {
								$where .= ' ('.$db->qn('s.published').' = '.$db->q($subscribe_state).' AND '.$db->qn('sd.IdList').' = '.$db->q($list);
								if (!empty($field)) 
									$where .= ' AND '.$db->qn('sd.FieldName').' = '.$db->q($field).' AND '.$db->qn('sd.FieldValue').' '.$operator.$value;
								$where .= ') '.$condition;
							}
						} else {
							$where .= '('.$db->qn('s.published').' = '.$db->q($subscribe_state).' AND '.$db->qn('s.IdList').' = '.$db->q($list).') '.$condition;
						}
					} else {
						$where .= ' '.$db->qn('s.published').' = '.$db->q($subscribe_state);
						if (!empty($field))
							$where .= ' AND '.$field.$operator.$value;
						$where  .= ' '.$condition;
					}
				break;
				
				case 'AND':
					if(!empty($list)) {
						if(!empty($field)) {
							if($filters['fields'][$key] == 'email') {
								$where .= ' ('.$db->qn('s.published').' = '.$db->q($subscribe_state).' AND '.$db->qn('s.IdList').' = '.$db->q($list).' AND '.$field.$operator.$value.') '.$condition;
							} else {
								$where .= ' (('.$db->qn('s.published').' = '.$db->q($subscribe_state).' AND '.$db->qn('sd.IdList').' = '.$db->q($list).' ';
								
								if (!empty($field)) {
									$where .= ' AND '.$db->qn('sd.FieldName').' = '.$db->q($field).' AND '.$db->qn('sd.FieldValue').' '.$operator.$value.') OR ('.$db->qn('s.SubscriberEmail').' IN (SELECT '.$db->qn('s.SubscriberEmail').' FROM '.$db->qn('#__rsmail_subscribers','s').' LEFT JOIN '.$db->qn('#__rsmail_subscriber_details','sd').' ON '.$db->qn('s.IdSubscriber').' = '.$db->qn('sd.IdSubscriber').' WHERE '.$db->qn('s.published').' = '.$db->q($subscribe_state).' AND '.$db->qn('sd.IdList').' = '.$db->q($list).' AND ';
									
									if ($value != "''") {
										$where .= $db->qn('sd.FieldName').' = '.$db->q($field).' AND '.$db->qn('sd.FieldValue').' '.$operator.$value.' ';
									} else {
										$where .= '(SELECT COUNT('.$db->qn('IdSubscriber').') FROM '.$db->qn('#__rsmail_subscriber_details').' WHERE '.$db->qn('FieldName').' = '.$db->q($field).' AND '.$db->qn('IdList').' = '.$db->q($list).' AND '.$db->qn('IdSubscriber').' = '.$db->qn('sd.IdSubscriber').') = 0';
									}
									
									$where .= ' ))';
								} else {
									$where .= ')';
								}
								$where .= ') '.$condition;
							}
						} else {
							$where .= ' ('.$db->qn('s.SubscriberEmail').' IN (SELECT '.$db->qn('SubscriberEmail').' FROM '.$db->qn('#__rsmail_subscribers').' WHERE '.$db->qn('IdList').' = '.$db->q($list).' AND '.$db->qn('published').' = '.$db->q($subscribe_state).')) '.$condition;
						}
					} else {
						$where .= ' '.$db->qn('s.published').' = '.$db->q($subscribe_state);
						if (!empty($field))
							$where .= ' AND '.$field.$operator.$value;
						$where .= ' '.$condition;
					}
				break;
			}
		}

		return $where;
	}
	
	// Autoresponder mail helper
	public static function mailHelper($message,$subscriber) {
		$db = JFactory::getDBO();
		
		// Get attachments
		$attachments = rsmailHelper::attachments($message->IdMessage,0);
		
		// Get message
		$db->setQuery('SELECT * FROM '.$db->qn('#__rsmail_messages').' WHERE '.$db->qn('IdMessage').' = '.(int) $message->IdMessage);
		$message_details = $db->loadObject();
		
		$from			= $message_details->MessageSenderEmail;
		$fromName		= $message_details->MessageSenderName;
		$mode			= $message_details->MessageType;
		$replyto		= $message_details->MessageReplyTo;
		$replytoname	= $message_details->MessageReplyToName;
		$subject		= $message_details->MessageSubject;
		$textonly		= ($mode == 1) ? $message_details->MessageBodyNoHTML : '';
		
		//prepare the body to be html valid
		if(strpos($message_details->MessageBody,strtolower("<html")) !== FALSE)
			$body = $message_details->MessageBody;
		else  {
			$body = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html><body>';
			$body .= $message_details->MessageBody;
		}
		
		if($mode == 0) $body = $message_details->MessageBody;
		
		//load the ar details
		$replacer = array();
		$db->setQuery('SELECT * FROM '.$db->qn('#__rsmail_ar_message_details').' WHERE '.$db->qn('IdAutoresponderMessage').' = '.(int) $message->IdAutoresponderMessage);
		if ($messageDetails = $db->loadObjectList())
			foreach($messageDetails as $sd) {
				$replacer[$sd->IdList][$sd->ToSearch] = $sd->ToReplace;
			}
		
		// Check for embeded files
		$db->setQuery('SELECT COUNT('.$db->qn('IdFile').') FROM '.$db->qn('#__rsmail_files').' WHERE '.$db->qn('Embeded').' = 1 AND '.$db->qn('IdMessage').' = '.(int) $message->IdMessage);
		$isEmbedOn = $db->loadResult();
		
		$embeds = null;
		
		if ($isEmbedOn) {
			$db->setQuery('SELECT '.$db->qn('FileName').' FROM '.$db->qn('#__rsmail_files').' WHERE '.$db->qn('IdMessage').' = '.(int) $message->IdMessage.' AND '.$db->qn('Embeded').' = 1');
			if ($embeds = $db->loadObjectList())
				foreach($embeds as $embed)
					$embed->Path = JPATH_SITE.'/administrator/components/com_rsmail/files/'.$embed->FileName;
		}
		
		$to = $subscriber->SubscriberEmail;
		$to = trim($to);
		
		// Load subscriber details
		$db->setQuery('SELECT * FROM '.$db->qn('#__rsmail_subscriber_details').' WHERE '.$db->qn('IdList').' = '.(int) $subscriber->IdList.' AND '.$db->qn('IdSubscriber').' = '.(int) $subscriber->IdSubscriber.'');
		$SubscriberDetails = $db->loadObjectList();
		
		$array_replace = array();
		$array_search = array();
		if(!empty($replacer)) 
			foreach($replacer[$subscriber->IdList] as $search=>$replace) {
			//search for the replace in subscriber details
			$replace_with = '';
			if($replace == JText::_('RSM_DO_NOT_REPLACE')) $replace_with = '{'.$search.'}';
			foreach($SubscriberDetails as $Detail) {
				if($replace == $Detail->FieldName) $replace_with = $Detail->FieldValue;
				if($replace == JText::_('RSM_IGNORE')) $replace_with = '';
				if($replace == JText::_('RSM_EMAIL')) $replace_with = $subscriber->SubscriberEmail;
			}	
			$array_search[] = '{'.$search.'}';
			$array_replace[] = $replace_with;
		}
		
		//replace the placeholders			
		$send_body     = str_replace($array_search,$array_replace,$body);
		$send_subject  = str_replace($array_search,$array_replace,$subject);
		$send_textonly = str_replace($array_search,$array_replace,$textonly); 
		
		// Parse RSMail! plugins
		$send_body		= rsmailHelper::cron_replace_articles($send_body,$message->IdMessage,$subscriber->IdSubscriber,'9999999',$to,$subscriber->IdList,1);
		$send_textonly	= rsmailHelper::cron_replace_articles($send_textonly,$message->IdMessage,$subscriber->IdSubscriber,'9999999',$to,$subscriber->IdList,1);
		
		// Replace the embeded placeholders
		$send_body = rsmailHelper::cron_replace_embeded_placeholders($send_body,$message->IdMessage,$isEmbedOn);
		
		if(strpos($message_details->MessageBody,strtolower("<html")) === FALSE && $mode == 1)
		$send_body .= '</body></html>';
		
		$send_body		= rsmailHelper::absolute($send_body);
		$send_textonly	= rsmailHelper::absolute($send_textonly);
		
		// Send mail
		rsmailHelper::sendMail($from, $fromName, $to, $send_subject, $send_body, $mode, null, null, $attachments, $replyto, $replytoname, $send_textonly, $isEmbedOn, $embeds, null, null);
	}
	
	// Read the content of a file
	public static function readfile($filename, $retbytes = true) {
		$chunksize = 1*(1024*1024); // how many bytes per chunk
		$buffer = '';
		$cnt =0;
		$handle = fopen($filename, 'rb');
		if ($handle === false) {
			return false;
		}
		while (!feof($handle)) {
			$buffer = fread($handle, $chunksize);
			echo $buffer;
			if ($retbytes) {
				$cnt += strlen($buffer);
			}
		}
	   $status = fclose($handle);
	   if ($retbytes && $status) {
			return $cnt; // return num. bytes delivered like readfile() does.
		}
		return $status;
	}
}