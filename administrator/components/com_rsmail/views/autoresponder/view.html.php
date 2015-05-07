<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined( '_JEXEC' ) or die( 'Restricted access' ); 
jimport( 'joomla.application.component.view');

class rsmailViewAutoresponder extends JViewLegacy
{
	protected $form;
	protected $item;
	
	public function display($tpl = null) {
		$layout = $this->getLayout();
		
		if ($layout == 'message') {
			$this->placeholders		= $this->get('Placeholders');
			$this->listfields		= $this->get('ListFields');
			$this->frequency		= $this->get('Frequency');
			$this->arlists			= $this->get('AutoresponderLists');
			$this->period			= $this->get('Period');
			$this->idamessage		= JFactory::getApplication()->input->getInt('IdAutoresponderMessage',0);
		} else {
			$this->form 	= $this->get('Form');
			$this->item 	= $this->get('Item');
			$this->lists	= rsmailHelper::lists();
			$this->messages	= $this->get('Messages');
			$this->subjects	= $this->get('Subjects');
			
			$this->addScripts();
			$this->addToolBar();
		}
		parent::display($tpl);
	}
	
	protected function addToolBar() {
		$this->item->IdAutoresponder ? JToolBarHelper::title(JText::_('RSM_EDIT_FOLLOWUP'),'rsmail') : JToolBarHelper::title(JText::_('RSM_ADD_FOLLOWUP'),'rsmail');

		JToolBarHelper::apply('autoresponder.apply');
		if($this->item->IdAutoresponder) JToolBarHelper::save('autoresponder.save');
		JToolBarHelper::cancel('autoresponder.cancel');
	}
	
	protected function addScripts() {
		$doc = JFactory::getDocument();
		
		if (!rsmailHelper::isJ3()) {
			$doc->addScript(JURI::root(true).'/administrator/components/com_rsmail/assets/js/jquery.js');
			$doc->addScriptDeclaration('jQuery.noConflict();');
		}
		$doc->addScript(JURI::root(true).'/administrator/components/com_rsmail/assets/js/jquery-ui-min.js');
	}
}