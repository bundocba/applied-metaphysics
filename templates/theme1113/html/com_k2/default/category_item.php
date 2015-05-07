<?php
/**
 * @version		$Id: category_item.php 1251 2011-10-19 17:50:13Z joomlaworks $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.gr
 * @copyright	Copyright (c) 2006 - 2011 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Define default image size (do not change)
K2HelperUtilities::setDefaultImage($this->item, 'itemlist', $this->params);

?>

<!-- Start K2 Item Layout -->
<div class="catItemView group<?php echo ucfirst($this->item->itemGroup); ?><?php /*echo ($this->item->featured) ? ' catItemIsFeatured' : '';*/ ?><?php if($this->item->params->get('pageclass_sfx')) echo ' '.$this->item->params->get('pageclass_sfx'); ?>">

	<!-- Plugins: BeforeDisplay -->
	<?php echo $this->item->event->BeforeDisplay; ?>

	<!-- K2 Plugins: K2BeforeDisplay -->
	<?php echo $this->item->event->K2BeforeDisplay; ?>
	
	
<!--Item Header - START -->
	<div class="catItemHeader">
	
		
<!-- Item title -->
		<?php if($this->item->params->get('catItemTitle')): ?>
		
				<?php if(isset($this->item->editLink)): ?>
					<!-- Item edit link -->
					<span class="catItemEditLink">
						<a class="modal" rel="{handler:'iframe',size:{x:990,y:550}}" href="<?php echo $this->item->editLink; ?>">
							<?php echo JText::_('K2_EDIT_ITEM'); ?>
						</a>
					</span>
				<?php endif; ?>
		
			<h3 class="catItemTitle">
				<?php if ($this->item->params->get('catItemTitleLinked')): ?>
						<a href="<?php echo $this->item->link; ?>">
							<?php echo $this->item->title; ?>
						</a>
					<?php else: ?>
				
					<?php echo $this->item->title; ?>
				
				<?php endif; ?>

				<?php if($this->item->params->get('catItemFeaturedNotice') && $this->item->featured): ?>
					<!-- Featured flag -->
					<span>
						<sup>
							<?php echo JText::_('K2_FEATURED'); ?>
						</sup>
					</span>
				<?php endif; ?>
			</h3>
		<?php endif; ?>

<!-- Item Author -->		
		<?php if($this->item->params->get('catItemAuthor')): ?>
			<span class="catItemAuthor">
				<?php echo K2HelperUtilities::writtenBy($this->item->author->profile->gender); ?> 
					<a rel="author" href="<?php echo $this->item->author->link; ?>">
						<?php echo $this->item->author->name; ?>
					</a>
			</span>
		<?php endif; ?>
		
<!-- Date created -->
		<?php if($this->item->params->get('catItemDateCreated')): ?>
			<span class="catItemDateCreated">
				<?php echo JHTML::_('date', $this->item->created , JText::_('K2_DATE_FORMAT_LC2')); ?>
			</span>
		<?php endif; ?>
	</div>
	
	
<!--Item Header - END -->


	<!-- Plugins: AfterDisplayTitle -->
	<?php echo $this->item->event->AfterDisplayTitle; ?>

	<!-- K2 Plugins: K2AfterDisplayTitle -->
	<?php echo $this->item->event->K2AfterDisplayTitle; ?>
	
	
<!-- Item Rating -->
	<?php if($this->item->params->get('catItemRating')): ?>
		<div class="catItemRatingBlock">
			<span><?php echo JText::_('K2_RATE_THIS_ITEM'); ?></span>
			<div class="itemRatingForm">
				<ul class="itemRatingList">
					<li class="itemCurrentRating" id="itemCurrentRating<?php echo $this->item->id; ?>" style="width:<?php echo $this->item->votingPercentage; ?>%;"></li>
					<li><a href="#" rel="<?php echo $this->item->id; ?>" title="<?php echo JText::_('K2_1_STAR_OUT_OF_5'); ?>" class="one-star">1</a></li>
					<li><a href="#" rel="<?php echo $this->item->id; ?>" title="<?php echo JText::_('K2_2_STARS_OUT_OF_5'); ?>" class="two-stars">2</a></li>
					<li><a href="#" rel="<?php echo $this->item->id; ?>" title="<?php echo JText::_('K2_3_STARS_OUT_OF_5'); ?>" class="three-stars">3</a></li>
					<li><a href="#" rel="<?php echo $this->item->id; ?>" title="<?php echo JText::_('K2_4_STARS_OUT_OF_5'); ?>" class="four-stars">4</a></li>
					<li><a href="#" rel="<?php echo $this->item->id; ?>" title="<?php echo JText::_('K2_5_STARS_OUT_OF_5'); ?>" class="five-stars">5</a></li>
				</ul>
				<div id="itemRatingLog<?php echo $this->item->id; ?>" class="itemRatingLog"><?php echo $this->item->numOfvotes; ?></div>
				<div class="clr"></div>
			</div>
			<div class="clr"></div>
		</div>
	<?php endif; ?>
	

<!--Item body - START -->
	<div class="catItemBody">

		<!-- Plugins: BeforeDisplayContent -->
		<?php echo $this->item->event->BeforeDisplayContent; ?>

		<!-- K2 Plugins: K2BeforeDisplayContent -->
		<?php echo $this->item->event->K2BeforeDisplayContent; ?>
		
		
<!-- Item Image -->
		<?php if($this->item->params->get('catItemImage') && !empty($this->item->image)): ?>
			<div class="catItemImageBlock">
				<span class="catItemImage">
				<a href="<?php echo $this->item->link; ?>" title="<?php if(!empty($this->item->image_caption)) echo K2HelperUtilities::cleanHtml($this->item->image_caption); else echo K2HelperUtilities::cleanHtml($this->item->title); ?>">
					<img src="<?php echo $this->item->image; ?>" alt="<?php if(!empty($this->item->image_caption)) echo K2HelperUtilities::cleanHtml($this->item->image_caption); else echo K2HelperUtilities::cleanHtml($this->item->title); ?>" style="width:<?php echo $this->item->imageWidth; ?>px; height:auto;" />
				</a>
				</span>
				<div class="clr"></div>
			</div>
		<?php endif; ?>

<!-- Item introtext -->		
		<?php if($this->item->params->get('catItemIntroText')): ?>
			<div class="catItemIntroText">
				<?php echo $this->item->introtext; ?>
			
					<!-- Item "read more..." link -->
					<?php if ($this->item->params->get('catItemReadMore')): ?>
					<div class="catItemReadMore">
						<a class="k2ReadMore" href="<?php echo $this->item->link; ?>">
							<?php echo JText::_('K2_READ_MORE_THEME'); ?>
						</a>
					</div>
					<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="clr"></div>

<!-- Item extra fields -->		
		<?php if($this->item->params->get('catItemExtraFields') && count($this->item->extra_fields)): ?>
			<div class="catItemExtraFields">
				<h4><?php echo JText::_('K2_ADDITIONAL_INFO'); ?></h4>
				<ul>
					<?php foreach ($this->item->extra_fields as $key=>$extraField): ?>
					<?php if($extraField->value): ?>
						<li class="<?php echo ($key%2) ? "odd" : "even"; ?> type<?php echo ucfirst($extraField->type); ?> group<?php echo $extraField->group; ?>">
							<span class="catItemExtraFieldsLabel"><?php echo $extraField->name; ?></span>
							<span class="catItemExtraFieldsValue"><?php echo $extraField->value; ?></span>
						</li>
					<?php endif; ?>
					<?php endforeach; ?>
				</ul>
			<div class="clr"></div>
			</div>
		<?php endif; ?>

		<!-- Plugins: AfterDisplayContent -->
		<?php echo $this->item->event->AfterDisplayContent; ?>

		<!-- K2 Plugins: K2AfterDisplayContent -->
		<?php echo $this->item->event->K2AfterDisplayContent; ?>

		<div class="clr"></div>
	</div>
<!--Item body - END -->



	<?php if(
	$this->item->params->get('catItemHits') ||
	$this->item->params->get('catItemCategory') ||
	$this->item->params->get('catItemTags') ||
	$this->item->params->get('catItemAttachments')
	): ?>

		<div class="catItemLinks">

<!-- Item Hits -->		
			<?php if($this->item->params->get('catItemHits')): ?>
				<div class="catItemHitsBlock">
					<span class="catItemHits">
						<?php echo JText::_('K2_READ'); ?> <b><?php echo $this->item->hits; ?></b> <?php echo JText::_('K2_TIMES'); ?>
					</span>
				</div>
			<?php endif; ?>

<!-- Item category name -->
			<?php if($this->item->params->get('catItemCategory')): ?>
				<div class="catItemCategory">
					<span><?php echo JText::_('K2_PUBLISHED_IN'); ?></span>
					<a href="<?php echo $this->item->category->link; ?>"><?php echo $this->item->category->name; ?></a>
				</div>
			<?php endif; ?>

<!-- Item tags -->
			<?php if($this->item->params->get('catItemTags') && count($this->item->tags)): ?>
				<div class="catItemTagsBlock">
					<span><?php echo JText::_('K2_TAGGED_UNDER'); ?></span>
					<ul class="catItemTags">
					<?php foreach ($this->item->tags as $tag): ?>
					<li><a href="<?php echo $tag->link; ?>"><?php echo $tag->name; ?></a></li>
					<?php endforeach; ?>
					</ul>
					<div class="clr"></div>
				</div>
			<?php endif; ?>

<!-- Item attachments -->
			<?php if($this->item->params->get('catItemAttachments') && count($this->item->attachments)): ?>
				<div class="catItemAttachmentsBlock">
					<span><?php echo JText::_('K2_DOWNLOAD_ATTACHMENTS'); ?></span>
					<ul class="catItemAttachments">
					<?php foreach ($this->item->attachments as $attachment): ?>
					<li>
						<a title="<?php echo K2HelperUtilities::cleanHtml($attachment->titleAttribute); ?>" href="<?php echo $attachment->link; ?>">
							<?php echo $attachment->title ; ?>
						</a>
						<?php if($this->item->params->get('catItemAttachmentsCounter')): ?>
							<span>(<?php echo $attachment->hits; ?> <?php echo ($attachment->hits==1) ? JText::_('K2_DOWNLOAD') : JText::_('K2_DOWNLOADS'); ?>)</span>
						<?php endif; ?>
					</li>
					<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<div class="clr"></div>
		</div>
	<?php endif; ?>

	<div class="clr"></div>

<!-- Item video -->	
	<?php if($this->item->params->get('catItemVideo') && !empty($this->item->video)): ?>

		<div class="catItemVideoBlock">
			<h3><?php echo JText::_('K2_RELATED_VIDEO'); ?></h3>
				<?php if($this->item->videoType=='embedded'): ?>
					<div class="catItemVideoEmbedded">
						<?php echo $this->item->video; ?>
					</div>
				<?php else: ?>
					<span class="catItemVideo"><?php echo $this->item->video; ?></span>
				<?php endif; ?>
		</div>
	<?php endif; ?>
	
<!-- Item image gallery -->
	<?php if($this->item->params->get('catItemImageGallery') && !empty($this->item->gallery)): ?>
		<div class="catItemImageGallery">
			<h4><?php echo JText::_('K2_IMAGE_GALLERY'); ?></h4>
			<?php echo $this->item->gallery; ?>
		</div>
	<?php endif; ?>

	<div class="clr"></div>

<!-- Anchor link to comments below -->	
	<?php if($this->item->params->get('catItemCommentsAnchor') && ( ($this->item->params->get('comments') == '2' && !$this->user->guest) || ($this->item->params->get('comments') == '1')) ): ?>
		<div class="catItemCommentsLink">
			<?php if(!empty($this->item->event->K2CommentsCounter)): ?>
				<!-- K2 Plugins: K2CommentsCounter -->
				<?php echo $this->item->event->K2CommentsCounter; ?>
			<?php else: ?>
				<?php if($this->item->numOfComments > 0): ?>
				<a href="<?php echo $this->item->link; ?>#itemCommentsAnchor">
					<?php echo $this->item->numOfComments; ?> <?php echo ($this->item->numOfComments>1) ? JText::_('K2_COMMENTS') : JText::_('K2_COMMENT'); ?>
				</a>
				<?php else: ?>
				<a href="<?php echo $this->item->link; ?>#itemCommentsAnchor">
					<?php echo JText::_('K2_BE_THE_FIRST_TO_COMMENT'); ?>
				</a>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="clr"></div>

<!-- Item date modified -->
	<?php if($this->item->params->get('catItemDateModified')): ?>
		<?php if($this->item->modified != $this->nullDate && $this->item->modified != $this->item->created ): ?>
			<span class="catItemDateModified">
				<?php echo JText::_('K2_LAST_MODIFIED_ON'); ?> <?php echo JHTML::_('date', $this->item->modified, JText::_('K2_DATE_FORMAT_LC2')); ?>
			</span>
		<?php endif; ?>
	<?php endif; ?>

	<!-- Plugins: AfterDisplay -->
	<?php echo $this->item->event->AfterDisplay; ?>

	<!-- K2 Plugins: K2AfterDisplay -->
	<?php echo $this->item->event->K2AfterDisplay; ?>

	<div class="clr"></div>
</div>
<!-- End K2 Item Layout -->
