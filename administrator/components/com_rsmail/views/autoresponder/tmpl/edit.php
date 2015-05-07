<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.keepalive');
JHTML::_('behavior.tooltip');
JHTML::_('behavior.modal');
JHTML::_('behavior.formvalidation'); ?>

<script type="text/javascript">
Joomla.submitbutton = function(task) {
	if (task == 'autoresponder.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
		Joomla.submitform(task, document.getElementById('adminForm'));
	} else {
		alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
	}
}

function rsm_change_type(val) {
	if (val == 1){
		if (document.getElementById('jform_AutoresponderDate').getParent().hasClass('input-append')) {
			document.getElementById('jform_AutoresponderDate').getParent().getParent().getParent().style.display = '';
		} else {
			document.getElementById('jform_AutoresponderDate').getParent().style.display = '';
		}
		
		$('rsm_start_sending').innerHTML = 	$('jform_AutoresponderDate').value;
	} else { 
		if (document.getElementById('jform_AutoresponderDate').getParent().hasClass('input-append')) {
			document.getElementById('jform_AutoresponderDate').getParent().getParent().getParent().style.display = 'none';
		} else {
			document.getElementById('jform_AutoresponderDate').getParent().style.display = 'none';
		}
		
		$('rsm_start_sending').innerHTML = '<?php echo JText::_('RSM_START_FOLLOWUP_AFTER_SUBSCRIPTION'); ?>';
	}
}

jQuery(document).ready(function(){
	// animate the progress bar
	jQuery('fieldset').click(function() {
		switch (jQuery(this).attr('id')) {
			case 'rsm_autores_step1_fieldset': 
				jQuery('#step_1').attr('class', 'rsm_active');
				jQuery('#step_2').attr('class', '');
				jQuery('#step_3').attr('class', '');
			break; 
			case 'rsm_autores_step2_fieldset': 
				jQuery('#step_1').attr('class', 'rsm_done');
				jQuery('#step_2').attr('class', 'rsm_active');
				jQuery('#step_3').attr('class', '');
			break; 
			case 'rsm_autores_step3_fieldset': 
				jQuery('#step_1').attr('class', 'rsm_done_first');
				jQuery('#step_2').attr('class', 'rsm_done');
				jQuery('#step_3').attr('class', 'rsm_active');
			break; 
		}
	});
	
	// change href
	jQuery('#IdMessage').change(function() {
		if(jQuery('[name="cid[]"]:checked').length == 0) {
			alert('<?php echo JText::_('RSM_SELECT_LISTS_FIRST');?>'); 
			return;
		}
		
		var IdAutoresponder = jQuery('#jform_IdAutoresponder').val();
		if (jQuery(this).val() == 0) {
			jQuery('#rsm_no_modal').css('display','');
			jQuery('#rsm_modal').css('display','none');
		} else{
			jQuery('#rsm_no_modal').css('display','none');
			jQuery('#rsm_modal').css('display','');
			jQuery('#rsm_modal').attr('href', 'index.php?option=com_rsmail&view=autoresponder&layout=message&id='+jQuery(this).val()+'&IdAutoresponder='+IdAutoresponder+'&tmpl=component');
		}
	});
	
	// save the auto responder details when checkbox is clicked
	jQuery('.rsm_save_ar').click(function(){
		var params = '';
		var lists = '';
		
		if (jQuery('#jform_AutoresponderName').val() == '') {
			alert('<?php echo JText::_('RSM_ERR_ADD_FOLLOWUP_NAME',true); ?>'); 
			return;
		}
		
		if (jQuery('input[name="AutoresponderType"]:checked').val() == '') {
			alert('<?php echo JText::_('RSM_ERR_SELECT_FOLLOWUP_METHOD',true); ?>');
			return;
		}
		
		if (jQuery('#jform_IdAutoresponder').val() != '') 
			params += 'jform[IdAutoresponder]=' + jQuery('#jform_IdAutoresponder').val();
		else
			params += 'jform[IdAutoresponder]=0';
		
		jQuery('[name="cid[]"]:checked').each(function(index,list){
			lists += '&cid[]='+jQuery(list).val();
		});
		
		if (lists == '') {
			alert('<?php echo JText::_('RSM_SELECT_LISTS_FIRST',true); ?>'); 
			return;
		}
		
		params += lists;
		params += '&jform[AutoresponderName]=' + jQuery('#jform_AutoresponderName').val();
		params += '&jform[AutoresponderType]=' + jQuery('input[name="jform[AutoresponderType]"]:checked').val();
		params += '&jform[AutoresponderDate]=' + jQuery('#jform_AutoresponderDate').val();
		
		jQuery.ajax({
			type: 'POST',
			url: "index.php?option=com_rsmail&task=autoresponder.saveajax",
			dataType: 'html',
			data : params,
			success: function(id_auto_responder) {
				jQuery('#jform_IdAutoresponder').val(id_auto_responder);
				document.getElementById('adminForm').action = '<?php echo JRoute::_('index.php?option=com_rsmail&view=autoresponder&layout=edit&IdAutoresponder=',false); ?>'+id_auto_responder;
			}
		});
	});
	
	// delete message
	jQuery('.rsm_del_msg').live('click', function(){
		if(confirm('<?php echo JText::_('RSM_EDIT_FOLLOWUP_CONFIRM_MESSAGE_DELETE',true); ?>'))
		{
			var ajax_url = jQuery(this).attr('href');

			jQuery.ajax({
				type: 'POST',
				url: ajax_url,
				dataType: 'html',
				success: function(deleted_message) {
					jQuery('div#msg'+deleted_message).remove();
					// reorder list
					var new_width =  ( 75 / jQuery('#rsm_timeline_bar').children('div.rsm_timeline_post:not(.rsm_no_sort)').length);

					jQuery('#rsm_timeline_bar').children('div.rsm_timeline_post:not(.rsm_no_sort)').each(function(i,item){
						jQuery(item).children('.rsm_bullet').html(i+1);
						jQuery(item).css('width', new_width+'%');

						jQuery(item).children('.rsm_msg_bubble').css('width', (115 - jQuery('#rsm_timeline_bar').children('div.rsm_timeline_post:not(.rsm_no_sort)').length)+'%');

						jQuery('.rsm_drop_zone').css('width', new_width+'%');
					});
					alert('<?php echo JText::_('RSM_EDIT_FOLLOWUP_MESSAGE_DELETED',true); ?>');
				}
			});
			return false;
		}
		return false;
	});
	
	// messatges timeline 
	jQuery( "#rsm_timeline_bar" ).sortable({
		placeholder: "rsm_drop_zone",
		items: "div.rsm_timeline_post:not(.rsm_no_sort)",
		axis: "x", 
		containment: 'parent',
		stop: function(event, ui) {
			params = 'IdAutoresponder='+jQuery('#jform_IdAutoresponder').val();

			jQuery('#rsm_timeline_bar').children('div.rsm_timeline_post:not(.rsm_no_sort)').each(function(i,item){
				jQuery(item).children('.rsm_bullet').html(i+1);
				params += '&cid[]='+jQuery(item).attr('id').split('msg')[1]+'&order[]='+i;
			});

			// save order
			jQuery.ajax({
				type: 'POST',
				url: 'index.php?option=com_rsmail&task=autoresponder.saveorder',
				dataType: 'html',
				data: params
			});
		}
	});

	// remove all the title messages
	jQuery('.rsm_bullet').click(function(){
		jQuery('.rsm_msg_title, .rsm_msg_bubble').css('display','none');
		
		if(jQuery(this).siblings('.rsm_msg_title, .rsm_msg_bubble').css('display') == 'none')
			jQuery(this).siblings('.rsm_msg_title, .rsm_msg_bubble').css('display','block');
		else
			jQuery(this).siblings('.rsm_msg_title, .rsm_msg_bubble').css('display','none');
	});
	
	// if title messages are clicked don't hide their container
	jQuery('.rsm_msg_title, .rsm_msg_bubble').live('click',function(){ return false; });
	
	// remove title messages if click outsite rsm_bullet
	jQuery(document).click(function(e){	if(!e.target.hasClass('rsm_bullet')) { jQuery('.rsm_msg_title, .rsm_msg_bubble').css('display','none'); } });
	
	// change the date of starting sending emails
	jQuery('.day').live('click',function(){
		jQuery('#rsm_start_sending').html(jQuery('#jform_AutoresponderDate').val());
	});
});
</script>

<form action="<?php echo JRoute::_('index.php?option=com_rsmail&view=autoresponder&layout=edit&IdAutoresponder='.(int) $this->item->IdAutoresponder); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off" class="form-validate form-horizontal">
	<div class="row-fluid">
		<div class="span12">
			<div id="rsm_progress_bar">
				<ul>
					<li id="step_1" class="<?php echo (!empty($this->item->IdAutoresponder) ? 'rsm_done_first' : 'rsm_active');?>"><?php echo JText::_('RSM_EDIT_FOLLOWUP_STEP_ONE_LIST_ITEM');?></li>
					<li id="step_2" class="<?php echo (!empty($this->item->IdAutoresponder) ? 'rsm_done' : '');?>"><?php echo JText::_('RSM_EDIT_FOLLOWUP_STEP_TWO_LIST_ITEM');?></li>
					<li id="step_3" class="<?php echo (!empty($this->item->IdAutoresponder) ? 'rsm_active' : '');?>"><?php echo JText::_('RSM_EDIT_FOLLOWUP_STEP_THREE_LIST_ITEM');?></li>
				</ul>
			</div>
		</div>
		
		<div class="rsm_clear"></div>
		
		<div class="span6 rsleft rsspan6">
			<fieldset id="rsm_autores_step1_fieldset">
				<legend><?php echo JText::_('RSM_STEP_1');?></legend>
				<?php echo JHtml::_('rsfieldset.start', 'adminform'); ?>
				<?php echo JHtml::_('rsfieldset.element', $this->form->getLabel('AutoresponderName'), $this->form->getInput('AutoresponderName')); ?>
				<?php echo JHtml::_('rsfieldset.element', $this->form->getLabel('AutoresponderType'), $this->form->getInput('AutoresponderType')); ?>
				<?php echo JHtml::_('rsfieldset.element', $this->form->getLabel('AutoresponderDate'), $this->form->getInput('AutoresponderDate')); ?>
				<?php echo JHtml::_('rsfieldset.end'); ?>
			</fieldset>
		</div>
		
		<div class="span6 rsleft rsspan6">
			<fieldset id="rsm_autores_step2_fieldset">
				<legend><?php echo JText::_('RSM_STEP_2');?></legend>
				<p><?php echo JText::_('RSM_EDIT_STEP_2_FOLLOWUP_DESC');?></p>

				<p>
					<label style="clear:none;line-height:22px;">
						<small><strong><?php echo JText::_('RSM_CHECK_ALL_LISTS');?></strong></small>
						<input class="rsm_save_ar" type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);"/>
					</label>
				</p>
				
				<span class="rsm_clear"></span>
				
				<div id="rsm_lists_box">
				<?php $AutoresponderLists = !empty($this->item->IdLists) ? explode(',',$this->item->IdLists) : array(); ?>
				<?php foreach ($this->lists as $i => $list) { ?>
				<?php $checked = in_array($list->IdList,$AutoresponderLists) ? 'checked="checked"' : ''; ?>
				<?php $name = empty($list->ListName) ? JText::_('RSM_NO_LIST') : $list->ListName; ?>
					<label>
						<?php echo $name; ?>
						<input type="checkbox" id="cb<?php echo $i; ?>" name="cid[]" value="<?php echo $list->IdList; ?>" class="rsm_save_ar" onclick="Joomla.isChecked(this.checked);" <?php echo $checked; ?> />
					</label>
				<?php } ?>
				</div>
			</fieldset>
		</div>
		
		<div class="rsm_clear"></div>
		
		<div class="span12">
			<fieldset id="rsm_autores_step3_fieldset">
				<legend><?php echo JText::_('RSM_STEP_3');?></legend>
				<p><?php echo JText::_('RSM_EDIT_STEP_3_FOLLOWUP_DESC');?></p>
				<div id="rsm_timeline_bar">
					<div class="rsm_no_sort" id="rsm_first_item">
						<strong id="rsm_start_sending"><?php echo JText::_('RSM_START_FOLLOWUP_AFTER_SUBSCRIPTION');?></strong>
					</div>

					<?php $n = count($this->messages); ?>
					<?php foreach ($this->messages as $i => $message) { ?>
					<?php $period = explode(' ',$message->DelayPeriod); ?>
					<?php $period_name = JText::_('RSM_'.strtoupper($period[1]).($period[0] != 1 ? 'S' : '')); ?>
					<div id="msg<?php echo $message->IdAutoresponderMessage; ?>" class="rsm_timeline_post" style="width:<?php echo (75/$n);?>%">
						<span class="rsm_period"><?php echo str_replace( $period[1], $period_name, $message->DelayPeriod);?></span>
						<span class="rsm_bullet"><?php echo $i+1;?></span>
						<span class="rsm_msg_bubble" style="width:<?php echo (115-$n);?>%" ></span>
						<div class="rsm_msg_title">
							<a href="<?php echo JRoute::_('index.php?option=com_rsmail&view=autoresponder&layout=message&id='.$message->IdMessage.'&IdAutoresponder='.$this->item->IdAutoresponder.'&IdAutoresponderMessage='.$message->IdAutoresponderMessage.'&tmpl=component'); ?>" class="modal" rel="{handler: 'iframe' ,size: {x: 850, y: 450}}">
								<?php echo $message->MessageSubject; ?>
							</a>
							<a href="<?php echo JRoute::_('index.php?option=com_rsmail&task=autoresponder.deletemessage&id='.$message->IdAutoresponderMessage); ?>" class="rsm_del_msg">
								<img src="<?php echo JURI::root();?>administrator/components/com_rsmail/assets/images/12x12-remove.png" alt="" />
							</a>
						</div>
					</div>
					<?php } ?>
					
					<div class="rsm_timeline_post rsm_no_sort" id="rsm_last_item">
						<span class="rsm_bullet hasTip" title="<?php echo JText::_('RSM_FOLLOW_UP_ADD_NEW_MESSAGE');?>">+</span>
						<span class="rsm_msg_bubble"></span>
						<div class="rsm_msg_title">
							<select name="IdMessage" id="IdMessage">
								<option value="0"><?php echo JText::_('RSM_SELECT_MESSAGES'); ?></option>
								<?php echo JHtml::_('select.options', $this->subjects); ?>
							</select>
							<a id="rsm_modal" style="display:none;margin-left: 5px;margin-top: 4px;" href="javascript:void(0);" rel="{handler: 'iframe' ,size: {x: 850, y: 450}}" class="modal rsm_btn rsm_left"><?php echo JText::_('RSM_ADD_BTN_MESSAGE'); ?></a>
							<a id="rsm_no_modal" class="rsm_btn rsm_left" style="margin-left: 5px;margin-top: 4px;" href="javascript:void(0);" onclick="alert('<?php echo JText::_('RSM_FOLLOWUP_SELECT_MESSAGE_FIRST',true); ?>');" ><?php echo JText::_('RSM_ADD_BTN_MESSAGE'); ?></a>
						</div>
					</div>
				</div>
			</fieldset>
		</div>
		
	</div>

	<?php echo JHTML::_( 'form.token' ); ?>
	<?php echo $this->form->getInput('IdAutoresponder'); ?>
	<input type="hidden" name="option" value="com_rsmail" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" id="boxchecked" value="0" />
</form>

<script type="text/javascript">
function rsm_update() {
	var cids = document.getElementsByName('cid[]');
	var j=0;
	
	for(i=0;i<cids.length;i++) {
		if ($('cb'+i).checked) j++;
	}
	document.getElementById('boxchecked').value = j;
}
rsm_update();
rsm_change_type('<?php echo $this->item->AutoresponderType; ?>');
</script>