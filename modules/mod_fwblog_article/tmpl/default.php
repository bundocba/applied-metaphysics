<?php
defined('_JEXEC') or die;

	
?>
<div class='news'>
	<div class='fwblog_article k2ItemsBlock'>
		<ul>
		<?php
			$num = count($items);

			foreach($items as $key=>$item): 
			
			$images = json_decode($item->images); 
			$item->slug = $item->id.':'.$item->alias;
			$item->catslug = $item->catid ? $item->catid .':'.$item->category_alias : $item->catid;
			$item->link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug).'&Itemid='.$ItemId);
			$photo = JURI::root().$images->image_intro;
			$class = ($key&1) ? 'odd ' : 'even ';
			$class.= ($key ==($num-1)) ? 'lastItem' : (($key == 0) ? 'firstItem' : '');
			?>
			
			<li  class='item <?php echo $class; ?>'>
				<a class="moduleItemImage" href="<?php echo $item->link; ?>">
					<img src="<?php echo $photo; ?>" />
				</a>
				<div class='extra-wrap'>
					<a class="moduleItemTitle" href="<?php echo $item->link; ?>"><?php echo $item->title; ?></a>
					<span class="moduleItemDateCreated"><?php echo JHtml::_('date', $item->created, 'F d, Y'); ?></span>
					<div class="moduleItemIntrotext">
						<?php echo strip_tags($item->introtext); ?>

						<a class="moduleItemReadMore" href="<?php echo $item->link; ?>">More</a>
					  </div>
				</div>
			</li>
			
		<?php 
			endforeach; ?>
			</ul>
		<div class='clear'></div>
	</div>
</div>