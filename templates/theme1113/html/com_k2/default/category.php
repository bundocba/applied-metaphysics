<?php
/**
 * @version		$Id: category.php 1273 2011-10-27 16:12:32Z lefteris.kavadas $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.gr
 * @copyright	Copyright (c) 2006 - 2011 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

$num_leading_columns = $this->params->get('num_leading_columns');
$num_primary_columns = $this->params->get('num_primary_columns');
$num_secondary_columns = $this->params->get('num_secondary_columns');
$num_links_columns = $this->params->get('num_links_columns');

?>

<!-- Start K2 Category Layout -->
<div id="k2Container" class="itemListView<?php if($this->params->get('pageclass_sfx')) echo ' '.$this->params->get('pageclass_sfx'); ?>">

<!-- Page title -->
	<?php if($this->params->get('show_page_title')): ?>
		<div class="componentheading <?php echo $this->params->get('pageclass_sfx')?>">
			<h2><?php echo $this->escape($this->params->get('page_title')); ?></h2>
		</div>
	<?php endif; ?>
	
<!-- RSS feed icon -->
	<?php if($this->params->get('catFeedIcon')): ?>
		<div class="k2FeedIcon">
			<a href="<?php echo $this->feed; ?>" title="<?php echo JText::_('K2_SUBSCRIBE_TO_THIS_RSS_FEED'); ?>">
				<span><?php echo JText::_('K2_SUBSCRIBE_TO_THIS_RSS_FEED'); ?></span>
			</a>
			<div class="clr"></div>
		</div>
	<?php endif; ?>

<!-- Blocks for current category and subcategories -->
	<?php if(isset($this->category) || ( $this->params->get('subCategories') && isset($this->subCategories) && count($this->subCategories) )): ?>
		<div class="itemListCategoriesBlock">
		
	<!-- Category block -->
			<?php if(isset($this->category) && ( $this->params->get('catImage') || $this->params->get('catTitle') || $this->params->get('catDescription') || $this->category->event->K2CategoryDisplay )): ?>
	
				<div class="itemListCategory">

			<!-- Item add link -->
					<?php if(isset($this->addLink)): ?>
						<span class="catItemAddLink">
							<a class="modal" rel="{handler:'iframe',size:{x:990,y:650}}" href="<?php echo $this->addLink; ?>">
								<?php echo JText::_('K2_ADD_A_NEW_ITEM_IN_THIS_CATEGORY'); ?>
							</a>
						</span>
					<?php endif; ?>
					
			<!-- Category image -->
					<?php if($this->params->get('catImage') && $this->category->image): ?>
						<img alt="<?php echo K2HelperUtilities::cleanHtml($this->category->name); ?>" src="<?php echo $this->category->image; ?>" style="width:<?php echo $this->params->get('catImageWidth'); ?>px; height:auto;" />
					<?php endif; ?>
					
			<!-- Category title -->
					<?php if($this->params->get('catTitle')): ?>
						<h2><?php echo $this->category->name; ?><?php if($this->params->get('catTitleItemCounter')) echo ' ('.$this->pagination->total.')'; ?></h2>
					<?php endif; ?>
					
			<!-- Category description -->
					<?php if($this->params->get('catDescription')): ?>
						<p><?php echo $this->category->description; ?></p>
					<?php endif; ?>

					<!-- K2 Plugins: K2CategoryDisplay -->
					<?php echo $this->category->event->K2CategoryDisplay; ?>

					<div class="clr"></div>
				</div>
			<?php endif; ?>
			

	<!-- Subcategories -->
			<?php if($this->params->get('subCategories') && isset($this->subCategories) && count($this->subCategories)): ?>
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
									<img alt="<?php echo K2HelperUtilities::cleanHtml($subCategory->name); ?>" src="<?php echo $subCategory->image; ?>" />
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


<!-- Item list -->
	<?php if((isset($this->leading) || isset($this->primary) || isset($this->secondary) || isset($this->links)) && (count($this->leading) || count($this->primary) || count($this->secondary) || count($this->links))): ?>
	
		<div class="itemList">
		

			<?php if(isset($this->leading) && count($this->leading)): 
				$column_number = $this->params->get('num_leading_columns');	// get columns number
			?>
	<!-- Leading items -->
			<div id="itemListLeading">
				<?php 
				$item_number = count($this->leading);	
				foreach($this->leading as $key=>$item): ?>

				<?php
				// Define a CSS class for the last container on each row
				if( (($key+1)%($this->params->get('num_leading_columns'))==0) || count($this->leading) < $this->params->get('num_leading_columns') )
					$lastContainer= ' itemContainerLast';
				else
					$lastContainer='';
				?>
				
				<?php if($key%$column_number==0): ?>
					<div class="K2ItemsRow">
				<?php endif; ?>

				
				<div class="itemContainer<?php echo $lastContainer; ?>"<?php echo (count($this->leading)==1) ? '' : ' style="width:'.number_format(100/$this->params->get('num_leading_columns'), 1).'%;"'; ?>>
					<?php
						// Load category_item.php by default
						$this->item=$item;
						echo $this->loadTemplate('item');
					?>
					<div class="clr"></div>
				</div>
				<?php if ( (($key+1)%$column_number==0) || (($key+1)==$item_number) ) : ?>
				<div class="clr"></div>
					</div>
				<?php endif; ?>
				<?php endforeach; ?>
			</div>
			<div class="clr"></div>
			<?php endif; ?>

			
			<?php if(isset($this->primary) && count($this->primary)): 
			$column_number = $this->params->get('num_primary_columns');	// get columns number  
			?>
            
	<!-- Primary items -->
			<div id="itemListPrimary">
				<?php 
				$item_number = count($this->primary);						// get items number  
				foreach($this->primary as $key=>$item): 
				?>
				
				<?php
				// Define a CSS class for the last container on each row
				if( (($key+1)%($this->params->get('num_primary_columns'))==0) || count($this->primary)<$this->params->get('num_primary_columns') )
					$lastContainer= ' itemContainerLast';
				else
					$lastContainer='';
				?>
                
				<?php if($key%$column_number==0): ?>
					<div class="K2ItemsRow">
				<?php endif; ?>
				
				<div class="itemContainer<?php echo $lastContainer; ?>"<?php echo (count($this->primary)==1) ? '' : ' style="width:'.number_format(100/$this->params->get('num_primary_columns'), 1).'%;"'; ?>>
					<?php
						// Load category_item.php by default
						$this->item=$item;
						echo $this->loadTemplate('item');
					?>
					<div class="clr"></div>
				</div>
			
				<?php if ( (($key+1)%$column_number==0) || (($key+1)==$item_number) ) : ?>
				<div class="clr"></div>
					</div>
				<?php endif; ?>
                
				<?php endforeach; ?>
			</div>
			<div class="clr"></div>
			<?php endif; ?>
			

			<?php if(isset($this->secondary) && count($this->secondary)): 
			$column_number = $this->params->get('num_secondary_columns');	// get columns number?>
	<!-- Secondary items -->
			<div id="itemListSecondary">
				<?php 
				$item_number = count($this->secondary);						// get items number  
				foreach($this->secondary as $key=>$item): ?>
				
				<?php
				// Define a CSS class for the last container on each row
				if( (($key+1)%($this->params->get('num_secondary_columns'))==0) || count($this->secondary)<$this->params->get('num_secondary_columns') )
					$lastContainer= ' itemContainerLast';
				else
					$lastContainer='';
				?>
				
				<?php if($key%$column_number==0): ?>
					<div class="K2ItemsRow">
				<?php endif; ?>

				
				<div class="itemContainer<?php echo $lastContainer; ?>"<?php echo (count($this->secondary)==1) ? '' : ' style="width:'.number_format(100/$this->params->get('num_secondary_columns'), 1).'%;"'; ?>>
					<?php
						// Load category_item.php by default
						$this->item=$item;
						echo $this->loadTemplate('item');
					?>
					<div class="clr"></div>
				</div>
				
				<?php if ( (($key+1)%$column_number==0) || (($key+1)==$item_number) ) : ?>
				<div class="clr"></div>
					</div>
				<?php endif; ?>

				<?php endforeach; ?>
			</div>
			<div class="clr"></div>
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
					<div class="clr"></div>
				</div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
<div class="clr"></div>
		</div>
		

		<!-- Pagination -->
		<?php if($this->params->get('catPagination') || $this->params->get('catPaginationResults')): ?>
			<div class="k2Pagination">
				<?php if($this->params->get('catPagination')) echo $this->pagination->getPagesLinks(); ?>
				<div class="clr"></div>
				<?php if($this->params->get('catPaginationResults')): ?>
					<p class="pagination-results"><?php if($this->params->get('catPaginationResults')) echo $this->pagination->getPagesCounter(); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

	<?php endif; ?>
</div>
<!-- End K2 Category Layout -->
