<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access'); ?>
<script type="text/javascript">
function rsm_select_template(value) {
	if(value != 0) {
		if(confirm('<?php echo JText::_('RSM_CONFIRM_TEMPLATE'); ?>')) {
			window.parent.document.location = '<?php echo JURI::root(); ?>administrator/index.php?option=com_rsmail&view=message&layout=edit&IdTemplate='+value ;
		}
	}
}
</script>

<div class="rsm_message_template">
	<select name="template" id="template" onchange="rsm_select_template(this.value);">
		<option value="0"><?php echo JText::_('RSE_SELECT_TEMPLATE'); ?></option>
		<?php echo JHtml::_('select.options', $this->templates); ?>
	</select>
</div>