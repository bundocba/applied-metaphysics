<?php
/**
 * @package     gantry
 * @subpackage  features
 * @version		3.2.16 February 8, 2012
 * @author		RocketTheme http://www.rockettheme.com
 * @copyright 	Copyright (C) 2007 - 2012 RocketTheme, LLC
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */

defined('JPATH_BASE') or die();

gantry_import('core.gantryfeature');

/**
 * @package     gantry
 * @subpackage  features
 */
class GantryFeatureStyleDeclaration extends GantryFeature {
    var $_feature_name = 'styledeclaration';

    function isEnabled() {
        global $gantry;
        $menu_enabled = $this->get('enabled');

        if (1 == (int)$menu_enabled) return true;
        return false;
    }

	function init() {
        global $gantry;

        //inline css for dynamic stuff
		
		$css = "";
		
	    $css  = 'body {background:'.$gantry->get('bgcolor').';}';
			
		//Top row color	
	    $css .= '#rt-top {background:'.$gantry->get('top_row-row').';}';
	    $css .= '#rt-top .rt-container {background:'.$gantry->get('top_row-container').';}';
		
		//Header row color
	    $css .= '#rt-header {background:'.$gantry->get('header_row-row').';}';
	    $css .= '#rt-header .rt-container {background:'.$gantry->get('header_row-container').';}';
		
		//Showcase row color
	    $css .= '#rt-showcase {background:'.$gantry->get('showcase_row-row').';}';
	    $css .= '#rt-showcase .rt-container {background:'.$gantry->get('showcase_row-container').';}';
		
		//Feature row color
	    $css .= '#rt-feature {background:'.$gantry->get('feature_row-row').';}';
	    $css .= '#rt-feature .rt-container {background:'.$gantry->get('feature_row-container').';}';
		
		//Utility row color
	    $css .= '#rt-utility {background:'.$gantry->get('utility_row-row').';}';
	    $css .= '#rt-utility .rt-container {background:'.$gantry->get('utility_row-container').';}';
		
		//Maintop row color
	    $css .= '#rt-maintop {background:'.$gantry->get('maintop_row-row').';}';
	    $css .= '#rt-maintop .rt-container {background:'.$gantry->get('maintop_row-container').';}';
		
		//Main row color
	    $css .= '#rt-main {background:'.$gantry->get('main_row-row').';}';
	    $css .= '#rt-main .rt-container {background:'.$gantry->get('main_row-container').';}';
		
		//Mainbottom row color
	    $css .= '#rt-mainbottom {background:'.$gantry->get('mainbottom_row-row').';}';
	    $css .= '#rt-mainbottom .rt-container {background:'.$gantry->get('mainbottom_row-container').';}';
		
		//Bottom row color
	    $css .= '#rt-bottom {background:'.$gantry->get('bottom_row-row').';}';
	    $css .= '#rt-bottom .rt-container {background:'.$gantry->get('bottom_row-container').';}';
		
		//Footer row color
	    $css .= '#footer {background:'.$gantry->get('footer_row-row').';}';
	    $css .= '#footer .rt-container {background:'.$gantry->get('footer_row-container').';}';
		
		
			//Link style
       		$css .= 'body a {color:'.$gantry->get('linkcolor-default').';}';
       		$css .= 'body a:hover {color:'.$gantry->get('linkcolor-hover').'; }';
			
			//Readmore style
       		$css .= 'a.moduleItemReadMore, a.k2ReadMore {color:'.$gantry->get('more-default').'; background:'.$gantry->get('more-bg').';}';
       		$css .= 'a.moduleItemReadMore:hover, a.k2ReadMore:hover {color:'.$gantry->get('more-hover').';background:'.$gantry->get('more-bg_hover').';}';
			
			//Button style
       		$css .= 'div.itemCommentsForm form input#submitCommentButton, input[type="submit"], button.button,.component-content div.toggle-editor a {color:'.$gantry->get('button-default').'; background:'.$gantry->get('button-bg').';}';
       		$css .= 'div.itemCommentsForm form input#submitCommentButton:hover, input[type="submit"]:hover, button.button:hover,.component-content div.toggle-editor a:hover {color:'.$gantry->get('button-hover').';background:'.$gantry->get('button-bg_hover').';}';

			
		//Top menu style
			
		if($gantry->get('top_menu-enabled') == '1'){
       		$css .= '.menutop li.root > .item {color:'.$gantry->get('top_menu-topMenuLink').'; background:'.$gantry->get('top_menu-topMenuLinkBg').';}';
			$css .= '.menutop li.root > .item:hover, .menutop li.root.active > .item, .menutop li.root.f-mainparent-itemfocus > .item {
					 color:'.$gantry->get('top_menu-topMenuLink_hover').'; background:'.$gantry->get('top_menu-topMenuLink_hoverBg').';}';
		};
		
		if($gantry->get('top_menu_sub-enabled') == '1'){
       		$css .= '.menutop ul {background:'.$gantry->get('top_menu_sub-topMenu_sublevel').';}';
       		$css .= '.menutop ul li > .item {color:'.$gantry->get('top_menu_sub-topMenuLink').'; background:'.$gantry->get('top_menu_sub-topMenuLinkBg').';}';
			$css .= '.menutop ul li > .item:hover, .menutop ul li.active > .item, .menutop ul li.f-menuparent-itemfocus > .item{
					 color:'.$gantry->get('top_menu_sub-topMenuLink_hover').'; background:'.$gantry->get('top_menu_sub-topMenuLink_hoverBg').';}';
		};
		
		//Color sets
			
		
		$css .= $gantry->get('colorset_1-text').'{background:'.$gantry->get('colorset_1-color').';}';
		$css .= $gantry->get('colorset_2-text').'{background:'.$gantry->get('colorset_2-color').';}';
		$css .= $gantry->get('colorset_3-text').'{background:'.$gantry->get('colorset_3-color').';}';
		
		//Default font style
		
		if($gantry->get('default_font-enabled') == '1'){
		
		$font = $gantry->get('default_font-font_family');
		$font_family="";

		switch($font){
		 	case 'arial':
				$font_family = 'Arial, Helvetica, sans-serif'; 	
				break;		
		 	case 'arial_black':
				$font_family = 'Arial Black, Gadget, sans-serif'; 
				break;	
		 	case 'courier':
				$font_family = 'Courier New, monospace'; 	
				break;		
		 	case 'georgia':
				$font_family = 'Georgia, serif'; 
				break;			
		 	case 'impact':
				$font_family = 'Impact, Charcoal, sans-serif'; 
				break;			
		 	case 'lucida_cons':
				$font_family = 'Monaco, monospace';
				break; 			
		 	case 'lucida_sans':
				$font_family = 'Lucida Grande, sans-serif'; 
				break;			
		 	case 'palatino':
				$font_family = 'Palatino Linotype, Book Antiqua, Palatino, serif'; 	
				break;		
		 	case 'tahoma':
				$font_family = 'Tahoma, Geneva, sans-serif'; 
				break;			
		 	case 'times':
				$font_family = 'Times New Roman, Times, serif'; 
				break;			
		 	case 'trebuchet':
				$font_family = 'Trebuchet MS, sans-serif'; 
				break;			
		 	case 'verdana':
				$font_family = 'Verdana, Geneva, sans-serif'; 
				break;			
		 	case 'ms':
				$font_family = 'MS Serif, New York, serif'; 
				break;			
		 	case 'ms_sans':
				$font_family = 'MS Sans Serif, Geneva, sans-serif'; 
				break;			
		};
						
			$css .= 'body {font-family:'.$font_family.'; font-size:'.$gantry->get('default_font-font_size').'; line-height:'.$gantry->get('default_font-line_height').'; color:'.$gantry->get('default_font-color').'; }';
		}
		
		
        $gantry->addInlineStyle($css);

        //style stuff
        $gantry->addStyle($gantry->get('cssstyle').".css");
	}

}