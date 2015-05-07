<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

require_once JPATH_SITE.'/components/com_rsmail/helpers/version.php';
require_once JPATH_SITE.'/components/com_rsmail/helpers/adapter/adapter.php';
// Load the component main helper
require_once JPATH_SITE.'/components/com_rsmail/helpers/rsmail.php';
// Load the component main controller
require_once JPATH_COMPONENT.'/controller.php';
//Captcha & Recaptcha
require_once JPATH_COMPONENT.'/helpers/securimage/securimage.php';
require_once JPATH_COMPONENT.'/helpers/recaptcha/recaptchalib.php';

rsmailHelper::initialize('site');

// Bounce handling
rsmailHelper::bounce();

$controller	= JControllerLegacy::getInstance('RSMail');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();