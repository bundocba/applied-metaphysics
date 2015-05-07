<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined( '_JEXEC' ) or die( 'Restricted access' ); 
jimport( 'joomla.application.component.view');

class rsmailViewHistory extends JViewLegacy
{
	public function display($tpl = null) {
		$layout = $this->getLayout();
		
		if ($layout == 'message') {
			$this->message	= $this->get('Message');
			$this->code		= JFactory::getApplication()->input->getString('code','');
		} else {
			$this->items	= $this->get('Items');
		}
		
		parent::display($tpl);
	}
}