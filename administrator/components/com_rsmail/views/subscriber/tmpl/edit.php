<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.keepalive');
JHTML::_('behavior.formvalidation');
JHTML::_('behavior.tooltip'); ?>

<script type="text/javascript">
Joomla.submitbutton = function(task) {
	if (task == 'subscriber.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
		Joomla.submitform(task, document.getElementById('adminForm'));
	} else {
		alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
	}
}

window.addEvent("domready", function() { 
	$$('.rsm_lists').each(function (el) {
		el.addEvent('click', function() {
			if (el.checked)
				$('fieldcontainer'+el.value).style.display = 'block';
			else
				$('fieldcontainer'+el.value).style.display = 'none';
		});
	});
});
</script>

<form action="<?php echo JRoute::_('index.php?option=com_rsmail&view=subscriber&layout=edit&IdSubscriber='.(int) $this->item->IdSubscriber); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off" class="form-validate form-horizontal">
<div class="row-fluid">
	<div class="span12">
		<?php echo JHtml::_('rsfieldset.start', 'adminform'); ?>
		<?php echo JHtml::_('rsfieldset.element', $this->form->getLabel('SubscriberEmail'), $this->form->getInput('SubscriberEmail')); ?>
		<?php echo JHtml::_('rsfieldset.end'); ?>
	</div>
	<div class="span12">
		<div id="RSMFieldLists">
			<h3 id="rsm_list_title"><?php echo JText::_('RSM_SUBSCRIBER_LISTS'); ?></h3>
			<?php foreach($this->lists as $IdList => $fields) { ?>
			<div id="FieldList<?php echo $IdList;?>" class="rsm_fieldscontainer">
				<label class="rs_clickable">
					<input class="rsm_lists" type="checkbox" name="lists[]" value="<?php echo $IdList;?>" <?php echo isset($fields['RSMisSubscribed']) && $fields['RSMisSubscribed'] == 1 ? 'checked="checked"' : '';?>/>
					<div class="rs_title"><?php echo $fields['RSMListName'];?></div>
				</label>
				
				<div id="fieldcontainer<?php echo $IdList; ?>" style="<?php echo isset($fields['RSMisSubscribed']) ? 'display:block;' : 'display:none;';?>">
					<?php if(isset($this->lists[$IdList]['fields'])) { ?>
					<table class="adminform table table-striped">
					<?php foreach ($this->lists[$IdList]['fields'] as $fieldname => $fieldvalue) { ?>
						<tr>
							<td width="10%"><label><?php echo $fieldname;?></label></td>
							<td>
								<input type="text" size="50" name="fields[<?php echo $IdList;?>][<?php echo $fieldname;?>]" value="<?php echo $fieldvalue;?>" class="input-xlarge" />	
							</td>
						</tr>
					<?php } ?>
					</table>
					<?php } ?>
				</div>
			</div>
			<?php } ?>
		</div>
	</div>
</div>

	<?php echo JHTML::_( 'form.token' ); ?>
	<input type="hidden" name="option" value="com_rsmail" />
	<input type="hidden" name="task" value="save" />
	<?php echo $this->form->getInput('IdSubscriber'); ?>
</form>