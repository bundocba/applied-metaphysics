<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controller');

class rsmailControllerList extends JControllerForm
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
	
	public function savefieldsorder() {
		JTable::addIncludePath(JPATH_COMPONENT.'/tables');
		$table	= JTable::getInstance('Fields','rsmailTable');
		$jinput	= JFactory::getApplication()->input;
		$cids 	= $jinput->get('cid', array(),'array');
		$order 	= $jinput->get('order',array(),'array');

		for ($i=0; $i < count($cids); $i++) {
			if(is_numeric($cids[$i])) {
				$table->load($cids[$i]);

				// Set the new ordering only if different
				if ($table->ordering != $order[$i]) {
					$table->ordering = $order[$i];
					$table->store();
				}
			}
		}
		JFactory::getApplication()->close();
	}
	
	public function clearlist() {
		// Get the model
		$model = $this->getModel();
		
		// Clear list
		$model->clearlist();
		
		echo JText::_('RSM_SUBSCRIBERS_LIST_EMPTY',true);
		exit();
	}
	
	public function deletefield() {
		// Get the model
		$model = $this->getModel();
		
		// Delete field
		$model->deletefield();
		
		exit();
	}
	
	public function savefield() {
		// Get the model
		$model = $this->getModel();
		
		// Delete field
		$response = $model->savefield();
		
		echo json_encode($response);
		exit();
	}
}