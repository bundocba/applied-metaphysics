<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access'); 
JHTML::_('behavior.keepalive');
JHTML::_('behavior.formvalidation');
JHTML::_('behavior.tooltip');
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'list.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
			Joomla.submitform(task, document.getElementById('adminForm'));
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
	
	function initialize_table_drag() {
		jQuery('#rsm_fields_list').tableDnD({
			onDragClass: 'rsm_drag_row',
			onDrop: function(table, row) {
				var fields = table.tBodies[0].rows;
				var params = '';

				for(i=0;i<fields.length;i++) {
					if(fields[i].id == 'email') 
						params += '&cid[]='+fields[i].id;
					else 
						params += '&cid[]='+fields[i].id.replace('cid_','');
					params += '&order[]='+i;
				}
				// save order
				rsc_order_fields(params);
			}
		});
	}
	
	function rsc_order_fields(params) {
		jQuery.ajax({
			type: 'POST',
			url: "index.php?option=com_rsmail&task=list.savefieldsorder",
			dataType: 'html',
			data : params,
			beforeSend: function (data) {
				jQuery('#rsm_fields_list tbody tr td').css('background','#EFFFFA');
			},
			success: function(data)	{
				jQuery('#rsm_fields_list tbody tr td').css('background','none');
			}
		});
	}
	
	jQuery(document).ready(function(){
		// ordering table
		initialize_table_drag();
		
		// show/hide edit field
		jQuery('.rsm_edit_field, #rsm_cancel_edit').live('click', function(){
			var action = jQuery(this).attr('rel');
			if(action == 'cancel')
				var field_id = jQuery(this).parent().parent().parent().parent().attr('id').replace('cid_','');
			else
				var field_id = jQuery(this).parent().parent().parent().attr('id').replace('cid_','');

			jQuery.ajax({
				type: 'POST',
				url: 'index.php?option=com_rsmail&view=list&layout=field&tmpl=component&id='+field_id+'&action='+action+'&randomTime='+Math.random(),
				dataType: 'html',
				success: function(response)	{ jQuery('#edit_field'+field_id).empty().html(response); }
			});
		});
		
		// pressing enter trigger button.rsm_save_edit 
		jQuery('.rsm_edit_inp').live('keypress', function(e){ if(e.which == '13') {	jQuery(this).siblings('.rsm_save_edit').trigger('click'); return false; } });
		
		// pressing enter trigger button.rsm_add_field 
		jQuery('#rsm_new_field').live('keypress', function(e){ if(e.which == '13') {	jQuery(this).siblings('#rsm_add_field').trigger('click'); return false; } });
		
		// save field name (add/edit)
		jQuery('#rsm_add_field, .rsm_save_edit').live('click',function(){
			var cid = '';
			var field_name 	= '&FieldName='+jQuery('#rsm_new_field').val();
			var id_list 	= '&IdList='+jQuery('#jform_IdList').val();
			var is_save_edit = jQuery(this).hasClass('rsm_save_edit');
			if(is_save_edit){
				var edit_form	= jQuery(this).parent();
				fieldId 			= edit_form.children('input[name="cid"]').val();
				field_name 		= '&FieldName='+edit_form.children('input[name="edit_field'+fieldId+'"]').val();
				cid				= '&id='+fieldId;
			}

			if (jQuery.trim(field_name.replace('&FieldName=','')) == '') {
				alert('<?php echo JText::_('RSM_ERR_EMPTY_FIELD', true);?>'); return;
			}
			// send field name 
			jQuery.ajax({
				type: 'POST',
				url: "index.php?option=com_rsmail&task=list.savefield",
				dataType: 'json',
				data : cid+field_name+id_list,
				success: function(response)	{
					if(is_save_edit){
						jQuery('#edit_field'+fieldId).html('<a href="javascript:void(0);" class="rsm_edit_field" rel="edit">'+response.FieldName+'</a>');
					} else {
						var tr 			= jQuery('<tr />', {id: 'cid_'+response.IdListFields, class: 'row1'});
						var IdCell 		= jQuery('<td/>', {text: response.IdListFields}).appendTo(tr);
						var NameCell	= jQuery('<td/>').appendTo(tr);
						var NameSpan	= jQuery('<span/>', {id: 'edit_field'+response.IdListFields}).appendTo(NameCell);
						var NameLink	= jQuery('<a/>', {href: 'javascript:void(0);', class : 'rsm_edit_field', rel: 'edit', text: response.FieldName}).appendTo(NameSpan);
						var DeleteCell	= jQuery('<td/>', {align: 'center'}).appendTo(tr);
						var DeleteLink	= jQuery('<a/>', {href: 'javascript:void(0);', class: 'rsm_delete_field', html: '<img src="components/com_rsmail/assets/images/icons/delete.png" />'}).appendTo(DeleteCell);
						jQuery('#rsm_fields_list tbody').append(tr);
						// empty field value
						field_name = '';
					}

					// save order
					var fields = jQuery('#rsm_fields_list tbody').children('tr');
					var params = '';

					for(i=0;i<fields.length;i++) {
						if(fields[i].id == 'email') params += '&cid[]='+fields[i].id;
						else params += '&cid[]='+fields[i].id.replace('cid_','');
						params += '&order[]='+i;
					}
					
					initialize_table_drag();
					rsc_order_fields(params);
					
					// empty field value
					jQuery('#rsm_new_field').val('');
				}
			});
		});

		// delete field
		jQuery('.rsm_delete_field').live('click', function(){
			var field_id = jQuery(this).parent().parent().attr('id').replace('cid_','');
			if(confirm('<?php echo JText::_('RSM_ERR_CONFIRM_DELETE_FIELD', true);?>')) {
				jQuery.ajax({
					type: 'POST',
					url: "index.php?option=com_rsmail&task=list.deletefield&id="+field_id,
					dataType: 'html',
					success: function(data)	{
						// delete row
						jQuery('#cid_'+field_id).remove();

						// save order
						var fields = jQuery('#rsm_fields_list tbody').children('tr');
						var params = '';

						for(i=0;i<fields.length;i++) {
							if(fields[i].id == 'email') params += '&cid[]='+fields[i].id;
							else params += '&cid[]='+fields[i].id.replace('cid_','');
							params += '&order[]='+i;
						}
						rsc_order_fields(params);

						// initialize table dragging
						initialize_table_drag();
					}
				});
			}
		});

		// empty subscriber lists
		jQuery('#rsm_empty_list').click(function(){
			var IdList = jQuery('#jform_IdList').val();
			if(confirm('<?php echo JText::_('RSM_DELETE_SUBSCRIBERS_FROM_LIST', true);?>'))
			{
				jQuery.ajax({
					type: 'POST',
					url: "index.php?option=com_rsmail&task=list.clearlist&id="+IdList,
					dataType: 'html',
					success: function(response)	{ alert(response);}
				});
			}
		});
	});
	
</script>

<div class="row-fluid">
	<div class="span6 rsspan6 rsleft">
		<form action="<?php echo JRoute::_('index.php?option=com_rsmail&view=list&layout=edit&IdList='.(int) $this->item->IdList); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off" class="form-validate form-horizontal">
			<?php echo JHtml::_('rsfieldset.start', 'adminform'); ?>
			<?php echo JHtml::_('rsfieldset.element', $this->form->getLabel('ListName'), $this->form->getInput('ListName')); ?>
			<?php if ($this->item->IdList) echo JHtml::_('rsfieldset.element', '<label></label>', '<a id="rsm_empty_list" class="btn btn-info button" href="javascript:void(0);">'.JText::_('RSM_CLEAR_LIST').'</a>'); ?>
			<?php echo JHtml::_('rsfieldset.end'); ?>
			
			<?php echo JHTML::_('form.token'); ?>
			<input type="hidden" name="task" value="" />
			<?php echo $this->form->getInput('IdList'); ?>
		</form>
	</div>
	<div class="span6 rsspan6 rsleft">
		<table class="table table-striped adminlist" id="rsm_fields_list">
			<thead>
				<tr class="nodrop nodrag">
					<th width="1%">#</th>
					<th width="50%"><?php echo JText::_('RSM_FIELD_NAME'); ?></th>
					<th width="10%"><?php echo JText::_('RSM_DELETE'); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php $n = count($this->item->fields)+1; ?>
			<?php for ($i=0;$i<$n;$i++) { ?>
			<?php $field = isset($this->item->fields[$i]) ? $this->item->fields[$i] : null; ?>
			<?php $fieldName = (empty($field->FieldName) || ($field->FieldName == ' ')) ? JText::_('RSM_NO_NAME') : $field->FieldName; ?>
			<?php if (isset($field)) { ?>
				<tr class="row1" id="cid_<?php echo $field->IdListFields;?>">
					<td><?php echo $field->IdListFields; ?></td>
					<td>
						<span id="edit_field<?php echo $field->IdListFields; ?>">
							<a href="javascript:void(0);" class="rsm_edit_field" rel="edit"><?php echo $fieldName; ?></a>
						</span>
					</td>
					<td align="center">
						<a class="rsm_delete_field" href="javascript:void(0);">
							<img src="<?php echo JURI::root(); ?>administrator/components/com_rsmail/assets/images/icons/delete.png" />
						</a>
					</td>
				</tr>
			<?php } else { ?>
				<tr class="row0" id="email">
					<td>-</td>
					<td><?php echo JText::_('RSM_EMAIL'); ?></td>
					<td></td>
				</tr>
			<?php } ?>
			<?php } ?>
			</tbody>
			<tfoot>
				<tr class="nodrop nodrag">
					<td colspan="3">
						<?php if ($this->item->IdList) { ?>
							<input type="text" name="new_field" value="" size="40" class="rsm_input" id="rsm_new_field"/> 
							<button type="button" class="btn button" id="rsm_add_field"><?php echo JText::_('RSM_ADD_BTN'); ?></button>
						<?php } ?>
					</td>
				</tr>
			</tfoot>
		</table>
	</div>
</div>