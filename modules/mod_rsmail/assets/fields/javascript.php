<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('JPATH_BASE') or die;
jimport('joomla.form.formfield');

/**
 * JFormFieldJlists Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	com_rsmail
 * @since		1.6
 */
class JFormFieldJavascript extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'Javascript';

	
	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput()
	{
		$result = "<script type='text/javascript'>
		function rsm_captcha(val) {
			if (val == 0) {
				if (document.getElementById('jform_params_captcha_enable').getParent().hasClass('controls')) {
					document.getElementById('jform_params_captcha_characters').getParent().getParent().style.display = 'none';
					document.getElementById('jform_params_captcha_generate_lines').getParent().getParent().style.display = 'none';
					document.getElementById('jform_params_captcha_case_sensitive').getParent().getParent().style.display = 'none';
					document.getElementById('jform_params_recaptcha_public_key').getParent().getParent().style.display = 'none';
					document.getElementById('jform_params_recaptcha_private_key').getParent().getParent().style.display = 'none';
					document.getElementById('jform_params_recaptcha_theme').getParent().getParent().style.display = 'none';
				} else {
					document.getElementById('jform_params_captcha_characters').getParent().style.display = 'none';
					document.getElementById('jform_params_captcha_generate_lines').getParent().style.display = 'none';
					document.getElementById('jform_params_captcha_case_sensitive').getParent().style.display = 'none';
					document.getElementById('jform_params_recaptcha_public_key').getParent().style.display = 'none';
					document.getElementById('jform_params_recaptcha_private_key').getParent().style.display = 'none';
					document.getElementById('jform_params_recaptcha_theme').getParent().style.display = 'none';
				}
			} else if (val == 1) {
				if (document.getElementById('jform_params_captcha_enable').getParent().hasClass('controls')) {
					document.getElementById('jform_params_captcha_characters').getParent().getParent().style.display = '';
					document.getElementById('jform_params_captcha_generate_lines').getParent().getParent().style.display = '';
					document.getElementById('jform_params_captcha_case_sensitive').getParent().getParent().style.display = '';
					document.getElementById('jform_params_recaptcha_public_key').getParent().getParent().style.display = 'none';
					document.getElementById('jform_params_recaptcha_private_key').getParent().getParent().style.display = 'none';
					document.getElementById('jform_params_recaptcha_theme').getParent().getParent().style.display = 'none';
				} else {
					document.getElementById('jform_params_captcha_characters').getParent().style.display = '';
					document.getElementById('jform_params_captcha_generate_lines').getParent().style.display = '';
					document.getElementById('jform_params_captcha_case_sensitive').getParent().style.display = '';
					document.getElementById('jform_params_recaptcha_public_key').getParent().style.display = 'none';
					document.getElementById('jform_params_recaptcha_private_key').getParent().style.display = 'none';
					document.getElementById('jform_params_recaptcha_theme').getParent().style.display = 'none';
				}
			} else {
				if (document.getElementById('jform_params_captcha_enable').getParent().hasClass('controls')) {
					document.getElementById('jform_params_captcha_characters').getParent().getParent().style.display = 'none';
					document.getElementById('jform_params_captcha_generate_lines').getParent().getParent().style.display = 'none';
					document.getElementById('jform_params_captcha_case_sensitive').getParent().getParent().style.display = 'none';
					document.getElementById('jform_params_recaptcha_public_key').getParent().getParent().style.display = '';
					document.getElementById('jform_params_recaptcha_private_key').getParent().getParent().style.display = '';
					document.getElementById('jform_params_recaptcha_theme').getParent().getParent().style.display = '';
				} else {
					document.getElementById('jform_params_captcha_characters').getParent().style.display = 'none';
					document.getElementById('jform_params_captcha_generate_lines').getParent().style.display = 'none';
					document.getElementById('jform_params_captcha_case_sensitive').getParent().style.display = 'none';
					document.getElementById('jform_params_recaptcha_public_key').getParent().style.display = '';
					document.getElementById('jform_params_recaptcha_private_key').getParent().style.display = '';
					document.getElementById('jform_params_recaptcha_theme').getParent().style.display = '';
				}
			}
		}
		
		window.addEvent('domready',function(){
			rsm_captcha(document.getElementById('jform_params_captcha_enable').value);
		});
		</script>";
		return $result;
	}
}