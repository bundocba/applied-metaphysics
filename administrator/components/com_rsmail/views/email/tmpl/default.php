<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined( '_JEXEC' ) or die( 'Restricted access' );

//keep session alive while editing
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>

<form action="<?php echo JRoute::_('index.php?option=com_rsmail&view=email&tmpl=component&type='.$this->type); ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal" autocomplete="off">
	<div class="row-fluid">
		<div class="span12">
			<div style="width:100%;text-align:right;">
				<button type="button" onclick="Joomla.submitbutton('email.save');" class="btn btn-primary button"><?php echo JText::_('RSM_SAVE_BTN'); ?></button>
				<button type="button" onclick="window.parent.SqueezeBox.close();" class="btn button"><?php echo JText::_('RSM_CANCEL_BTN'); ?></button>
			</div>
			<?php 
				echo JHtml::_('rsfieldset.start', 'adminform', JText::_('RSM_CONF_MESSAGE_'.strtoupper($this->type)));
				echo JHtml::_('rsfieldset.element', $this->form->getLabel('language'), $this->form->getInput('language'));
				
				if (in_array($this->type, array('confirmation','unsubscribe')))
					echo JHtml::_('rsfieldset.element', $this->form->getLabel($this->type.'_enable'), $this->form->getInput($this->type.'_enable'));
				
				if ($this->type != 'thankyou') {
					echo JHtml::_('rsfieldset.element', $this->form->getLabel($this->type.'_from'), $this->form->getInput($this->type.'_from'));
					echo JHtml::_('rsfieldset.element', $this->form->getLabel($this->type.'_fromname'), $this->form->getInput($this->type.'_fromname'));
					echo JHtml::_('rsfieldset.element', $this->form->getLabel($this->type.'_subject'), $this->form->getInput($this->type.'_subject'));
					echo JHtml::_('rsfieldset.element', $this->form->getLabel($this->type.'_mode'), $this->form->getInput($this->type.'_mode'));
				}
				
				echo JHtml::_('rsfieldset.end');
			?>
			
			<div class="clr"></div>
			<?php echo $this->form->getInput($this->type.'_message'); ?>
			<div>
			<?php echo JHtml::_('form.token'); ?>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="jform[type]" value="<?php echo $this->type; ?>" />
			</div>
		</div>
	</div>
</form>