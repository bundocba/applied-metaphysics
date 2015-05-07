<?php
/**
* @version 1.0.0
* @package RSform!Pro 1.0.0
* @copyright (C) 2007-2012 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.plugin.plugin' );

class plgRSMailRsmsubscription extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatibility we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @access	protected
	 * @param	object	$subject The object to observe
	 * @param 	array   $config  An array that holds the plugin configuration
	 * @since	1.0
	 */
	 
	protected $_selector = null;
	protected $_editor = null;
	
	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
		
		$conf				= JFactory::getConfig();
		$selector			= JFactory::getApplication()->input->get('selector','');
		$editor				= $conf->get('editor');
		$this->_editor		= JEditor::getInstance($editor);
		
		if ($selector == 'TemplateBody')
			$this->_selector = 'jform_TemplateBody';
		else $this->_selector = 'jform_MessageBody';
	}
	
	protected function canRun() {
		$helper = JPATH_SITE.'/components/com_rsmail/helpers/rsmail.php';
		if (file_exists($helper)) {
			if (JFactory::getApplication()->isAdmin())
				require_once($helper);
				
			JFactory::getLanguage()->load('plg_rsmail_rsmsubscription');
			return true;
		}
		
		return false;
	}
	
	public function rsm_addPlaceholderButtons() {
		if (!$this->canRun()) 
			return;
		
		echo '<a href="javascript:void(0);" class="rsm_btn_pla" onclick="rsm_show(\'rsmsubscription\')">'.JText::_('RSM_SUBSCRIPTION_PLA').'</a>';
	}
	
	public function rsm_showPlaceholderContent() {
		if (!$this->canRun()) 
			return;
		
		if ($this->_editor->get('_name') == 'jce')
			$js = str_replace("'content'", 'content', $this->_editor->setContent($this->_selector, 'content'));
		else 
			$js = $this->_editor->setContent($this->_selector, 'content');
?>	
	<script type="text/javascript">
		window.addEvent('domready', function() {
			window.parent.document.getElementById('rsm_frame').style.height = document.getElementById('adminForm').offsetHeight + 50 +'px';
		});
		
		function rsm_placeholder(type) {
			var display = window.parent.window.parent.document.getElementById('<?php echo $this->_selector; ?>').style.display;
			if (display) {
				if (typeof window.parent.window.parent.jInsertEditorText == 'function') {
					window.parent.window.parent.jInsertEditorText('['+type+']', '<?php echo $this->_selector; ?>');
				} else {
					var content = window.parent.window.parent.<?php echo $this->_editor->getContent($this->_selector); ?>
					content += '['+type+']';
					window.parent.window.parent.<?php echo $js; ?>
				}
				
				window.parent.window.parent.SqueezeBox.close();
			} else {
				var content = window.parent.window.parent.document.getElementById('<?php echo $this->_selector; ?>').value;
				content += '['+type+']';
				window.parent.window.parent.document.getElementById('<?php echo $this->_selector; ?>').value = content;
				window.parent.window.parent.SqueezeBox.close();
			}
		}
	</script>
	
	<table id="adminForm" class="adminlist table table-striped" cellspacing="1">
		<tr>
			<td><a href="javascript:void(0)" onclick="rsm_placeholder('site')"><?php echo JText::_('RSM_SUBSCRIPTION_SITE_NAME'); ?></td>
		</tr>
		<tr>
			<td><a href="javascript:void(0)" onclick="rsm_placeholder('email')"><?php echo JText::_('RSM_SUBSCRIPTION_EMAIL'); ?></td>
		</tr>
		<tr>
			<td><a href="javascript:void(0)" onclick="rsm_placeholder('unsubscribe')"><?php echo JText::_('RSM_SUBSCRIPTION_UNSUBSCRIBE'); ?></td>
		</tr>
		<tr>
			<td><a href="javascript:void(0)" onclick="rsm_placeholder('sitenewsletter')"><?php echo JText::_('RSM_SUBSCRIPTION_MESSAGE_ONLINE'); ?></td>
		</tr>
		<tr>
			<td><a href="javascript:void(0)" onclick="rsm_placeholder('detailslink')"><?php echo JText::_('RSM_SUBSCRIPTION_DETAILS'); ?></td>
		</tr>
		<tr>
			<td><a href="javascript:void(0)" onclick="rsm_placeholder('date')"><?php echo JText::_('RSM_SUBSCRIPTION_DATE'); ?></td>
		</tr>
	</table>
<?php
	}
	
	public function rsm_parseMessageContent($args) {
		if (!$this->canRun()) 
			return;
		
		$jconfig		= new JConfig();
		$mask			= rsmailHelper::getConfig('global_dateformat');
		$message		= $args['message'];
		$idmessage		= @$args['idmessage'];
		$idsubscriber	= @$args['idsubscriber'];
		$idsession		= @$args['idsession'];
		$email			= @$args['email'];
		$idlist			= @$args['idlist'];
		$aresponder		= @$args['ar'];
		
		$message = str_replace('[site]'	,$jconfig->sitename	,$message);
		$message = str_replace('[date]'	,JFactory::getDate()->format($mask) ,$message);
		
		if (isset($email)) {
			$viewmessageonline = isset($aresponder) ? '' : JRoute::_(JURI::root().'index.php?option=com_rsmail&view=history&layout=message&code='.md5($idmessage.$idsession.$email));
			$unsubscribe = JRoute::_(JURI::root().'index.php?option=com_rsmail&view=unsubscribe&vid='.md5($email.$idsubscriber).'&IdSession='.$idsession);
			$details = JRoute::_(JURI::root().'index.php?option=com_rsmail&view=details&id='.md5($email.$idsubscriber.$idlist));
			
			$message = str_replace('[email]'			,$email					,$message);
			$message = str_replace('[unsubscribe]'		,$unsubscribe			,$message);
			$message = str_replace('[detailslink]'		,$details				,$message);
			$message = str_replace('[sitenewsletter]'	,$viewmessageonline		,$message);
		}
		
		$args['message'] = $message;
	}
}