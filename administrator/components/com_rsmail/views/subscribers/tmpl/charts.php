<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2012 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip'); ?>

<script type="text/javascript">
Joomla.submitbutton = function(task) {
	if (task == 'back')
		document.location = '<?php echo JRoute::_('index.php?option=com_rsmail&view=subscribers',false); ?>';
	else 
		Joomla.submitform(task);
}
</script>

<?php if (!empty($this->data)) { ?>
<script type="text/javascript">
	google.load("visualization", "1", {packages:["corechart"]});
	google.setOnLoadCallback(drawChart);
	function drawChart() {
		var data = new google.visualization.DataTable();
		data.addColumn('string');
		<?php $dates = $types = $values = array(); ?>
		<?php $i= 0; ?>
		<?php $j = 1; ?>
		<?php foreach($this->data as $result) {
			if (!isset($dates[$result->date])) {
				$dates[$result->date] = $i;
				$i++;
				echo 'data.addRows(1);'."\n";
				$thedate = JHtml::date($result->date,$this->format);
				echo 'data.setValue('.$dates[$result->date].', 0, "'.$thedate.'");'."\n";
			}

			if (!isset($types[$result->list])) {
				$types[$result->list] = $j;
				echo 'data.addColumn("number","'.$result->list.'");'."\n";
				$j++;
			}

			echo 'data.setValue('.$dates[$result->date].', '.$types[$result->list].', '.$result->total.');'."\n";
			$values[$result->date][$result->list] = $result->total;
		}

		foreach ($dates as $date => $number){
			foreach ($types as $type => $typenumber){
				if (!isset($values[$date][$type]))
					echo 'data.setValue('.$number.', '.$typenumber.',0);'."\n";
			}
		}
	?>

		var options = {
		  legend : {position: 'top', alignment: 'center'},
		  title: '<?php echo $this->state ? JText::_('RSM_CHARTS_UNSUBSCRIBERS',true) : JText::_('RSM_CHARTS_SUBSCRIBERS',true); ?>'
		};
		var chart = new google.visualization.ColumnChart(document.getElementById('subscribers_chart'));
		chart.draw(data, options);
	}
</script>
<?php } ?>

<form method="post" action="<?php echo JRoute::_('index.php?option=com_rsmail&view=subscribers&layout=charts'); ?>" name="adminForm" id="adminForm" class="form-validate form-horizontal">
<div class="row-fluid">
	<div class="span12">
		<?php 
			$lists = array();
			foreach ($this->lists as $list) {
				$selected = in_array($list->IdList,$this->selected) ? ' checked="checked"' : '';
				$lists[] = '<input type="checkbox" id="list'.$list->IdList.'" name="lists[]" value="'.$list->IdList.'" '.$selected.' class="rsm_chart_chk" /> <label class="rsm_chart_lbl" for="list'.$list->IdList.'">'.$list->ListName.'</label>';
			}
			
			$interval = '<select name="interval" id="interval" size="1">';
			$interval .= JHtml::_('select.options',$this->intervals,'value','text',$this->interval);
			$interval .= '</select>';
			
			$unsubscribers = '<input type="checkbox" id="unsubscribers" name="unsubscribers" '.($this->state == 1 ? 'checked="checked"' : '').' value="1" />';
			?>
		
		<?php $range = '<span class="rsm_label">'.JText::_('RSM_CHARTS_FROM').'</span> '.JHtml::_('calendar', $this->from, 'from', 'from', '%Y-%m-%d').' <span class="rsm_label">'.JText::_('RSM_CHARTS_TO').'</span> '.JHtml::_('calendar', $this->to, 'to', 'to', '%Y-%m-%d'); ?>
		<?php echo JHtml::_('rsfieldset.start', 'adminform'); ?>
		<?php echo JHtml::_('rsfieldset.element', '<label>'.JText::_('RSM_CHARTS_DATE_RANGE').'</label>', $range); ?>
		<?php echo JHtml::_('rsfieldset.element', '<label>'.JText::_('RSM_CHARTS_LISTS').'</label>', implode(' ',$lists)); ?>
		<?php echo JHtml::_('rsfieldset.element', '<label>'.JText::_('RSM_CHARTS_INTERVAL').'</label>', $interval); ?>
		<?php echo JHtml::_('rsfieldset.element', '<label for="unsubscribers">'.JText::_('RSM_CHARTS_UNSUBSCRIBERS').'</label>', $unsubscribers); ?>
		<?php echo JHtml::_('rsfieldset.element', '<label></label>', '<button type="submit" class="btn btn-primary button">'.JText::_('RSM_CHARTS_GENERATE').'</button>'); ?>
		<?php echo JHtml::_('rsfieldset.end'); ?>
	</div>
	
	<div class="rsm_clear"></div>
	
	<div class="span12">
		<?php if (!empty($this->data)) { ?>
		<div id="subscribers_chart"></div>
		<?php } else { ?>
		<div class="well" style="text-align: center;">
			<?php echo JText::_('RSM_CHARTS_NO_DATA'); ?>
		</div>
		<?php } ?>
	</div>
</div>

	<?php echo JHTML::_( 'form.token' ); ?>
	<input type="hidden" name="task" value="" />
</form>