<?php
/**
* @package Gantry Template Framework - RocketTheme
* @version 3.2.8 August 1, 2011
* @author RocketTheme http://www.rockettheme.com
* @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*
* Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
*
*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted index access' );

// load and inititialize gantry class

require_once('lib/gantry/gantry.php');

$class="";
$menu = JSite::getMenu();
if ($menu->getActive() == $menu->getDefault()) {
  $class="homepage";
}

global $gpath;
$gpath = $this->baseurl."/templates/".$this->template;

$menus = &JSite::getMenu();
$menu_array = $menus->getActive();

$pageID   = "";

if (is_object( $menu_array )) :
  $params = new JParameter( $menu_array->params );
  $cleanPID = $pageID = $params->get( 'pageclass_sfx' );
endif;

if ($pageID != "") {
  $pageID = "id=\"".$pageID."\"";
} else {
  $pageID = "id=\"all\"";
}
  
$doc = &JFactory::getDocument();
$title = $doc->getTitle();
$title = str_replace('#',' ',$title);
$doc->setTitle($title);
  
  $view = JRequest::getVar('view'); /*Current page view. K2 related pages*/
  $option = $_GET['option']; /*Current page view. Joomla related pages*/
    $task = '';
    $task =& $_GET['task'];
    if($task === 'date'){$pageID="id=\"date\"";};

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $gantry->language; ?>" lang="<?php echo $gantry->language;?>" >
    <head>
  
        <?php
            $gantry->displayHead();
            $gantry->addStyles(array(
        'template.css',
        'tooltips.css',
        'prettyPhoto.css',
        'framework-reset.css',
        'http://fonts.googleapis.com/css?family=Open+Sans',
        'fwz.css'
      ));
      
      $gantry->addScript(array(
        'https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js'
      )); ?>
      
      <script type="text/javascript">
        $.noConflict();
      </script>
      
      <?php 
      $gantry->addScript(array(
        'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js',
        'jQuery.ScrollTo.js',
        'tooltips.js',
        'jquery.tipsy.js',
        'jquery.prettyPhoto.js',
        'scripts.js'
      ));
      if (isset($cleanPID) && ($cleanPID == "port")) {
        $gantry->addScript(array(
          'SmoothBoosh.js'
        ));
      }
    ?>
    </head>
  
  <body <?php echo $gantry->displayBodyTag();  echo ' ' . $pageID; ?>>
      <div id="rt-iewarn">
        <div class="rt-container">
          <?php echo $gantry->displayModules('iewarn','standard','standard'); ?>
          <div class="clear"></div>
        </div>
      </div>
  
    <div class="wrapper<?php echo ' '. $class; ?>">
      <?php /** Begin Drawer **/ if ($gantry->countModules('drawer')) : ?>
      <div id="rt-drawer">
        <div class="rt-container">
          <?php echo $gantry->displayModules('drawer','standard','standard'); ?>
          <div class="clear"></div>
        </div>
      </div>
      <?php /** End Drawer **/ endif; ?>
      <?php /** Begin Top **/ if ($gantry->countModules('top')) : ?>
      <div id="rt-top" <?php echo $gantry->displayClassesByTag('rt-top'); ?>>
        <div class="rt-container">
          <?php echo $gantry->displayModules('top','standard','standard'); ?>
          <div class="clear"></div>
        </div>
      </div>
      <?php /** End Top **/ endif; ?>
      <?php /** Begin Header **/ if ($gantry->countModules('header')) : ?>
      <div id="rt-header">
        <div class="rt-container">
          <?php echo $gantry->displayModules('header','standard','standard'); ?>
          <div class="clear"></div>
        </div>
      </div>
      <?php /** End Header **/ endif; ?>
      <?php /** Begin slider **/ if ($this->countModules('Upcoming_activity')): ?>
      <div id="rt-slider">
        <div class="rt-container">
          <jdoc:include type="modules" name="Upcoming_activity" style="xhtml"/>
          <div class="clear"></div>
        </div>
      </div>
      <?php /** End slider **/ endif; ?>
      <?php /** Begin Menu **/ if ($gantry->countModules('navigation')) : ?>
      <div id="rt-menu">
        <div class="rt-container">
          <?php echo $gantry->displayModules('navigation','basic','basic'); ?>
          <div class="clear"></div>
        </div>
      </div>
      <?php /** End Menu **/ endif; ?>
      <?php /** Begin Showcase **/ if ($gantry->countModules('showcase') && ($option !== 'com_search') && ($view !== 'item')) : ?>
      <div id="rt-showcase">
        <div class="rt-container <?php echo $class; ?>">
          <?php echo $gantry->displayModules('showcase','standard','standard'); ?>
          <div class="clear"></div>
        </div>
      </div>
      <?php /** End Showcase **/ endif; ?>
      <?php /** Begin Feature **/ if ($gantry->countModules('feature') && ($option !== 'com_search') && ($view !== 'item')) : ?>
      <div id="rt-feature">
        <div class="rt-container">
          <?php echo $gantry->displayModules('feature','standard','standard'); ?>
          <div class="clear"></div>
        </div>
      </div>
      <?php /** End Feature **/ endif; ?>
      <?php /** Begin Utility **/ if ($gantry->countModules('utility') && ($option !== 'com_search') && ($view !== 'item')) : ?>
      <div id="rt-utility">
        <div class="rt-container">
          <?php echo $gantry->displayModules('utility','standard','basic'); ?>
          <div class="clear"></div>
        </div>
      </div>
      <?php /** End Utility **/ endif; ?>
      <?php /** Begin Breadcrumbs **/ if ($gantry->countModules('breadcrumb')) : ?>
      <div id="rt-breadcrumbs">
        <div class="rt-container">
          <?php echo $gantry->displayModules('breadcrumb','standard','standard'); ?>
          <div class="clear"></div>
        </div>
      </div>
      <?php /** End Breadcrumbs **/ endif; ?>
      <?php /** Begin Main Top **/ if ($gantry->countModules('maintop') && ($option !== 'com_search') && ($view !== 'item') && ($task !== 'date')) : ?>
      <div id="rt-maintop">
        <div class="rt-container">
          <?php echo $gantry->displayModules('maintop','standard','standard'); ?>
          <div class="clear"></div>
        </div>
      </div>
      <?php /** End Main Top **/ endif; ?>
      <?php /** Begin Main Body **/ ?>
      <?php echo $gantry->displayMainbody('mainbody','sidebar','standard','standard','standard','standard','standard'); ?>
      <?php /** End Main Body **/ ?>
      <?php /** Begin Main Bottom **/ if ($gantry->countModules('mainbottom') && ($option !== 'com_search') && ($view !== 'item') && ($task !== 'user') && ($task !== 'date')) : ?>
      <div id="rt-mainbottom">
        <div class="rt-container">
          <?php echo $gantry->displayModules('mainbottom','standard','standard'); ?>
          <div class="clear"></div>
        </div>
      </div>
      <?php /** End Main Bottom **/ endif; ?>
      <?php /** Begin Bottom **/ if ($gantry->countModules('bottom') && ($option !== 'com_search') && ($view !== 'item') && ($task !== 'date')) : ?>
      <div id="rt-bottom">
        <div class="rt-container">
          <?php echo $gantry->displayModules('bottom','standard','standard'); ?>
          <div class="clear"></div>
        </div>
      </div>
      <?php /** End Bottom **/ endif; ?>
    
    <div id="footer">
        <?php /** Begin Footer **/ if ($gantry->countModules('footer')) : ?>
        <div id="rt-footer">
          <div class="rt-container">
            <?php echo $gantry->displayModules('footer','standard','standard'); ?>
            <div class="clear"></div>
          </div>
        </div>
        <?php /** End Footer **/ endif; ?>
        <div class="fwclear"></div>
        <?php /** Begin Copyright **/ if ($gantry->countModules('copyright')) : ?>
        <div id="rt-copyright">
          <div class="rt-container">
            <?php echo $gantry->displayModules('copyright','standard','standard'); ?>
            <div class="fwclear"></div>
          </div>
        </div>
        <?php /** End Copyright **/ endif; ?>
        <?php /** Begin Debug **/ if ($gantry->countModules('debug')) : ?>
        <div id="rt-debug">
          <div class="rt-container">
            <?php echo $gantry->displayModules('debug','standard','standard'); ?>
            <div class="clear"></div>
          </div>
        </div>
        <?php /** End Debug **/ endif; ?>
        <?php /** Begin Analytics **/ if ($gantry->countModules('analytics')) : ?>
        <?php echo $gantry->displayModules('analytics','basic','basic'); ?>
        <?php /** End Analytics **/ endif; ?>
        <?php /** Begin Popup **/ 
        echo $gantry->displayModules('popup','popup','standard'); 
        /** End Popup **/ ?>
    </div>
    </div>
  </body>
</html>
<?php
$gantry->finalize();
?>