<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die('Restricted access');

if (!$this->is_logged && !$this->vid && !$this->cid)
	echo $this->loadTemplate('form');
else
	echo $this->loadTemplate('lists');