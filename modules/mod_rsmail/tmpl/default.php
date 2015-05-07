<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// no direct access
defined('_JEXEC') or die('Restricted access');
if(is_array($fieldIds)) $fieldIds = implode(',',$fieldIds); ?>

<?php if (isset($_SESSION['showTM'])) { ?>
	<div class="rsmail<?php echo $params->get('moduleclass_sfx'); ?>">
		<?php echo $thankyou; ?> <br />
		<button type="button" class="button btn btn-primary" onclick="document.location='<?php echo JURI::root(); ?>index.php?option=com_rsmail&task=unsetsession'"><?php echo JText::_('RSM_CONTINUE'); ?></button>
	</div>
<?php } else { ?>

<?php if($idList != '' && $idList != 0) { ?>
	<div class="rsmail<?php echo $params->get('moduleclass_sfx'); ?>">
		
		<?php echo $introtext ? '<p>'.$introtext.'</p>' : ''; ?>
		
		<form id="rsm_subscribe<?php echo $module->id;?>" action="<?php echo $action; ?>" method="post">
		
		<?php if($params->get('enablemultiple','1') == 1) { ?>
			<label for="IdList"><?php echo JText::_('RSM_SELECT_MODULE_LIST'); ?> </label> <?php echo $lists; ?>
		<?php } ?>
		
		<label for="rsm_email"><?php echo JText::_('RSM_MODULE_EMAIL'); ?> </label>
		<input type="text" name="rsm_email" id="rsm_email<?php echo $module->id;?>" value="<?php echo $email; ?>" onfocus="if(this.value == '<?php echo JText::_('RSM_YOUR_EMAIL',true); ?>') this.value ='';" onblur="if(this.value == '') this.value='<?php echo JText::_('RSM_YOUR_EMAIL',true); ?>'" />
		
		<div id="rsm_loader<?php echo $module->id; ?>" style="display:none;"><img src="<?php echo JURI::root().'modules/mod_rsmail/assets/loader.gif'; ?>" width="16" height="16" alt=""/></div>
		<div id="rsm_fields_location<?php echo $module->id; ?>"></div>

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
		<?php $function = $captcha_enable != 0 ? 'doValidate' : 'rsm_validation'; ?>
		<button type="button" class="button btn btn-primary" onclick="<?php echo $function; ?>('<?php echo JURI::root(); ?>','<?php echo JText::_('RSM_YOUR_EMAIL',true); ?>','<?php echo JText::_('RSM_ENTER_YOUR_EMAIL',true); ?>','<?php echo JText::_('RSM_INVALID_EMAIL',true); ?>',<?php echo $module->id;?>);"><?php echo JText::_('RSM_SUBSCRIBE'); ?></button>

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
<?php } else echo JText::_('RSM_MODULE_SELECT_LIST'); } ?>