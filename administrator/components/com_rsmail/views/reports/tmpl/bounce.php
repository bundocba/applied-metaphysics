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

<script type="text/javascript">
Joomla.submitbutton = function(task) {
	if(task == 'back') 
		document.location = '<?php echo JRoute::_('index.php?option=com_rsmail&view=reports&layout=view&id='.$this->id,false); ?>';
	else Joomla.submitform(task);
}
</script>

<form method="post" action="<?php echo JRoute::_('index.php?option=com_rsmail&view=reports&layout=bounce'); ?>" name="adminForm" id="adminForm">
	<div class="row-fluid">
		<div class="span2">
			<?php echo $this->sidebar; ?>
		</div>
		<div class="span10">
			<?php echo $this->filterbar->show(); ?>
			<table class="table table-striped adminlist">
				<thead>
					<tr>
						<th><?php echo JText::_('RSM_BOUNCE_EMAILS'); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($this->items as $i => $item) { ?>
					<tr class="row<?php echo $i % 2; ?>">
						<td><?php echo $item->Email; ?></td>
					</tr>
				<?php } ?>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="1"><?php echo $this->pagination->getListFooter(); ?></td>
					</tr>
				</tfoot>
			</table>
			
		</div>
	</div>
	
	<?php echo JHTML::_( 'form.token' ); ?>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="1" />
	<input type="hidden" name="id" value="<?php echo $this->id; ?>" />
</form>