<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// Load the needed files
if (file_exists(JPATH_SITE.'/components/com_rsmail/helpers/securimage/securimage.php'))
	require_once JPATH_SITE.'/components/com_rsmail/helpers/securimage/securimage.php';

if (file_exists(JPATH_SITE.'/components/com_rsmail/helpers/recaptcha/recaptchalib.php'))
	require_once JPATH_SITE.'/components/com_rsmail/helpers/recaptcha/recaptchalib.php';
if (file_exists(dirname(__FILE__).'/helper.php'))
	require_once dirname(__FILE__).'/helper.php';

// Load variables
$user 		= JFactory::getUser();
$lang 		= JFactory::getLanguage();
$document 	=& JFactory::getDocument();
$idList 	= $params->get('listid');
$fieldIds 	= $params->get('fieldids');
$introtext 	= $params->get('introtext');
$posttext 	= $params->get('finaltext');
$action 	= htmlentities(JURI::getInstance(),ENT_COMPAT,'UTF-8');

$captcha_enable 		= $params->get('captcha_enable');
$captcha_characters 	= $params->get('captcha_characters');
$captcha_generate_lines = $params->get('captcha_generate_lines');
$captcha_case_sensitive = $params->get('captcha_case_sensitive');
$recaptcha_public_key 	= $params->get('recaptcha_public_key');
$recaptcha_private_key 	= $params->get('recaptcha_private_key');
$recaptcha_theme 		= $params->get('recaptcha_theme');


// Load language and scripts
$document->addScript(JURI::root(true).'/modules/mod_rsmail/assets/rsmail.js');
$document->addStyleSheet(JURI::root(true).'/modules/mod_rsmail/assets/rsmail.css');
$lang->load('mod_rsmail');
$lang->load('com_rsmail');

$thankyou	= modRsmailHelper::getThankYou();
$lists		= modRsmailHelper::getLists($params,$module->id);
$email 		= $user->get('id') > 0 ? $user->get('email') : JText::_('RSM_YOUR_EMAIL');
	
require(JModuleHelper::getLayoutPath('mod_rsmail'));