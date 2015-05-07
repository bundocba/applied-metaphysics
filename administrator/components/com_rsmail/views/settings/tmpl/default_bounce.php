<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined( '_JEXEC' ) or die( 'Restricted access' );

$fieldsets = array('bounce_details', 'bounce_rules'); 
foreach ($fieldsets as $fieldset) {
	echo JHtml::_('rsfieldset.start', 'adminform', JText::_($this->fieldsets[$fieldset]->label));
	
	if ($fieldset == 'bounce_details')
		echo JHtml::_('rsfieldset.element', '', '<button type="button" class="btn btn-info button" onclick="Joomla.submitbutton(\'settings.test\')">'.JText::_('RSM_TEST_CONNECTION').'</button>');
	
	foreach ($this->form->getFieldset($fieldset) as $field) {
		$extra = '';
		if ($field->id == 'jform_bounce_mail_connection') $extra = '<div class="rsm_message_container"><span id="rsm_connection" style="display:none;"><img src="'.JURI::root().'administrator/components/com_rsmail/assets/images/icons/notice.png" style="vertical-align: middle;" /> '.JText::_('RSM_POP3_NOTICE').'</span></div>';
		
		if ($field->id == 'jform_bounce_handle') $extra = '<div class="rsm_message_container"><span id="manual_message"><strong>'.JText::_('RSM_BOUNCE_MANUAL_TEXT').'</strong> - <font color="red">'.JText::_('RSM_BOUNCE_MANUAL_WARNING').'</font></span> <span id="cron_message"><strong>'.JText::_('RSM_BOUNCE_CRON_TEXT').'</strong> <a href="'.JURI::root().'index.php?option=com_rsmail&task=bounce">'.JURI::root().'index.php?option=com_rsmail&task=bounce</a></span></div>';
		
		echo JHtml::_('rsfieldset.element', $field->label, $field->input.$extra);
	}
	echo JHtml::_('rsfieldset.end');
}