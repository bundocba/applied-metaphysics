<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access'); ?>

<div class="well">
	<table>
		<tr>
			<td><?php echo JText::_('RSM_MESSAGE_FILES'); ?></td>
			<td>
				<span id="rsmfiles">
					<input class="rsm_input" type="file" name="files[]" size="40" />
				</span>
			</td>
		</tr>
	</table>
	
	<div class="rsm_files_actions">
		<a href="javascript:void(0);" onclick="addFile()" class="btn"><?php echo JText::_('RSM_ADD_FILES');?></a>
		<a href="javascript:void(0);" onclick="Joomla.submitbutton('message.apply');" class="btn"><?php echo JText::_('RSM_UPLOAD'); ?></a> 
		<a class="btn modal" href="<?php echo JRoute::_('index.php?option=com_rsmail&view=message&layout=files&tmpl=component'); ?>" rel="{handler: 'iframe'}"><?php echo JText::_('RSM_MESSAGE_FILES_UPLOAD'); ?></a>
	</div>
</div>

<table class="adminlist table table-striped">
	<thead>
		<tr>
			<th width="5">#</th>
			<th><?php echo JText::_('RSM_FILE_NAME'); ?></th>
			<th width="100" align="center" class="center"><?php echo JText::_('RSM_EMBED_PLACEHOLDER'); ?></th>
			<th width="100" align="center" class="center"><?php echo JText::_('RSM_EMBED_FILE'); ?></th>
			<th width="100" align="center" class="center"><?php echo JText::_('RSM_DELETE_FILE'); ?></th>
		</tr>
	</thead>
	<tbody id="files_table">
	<?php if(!empty($this->files)) { ?>
	<?php foreach ($this->files as $i => $file) { ?>
	<?php $src = ($file->Embeded == 1) ? JURI::root().'administrator/components/com_rsmail/assets/images/icons/tick.png' : JURI::root().'administrator/components/com_rsmail/assets/images/icons/delete.png';  ?>
	<?php $ext = explode('.',$file->FileName); ?>
	<?php $isImg = (in_array(strtolower(end($ext)),array('jpg','gif','png')) ? 1 : 0 ); ?>
		<tr class="row<?php echo $i % 2; ?>" id="<?php echo $file->FileName; ?>">
			<td>
				<?php echo $file->IdFile; ?>
			</td>
			<td>
				<a href="<?php echo JURI::root(); ?>administrator/components/com_rsmail/files/<?php echo $file->FileName; ?>" target="_blank">
					<?php echo $file->FileName; ?>
				</a>
			</td>
			<td align="center" class="center">
				<?php if($isImg && $file->Embeded == 1) { ?> [img<?php echo $file->IdFile; ?>] <?php } else echo '-'; ?>
			</td>
			<td align="center" class="center">
				<?php if($isImg) { ?> 
				<a href="<?php echo JRoute::_('index.php?option=com_rsmail&task=messages.embed&id='.$file->IdFile); ?>"> 
					<img src="<?php echo $src; ?>" alt="" />
				</a> 
				<?php } else echo '-'; ?>
			</td>
			<td align="center" class="center">
				<a href="<?php echo JRoute::_('index.php?option=com_rsmail&task=messages.deletefile&id='.$file->IdFile); ?>" onclick="return rsm_validate();">
					<img src="<?php echo JURI::root().'administrator/components/com_rsmail/assets/images/icons/delete.png'; ?>">
				</a>
			</td>
		</tr>
	<?php } ?>
	<?php } ?>
	</tbody>
</table>