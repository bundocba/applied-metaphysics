<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access'); ?>

<div class="row-fluid">
	<div class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div class="span10">
		<form action="<?php echo JRoute::_('index.php?option=com_rsmail'); ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">

			<div style="text-align:center;">
				<strong><?php echo JText::_('RSM_SELECT_LIST'); ?></strong><br/><br/>
				<?php echo JText::_('RSM_DELIMITER'); ?> <input class="input-mini" style="text-align:center;" type="text" name="rsm_delimiter" value="," size="1" maxlength="2" /> 
				<select name="IdList" id="IdList">
					<option value="0"><?php echo JText::_('RSM_SELECT_LISTS'); ?></option>
					<?php echo JHtml::_('select.options', $this->lists); ?>
				</select>
				<input class="rsm_input" type="file" name="file" size="40" /> 
				<button class="btn btn-primary button" type="button" onclick="Joomla.submitform('import.upload');"><?php echo JText::_('RSM_IMPORT'); ?></button>
			</div>
			
			<?php echo JHTML::_( 'form.token' ); ?>
			<input type="hidden" name="option" value="com_rsmail" />
			<input type="hidden" name="task" value="" />
		</form>
	</div>
</div>