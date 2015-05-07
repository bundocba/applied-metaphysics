<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controller');

class rsmailControllerAutoresponder extends JControllerForm
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
	
	public function saveajax() {
		$data = JFactory::getApplication()->input->get('jform',array(),'array');
		
		$model = $this->getModel();
		$model->save($data);
		
		echo $model->getState('autoresponder.id');
		JFactory::getApplication()->setUserState('com_rsmail.edit.autoresponder.id', $model->getState('autoresponder.id'));
		JFactory::getApplication()->close();
	}
	
	public function saveplaceholders() {
		$model = $this->getModel();
		$model->saveplaceholders();
		
		echo '<script type="text/javascript">window.parent.location = \''.JRoute::_('index.php?option=com_rsmail&view=autoresponder&layout=edit&IdAutoresponder='.JFactory::getApplication()->input->getInt('IdAutoresponder',0),false).'\';</script>';
		JFactory::getApplication()->close();
	}
	
	public function deletemessage() {
		$model = $this->getModel();
		echo $model->deletemessage();
		JFactory::getApplication()->close();
	}
	
	public function saveorder() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$order	= JFactory::getApplication()->input->get('order',array(),'array');
		$cids	= JFactory::getApplication()->input->get('cid',array(),'array');
		$ida	= JFactory::getApplication()->input->getInt('IdAutoresponder',0);
		
		if (!empty($cids)) {
			foreach($cids as $i => $cid) {
				$query->clear()
					->update($db->qn('#__rsmail_ar_messages'))
					->set($db->qn('ordering').' = '.(int) $order[$i])
					->where($db->qn('IdAutoresponder').' = '.$ida)
					->where($db->qn('IdAutoresponderMessage').' = '.(int) $cid);
				
				$db->setQuery($query);
				$db->execute();
			}
		}

		echo 'ok';
		JFactory::getApplication()->close();
	}
}