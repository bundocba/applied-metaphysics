<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.plugin.plugin' );

class plgSystemRsmail_Joomla_Registration extends JPlugin
{
	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
	}
	
	public function onAfterRender() {
		if (!$this->canRun() || JFactory::getApplication()->isAdmin())
			return;
		
		$config	= rsmailHelper::getConfig();
		
		if (!$config->enable_jur)
			return;
		
		if (!empty($config->jur_list)) {
			if (JFactory::getApplication()->input->get('option','') == 'com_users' && JFactory::getApplication()->input->get('view','') == 'registration' && $config->jur_auto == 1) {
				$content = JResponse::getBody();
					
				if (rsmailHelper::isJ3()) {
					$search = '<input type="text" name="jform[email2]" class="validate-email required" id="jform_email2" value="" size="30"/>					</div>
				</div>';
					
					$replace = '<input type="text" name="jform[email2]" class="validate-email required" id="jform_email2" value="" size="30"/>					</div>
				</div><div class="control-group">
					<div class="control-label">
					<label id="rsm_subscribe-lbl" for="rsm_subscribe">'.JText::_('RSM_PLG_SUBSCRIBE').'</label>										</div>
					<div class="controls">
						<input type="checkbox" name="rsm_subscribe" id="rsm_subscribe" value="1" />					</div>
				</div>';
					
				} else {
					$search = 'class="validate-email required" id="jform_email2" value="" size="30"/></dd>
																				</dl>
		</fieldset>';					
					$replace = 'class="validate-email required" size="30"/></dd>
				<dt>
				<label id="rsm_subscribe" for="rsm_subscribe">'.JText::_('RSM_PLG_SUBSCRIBE').':</label>								</dt>
				<dd><input type="checkbox" name="rsm_subscribe" id="rsm_subscribe" value="1" /></dd></dl>
		</fieldset>';
				}
					
				$content = str_replace($search,$replace,$content);
				JResponse::setBody($content);
			}
		}
	}
	
	protected function canRun() {
		if (file_exists(JPATH_SITE.'/components/com_rsmail/helpers/rsmail.php')) {
			require_once JPATH_SITE.'/components/com_rsmail/helpers/rsmail.php';
			$lang 	= JFactory::getLanguage();
			$lang->load('com_rsmail', JPATH_ADMINISTRATOR);
			$lang->load('com_rsmail', JPATH_SITE);
			
			return true;
		}
		
		return false;
	}
}