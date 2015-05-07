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

<form action="<?php echo JRoute::_('index.php?option=com_rsmail&task=sendunsubscribelink'); ?>" method="post">
	<p><?php echo JText::_('RSM_GET_UNSUBSCRIBE_LINK_DESC'); ?></p>
	<input name="unsubscriber_email" id="unsubscriber_email" class="inputbox" type="text" value="" />
	<button type="submit" class="btn btn-primary button"><?php echo JText::_('RSM_GET_UNSUBSCRIBE_LINK'); ?></button>
</form>
<div style="width:100%;clear:both"></div>