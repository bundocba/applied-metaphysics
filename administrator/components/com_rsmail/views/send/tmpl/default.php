<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access'); 
JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation');
?>
<script type="text/javascript">
function rsm_hide(val) {
	if (val == 0) {
		if (document.getElementById('Delivery0').getParent().getParent().hasClass('controls')) {
			$$('fieldset[class=adminform] > div')[1].style.display = 'none';
		} else {
			$$('fieldset[class=adminform] > ul > li')[1].style.display = 'none';
		}
	} else {
		if (document.getElementById('Delivery0').getParent().getParent().hasClass('controls')) {
			$$('fieldset[class=adminform] > div')[1].style.display = '';
		} else {
			$$('fieldset[class=adminform] > ul > li')[1].style.display = '';
		}
	}
}

function rsm_warning(type) {
	if (type == 1) {	
		if ($('LinkHistory').checked == true)
			alert('<?php echo JText::_('RSM_LINK_WARNING',true); ?>');
	} else {
		if($('OpensHistory').checked)
			alert('<?php echo JText::_('RSM_LINK_WARNING',true); ?>');
	}
}

Joomla.submitbutton = function(task) {
	if (task == 'cancel') {
		document.location = '<?php echo JRoute::_('index.php?option=com_rsmail&view=messages',false); ?>'
	} else if (task == 'send.save') {
		if (document.getElementById('Delivery1').checked == true) {
			if (document.formvalidator.isValid(document.id('adminForm')))
				Joomla.submitform(task, document.getElementById('adminForm'));
			else
				alert('<?php echo JText::_('RSM_INVALID_FORM',true); ?>');
		} else {
			Joomla.submitform(task, document.getElementById('adminForm'));
		}
	} else {
		Joomla.submitform(task, document.getElementById('adminForm'));
	}
}
</script>

<div class="row-fluid">
	<div class="span12">
		<div class="well">
			<img src="<?php echo JURI::root(); ?>/administrator/components/com_rsmail/assets/images/icons/info-32.png"/>
			<strong style="font-size: 12px;"><?php echo JText::_('RSM_SEND_INFO'); ?></strong>
		</div>
		
		<form action="<?php echo JRoute::_('index.php?option=com_rsmail&view=send'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal">
			<?php 
				$extra = JText::_('RSM_DELIVER_1').' <strong>'.$this->config->cron_emails.'</strong> '.JText::_('RSM_DELIVER_2').' <strong>'.($this->config->cron_period == 0 ? JText::_('RSM_HOUR') : JText::_('RSM_DAY')).'</strong> '.JText::_('RSM_DELIVER_3').' '.$this->form->getInput('DeliverDate').' '.JText::_('RSM_SERVER_TIME_MESSAGE').'<br /> <b>'.JText::_('RSM_SERVER_TIME').' '.JHTML::date('now', 'Y-m-d H:i:s', 'UTC').'</b>';
				
				echo JHtml::_('rsfieldset.start', 'adminform');
				echo JHtml::_('rsfieldset.element', $this->form->getLabel('Delivery'), $this->form->getInput('Delivery'));
				echo JHtml::_('rsfieldset.element', '<label></label>', '<span class="rsleft rsm_extra_send">'.$extra.'</span>');
				echo JHtml::_('rsfieldset.element', $this->form->getLabel('LinkHistory'), $this->form->getInput('LinkHistory'));
				echo JHtml::_('rsfieldset.element', $this->form->getLabel('OpensHistory'), $this->form->getInput('OpensHistory'));
				echo JHtml::_('rsfieldset.element', '<label></label>', '<span class="rsleft">'.JText::_('RSM_CRON_MESSAGE').'</span>');
				echo JHtml::_('rsfieldset.end');
			?>
			
			<table class="adminlist table table-striped">
				<thead>
					<tr>
						<th width="1%">
							<input type="checkbox" name="checkall-toggle" id="rscheckbox" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this);" <?php echo isset($this->filtered_subs) ? ' checked="checked"' : ''; ?> />
						</th>
						<th width="10%"><?php echo JText::_('RSM_LIST_NAME'); ?></th>
						<?php foreach($this->placeholders as $placeholder) { ?><th><?php echo $placeholder; ?></th><?php } ?>
					</tr>
				</thead>
				<tbody>
				<?php $count = count($this->placeholders); ?>
				<?php foreach ($this->lists as $i => $list) { ?>
					<tr class="row<?php echo $i % 2; ?>">
						<td align="center"><?php echo JHtml::_('grid.id', $i, $list->IdList); ?></td>
						<td><?php echo $list->ListName; ?></td>
						<?php for ($j=0;$j<$count;$j++) { ?>
						<td align="center"> <?php echo $this->fieldlists['fields'][$list->IdList][$j]; ?> </td>
						<?php } ?>
					</tr>
				<?php } ?>
				</tbody>
			</table>
			<?php echo JHTML::_( 'form.token' ); ?>
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="IdMessage" value="<?php echo $this->id; ?>" />
		</form>
	</div>
</div>
<script type="text/javascript">
rsm_hide($$('input[name=Delivery]:checked')[0].value);
<?php echo isset($this->filtered_subs) ? "document.getElementById('rscheckbox').click();" : ""; ?>
</script>
<span style="display:none;"><?php echo $this->form->getLabel('DeliverDate'); ?></span>