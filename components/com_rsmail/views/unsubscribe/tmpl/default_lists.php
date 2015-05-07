<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.keepalive'); ?>

<?php if ($this->params->get('show_page_heading', 1)) { ?>
	<h1><?php echo $this->params->get('page_heading', ''); ?></h1>
<?php } ?>

<?php if(!empty($this->lists)) { ?>
	<form action="<?php echo JRoute::_('index.php?option=com_rsmail&task=unsubscribe');?>" method="post" class="rsm_unsubscribe_form">
		<?php echo JText::_('RSM_EMAIL_ADDRESS'); ?> <input type="text" name="rsm_unsub_email" id="rsm_unsub_email" value="<?php echo $this->escape($this->email); ?>" size="40" />

			<?php if($this->config->unsubscribe_option == 'userchoice') { ?>
				<p id="rsm_unsubscribe_description"><?php echo JText::_('RSM_UNSUBSCRIBE_DESC');?></p>
				<p id="rsm_lists_container">
					<?php $i=0; ?>
					<?php foreach($this->lists as $list) { ?>
					<?php $i++; ?>
						<label><input type="checkbox" name="lists[]" value="<?php echo $list->IdList;?>" /> <?php echo $list->ListName;?></label>
						<?php echo (($i%4) == 0 ) ? '<br style="clear:both;">' : '';?>
					<?php } ?>
				</p>
			<?php } else { ?>
			<?php $lists = explode(',',$this->config->unsubscribe_lists); ?>
			<?php if (!empty($lists)) { ?>
			<?php foreach($lists as $list) { ?>
			<input type="hidden" name="lists[]" value="<?php echo $list; ?>" />
			<?php } ?>
			<?php } ?>
			<?php } ?>
		
		<button type="submit" class="button btn btn-primary"><?php echo JText::_('RSM_UNSUBSCRIBE'); ?></button>
		
		<?php echo JHTML::_('form.token'); ?>
		<input type="hidden" name="vid" value="<?php echo $this->vid; ?>" />
		<input type="hidden" name="IdSession" value="<?php echo $this->cid; ?>" />
		<input type="hidden" name="option" value="com_rsmail" />
		<input type="hidden" name="task" value="unsubscribe" />
	</form>
<?php } else { ?>
<p id="rsm_unsubscribe_description"><?php echo JText::sprintf('RSM_NO_LISTS_SUBSCRIBED',$this->escape($this->email));?></p>
<?php } ?>