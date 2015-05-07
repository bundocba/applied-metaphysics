<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2010 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

function RSMailBuildRoute(&$query) {	
	$segments = array();
	
	if (!empty($query['view']))
		switch ($query['view']) {
			case 'history':
			
				if (!empty($query['layout']))
				switch($query['layout'])
				{
					case 'message':
						$segments[] = 'message';
						$segments[] = @$query['cid'];
					break;
				}
			break;
			
			case 'unsubscribe':
				$segments[] = 'unsubscribe';
			break;
			
			case 'details':
				$segments[] = 'details';
			break;
			
		}
	
	unset($query['layout'], $query['view'], $query['cid']);
	return $segments;
}

function RSMailParseRoute($segments) {	
	$query = array();
	$segments[0] = str_replace(':', '-', $segments[0]);
	
	switch ($segments[0]) {
		case 'message':
			$query['view'] = 'history';
			$query['layout'] = 'message';
			$query['cid'] = @$segments[1];
		break;
		
		case 'unsubscribe':
			$query['view'] = 'unsubscribe';
		break;
		
		case 'details':
			$query['view'] = 'details';
		break;
	}
	
	return $query;
}