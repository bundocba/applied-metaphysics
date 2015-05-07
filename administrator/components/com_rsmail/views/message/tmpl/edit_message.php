<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

echo JHtml::_('rsfieldset.start', 'adminform');
echo JHtml::_('rsfieldset.element', $this->form->getLabel('MessageName'), $this->form->getInput('MessageName'));
echo JHtml::_('rsfieldset.element', $this->form->getLabel('MessageSenderEmail'), $this->form->getInput('MessageSenderEmail'));
echo JHtml::_('rsfieldset.element', $this->form->getLabel('MessageSenderName'), $this->form->getInput('MessageSenderName'));
echo JHtml::_('rsfieldset.element', $this->form->getLabel('MessageReplyTo'), $this->form->getInput('MessageReplyTo'));
echo JHtml::_('rsfieldset.element', $this->form->getLabel('MessageReplyToName'), $this->form->getInput('MessageReplyToName'));
echo JHtml::_('rsfieldset.element', $this->form->getLabel('MessageSubject'), $this->form->getInput('MessageSubject'));
echo JHtml::_('rsfieldset.element', $this->form->getLabel('MessageType'), $this->form->getInput('MessageType'));

if ($this->item->IdMessage) {
	if ($this->item->MessageType) {
		echo JHtml::_('rsfieldset.element', $this->form->getLabel('MessageBody'), '<span class="rsm_message">'.$this->form->getInput('MessageBody').'</span>');
		echo JHtml::_('rsfieldset.element', $this->form->getLabel('MessageBodyNoHTML'), $this->form->getInput('MessageBodyNoHTML'));
	} else {
		echo JHtml::_('rsfieldset.element', $this->form->getLabel('MessageBody'), '<span class="rsm_message"><textarea id="jform_MessageBody" name="jform[MessageBody]" rows="20" cols="90" class="input-xxlarge">'.$this->item->MessageBody.'</textarea></span>');
	}
} else {
	echo JHtml::_('rsfieldset.element', $this->form->getLabel('MessageBody'), '<span class="rsm_message">'.$this->form->getInput('MessageBody').'</span>');
	echo JHtml::_('rsfieldset.element', $this->form->getLabel('MessageBodyNoHTML'), $this->form->getInput('MessageBodyNoHTML'));
}

echo JHtml::_('rsfieldset.end');