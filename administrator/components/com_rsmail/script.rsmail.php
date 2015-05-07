<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

class com_rsmailInstallerScript 
{
	public function install($parent) {}
	
	public function preflight($type, $parent) {
		$app = JFactory::getApplication();
		
		$jversion = new JVersion();
		if (!$jversion->isCompatible('2.5.5')) {
			$app->enqueueMessage('Please upgrade to at least Joomla! 2.5.5 before continuing!', 'error');
			return false;
		}
		
		if ($type == 'update') {
			if ($jversion->isCompatible('2.5')) {
				if (!file_exists(JPATH_SITE.'/components/com_rsmail/helpers/version.php')) {
					$contents = file_get_contents(JPATH_ADMINISTRATOR.'/components/com_rsmail/rsmail.php');
					if (strpos($contents,'define(\'RSM_RS_REVISION\',\'18\');') === false) {
						$app->enqueueMessage('Please upgrade to at least RSMail! rev.18 before continuing!', 'error');
						return false;
					}
				}
			}
		}
		
		return true;
	}
	
	public function postflight($type, $parent) {
		$db = JFactory::getDbo();
		
		if ($type == 'update') {
			$this->updateProcess();
		}
		
		if ($type == 'update' || $type == 'install') {
			// Get a new installer
			$installer = new JInstaller();
			
			// Install the module
			$installer->install($parent->getParent()->getPath('source').'/extras/mod_rsmail');
			
			// Install the system plugin
			$installer->install($parent->getParent()->getPath('source').'/extras/plugins/plg_rsmail_joomla_registration');
			
			// Install the user plugin
			$installer->install($parent->getParent()->getPath('source').'/extras/plugins/plg_rsmail_user');
			
			// Install the RSMail! plugins
			$installer->install($parent->getParent()->getPath('source').'/extras/plugins/rsmcontent');
			$installer->install($parent->getParent()->getPath('source').'/extras/plugins/rsmsubscription');
			
			$db->setQuery('UPDATE '.$db->qn('#__extensions').' SET '.$db->qn('enabled').' = 1 WHERE '.$db->qn('element').' = '.$db->q('rsmail_joomla_registration').' AND '.$db->qn('type').' = '.$db->q('plugin').' AND '.$db->qn('folder').' = '.$db->q('system'));
			$db->execute();
			
			$db->setQuery('UPDATE '.$db->qn('#__extensions').' SET '.$db->qn('enabled').' = 1 WHERE '.$db->qn('element').' = '.$db->q('rsmail_joomla_registration').' AND '.$db->qn('type').' = '.$db->q('plugin').' AND '.$db->qn('folder').' = '.$db->q('user'));
			$db->execute();
			
			$db->setQuery('UPDATE '.$db->qn('#__extensions').' SET '.$db->qn('enabled').' = 1 WHERE '.$db->qn('element').' = '.$db->q('rsmcontent').' AND '.$db->qn('type').' = '.$db->q('plugin').' AND '.$db->qn('folder').' = '.$db->q('rsmail'));
			$db->execute();
			
			$db->setQuery('UPDATE '.$db->qn('#__extensions').' SET '.$db->qn('enabled').' = 1 WHERE '.$db->qn('element').' = '.$db->q('rsmsubscription').' AND '.$db->qn('type').' = '.$db->q('plugin').' AND '.$db->qn('folder').' = '.$db->q('rsmail'));
			$db->execute();
			
			// Update RSForm!Pro - RSMail! integration plugin
			if (file_exists(JPATH_SITE.'/plugins/system/rsfprsmail/rsfprsmail.xml')) {
				$installer->install($parent->getParent()->getPath('source').'/extras/plugins/rsfprsmail');
			}
			
			// Update RSMembership! - RSMail! integration plugin
			if (file_exists(JPATH_SITE.'/plugins/system/rsmrsmail/rsmrsmail.xml')) {
				$installer->install($parent->getParent()->getPath('source').'/extras/plugins/rsmrsmail');
			}
			
			// Update RSMail! plugins
			if (file_exists(JPATH_SITE.'/plugins/rsmail/rsmk2/rsmk2.xml')) {
				$installer = new JInstaller();
				$installer->install($parent->getParent()->getPath('source').'/extras/plugins/rsmk2');
			}
			if (file_exists(JPATH_SITE.'/plugins/rsmail/rsmrsblog/rsmrsblog.xml')) {
				$installer = new JInstaller();
				$installer->install($parent->getParent()->getPath('source').'/extras/plugins/rsmrsblog');
			}
			if (file_exists(JPATH_SITE.'/plugins/rsmail/rsmrseventspro/rsmrseventspro.xml')) {
				$installer = new JInstaller();
				$installer->install($parent->getParent()->getPath('source').'/extras/plugins/rsmrseventspro');
			}
			if (file_exists(JPATH_SITE.'/plugins/rsmail/rsmrsfiles/rsmrsfiles.xml')) {	
				$installer = new JInstaller();
				$installer->install($parent->getParent()->getPath('source').'/extras/plugins/rsmrsfiles');
			}
			if (file_exists(JPATH_SITE.'/plugins/rsmail/rsmvirtuemart/rsmvirtuemart.xml')) {
				$installer = new JInstaller();
				$installer->install($parent->getParent()->getPath('source').'/extras/plugins/rsmvirtuemart');
			}
			
			
			$sqlfile = JPATH_ADMINISTRATOR.'/components/com_rsmail/install.mysql.utf8.sql';
			$buffer = file_get_contents($sqlfile);
			if ($buffer === false) {
				JError::raiseWarning(1, JText::_('JLIB_INSTALLER_ERROR_SQL_READBUFFER'));
				return false;
			}
			
			jimport('joomla.installer.helper');
			$queries = JInstallerHelper::splitSql($buffer);
			if (count($queries) == 0) {
				// No queries to process
				return 0;
			}
			
			// Process each query in the $queries array (split out of sql file).
			foreach ($queries as $query)
			{
				$query = trim($query);
				if ($query != '' && $query{0} != '#') {
					$db->setQuery($query);
					if (!$db->execute()) {
						JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)));
						return false;
					}
				}
			}
		}
		
		if ($type == 'update') {
			$tables = $db->getTableList();
			if (in_array($db->getPrefix().'rsmail_integrations', $tables)) {
				$db->setQuery("SELECT `IdList`, `params` FROM `#__rsmail_integrations` WHERE `IntegrationType` = 'jur'");
				$integration = $db->loadObject();
				
				$registry = new JRegistry;
				$registry->loadString($integration->params);
				
				$db->setQuery("UPDATE `#__rsmail_config` SET `ConfigValue` = ".(int) $integration->IdList." WHERE `ConfigName` = 'jur_list' ");
				$db->execute();
				$db->setQuery("UPDATE `#__rsmail_config` SET `ConfigValue` = ".$db->q($registry->get('auto',0))." WHERE `ConfigName` = 'jur_auto' ");
				$db->execute();
				$db->setQuery("UPDATE `#__rsmail_config` SET `ConfigValue` = ".$db->q($registry->get('name',''))." WHERE `ConfigName` = 'jur_name' ");
				$db->execute();
				$db->setQuery("UPDATE `#__rsmail_config` SET `ConfigValue` = ".$db->q($registry->get('username',''))." WHERE `ConfigName` = 'jur_username' ");
				$db->execute();
				$db->setQuery("DROP TABLE IF EXISTS `#__rsmail_integrations`");
				$db->execute();
			}
		}
		
		$this->showInstall();
	}
	
	public function uninstall($parent) {
		$db 		= JFactory::getDbo();
		$installer	= new JInstaller();

		$db->setQuery('SELECT '.$db->qn('extension_id').' FROM '.$db->qn('#__extensions').' WHERE '.$db->qn('element').' = '.$db->q('rsmail_joomla_registration').' AND '.$db->qn('folder').' = '.$db->q('system').' AND '.$db->qn('type').' = '.$db->q('plugin').' LIMIT 1');
		$sysid = $db->loadResult();
		if ($sysid) $installer->uninstall('plugin', $sysid);
		
		$db->setQuery('SELECT '.$db->qn('extension_id').' FROM '.$db->qn('#__extensions').' WHERE '.$db->qn('element').' = '.$db->q('rsmail_joomla_registration').' AND '.$db->qn('folder').' = '.$db->q('user').' AND '.$db->qn('type').' = '.$db->q('plugin').' LIMIT 1');
		$uid = $db->loadResult();
		if ($uid) $installer->uninstall('plugin', $uid);
		
		$db->setQuery('SELECT '.$db->qn('extension_id').' FROM '.$db->qn('#__extensions').' WHERE '.$db->qn('element').' = '.$db->q('rsmcontent').' AND '.$db->qn('folder').' = '.$db->q('rsmail').' AND '.$db->qn('type').' = '.$db->q('plugin').' LIMIT 1');
		$rcid = $db->loadResult();
		if ($rcid) $installer->uninstall('plugin', $rcid);
		
		$db->setQuery('SELECT '.$db->qn('extension_id').' FROM '.$db->qn('#__extensions').' WHERE '.$db->qn('element').' = '.$db->q('rsmsubscription').' AND '.$db->qn('folder').' = '.$db->q('rsmail').' AND '.$db->qn('type').' = '.$db->q('plugin').' LIMIT 1');
		$rsid = $db->loadResult();
		if ($rsid) $installer->uninstall('plugin', $rsid);
		
		$db->setQuery('SELECT '.$db->qn('extension_id').' FROM '.$db->qn('#__extensions').' WHERE '.$db->qn('element').' = '.$db->q('mod_rsmail').' AND '.$db->qn('type').' = '.$db->q('module').' LIMIT 1');
		$mid = $db->loadResult();
		if ($mid) $installer->uninstall('module', $mid);
		
		$this->showUninstall();
	}
	
	
	protected function updateProcess() {
		$db = JFactory::getDbo();
		
		$db->setQuery("SHOW COLUMNS FROM #__rsmail_subscribers WHERE Field = ".$db->q('DateSubscribed')."");
		if ($datesub = $db->loadObject()) {
			if ($datesub->Type == 'int(15)') {
				$db->setQuery("ALTER TABLE #__rsmail_subscribers CHANGE `DateSubscribed` `DateSubscribed` VARCHAR(255) NOT NULL");
				$db->execute();
				$db->setQuery("UPDATE #__rsmail_subscribers SET `DateSubscribed` = FROM_UNIXTIME(`DateSubscribed`)");
				$db->execute();
				$db->setQuery("UPDATE #__rsmail_subscribers SET `DateSubscribed` = '0000-00-00 00:00:00' WHERE `DateSubscribed` = '1970-01-01 02:00:00'");
				$db->execute();					
				$db->setQuery("ALTER TABLE #__rsmail_subscribers CHANGE `DateSubscribed` ".$db->qn('DateSubscribed')." DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'");
				$db->execute();
			}
		}
		
		$db->setQuery("SHOW COLUMNS FROM #__rsmail_subscribers_opens WHERE Field = ".$db->q('date')."");
		if ($datesubo = $db->loadObject()) {
			if ($datesubo->Type == 'int(15)') {
				$db->setQuery("ALTER TABLE #__rsmail_subscribers_opens CHANGE `date` `date` VARCHAR(255) NOT NULL");
				$db->execute();
				$db->setQuery("UPDATE #__rsmail_subscribers_opens SET `date` = FROM_UNIXTIME(`date`)");
				$db->execute();
				$db->setQuery("UPDATE #__rsmail_subscribers_opens SET `date` = '0000-00-00 00:00:00' WHERE `date` = '1970-01-01 02:00:00'");
				$db->execute();					
				$db->setQuery("ALTER TABLE #__rsmail_subscribers_opens CHANGE `date` ".$db->qn('date')." DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'");
				$db->execute();
			}
		}
		
		$db->setQuery("SHOW COLUMNS FROM #__rsmail_subscribers_clicks WHERE Field = ".$db->q('date')."");
		if ($datesubc = $db->loadObject()) {
			if ($datesubc->Type == 'int(15)') {
				$db->setQuery("ALTER TABLE #__rsmail_subscribers_clicks CHANGE `date` `date` VARCHAR(255) NOT NULL");
				$db->execute();
				$db->setQuery("UPDATE #__rsmail_subscribers_clicks SET `date` = FROM_UNIXTIME(`date`)");
				$db->execute();
				$db->setQuery("UPDATE #__rsmail_subscribers_clicks SET `date` = '0000-00-00 00:00:00' WHERE `date` = '1970-01-01 02:00:00'");
				$db->execute();					
				$db->setQuery("ALTER TABLE #__rsmail_subscribers_clicks CHANGE `date` ".$db->qn('date')." DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'");
				$db->execute();
			}
		}
		
		$db->setQuery("SHOW COLUMNS FROM #__rsmail_sessions WHERE Field = ".$db->q('Date')."");
		if ($dateses = $db->loadObject()) {
			if ($dateses->Type == 'int(20)') {
				$db->setQuery("ALTER TABLE #__rsmail_sessions CHANGE `Date` `Date` VARCHAR(255) NOT NULL");
				$db->execute();
				$db->setQuery("UPDATE #__rsmail_sessions SET `Date` = FROM_UNIXTIME(`Date`)");
				$db->execute();
				$db->setQuery("UPDATE #__rsmail_sessions SET `Date` = '0000-00-00 00:00:00' WHERE `Date` = '1970-01-01 02:00:00'");
				$db->execute();					
				$db->setQuery("ALTER TABLE #__rsmail_sessions CHANGE `Date` ".$db->qn('Date')." DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'");
				$db->execute();
			}
		}
		
		$db->setQuery("SHOW COLUMNS FROM #__rsmail_sessions WHERE Field = ".$db->q('DeliverDate')."");
		if ($datesesd = $db->loadObject()) {
			if ($datesesd->Type == 'int(20)') {
				$db->setQuery("ALTER TABLE #__rsmail_sessions CHANGE `DeliverDate` `DeliverDate` VARCHAR(255) NOT NULL");
				$db->execute();
				$db->setQuery("UPDATE #__rsmail_sessions SET `DeliverDate` = FROM_UNIXTIME(`DeliverDate`)");
				$db->execute();
				$db->setQuery("UPDATE #__rsmail_sessions SET `DeliverDate` = '0000-00-00 00:00:00' WHERE `DeliverDate` = '1970-01-01 02:00:00'");
				$db->execute();					
				$db->setQuery("ALTER TABLE #__rsmail_sessions CHANGE `DeliverDate` ".$db->qn('DeliverDate')." DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'");
				$db->execute();
			}
		}
		
		$db->setQuery("SHOW COLUMNS FROM #__rsmail_log WHERE Field = ".$db->q('date')."");
		if ($datelog = $db->loadObject()) {
			if ($datelog->Type == 'int(11)') {
				$db->setQuery("ALTER TABLE #__rsmail_log CHANGE `date` `date` VARCHAR(255) NOT NULL");
				$db->execute();
				$db->setQuery("UPDATE #__rsmail_log SET `date` = FROM_UNIXTIME(`date`)");
				$db->execute();
				$db->setQuery("UPDATE #__rsmail_log SET `date` = '0000-00-00 00:00:00' WHERE `date` = '1970-01-01 02:00:00'");
				$db->execute();					
				$db->setQuery("ALTER TABLE #__rsmail_log CHANGE `date` ".$db->qn('date')." DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'");
				$db->execute();
			}
		}
		
		$db->setQuery("SHOW COLUMNS FROM #__rsmail_autoresponders WHERE Field = ".$db->q('AutoresponderDate')."");
		if ($datear = $db->loadObject()) {
			if ($datear->Type == 'int(15)') {
				$db->setQuery("ALTER TABLE #__rsmail_autoresponders CHANGE `AutoresponderDate` `AutoresponderDate` VARCHAR(255) NOT NULL");
				$db->execute();
				$db->setQuery("UPDATE #__rsmail_autoresponders SET `AutoresponderDate` = FROM_UNIXTIME(`AutoresponderDate`)");
				$db->execute();
				$db->setQuery("UPDATE #__rsmail_autoresponders SET `AutoresponderDate` = '0000-00-00 00:00:00' WHERE `AutoresponderDate` = '1970-01-01 02:00:00'");
				$db->execute();					
				$db->setQuery("ALTER TABLE #__rsmail_autoresponders CHANGE `AutoresponderDate` ".$db->qn('AutoresponderDate')." DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'");
				$db->execute();
			}
		}
		
		$db->setQuery("SHOW COLUMNS FROM #__rsmail_autoresponders WHERE Field = ".$db->q('DateCreated')."");
		if ($dateardc = $db->loadObject()) {
			if ($dateardc->Type == 'int(15)') {
				$db->setQuery("ALTER TABLE #__rsmail_autoresponders CHANGE `DateCreated` `DateCreated` VARCHAR(255) NOT NULL");
				$db->execute();
				$db->setQuery("UPDATE #__rsmail_autoresponders SET `DateCreated` = FROM_UNIXTIME(`DateCreated`)");
				$db->execute();
				$db->setQuery("UPDATE #__rsmail_autoresponders SET `DateCreated` = '0000-00-00 00:00:00' WHERE `DateCreated` = '1970-01-01 02:00:00'");
				$db->execute();					
				$db->setQuery("ALTER TABLE #__rsmail_autoresponders CHANGE `DateCreated` ".$db->qn('DateCreated')." DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'");
				$db->execute();
			}
		}
		
		$db->setQuery("SHOW COLUMNS FROM #__rsmail_ar_details WHERE Field = ".$db->q('SentDate')."");
		if ($datearsd = $db->loadObject()) {
			if ($datearsd->Type == 'int(15)') {
				$db->setQuery("ALTER TABLE #__rsmail_ar_details CHANGE `SentDate` `SentDate` VARCHAR(255) NOT NULL");
				$db->execute();
				$db->setQuery("UPDATE #__rsmail_ar_details SET `SentDate` = FROM_UNIXTIME(`SentDate`)");
				$db->execute();
				$db->setQuery("UPDATE #__rsmail_ar_details SET `SentDate` = '0000-00-00 00:00:00' WHERE `SentDate` = '1970-01-01 02:00:00'");
				$db->execute();					
				$db->setQuery("ALTER TABLE #__rsmail_ar_details CHANGE `SentDate` ".$db->qn('SentDate')." DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'");
				$db->execute();
			}
		}
		
		$db->setQuery("SHOW COLUMNS FROM `#__rsmail_emails` WHERE `Field` = 'enable'");
		if (!$db->loadResult()) {
			$db->setQuery("ALTER TABLE `#__rsmail_emails` ADD `enable` TINYINT( 1 ) NOT NULL AFTER `type`");
			$db->execute();
		}
		
		$db->setQuery("SHOW COLUMNS FROM `#__rsmail_sessions` WHERE `Field` = 'paused'");
		if (!$db->loadResult()) {
			$db->setQuery("ALTER TABLE `#__rsmail_sessions` ADD `paused` TINYINT( 1 ) NOT NULL");
			$db->execute();
		}
		
		$db->setQuery("SHOW COLUMNS FROM `#__rsmail_emails` WHERE `Field` = 'mode'");
		if (!$db->loadResult()) {
			$db->setQuery("ALTER TABLE `#__rsmail_emails` ADD `mode` TINYINT( 1 ) NOT NULL AFTER `enable`");
			$db->execute();
		}
		
		$db->setQuery("SHOW COLUMNS FROM `#__rsmail_emails` WHERE `Field` = 'from'");
		if (!$db->loadResult()) {
			$db->setQuery("ALTER TABLE `#__rsmail_emails` ADD `from` VARCHAR( 255 ) NOT NULL AFTER `mode`");
			$db->execute();
		}
		
		$db->setQuery("SHOW COLUMNS FROM `#__rsmail_emails` WHERE `Field` = 'fromname'");
		if (!$db->loadResult()) {
			$db->setQuery("ALTER TABLE `#__rsmail_emails` ADD `fromname` VARCHAR( 255 ) NOT NULL AFTER `from`");
			$db->execute();
		}
		
		$db->setQuery("SHOW COLUMNS FROM `#__rsmail_emails` WHERE `Field` = 'subject'");
		if (!$db->loadResult()) {
			$db->setQuery("ALTER TABLE `#__rsmail_emails` ADD `subject` VARCHAR( 255 ) NOT NULL AFTER `fromname`");
			$db->execute();
		}
		
		// Confirmation email update
		$db->setQuery("SELECT `ConfigValue` FROM `#__rsmail_config` WHERE `ConfigName` = 'confirm_email'");
		$confirm_email = $db->loadResult();
		if (!is_null($confirm_email)) {
			$db->setQuery("UPDATE `#__rsmail_emails` SET `enable` = ".(int) $confirm_email." WHERE `type` = 'confirmation'");
			$db->execute();
			$db->setQuery("DELETE FROM `#__rsmail_config` WHERE `ConfigName` = 'confirm_email'");
			$db->execute();
		}
		
		$db->setQuery("SELECT `ConfigValue` FROM `#__rsmail_config` WHERE `ConfigName` = 'confirm_type'");
		$confirm_type = $db->loadResult();
		if (!is_null($confirm_type)) {
			$db->setQuery("UPDATE `#__rsmail_emails` SET `mode` = ".(int) $confirm_type." WHERE `type` = 'confirmation'");
			$db->execute();
			$db->setQuery("DELETE FROM `#__rsmail_config` WHERE `ConfigName` = 'confirm_type'");
			$db->execute();
		}
		
		$db->setQuery("SELECT `ConfigValue` FROM `#__rsmail_config` WHERE `ConfigName` = 'confirm_from'");
		$confirm_from = $db->loadResult();
		if (!is_null($confirm_from)) {
			$db->setQuery("UPDATE `#__rsmail_emails` SET `from` = ".$db->q($confirm_from)." WHERE `type` = 'confirmation'");
			$db->execute();
			$db->setQuery("DELETE FROM `#__rsmail_config` WHERE `ConfigName` = 'confirm_from'");
			$db->execute();
		}
		
		$db->setQuery("SELECT `ConfigValue` FROM `#__rsmail_config` WHERE `ConfigName` = 'confirm_fromname'");
		$confirm_fromname = $db->loadResult();
		if (!is_null($confirm_fromname)) {
			$db->setQuery("UPDATE `#__rsmail_emails` SET `fromname` = ".$db->q($confirm_fromname)." WHERE `type` = 'confirmation'");
			$db->execute();
			$db->setQuery("DELETE FROM `#__rsmail_config` WHERE `ConfigName` = 'confirm_fromname'");
			$db->execute();
		}
		
		$db->setQuery("SELECT `ConfigValue` FROM `#__rsmail_config` WHERE `ConfigName` = 'confirm_subject'");
		$confirm_subject = $db->loadResult();
		if (!is_null($confirm_subject)) {
			$db->setQuery("UPDATE `#__rsmail_emails` SET `subject` = ".$db->q($confirm_subject)." WHERE `type` = 'confirmation'");
			$db->execute();
			$db->setQuery("DELETE FROM `#__rsmail_config` WHERE `ConfigName` = 'confirm_subject'");
			$db->execute();
		}
		
		// Unsubscribe email update
		$db->setQuery("SELECT `ConfigValue` FROM `#__rsmail_config` WHERE `ConfigName` = 'unsubscribe_email'");
		$unsubscribe_email = $db->loadResult();
		if (!is_null($unsubscribe_email)) {
			$db->setQuery("UPDATE `#__rsmail_emails` SET `enable` = ".(int) $unsubscribe_email." WHERE `type` = 'unsubscribe'");
			$db->execute();
			$db->setQuery("DELETE FROM `#__rsmail_config` WHERE `ConfigName` = 'unsubscribe_email'");
			$db->execute();
		}
		
		$db->setQuery("SELECT `ConfigValue` FROM `#__rsmail_config` WHERE `ConfigName` = 'unsubscribe_type'");
		$unsubscribe_type = $db->loadResult();
		if (!is_null($unsubscribe_type)) {
			$db->setQuery("UPDATE `#__rsmail_emails` SET `mode` = ".(int) $unsubscribe_type." WHERE `type` = 'unsubscribe'");
			$db->execute();
			$db->setQuery("DELETE FROM `#__rsmail_config` WHERE `ConfigName` = 'unsubscribe_type'");
			$db->execute();
		}
		
		$db->setQuery("SELECT `ConfigValue` FROM `#__rsmail_config` WHERE `ConfigName` = 'unsubscribe_from'");
		$unsubscribe_from = $db->loadResult();
		if (!is_null($unsubscribe_from)) {
			$db->setQuery("UPDATE `#__rsmail_emails` SET `from` = ".$db->q($unsubscribe_from)." WHERE `type` = 'unsubscribe'");
			$db->execute();
			$db->setQuery("DELETE FROM `#__rsmail_config` WHERE `ConfigName` = 'unsubscribe_from'");
			$db->execute();
		}
		
		$db->setQuery("SELECT `ConfigValue` FROM `#__rsmail_config` WHERE `ConfigName` = 'unsubscribe_fromname'");
		$unsubscribe_fromname = $db->loadResult();
		if (!is_null($unsubscribe_fromname)) {
			$db->setQuery("UPDATE `#__rsmail_emails` SET `fromname` = ".$db->q($unsubscribe_fromname)." WHERE `type` = 'unsubscribe'");
			$db->execute();
			$db->setQuery("DELETE FROM `#__rsmail_config` WHERE `ConfigName` = 'unsubscribe_fromname'");
			$db->execute();
		}
		
		$db->setQuery("SELECT `ConfigValue` FROM `#__rsmail_config` WHERE `ConfigName` = 'unsubscribe_subject'");
		$unsubscribe_subject = $db->loadResult();
		if (!is_null($unsubscribe_subject)) {
			$db->setQuery("UPDATE `#__rsmail_emails` SET `subject` = ".$db->q($unsubscribe_subject)." WHERE `type` = 'unsubscribe'");
			$db->execute();
			$db->setQuery("DELETE FROM `#__rsmail_config` WHERE `ConfigName` = 'unsubscribe_subject'");
			$db->execute();
		}
		
		// Unsubscribe link email update
		$db->setQuery("SELECT `ConfigValue` FROM `#__rsmail_config` WHERE `ConfigName` = 'unsubscribelink_type'");
		$unsubscribelink_type = $db->loadResult();
		if (!is_null($unsubscribelink_type)) {
			$db->setQuery("UPDATE `#__rsmail_emails` SET `mode` = ".(int) $unsubscribelink_type." WHERE `type` = 'unsubscribelink'");
			$db->execute();
			$db->setQuery("DELETE FROM `#__rsmail_config` WHERE `ConfigName` = 'unsubscribelink_type'");
			$db->execute();
		}
		
		$db->setQuery("SELECT `ConfigValue` FROM `#__rsmail_config` WHERE `ConfigName` = 'unsubscribelink_from'");
		$unsubscribelink_from = $db->loadResult();
		if (!is_null($unsubscribelink_from)) {
			$db->setQuery("UPDATE `#__rsmail_emails` SET `from` = ".$db->q($unsubscribelink_from)." WHERE `type` = 'unsubscribelink'");
			$db->execute();
			$db->setQuery("DELETE FROM `#__rsmail_config` WHERE `ConfigName` = 'unsubscribelink_from'");
			$db->execute();
		}
		
		$db->setQuery("SELECT `ConfigValue` FROM `#__rsmail_config` WHERE `ConfigName` = 'unsubscribelink_fromname'");
		$unsubscribelink_fromname = $db->loadResult();
		if (!is_null($unsubscribelink_fromname)) {
			$db->setQuery("UPDATE `#__rsmail_emails` SET `fromname` = ".$db->q($unsubscribelink_fromname)." WHERE `type` = 'unsubscribelink'");
			$db->execute();
			$db->setQuery("DELETE FROM `#__rsmail_config` WHERE `ConfigName` = 'unsubscribelink_fromname'");
			$db->execute();
		}
		
		$db->setQuery("SELECT `ConfigValue` FROM `#__rsmail_config` WHERE `ConfigName` = 'unsubscribelink_subject'");
		$unsubscribelink_subject = $db->loadResult();
		if (!is_null($unsubscribelink_subject)) {
			$db->setQuery("UPDATE `#__rsmail_emails` SET `subject` = ".$db->q($unsubscribelink_subject)." WHERE `type` = 'unsubscribelink'");
			$db->execute();
			$db->setQuery("DELETE FROM `#__rsmail_config` WHERE `ConfigName` = 'unsubscribelink_subject'");
			$db->execute();
		}
		
		$db->setQuery("SHOW INDEX FROM #__rsmail_templates WHERE Key_name = 'TemplateName'");
		if ($db->loadResult())
		{
			$db->setQuery("ALTER TABLE #__rsmail_templates DROP INDEX TemplateName");
			$db->execute();
		}
	}
	
	protected function showUninstall() {
		echo 'RSMail! component has been successfully uninstaled!';
	}
	
	protected function showInstall() {
?>
<style type="text/css">
.version-history {
	margin: 0 0 2em 0;
	padding: 0;
	list-style-type: none;
}
.version-history > li {
	margin: 0 0 0.5em 0;
	padding: 0 0 0 4em;
}
.version-new,
.version-fixed,
.version-upgraded {
	float: left;
	font-size: 0.8em;
	margin-left: -4.9em;
	width: 4.5em;
	color: white;
	text-align: center;
	font-weight: bold;
	text-transform: uppercase;
	-webkit-border-radius: 4px;
	-moz-border-radius: 4px;
	border-radius: 4px;
}

.version-new {
	background: #7dc35b;
}
.version-fixed {
	background: #e9a130;
}
.version-upgraded {
	background: #61b3de;
}

.install-ok {
	background: #7dc35b;
	color: #fff;
	padding: 3px;
}

.install-not-ok {
	background: #E9452F;
	color: #fff;
	padding: 3px;
}

#installer-left {
	float: left;
	width: 230px;
	padding: 5px;
}

#installer-right {
	float: left;
}

.com-rsmail-button {
	display: inline-block;
	background: #459300 url(components/com_rsmail/assets/images/bg-button-green.gif) top left repeat-x !important;
	border: 1px solid #459300 !important;
	padding: 2px;
	color: #fff !important;
	cursor: pointer;
	margin: 0;
	-webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    border-radius: 5px;
	text-decoration: none !important;
}

.big-warning {
	background: #FAF0DB;
	border: solid 1px #EBC46F;
	padding: 5px;
}

.big-warning b {
	color: red;
}
</style>
<div id="installer-left">
	<img src="components/com_rsmail/assets/images/logos/rsmail_box.png" alt="RSMail! Box" />
</div>
<div id="installer-right">
	<ul class="version-history">
		<li><span class="version-upgraded">Upg</span> Joomla! 3.0 compatibility (including responsive design &amp; bootstrap compatibility).</li>
		<li><span class="version-upgraded">Upg</span> Refactored code to use less resources.</li>
		<li><span class="version-upgraded">Upg</span> Download attachments in the online version of the message.</li>
		<li><span class="version-new">New</span> Pause/Resume cron sessions.</li>
		<li><span class="version-new">New</span> Session and subscribers charts.</li>
	</ul>
	<a class="com-rsmail-button" href="index.php?option=com_rsmail">Start using RSMail!</a>
	<a class="com-rsmail-button" href="http://www.rsjoomla.com/support/documentation/view-knowledgebase/56-rsmail-user-guide.html" target="_blank">Read the RSMail! User Guide</a>
	<a class="com-rsmail-button" href="http://www.rsjoomla.com/customer-support/tickets.html" target="_blank">Get Support!</a>
</div>
<div style="clear: both;"></div>
<?php
	}
}