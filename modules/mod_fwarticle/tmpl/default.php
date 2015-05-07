<?php
defined('_JEXEC') or die;

	
?>
<div class='fw_article <?php echo $moduleclass_sfx; ?>'>
	<?php
	
	$images = json_decode($item->images); 
	$item->slug = $item->id.':'.$item->alias;
	$item->catslug = $item->catid ? $item->catid .':'.$item->category_alias : $item->catid;
	$item->link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug).'&Itemid='.$ItemId);
	$photo = JURI::root().$images->image_intro;
	
	?>
		<div class='item'>
			<div class='photo'><img src='<?php echo $photo; ?>' /></div>
			<div class='intro_text'><?php echo $item->introtext; ?></div>
			<div class='readmore'><a class='k2ReadMore' href='<?php echo $item->link; ?>'>Read More</a> </div>
		</div>

	<div class='clear'></div>
</div>