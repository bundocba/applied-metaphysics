<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined( '_JEXEC' ) or die( 'Restricted access' ); 
jimport( 'joomla.application.component.view');

class rsmailViewTemplate extends JViewLegacy
{
	protected $form;
	protected $item;
	protected $preview;
	
	public function display($tpl = null) {		
		$layout = $this->getLayout();
		
		if ($layout == 'preview') {
			$this->preview	= $this->get('Preview');
		} else {
			$this->form 	= $this->get('Form');
			$this->item 	= $this->get('Item');
			
			$this->addToolBar();
		}
		parent::display($tpl);
	}
	
	protected function addToolBar() {
		$this->item->IdTemplate ? JToolBarHelper::title(JText::_('RSM_EDIT_TEMPLATE'),'rsmail') : JToolBarHelper::title(JText::_('RSM_ADD_TEMPLATE'),'rsmail');
		
		JToolBarHelper::apply('template.apply');
		JToolBarHelper::save('template.save');
		JToolBarHelper::cancel('template.cancel');
		
		if($this->item->IdTemplate) {	
			JToolBarHelper::custom('template.preview','previewb','previewb',JText::_('RSM_PREVIEW'),false);
		}
		
		$html = '<a class="modal btn btn-small" href="index.php?option=com_rsmail&view=templates&layout=placeholders&from=template&tmpl=component" rel="{handler: \'iframe\', size: {x: 750, y: 500}}">'."\n";
		$html .= rsmailHelper::isJ3() ? "<span class=\"icon-new\">\n" : "<span class=\"icon-32-new\">\n";
		$html .= "</span>\n";
		$html .= JText::_('RSM_PLACEHOLDERS')."\n";
		$html .= "</a>\n";
		
		JToolBar::getInstance('toolbar')->appendButton('Custom', $html);
	}
}