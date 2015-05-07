<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined( '_JEXEC' ) or die( 'Restricted access' ); 
jimport( 'joomla.application.component.view');

class rsmailViewImport extends JViewLegacy
{
	public function display($tpl = null) {
		$layout = $this->getLayout();
		
		if ($layout == 'import') {
			$this->content = $this->get('Content');
			$this->headers = $this->get('Headers');
			
			if (!empty($this->content)) {
				$max = 1; 	
				$key = 0;
				foreach ($this->content as $i => $el) {
					if (count($el) > $max) {
						$max = count($el);
						$key = $i;
					}
				}
				$n = count($this->content[$key]);
				
				$default = array(JHTML::_('select.option', 0, JText::_('RSM_IGNORE') , 'FieldName', 'FieldName'), JHTML::_('select.option', 0, JText::_('RSM_EMAIL') , 'FieldName', 'FieldName' ));
				$fields = array_merge($default,$this->headers);
				
				for ($i=0; $i<=$n; $i++) {
					$lists['fields'][$i] = JHTML::_('select.genericlist', $fields, 'FieldName['.$i.']','class="inputbox" size="1"','FieldName','FieldName');
				}
				
				$this->lists = $lists;
			}
		} else {
			$this->lists		= $this->get('Lists');
			$this->sidebar		= $this->get('Sidebar');
		}
		
		$this->addToolBar($layout);
		parent::display($tpl);
	}
	
	protected function addToolBar($layout) {
		if ($layout == 'import') {
			JToolBarHelper::title(JText::_('RSM_IMPORT'),'rsmail');
			JToolBarHelper::custom('import.save', 'import-btn', 'import-btn', JText::_('RSM_IMPORT') , false);
			JToolBarHelper::cancel('import.cancel');
			JToolBarHelper::custom('rsmail','rsmail32','rsmail32',JText::_('RSM_RS_PRODUCT'),false);
		} else {
			JToolBarHelper::title(JText::_('RSM_IMPORT'),'rsmail');
			JToolBarHelper::custom('rsmail','rsmail32','rsmail32',JText::_('RSM_RS_PRODUCT'),false);
		}
	}
}