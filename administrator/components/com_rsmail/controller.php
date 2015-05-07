<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined( '_JEXEC' ) or die( 'Restricted access' ); 
jimport('joomla.application.component.controller');

class RSMailController extends JControllerLegacy
{
	var $_db;
	
	public function __construct() {
		parent::__construct();
		
		// Set the table directory
		JTable::addIncludePath(JPATH_COMPONENT.'/tables');
	}
	
	/**
	 * Method to display a view.
	 *
	 * @param	boolean			If true, the view output will be cached
	 * @param	array			An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController		This object to support chaining.
	 * @since	1.5
	 */
	public function display($cachable = false, $urlparams = false) {
		// Add the submenu
		rsmailHelper::subMenu();
		
		parent::display();
		return $this;
	}
	
	/**
	 *	Method to display the RSMail! Dashboard
	 *
	 * @return void
	 */
	public function rsmail() {		
		return $this->setRedirect('index.php?option=com_rsmail');
	}
	
	public function jsonfields() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$app	= JFactory::getApplication();
		$jinput	= $app->input;
		$IdList = $jinput->getInt('IdList',0);
		$action = $jinput->get('action','');
		$ignore	= $jinput->getInt('ignore',0);
		
		$query->clear()
			->select($db->qn('IdListFields'))->select($db->qn('FieldName'))
			->from($db->qn('#__rsmail_list_fields'))
			->where($db->qn('IdList').' = '.$IdList)
			->order($db->qn('ordering').' ASC');
		
		$db->setQuery($query);
		$fields = $db->loadObjectList();
		
		if ($ignore) {
			$no_filter = new stdClass();
			$no_filter->IdListFields = 0;
			$no_filter->FieldName = JText::_('RSM_IGNORE');
		} else {
			// adding No Filter
			$no_filter = new stdClass();
			$no_filter->IdListFields = 0;
			$no_filter->FieldName = JText::_('RSM_NO_FILTER');
		}

		// adding email field in fields filter
		if($action == 'filter_subscribers') {
			$email_filter = new stdClass();
			$email_filter->IdListFields = 'email';
			$email_filter->FieldName = JText::_('RSM_EMAIL');
			array_unshift($fields, $email_filter);
		}
		array_unshift($fields, $no_filter);

		echo json_encode($fields);
		$app->close();
	}
	
	public function content() {
		jimport('joomla.plugin.helper');
		$plugin = JFactory::getApplication()->input->get('plugin');
		JPluginHelper::importPlugin('rsmail',$plugin);
		
		JFactory::getApplication()->triggerEvent('rsm_showPlaceholderContent');
	}
	
	public function send() {
		$input = JFactory::getApplication()->input;
		
		$input->set('view','send');
		$input->set('layout','default');
		parent::display();
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	function subscribers()
	{
		JRequest::setVar('view','subscribers');
		// reset the pagination if page is refreshed
		JRequest::setVar('limitstart','0');
		
		JRequest::setVar('layout','default');
		parent::display();
	}
	
	function editsubscriber()
	{
		JRequest::setVar('view','subscribers');
		JRequest::setVar('layout','edit');
		parent::display();
	}
	
	function import()
	{
		JRequest::setVar('view','import');
		JRequest::setVar('layout','default');
		parent::display();
	}
	
	function importfile()
	{
		JRequest::setVar('view','import');
		JRequest::setVar('layout','importfile');
		parent::display();
	}
	
	function messages()
	{
		JRequest::setVar('view','messages');
		JRequest::setVar('layout','default');
		parent::display();
	}
	
	function editmessage()
	{
		JRequest::setVar('view','messages');
		JRequest::setVar('layout','edit');
		parent::display();
	}
	
	function reports()
	{
		JRequest::setVar('view','reports');
		JRequest::setVar('layout','default');
		parent::display();
	}

	function templates()
	{
		JRequest::setVar('view','templates');
		JRequest::setVar('layout','default');
		parent::display();
	}
	
	function edittemplate()
	{
		JRequest::setVar('view','templates');
		JRequest::setVar('layout','edit');
		parent::display();
	}
	
	function fromtemplate()
	{
		JRequest::setVar('view','messages');
		JRequest::setVar('layout','fromtemplate');
		parent::display();
	}
	
	function fromarticle()
	{
		JRequest::setVar('view','messages');
		JRequest::setVar('layout','fromarticle');
		JRequest::setVar('tmpl','component');
		parent::display();
	}
	
	function fromkarticle()
	{
		JRequest::setVar('view','messages');
		JRequest::setVar('layout','fromkarticle');
		JRequest::setVar('tmpl','component');
		parent::display();
	}
	
	function sendmessages()
	{
		JRequest::setVar('view','send');
		JRequest::setVar('layout','sendmessages');
		parent::display();
	}
	
	function sessions()
	{
		JRequest::setVar('view','sessions');
		JRequest::setVar('layout','default');
		parent::display();
	}
	
	function viewreport()
	{
		JRequest::setVar('view','reports');
		JRequest::setVar('layout','edit');
		parent::display();
	}
	
	function showlinks()
	{
		JRequest::setVar('view','reports');
		JRequest::setVar('layout','links');
		parent::display();
	}
	
	function unsubscribers()
	{
		JRequest::setVar('view','unsubscribers');
		JRequest::setVar('layout','default');
		parent::display();
	}
	
	function editunsubscriber()
	{
		JRequest::setVar('view','unsubscribers');
		JRequest::setVar('layout','edit');
		parent::display();
	}
	
	function integrations()
	{
		JRequest::setVar('view','integrations');
		JRequest::setVar('layout','default');
		parent::display();
	}
	
	function editintegration()
	{
		JRequest::setVar('view','integrations');
		JRequest::setVar('layout','edit');
		parent::display();
	}
	
	function addrsmembership()
	{
		JRequest::setVar('view','integrations');
		JRequest::setVar('IntegrationType','rsmembership');
		JRequest::setVar('layout','edit');
		parent::display();
	}
	
	function autoresponders()
	{
		JRequest::setVar('view','autoresponders');
		JRequest::setVar('layout','default');
		parent::display();
	}
	
	function editautoresponder()
	{
		JRequest::setVar('view','autoresponders');
		JRequest::setVar('layout','edit');
		parent::display();
	}
	
	function addmessage()
	{
		JRequest::setVar('view','autoresponders');
		JRequest::setVar('layout','message');
		parent::display();
	}
	
	
	function bounce()
	{
		JRequest::setVar('view','reports');
		JRequest::setVar('layout','bounce');
		parent::display();
	}
	
	function errors()
	{
		JRequest::setVar('view','reports');
		JRequest::setVar('layout','errors');
		parent::display();
	}
	
	function placeholders()
	{
		JRequest::setVar('view','templates');
		JRequest::setVar('layout','placeholders');
		JRequest::setVar('tmpl','component');
		parent::display();
	}
	
	function addfiles()
	{
		JRequest::setVar('view','messages');
		JRequest::setVar('layout','addfiles');
		JRequest::setVar('tmpl','component');
		parent::display();
	}
	
	function showopens()
	{
		JRequest::setVar('view','reports');
		JRequest::setVar('layout','opens');
		parent::display();
	}
	
	function addrsfp()
	{
		JRequest::setVar('view','integrations');
		JRequest::setVar('IntegrationType','rsfp');
		JRequest::setVar('layout','edit');
		parent::display();
	}
	
	function cronlogs()
	{
		JRequest::setVar('view','cronlogs');
		JRequest::setVar('layout','default');
		parent::display();
	}
	
	function viewcronlogemails(){
		JRequest::setVar('view','cronlogs');
		JRequest::setVar('layout','viewcronlogemails');
		parent::display();
	}
	
	function filtered_lists()
	{
		JRequest::setVar('view','subscribers');
		JRequest::setVar('layout','filtered_lists');
		JRequest::setVar('tmpl','component');

		parent::display();
	}
}