<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined( '_JEXEC' ) or die( 'Restricted access' ); 

class rsmailBounce {
	
	protected $_config = null;
	protected $_errors = array();
	
	/*
	*	Main constructor
	*/
	public function __construct() {
		$this->_config	= $this->_getConfig();
	}
	
	// Get a new instance
	public function getInstance() {
		return new rsmailBounce;
	}
	
	// Test connection
	public function testconnection() {
		if (!self::checkforfunctions()) 
			return false;
		
		// Connect to mail
		$mbox = $this->_connect();
		$this->_getConnectionErrors();
		$this->_disconnect($mbox);
	}
	
	// Check for required functions
	protected function checkforfunctions($show_message = true) {	
		
		if (empty($this->_config->bounce_mail_server) || empty($this->_config->bounce_mail_port) || empty($this->_config->bounce_mail_username) || empty($this->_config->bounce_mail_password)) {
			if ($show_message)
				$this->setError(JText::_('RSM_BOUNCE_INVALID_DATA'));
			return false;
		}
		
		if (!function_exists('imap_open')) {
			if ($show_message)
				$this->setError(JText::_('RSM_NO_IMAP'));
			return false;
		}
		
		if (!function_exists('iconv')) {
			if ($show_message)
				$this->setError(JText::_('RSM_NO_ICONV'));
			return false;
		}
		
		return true; 
	}
	
	// Set errors
	protected function setError($error) {
		if (!is_array($this->_errors)) {
			$this->_errors = array();
		}
		
		$this->_errors[] = $error;
	}
	
	// Get errors
	public function getErrors() {
		if (!empty($this->_errors)) {
			return $this->_errors;
		}
		return false;
	}
	
	
	/*
	*	Parse e-mails
	*/
	public function parse() {
		if (!$this->checkforfunctions(false))
			return;
		
		$mbox = $this->_connect();
		if (!$mbox) return;
		$total = imap_num_msg($mbox);
		
		if ($total == 0) {
			$this->_disconnect($mbox);
			return;
		}
		
		// little hack to prevent server from timing out
		if ($total > $this->_config->bounce_parse_nr)
			$total = $this->_config->bounce_parse_nr;
		
		if ($this->_config->bounce_mail_connection == 'pop3')
			$this->_parsePop($mbox,$total);
		else 
			$this->_parseImap($mbox,$total);
		
		imap_expunge($mbox);
		$this->_disconnect($mbox);
	}
	
	
	/*
	*	Parse imap emails
	*/
	protected function _parseImap($mbox,$total) {
		//get the unread messages
		$imbox_emails = imap_search($mbox, 'UNSEEN');
		
		$i = 0;
		
		if (!empty($imbox_emails))
		foreach ($imbox_emails as $mid)
		{
			if ($i > $total) break;
			
			$email = '';
			$headers = $this->_decodeHeaders($mbox, $mid);
			
			if (empty($headers))
				continue;
			
			// headers we need
			$subject = @$headers->subject->text;
			preg_match('#deliver|undeliverd|daemon|fail|failed|system|reject|return|returned|impos#i',$subject,$matches);
			if (empty($matches)) continue;
			
			$additional_headers = explode("\n",imap_fetchheader($mbox, $mid));			
			foreach($additional_headers as $line) {
				if (strpos($line,'X-Failed') !== false) {
					$emailpattern = '#[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{1,4}#is';
					preg_match($emailpattern,$line,$emailmatch);
					if (!empty($emailmatch) && !empty($emailmatch[1]))
						$email = $emailmatch[1];
				}
			}
			
			$mail = new RSMail_helper($mbox, $mid);
			if (empty($mail->structure)) continue;
			
			$message = '';
			if (!empty($mail->htmlmsg))
				$message = $mail->htmlmsg;
			elseif (!empty($mail->plainmsg))
				$message = $mail->plainmsg;
			else
				$message = @$mail->htmlmsg ? @$mail->htmlmsg : @$mail->plainmsg;
			
			preg_match('#RSMIdSession: ([0-9]+)#',$message,$idsession);
			
			if (empty($email)) {
				$epattern = '#To: ([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{1,4})#is';
				preg_match($epattern,$message,$ematch);
				if (!empty($ematch) && !empty($ematch[1]))
					$email = $ematch[1];
			}
			
			$email = trim(str_replace(array("\r","\n"),'',$email));
			
			$mailParts = $mail->parts;
			if (empty($email)) {
				if (isset($mailParts['DELIVERY-STATUS']) && !empty($mailParts['DELIVERY-STATUS']) && is_array($mailParts['DELIVERY-STATUS'])) {
					foreach ($mailParts['DELIVERY-STATUS'] as $mailpart) {
						if (empty($mailpart)) continue;
						
						$pattern1 = '#Final-Recipient: rfc822;([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{1,4})#is';
						preg_match($pattern1,$mailpart,$ematch);
						if (!empty($ematch) && !empty($ematch[1]))
							$email = $ematch[1];
					}
				}
			}
			
			if (empty($email)) {
				if (isset($mailParts['RFC822']) && !empty($mailParts['RFC822']) && is_array($mailParts['RFC822'])) {
					foreach ($mailParts['DELIVERY-STATUS'] as $mailpart) {
						if (empty($mailpart)) continue;
						
						$epattern = '#To: ([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{1,4})#is';
						preg_match($epattern,$message,$ematch);
						if (!empty($ematch) && !empty($ematch[1]))
							$email = $ematch[1];
					}
				}
			}
			
			if (empty($idsession)) {
				if (isset($mailParts['RFC822']) && !empty($mailParts['RFC822']) && is_array($mailParts['RFC822'])) {
					foreach ($mailParts['RFC822'] as $mailpart) {
						if (empty($mailpart)) continue;
						
						preg_match('#RSMIdSession: ([0-9]+)#',$mailpart,$idsession);
					}
				}
			}
			
			if (empty($email)) continue;
			if (empty($idsession[1])) continue;
			$idsession = intval($idsession[1]);
			
			if (!$this->_insert($idsession,$email)) continue;
			$this->_subscriber_action($email);
			
			//Take no action
			if ($this->_config->bounce_rule == 0) {
				if ($this->_config->bounce_delete_no_action == 1)
					imap_delete($mbox, $mid);
				
			}
			
			//Delete message
			if ($this->_config->bounce_rule == 1)
				imap_delete($mbox, $mid);
			
			if ($this->_config->bounce_rule == 2) {
				$config = JFactory::getConfig();
				
				$mailer	= JFactory::getMailer();
				$mailer->sendMail($config->get('mailfrom') , $config->get('fromname') , $this->_config->bounce_to_email, JText::_('Bounced Email: ').$subject , nl2br($message) , 1);
				
				if ($this->_config->bounce_delete_forward == 1)
					imap_delete($mbox, $mid);
			}

			$i++;
		}
	}
	
	/*
	*	Parse pop3 emails
	*/
	
	protected function _parsePop($mbox,$total) {		
		//for ($mid = $total; $mid > 0; $mid--)
		for ($mid=1;$mid<=$total; ++$mid) {
			$email = '';
			$headers = $this->_decodeHeaders($mbox, $mid);
			
			if (empty($headers))
				continue;
			
			// headers we need
			$subject = @$headers->subject->text;
			preg_match('#deliver|daemon|fail|failed|system|reject|return|impos#i',$subject,$matches);
			if (empty($matches)) continue;
			
			$additional_headers = explode("\n",imap_fetchheader($mbox, $mid));
			foreach($additional_headers as $line) {
				if (strpos($line,'X-Failed') !== false) {
					$emailpattern = '#[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}#is';
					preg_match($emailpattern,$line,$emailmatch);
					if (!empty($emailmatch) && !empty($emailmatch[1]))
						$email = $emailmatch[1];
				}
			}
			
			$mail = new RSMail_helper($mbox, $mid);
			if (empty($mail->structure)) continue;
			
			$message = '';
			if (!empty($mail->htmlmsg))
				$message = $mail->htmlmsg;
			elseif (!empty($mail->plainmsg))
				$message = $mail->plainmsg;
			else
				$message = @$mail->htmlmsg ? @$mail->htmlmsg : @$mail->plainmsg;
			
			preg_match('#RSMIdSession: ([0-9]+)#',$message,$idsession);
			
			if (empty($email)) {
				$epattern = '#To: ([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4})#is';
				preg_match($epattern,$message,$ematch);
				if (!empty($ematch) && !empty($ematch[1]))
					$email = $ematch[1];
			}
			
			$email = trim(str_replace(array("\r","\n"),'',$email));
			$mailParts = $mail->parts;
			if (empty($email)) {
				if (isset($mailParts['DELIVERY-STATUS']) && !empty($mailParts['DELIVERY-STATUS']) && is_array($mailParts['DELIVERY-STATUS'])) {
					foreach ($mailParts['DELIVERY-STATUS'] as $mailpart) {
						if (empty($mailpart)) continue;
						
						$pattern1 = '#Final-Recipient: rfc822;([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4})#is';
						preg_match($pattern1,$mailpart,$ematch);
						if (!empty($ematch) && !empty($ematch[1]))
							$email = $ematch[1];
					}
				}
			}
			
			if (empty($email)) {
				if (isset($mailParts['RFC822']) && !empty($mailParts['RFC822']) && is_array($mailParts['RFC822'])) {
					foreach ($mailParts['DELIVERY-STATUS'] as $mailpart) {
						if (empty($mailpart)) continue;
						
						$epattern = '#To: ([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4})#is';
						preg_match($epattern,$message,$ematch);
						if (!empty($ematch) && !empty($ematch[1]))
							$email = $ematch[1];
					}
				}
			}
			
			if (empty($idsession)) {
				if (isset($mailParts['RFC822']) && !empty($mailParts['RFC822']) && is_array($mailParts['RFC822'])) {
					foreach ($mailParts['RFC822'] as $mailpart) {
						if (empty($mailpart)) continue;
						
						preg_match('#RSMIdSession: ([0-9]+)#',$mailpart,$idsession);
					}
				}
			}
			
			if (empty($email)) continue;
			if (empty($idsession[1])) continue;
			$idsession = intval($idsession[1]);
			
			if (!$this->_insert($idsession,$email)) continue;
			$this->_subscriber_action($email);
			
			//Take no action
			if ($this->_config->bounce_rule == 0) {
				if ($this->_config->bounce_delete_no_action == 1)
					imap_delete($mbox, $mid);
				
			}
			
			//Delete message
			if ($this->_config->bounce_rule == 1)
				imap_delete($mbox, $mid);
			
			if ($this->_config->bounce_rule == 2) {
				$config = JFactory::getConfig();
				
				$mailer	= JFactory::getMailer();
				$mailer->sendMail($config->get('mailfrom') , $config->get('fromname') , $this->_config->bounce_to_email, JText::_('Bounced Email: ').$subject , nl2br($message) , 1);
				
				if ($this->_config->bounce_delete_forward == 1)
					imap_delete($mbox, $mid);
			}
		}
	}
	
	
	/*
	*	Insert bounce email into DB
	*/
	
	protected function _insert($idsession,$email) {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		
		// Check to see if this email exists already in our bounce table
		$query->clear()
			->select($db->qn('IdBounce'))
			->from($db->qn('#__rsmail_bounce_emails'))
			->where($db->qn('IdSession').' = '.(int) $idsession)
			->where($db->qn('email').' = '.$db->q($email));
		
		$db->setQuery($query);
		$result = $db->loadResult();
		if (!empty($result)) return false;
		
		// Increment the bounce number
		$query->clear()
			->update($db->qn('#__rsmail_sessions'))
			->set($db->qn('BounceNumber').' = '.$db->qn('BounceNumber').' + 1')
			->where($db->qn('IdSession').' = '.(int) $idsession);
		
		$db->setQuery($query);
		$db->execute();
		
		// Insert the email in our bounce table
		$query->clear()
			->insert($db->qn('#__rsmail_bounce_emails'))
			->set($db->qn('IdSession').' = '.(int) $idsession)
			->set($db->qn('Email').' = '.$db->q($email));
		
		$db->setQuery($query);
		$db->execute();
		
		return true;
	}
	
	/*
	*	Delete , Unsubscribe or take no action regarding the subscriber
	*/
	
	protected function _subscriber_action($email) {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		
		// Unsubscribe user		
		if ($this->_config->subscriber_rule == 1) {
			$query->clear()
				->update($db->qn('#__rsmail_subscribers'))
				->set($db->qn('published').' = 0')
				->where($db->qn('SubscriberEmail').' = '.$db->q($email));
			
			$db->setQuery($query);
			$db->execute();
		}
		
		//delete user 
		if ($this->_config->subscriber_rule == 2) {
			// Get subscriber ids
			$query->clear()
				->select($db->qn('IdSubscriber'))
				->from($db->qn('#__rsmail_subscribers'))
				->where($db->qn('SubscriberEmail').' = '.$db->q($email));
			
			$db->setQuery($query);
			if ($ids = $db->loadColumn()) {
				foreach($ids as $id) {
					$query->clear()->delete()->from($db->qn('#__rsmail_subscriber_details'))->where($db->qn('IdSubscriber').' = '.(int) $id);
					$db->setQuery($query);
					$db->execute();
					
					$query->clear()->delete()->from($db->qn('#__rsmail_subscribers'))->where($db->qn('IdSubscriber').' = '.(int) $id);
					$db->setQuery($query);
					$db->execute();
				}
			}
		}
	}
	
	
	/*
	*	Decode headers
	*/
	
	protected function _decodeHeaders($mbox, $mid) {
		$headers = imap_headerinfo($mbox, $mid);
		
		if (empty($headers))
			return false;
		
		foreach ($headers as $header => $value)
			if (!is_array($value)) {
				$obj = imap_mime_header_decode($value);
				$obj = $obj[0];
				
				$obj->charset = strtoupper($obj->charset);
				
				if ($obj->charset != 'DEFAULT' && $obj->charset != 'UTF-8')
					$obj->text = iconv($obj->charset, 'UTF-8//IGNORE', $obj->text);
				
				$headers->$header = $obj;
			}
		
		return $headers;
	}
	
	/*
	*	Connect to server
	*/
	
	protected function _connect() {
		// {[server]:[port][flags]}
		$server = $this->_config->bounce_mail_server;
		$port = $this->_config->bounce_mail_port;
		$flags = '/'.$this->_config->bounce_mail_connection;
		
		if(empty($server) || empty($this->_config->bounce_mail_username)) return;
		
		if ($this->_config->bounce_mail_security == 'ssl') $flags .= '/ssl';
		elseif ($this->_config->bounce_mail_security == 'tls') $flags .= '/tls';
		else $flags .= '';
		
		if (!$this->_config->bounce_mail_certificate)
			$flags .= '/novalidate-cert';
		
		$connect = '{'.$server.':'.$port.$flags.'}INBOX';
		
		$mbox = @imap_open($connect, $this->_config->bounce_mail_username, $this->_config->bounce_mail_password);
		return $mbox;
	}
	
	
	// Disconnect from server
	protected function _disconnect($mbox) {
		return $mbox === false ? false : imap_close($mbox);
	}
	
	// Get connection errors
	protected function _getConnectionErrors() {
		$errors = imap_errors();
		if (!empty($errors)) {
			foreach ($errors as $error)
				$this->setError($error);
		}
	}
	
	// Get config
	protected function _getConfig() {
		$config = rsmailHelper::getConfig();
		return $config;
	}
}


class RSMail_helper
{
	var $htmlmsg;
	var $plainmsg;
	var $charset;
	var $attachments;
	var $parts = array();
	
	var $mbox;
	var $mid;
	var $structure;
	
	public function __construct($mbox, $mid) {
		$this->mbox = $mbox;
		$this->mid = $mid;
		
		$this->structure = imap_fetchstructure($this->mbox, $this->mid);		
		if (empty($this->structure))
			return false;
		
		$this->_getMessage();
		
		if ($this->charset != 'UTF-8') {
			if ($this->charset == 'X-UNKNOWN') $this->charset = 'UTF-8';
			if (strtolower($this->charset) == 'unicode-1-1-utf-7') $this->charset = 'utf-7';
			$this->plainmsg = iconv($this->charset, 'UTF-8//IGNORE', $this->plainmsg);
			$this->htmlmsg = iconv($this->charset, 'UTF-8//IGNORE', $this->htmlmsg);
		}
	}

	protected function _getMessage() {		
		$this->htmlmsg = $this->plainmsg = $this->charset = '';
		$this->attachments = array();

		// BODY
		// not multipart
		if (empty($this->structure->parts))
			$this->_getPart($this->structure, 0);
		else
			// multipart: iterate through each part
			foreach ($this->structure->parts as $partno0 => $p)
				$this->_getPart($p, $partno0+1);
	}
	
	protected function _getPart($p, $partno) {
		// $partno = '1', '2', '2.1', '2.1.3', etc if multipart, 0 if not multipart

		// DECODE DATA
		if ($partno)
			$data = imap_fetchbody($this->mbox, $this->mid, $partno);
		else
			$data = imap_body($this->mbox, $this->mid);	
		
		// Any part may be encoded, even plain text messages, so check everything.
		if ($p->encoding == 4)
			$data = quoted_printable_decode($data);
		elseif ($p->encoding == 3)
			$data = base64_decode($data);
		// no need to decode 7-bit, 8-bit, or binary
		
		$this->parts[$p->subtype][] = $data; 
		
		// PARAMETERS
		// get all parameters, like charset, filenames of attachments, etc.
		$params = array();
		if (!empty($p->parameters))
			foreach ($p->parameters as $x)
				$params[ strtolower( $x->attribute ) ] = $x->value;
		if (!empty($p->dparameters))
			foreach ($p->dparameters as $x)
				$params[ strtolower( $x->attribute ) ] = $x->value;

		// TEXT
		if ($p->type == 0 && $data)
		{
			// Messages may be split in different parts because of inline attachments,
			// so append parts together with blank row.
			if (strtolower($p->subtype)=='plain')
				$this->plainmsg .= trim($data) ."\n\n";
			else
				$this->htmlmsg .= $data .'<br /><br />';
			$this->charset = $params['charset'];  // assume all parts are same charset
		}

		// EMBEDDED MESSAGE
		// Many bounce notifications embed the original message as type 2,
		// but AOL uses type 1 (multipart), which is not handled here.
		// There are no PHP functions to parse embedded messages,
		// so this just appends the raw source to the main message.
		elseif ($p->type == 2 && $data)
		{		
			$this->plainmsg .= trim($data) ."\n\n";
		}

		// SUBPART RECURSION
		if (!empty($p->parts))
			foreach ($p->parts as $partno0 => $p2)
				$this->_getPart($p2, $partno.'.'.($partno0+1));  // 1.2, 1.2.1, etc.
	}
}