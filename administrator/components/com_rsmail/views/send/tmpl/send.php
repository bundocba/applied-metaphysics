<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.keepalive');
$percentage = $this->max == 0 ? 0 : (ceil($this->session->Position * 100 / $this->max) > 100) ? 100 : ceil($this->session->Position * 100 / $this->max);
?>

<script type="text/javascript">
Joomla.submitbutton = function(task) {
	if (task == 'cancel') {
		document.location = '<?php echo JRoute::_('index.php?option=com_rsmail&view=sessions',false); ?>';
	} else {
		Joomla.submitform(task);
	}
}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_rsmail'); ?>" id="adminForm" name="adminForm">
<div class="row-fluid">
	<div class="span12">
		<div class="well">
			<div style="text-align:center;">
				<img src="<?php echo JURI::root(); ?>/administrator/components/com_rsmail/assets/images/icons/info-32.png"/>
				<?php echo JText::_('RSM_SEND_WARNING'); ?>
			</div>
		</div>
		<?php echo JText::sprintf('RSM_INFO_SESSION',$this->name,$this->listnames); ?> <br/><br/>
		
		<div id="com-rsmail-import-progress" class="com-rsmail-progress" style="display:none;"><div style="width: <?php echo $percentage;?>%;" id="com-rsmail-bar" class="com-rsmail-bar"><?php echo $percentage;?>%</div></div>
		
		<button class="btn btn-primary button" type="button" onclick="document.getElementById('rsmstatus').innerHTML = 'resume';rsm_send(<?php echo $this->session->IdSession; ?>);"><?php echo JText::_('RSM_START'); ?></button> 
		<button class="btn button" type="button" onclick="document.getElementById('rsmstatus').innerHTML = 'paused';"><?php echo JText::_('RSM_PAUSE'); ?></button>
		
		<span class="rsm_invisible" id="IdSession"><?php echo $this->session->IdSession; ?></span>
		<span class="rsm_invisible" id="IdMessage"><?php echo $this->session->IdMessage; ?></span>
		<span class="rsm_invisible" id="rsmstatus"></span>
	</div>
</div>

<input type="hidden" name="task" value="" />
</form>