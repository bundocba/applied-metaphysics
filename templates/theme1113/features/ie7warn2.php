<?php
defined('JPATH_BASE') or die();

gantry_import('core.gantryfeature');
/**
 * @package     gantry
 * @subpackage  features
 */
 
class GantryFeatureIE7Warn2 extends GantryFeature {
    var $_feature_name = 'ie7warn2';
	
	function render($position="") {
			global $gantry;

			ob_start();	
			
			if ($gantry->browser->name == 'ie' && $gantry->browser->shortversion == '7') {
				if ($this->get('enabled')) { ?>
			
				  <div style=' clear: both; text-align:center; position: relative; height:42px;'>
					<a href="http://windows.microsoft.com/en-US/internet-explorer/products/ie/home?ocid=ie6_countdown_bannercode">
					  <img src="http://storage.ie6countdown.com/assets/100/images/banners/warning_bar_0000_us.jpg" border="0" height="42" width="820" alt="You are using an outdated browser. For a faster, safer browsing experience, upgrade for free today." />
				   </a>
				 </div>
		 
			<?php }
			}
			
		return ob_get_clean();
	}
}