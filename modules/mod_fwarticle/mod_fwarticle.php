<?php
defined('_JEXEC') or die;

	
$com_path = JPATH_SITE.'/components/com_content/';
require_once $com_path.'router.php';
require_once $com_path.'helpers/route.php';
JModelLegacy::addIncludePath($com_path . '/models', 'ContentModel');


$article_id = $params->get('article_id', 0);

$DB = &JFactory::getDBO();
$query = "SELECT c.*,cat.alias as category_alias 
FROM #__content c 
left join #__categories cat on c.catid = cat.id 
where c.state =1 AND c.id in ({$article_id}) order by ordering asc LIMIT 1";

$DB->setQuery($query);
$item = $DB->loadObject();

if($item ==null)
	return;
$ItemId  = $params->get('ItemId', 0);	
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
require JModuleHelper::getLayoutPath('mod_fwarticle', $params->get('layout', 'default'));

