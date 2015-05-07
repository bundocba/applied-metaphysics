<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die();
jimport( 'joomla.application.component.model' );

class rsmailModelCronLogs extends JModelLegacy
{
	protected $_query;
	protected $_equery;
	protected $_data;
	protected $_edata;
	protected $_total=null;
	protected $_etotal=null;
	protected $_pagination=null;
	protected $_epagination=null;
	
	function __construct() {
		parent::__construct();
		
		$this->_buildQuery();
		$this->_buildEmailsQuery();
		$app = JFactory::getApplication();

		// Get pagination request variables
		$limit		= $app->getUserStateFromRequest('com_rsmail.cronlogs.limit', 'limit', $app->getCfg('list_limit'), 'int');
		$limitstart = $app->getUserStateFromRequest('com_rsmail.cronlogs.limitstart', 'limitstart', 0, 'int');
		$limite 	= $app->getUserStateFromRequest('com_rsmail.cronlogsemails.limit', 'limit', $app->getCfg('list_limit'), 'int');
		$limitstarte = $app->getUserStateFromRequest('com_rsmail.cronlogsemails.limitstart', 'limitstart', 0, 'int');

		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		$limitstarte = ($limite != 0 ? (floor($limitstarte / $limite) * $limite) : 0);

		$this->setState('com_rsmail.cronlogsemails.limit', $limite);
		$this->setState('com_rsmail.cronlogsemails.limitstart', $limitstarte);
		$this->setState('com_rsmail.cronlogs.limit', $limit);
		$this->setState('com_rsmail.cronlogs.limitstart', $limitstart);
	}
	
	protected function _buildQuery() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		
		$query->clear()
			->select($db->qn('cl.TotalSentEmails'))->select($db->qn('m.MessageSubject'))
			->select($db->qn('s.IdSession'))->select($db->qn('m.MessageName'))
			->select($db->qn('cl.DateAccessed'))
			->from($db->qn('#__rsmail_cron_logs','cl'))
			->join('LEFT',$db->qn('#__rsmail_sessions','s').' ON '.$db->qn('cl.IdSession').' = '.$db->qn('s.IdSession'))
			->join('LEFT',$db->qn('#__rsmail_messages','m').' ON '.$db->qn('s.IdMessage').' = '.$db->qn('m.IdMessage'))
			->group($db->qn('cl.IdSession'))
			->order($db->qn('cl.DateAccessed').' DESC');
		
		$this->_query = $query;
	}
	
	protected function _buildEmailsQuery() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$id		= JFactory::getApplication()->input->getInt('id',0);
		
		$query->clear()
			->select('*')
			->from($db->qn('#__rsmail_cron_logs_emails'))
			->where($db->qn('IdSession').' = '.$id);
		
		$this->_equery = $query;
	}
	
	public function getData() {
		if (empty($this->_data)) {
			$db	= JFactory::getDbo();
			$db->setQuery($this->_query,$this->getState('com_rsmail.cronlogs.limitstart'), $this->getState('com_rsmail.cronlogs.limit'));
			$this->_data = $db->loadObjectList();
		}

		return $this->_data;
	}
	
	public function getCronLogEmails() {
		if (empty($this->_edata)) {
			$db	= JFactory::getDbo();
			$db->setQuery($this->_equery,$this->getState('com_rsmail.cronlogsemails.limitstart'), $this->getState('com_rsmail.cronlogsemails.limit'));
			$this->_edata = $db->loadObjectList();
		}

		return $this->_edata;
	}
	
	public function getTotal() {
		if (empty($this->_total)) {
			$db	= JFactory::getDbo();
			$db->setQuery($this->_query);
			$db->execute();
			$this->_total = $db->getNumRows();
		}
		
		return $this->_total;
	}
	
	public function getEmailsTotal() {
		if (empty($this->_etotal)) {
			$db	= JFactory::getDbo();
			$db->setQuery($this->_equery);
			$db->execute();
			$this->_etotal = $db->getNumRows();
		}
		
		return $this->_etotal;
	}
	
	public function getPagination() {
		if (empty($this->_pagination)) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('com_rsmail.cronlogs.limitstart'), $this->getState('com_rsmail.cronlogs.limit'));
		}
		return $this->_pagination;
	}
	
	public function getCronLogEmailsPagination() {
		if (empty($this->_epagination)) {
			jimport('joomla.html.pagination');
			$this->_epagination = new JPagination($this->getEmailsTotal(), $this->getState('com_rsmail.cronlogsemails.limitstart'), $this->getState('com_rsmail.cronlogsemails.limit'));
		}
		return $this->_epagination;
	}
	
	/**
	 * Method to set the side bar.
	 */
	public function getSidebar() {
		if (rsmailHelper::isJ3()) {
			return JHtmlSidebar::render();
		}
		
		return;
	}
	
	/**
	 * Method to set the filter bar.
	 */
	public function getFilterBar() {
		$layout = JFactory::getApplication()->input->get('layout');
		$options = array();
		$options['orderDir']  = false;
		$options['limitBox']   = $this->getPagination()->getLimitBox();
		
		$bar = new RSFilterBar($options);
		return $bar;
	}
}