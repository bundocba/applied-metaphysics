<?php
/**
 * @version		$Id: category.php 785 2011-04-28 12:39:17Z lefteris.kavadas $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.gr
 * @copyright	Copyright (c) 2006 - 2011 JoomlaWorks, a business unit of Nuevvo Webware Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
$K2ModelItem = new K2ModelItem;
$K2ModelItemlist = new K2ModelItemlist;
$categoryData = $K2ModelItemlist->getData();
foreach ($categoryData as $catItem) {
	$catItems[] = $catItem->id;
}

$K2ModelItem = new K2ModelItem;

foreach ($catItems as $catItem) {
	$catTags[] = $K2ModelItem->getItemTags($catItem);
}

foreach ($catTags as $catTag) {
	foreach ($catTag as $tag) {
		$allTags[] = $tag->name;
	}
}
$tags = array_unique($allTags);
?>

<!-- Start K2 Category Layout -->
<div class="TagCloudBlock">
	<div id="portfoliosorting">
    	<div class="sortbytype">
        <?php
			foreach ($tags as $tag) {
				echo "<a href=\"#\" id=\"".strtolower(str_replace(" ","_",$tag))."\">".$tag."</a>";
			}
        ?>
        <a href="#" id="all" class="active_sort">All</a>
        </div>
    </div>
</div>
<div id="k2Container container" class="itemListView portfolio<?php if($this->params->get('pageclass_sfx')) echo ' '.$this->params->get('pageclass_sfx'); ?>">

<!-- Page title -->
	<?php if($this->params->get('show_page_title')): ?>
		<div class="componentheading <?php echo $this->params->get('pageclass_sfx')?>">
			<h2><?php echo $this->escape($this->params->get('page_title')); ?></h2>
		</div>
	<?php endif; ?>

	<?php if($this->params->get('catFeedLink')): ?>
	<!-- RSS feed icon -->
	<div class="k2FeedIcon">
		<a href="<?php echo $this->feed; ?>" title="<?php echo JText::_('K2_SUBSCRIBE_TO_THIS_RSS_FEED'); ?>">
			<span><?php echo JText::_('K2_SUBSCRIBE_TO_THIS_RSS_FEED'); ?></span>
		</a>
		<div class="clr"></div>
	</div>
	<?php endif; ?>

	<?php if(isset($this->category) || ( $this->params->get('subCategories') && isset($this->subCategories) && count($this->subCategories) )): ?>
	<!-- Blocks for current category and subcategories -->
	<div class="itemListCategoriesBlock">

		<?php if(isset($this->category) && ( $this->params->get('catImage') || $this->params->get('catTitle') || $this->params->get('catDescription') || $this->category->event->K2CategoryDisplay )): ?>
		<!-- Category block -->
		<div class="itemListCategory">

			<?php if(isset($this->addLink)): ?>
			<!-- Item add link -->
			<span class="catItemAddLink">
				<a class="modal" rel="{handler:'iframe',size:{x:990,y:650}}" href="<?php echo $this->addLink; ?>">
					<?php echo JText::_('K2_ADD_A_NEW_ITEM_IN_THIS_CATEGORY'); ?>
				</a>
			</span>
			<?php endif; ?>

			<?php if($this->params->get('catImage') && $this->category->image): ?>
			<!-- Category image -->
			<img alt="<?php echo $this->category->name; ?>" src="<?php echo $this->category->image; ?>" style="width:<?php echo $this->params->get('catImageWidth'); ?>px; height:auto;" />
			<?php endif; ?>

			<?php if($this->params->get('catTitle')): ?>
			<!-- Category title -->
			<h2><?php echo $this->category->name; ?><?php if($this->params->get('catTitleItemCounter')) echo ' ('.$this->pagination->total.')'; ?></h2>
			<?php endif; ?>

			<?php if($this->params->get('catDescription')): ?>
			<!-- Category description -->
			<p><?php echo $this->category->description; ?></p>
			<?php endif; ?>

			<!-- K2 Plugins: K2CategoryDisplay -->
			<?php echo $this->category->event->K2CategoryDisplay; ?>

			<div class="clr"></div>
		</div>
		<?php endif; ?>

		<?php if($this->params->get('subCategories') && isset($this->subCategories) && count($this->subCategories)): ?>
		<!-- Subcategories -->
		<div class="itemListSubCategories">
			<h3><?php echo JText::_('K2_CHILDREN_CATEGORIES'); ?></h3>

			<?php foreach($this->subCategories as $key=>$subCategory): ?>

			<?php
			// Define a CSS class for the last container on each row
			if( (($key+1)%($this->params->get('subCatColumns'))==0) || count($this->subCategories)<$this->params->get('subCatColumns') )
				$lastContainer= ' subCategoryContainerLast';
			else
				$lastContainer='';
			?>

			<div class="subCategoryContainer<?php echo $lastContainer; ?>"<?php echo (count($this->subCategories)==1) ? '' : ' style="width:'.number_format(100/$this->params->get('subCatColumns'), 1).'%;"'; ?>>
				<div class="subCategory">
					<?php if($this->params->get('subCatImage') && $subCategory->image): ?>
					<!-- Subcategory image -->
					<a class="subCategoryImage" href="<?php echo $subCategory->link; ?>">
						<img alt="<?php echo $subCategory->name; ?>" src="<?php echo $subCategory->image; ?>" />
					</a>
					<?php endif; ?>

					<?php if($this->params->get('subCatTitle')): ?>
					<!-- Subcategory title -->
					<h2>
						<a href="<?php echo $subCategory->link; ?>">
							<?php echo $subCategory->name; ?><?php if($this->params->get('subCatTitleItemCounter')) echo ' ('.$subCategory->numOfItems.')'; ?>
						</a>
					</h2>
					<?php endif; ?>

					<?php if($this->params->get('subCatDescription')): ?>
					<!-- Subcategory description -->
					<p><?php echo $subCategory->description; ?></p>
					<?php endif; ?>

					<!-- Subcategory more... -->
					<a class="subCategoryMore" href="<?php echo $subCategory->link; ?>">
						<?php echo JText::_('K2_VIEW_ITEMS'); ?>
					</a>

					<div class="clr"></div>
				</div>
			</div>
			<?php if(($key+1)%($this->params->get('subCatColumns'))==0): ?>
			<div class="clr"></div>
			<?php endif; ?>
			<?php endforeach; ?>

			<div class="clr"></div>
		</div>
		<?php endif; ?>

	</div>
	<?php endif; ?>


	<?php if((isset($this->leading) || isset($this->primary) || isset($this->secondary) || isset($this->links)) && (count($this->leading) || count($this->primary) || count($this->secondary) || count($this->links))): ?>
	<!-- Item list -->
	<div class="itemList portfolio-content">
    	<?php $i = 0; ?>

		<?php if(isset($this->leading) && count($this->leading)): 
		$column_number = $this->params->get('num_leading_columns');	// get columns number
		?>
		<!-- Leading items -->
        
		<div id="itemListLeading">
			<?php 
			$item_number = count($this->leading);						// get items number  
			$item_width = (940/$column_number);	// get item width  
			foreach($this->leading as $key=>$item): 
				$i++;
			?>
            <?php if($key%$column_number==0): ?>
            	<div class="portfolio-row">
            <?php endif; ?>
			<?php

			$tag_name = "";
			foreach ($K2ModelItem->getItemTags($this->leading[$key]->id) as $itm=>$tag_item) {
				$tName = str_replace(" ", "_", $tag_item->name);
				$tag_name[] = strtolower($tName);
			}
			$classes = implode(" ", $tag_name);
			
			 ?>
			<?php
			// Define a CSS class for the last container on each row
			if( (($key+1)%($this->params->get('num_leading_columns'))==0) || count($this->leading)<$this->params->get('num_leading_columns') )
				$lastContainer= ' itemContainerLast';
			else
				$lastContainer='';
			?>

			<div class="<?php echo $classes ?> all one_third itemContainer rows_<?php echo $column_number ?> size_<?php echo number_format($item_width, 0) ?><?php echo $lastContainer; ?>">
				<?php
					// Load category_item.php by default
					$this->item=$item;
					echo $this->loadTemplate('item');
				?>
			</div>
			<?php if ( (($key+1)%$column_number==0) || (($key+1)==$item_number) ) : ?>
            	</div>
            <?php endif; ?>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<?php if(isset($this->primary) && count($this->primary)): 
		$column_number = $this->params->get('num_primary_columns');	// get columns number  
		?>
		<!-- Primary items -->
		<div id="itemListPrimary">
			<?php 
			$item_number = count($this->primary);						// get items number  
			$item_width = (940/$column_number);	// get item width  
			foreach($this->primary as $key=>$item): 
			$i++;
			?>
			<?php if($key%$column_number==0): ?>
            	<div class="portfolio-row">
            <?php endif; ?>
			<?php															
			// Define a CSS class for the last container on each row
			if( (($key+1)%($this->params->get('num_primary_columns'))==0) || count($this->primary)<$column_number )
				$lastContainer= ' itemContainerLast';
			else
				$lastContainer='';
			?>
			<?php 
/*-----------*/			
			$tag_name = "";
			foreach ($K2ModelItem->getItemTags($this->primary[$key]->id) as $itm=>$tag_item) {
				$tName = str_replace(" ", "_", $tag_item->name);
				$tag_name[] = strtolower($tName);
			}
			$classes = implode(" ", $tag_name);
			 ?>
			<div class="one_third id-<?php echo $i ?> all <?php echo $classes ?> rows_<?php echo $column_number ?> itemContainer<?php echo $lastContainer; ?> size_<?php echo number_format($item_width, 0) ?>">
				<?php
					// Load category_item.php by default
					$this->item=$item;
					echo $this->loadTemplate('item');
				?>
			</div>
			<?php  /*if(($key+1)%($this->params->get('num_primary_columns'))==0): ?>
			<div class="clr"></div>
			<?php endif;*/?>
            <?php if ( (($key+1)%$column_number==0) || (($key+1)==$item_number) ) : ?>
            	</div>
            <?php endif; ?>
			<?php endforeach; ?>
			<div class="clr"></div>
		</div>
		<?php endif; ?>

		<?php if(isset($this->secondary) && count($this->secondary)): 
		$column_number = $this->params->get('num_secondary_columns');	// get columns number
		?>
		<!-- Secondary items -->
		<div id="itemListSecondary" class="rows_<?php echo $column_number ?>">
			<?php 
			$item_number = count($this->secondary);						// get items number  
			$item_width = (940/$column_number);	// get item width  
			foreach($this->secondary as $key=>$item): 
			$i++;
			?>
            <?php if($key%$column_number==0): ?>
            	<div class="portfolio-row rows_<?php echo $column_number ?>">
            <?php endif; ?>
			<?php
			$tag_name = "";
			foreach ($K2ModelItem->getItemTags($this->secondary[$key]->id) as $itm=>$tag_item) { 
				$tName = str_replace(" ", "_", $tag_item->name);
				$tag_name[] = strtolower($tName);
			}
			$classes = implode(" ", $tag_name);
			 ?>
			<?php
			// Define a CSS class for the last container on each row
			if( (($key+1)%($this->params->get('num_secondary_columns'))==0) || count($this->secondary)<$this->params->get('num_secondary_columns') )
				$lastContainer= ' itemContainerLast';
			else
				$lastContainer='';
			?>
			
			<div class="id-<?php echo $i ?> all one_third <?php echo $classes ?> rows_<?php echo $column_number ?> itemContainer<?php echo $lastContainer; ?> size_<?php echo number_format($item_width, 0) ?>">
				<?php
					// Load category_item.php by default
					$this->item=$item;
					echo $this->loadTemplate('item');
				?>
			</div>
			<?php if ( (($key+1)%$column_number==0) || (($key+1)==$item_number) ) : ?>
            	</div>
            <?php endif; ?>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<?php if(isset($this->links) && count($this->links)): ?>
		<!-- Link items -->
		<div id="itemListLinks">
			<h4><?php echo JText::_('K2_MORE'); ?></h4>
			<?php foreach($this->links as $key=>$item): ?>

			<?php
			// Define a CSS class for the last container on each row
			if( (($key+1)%($this->params->get('num_links_columns'))==0) || count($this->links)<$this->params->get('num_links_columns') )
				$lastContainer= ' itemContainerLast';
			else
				$lastContainer='';
			?>

			<div class="itemContainer<?php echo $lastContainer; ?>"<?php echo (count($this->links)==1) ? '' : ' style="width:'.number_format(100/$this->params->get('num_links_columns'), 1).'%;"'; ?>>
				<?php
					// Load category_item_links.php by default
					$this->item=$item;
					echo $this->loadTemplate('item_links');
				?>
			</div>
			<?php if(($key+1)%($this->params->get('num_links_columns'))==0): ?>
			<div class="clr"></div>
			<?php endif; ?>
			<?php endforeach; ?>
			<div class="clr"></div>
		</div>
		<?php endif; ?>

	</div>

	

	<?php endif; ?>
</div>
<div class="clear"></div>
<!-- Pagination -->
	<?php if(count($this->pagination->getPagesLinks())): ?>
	<div class="k2Pagination">
		<?php if($this->params->get('catPagination')) echo $this->pagination->getPagesLinks(); ?>
		<div class="clr"></div>
		<?php if($this->params->get('catPaginationResults')) echo $this->pagination->getPagesCounter(); ?>
	</div>
	<?php endif; ?>
<!-- End K2 Category Layout -->
