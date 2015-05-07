<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// no direct access
defined('_JEXEC') or die('Restricted access');
	
class modRsmailHelper {
	
	public function getThankYou() {
		if (file_exists(JPATH_SITE.'/components/com_rsmail/helpers/rsmail.php'))
			require_once JPATH_SITE.'/components/com_rsmail/helpers/rsmail.php';
		else return;
		
		$thankyou = rsmailHelper::getMessage('thankyou');
		return $thankyou->text;
	}
	
	public function getLists($params,$mid) {
		$db 		= JFactory::getDbo();
		$query		= $db->getQuery(true);
		$listid 	= $params->get('listid');
		$fieldids 	= $params->get('fieldids');
		$multi		= $params->get('enablemultiple','0');
		$lists		= '';
		$list		= array();
		
		if(is_array($fieldids)) 
			$fieldids = implode(',',$fieldids); 
		
		if($multi == 1 && !empty($listid)) {
			if(is_array($listid)) {
				foreach($listid as $id) {
					$query->clear()->select($db->qn('ListName'))->from($db->qn('#__rsmail_lists'))->where($db->qn('IdList').' = '.(int) $id);
					$db->setQuery($query);
					$list[] = JHTML::_('select.option', $id, $db->loadResult());
				}
			} else {
				$query->clear()->select($db->qn('ListName'))->from($db->qn('#__rsmail_lists'))->where($db->qn('IdList').' = '.(int) $id);
				$db->setQuery($query);
				$list[] = JHTML::_('select.option', $listid, $db->loadResult() );
			}
			
			$lists = JHTML::_('select.genericlist', $list, 'IdList'.$mid, 'size="1" class="inputbox input-medium" onchange="rsm_show_fields(\''.(JURI::root()).'\',this.value,\''.$fieldids.'\','.$mid.');"', 'value', 'text');
		}
		
		return $lists;
	}
	
}