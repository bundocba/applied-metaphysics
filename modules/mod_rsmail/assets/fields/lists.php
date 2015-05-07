<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('JPATH_BASE') or die;
jimport('joomla.form.formfield');

/**
 * JFormFieldJlists Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	com_rsmail
 * @since		1.6
 */
class JFormFieldLists extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'Lists';

	
	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput() {
		$options	= $this->getOptions();
		$multiple	= $this->getMultiple();
		
		$html = '<select '.($multiple ? 'multiple="multiple" size="3"' : 'size="1"').' name="'.$this->name.'[]" id="'.$this->name.'" class="inputbox">';
		foreach($options as $option) {
			if(is_array( $this->value) ) {
				if( in_array( $option->IdList, $this->value ) ) {
					$html .= '<option selected="true" value="'.$option->IdList.'" >'.$option->ListName.'</option>';
				} else {
					$html .= '<option value="'.$option->IdList.'" >'.$option->ListName.'</option>';
				}
			} elseif ( $this->value ) {
				if( $this->value == $option->IdList ) {
					$html .= '<option selected="true" value="'.$option->IdList.'" >'.$option->ListName.'</option>';
				} else {
					$html .= '<option value="'.$option->IdList.'" >'.$option->ListName.'</option>';
				}
			} elseif ( !( $this->value ) ) {
				$html .= '<option value="'.$option->IdList.'" >'.$option->ListName.'</option>';
			}
		}
		$html .= '</select>';
		
		return $html;
	}
	
	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions() {
		$options	= array();
		$db			= JFactory::getDbo();
		$query		= $db->getQuery(true);
		
		$query->clear()->select('*')->from($db->qn('#__rsmail_lists'));
		$db->setQuery($query);
		$options = $db->loadObjectList();
		
		reset($options);
		return $options;
	}
	
	protected function getMultiple() {
		$db			= JFactory::getDbo();
		$query		= $db->getQuery(true);
		$id			= JFactory::getApplication()->input->getInt('id',0);
		
		$query->clear()->select($db->qn('params'))->from($db->qn('#__modules'))->where($db->qn('id').' = '.$id);
		$db->setQuery($query);
		$params = $db->loadResult();
		
		$registry = new JRegistry;
		$registry->loadString($params);
		
		return $registry->get('enablemultiple',0) == 1;
	}
}