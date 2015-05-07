<?php

defined('JPATH_PLATFORM') or die;

class JCKConfigHandlerResizeRadio 
{
	function getOptions($key,$value,$default,$node,$params,$pluginName)
	{
		
		if(!isset($value))
			$value = $default;
		

		$value = (int) $value;

		if($value)
		   $value = 'true';
		else
		  $value = 'false';   
		$options .= "\"$key=".$value."\",";  
		
		return $options;
	}
}



















