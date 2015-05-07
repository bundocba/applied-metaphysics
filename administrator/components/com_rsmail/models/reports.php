<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die();
jimport( 'joomla.application.component.model' );

class rsmailModelReports extends JModelLegacy
{
	protected $_query;
	protected $_query_links;
	protected $_query_opens;
	protected $_query_bounce;
	protected $_query_errors;
	protected $_data;
	protected $_data_links;
	protected $_data_opens;
	protected $_data_bounce;
	protected $_data_errors;
	protected $_total=null;
	protected $_total_links=null;
	protected $_total_opens=null;
	protected $_total_bounce=null;
	protected $_total_errors=null;
	protected $_pagination=null;
	protected $_pagination_links=null;
	protected $_pagination_opens=null;
	protected $_pagination_bounce=null;
	protected $_pagination_errors=null;
	protected $_database;
	
	public function __construct() {	
		parent::__construct();
		
		$this->_database = JFactory::getDbo();
		
		$this->_buildQuery();
		$this->_getLinksQuery();
		$this->_getOpensQuery();
		$this->_getBouncesQuery();
		$this->_getErrorsQuery();
		$app = JFactory::getApplication();

		$filter = $app->getUserStateFromRequest('com_rsmail.errors.filter','filter_search','','string' );
		$app->setUserState('com_rsmail.errors.filter',$filter);
		
		// Get pagination request variables
		$limit = $app->getUserStateFromRequest('com_rsmail.reports.limit', 'limit', $app->getCfg('list_limit'), 'int');
		$limitstart = $app->getUserStateFromRequest('com_rsmail.reports.limitstart', 'limitstart', 0, 'int');
		
		$limit_l = $app->getUserStateFromRequest('com_rsmail.links.limit', 'limit', $app->getCfg('list_limit'), 'int');
		$limitstart_l = $app->getUserStateFromRequest('com_rsmail.links.limitstart', 'limitstart', 0, 'int');

		$limit_o = $app->getUserStateFromRequest('com_rsmail.opens.limit', 'limit', $app->getCfg('list_limit'), 'int');
		$limitstart_o = $app->getUserStateFromRequest('com_rsmail.opens.limitstart', 'limitstart', 0, 'int');

		$limit_b = $app->getUserStateFromRequest('com_rsmail.bounce.limit', 'limit', $app->getCfg('list_limit'), 'int');
		$limitstart_b = $app->getUserStateFromRequest('com_rsmail.bounce.limitstart', 'limitstart', 0, 'int');

		$limit_e = $app->getUserStateFromRequest('com_rsmail.errors.limit', 'limit', $app->getCfg('list_limit'), 'int');
		$limitstart_e = $app->getUserStateFromRequest('com_rsmail.errors.limitstart', 'limitstart', 0, 'int');

		// In case limit has been changed, adjust it
		 $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		 $limitstart_l = ($limit_l != 0 ? (floor($limitstart_l / $limit_l) * $limit_l) : 0);
		 $limitstart_o = ($limit_o != 0 ? (floor($limitstart_o / $limit_o) * $limit_o) : 0);
		 $limitstart_b = ($limit_b != 0 ? (floor($limitstart_b / $limit_b) * $limit_b) : 0);
		 $limitstart_e = ($limit_e != 0 ? (floor($limitstart_e / $limit_e) * $limit_e) : 0);

		$this->setState('com_rsmail.reports.limit', $limit);
		$this->setState('com_rsmail.reports.limitstart', $limitstart);
		
		$this->setState('com_rsmail.links.limit', $limit_l);
		$this->setState('com_rsmail.links.limitstart', $limitstart_l);
		
		$this->setState('com_rsmail.opens.limit', $limit_o);
		$this->setState('com_rsmail.opens.limitstart', $limitstart_o);
		
		$this->setState('com_rsmail.bounce.limit', $limit_b);
		$this->setState('com_rsmail.bounce.limitstart', $limitstart_b);
		
		$this->setState('com_rsmail.errors.limit', $limit_e);
		$this->setState('com_rsmail.errors.limitstart', $limitstart_e);
	}
	
	protected function _buildQuery() {	
		$query = $this->_database->getQuery(true);
		
		$query->clear()
			->select($this->_database->qn('s.IdSession'))->select($this->_database->qn('s.IdMessage'))->select($this->_database->qn('s.Date'))
			->select($this->_database->qn('s.Lists'))->select($this->_database->qn('s.Position'))->select($this->_database->qn('s.MaxEmails'))
			->select($this->_database->qn('m.MessageName'))->select($this->_database->qn('m.MessageSubject'))
			->from($this->_database->qn('#__rsmail_sessions','s'))
			->join('LEFT',$this->_database->qn('#__rsmail_messages','m').' ON '.$this->_database->qn('m.IdMessage').' = '.$this->_database->qn('s.IdMessage'))
			->order($this->_database->qn('s.Date').' DESC');
		
		$this->_query = $query;
	}
	
	protected function _getLinksQuery() {
		$query	= $this->_database->getQuery(true);
		$id		= JFactory::getApplication()->input->getInt('id',0);
		$unique = JFactory::getApplication()->input->getInt('unique',0);
		$filter = JFactory::getApplication()->input->getString('filter_search','');
		
		$query->clear()
			->select($this->_database->qn('s.SubscriberEmail'))->select($this->_database->qn('r.Url'))
			->select($this->_database->qn('sc.date'))->select($this->_database->qn('sc.ip'))
			->from($this->_database->qn('#__rsmail_subscribers','s'))
			->join('LEFT',$this->_database->qn('#__rsmail_subscribers_clicks','sc').' ON '.$this->_database->qn('sc.IdSubscriber').' = '.$this->_database->qn('s.IdSubscriber'))
			->join('LEFT',$this->_database->qn('#__rsmail_reports','r').' ON '.$this->_database->qn('r.IdReport').' = '.$this->_database->qn('sc.IdReport'))
			->where($this->_database->qn('r.IdSession').' = '.$id);
		
		if (!empty($filter))
			$query->where($this->_database->qn('s.SubscriberEmail').' LIKE '.$this->_database->q('%'.$filter.'%'));
		
		if ($unique)
			$query->group($this->_database->qn('r.Url'));
		
		$this->_query_links = $query;
	}
	
	protected function _getOpensQuery() {
		$query	= $this->_database->getQuery(true);
		$id		= JFactory::getApplication()->input->getInt('id',0);
		$unique = JFactory::getApplication()->input->getInt('unique',0);
		$filter = JFactory::getApplication()->input->getString('filter_search','');
		
		$query->clear()
			->select($this->_database->qn('s.SubscriberEmail'))->select($this->_database->qn('so.date'))
			->select($this->_database->qn('so.ip'))
			->from($this->_database->qn('#__rsmail_subscribers','s'))
			->join('LEFT',$this->_database->qn('#__rsmail_subscribers_opens','so').' ON '.$this->_database->qn('so.IdSubscriber').' = '.$this->_database->qn('s.IdSubscriber'))
			->where($this->_database->qn('so.IdSession').' = '.$id);
		
		if (!empty($filter))
			$query->where($this->_database->qn('s.SubscriberEmail').' LIKE '.$this->_database->q('%'.$filter.'%'));
		
		if ($unique)
			$query->group($this->_database->qn('so.IdSubscriber'));
		
		$this->_query_opens	= $query;
	}
	
	protected function _getBouncesQuery() {
		$query	= $this->_database->getQuery(true);
		$id		= JFactory::getApplication()->input->getInt('id',0);
		$filter = JFactory::getApplication()->input->getString('filter_search','');
		
		$query->clear()
			->select('*')
			->from($this->_database->qn('#__rsmail_bounce_emails'))
			->where($this->_database->qn('IdSession').' = '.$id)
			->where($this->_database->qn('Email').' LIKE '.$this->_database->q('%'.$filter.'%'));
		
		$this->_query_bounce = $query;
	}
	
	protected function _getErrorsQuery() {
		$query	= $this->_database->getQuery(true);
		$id		= JFactory::getApplication()->input->getInt('id',0);
		$filter = JFactory::getApplication()->getUserStateFromRequest('com_rsmail.errors.filter','filter_search','','string');
		
		$query->clear()
			->select('e.*')->select($this->_database->qn('s.SubscriberEmail'))
			->from($this->_database->qn('#__rsmail_errors','e'))
			->join('LEFT',$this->_database->qn('#__rsmail_subscribers','s').' ON '.$this->_database->qn('s.IdSubscriber').' = '.$this->_database->qn('e.IdSubscriber'))
			->where($this->_database->qn('e.IdSession').' = '.$id)
			->where($this->_database->qn('s.SubscriberEmail').' LIKE '.$this->_database->q('%'.$filter.'%'));
		
		
		$this->_query_errors = $query;
	}
	
	public function getData() {
		if (empty($this->_data)) {
			$this->_database->setQuery($this->_query,$this->getState('com_rsmail.reports.limitstart'), $this->getState('com_rsmail.reports.limit'));
			$this->_data = $this->_database->loadObjectList();
		}
		
		return $this->_data;
	}
	
	public function getLinks() {
		if (empty($this->_data_links)) {
			$this->_database->setQuery($this->_query_links,$this->getState('com_rsmail.links.limitstart'), $this->getState('com_rsmail.links.limit'));
			$this->_data_links = $this->_database->loadObjectList();
		}
		
		return $this->_data_links;
	}
	
	public function getOpens() {
		if (empty($this->_data_opens)) {
			$this->_database->setQuery($this->_query_opens,$this->getState('com_rsmail.opens.limitstart'), $this->getState('com_rsmail.opens.limit'));
			$this->_data_opens = $this->_database->loadObjectList();
		}
		
		return $this->_data_opens;
	}
	
	public function getBounces() {
		if (empty($this->_data_bounce)) {
			$this->_database->setQuery($this->_query_bounce,$this->getState('com_rsmail.bounce.limitstart'), $this->getState('com_rsmail.bounce.limit'));
			$this->_data_bounce = $this->_database->loadObjectList();
		}
		
		return $this->_data_bounce;
	}
	
	public function getErrors() {
		if (empty($this->_data_errors)) {
			$this->_database->setQuery($this->_query_errors,$this->getState('com_rsmail.errors.limitstart'), $this->getState('com_rsmail.errors.limit'));
			$this->_data_errors = $this->_database->loadObjectList();
		}
		
		return $this->_data_errors;
	}
	
	public function getTotal() {
		if (empty($this->_total)) {
			$this->_database->setQuery($this->_query);
			$this->_database->execute();
			$this->_total = $this->_database->getNumRows();
		}
		
		return $this->_total;
	}
	
	public function getTotalLinks() {
		if (empty($this->_total_links)) {
			$this->_database->setQuery($this->_query_links);
			$this->_database->execute();
			$this->_total_links = $this->_database->getNumRows();
		}
		
		return $this->_total_links;
	}
	
	public function getTotalOpens() {
		if (empty($this->_total_opens)) {
			$this->_database->setQuery($this->_query_opens);
			$this->_database->execute();
			$this->_total_opens = $this->_database->getNumRows();
		}
		
		return $this->_total_opens;
	}
	
	public function getTotalBounces() {
		if (empty($this->_total_bounce)) {
			$this->_database->setQuery($this->_query_bounce);
			$this->_database->execute();
			$this->_total_bounce = $this->_database->getNumRows();
		}
		
		return $this->_total_bounce;
	}
	
	public function getTotalErrors() {
		if (empty($this->_total_errors)) {
			$this->_database->setQuery($this->_query_errors);
			$this->_database->execute();
			$this->_total_errors = $this->_database->getNumRows();
		}
		
		return $this->_total_errors;
	}
	
	public function getPagination() {
		if (empty($this->_pagination)) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('com_rsmail.reports.limitstart'), $this->getState('com_rsmail.reports.limit'));
		}
		return $this->_pagination;
	}
	
	public function getPaginationLinks() {
		if (empty($this->_pagination_links)) {
			jimport('joomla.html.pagination');
			$this->_pagination_links = new JPagination($this->getTotalLinks(), $this->getState('com_rsmail.links.limitstart'), $this->getState('com_rsmail.links.limit'));
		}
		return $this->_pagination_links;
	}
	
	public function getPaginationOpens() {
		if (empty($this->_pagination_opens)) {
			jimport('joomla.html.pagination');
			$this->_pagination_opens = new JPagination($this->getTotalOpens(), $this->getState('com_rsmail.opens.limitstart'), $this->getState('com_rsmail.opens.limit'));
		}
		return $this->_pagination_opens;
	}
	
	public function getPaginationBounces() {	
		if (empty($this->_pagination_bounce)) {
			jimport('joomla.html.pagination');
			$this->_pagination_bounce = new JPagination($this->getTotalBounces(), $this->getState('com_rsmail.bounce.limitstart'), $this->getState('com_rsmail.bounce.limit'));
		}
		return $this->_pagination_bounce;
	}
	
	public function getPaginationErrors() {
		if (empty($this->_pagination_errors)) {
			jimport('joomla.html.pagination');
			$this->_pagination_errors = new JPagination($this->getTotalErrors(), $this->getState('com_rsmail.errors.limitstart'), $this->getState('com_rsmail.errors.limit'));
		}
		return $this->_pagination_errors;
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
		
		if ($layout == 'errors') {
			$options['limitBox']   = $this->getPaginationErrors()->getLimitBox();
			$options['search'] = array(
				'label' => JText::_('JSEARCH_FILTER'),
				'value' => JFactory::getApplication()->getUserStateFromRequest('com_rsmail.errors.filter','filter_search','','string')
			);
		} else if ($layout == 'opens') {
			$options['limitBox']   = $this->getPaginationOpens()->getLimitBox();
			$options['search'] = array(
				'label' => JText::_('JSEARCH_FILTER'),
				'value' => JFactory::getApplication()->input->getString('filter_search','')
			);
		} else if ($layout == 'links') {
			$options['limitBox']   = $this->getPaginationLinks()->getLimitBox();
			$options['search'] = array(
				'label' => JText::_('JSEARCH_FILTER'),
				'value' => JFactory::getApplication()->input->getString('filter_search','')
			);
		} else if ($layout == 'bounce') {
			$options['limitBox']   = $this->getPaginationBounces()->getLimitBox();
			$options['search'] = array(
				'label' => JText::_('JSEARCH_FILTER'),
				'value' => JFactory::getApplication()->input->getString('filter_search','')
			);
		} else {
			$options['limitBox']   = $this->getPagination()->getLimitBox();
		}
		
		$bar = new RSFilterBar($options);
		return $bar;
	}
	
	public function getUrlDetails() {
		$query	= $this->_database->getQuery(true);
		$id		= JFactory::getApplication()->input->getInt('id',0);
		
		$query->clear()->select('*')->from($this->_database->qn('#__rsmail_reports'))->where($this->_database->qn('IdSession').' = '.$id);
		$this->_database->setQuery($query);
		return $this->_database->loadObjectList();
	}
	
	public function getReportDetails() {
		$query	= $this->_database->getQuery(true);
		$id		= JFactory::getApplication()->input->getInt('id',0);
		$result = array();
		
		// Get session lists
		$query->clear()
			->select($this->_database->qn('Lists'))->select($this->_database->qn('LinkHistory'))
			->select($this->_database->qn('OpensHistory'))->select($this->_database->qn('Delivery'))
			->from($this->_database->qn('#__rsmail_sessions'))
			->where($this->_database->qn('IdSession').' = '.$id);
		
		$this->_database->setQuery($query);
		$session = $this->_database->loadObject();
		$lists = $session->Lists;
		
		// Get maximum number of emails
		$query->clear()
			->select('COUNT(DISTINCT '.$this->_database->qn('SubscriberEmail').')')
			->from($this->_database->qn('#__rsmail_subscribers'))
			->where($this->_database->qn('IdList').' IN ('.$lists.')');
		
		$this->_database->setQuery($query);
		$max = $this->_database->loadResult();
		
		// Get unique opens
		$query->clear()
			->select('COUNT(DISTINCT '.$this->_database->qn('IdSubscriber').')')
			->from($this->_database->qn('#__rsmail_subscribers_opens'))
			->where($this->_database->qn('IdSession').' = '.$id);
		
		$this->_database->setQuery($query);
		$uniqueopens = $this->_database->loadResult();
		
		// Get details
		$query->clear()
			->select($this->_database->qn('s.Date'))->select($this->_database->qn('s.DeliverDate'))->select($this->_database->qn('s.Counter'))
			->select($this->_database->qn('s.UnsubscribeCounter'))->select($this->_database->qn('s.MessageCounterSent'))->select($this->_database->qn('s.MaxEmails'))
			->select($this->_database->qn('s.BounceNumber'))->select($this->_database->qn('m.MessageName'))->select($this->_database->qn('m.MessageSubject'))
			->from($this->_database->qn('#__rsmail_sessions','s'))
			->join('LEFT', $this->_database->qn('#__rsmail_messages','m').' ON '.$this->_database->qn('m.IdMessage').' = '.$this->_database->qn('s.IdMessage'))
			->where($this->_database->qn('s.IdSession').' = '.$id);
		
		$this->_database->setQuery($query);
		$details = $this->_database->loadObject();
		
		// Get number of errors
		$query->clear()
			->select('COUNT('.$this->_database->qn('id').')')
			->from($this->_database->qn('#__rsmail_errors'))
			->where($this->_database->qn('IdSession').' = '.$id);
		
		$this->_database->setQuery($query);
		$errors = $this->_database->loadResult();
		
		$result['max']				= (empty($details->MaxEmails)) ? $max : $details->MaxEmails;
		$result['date']				= ($details->DeliverDate == 0 ? $details->Date : $details->DeliverDate);
		$result['subject']			= @$details->MessageSubject;
		$result['name']				= @$details->MessageName;
		$result['opens']			= @$details->Counter;
		$result['uniqueopens']		= @$uniqueopens;
		$result['unsubscribers']	= @$details->UnsubscribeCounter;
		$result['sentmessages']		= @$details->MessageCounterSent;
		$result['bounce']			= @$details->BounceNumber;
		$result['errors']			= $errors;
		$result['linkhistory']		= $session->LinkHistory;
		$result['openshistory']		= $session->OpensHistory;
		$result['Delivery']			= $session->Delivery;
		$result['IdSession']		= $id;
		
		return $result;
	}
}