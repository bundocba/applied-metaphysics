<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access'); ?>
<fieldset>
	<legend><?php echo JText::_('RSM_TEST_EMAIL'); ?></legend>
	<strong class="rsemailtest"><?php echo JText::_('RSM_EMAIL_TO_TEST'); ?></strong>
	<input type="text" class="input-xlarge" name="preview" value="" size="40" /> 
	<button class="btn btn-primary button" type="submit" onclick="Joomla.submitbutton('message.test');"><?php echo JText::_('RSM_SEND'); ?></button>
</fieldset>

<fieldset>
	<legend><?php echo JText::_('RSM_SPAM_CHECK'); ?></legend>
	<?php if ($this->spam) { ?>
	<iframe scrolling="yes" id="ifrSpamCheck" frameborder="no" style="border: 0px solid ; width: 100%; height: 380px;" src="http://www.rsjoomla.com/spamcheck?sess=<?php echo rsmailHelper::genKeyCode(); ?>&mess=<?php echo md5($this->item->MessageSenderEmail.$this->item->MessageSubject); ?>"/></iframe>
	<?php } ?>
</fieldset>