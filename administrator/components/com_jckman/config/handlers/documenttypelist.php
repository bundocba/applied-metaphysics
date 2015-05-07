<?php

defined('JPATH_PLATFORM') or die;

class JCKConfigHandlerDocumentTypeList
{

	function getOptions($key,$value,$default,$node,$params)
	{
		$options = '';
		$value = preg_replace('/"/','\"',$value);
  	   	$options .= "\"$key='".$value."'\"\"";   
		return $options;
	}
    
}



















