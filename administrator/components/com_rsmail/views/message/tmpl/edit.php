<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.keepalive');
JHTML::_('behavior.formvalidation');
JHTML::_('behavior.tooltip');
?>

<script type="text/javascript">
Joomla.submitbutton = function(task) {
	if (task == 'message.preview') {
		window.open('index.php?option=com_rsmail&view=message&layout=preview&id=<?php echo $this->item->IdMessage; ?>' , 'win2' , 'status=no, toolbar=no, scrollbars=yes, titlebar=no, menubar=no, resizable=yes, width=640,height=480,directories=no,location=no');
	} else if (task == 'send') {
		document.location = 'index.php?option=com_rsmail&view=send&id=<?php echo $this->item->IdMessage; ?>';
	} else if (task == 'message.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
		Joomla.submitform(task, document.getElementById('adminForm'));
	} else {
		alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
	}
}

function addFile() {
	var container = document.getElementById('rsmfiles');
	var newBr = new Element('br');
	var newFile = new Element('input', {
		type: 'file',
		name: 'files[]',
		size: '40'
	});
	
	container.adopt(newBr);
	container.adopt(newFile);
}

function rsm_insert(file) {
	var thetable = $('files_table');
	
	if (isset($(file))) {
		alert('<?php echo JText::_('RSM_MESSAGE_FILE_EXIST',true); ?>');
		return;
	}	
	
	var row = thetable.insertRow(thetable.rows.length);
	row.id = file;
	row.setAttribute('id', file);
	var cell = row.insertCell(0);
	cell.noWrap = true;
	cell.innerHTML = thetable.rows.length;
	var cell = row.insertCell(1);
	cell.noWrap = true;
	cell.innerHTML = '<a href="<?php echo JURI::root().'administrator/components/com_rsmail/files/'; ?>'+file +'" target="_blank">' + file + '</a>';
	var cell = row.insertCell(2);
	cell.noWrap = true;
	cell.align = 'center';
	cell.innerHTML = '-';
	var cell = row.insertCell(3);
	cell.noWrap = true;
	cell.align = 'center';
	cell.innerHTML = '-';
	var cell = row.insertCell(4);
	cell.noWrap = true;
	cell.align = 'center';
	cell.innerHTML = '<input type="hidden" name="rsmfiles[]" value="'+file+'" />';
	cell.innerHTML += '<a href="javascript:void(0)" onclick="rsm_remove(\''+file+'\');"><img src="<?php echo JURI::root(); ?>administrator/components/com_rsmail/assets/images/icons/delete.png"></a>';
}

function rsm_remove(what) {
	var therow = $(what);
	therow.parentNode.removeChild(therow);
}

function rsm_validate() {
	if (confirm('<?php echo JText::_('RSM_DELETE_FILE_MESSAGE1',true); ?>'+ "\n" + '<?php echo JText::_('RSM_DELETE_FILE_MESSAGE2',true); ?>'))
		return true;
	else return false;
}
</script>

<div class="row-fluid">
	<div class="span12">
		<form action="<?php echo JRoute::_('index.php?option=com_rsmail&view=message&layout=edit&IdMessage='.(int) $this->item->IdMessage); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off" class="form-validate form-horizontal" enctype="multipart/form-data">
		
		<?php foreach ($this->layouts as $layout) {
			if (!$this->item->IdMessage && $layout == 'spam') continue;
			
			// add the tab title
			$this->tabs->title('RSM_MESSAGE_TAB_'.strtoupper($layout), $layout);
			
			// prepare the content
			$content = $this->loadTemplate($layout);
			
			// add the tab content
			$this->tabs->content($content);
		}
		
		// render tabs
		echo $this->tabs->render();
		?>
		
		<?php echo JHTML::_('form.token'); ?>
		<input type="hidden" name="task" value="" />
		<?php echo $this->form->getInput('IdMessage'); ?>
		</form>
	</div>
</div>