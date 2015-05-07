<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die('Restricted access'); ?>
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
		<td align="center" class="center hidden-phone"><?php echo (empty($item->username)) ? JText::_('RSM_GUEST') : $item->username; ?></a></td>
		<td align="center" class="center hidden-phone"><?php echo $item->SubscriberIp; ?></a></td>
	</tr>
<?php } ?>