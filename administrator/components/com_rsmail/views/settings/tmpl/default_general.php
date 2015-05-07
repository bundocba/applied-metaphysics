<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined( '_JEXEC' ) or die( 'Restricted access' );

$fieldsets = array('general','notifications','quota', 'cron', 'unsubscribe', 'redirects');
$cron_date = $this->config->cron_interval_last_check + ($this->config->cron_interval_check * 60);
$cron_text = !empty($this->config->cron_interval_last_check) ? '<br /> '.JText::_('RSM_CRON_MESSAGE_INFO_6').' <span class="rsm_cron_message">'.rsmailHelper::showDate($cron_date).'</span>' : '';
foreach ($fieldsets as $fieldset) {
	echo JHtml::_('rsfieldset.start', 'adminform', JText::_($this->fieldsets[$fieldset]->label));
	foreach ($this->form->getFieldset($fieldset) as $field) {
		$extra = '';
		if ($field->id == 'jform_cron_period') $extra = '<div class="rsm_message_container">'.JText::_('RSM_CRON_MESSAGE_INFO_1').' <span id="rsm_cron_message_nr" class="rsm_cron_message"></span> '.JText::_('RSM_CRON_MESSAGE_INFO_2').' <span id="rsm_cron_message_min" class="rsm_cron_message"></span> '.JText::_('RSM_CRON_MESSAGE_INFO_3').' <span id="rsm_cron_message_period" class="rsm_cron_message"></span>. <br /> '.JText::_('RSM_CRON_MESSAGE_INFO_4').' <span id="rsm_cron_period" class="rsm_cron_message"></span> '.JText::_('RSM_CRON_MESSAGE_INFO_5').$cron_text.'</div>';
		echo JHtml::_('rsfieldset.element', $field->label, $field->input.$extra);
	}
	echo JHtml::_('rsfieldset.end');
}