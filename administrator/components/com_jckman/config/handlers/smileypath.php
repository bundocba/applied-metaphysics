<?php

defined('JPATH_PLATFORM') or die;

class JCKConfigHandlerSmileyPath 
{

	function getOptions($key,$value,$default,$node,$params)
	{
		$options = '';
				  
		if($value)
			$value = str_replace('/administrator','',JURI::base(true)).'/'.$value;		  
				  
	   	$options .= "\"$key='".$value."'\",";   
		
		return $options;
	}
}



















