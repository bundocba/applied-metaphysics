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

<form method="post" action="<?php echo JRoute::_('index.php?option=com_rsmail&view=cronlogs'); ?>" name="adminForm" id="adminForm">
<div class="row-fluid">
	<div class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div class="span10">
		<?php echo $this->filterbar->show(); ?>
		<table class="table table-striped adminlist">
			<thead>
				<th><?php echo JText::_('RSM_SESSION_NAME'); ?></th>
				<th width="10%" align="center" class="center hidden-phone"><?php echo JText::_('RSM_SENT_TO'); ?></th>
				<th width="10%" align="center" class="center hidden-phone"><?php echo JText::_('RSM_SESSION_DATE'); ?></th>
			</thead>
			<tbody>
				<?php foreach ($this->items as $i => $item) { ?>
				<?php $sessionName = empty($item->MessageSubject) ? JText::_('RSM_NO_SESSION_NAME') : $item->MessageSubject; ?>
				<?php $sname = empty($item->MessageName) ? $sessionName : $item->MessageName; ?>
					
					<tr class="row<?php echo $i % 2; ?>">
						<td class="nowrap has-context">
							<a href="<?php echo JRoute::_('index.php?option=com_rsmail&view=cronlogs&layout=log&id='.$item->IdSession); ?>"><?php echo $sname; ?></a>
						</td>
						
						<td align="center" class="center hidden-phone">
							<?php echo $item->TotalSentEmails; ?>
						</td>
						
						<td align="center" class="center hidden-phone">
							<?php echo rsmailHelper::showDate($item->DateAccessed); ?>
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
</form>