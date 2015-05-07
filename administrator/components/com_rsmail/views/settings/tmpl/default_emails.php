<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<table class="adminlist table table-striped">
	<tbody>
		<tr>
			<td><a class="modal hasTip" rel="{handler: 'iframe'}" title="<?php echo JText::_('RSM_CONFIRMATION_EMAIL_DESC'); ?>" href="<?php echo JRoute::_('index.php?option=com_rsmail&view=email&type=confirmation&tmpl=component'); ?>"><?php echo JText::_('RSM_CONFIRMATION_EMAIL'); ?></a></td>
		</tr>
		<tr>
			<td><a class="modal hasTip" rel="{handler: 'iframe'}" title="<?php echo JText::_('RSM_UNSUBSCRIBE_MAIL_DESC'); ?>" href="<?php echo JRoute::_('index.php?option=com_rsmail&view=email&type=unsubscribe&tmpl=component'); ?>"><?php echo JText::_('RSM_UNSUBSCRIBE_MAIL'); ?></a></td>
		</tr>
		<tr>
			<td><a class="modal hasTip" rel="{handler: 'iframe'}" title="<?php echo JText::_('RSM_UNSUBSCRIBELINK_MAIL_DESC'); ?>" href="<?php echo JRoute::_('index.php?option=com_rsmail&view=email&type=unsubscribelink&tmpl=component'); ?>"><?php echo JText::_('RSM_UNSUBSCRIBELINK_MAIL'); ?></a></td>
		</tr>
		<tr>
			<td><a class="modal hasTip" rel="{handler: 'iframe'}" title="<?php echo JText::_('RSM_THANKYOUMESSAGE_DESC'); ?>" href="<?php echo JRoute::_('index.php?option=com_rsmail&view=email&type=thankyou&tmpl=component'); ?>"><?php echo JText::_('RSM_THANKYOUMESSAGE'); ?></a></td>
		</tr>
	</tbody>
</table>