<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die('Restricted access'); 
JHTML::_('behavior.tooltip');
JHTML::_('behavior.modal'); ?>

<script type="text/javascript">
Joomla.submitbutton = function(task) {
	var cids = document.getElementsByName('cid[]');
	var checkfields	= false;
	for(i=0;i<cids.length; i++) {
		if(document.getElementsByName('cid[]')[i].checked === true) {
			checkfields = true;
			break;
		}
	}

	if (task == 'charts') {
		document.location = 'index.php?option=com_rsmail&view=subscribers&layout=charts';
	} else if (task == 'export') {
		if (checkfields === false && document.getElementById('rsm_filtered_results').value == 0 || ( document.getElementById('rsm_filtered_results').value == 1 && cids.length == 0)) {
			alert("<?php echo JText::_('RSM_ERROR_CHECK_FIELDS',true);?>");
			return;
		} else {
			exportCSV();
		}
	} else if (task == 'subscribers.unsubscribe') {
		if (checkfields === false && document.getElementById('rsm_filtered_results').value == 0) {
			alert("<?php echo JText::_('RSM_ERROR_CHECK_FIELDS',true);?>");
			return;
		} else {
			if (confirm("<?php echo JText::_('RSM_CONFIRM_UNSUBSCRIBE',true);?>"))
				Joomla.submitform(task);
		}
	} else if (task == 'subscribers.copy') {
		if (checkfields === false && document.getElementById('rsm_filtered_results').value == 0 || ( document.getElementById('rsm_filtered_results').value == 1 && cids.length == 0)) {
			alert("<?php echo JText::_('RSM_ERROR_CHECK_FIELDS',true);?>");
			return;
		} else 
			Joomla.submitform(task);
	} else if (task == 'subscribers.remove') {
		if (checkfields === false && document.getElementById('rsm_filtered_results').value == 0) {
			alert("<?php echo JText::_('RSM_ERROR_CHECK_FIELDS',true);?>");
			return;
		}

		var filter_lists = document.getElementsByName('rsm_lists[]');
		var lists_param = '';
		if(filter_lists.length > 0) {
			lists_param = '&filtered_lists=';
			for(i=0;i<filter_lists.length; i++)
				lists_param += filter_lists[i].value+',';
		}
		
		SqueezeBox.open('<?php echo JURI::root();?>administrator/index.php?option=com_rsmail&view=subscribers&layout=remove&tmpl=component'+lists_param , {handler: 'iframe',size: {x: '600', y: '400'}});
	} else if(task == 'subscribers.send') {
		if (checkfields === false && document.getElementById('rsm_filtered_results').value == 0 || ( document.getElementById('rsm_filtered_results').value == 1 && cids.length == 0)) {
			alert("<?php echo JText::_('RSM_ERROR_CHECK_FIELDS',true);?>");
			return;
		} else 
			Joomla.submitform(task);
	} else if (task == 'import') {
		document.location = 'index.php?option=com_rsmail&view=import';
	} else 
		Joomla.submitform(task);
}
</script>

<div class="row-fluid">
	<div class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div class="span10">
		<div id="com-rsmail-import-progress" class="com-rsmail-progress" style="display:none;"><div style="width: 1%;" id="com-rsmail-bar" class="com-rsmail-bar">0%</div></div>
		
		<div id="rsmail_container_top">
			<form action="<?php echo JRoute::_('index.php?option=com_rsmail'); ?>" id="rsm_filter_form">
				<div class="rsm_filter_toolbar" id="rsm_filter_options">
					<ul>
						<li id="rsm_show_lbl"><?php echo JText::_('RSM_SHOW');?></li>
						<li id="rsm_published"><a href="javascript: void(0);"><?php echo JText::_('RSM_SUBSCRIBE_STATUS_1');?></a>
							<ul>
								<li><a href="" class="rsm_tick" rel="1"><?php echo JText::_('RSM_SUBSCRIBE_STATUS_1');?></a></li>
								<li><a href="" rel="0"><?php echo JText::_('RSM_SUBSCRIBE_STATUS_0');?></a></li>
							</ul>
						</li>
						<li id="rsm_from_lbl"><?php echo JText::_('RSM_FROM');?> </li>
						<li id="rsm_list"><a href="javascript: void(0);"><?php echo JText::_('RSM_SELECT_FILTER_LIST'); ?></a>
							<ul>
								<li><a href="javascript: void(0);" class="rsm_tick" rel="0"><?php echo JText::_('RSM_SELECT_FILTER_LIST'); ?></a></li>
								<?php foreach($this->SubscriberLists as $list) { ?>
								<li><a href="javascript: void(0);" rel="<?php echo $list->IdList;?>"><?php echo $list->ListName; ?></a></li>
								<?php } ?>
							</ul>
						</li>
						<li id="rsm_field"><a href="javascript: void(0);"><?php echo JText::_('RSM_NO_FILTER'); ?></a>
							<ul>
								<li><a href="javascript: void(0);" rel="0" class="rsm_tick"><?php echo JText::_('RSM_NO_FILTER'); ?></a></li>
								<li><a href="javascript: void(0);" rel="email" ><?php echo JText::_('RSM_EMAIL'); ?></a></li>
							</ul>
						</li>
						<li id="rsm_operator"><a href="javascript: void(0);"><?php echo JText::_('RSM_CONTAINS'); ?></a>
							<ul>
								<li><a href="javascript: void(0);" rel="contains" class="rsm_tick"><?php echo JText::_('RSM_CONTAINS') ?></a></li>
								<li><a href="javascript: void(0);" rel="not_contain"><?php echo JText::_('RSM_NOT_CONTAIN') ?></a></li>
								<li><a href="javascript: void(0);" rel="is"><?php echo JText::_('RSM_IS') ?></a></li>
								<li><a href="javascript: void(0);" rel="is_not"><?php echo JText::_('RSM_IS_NOT') ?></a></li>
							</ul>
						</li>
						<li id="rsm_text_input">
							<input type="text" value="" id="rsm_filter_text" />
						</li>                    	                    	
					</ul>
				</div>
				
				<button type="button" id="rsm_filter" class="rsm_button"><?php echo JText::_('RSM_FILTER'); ?></button>
				<a href="javascript: void(0);" id="rsm_select_all_btn" ><?php echo JText::sprintf('RSM_SELECT_ALL_RESULTS', $this->pagination->total);?></a>
				
			</form>
			<div id="rsm_subscribers_info"> <?php echo JText::sprintf('RSM_DISPLAY_RESULTS',($this->pagination->total >= $this->pagination->limit ? $this->pagination->limit : $this->pagination->total),$this->pagination->total);?></div>
		</div>
		
		<div class="rsm_clear"></div>
		<div class="rsm_filter_toolbar" id="rsm_condition"  style="<?php echo ( count($this->filter_published) > 1 ? 'display:block;' : 'display:none;');?>">
			<ul>
				<li><a href="javascript: void(0);" class="rsm_tick" rel="AND"><?php echo JText::_('RSM_'.$this->filter_condition);?></a>
					<ul>
						<li><a id="rsm_condition_and" href="javascript: void(0);" <?php echo ($this->filter_condition == 'AND' ? 'class="rsm_tick"' : '');?> rel="AND"><?php echo JText::_('RSM_AND');?></a></li>
						<li><a id="rsm_condition_or" href="javascript: void(0);" <?php echo ($this->filter_condition == 'OR' ? 'class="rsm_tick"' : '');?> rel="OR"><?php echo JText::_('RSM_OR');?></a></li>
					</ul>
				</li>
			</ul>
			<input name="rsm_condition" type="hidden" value="<?php echo $this->filter_condition;?>" />
		</div>
		<ul id="rsm_filters">	
			<?php if (!empty($this->filter_published)) { ?>
				<?php for ($i=0; $i<count($this->filter_published); $i++) { ?>
				<?php if($i > 0) { ?>
					<li class="rsm_filter_condition">
						<?php echo JText::_('RSM_'.$this->filter_condition);?>
					</li>
				<?php } ?>
				<li>
					<span><?php echo JText::_('RSM_SUBSCRIBE_STATUS_'.$this->filter_published[$i]); ?></span>
					<span><?php echo $this->filter_lists[$i]['ListName']; ?></span>
					<?php if(!empty($this->filter_fields[$i]['IdListFields'])) { ?>
							<span><?php echo $this->filter_fields[$i]['FieldName']; ?></span>
						<?php if(!empty($this->filter_fields[$i]['IdListFields'])) { ?>
								<span><?php echo JText::_('RSM_'.strtoupper($this->escape($this->filter_operators[$i]))); ?></span>
								<strong><?php echo $this->escape($this->filter_values[$i]); ?></strong>
						<?php } ?>
					<?php } ?>
					<a class="rsm_close" style="margin-left: 0; padding-left: 0; border-left: 0" href="javascript: void(0);"></a>
					<input type="hidden" name="rsm_published[]" value="<?php echo $this->escape($this->filter_published[$i]); ?>" 	/>
					<input type="hidden" name="rsm_lists[]" value="<?php echo $this->escape($this->filter_lists[$i]['IdList']); ?>" 	/>
					<input type="hidden" name="rsm_fields[]" value="<?php echo (isset($this->filter_fields[$i]['IdListFields']) ? $this->escape($this->filter_fields[$i]['IdListFields']) : ''); ?>" />
					<input type="hidden" name="rsm_operators[]" value="<?php echo $this->escape($this->filter_operators[$i]); ?>" />
					<input type="hidden" name="rsm_values[]" value="<?php echo $this->escape($this->filter_values[$i]); ?>" 	/>
				</li>
				<?php } ?>
				<?php if (count($this->filter_lists) > 2) { ?>
				<li id="rsm_clear_filters"><?php echo JText::_('RSM_CLEAR_ALL_FILTERS'); ?></li>
				<?php } ?>
			<?php } ?>
		</ul>
		<div class="rsm_clear"></div>
		
		<form action="<?php echo JRoute::_('index.php?option=com_rsmail&view=subscribers'); ?>" method="post" name="adminForm" id="adminForm">
			<table class="adminlist table table-striped">
				<thead>
					<tr>
						<th width="1%" align="center" class="center hidden-phone">#</th>
						<th width="1%" align="center" class="center hidden-phone"><input type="checkbox" name="checkall-toggle" id="checkAll" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this);"/></th>
						<th width="50%"><?php echo JHtml::_('grid.sort', 'RSM_SUBSCRIBER_EMAIL', 's.SubscriberEmail', $this->listDirn, $this->listOrder);?></th>
						<th width="5%" align="center" class="nowrap center hidden-phone"> <?php echo JHtml::_('grid.sort', 'RSM_DATE_SUBSCRIBED',  's.DateSubscribed', $this->listDirn, $this->listOrder); ?></th>
						<th width="5%" align="center" class="nowrap center hidden-phone"> <?php echo JHtml::_('grid.sort', 'RSM_USERNAME', 		'u.username', $this->listDirn, $this->listOrder); ?></th>
						<th width="5%" align="center" class="nowrap center hidden-phone"> <?php echo JHtml::_('grid.sort', 'RSM_USER_IP', 			's.SubscriberIp', $this->listDirn, $this->listOrder); ?></th>
					</tr>
				</thead>
				<tbody id="rsm_subscribers">
				<?php foreach ($this->data as $i => $item) { ?>
				<?php $email = ($item->SubscriberEmail) ? $item->SubscriberEmail : JText::_('RSM_NO_EMAIL_TO_EDIT'); ?>
					<tr class="row<?php echo $i % 2; ?>">
						<td align="center" class="center hidden-phone"><?php echo $item->IdSubscriber; ?></td>
						<td align="center" class="center hidden-phone"><?php echo JHTML::_('grid.id',$i,$item->IdSubscriber); ?></td>
						<td>
							<a href="<?php echo JRoute::_('index.php?option=com_rsmail&task=subscriber.edit&IdSubscriber='.$item->IdSubscriber); ?>" class="rsm_list_email"><?php echo $email; ?></a>
							<span class="rsm_subscriber_info">
								<img src="<?php echo JURI::root(); ?>administrator/components/com_rsmail/assets/images/icons/info.png" alt="" class="hasTip" title="<?php echo rsmailHelper::userlists($item->SubscriberEmail); ?>" />
							</span>
						</td>
						<td align="center" class="center hidden-phone"><?php echo rsmailHelper::showDate($item->DateSubscribed); ?></a></td>
						<td align="center" class="center hidden-phone"><?php echo empty($item->username) ? JText::_('RSM_GUEST') : $item->username; ?></a></td>
						<td align="center" class="center hidden-phone"><?php echo $item->SubscriberIp; ?></a></td>
					</tr>
				<?php }
				$currentPages	= rsmailHelper::isJ3() ? $this->pagination->pagesCurrent : $this->pagination->{'pages.current'};
				$difference 	= ($this->pagination->total - ($currentPages * $this->pagination->limit));
				$load_results 	= ($difference > 0 && $difference < $this->pagination->limit ? $difference : ( $this->pagination->total > $this->pagination->limit ?  $this->pagination->limit : 0));
				?>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="6">
							<?php $style = $load_results > 0 ? 'style="display:block;"' : 'style="display:none;"' ?>
							<a href="javascript: void(0);" id="rsm_load_more_results" rel="<?php echo $this->pagination->limit; ?>" <?php echo $style; ?>><?php echo JText::sprintf('RSM_LOAD_MORE_RESULTS', $load_results);?></a>
						</td>
					</tr>
				</tfoot>
			</table>
		
		<?php echo JHTML::_( 'form.token' ); ?>
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="position" id="rsmail_position" value="0" />
		<input type="hidden" name="filtered_results" id="rsm_filtered_results" value="0" />
		<input type="hidden" name="from_lists" id="rsm_from_lists" value="" />
		<input type="hidden" name="filter_order" value="<?php echo $this->listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->listDirn; ?>" />
		<input type="hidden" name="rslimit" id="rslimit" value="<?php echo $this->pagination->limit; ?>" />
		</form>
	</div>
</div>