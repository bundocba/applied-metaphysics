<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.keepalive'); ?>

<h1><?php echo JText::sprintf('RSM_DETAILS_FOR',$this->email); ?></h1>
<form action="<?php echo JRoute::_('index.php?option=com_rsmail&task=details',false); ?>" method="post" class="rsm_details">
	<?php if (!empty($this->lists)) { ?>
	<?php foreach ($this->lists as $i => $list) { ?>
	<fieldset>
		<legend><?php echo $list['name']; ?></legend>
		<?php if (!empty($list['fields'])) { ?>
		<table class="rsm_table" width="100%" cellspacing="0" cellpadding="0">
		<?php foreach ($list['fields'] as $name => $value) { ?>
			<tr>
				<td width="200"><?php echo $name; ?></td>
				<td><input type="text" name="fields[<?php echo $list['id']; ?>][<?php echo $name; ?>]" value="<?php echo $this->escape($value); ?>" size="40" /></td>
			</tr>
		<?php } ?>
		</table>
		<?php } ?>
	</fieldset>
	<?php } ?>
	<?php } ?>
	
	<button type="submit" class="button btn btn-primary"><?php echo JText::_('RSM_SAVE'); ?></button>

<?php echo JHTML::_( 'form.token' ); ?>
<input type="hidden" name="option" value="com_rsmail" />
<input type="hidden" name="task" value="details" />
<input type="hidden" name="id" value="<?php echo JFactory::getApplication()->input->getString('id',''); ?>" />
</form>
