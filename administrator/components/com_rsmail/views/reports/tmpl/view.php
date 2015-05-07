<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.keepalive');
JHTML::_('behavior.tooltip'); ?>

<script type="text/javascript">
Joomla.submitbutton = function(task) {
	if (task == 'back') {
		document.location = '<?php echo JRoute::_('index.php?option=com_rsmail&view=reports',false); ?>';
	} else if (task == 'log') {
		document.location = '<?php echo JRoute::_('index.php?option=com_rsmail&view=cronlogs&layout=log&id='.$this->details['IdSession'],false); ?>';
	} else {
		Joomla.submitform(task);
	}
}
</script>

<script type="text/javascript">
google.load("visualization", "1", {packages:["corechart"]});
google.setOnLoadCallback(drawSessionChart);
google.setOnLoadCallback(drawOpensChart);

function drawSessionChart() {
	var data = google.visualization.arrayToDataTable([
		['', ''],
		['<?php echo JText::_('RSM_CHARTS_EMAILS_SENT',true); ?>',		<?php echo $this->details['sentmessages']; ?>],
		['<?php echo JText::_('RSM_CHARTS_EMAILS_IN_QUEUE',true); ?>', 	<?php echo $this->details['max'] - $this->details['sentmessages']; ?>],
		['<?php echo JText::_('RSM_CHARTS_ERRORS',true); ?>',  	  		<?php echo $this->details['errors']; ?>]
	]);

	var options = {
		title: '<?php echo JText::_('RSM_CHARTS_EMAIL_SENDING',true); ?>'
	};

	var chart = new google.visualization.PieChart(document.getElementById('session_email_chart'));
	chart.draw(data, options);
}


function drawOpensChart() {
	var data = google.visualization.arrayToDataTable([
		['', ''],
		['<?php echo JText::_('RSM_CHARTS_OPENED',true); ?>',		<?php echo $this->details['uniqueopens']; ?>],
		['<?php echo JText::_('RSM_CHARTS_NOT_OPENED',true); ?>',   <?php echo $this->details['max'] - $this->details['uniqueopens']; ?>],
	]);

	var options = {
		title: '<?php echo JText::_('RSM_CHARTS_EMAIL_OPENS',true); ?>'
	};

	var chart = new google.visualization.PieChart(document.getElementById('session_opens_chart'));
	chart.draw(data, options);
}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_rsmail') ?>" method="post" name="adminForm" id="adminForm">
	<div class="row-fluid">
		<div class="span6 rsleft rsspan6">
			<fieldset>
				<legend><?php echo JText::_('RSM_MESSAGE_PROP'); ?></legend>
					<table class="table table-striped table-bordered adminform">
						<tr>
							<td width="20%"><b><?php echo JText::_('RSM_REPORT_MESSAGE_NAME'); ?></b></td>
							<td><?php echo $this->details['name']; ?></td>
						</tr>
						<tr>
							<td width="20%"><b><?php echo JText::_('RSM_MESSAGE_SUBJECT'); ?></b></td>
							<td><?php echo $this->details['subject']; ?></td>
						</tr>
						<tr>
							<td width="20%"><b><?php echo JText::_('RSM_SENT_ON'); ?></b></td>
							<td><?php echo rsmailHelper::showDate($this->details['date']); ?></td>
						</tr>
						<tr>
							<td width="20%"><b><?php echo JText::_('RSM_TOTAL_EMAILS'); ?></b></td>
							<td><?php echo $this->details['max']; ?></td>
						</tr>
						<tr>
							<td width="20%"><b><?php echo JText::_('RSM_TOTAL_ERRORS'); ?></b></td>
							<td>
								<?php if (!empty($this->details['errors'])) { ?>
								<a id="rsm_show_errors" href="<?php echo JRoute::_('index.php?option=com_rsmail&view=reports&layout=errors&id='.$this->details['IdSession']); ?>" class="rs_button hasTip" title="<?php echo JText::_('RSM_TOTAL_ERRORS').'::'.JText::_('RSM_TOTAL_ERRORS_DESC'); ?>">
									<img src="<?php echo JURI::root(); ?>administrator/components/com_rsmail/assets/images/open_loop.png" />
									<?php echo $this->details['errors']; ?>
								</a>
								<?php } else { ?>0<?php } ?>
							</td>
						</tr>
					</table>
			</fieldset>
		</div>
		
		<div class="span6 rsleft rsspan6">
			<fieldset>
				<legend><?php echo JText::_('RSM_MESSAGE_STATS'); ?></legend>
					<table class="table table-striped table-bordered adminform">
						<tr>
							<td width="20%"><b><?php echo JText::_('RSM_OPENED'); ?></b></td>
							<td align="center">
								<?php if ($this->details['openshistory']) { ?>
								<a class="hasTip" title="<?php echo JText::_('RSM_TOTAL_OPENS'); ?>" href="<?php echo JRoute::_('index.php?option=com_rsmail&view=reports&layout=opens&id='.$this->details['IdSession']); ?>">
									<?php echo $this->details['opens']; ?>
								</a>
								<?php } else echo $this->details['opens']; ?>
							</td>
						</tr>
						<tr>
							<td width="20%"><b><?php echo JText::_('RSM_UNIQUE_OPENED'); ?></b></td>
							<td align="center">
								<?php if ($this->details['openshistory']) { ?>
								<a class="hasTip" title="<?php echo JText::_('RSM_TOTAL_OPENS_UNIQUE'); ?>" href="<?php echo JRoute::_('index.php?option=com_rsmail&view=reports&layout=opens&unique=1&id='.$this->details['IdSession']); ?>">
									<?php echo $this->details['uniqueopens']; ?>
								</a>
								<?php } else echo $this->details['uniqueopens']; ?>
							</td>
						</tr>
						<tr>
							<td width="20%"><b><?php echo JText::_('RSM_UNSUBSCRIBERS'); ?></b></td>
							<td align="center"><?php echo $this->details['unsubscribers']; ?></td>
						</tr>
						<tr>
							<td width="20%"><b><?php echo JText::_('RSM_RELEASESD'); ?></b></td>
							<td align="center"><?php echo $this->details['sentmessages']; ?></td>
						</tr>
						<tr>
							<td width="20%"><b><?php echo JText::_('RSM_BOUNCE_EMAILS_RATE'); ?></b></td>
							<?php $bounces = ($this->details['sentmessages'] == 0) ? '0.00' : number_format($this->details['bounce'] * 100 / $this->details['sentmessages'],2,',','.'); ?>
							<td><a href="<?php echo JRoute::_('index.php?option=com_rsmail&view=reports&layout=bounce&id='.$this->details['IdSession']); ?>" class="hasTip" title="<?php echo JText::_('RSM_BOUNCE_EMAILS_RATE'); ?>::<?php echo JText::_('RSM_BOUNCE_EMAILS_RATE_DESC'); ?>"><?php echo $bounces; ?> %</a></td>
						</tr>
					</table>
			</fieldset>
		</div>
		
		<div class="rsm_clear"></div>
		
		<div class="span12">
			<div class="span6 rsleft rsspan6">
				<div id="session_email_chart" style="width: 500px; height: 200px;"></div>
			</div>
			<div class="span6 rsleft rsspan6">
				<div id="session_opens_chart" style="width: 500px; height: 200px;"></div>
			</div>
		</div>
		
		<div class="rsm_clear"></div>
		
		<div class="span12">
			<table class="table table-striped adminlist">
				<thead>
					<tr>
						<th width="80%"><?php echo JText::_('RSM_URL_NAME'); ?></th>
						<th width="5%" align="center" class="center">
							<?php if($this->details['linkhistory'] == 1) { ?>
							<a href="<?php echo JRoute::_('index.php?option=com_rsmail&view=reports&layout=links&unique=1&id='.$this->details['IdSession']); ?>" class="hasTip" title="<?php echo JText::_('RSM_UNIQUE_URL_COUNTER'); ?>::<?php echo JText::_('RSM_URL_COUNTER_DESC'); ?>">
								<?php echo JText::_('RSM_UNIQUE_URL_COUNTER'); ?>
							</a> 
							<?php } else { echo JText::_('RSM_UNIQUE_URL_COUNTER'); } ?>
						</th>
						<th width="5%" align="center" class="center">
							<?php if($this->details['linkhistory'] == 1) { ?>
							<a href="<?php echo JRoute::_('index.php?option=com_rsmail&view=reports&layout=links&id='.$this->details['IdSession']); ?>" class="hasTip" title="<?php echo JText::_('RSM_URL_COUNTER'); ?>::<?php echo JText::_('RSM_URL_COUNTER_DESC'); ?>">
								<?php echo JText::_('RSM_URL_COUNTER'); ?>
							</a> 
							<?php } else { echo JText::_('RSM_URL_COUNTER'); } ?>
						</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($this->urls as $i => $item) { ?>
				<?php if(strpos($item->Url,'http') !== FALSE) $url = $item->Url;  else $url = 'http://'.$item->Url; ?>
					<tr class="row<?php echo $i % 2; ?>">
						<td><?php echo '<a href="'.$url.'" target="_blank">'.$item->Url.'</a>'; ?></td>
						<td align="center" class="center"><?php echo $item->UniqueUrlCounter; ?></td>
						<td align="center" class="center"><?php echo $item->UrlCounter; ?></td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
		</div>
	</div>


	<?php echo JHTML::_( 'form.token' ); ?>
	<input type="hidden" name="task" value="" />
</form>