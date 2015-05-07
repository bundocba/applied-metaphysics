<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Platform.
 * Implements a combo box field.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldRSLists extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'RSLists';

	/**
	 * Method to get the field input markup for a combo box field.
	 *
	 * @return  string   The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getOptions() {
		$db			= JFactory::getDbo();
		$query		= $db->getQuery(true);
		$default	= isset($this->element['select']) ? $this->element['select'] : false;
		
		$defaultOption = array(JHTML::_('select.option', 0, JText::_('RSM_SELECT_LISTS')));
		
		$query->clear()
			->select($db->qn('IdList','value'))->select($db->qn('ListName','text')) 
			->from($db->qn('#__rsmail_lists'));
		
		$db->setQuery($query);
		$options = $db->loadObjectList();
		
		return $default ? array_merge($defaultOption,$options) : $options;
	}
}