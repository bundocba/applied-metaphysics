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
 * JFormFieldJfields Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	com_rsmail
 * @since		1.6
 */
class JFormFieldFields extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'Fields';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput() {
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$id		= JFactory::getApplication()->input->getInt('id',0);
		
		$query->clear()->select($db->qn('params'))->from($db->qn('#__modules'))->where($db->qn('id').' = '.$id);
		$db->setQuery($query);
		$params = $db->loadResult();
		
		$regisrty = new JRegistry;
		$regisrty->loadString($params);
		
		$lists = $regisrty->get('listid','');
		$multiple = $regisrty->get('enablemultiple',0);
		
		if ($multiple == 0 && count($lists) > 1)
			return JText::_('RSM_MODULE_SAVE_FIRST');
		
		$result = '<div style="float:left;">';
		
		if(!empty($lists)) {
			if(is_array($lists)) {
				foreach($lists as $listid) {
					
					$query->clear()
						->select($db->qn('IdListFields'))->select($db->qn('FieldName'))
						->from($db->qn('#__rsmail_list_fields'))
						->where($db->qn('IdList').' = '.(int) $listid)
						->order($db->qn('ordering').' ASC');
					
					$db->setQuery($query);
					$options = $db->loadObjectList();
					
					$query->clear()->select($db->qn('ListName'))->from($db->qn('#__rsmail_lists'))->where($db->qn('IdList').' = '.(int) $listid);
					$db->setQuery($query);
					$listname = $db->loadResult();
				
					if (!empty($options)) {
						$result .= '<select style="vertical-align: middle;float:none;" size="8" multiple="multiple" name="'.$this->name.'[]" id="'.$this->id.$listid.'">';
						
						foreach( $options as $option ) {
							if(is_array( $this->value) ) {
								if( in_array( $option->IdListFields, $this->value ) ) {
									$result .= '<option selected="true" value="'.$option->IdListFields.'" >'.$option->FieldName.'</option>';
								} else {
									$result .= '<option value="'.$option->IdListFields.'" >'.$option->FieldName.'</option>';
								}
							} elseif ( $this->value ) {
								if( $this->value == $option->IdListFields ) {
									$result .= '<option selected="true" value="'.$option->IdListFields.'" >'.$option->FieldName.'</option>';
								} else {
									$result .= '<option value="'.$option->IdListFields.'" >'.$option->FieldName.'</option>';
								}
							} elseif ( !( $this->value ) ) {
								$result .= '<option value="'.$option->IdListFields.'" >'.$option->FieldName.'</option>';
							}
						}
						$result .= '</select> <span style="text-align: left;vertical-align: super;"> -> '.$listname.'</span><div style="clear:both;"></div>';
					}
				} 
			} else {
				$query->clear()
					->select($db->qn('IdListFields'))->select($db->qn('FieldName'))
					->from($db->qn('#__rsmail_list_fields'))
					->where($db->qn('IdList').' = '.(int) $lists)
					->order($db->qn('ordering').' ASC');
				
				$db->setQuery($query);
				$options = $db->loadObjectList();
			
				if(!empty($options)) {
					$result .= '<select size="8" multiple="multiple" style="vertical-align: middle;" name="'.$this->name.'][]" id="'.$this->name.'">';
					
					foreach( $options as $option ) {
						if(is_array( $this->value) ) {
							if( in_array( $option->IdListFields, $this->value ) ) {
								$result .= '<option selected="true" value="'.$option->IdListFields.'" >'.$option->FieldName.'</option>';
							} else {
								$result .= '<option value="'.$option->IdListFields.'" >'.$option->FieldName.'</option>';
							}
						} elseif ( $value ) {
							if( $this->value == $option->IdListFields ) {
								$result .= '<option selected="true" value="'.$option->IdListFields.'" >'.$option->FieldName.'</option>';
							} else {
								$result .= '<option value="'.$option->IdListFields.'" >'.$option->FieldName.'</option>';
							}
						} elseif ( !( $this->value ) ) {
							$result .= '<option value="'.$option->IdListFields.'" >'.$option->FieldName.'</option>';
						}
					}
					$result .= '</select>';	
				}
			}
			$result .= '</div>';
			
			return $result;
		} else {
			return JText::_('RSM_MODULE_SELECT_LIST_SAVE');
		}
	}
}