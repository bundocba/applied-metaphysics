<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.keepalive'); 

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction')); ?>


<form method="post" action="<?php echo JRoute::_('index.php?option=com_rsmail&view=autoresponders'); ?>" name="adminForm" id="adminForm">
<div class="row-fluid">
	<div class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div class="span10">
		<div class="well">
			<img src="<?php echo JURI::root(); ?>administrator/components/com_rsmail/assets/images/icons/info-32.png" alt="" />
			<strong style="font-size: 12px;"><?php echo JText::_('RSM_FOLLOWUP_INFO'); ?></strong>
		</div>
		
		<?php echo $this->filterbar->show(); ?>
		<table class="table table-striped adminlist">
			<thead>
				<th width="1%" align="center" class="hidden-phone"><input type="checkbox" name="checkall-toggle" id="rscheckbox" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this);"/></th>
				<th><?php echo JHtml::_('grid.sort', 'RSM_FOLLOWUP_NAME', 'AutoresponderName', $listDirn, $listOrder); ?></th>
				<th class="center hidden-phone" align="center" width="20%"><?php echo JText::_('RSM_FOLLOWUP_METHOD'); ?></th>
				<th class="center hidden-phone" align="center" width="20%"><?php echo JText::_('RSM_FOLLOWUP_LISTS'); ?></th>
				<th width="1%" class="nowrap hidden-phone" align="center"><?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'IdAutoresponder', $listDirn, $listOrder); ?></th>
			</thead>
			<tbody>
				<?php foreach ($this->items as $i => $item) { ?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="center hidden-phone">
							<?php echo JHtml::_('grid.id', $i, $item->IdAutoresponder); ?>
						</td>
						<td class="nowrap has-context">
							<a href="<?php echo JRoute::_('index.php?option=com_rsmail&task=autoresponder.edit&IdAutoresponder='.$item->IdAutoresponder); ?>"><?php echo $item->AutoresponderName; ?></a>
						</td>
						<td class="center hidden-phone" align="center">
							<?php echo $item->AutoresponderType == 0 ? JText::_('RSM_START_FOLLOWUP_AFTER_SUBSCRIPTION') : JText::sprintf('RSM_FOLLOWUP_START_DATE', rsmailHelper::showDate($item->AutoresponderDate)); ?>
						</td>
						<td class="center hidden-phone" align="center">
							<?php echo $item->lists; ?>
						</td>
						<td class="center hidden-phone" align="center">
							<?php echo (int) $item->IdAutoresponder; ?>
						</td>
					</tr>
				<?php } ?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="5" align="center"><?php echo $this->pagination->getListFooter(); ?></td>
				</tr>
			</tfoot>
		</table>
	</div>
</div>
	
	<?php echo JHTML::_( 'form.token' ); ?>
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="task" value="" />
</form>