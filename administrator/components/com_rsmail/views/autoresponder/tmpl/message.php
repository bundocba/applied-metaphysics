<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.keepalive'); ?>

<form action="<?php echo JRoute::_('index.php?option=com_rsmail'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row-fluid">
		<div class="span12">
			<div class="rsm_ar_add_message">
				<button type="button" onclick="Joomla.submitform('autoresponder.saveplaceholders')" class="btn btn-primary button">
					<?php if($this->idamessage) echo JText::_('RSM_UPDATE_BTN'); else echo JText::_('RSM_ADD_BTN'); ?>
				</button>
				<button type="button" onclick="window.parent.SqueezeBox.close();" class="btn button"><?php echo JText::_('RSM_CANCEL_BTN'); ?></button>
			</div>
			
			<div class="well">
				<?php echo JText::_('RSM_DELAY'); ?> <input type="text" class="input-mini rsm_ar_input" size="2" onkeyup="javascript:this.value=this.value.replace(/[^0-9]/g, '');" name="DelayPeriod" value="<?php echo empty($this->period[0]) ? '7' : $this->period[0]; ?>" />
				<select name="DelayType" id="DelayType">
					<?php echo JHtml::_('select.options', $this->frequency, 'value', 'text', $this->period[1]); ?>
				</select>
			</div>
			
			<table class="adminlist table table-striped">
				<thead>
					<tr>
						<th width="10%"><?php echo JText::_('RSM_LIST_NAME'); ?></th>
						<?php foreach($this->placeholders as $placeholder) { ?>
						<th><?php echo $placeholder; ?></th>
						<?php } ?>
					</tr>
				</thead>
				<tbody>
				<?php $count = count($this->placeholders); ?>
				<?php foreach ($this->arlists as $i => $list) { ?>
					<tr class="row<?php echo $i % 2; ?>">
						<td><?php echo $list->ListName; ?></td>
						<?php for ($j=0;$j<$count;$j++) { ?>
						<td align="center"><?php echo $this->listfields['fields'][$list->IdList][$j]; ?> </td>
						<?php } ?>
					</tr>
				<?php } ?>
				</tbody>
			</table>
		</div>
	</div>

	<?php echo JHTML::_( 'form.token' ); ?>
	<input type="hidden" name="IdMessage" value="<?php echo JFactory::getApplication()->input->getInt('id',0); ?>" />
	<input type="hidden" name="IdAutoresponderMessage" value="<?php echo $this->idamessage; ?>" />
	<input type="hidden" name="IdAutoresponder" value="<?php echo JFactory::getApplication()->input->getInt('IdAutoresponder',0); ?>" />
	<input type="hidden" name="task" value="" />
</form>