<?php

defined('JPATH_PLATFORM') or die;

class JCKConfigHandlerWindowFeaturesList
{

	function getOptions($key,$value,$default,$node,$params)
	{
		
        $options = '';
		
        if(is_array($value))
		{  
            $value = implode(",",$value);
		} 
        elseif($value &&  preg_match('/^\[.*\]$/',$value))
        {
            
            $value = str_replace('\'','"',$value);
            $value = json_decode($value); 
            $value =  implode("",$value);
        }
  	   	$options .= "\"$key='".$value."'\"\"";   
     
		return $options;
	}
    
}



















