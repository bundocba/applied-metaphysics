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

class rsmailControllerSubscribers extends JControllerAdmin
{
	protected $text_prefix = 'COM_RSMAIL_SUBSCRIBERS';
	
	/**
	 * Constructor.
	 *
	 * @param	array	$config	An optional associative array of configuration settings.

	 * @return	rseventsproControllerGroups
	 * @see		JController
	 * @since	1.6
	 */
	public function __construct($config = array()) {
		parent::__construct($config);
	}
	
	/**
	 * Proxy for getModel.
	 *
	 * @param	string	$name	The name of the model.
	 * @param	string	$prefix	The prefix for the PHP class name.
	 *
	 * @return	JModel
	 * @since	1.6
	 */
	public function getModel($name = 'Subscriber', $prefix = 'rsmailModel', $config = array('ignore_request' => true)) {
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}
	
	/**
	 * Export subscribers
	 *
	 * @return	JSON
	 */
	public function export() {
		// Get the model
		$model = $this->getModel('Subscribers');
		
		// Export
		$return = $model->export();
		echo json_encode($return);
		exit();
	}
	
	/**
	 * Download exported file
	 *
	 * @return	file
	 */
	public function getfile() {
		// Get the model
		$model = $this->getModel('Subscribers');
		
		// Download file
		$model->getfile();
		exit();
	}
	
	/**
	 * Unsubscribe subscribers
	 *
	 * @return	void
	 */
	public function unsubscribe() {
		// Get the model
		$model = $this->getModel('Subscribers');
		
		$model->unsubscribe();
		
		$this->setRedirect('index.php?option=com_rsmail&view=subscribers');
	}
	
	/**
	 * Subscribers pagination
	 *
	 * @return
	 */
	public function ajax() {
		// Get the model
		$model = $this->getModel('Subscribers');
		
		echo $model->ajax();
		exit();
	}
	
	/**
	 * Copy/Move subscribers layout
	 *
	 * @return
	 */
	public function copy() {
		// Get the model
		$model = $this->getModel('Subscribers');
		
		$model->copy();
	}
	
	/**
	 * Copy/Move subscribers
	 *
	 * @return	
	 */
	public function copymove() {
		// Get the model
		$model = $this->getModel('Subscribers');
		
		$message = $model->copymove();
		
		$this->setRedirect('index.php?option=com_rsmail&view=subscribers', $message);
	}
	
	/**
	 * Remove subscribers
	 *
	 * @return	
	 */
	public function delete() {
		// Get the model
		$model = $this->getModel('Subscribers');
		
		$message = $model->delete();
		
		$this->setRedirect('index.php?option=com_rsmail&view=subscribers', $message);
	}
	
	/**
	 * Send emails to selected subscribers
	 *
	 * @return	
	 */
	public function send() {
		// Get the model
		$model = $this->getModel('Subscribers');
		$model->send();
		
		$this->setRedirect('index.php?option=com_rsmail&view=messages&filter=1', JText::_('RSM_PLEASE_SELECT_MESSAGE'));
	}
}