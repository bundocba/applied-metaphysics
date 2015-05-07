<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controller');
jimport('joomla.filesystem.file');

class rsmailControllerMessage extends JControllerForm
{
	/**
	 * Class constructor.
	 *
	 * @param   array  $config  A named array of configuration variables.
	 *
	 * @since	1.6
	 */
	public function __construct() {
		parent::__construct();
	}
	
	public function test() {
		// Get the model
		$model = $this->getModel();
		
		// Send email
		$response = $model->test();
		
		if ($response['status'])
			return $this->setRedirect('index.php?option=com_rsmail&view=message&layout=edit&IdMessage='.$response['id'].'&spam=1', JText::_('RSM_EMAIL_SENT'));
		else 
			return $this->setRedirect('index.php?option=com_rsmail&task=message.edit&IdMessage='.$response['id'], JText::_('RSM_EMAIL_ERROR'));
	}
	
	public function postSaveHook($model, $validData) {
		jimport('joomla.filesystem.file');
		
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$files	= JFactory::getApplication()->input->files->get('files');
		$path 	= JPATH_SITE.'/administrator/components/com_rsmail/files/';
		$ffiles	= JFactory::getApplication()->input->get('rsmfiles',array(),'array');
		$id		= $model->getState('message.id');
		
		$extensions	= rsmailHelper::getConfig('file_extensions');
		$extensions = str_replace(array("\r"," "),"",$extensions);
		$extensions = explode("\n",$extensions);
		
		if (!empty($ffiles)) {
			foreach ($ffiles as $file) {
				if (JFile::exists($path.urldecode($file))) {
					$query->clear()
						->insert($db->qn('#__rsmail_files'))
						->set($db->qn('IdMessage').' = '.$id)
						->set($db->qn('FileName').' = '.$db->q(urldecode($file)));
					$db->setQuery($query);
					$db->execute();
				}
			}
		}
		
		if (!empty($files)) {
			foreach ($files as $file) {
				$extension = JFile::getExt($file['name']);
				if (in_array($extension, $extensions)) {
					if (!empty($file['tmp_name']) && $file['error'] == 0 && $file['size'] > 0) {
						$filename	= JFile::stripExt($file['name']);
						
						while(JFile::exists($path.$filename.'.'.$extension))
							$filename .= rand(1,999);
						
						if (JFile::upload($file['tmp_name'],$path.$filename.'.'.$extension)) {
							$query->clear()
								->insert($db->qn('#__rsmail_files'))
								->set($db->qn('IdMessage').' = '.$id)
								->set($db->qn('FileName').' = '.$db->q($filename.'.'.$extension));
							$db->setQuery($query);
							$db->execute();
						}
					}
				}
			}
		}
	}
	
	public function addtemplate() {
		// Get the model
		$model = $this->getModel();
		
		// Add new template
		$model->addtemplate();
		
		$this->setRedirect('index.php?option=com_rsmail&task=message.edit&IdMessage='.JFactory::getApplication()->input->getInt('IdMessage',0), JText::_('RSM_TEMPLATE_ADDED'));
	}
}