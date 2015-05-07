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
$listDirn	= $this->escape($this->state->get('list.direction'));
?>

<script type="text/javascript">
Joomla.submitbutton = function(task) {
	if (task == 'import') {
		document.location = 'index.php?option=com_rsmail&view=import';
	} else {
		Joomla.submitform(task);
	}
}
</script>

<form method="post" action="<?php echo JRoute::_('index.php?option=com_rsmail&view=lists'); ?>" name="adminForm" id="adminForm">
<div class="row-fluid">
	<div class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div class="span10">
		<?php echo $this->filterbar->show(); ?>
		<table class="table table-striped adminlist">
			<thead>
				<th width="1%" align="center" class="hidden-phone"><input type="checkbox" name="checkall-toggle" id="rscheckbox" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this);"/></th>
				<th><?php echo JHtml::_('grid.sort', 'RSM_LIST_NAME', 'ListName', $listDirn, $listOrder); ?></th>
				<th width="10%" align="center" class="center"><?php echo JText::_('RSM_SUBSCRIBERS'); ?></th>
				<th width="1%" class="nowrap hidden-phone"><?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'IdList', $listDirn, $listOrder); ?></th>
			</thead>
			<tbody>
				<?php foreach ($this->items as $i => $item) { ?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="center hidden-phone">
							<?php echo JHtml::_('grid.id', $i, $item->IdList); ?>
						</td>
						<td class="nowrap has-context">
							<a href="<?php echo JRoute::_('index.php?option=com_rsmail&task=list.edit&IdList='.$item->IdList); ?>"><?php echo $item->ListName; ?></a>
						</td>
						<td align="center" class="center hidden-phone">
							<strong>
								<a class="hasTip" href="<?php echo JRoute::_('index.php?option=com_rsmail&view=subscribers&showlist='.$item->IdList);?>" title="<?php echo JText::sprintf('RSM_VIEW_SUBSCRIBERS_FROM', $item->ListName);?>::">
									<?php echo JText::sprintf('RSM_NR_SUBSCRIBERS',$item->subscribers); ?>
								</a>
							</strong>
						</td>
						<td class="center hidden-phone">
							<?php echo (int) $item->IdList; ?>
						</td>
					</tr>
				<?php } ?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="4" align="center"><?php echo $this->pagination->getListFooter(); ?></td>
				</tr>
			</tfoot>
		</table>
	</div>
</div>
	
	<?php echo JHTML::_( 'form.token' ); ?>
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="task" value="" />
</form>