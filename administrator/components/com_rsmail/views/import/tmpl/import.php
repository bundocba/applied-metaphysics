<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.keepalive'); ?>

<script type="text/javascript">
Joomla.submitbutton = function(task) {
	if(task == 'import.cancel') {
		document.location = 'index.php?option=com_rsmail&view=import';
	} else {
		ret1 = true;
		ret2 = true;
		<?php 
			$max = 1; 
			$key = 0;
			foreach ($this->content as $i => $el) {
				if (count($el) > $max) {
					$max = count($el);
					$key = $i;
				}
			}
		?>
		var count = <?php echo count($this->content[$key]); ?>;
		<?php foreach ($this->headers as $header) { ?>
		// check if two identical headers are selected
		var nr1 = 0;
		for(var i=0;i<count;i++) {
			if (document.getElementById('FieldName' + i).value == '<?php echo htmlentities($header->FieldName,ENT_QUOTES,'UTF-8'); ?>')
				nr1 += 1;
		}
		// if a header is found more than once, set the flag
		if (nr1 > 1)
			ret1 = false;
		<?php } ?>
		// check if emails are selected
		var nr2 = 0;
		for(var i=0;i<count;i++) {
			if (document.getElementById('FieldName' + i).value == '<?php echo JText::_('RSM_EMAIL',true); ?>')
				nr2 += 1;
		}
		// if an email is found more than once, set the flag
		if (nr2 > 1)
			ret1 = false;
		// if an email is not found at all, set the flag
		if (nr2 == 0)
			ret2 = false;
		
		// show errors
		if (!ret1)
			alert('<?php echo JText::_('RSM_SAME_FIELDS',true); ?>');
		else if (!ret2)
			alert('<?php echo JText::_('RSM_NO_EMAIL',true); ?>');
		else {
			if (task == 'import.save')
				repeat();
			else
				Joomla.submitform(task);
		}
	}
	return false;
}
</script>

<div style="text-align:center;font-weight:bold;" class="well"><?php echo JText::_('RSM_IMPORT_DETAILS'); ?></div>

<br/><br/>
<div id="com-rsmail-import-progress" class="com-rsmail-progress" style="display:none;"><div style="width: 1%;" id="com-rsmail-bar" class="com-rsmail-bar">0%</div></div>

<form action="<?php echo JRoute::_('index.php?option=com_rsmail&view=import&layout=import'); ?>" method="post" name="adminForm" id="adminForm">
	<table class="adminlist table table-striped">
		<thead>
			<tr>
				<th width="1%">#</th>
				<?php $max = 1; ?>
				<?php $key = 0; ?>
				<?php foreach ($this->content as $i => $el) { ?>
				<?php if (count($el) > $max) { ?>
				<?php $max = count($el); ?>
				<?php $key = $i; ?>
				<?php } ?>
				<?php } ?>
				<?php $count = count($this->content[$key]); ?>
				<?php for($i=0,$n=$count;$i<$n;$i++) { ?>
				<th><?php echo $this->lists['fields'][$i]; ?></th>
				<?php } ?>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($this->content as $i => $row) { ?>
			<tr class="row<?php echo $i % 2; ?>">
				<td><?php echo $i+1; ?></td>
				<?php foreach ($row as $item) { ?>
				<td><?php echo $item; ?></td>
				<?php } ?>
			</tr>
			<?php } ?>
		</tbody>
	</table>

	<?php echo JHTML::_( 'form.token' ); ?>
	<input type="hidden" name="option" value="com_rsmail" />
	<input type="hidden" id="IdList" name="IdList" value="<?php echo JFactory::getApplication()->input->getInt('id',0); ?>" />
	<input type="hidden" name="task" value="importfile" />
	<input type="hidden" name="bytes" value="0" id="rsmail_bytes" />
</form>