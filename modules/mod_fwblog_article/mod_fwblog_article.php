<?php
defined('_JEXEC') or die;

	
$com_path = JPATH_SITE.'/components/com_content/';
require_once $com_path.'router.php';
require_once $com_path.'helpers/route.php';
JModelLegacy::addIncludePath($com_path . '/models', 'ContentModel');


$catid = $params->get('catid', 0);

$DB = &JFactory::getDBO();
$query = "SELECT c.*,cat.alias as category_alias 
FROM #__content c 
left join #__categories cat on c.catid = cat.id 
where c.state =1 AND cat.id = ".$catid." order by c.created desc";

$DB->setQuery($query);
$items = $DB->loadObjectList();

if($items ==null)
	return;
	
$ItemId  = $params->get('ItemId', 0);
require JModuleHelper::getLayoutPath('mod_fwblog_article', $params->get('layout', 'default'));

