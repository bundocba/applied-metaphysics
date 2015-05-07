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

class plgRSMailRsmcontent extends JPlugin
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
	 
	var $_db = null;
	var $_query = null;
	var $_data = null;
	var $_total = null;
	var $_limit = null;
	var $_limitstart = null;
	var $_pagination = null;
	var $_filter = null;
	var $_selector = null;
	var $_editor = null;
	
	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
		
		$this->_db			= JFactory::getDBO();
		$app 				= JFactory::getApplication();
		$conf				= JFactory::getConfig();
		$this->_limit		= $app->getUserStateFromRequest('rsmcontent.limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$this->_limitstart 	= $app->getUserStateFromRequest('rsmcontent.limitstart', 'limitstart', 0, 'int' );
		$selector			= JFactory::getApplication()->input->get('selector','');
		$editor				= $conf->get('editor');
		$this->_editor		= JEditor::getInstance($editor);
		
		if ($selector == 'TemplateBody')
			$this->_selector = 'jform_TemplateBody';
		else $this->_selector = 'jform_MessageBody';
		
		$this->_setFilter();
		$this->_setQuery();
		$this->_getData();
		$this->_setTotal();
		$this->_setPagination();
	}
	
	protected function canRun() {
		$helper = JPATH_SITE.'/components/com_rsmail/helpers/rsmail.php';
		if (file_exists($helper)) {
			if (JFactory::getApplication()->isAdmin())
				require_once($helper);
				
			JFactory::getLanguage()->load('plg_rsmail_rsmcontent');
			return true;
		}
		
		return false;
	}
	
	public function rsm_addPlaceholderButtons() {
		if (!$this->canRun()) 
			return;
			
		echo '<a href="javascript:void(0);" class="rsm_btn_pla" onclick="rsm_show(\'rsmcontent\')">'.JText::_('RSM_CONTENT_ARTICLES_PLA').'</a>';
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
		
		function rsm_placeholder(type,id) {
			var display = window.parent.window.parent.document.getElementById('<?php echo $this->_selector; ?>').style.display;
			
			if (display) {
				if (typeof window.parent.window.parent.jInsertEditorText == 'function') {
					window.parent.window.parent.jInsertEditorText('['+type+':'+id+']', '<?php echo $this->_selector; ?>');
				} else {
					var content = window.parent.window.parent.<?php echo $this->_editor->getContent($this->_selector); ?>
					content += '['+type+':'+id+']';
					window.parent.window.parent.<?php echo $js; ?>
				}
				window.parent.window.parent.SqueezeBox.close();
			} else {
				var content = window.parent.window.parent.document.getElementById('<?php echo $this->_selector; ?>').value;
				content += '['+type+':'+id+']';
				window.parent.window.parent.document.getElementById('<?php echo $this->_selector; ?>').value = content;
				window.parent.window.parent.SqueezeBox.close();
			}
		}
	</script>
	
	<form method="post" name="adminForm" id="adminForm" action="index.php">
		<table>
			<tr>
				<td width="100%">
					<input type="text" name="rs_filter" id="filter" onchange="this.form.submit();" value="<?php echo $this->_filter; ?>" placeholder="<?php echo JText::_('RSM_FILTER'); ?>" /> 
					<input type="button" class="btn button" value="<?php echo JText::_('RSM_FILTER'); ?>" onclick="this.form.submit();" /> 
					<input type="button" class="btn button" onclick="this.form.rs_filter.value='';this.form.submit();" value="<?php echo JText::_('RSM_CLEAR_FILTER'); ?> " />
				</td>
			</tr>
		</table>
		
		<table class="adminlist table table-striped" cellspacing="1">
			<thead>
				<tr>
					<th width="1%">#</th>
					<th width="15%"><?php echo JText::_('RSM_ARTICLE_TITLE'); ?></th>
					<th width="20%"><?php echo JText::_('RSM_ARTICLE_CONTENT'); ?></th>
					<th width="3%"><?php echo JText::_('RSM_ARTICLE_DATE'); ?></th>
					<th width="3%"><?php echo JText::_('RSM_ARTICLE_AUTHOR'); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($this->_data as $i => $row) { ?>
				<tr>
					<td><?php echo $row->id; ?></td>
					<td><a href="javascript:void(0)" onclick="rsm_placeholder('articletitle','<?php echo $row->id; ?>')"><?php echo $row->title; ?></td>
					<td><a href="javascript:void(0)" onclick="rsm_placeholder('articletext','<?php echo $row->id; ?>')"><?php echo $row->title; ?></td>
					<td align="center"><a href="javascript:void(0)" onclick="rsm_placeholder('articledate','<?php echo $row->id; ?>')"><?php echo $row->created; ?></td>
					<td align="center"><a href="javascript:void(0)" onclick="rsm_placeholder('articleuser','<?php echo $row->id; ?>')"><?php echo $row->username; ?></td>
				</tr>
			<?php } ?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="5"><?php echo $this->_pagination->getListFooter(); ?></td>
				</tr>
			</tfoot>
		</table>
		
		<input type="hidden" name="option" value="com_rsmail" />
		<input type="hidden" name="task" value="content" />
		<input type="hidden" name="tmpl" value="component" />
		<input type="hidden" name="plugin" value="rsmcontent" />
		<input type="hidden" name="selector" value="<?php echo str_replace('jform_','',$this->_selector); ?>" />
	</form>
<?php
	}
	
	protected function _setFilter() {
		$this->_filter = JFactory::getApplication()->input->getString('rs_filter','');
	}
	
	protected function _setQuery() {
		$this->_query = 'SELECT SQL_CALC_FOUND_ROWS '.$this->_db->qn('c.id').', '.$this->_db->qn('c.title').', '.$this->_db->qn('c.state','published').', '.$this->_db->qn('c.created').', '.$this->_db->qn('u.username').' FROM '.$this->_db->qn('#__content','c').' LEFT JOIN '.$this->_db->qn('#__users','u').' ON '.$this->_db->qn('u.id').' = '.$this->_db->qn('c.created_by').' WHERE '.$this->_db->qn('c.title').' LIKE '.$this->_db->q('%'.$this->_db->escape($this->_filter, true).'%').' ORDER BY '.$this->_db->qn('c.ordering').' DESC';
	}
	
	protected function _getData() {
		if (empty($this->_data)) {
			$this->_db->setQuery($this->_query,$this->_limitstart,$this->_limit);
			$this->_data = $this->_db->loadObjectList();
		}
	}
	
	protected function _setTotal() {
		if (empty($this->_total)) {
			$this->_db->setQuery('SELECT FOUND_ROWS()');
			$this->_total = $this->_db->loadResult();
		}
	}
	
	protected function _setPagination() {
		if (empty($this->_pagination)) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->_total, $this->_limitstart, $this->_limit);
		}
	}
	
	public function rsm_parseMessageContent($args) {
		if (!$this->canRun()) 
			return;
		
		$message = $args['message'];
		
		$pattern = '#\[article(.*?):([0-9]+)\]#is';
		preg_match_all($pattern,$message,$matches);
		
		if (!empty($matches) && !empty($matches[1]))
			foreach ($matches[1] as $i => $article) {
				$this->_db->setQuery("SELECT CONCAT(c.`introtext`,c.`fulltext`) as content , c.title, c.created , u.username FROM #__content c LEFT JOIN #__users u ON u.id = c.created_by WHERE c.id = '".$matches[2][$i]."'");
				$details = $this->_db->loadObject();
				
				if (strpos($matches[1][$i],'title') !== false)
					$message = str_replace($matches[0][$i],$details->title,$message);
				
				if (strpos($matches[1][$i],'text') !== false)
					$message = str_replace($matches[0][$i],$details->content,$message);
				
				if (strpos($matches[1][$i],'date') !== false)
					$message = str_replace($matches[0][$i],$details->created,$message);
				
				if (strpos($matches[1][$i],'user') !== false)
					$message = str_replace($matches[0][$i],$details->username,$message);
			}
		
		$args['message'] = $message;
	}
}