<?php
/**
 * @package		JomSocial Facebook Stream
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license http://www.azrul.com Copyrighted Commercial Software
 */
defined('_JEXEC') or die('Restricted access');


$html  = '<iframe ';
$html .= 'src="http://www.facebook.com/plugins/likebox.php?';
$html .= 'id='. $params->get('facebookid', '29952106698');
$html .= '&width=' . $params->get('width', '300');
$html .= '&connections='. $params->get('connections', '10');
$html .= '&height=' . $params->get('height', '300');
$html .= '&header='. $params->get('showHeader', 'true');
$html .= '&stream=' . $params->get('showStream', 'true') . '"'; 
$html .= ' scrolling="no" frameborder="0" allowTransparency="true" ';
$html .= 'style="border:none; overflow:hidden; width:'. $params->get('width', '300') . 'px; height:'. $params->get('height', '300') . 'px">';
$html .= '</iframe>';

echo $html;
	
?>