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

<script language="javascript" type="text/javascript">
Joomla.submitbutton = function(task) {
	if(task == 'back') 
		document.location = '<?php echo JRoute::_('index.php?option=com_rsmail&view=reports&layout=view&id='.$this->id,false); ?>';
	else Joomla.submitform(task);
}
</script>

<form method="post" action="<?php echo JRoute::_('index.php?option=com_rsmail&view=reports&layout=opens'); ?>" name="adminForm" id="adminForm">
	<div class="row-fluid">
		<div class="span2">
			<?php echo $this->sidebar; ?>
		</div>
		<div class="span10">
			<?php echo $this->filterbar->show(); ?>
			<table class="table table-striped adminlist">
				<thead>
					<tr>
						<th><?php echo JText::_('RSM_SUBSCR_EMAIL'); ?></th>
						<th width="10%" class="center"><?php echo JText::_('RSM_LINK_DATE'); ?></th>
						<th width="5%" class="center"><?php echo JText::_('RSM_LINK_IP'); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($this->items as $i => $item) { ?>
					<tr class="row<?php echo $i % 2; ?>">
						<td><?php echo $item->SubscriberEmail; ?></td>
						<td align="center" class="center"><?php if (!empty($item->date)) echo rsmailHelper::showDate($item->date); ?></td>
						<td align="center" class="center"><?php echo $item->ip; ?></td>
					</tr>
				<?php } ?>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="3"><?php echo $this->pagination->getListFooter(); ?></td>
					</tr>
				</tfoot>
			</table>
			
		</div>
	</div>
	
	<?php echo JHTML::_( 'form.token' ); ?>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="id" value="<?php echo $this->id; ?>" />
	<input type="hidden" name="unique" value="<?php echo JFactory::getApplication()->input->getInt('unique',0); ?>" />
</form>