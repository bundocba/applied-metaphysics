<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

if (!JFactory::getUser()->authorise('core.manage', 'com_rsmail'))
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));

require_once JPATH_SITE.'/components/com_rsmail/helpers/version.php';
require_once JPATH_SITE.'/components/com_rsmail/helpers/adapter/adapter.php';
require_once JPATH_SITE.'/components/com_rsmail/helpers/rsmail.php';
require_once JPATH_ADMINISTRATOR.'/components/com_rsmail/controller.php';

JHTML::_('behavior.framework');

// Load scripts
rsmailHelper::initialize();

// Bounce handling
rsmailHelper::bounce();

$controller	= JControllerLegacy::getInstance('RSMail');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();