<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die();
jimport( 'joomla.application.component.model' );

class rsmailModelMessage extends JModelAdmin
{
	protected $text_prefix = 'COM_RSMAIL';
	
	/**
	 * Returns a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 *
	 * @return	JTable	A database object
	*/
	public function getTable($type = 'Message', $prefix = 'rsmailTable', $config = array()) {
		return JTable::getInstance($type, $prefix, $config);
	}
	
	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 *
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true) {
		// Get the form.
		$form = $this->loadForm('com_rsmail.message', 'message', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
			return false;
		
		return $form;
	}
	
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData() {
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_rsmail.edit.message.data', array());

		if (empty($data))
			$data = $this->getItem();

		return $data;
	}
	
	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 */
	public function getItem($pk = null) {
		$tid = JFactory::getApplication()->input->getInt('IdTemplate',0);
		
		if ($item = parent::getItem($pk)) {
			if (empty($item->IdMessage) && !empty($tid)) {
				$db		= JFactory::getDbo();
				$query	= $db->getQuery(true);
				
				$query->clear()->select('*')->from($db->qn('#__rsmail_templates'))->where($db->qn('IdTemplate').' = '.$tid);
				$db->setQuery($query);
				if ($template = $db->loadObject()) {
					$item->MessageType = $template->MessageType;
					
					if ($template->MessageType) {
						$item->MessageBody			= $template->TemplateBody;
						$item->MessageBodyNoHTML	= $template->TemplateText;
					} else {
						$item->MessageBody			= $template->TemplateText;
						$item->MessageBodyNoHTML	= $template->TemplateText;
					}

					$item->MessageSenderEmail	= $template->MessageSenderEmail;
					$item->MessageSenderName	= $template->MessageSenderName;
					$item->MessageReplyTo		= $template->MessageReplyTo;
					$item->MessageReplyToName	= $template->MessageReplyToName;
					$item->MessageSubject 		= $template->TemplateName;
				}
			}
		}
		
		return $item;
	}
	
	/**
	 * Method to get Tabs
	 *
	 * @return	mixed	The Joomla! Tabs.
	 * @since	1.6
	 */
	public function getTabs() {
		$tabs = new RSTabs('settings');
		return $tabs;
	}
	
	/**
	 * Method to get message layouts
	 *
	 */
	public function getLayouts() {
		return array('message','files','spam');
	}
	
	/**
	 * Method to get Message files
	 *
	 */
	public function getFiles() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$id		= JFactory::getApplication()->input->getInt('IdMessage',0);
		
		$query->clear()->select('*')->from($db->qn('#__rsmail_files'))->where($db->qn('IdMessage').' = '.$id);
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	/**
	 * Method to get Message uploaded files
	 *
	 */
	public function getAllFiles() {
		jimport('joomla.filesystem.folder');
		$path = JPATH_ADMINISTRATOR.'/components/com_rsmail/files';
		return JFolder::files($path,'.',false,false,array('index.html','tmp.csv'));
	}
	
	/**
	 * Method to get send a preview message
	 *
	 */
	public function test() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$jinput	= JFactory::getApplication()->input;
		$id		= $jinput->getInt('IdMessage',0);
		$row	= $this->getItem($id);
		
		$attachments	= rsmailHelper::attachments($id);
		$from			= $row->MessageSenderEmail;
		$fromName		= $row->MessageSenderName;
		$to				= $jinput->getString('preview');
		$bcc			= 'spamcheck@rsjoomla.com';
		$subject		= $row->MessageSubject;
		$mode			= $row->MessageType;
		$replyto		= $row->MessageReplyTo;
		$replytoname	= $row->MessageReplyToName;
		$textonly		= ($mode == 1) ? $row->MessageBodyNoHTML : '';
		$message		= rsmailHelper::placeholders($row->MessageBody);
		
		$body		= rsmailHelper::setHeader($message, $mode);
		$body		= rsmailHelper::absolute($body);
		$textonly	= rsmailHelper::absolute($textonly);
		$mail		= rsmailHelper::sendMail($from , $fromName , $to , $subject , $body , $mode , null , $bcc , $attachments , $replyto , $replytoname , $textonly);
		
		return array('id' => $id, 'status' => $mail);
	}
	
	/**
	 * Method to get the preview message
	 *
	 */
	public function getPreview() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$jinput	= JFactory::getApplication()->input;
		$id		= $jinput->getInt('id',0);
		$row	= $this->getItem($id);
		
		$message	= $row->MessageBody;
		$message	= rsmailHelper::placeholders($message);
		$message	= rsmailHelper::setHeader($message, $row->MessageType);
		$message	= rsmailHelper::absolute($message);
		
		return $message;
	}
	
	/**
	 * Method to embed a image
	 *
	 */
	public function embed() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$jinput	= JFactory::getApplication()->input;
		$id		= $jinput->getInt('id',0);
		
		$query->clear()
			->select($db->qn('IdMessage'))->select($db->qn('Embeded'))
			->from($db->qn('#__rsmail_files'))
			->where($db->qn('IdFile').' = '.$id);
		
		$db->setQuery($query);
		if ($file = $db->loadObject()) {
			if ($file->Embeded == 1) {
				$query->clear()
					->update($db->qn('#__rsmail_files'))
					->set($db->qn('Embeded').' = 0')
					->where($db->qn('IdFile').' = '.$id);
			} else {
				$query->clear()
					->update($db->qn('#__rsmail_files'))
					->set($db->qn('Embeded').' = 1')
					->where($db->qn('IdFile').' = '.$id);
			}
			
			$db->setQuery($query);
			$db->execute();
		}
		
		return $file->IdMessage;
	}
	
	/**
	 * Method to delete a file
	 *
	 */
	public function deletefile() {
		jimport('joomla.filesystem.file');
		
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$jinput	= JFactory::getApplication()->input;
		$id		= $jinput->getInt('id',0);
		$path	= JPATH_ADMINISTRATOR.'/components/com_rsmail/files/';
		
		$query->clear()
			->select($db->qn('IdMessage'))->select($db->qn('FileName'))
			->from($db->qn('#__rsmail_files'))
			->where($db->qn('IdFile').' = '.$id);
		
		$db->setQuery($query);
		$file = $db->loadObject();
		
		if (JFile::exists($path.$file->FileName)) {
			if (JFile::delete($path.$file->FileName)) {		
				$query->clear()->delete()->from($db->qn('#__rsmail_files'))->where($db->qn('IdFile').' = '.$id);
				$db->setQuery($query);
				$db->execute();
				
				$query->clear()->delete()->from($db->qn('#__rsmail_files'))->where($db->qn('FileName').' = '.$db->q($file->FileName));
				$db->setQuery($query);
				$db->execute();
			}
		}
		
		return $file->IdMessage;
	}
	
	public function addtemplate() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$jinput	= JFactory::getApplication()->input;
		$id		= $jinput->getInt('IdMessage',0);
		
		$query->clear()
			->select('*')
			->from($db->qn('#__rsmail_messages'))
			->where($db->qn('IdMessage').' = '.$id);
		
		$db->setQuery($query);
		if ($message = $db->loadObject()) {
			$query->clear()
				->insert($db->qn('#__rsmail_templates'))
				->set($db->qn('TemplateName').' = '.$db->q($message->MessageSubject))
				->set($db->qn('TemplateBody').' = '.$db->q($message->MessageBody))
				->set($db->qn('TemplateText').' = '.$db->q($message->MessageBodyNoHTML))
				->set($db->qn('MessageType').' = '.$db->q($message->MessageType))
				->set($db->qn('MessageSenderEmail').' = '.$db->q($message->MessageSenderEmail))
				->set($db->qn('MessageSenderName').' = '.$db->q($message->MessageSenderName))
				->set($db->qn('MessageReplyTo').' = '.$db->q($message->MessageReplyTo))
				->set($db->qn('MessageReplyToName').' = '.$db->q($message->MessageReplyToName));
			
			$db->setQuery($query);
			$db->execute();
		}
		
		return true;
	}
}