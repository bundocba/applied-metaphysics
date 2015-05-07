<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access'); ?>

<?php if (JFactory::getApplication()->input->get('tmpl') == 'component') { ?>
	<script type="text/javascript">setTimeout('window.print()', 700 );</script>
<?php } else { ?>
	<div class="rsm_header">
		<?php if (empty($this->code)) { ?>
		<a style="float:left;" href="<?php echo JRoute::_('index.php?option=com_rsmail&view=history',false); ?>">
			<img src="<?php echo JURI::root(); ?>components/com_rsmail/images/back.png" alt="<?php echo JText::_('RSM_BACK'); ?>" style="vertical-align:middle;" />
			<?php echo JText::_('RSM_BACK'); ?>
		</a>
		<?php } ?>
		<span>
			<?php $print = !empty($this->code) ? JRoute::_('index.php?option=com_rsmail&view=history&layout=message&code='.$this->code.'&tmpl=component',false) : JRoute::_('index.php?option=com_rsmail&view=history&layout=message&cid='.$this->message->IdMessage.'&sess='.$this->message->IdSession.'&tmpl=component',false); ?>
			<a onclick="window.open(this.href,'win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no'); return false;" href="<?php echo $print; ?>">
				<img src="<?php echo JURI::root(); ?>components/com_rsmail/images/print.png" alt="<?php echo JText::_('RSM_PRINT'); ?>" style="vertical-align:middle;" />
			</a>
		</span>
	</div>
<?php } ?>

<div class="rsm_message">
	<h3><?php echo $this->message->MessageSubject; ?></h3>
	<?php echo $this->message->MessageBody; ?>
	
	<?php if (!empty($this->message->attachements) && JFactory::getApplication()->input->get('tmpl') != 'component') { ?>
	<?php $code = $this->code ? '&code='.$this->code : ''; ?>
		<ul class="rsm_attachements">
			<?php foreach ($this->message->attachements as $attachement) { ?>
				<li>
					<a href="<?php echo JRoute::_('index.php?option=com_rsmail&task=download&id='.$attachement->IdFile.$code); ?>"><?php echo $attachement->FileName; ?></a>
				</li>
			<?php } ?>
		</ul>
	<?php } ?>
</div>