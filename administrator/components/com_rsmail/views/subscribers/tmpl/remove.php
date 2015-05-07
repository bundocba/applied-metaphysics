<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2012 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');
?>
<script type="text/javascript">
function sendlists() {
	var lists = document.getElementsByName('lids[]');
	parent.document.getElementById('rsm_from_lists').value = '';
	for(i=0;i<lists.length;i++)
		if( lists[i].checked === true)
			parent.document.getElementById('rsm_from_lists').value += lists[i].value+',';
	
	if(parent.document.getElementById('rsm_from_lists').value == '') {
		alert('<?php echo JText::_('RSM_SELECT_SUBSCRIBER_LIST');?>');
		return;
	}
	parent.Joomla.submitform('subscribers.delete');
}

function checkAll() {
	var lists = document.getElementsByName('lids[]');
	for(i=0;i<lists.length;i++)
		if(document.getElementById('rsm_select_all_list').checked === true)
			lists[i].checked = true;
		else 
			lists[i].checked = false;
}
</script>

<p class="rsm_info"><?php echo JText::_('RSM_DELETE_LISTS_FILTER_INFO');?></p>
<div style="text-align:right;">
	<button type="button" onclick="sendlists();" class="button btn btn-primary"><?php echo JText::_('RSM_DELETE');?></button>
	<button type="button" onclick="window.parent.SqueezeBox.close();" class="button btn"><?php echo JText::_('RSM_CANCEL_BTN');?></button>
</div>

<table class="adminlist table table-striped">
	<thead>
		<tr>
			<th width="1%"><label class="hasTip" title="<?php echo JText::_('RSM_ALL_LISTS');?>"><input type="checkbox" name="checkall" value="0" onclick="checkAll();" id="rsm_select_all_list" /></label></th>
			<th align="center"><?php echo JText::_('RSM_LISTS');?></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->lists as $IdList => $list){ ?>
		<tr>
			<td width="5%">
				<input type="checkbox" name="lids[]" value="<?php echo $IdList;?>" id="rsm_list<?php echo $IdList;?>" <?php echo ( array_key_exists($IdList, $this->filtered_lists)? ' checked="checked"' : '') ;?> />
			</td>
			<td><?php echo $list['RSMListName'];?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>