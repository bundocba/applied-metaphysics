<?php
/**
 * @version		$Id: register.php 1206 2011-10-17 21:09:08Z joomlaworks $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.gr
 * @copyright	Copyright (c) 2006 - 2011 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

?>


<!-- K2 user register form -->
<?php if(isset($this->message)) $this->display('message'); ?>

<form action="<?php echo JRoute::_('index.php'); ?>" enctype="multipart/form-data" method="post" id="josForm" name="josForm" class="form-validate">
	<?php if($this->params->def('show_page_title',1)): ?>
	<div class="componentheading<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
		<h2><?php echo $this->escape($this->params->get('page_title')); ?></h2>
	</div>
	<?php endif; ?>
	<div id="k2Container" class="k2AccountPage">
		
				<h2 class="k2ProfileHeading"><?php echo JText::_('K2_ACCOUNT_DETAILS'); ?></h2>
			
			<dl>
				<dt class="key"><label id="namemsg" for="name"><?php echo JText::_('K2_NAME'); ?></label></dt>
				<dd><input type="text" name="<?php echo (K2_JVERSION=='16')?'jform[name]':'name'?>" id="name" size="40" value="<?php echo $this->escape($this->user->get( 'name' )); ?>" class="inputbox required" maxlength="50" />
					*</dd>
			
				<dt class="key"><label id="usernamemsg" for="username"><?php echo JText::_('K2_USER_NAME'); ?></label></dt>
				<dd><input type="text" id="username" name="<?php echo (K2_JVERSION=='16')?'jform[username]':'username'?>" size="40" value="<?php echo $this->escape($this->user->get( 'username' )); ?>" class="inputbox required validate-username" maxlength="25" /> *</dd>
			
				<dt class="key"><label id="emailmsg" for="email"><?php echo JText::_('K2_EMAIL'); ?></label></dt>
				<dd><input type="text" id="email" name="<?php echo (K2_JVERSION=='16')?'jform[email1]':'email'?>" size="40" value="<?php echo $this->escape($this->user->get( 'email' )); ?>" class="inputbox required validate-email" maxlength="100" /> *</dd>
			
			<?php if(K2_JVERSION == '16'): ?>
			
				<dt class="key"><label id="email2msg" for="email2"><?php echo JText::_('K2_CONFIRM_EMAIL'); ?></label></dt>
				<dd><input type="text" id="email2" name="jform[email2]" size="40" value="" class="inputbox required validate-email" maxlength="100" /> *</dd>
			
			<?php endif; ?>
			
				<dt class="key"><label id="pwmsg" for="password"><?php echo JText::_('K2_PASSWORD'); ?></label></dt>
				<dd><input class="inputbox required validate-password" type="password" id="password" name="<?php echo (K2_JVERSION=='16')?'jform[password1]':'password'?>" size="40" value="" /> *</dd>
			
				<dt class="key"><label id="pw2msg" for="password2"><?php echo JText::_('K2_VERIFY_PASSWORD'); ?></label></dt>
				<dd><input class="inputbox required validate-passverify" type="password" id="password2" name="<?php echo (K2_JVERSION=='16')?'jform[password2]':'password2'?>" size="40" value="" /> *</dd>
			</dl>
			
				<h2 class="k2ProfileHeading"><?php echo JText::_('K2_PERSONAL_DETAILS'); ?></h2>
			
			<!-- K2 attached fields -->
			
			<dl>
				<dt class="key"><label id="gendermsg" for="gender"><?php echo JText::_('K2_GENDER'); ?></label></dt>
				<dd class="gender"><?php echo $this->lists['gender']; ?></dd>
				<dt class="key editor"><label id="descriptionmsg" for="description"><?php echo JText::_('K2_DESCRIPTION'); ?></label></dt>
				<dd class="editor"><?php echo $this->editor; ?></dd>
			
				<dt class="key"><label id="imagemsg" for="image"><?php echo JText::_( 'K2_USER_IMAGE_AVATAR' ); ?></label></dt>
				<dd>
					<input type="file" id="image" name="image"/>
					<?php if ($this->K2User->image): ?>
					<img class="k2AdminImage" src="<?php echo JURI::root().'media/k2/users/'.$this->K2User->image; ?>" alt="<?php echo $this->user->name; ?>" />
					<input type="checkbox" name="del_image" id="del_image" />
					<label for="del_image"><?php echo JText::_('K2_CHECK_THIS_BOX_TO_DELETE_CURRENT_IMAGE_OR_JUST_UPLOAD_A_NEW_IMAGE_TO_REPLACE_THE_EXISTING_ONE'); ?></label>
					<?php endif; ?>
				</dd>
			
				<dt class="key"><label id="urlmsg" for="url"><?php echo JText::_('K2_URL'); ?></label></dt>
				<dd><input type="text" size="50" value="<?php echo $this->K2User->url; ?>" name="url" id="url"/></dd>
			</dl>
			
			<?php if(count(array_filter($this->K2Plugins))): ?>
			<!-- K2 Plugin attached fields -->
				<h2  class="k2ProfileHeading"><?php echo JText::_('K2_ADDITIONAL_DETAILS'); ?></h2>
			
				<?php foreach ($this->K2Plugins as $K2Plugin): ?>
					<?php if(!is_null($K2Plugin)): ?>
						<div><?php echo $K2Plugin->fields; ?></div>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>

<!-- Joomla! 1.6+ JForm implementation -->
			<?php if(K2_JVERSION == '16'): ?>
			<?php foreach ($this->form->getFieldsets() as $fieldset): // Iterate through the form fieldsets and display each one.?>


				<?php if($fieldset->name != 'default'): ?>
				<?php $fields = $this->form->getFieldset($fieldset->name);?>
				<?php if (count($fields)):?>
					<?php if (isset($fieldset->label)):// If the fieldset has a label set, display it as the legend.?>
						<h2 class="k2ProfileHeading">
							<?php echo JText::_($fieldset->label);?>
						</h2>
					<?php endif;?>
					<dl>
					<?php foreach($fields as $field):// Iterate through the fields in the set and display them.?>
						<?php if ($field->hidden):// If the field is hidden, just display the input.?>
							<div><?php echo $field->input;?></div>
						<?php else:?>
								<dt class="key">
									<?php echo $field->label; ?>
									<?php if (!$field->required && $field->type != 'Spacer'): ?>
										<span class="optional"><?php echo JText::_('COM_USERS_OPTIONAL');?></span>
									<?php endif; ?>
								</dt>
								<dd><?php echo $field->input;?></dd>
						<?php endif;?>
					<?php endforeach;?>
					</dl>
				<?php endif;?>
				<?php endif; ?>
			<?php endforeach;?>
			<?php endif; ?>
		
		<?php if($this->K2Params->get('recaptchaOnRegistration') && $this->K2Params->get('recaptcha_public_key')): ?>
		<label class="formRecaptcha"><?php echo JText::_('K2_ENTER_THE_TWO_WORDS_YOU_SEE_BELOW'); ?></label>
		<div id="recaptcha"></div>
		<?php endif; ?>
		
		<div class="k2AccountPageNotice"><?php echo JText::_('K2_REGISTER_REQUIRED'); ?></div>
		<div class="k2AccountPageUpdate">
			<button class="button validate" type="submit">
				<?php echo JText::_('K2_REGISTER'); ?>
			</button>
		</div>
	</div>
	<input type="hidden" name="option" value="<?php echo (K2_JVERSION=='16')?'com_users':'com_user'?>" />
	<input type="hidden" name="task" value="<?php echo (K2_JVERSION=='16')?'registration.register':'register_save'?>" />
	<input type="hidden" name="id" value="0" />
	<input type="hidden" name="gid" value="0" />
	<input type="hidden" name="K2UserForm" value="1" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
