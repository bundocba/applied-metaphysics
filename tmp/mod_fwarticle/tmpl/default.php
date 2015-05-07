<?php
defined('_JEXEC') or die;

	
?>
<div class='fw_articles'>
	<?php
		$num = count($items);

		foreach($items as $key=>$item): 
		
		$images = json_decode($item->images); 
		$item->slug = $item->id.':'.$item->alias;
		$item->catslug = $item->catid ? $item->catid .':'.$item->category_alias : $item->catid;
		$item->link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug));
		$photo = JURI::root().$images->image_intro;
		
		$class = ($key ==($num-1)) ? 'last' : '';
		?>
			<div class='item <?php echo $class; ?>'>
				<div class='title'><?php echo $item->title; ?></div>
				<div class='photo'><img src='<?php echo $photo; ?>' /></div>
				<div class='intro_text'><?php echo substr(strip_tags($item->introtext),0,200)."..."; ?></div>
				<div class='readmore'><a href='<?php echo $item->link; ?>'>Learn More</a> </div>
			</div>
	<?php 
		endforeach; ?>
	<div class='clear'></div>
</div>