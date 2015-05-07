<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2012 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die('Restricted access'); ?>
<script language="javascript" type="text/javascript">
Joomla.submitbutton = function(task) {
	if (task == 'subscribers.copymove') {
		var valid = true;
		var msg = '';
		
		if (document.getElementById('rsm_destination_select').value == '') {
			msg = "<?php echo JText::_('RSM_ERR_JS_DESTINATION_LIST',true);?>";
			valid = false;
		}
		
		if (document.getElementById('rsm_action_select').value == '') {
			msg = "<?php echo JText::_('RSM_ERR_JS_ACTION',true);?>";
			valid = false;
		}
		
		if(!valid && msg != '') {
			alert(msg);
			return false;
		} else 
			Joomla.submitform(task);

	} else if (task == 'subscribers') {
		document.location = 'index.php?option=com_rsmail&view=subscribers';
	}
}

jQuery(document).ready(function(){
	var list_selects 	= jQuery('.rsm_select_fields');
	var select_boxes	= new Array();

	list_selects.each(function(i, original_select_container){
		// get the first select to clone
		var position = jQuery(original_select_container).attr('id').replace('listcontainer','');
		select_boxes[parseInt(position)] = jQuery(original_select_container).children('tr');
	});
	
	jQuery('#rsm_destination_select').change(function(){
		var selected_list 	= jQuery('#rsm_destination_select').val();
		
		if(selected_list != '') {
			jQuery.ajax({
				type: 'POST',
				url: "index.php?option=com_rsmail&task=jsonfields&IdList="+selected_list,
				dataType: 'html',
				beforeSend: function ( xhr ) {
					jQuery('#rsm_lists_container').css('display','none');
				},
				success: function(fields) {
					jQuery('#rsm_lists_container').css('display','block');
					
					<?php if(!empty($this->filtered_lists)) { ?>
					<?php foreach($this->filtered_lists as $listid) { ?>
					jQuery('#dlistname<?php echo $listid; ?>').text(jQuery('#rsm_destination_select option:selected').text());
					<?php }} ?>
					
					// clean lists
					jQuery('.rsm_select_fields tr').remove();
					
					jQuery.parseJSON(fields).each(function(el,index){
						// skip the first element "No Filter"
						if(index == 0) return true;

						// Create new rows
						<?php if(!empty($this->filtered_lists)) { ?>
						<?php foreach($this->filtered_lists as $listid) { ?>
						var clone = jQuery(select_boxes[<?php echo $listid; ?>]).clone();
						var list_id = clone.children('td').children('select').attr('rel');
						clone.children('td').children('select').attr('name','original_field['+list_id+']['+el.IdListFields+']');
						jQuery(clone.children('td')[2]).attr('id','destionation'+list_id+el.IdListFields);
						clone.appendTo(jQuery('#listcontainer<?php echo $listid; ?>'));
						jQuery('#destionation'+list_id+el.IdListFields).text(el.FieldName);
						<?php } ?>
						<?php } ?>
					});
				}
			});
		} else 
			jQuery('#rsm_lists_container').css('display','none');
	});
});
</script>

<div class="row-fluid">
	<div class="span12">
		<div id="rsm_progress_bar">
			<ul>
				<li class="rsm_done"><?php echo JText::sprintf('RSM_SELECT_USERS_STEP_1', $this->subscribers_count);?> </li>
				<li class="rsm_active"><?php echo JText::_('RSM_SELECT_DESTINATION_STEP_2');?></li>
				<li class=""><?php echo JText::_('RSM_FINISH_STEP_3');?></li>
			</ul>
		</div>

		<form action="<?php echo JRoute::_('index.php?option=com_rsmail'); ?>" method="post" id="adminForm" name="adminForm">
			<fieldset>
				<legend><?php echo JText::_('RSM_STEP_1');?></legend>
				<p id="rsm_step_one_container">
					<span class="rsleft"><?php echo JText::_('RSM_SELECT_ACTION');?></span>
					<select name="action" class="rsm_select rsleft" id="rsm_action_select">
						<option value=""><?php echo JText::_('RSM_SELECT_ACTION_OPTION_1');?></option>
						<option value="copy"><?php echo JText::_('RSM_SELECT_ACTION_OPTION_2');?></option>
						<option value="move"><?php echo JText::_('RSM_SELECT_ACTION_OPTION_3');?></option>
					</select>
					<span class="rsleft"><?php echo JText::_('RSM_SELECT_DESTINATION_LIST_LABEL');?></span>
					<select id="rsm_destination_select" class="rsm_select rsleft" name="destination_list">
						<option value=""><?php echo JText::_('RSM_SELECT_DESTINATION_LIST');?></option>
						<?php foreach($this->lists as $ListId => $list) { ?>
							<option value="<?php echo $ListId;?>"><?php echo $list['RSMListName'];?></option>
						<?php } ?>
					</select>
				</p>
			</fieldset>
			
			<span class="rsm_clear"></span>
			
			<div id="rsm_lists_container" style="display:none;">
				<fieldset>
				<legend><?php echo JText::_('RSM_STEP_2');?></legend>
					<p><?php echo JText::_('RSM_ASSIGN_FIELD_NAMES_DESC');?></p>
					<p class="rsm_info"><?php echo JText::_('RSM_ASSIGN_FIELD_NAMES_NOTE');?></p>
					<span class="rsm_clear"></span><br /><br />
						<?php if(!empty($this->filtered_lists)) { ?>
						<?php foreach($this->filtered_lists as $listid) { ?>
						<table id="table<?php echo $listid; ?>" class="adminlist table table-striped">
							<thead>
								<tr>
									<th width="40%"><?php echo $this->lists[$listid]['RSMListName'];?></th>
									<th></th>
									<th width="40%"><?php echo JText::_('RSM_DESTINATION_LIST');?> ( <strong id="dlistname<?php echo $listid; ?>"></strong> )</th>
								</tr>
							</thead>
							<tbody id="listcontainer<?php echo $listid; ?>" class="rsm_select_fields">
								<tr class="row0">
									<td>
										<select name="original_field[<?php echo $listid;?>]" rel="<?php echo $listid;?>">
											<option value=""><?php echo JText::_('RSM_IGNORE_VALUES');?></option>
											<?php foreach($this->lists[$listid]['fields'] as $field => $fieldid){ ?>
												<option value="<?php echo $field;?>"><?php echo $field;?></option>
											<?php } ?>
										</select>
									</td>
									<td><img src="<?php echo JURI::root(); ?>administrator/components/com_rsmail/assets/images/right-arrow.png" alt="" /></td>
									<td id="destionation<?php echo $listid; ?>"></td>
								</tr>
							</tbody>
						</table>
						<hr />
						<?php } ?>
						<?php } ?>
				</fieldset>
			</div>

		<?php echo JHTML::_( 'form.token' ); ?>
		<?php foreach($this->cid as $cid) { ?><input type="hidden" name="cid[]" value="<?php echo $cid;?>" /><?php } ?>
		<input type="hidden" name="option" value="com_rsmail" />
		<input type="hidden" name="task" value="" />
		</form>
	</div>
</div>