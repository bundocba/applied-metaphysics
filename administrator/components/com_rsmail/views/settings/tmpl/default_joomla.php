<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined( '_JEXEC' ) or die( 'Restricted access' );

$fieldsets = array('joomla');
foreach ($fieldsets as $fieldset) {
	echo JHtml::_('rsfieldset.start', 'adminform', JText::_($this->fieldsets[$fieldset]->label));
	foreach ($this->form->getFieldset($fieldset) as $field) {
		echo JHtml::_('rsfieldset.element', $field->label, $field->input);
	}
	
	echo JHtml::_('rsfieldset.element', '<label for="joomla_users">'.JText::_('RSM_CONF_JUR_ADD_USERS').'</label>', '<input type="checkbox" name="joomla_users" id="joomla_users" value="1" />');
	echo JHtml::_('rsfieldset.element', '<label for="joomla_disabled_users">'.JText::_('RSM_CONF_JUR_ADD_USERS_DISABLED').'</label>', '<input type="checkbox" name="joomla_blocked_users" id="joomla_disabled_users" value="1" />');
	echo JHtml::_('rsfieldset.end');
}