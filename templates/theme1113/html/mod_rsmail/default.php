<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// no direct access
defined('_JEXEC') or die('Restricted access');
if(is_array($fieldIds)) $fieldIds = implode(',',$fieldIds); 
$text_email = 'Your Email';
?>


	<div class="rsmail<?php echo $params->get('moduleclass_sfx'); ?>">
		
		<?php echo $introtext ? '<p>'.$introtext.'</p>' : ''; ?>
		
		<form id="rsm_subscribe<?php echo $module->id;?>" action="<?php echo $action; ?>" method="post">
		
		<h3 class='title'>Mailing List</h3>
		<?php $function = $captcha_enable != 0 ? 'doValidate' : 'rsm_validation'; ?>
		<table class='tbl_frm_rsmail' border='0' cellspacing='0' cellpadding='0'>
			<tr><td>
		<input type="text" name="rsm_email" class='input_rsmail' id="rsm_email<?php echo $module->id;?>" value="<?php echo $text_email; ?>" onfocus="if(this.value == '<?php echo $text_email; ?>') this.value ='';" onblur="if(this.value == '') this.value='<?php echo $text_email; ?>'" />
			</td>
			<td>
		<button type="button" class="fwbutton btn btn-primary" onclick="<?php echo $function; ?>('<?php echo JURI::root(); ?>','<?php echo JText::_('RSM_YOUR_EMAIL',true); ?>','<?php echo JText::_('RSM_ENTER_YOUR_EMAIL',true); ?>','<?php echo JText::_('RSM_INVALID_EMAIL',true); ?>',<?php echo $module->id;?>);">Submit</button>
			</td></tr>
		</table>

		<?php if ($captcha_enable != 0) { ?>
			<div id="rsmail_captcha">
			
				<label for="submit_captcha<?php echo $module->id;?>"><?php echo JText::_('RSM_CAPTCHA_LABEL'); ?></label>
				<?php if ($captcha_enable == 1) { ?>
				<img src="<?php echo JRoute::_('index.php?option=com_rsmail&task=captcha&id='.$module->id.'&sid='.mt_rand()); ?>" id="submit_captcha_image<?php echo $module->id;?>" alt="Antispam" />
				<a style="border-style: none" href="javascript: void(0)" onclick="return rsm_refresh_captcha(<?php echo $module->id;?>);">
					<img src="<?php echo JURI::root(); ?>components/com_rsmail/images/refresh.gif" alt="" border="0" align="top" />
				</a>
				<br />
				<input type="text" name="captcha<?php echo $module->id;?>" id="submit_captcha<?php echo $module->id;?>" size="35" value="" class="inputbox required" />
				<?php } elseif ($captcha_enable == 2) { ?>
					<?php echo RSMReCAPTCHA::getHTML(null,false,$recaptcha_public_key,$recaptcha_theme); ?>
				<?php } ?>
			</div> <!-- rsmail_captcha -->
		<?php } ?>

		<br />
		
		

		<?php if (is_array($idList) && count($idList) == 1) echo '<input type="hidden" id="IdList'.$module->id.'" name="IdList'.$module->id.'" value="'.$idList[0].'" />'; ?>
		<?php if (!is_array($idList)) echo '<input type="hidden" id="IdList'.$module->id.'" name="IdList'.$module->id.'" value="'.$idList.'" />'; ?>
		<input type="hidden" name="option" value="com_rsmail" />
		<input type="hidden" name="task" value="subscribe" />
		<input type="hidden" name="mid" value="<?php echo $module->id;?>" />
		</form>
		<?php echo $posttext ? '<p>'.$posttext.'</p>'."\n" : ''; ?>
		<script type="text/javascript">
		rsm_show_fields('<?php echo JURI::root(); ?>', document.getElementById('IdList<?php echo $module->id; ?>').value,'<?php echo $fieldIds; ?>',<?php echo $module->id; ?>);
		
		<?php if ($captcha_enable == 1) { ?>
		function rsm_refresh_captcha(id) {
			var rsm_url = '<?php echo JRoute::_('index.php?option=com_rsmail&task=captcha&id='.$module->id,false); ?>';			
			if (rsm_url.indexOf('?') != -1)
				rsm_url += '&sid='+Math.random();
			
			document.getElementById('submit_captcha_image'+id).src = rsm_url;
			return false;
		}
		<?php } ?>
		</script>
	</div>
