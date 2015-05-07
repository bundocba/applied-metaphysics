<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.tooltip'); ?>
<div class="row-fluid">
	<div class="span8 rsleft rsspan8">
		<div class="fltlft">
			<div class="dashboard-container">
				<div class="rsspan4">
					<div class="dashboard-wraper">
						<div class="dashboard-content"> 
						<a href="index.php?option=com_rsmail&amp;view=lists">
							<?php echo JHTML::_('image', 'administrator/components/com_rsmail/assets/images/dashboard/lists.png', JText::_('RSM_SUBMENU_LISTS')); ?>
							<span class="dashboard-title"><?php echo JText::_('RSM_LISTS'); ?></span>
						</a>
						</div>
					</div>
				</div>
				<div class="rsspan4">
					<div class="dashboard-wraper">
						<div class="dashboard-content"> 
							<div class="dashboard-content">
								<a href="index.php?option=com_rsmail&amp;view=subscribers">
									<?php echo JHTML::_('image', 'administrator/components/com_rsmail/assets/images/dashboard/subscribers.png', JText::_('RSM_SUBMENU_SUBSCRIBERS')); ?>
									<span class="dashboard-title"><?php echo JText::_('RSM_SUBSCRIBERS'); ?></span>
								</a>
							</div>
						</div>
					</div>
				</div>
				<div class="rsspan4">
					<div class="dashboard-wraper">
						<div class="dashboard-content"> 
							<div class="dashboard-content">
								<a href="index.php?option=com_rsmail&amp;view=messages">
									<?php echo JHTML::_('image', 'administrator/components/com_rsmail/assets/images/dashboard/messages.png', JText::_('RSM_SUBMENU_MESSAGES')); ?>
									<span class="dashboard-title"><?php echo JText::_('RSM_MESSAGES'); ?></span>
								</a>
							</div>
						</div>
					</div>
				</div>
				<div class="rsspan4">
					<div class="dashboard-wraper">
						<div class="dashboard-content"> 
							<div class="dashboard-content">
								<a href="index.php?option=com_rsmail&amp;view=autoresponders">
									<?php echo JHTML::_('image', 'administrator/components/com_rsmail/assets/images/dashboard/followups.png', JText::_('RSM_SUBMENU_FOLLOWUPS')); ?>
									<span class="dashboard-title"><?php echo JText::_('RSM_FOLLOWUPS'); ?></span>
								</a>
							</div>
						</div>
					</div>
				</div>
				<div class="rsspan4">
					<div class="dashboard-wraper">
						<div class="dashboard-content"> 
							<div class="dashboard-content">
								<a href="index.php?option=com_rsmail&amp;view=templates">
									<?php echo JHTML::_('image', 'administrator/components/com_rsmail/assets/images/dashboard/templates.png', JText::_('RSM_SUBMENU_TEMPLATES')); ?>
									<span class="dashboard-title"><?php echo JText::_('RSM_TEMPLATES'); ?></span>
								</a>
							</div>
						</div>
					</div>
				</div>
				<div class="rsspan4">
					<div class="dashboard-wraper">
						<div class="dashboard-content"> 
							<div class="dashboard-content">
								<a href="index.php?option=com_rsmail&amp;view=sessions">
									<?php echo JHTML::_('image', 'administrator/components/com_rsmail/assets/images/dashboard/sessions.png', JText::_('RSM_SUBMENU_SESSIONS')); ?>
									<span class="dashboard-title"><?php echo JText::_('RSM_SESSIONS'); ?></span>
								</a>
							</div>
						</div>
					</div>
				</div>
				<div class="rsspan4">
					<div class="dashboard-wraper">
						<div class="dashboard-content"> 
							<div class="dashboard-content">
								<a href="index.php?option=com_rsmail&amp;view=reports">
									<?php echo JHTML::_('image', 'administrator/components/com_rsmail/assets/images/dashboard/reports.png', JText::_('RSM_SUBMENU_REPORTS')); ?>
									<span class="dashboard-title"><?php echo JText::_('RSM_REPORTS'); ?></span>
								</a>
							</div>
						</div>
					</div>
				</div>
				<div class="rsspan4">
					<div class="dashboard-wraper">
						<div class="dashboard-content"> 
							<div class="dashboard-content">
								<a href="index.php?option=com_rsmail&amp;view=cronlogs">
									<?php echo JHTML::_('image', 'administrator/components/com_rsmail/assets/images/dashboard/cronlogs.png', JText::_('RSM_SUBMENU_CRONLOGS')); ?>
									<span class="dashboard-title"><?php echo JText::_('RSM_CRON_LOGS'); ?></span>
								</a>
							</div>
						</div>
					</div>
				</div>
				<div class="rsspan4">
					<div class="dashboard-wraper">
						<div class="dashboard-content"> 
							<div class="dashboard-content">
								<a href="index.php?option=com_rsmail&amp;view=import">
									<?php echo JHTML::_('image', 'administrator/components/com_rsmail/assets/images/dashboard/import.png', JText::_('RSM_SUBMENU_SUBMENU_IMPORTS')); ?>
									<span class="dashboard-title"><?php echo JText::_('RSM_IMPORT'); ?></span>
								</a>
							</div>
						</div>
					</div>
				</div>
				<div class="rsspan4">
					<div class="dashboard-wraper">
						<div class="dashboard-content"> 
							<div class="dashboard-content">
								<a href="index.php?option=com_rsmail&amp;view=settings">
									<?php echo JHTML::_('image', 'administrator/components/com_rsmail/assets/images/dashboard/settings.png', JText::_('RSM_SUBMENU_SETTINGS')); ?>
									<span class="dashboard-title"><?php echo JText::_('RSM_SETTINGS'); ?></span>
								</a>
							</div>
						</div>
					</div>
				</div>
				<div class="rsspan4">
					<div class="dashboard-wraper">
						<div class="dashboard-content"> 
							<div class="dashboard-content">
								<a href="index.php?option=com_rsmail&amp;view=updates">
									<?php echo JHTML::_('image', 'administrator/components/com_rsmail/assets/images/dashboard/updates.png', JText::_('RSM_SUBMENU_UPDATES')); ?>
									<span class="dashboard-title"><?php echo JText::_('RSM_UPDATES'); ?></span>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<div class="span4 rsleft rsspan4 pull-left rsj_margin">
		<div class="dashboard-container">
			<div class="dashboard-info">
				<span>
					<img src="components/com_rsmail/assets/images/logos/rsmail.jpg" align="middle" alt="RSMail! logo"/>
				</span>
				<table class="dashboard-table">
					<tr>
						<td nowrap="nowrap" align="right"><strong><?php echo JText::_('RSM_INSTALLED_VERSION') ?> </strong></td>
						<td colspan="2"><?php echo RSM_RS_PRODUCT . ' ' . RSM_RS_VERSION. ' rev ' . RSM_RS_REVISION; ?></td>
					</tr>
					<tr>
						<td nowrap="nowrap" align="right"><strong><?php echo JText::_('RSM_COPYRIGHT') ?> </strong></td>
						<td nowrap="nowrap">&copy; 2007 - <?php echo date('Y'); ?> <a href="http://www.rsjoomla.com" target="_blank">RSJoomla.com</a></td>
					</tr>
					<tr>
						<td nowrap="nowrap" align="right"><strong><?php echo JText::_('RSM_LICENSE') ?> </strong></td>
						<td nowrap="nowrap">GPL Commercial License</td>
					</tr>
					<tr>
						<td nowrap="nowrap" align="right"><strong><?php echo JText::_('RSM_AUTHOR') ?> </strong></td>
						<td nowrap="nowrap"><a href="http://www.rsjoomla.com" target="_blank">www.rsjoomla.com</a></td>
					</tr>
					<tr>
						<td nowrap="nowrap" align="right"><strong><?php echo JText::_('RSM_UPDATE_CODE') ?> </strong></td>
						<?php if (strlen($this->code) == 20) { ?>
						<td nowrap="nowrap" class="text-success"><?php echo $this->escape($this->code); ?></td>
						<?php } elseif ($this->code) { ?>
						<td nowrap="nowrap" class="text-error"><strong><?php echo $this->escape($this->code);?></strong></td>
						<?php } else { ?>
						<td nowrap="nowrap" class="missing-code">
							<a href="<?php echo JRoute::_('index.php?option=com_rsmail&view=settings'); ?>">
								<?php echo JText::_('RSM_PLEASE_ENTER_YOUR_CODE_IN_THE_CONFIGURATION'); ?>
							</a>
						</td>
						<?php } ?>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>