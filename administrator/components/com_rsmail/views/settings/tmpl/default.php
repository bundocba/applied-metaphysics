<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined( '_JEXEC' ) or die( 'Restricted access' );
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
JHtml::_('behavior.formvalidation'); 
JText::script('RSM_CONF_CRON_PERIOD_HOUR',true);
JText::script('RSM_CONF_CRON_PERIOD_DAY',true);
?>

<form action="<?php echo JRoute::_('index.php?option=com_rsmail&view=settings'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal" autocomplete="off">
	<div class="row-fluid">
		<div class="span2">
			<?php echo $this->sidebar; ?>
		</div>
		<div class="span10">
		<?php foreach ($this->layouts as $layout) {
			// add the tab title
			$this->tabs->title('RSM_CONF_TAB_'.strtoupper($layout), $layout);
			
			// prepare the content
			$content = $this->loadTemplate($layout);
			
			// add the tab content
			$this->tabs->content($content);
		}
		
		// render tabs
		echo $this->tabs->render();
		?>
			<div>
			<?php echo JHtml::_('form.token'); ?>
			<input type="hidden" name="task" value="" />
			</div>
		</div>
	</div>
</form>
<script type="text/javascript">
rsm_unsubscribe_lists();
rsm_calculate_cron_emails();
rsm_notice(document.getElementById('jform_bounce_mail_connection').value);
rsm_check_handle(document.getElementById('jform_bounce_handle').value);
rsm_check_rule(document.getElementById('jform_bounce_rule').value);
rsm_fields(document.getElementById('jform_jur_list').value, '<?php echo $this->config->jur_name; ?>', '<?php echo $this->config->jur_username; ?>');
</script>