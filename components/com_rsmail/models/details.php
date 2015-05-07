<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2010 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.model');

class RSMailModelDetails extends JModelLegacy
{	
	public function __construct() {
		parent::__construct();
	}
	
	public function getData() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$email	= $this->getEmail();
		$return = array();
		
		$query->clear()
			->select($db->qn('IdSubscriber'))->select($db->qn('IdList'))
			->from($db->qn('#__rsmail_subscribers'))
			->where($db->qn('SubscriberEmail').' = '.$db->q($email))
			->where($db->qn('published').' = 1');
		
		$db->setQuery($query);
		$subscribers = $db->loadObjectList();
		
		if (empty($subscribers))
			return;
		
		foreach ($subscribers as $subscriber) {
			$list	= array();
			$fields = array();
			
			$query->clear()->select($db->qn('ListName'))->from($db->qn('#__rsmail_lists'))->where($db->qn('IdList').' = '.(int) $subscriber->IdList);
			$db->setQuery($query);
			$list['name'] = $db->loadResult();
			$list['id'] = $subscriber->IdSubscriber;
			
			$query->clear()
				->select($db->qn('FieldName'))->select($db->qn('FieldValue'))
				->from($db->qn('#__rsmail_subscriber_details'))
				->where($db->qn('IdSubscriber').' = '.(int) $subscriber->IdSubscriber);
			
			$db->setQuery($query);
			if ($listfields = $db->loadObjectList()) {
				foreach ($listfields as $field)
					$fields[$field->FieldName] = $field->FieldValue;
			}
			
			$list['fields'] = $fields;
			$return[] = $list;
		}
		
		return $return;
	}
	
	public function getEmail() {
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$id		= JFactory::getApplication()->input->getString('id',0);
		
		$query->clear()
			->select($db->qn('SubscriberEmail'))
			->from($db->qn('#__rsmail_subscribers'))
			->where('MD5(CONCAT('.$db->qn('SubscriberEmail').','.$db->qn('IdSubscriber').','.$db->qn('IdList').')) = '.$db->q($id));
		
		$db->setQuery($query);
		return $db->loadResult();
	}
}