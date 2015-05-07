<?php
/**
 * @package     gantry
 * @subpackage  features
 * @version		3.2.13 December 1, 2011
 * @author		RocketTheme http://www.rockettheme.com
 * @copyright 	Copyright (C) 2007 - 2011 RocketTheme, LLC
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
 
class GantryFeatureCopyright extends GantryFeature {
    var $_feature_name = 'copyright';
	
	function render($position="") {
	global $gantry;
	
		$app = JFactory::getApplication();
		$sitename = $app->getCfg( 'sitename' );
	
			ob_start();	?>
			
			<div class="clear"></div>
			<div class="rt-block">
				<p class="copyright"><?php echo $sitename. ' &copy; '. date('Y') . $this->get('text'); ?> &bull; <a href="index.php/privacy-policy"><?php echo JText::_('TPL_PRIVACY'); ?></a></p>
			</div>
			<?php
		return ob_get_clean();
	}
}