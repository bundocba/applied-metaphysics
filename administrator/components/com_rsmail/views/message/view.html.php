<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined( '_JEXEC' ) or die( 'Restricted access' ); 
jimport( 'joomla.application.component.view');

class rsmailViewMessage extends JViewLegacy
{
	protected $form;
	protected $item;
	protected $tabs;
	protected $layouts;
	protected $files;
	
	public function display($tpl = null) {
		$layout	= $this->getLayout();
		
		if ($layout == 'files') {
			$this->files 	= $this->get('AllFiles');
		} elseif ($layout == 'preview') {
			$this->preview	= $this->get('Preview');
		} else {
			$this->form 	= $this->get('Form');
			$this->item 	= $this->get('Item');
			$this->tabs		= $this->get('Tabs');
			$this->files 	= $this->get('Files');
			$this->layouts 	= $this->get('Layouts');
			$this->spam		= JFactory::getApplication()->input->getInt('spam',0);
			
			$this->addToolBar();
		}
		parent::display($tpl);
	}
	
	protected function addToolBar() {
		$this->item->IdMessage ? JToolBarHelper::title(JText::_('RSM_EDIT_MESSAGE'),'rsmail') : JToolBarHelper::title(JText::_('RSM_ADD_MESSAGE'),'rsmail');
		
		JToolBarHelper::apply('message.apply');
		if ($this->item->IdMessage) {
			JToolBarHelper::save('message.save');
			JToolBarHelper::save2copy('message.save2copy');
		}
		JToolBarHelper::cancel('message.cancel');
		
		$html = '<a class="modal btn btn-small" href="index.php?option=com_rsmail&view=templates&layout=placeholders&from=message&tmpl=component" rel="{handler: \'iframe\', size: {x: 750, y: 500}}">'."\n";
		$html .= rsmailHelper::isJ3() ? "<span class=\"icon-new\">\n" : "<span class=\"icon-32-new\">\n";
		$html .= "</span>\n";
		$html .= JText::_('RSM_PLACEHOLDERS')."\n";
		$html .= "</a>\n";
		
		JToolBar::getInstance('toolbar')->appendButton('Custom', $html);
		
		if($this->item->IdMessage) {
			JToolBarHelper::custom('message.addtemplate','addtemplate','addtemplate',JText::_('RSM_ADD_TO_TEMPLETE'),false);
			JToolBarHelper::custom('message.preview','previewb','previewb',JText::_('RSM_PREVIEW'),false);
			JToolBarHelper::custom('send','send','send',JText::_('RSM_SEND'),false);
		}
	}
}