<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined( '_JEXEC' ) or die( 'Restricted access' ); 
jimport( 'joomla.application.component.view');

class rsmailViewCronLogs extends JViewLegacy
{
	protected $sidebar;
	protected $filterbar;
	
	public function display($tpl = null) {
		$layout				= $this->getLayout();
		$this->filterbar	= $this->get('Filterbar');
		$this->sidebar		= $this->get('Sidebar');
		
		if ($layout == 'log') {
			$this->items		= $this->get('CronLogEmails');
			$this->pagination	= $this->get('CronLogEmailsPagination');
		} else {
			$this->items		= $this->get('Data');
			$this->pagination	= $this->get('Pagination');
		}
		
		$this->addToolBar();
		parent::display($tpl);
	}
	
	protected function addToolBar() {
		JToolBarHelper::title(JText::_('RSM_CRON_LOGS'),'rsmail');
		JToolBarHelper::custom('rsmail','rsmail32','rsmail32',JText::_('RSM_RS_PRODUCT'),false);
	}
}