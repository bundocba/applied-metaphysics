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
JHTML::_('behavior.tooltip'); ?>

<script type="text/javascript">
Joomla.submitbutton = function(task) {
	if (task == 'template.preview') {
		window.open('index.php?option=com_rsmail&view=template&layout=preview&id=<?php echo $this->item->IdTemplate; ?>' , 'win2' , 'status=no, toolbar=no, scrollbars=yes, titlebar=no, menubar=no, resizable=yes, width=640,height=480,directories=no,location=no');
	} else if (task == 'template.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
		Joomla.submitform(task, document.getElementById('adminForm'));
	} else {
		alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
	}
}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_rsmail&view=template&layout=edit&IdTemplate='.(int) $this->item->IdTemplate); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off" class="form-validate form-horizontal">
	<div class="row-fluid">
		<div class="span8 rsleft rsspan8">
			<?php 
				echo JHtml::_('rsfieldset.start', 'adminform');
				echo JHtml::_('rsfieldset.element', $this->form->getLabel('TemplateName'), $this->form->getInput('TemplateName'));
				echo JHtml::_('rsfieldset.element', $this->form->getLabel('MessageType'), $this->form->getInput('MessageType'));
				echo JHtml::_('rsfieldset.element', $this->form->getLabel('TemplateBody'), '<span class="rsm_message">'.$this->form->getInput('TemplateBody').'</span>');
				echo JHtml::_('rsfieldset.element', $this->form->getLabel('TemplateText'), $this->form->getInput('TemplateText'));
				echo JHtml::_('rsfieldset.end');
			?>
		</div>
		
		<div class="span4 rsleft rsspan4">
			<?php 
				echo JHtml::_('rsfieldset.start', 'adminform');
				echo JHtml::_('rsfieldset.element', $this->form->getLabel('MessageSenderEmail'), $this->form->getInput('MessageSenderEmail'));
				echo JHtml::_('rsfieldset.element', $this->form->getLabel('MessageSenderName'), $this->form->getInput('MessageSenderName'));
				echo JHtml::_('rsfieldset.element', $this->form->getLabel('MessageReplyTo'), $this->form->getInput('MessageReplyTo'));
				echo JHtml::_('rsfieldset.element', $this->form->getLabel('MessageReplyToName'), $this->form->getInput('MessageReplyToName'));
				echo JHtml::_('rsfieldset.end');
			?>
		</div>
	</div>

	<?php echo JHTML::_('form.token'); ?>
	<input type="hidden" name="task" value="" />
	<?php echo $this->form->getInput('IdTemplate'); ?>
</form>