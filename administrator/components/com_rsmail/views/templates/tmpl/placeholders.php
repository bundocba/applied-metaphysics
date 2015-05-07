<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access'); ?>
<script type="text/javascript">
function rsm_show(plugin) {
	document.getElementById('rsm_frame').src = 'index.php?option=com_rsmail&task=content&tmpl=component&selector=<?php echo $this->selector; ?>&plugin='+plugin;
}
</script>

<?php JFactory::getApplication()->triggerEvent('rsm_addPlaceholderButtons'); ?>
<div class="rsm_clear"></div>
<hr />
<div class="rsm_pla_content">
	<iframe src="" id="rsm_frame" width="100%" frameborder="0" height="100%"></iframe>
</div>