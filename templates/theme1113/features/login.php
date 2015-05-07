<?php
/**
 * @package     gantry
 * @subpackage  features
 * @version    ${project.version} ${build_date}
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright   Copyright (C) 2007 - ${copyright_year} RocketTheme, LLC
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */

defined('JPATH_BASE') or die();

gantry_import('core.gantryfeature');

class GantryFeatureLogin extends GantryFeature {
    var $_feature_name = 'login';

  function render($position="") {
      ob_start();
      ?>
    <div class="clear"></div>
    <div class="rt-block">
    <div id="rt-login-button">
      <a href="#" title="Login Form" class="buttontext" rel="rokbox[215 320][module=rt-popup]">
        <span><?php echo $this->get('text'); ?></span>
      </a>
    </div><div class="clear"></div>
    </div>
    <?php
      return ob_get_clean();
  }
}
