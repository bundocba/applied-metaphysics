<?php
/**
 * @version		$Id: default.php 834 2011-06-20 07:36:03Z joomlaworks $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.gr
 * @copyright	Copyright (c) 2006 - 2011 JoomlaWorks, a business unit of Nuevvo Webware Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

/*style="width: ?>"
// no direct access */
defined('_JEXEC') or die('Restricted access');
global $gpath;
$document =& JFactory::getDocument();
?>
<div id="k2ModuleBox<?php echo $module->id; ?>">

<?php if(count($items)): ?>
    <div id="slider-container">
		<ul id="slider" class="nivoSlider">
    	<?php foreach ($items as $key=>$item): ?>

    		<li class="<?php echo "counter".$key; if(count($items)==$key+1) echo ' lastItem'; ?>">
				<div class="inner">
		
					<?php if($params->get('itemImage') && isset($item->image)): ?>
						<a class="moduleItemImage" href="<?php echo $item->link; ?>" title="<?php echo JText::_('K2_CONTINUE_READING'); ?> &quot;
							<?php echo K2HelperUtilities::cleanHtml($item->title); ?>&quot;">
							<img src="<?php echo $item->image; ?>" alt="<?php echo $item->title; ?>" title="#<?php echo "counter".$key; ?>"/>
						</a>
					<?php endif; ?>
				</div>
			</li>
		<?php endforeach; ?>
		</ul>
	</div>

	<?php foreach ($items as $key=>$item): ?>
		<div class="nivo-html-caption" id="<?php echo "counter".$key; ?>">
            
    		<!-- Plugins: BeforeDisplay -->
     		<?php echo $item->event->BeforeDisplay; ?>

    		<!-- K2 Plugins: K2BeforeDisplay -->
    		<?php echo $item->event->K2BeforeDisplay; ?>

    		<?php if($params->get('itemAuthorAvatar')): ?>
    			<a class="k2Avatar moduleItemAuthorAvatar" rel="author" href="<?php echo $item->authorLink; ?>">
				<img src="<?php echo $item->authorAvatar; ?>" alt="<?php echo $item->author; ?>" style="width:<?php echo $avatarWidth; ?>px;height:auto;" />
				</a>
    		<?php endif; ?>

   			<?php if($params->get('itemTitle')): ?>
    			<span class="moduleItemTitle"><?php echo $item->title; ?></span>
    		<?php endif; ?>

    		<?php if($params->get('itemAuthor')): ?>
    			<div class="moduleItemAuthor">
	      			<?php echo K2HelperUtilities::writtenBy($item->authorGender); ?>
                    
					<?php if(isset($item->authorLink)): ?>
						<a rel="author" href="<?php echo $item->authorLink; ?>"><?php echo $item->author; ?></a>
					<?php else: ?>
						<?php echo $item->author; ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>

    		<!-- Plugins: AfterDisplayTitle -->
    		<?php echo $item->event->AfterDisplayTitle; ?>

    		<!-- K2 Plugins: K2AfterDisplayTitle -->
    		<?php echo $item->event->K2AfterDisplayTitle; ?>

    		<!-- Plugins: BeforeDisplayContent -->
    		<?php echo $item->event->BeforeDisplayContent; ?>

    		<!-- K2 Plugins: K2BeforeDisplayContent -->
    		<?php echo $item->event->K2BeforeDisplayContent; ?>

    		<?php if($params->get('itemIntroText')): ?>
      			<div class="intro_space"><?php echo $item->introtext; ?></div>
   			<?php endif; ?>

    		<?php if($params->get('itemExtraFields') && count($item->extra_fields)): ?>
      			<div class="moduleItemExtraFields">
	      			<b><?php echo JText::_('K2_ADDITIONAL_INFO'); ?></b>
	      				<ul>
	        			<?php foreach ($item->extra_fields as $extraField): ?>
							<li class="type<?php echo ucfirst($extraField->type); ?> group<?php echo $extraField->group; ?>">
								<span class="moduleItemExtraFieldsLabel"><?php echo $extraField->name; ?></span>
								<span class="moduleItemExtraFieldsValue">
									<?php echo ($extraField->type=='date')?JHTML::_('date', $extraField->value, JText::_('K2_DATE_FORMAT_LC')):$extraField->value; ?>
                                </span>
								<div class="clr"></div>
							</li>
	        			<?php endforeach; ?>
	      				</ul>
      			</div>
   			<?php endif; ?>

    		<div class="clr"></div>

    		<?php if($params->get('itemVideo')): ?>
      			<div class="moduleItemVideo">
      				<?php echo $item->video ;?>
      				<span class="moduleItemVideoCaption"><?php echo $item->video_caption ;?></span>
      				<span class="moduleItemVideoCredits"><?php echo $item->video_credits ;?></span>
      			</div>
    		<?php endif; ?>

    		<div class="clr"></div>

    		<!-- Plugins: AfterDisplayContent -->
    		<?php echo $item->event->AfterDisplayContent; ?>

    		<!-- K2 Plugins: K2AfterDisplayContent -->
    		<?php echo $item->event->K2AfterDisplayContent; ?>

    		<?php if($params->get('itemDateCreated')): ?>
    			<span class="moduleItemDateCreated"><?php echo JText::_('K2_WRITTEN_ON') ;?> <?php echo JHTML::_('date', $item->created, JText::_('K2_DATE_FORMAT_LC2')); ?></span>
    		<?php endif; ?>

   			<?php if($params->get('itemCategory')): ?>
    			<?php echo JText::_('K2_IN') ;?> <a class="moduleItemCategory" href="<?php echo $item->categoryLink; ?>"><?php echo $item->categoryname; ?></a>
    		<?php endif; ?>

    		<?php if($params->get('itemTags') && count($item->tags)>0): ?>
      			<div class="moduleItemTags">
      				<b><?php echo JText::_('K2_TAGS'); ?>:</b>
        			<?php foreach ($item->tags as $tag): ?>
        				<a href="<?php echo $tag->link; ?>"><?php echo $tag->name; ?></a>
        			<?php endforeach; ?>
     			</div>
    		<?php endif; ?>

    		<?php if($params->get('itemAttachments') && count($item->attachments)): ?>
				<div class="moduleAttachments">
					<?php foreach ($item->attachments as $attachment): ?>
					<a title="<?php echo $attachment->titleAttribute; ?>" href="<?php echo JRoute::_('index.php?option=com_k2&view=item&task=download&id='.$attachment->id); ?>">
						<?php echo $attachment->title ; ?>
                    </a>
					<?php endforeach;?>
				</div>
   			<?php endif; ?>

			<?php if($params->get('itemCommentsCounter') && $componentParams->get('comments')): ?>		
				<?php if(!empty($item->event->K2CommentsCounter)):?>
					<!-- K2 Plugins: K2CommentsCounter -->
					<?php echo $item->event->K2CommentsCounter; ?>
				<?php else:?>
					<?php if($item->numOfComments>0): ?>
						<a class="moduleItemComments" href="<?php echo $item->link.'#itemCommentsAnchor'; ?>">
							<?php echo $item->numOfComments; ?> <?php if($item->numOfComments>1) echo JText::_('K2_COMMENTS'); else echo JText::_('K2_COMMENT'); ?>
						</a>
					<?php else: ?>
						<a class="moduleItemComments" href="<?php echo $item->link.'#itemCommentsAnchor'; ?>">
							<?php echo JText::_('K2_BE_THE_FIRST_TO_COMMENT'); ?>
						</a>
					<?php endif; ?>
				<?php endif; ?>
			<?php endif; ?>

			<?php if($params->get('itemHits')): ?>
				<span class="moduleItemHits">
					<?php echo JText::_('K2_READ'); ?> <?php echo $item->hits; ?> <?php echo JText::_('K2_TIMES'); ?>
				</span>
			<?php endif; ?>

			
			<?php if($params->get('itemReadMore') && $item->fulltext): ?>
				<div class="moduleItemReadMore-wrapper">
				<a class="moduleItemReadMore" href="<?php echo $item->link; ?>">
					<?php echo JText::_('K2_READ_MORE_SLIDER_THEME'); ?>
					<!-- Read more slider button translation can be found in the '/languages/en-GB/en-GB.tpl_theme###.ini' file -->
				</a>
				</div>
			<?php endif; ?>
			

    		<!-- Plugins: AfterDisplay -->
    		<?php echo $item->event->AfterDisplay; ?>

    		<!-- K2 Plugins: K2AfterDisplay -->
    		<?php echo $item->event->K2AfterDisplay; ?>
	
    	</div>
		<?php endforeach; ?>  
  <?php endif; ?>
</div>

<?php
$document->addStyleSheet($gpath."/css/nivo-slider.css");
$document->addScript($gpath."/js/jquery.nivo.slider.js");
?>

<script type="text/javascript">
(function($){ 
   $(window).load(function(){

	var $slider_ul_id = $('#slider'); //Please define slider items list ID

	$slider_ul_id.nivoSlider({
		effect: 'random',
		slices: 1,
		boxCols: 8,
		boxRows: 4,
		animSpeed: 500,
		pauseTime: 7000,
		startSlide: 0,
		directionNav: false,
		directionNavHide: false,
		controlNav: true,
		controlNavThumbs: false,
        controlNavThumbsFromRel: false,
		controlNavThumbsSearch: '.jpg',
		controlNavThumbsReplace: '_thumb.jpg',
		keyboardNav: false,
		pauseOnHover: true,
		manualAdvance: false,
		captionOpacity: 1,
		prevText: 'Prev',
		nextText: 'Next',
		beforeChange: function(){},
		afterChange: function(){},
		slideshowEnd: function(){},
        lastSlide: function(){},
        afterLoad: function(){}
	});
});
})(jQuery);     </script>