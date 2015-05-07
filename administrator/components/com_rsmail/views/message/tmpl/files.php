<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access'); ?>
<script type="text/javascript">
function rsm_actions(action,hasClose,path) 	{
	if (action == 'insert') {
		if (document.getElementById('selectedfile').value == '') {
			alert('<?php echo JText::_('RSM_MESSAGE_NO_FILE_SELECTED',true); ?>');
			return;
		}
		
		var toInsert = document.getElementById('selectedfile').value.replace('administrator/components/com_rsmail/files/','');
		window.parent.rsm_insert(toInsert);
	}
	
	if (action == 'select') {
		$$('.rsm_item span').each(function(el) {
			el.removeClass('rsm_selected');
			if (el.id == path)
				el.addClass('rsm_selected');
		});
		document.getElementById('selectedfile').value = 'administrator/components/com_rsmail/files/' + path;
	}
	
	if (hasClose) {
		window.parent.SqueezeBox.close();
	}
}
</script>

<div style="text-align:right;">
	<button onclick="rsm_actions('insert',1)" type="button" class="btn btn-primary button"><?php echo JText::_('RSM_INSERT_BTN'); ?></button>
	<button onclick="rsm_actions('',1)" type="button" class="btn button"><?php echo JText::_('RSM_CANCEL_BTN'); ?></button>
</div>

<div class="rsm_manager">
	<?php if (!empty($this->files)) { ?>
	<?php foreach ($this->files as $file) { ?>
		<div class="rsm_item">
			<a href="javascript:void(0);" onclick="rsm_actions('select',0,'<?php echo $this->escape($file); ?>')">
				<img width="64" height="64" alt="" src="<?php echo JURI::root(); ?>administrator/components/com_rsmail/assets/images/icons/file.png">
				<span id="<?php echo $this->escape($file); ?>"><?php echo $file; ?></span>
			</a>
		</div>
	<?php } ?>
	<?php } ?>
</div>

<fieldset>
	<?php echo JText::_('RSM_MESSAGE_FILE_URL'); ?> <input type="text" name="selectedfile" id="selectedfile" disabled="disabled" size="80" />
</fieldset>