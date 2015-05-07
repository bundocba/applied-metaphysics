<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access'); ?>

<h2><?php echo JText::_('RSM_HISTORY_MESSAGES'); ?></h2>
<table class="rsmtable table table-striped">
	<thead>
		<tr>
			<th width="1%" align="right">#</th>
			<th><?php echo JText::_('RSM_MESSAGE'); ?></th>
			<th width="25%" align="right"><?php echo JText::_('RSM_RELEASED_ON'); ?></th>
		</tr>
	</tbody>
	<tbody>
	<?php if (!empty($this->items)) { ?>
	<?php foreach($this->items as $i => $item) { ?>
		<tr class="row<?php echo $i % 2; ?>">
			<td><?php echo $i+1; ?></td>
			<td>
				<a href="<?php echo JRoute::_('index.php?option=com_rsmail&view=history&layout=message&cid='.$item->IdMessage.'&sess='.$item->IdSession,false); ?>">
					<?php echo $item->MessageSubject; ?>
				</a>
			</td>
			<td align="right"><?php echo rsmailHelper::showDate($item->Date); ?></td>
		</tr>
	<?php } ?>
	<?php } ?>
	</tbody>
</table>