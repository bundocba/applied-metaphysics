<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access'); 
JHTML::_('behavior.keepalive');

if ($this->action == 'edit') { ?>
<form>
	<input type="text" size="40" id="edit_field<?php echo $this->id; ?>" name="edit_field<?php echo $this->id; ?>" value="<?php echo $this->field->FieldName; ?>" class="rsm_input rsm_edit_inp" style="margin-bottom: 0px;" />
	<button type="button" class="btn btn-primary rsm_save_edit"><?php echo JText::_('RSM_SAVE_BTN'); ?></button>
	<button type="button" class="btn" id="rsm_cancel_edit" rel="cancel"><?php echo JText::_('RSM_CANCEL_BTN'); ?></button>
	<input type="hidden" name="IdList" value="<?php echo $this->field->IdList; ?>" />
	<input type="hidden" name="cid" value="<?php echo $this->id; ?>" id="FieldId" />
	<input type="hidden" name="option" value="com_rsmail" />
</form>
<?php } else if ($this->action == 'cancel') { ?>
<a href="javascript:void(0);" class="rsm_edit_field" rel="edit"><?php echo $this->field->FieldName; ?></a>
<?php } ?>
<?php JFactory::getApplication()->close(); ?>