<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.keepalive'); ?>

<form method="post" action="<?php echo JRoute::_('index.php?option=com_rsmail&view=cronlogs&layout=log'); ?>" name="adminForm" id="adminForm">
<div class="row-fluid">
	<div class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div class="span10">
		<?php echo $this->filterbar->show(); ?>
		<table class="table table-striped adminlist">
			<thead>
				<th width="1%">#</th>
				<th class="nowrap has-context"><?php echo JText::_('RSM_EMAILS'); ?></th>
				<th width="25%" align="center" class="center hidden-phone"><?php echo JText::sprintf('RSM_TIME_SENT', rsmailHelper::showDate(JFactory::getDate()->toSql(),true)); ?></th>
			</thead>
			<tbody>
				<?php foreach ($this->items as $i => $item) { ?>
					<tr class="row<?php echo $i % 2; ?>">
						<td><?php echo $this->pagination->getRowOffset($i); ?></td>
						
						<td class="nowrap has-context">
							<?php echo $item->SubscriberEmail; ?>
						</td>
						
						<td align="center" class="center hidden-phone">
							<?php echo rsmailHelper::showDate($item->DateSent, true); ?>
						</td>
						
					</tr>
				<?php } ?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="3" align="center"><?php echo $this->pagination->getListFooter(); ?></td>
				</tr>
			</tfoot>
		</table>
	</div>
</div>
	
	<?php echo JHTML::_( 'form.token' ); ?>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="id" value="<?php echo JFactory::getApplication()->input->getInt('id',0); ?>" />
</form>