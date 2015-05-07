<?php
/**
 * @package		VINAORA NIVO SLIDER
 * @subpackage	mod_vt_nivo_slider
 * @copyright	Copyright (C) 2011-2013 VINAORA. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @website		http://vinaora.com
 * @twitter		https://twitter.com/vinaora
 * @facebook	https://facebook.com/vinaora
 * @google+		https://plus.google.com/111142324019789502653
 */

// no direct access
defined('_JEXEC') or die;

class mod_vt_nivo_sliderInstallerScript
{
	/**
	 * Function to act prior to installation process begins
	 * 
	 * @param	(string)	$type	The action being performed
	 * @param	(string)	$parent	The function calling this method
	 * 
	 * @return	(mixed)		Boolean false on failure, void otherwise
	 * 
	 */
	function preflight($type, $parent)
	{
		// Installing extension manifest file version
		$this->release = $parent->get('manifest')->version;
		
		if (strtolower($type) == 'update')
		{
			$oldRelease = $this->getParam('version');
			
			if(version_compare($oldRelease, '2.5.30', 'lt'))
			{
				// Remove the directory 'fonts', 'helper'
				$folders[] = JPATH_ROOT . '/media/mod_vt_nivo_slider/fonts';
				$folders[] = JPATH_ROOT . '/modules/mod_vt_nivo_slider/helper';
				foreach($folders as $folder)
				{
					$folder = JPath::clean($folder);
					if( file_exists( $folder ) ) JFolder::delete( $folder );
				}
				unset($folders);
				
				// Remove the outdated jquery scripts from 1.0.x to 1.7.x'
				$folder = JPATH_ROOT . '/media/mod_vt_nivo_slider/js/jquery';
				$folder = JPath::clean($folder);
				$folders = JFolder::folders($folder, '^1\.[0-7]\.[0-9]$', false, true);
				foreach($folders as $folder)
				{
					JFolder::delete( $folder );
				}
				unset($folders);
				
				// Get old language files
				$files[] = JPATH_ROOT . '/language/en-GB/en-GB.mod_vt_nivo_slider.ini';
				$files[] = JPATH_ROOT . '/language/en-GB/en-GB.mod_vt_nivo_slider.sys.ini';
				// Get colorpicker files
				$files[] = JPATH_ROOT . '/modules/mod_vt_nivo_slider/fields/colorpicker.php';
				$files[] = JPATH_ROOT . '/media/mod_vt_nivo_slider/js/colorpicker-uncompressed.js';
				$files[] = JPATH_ROOT . '/media/mod_vt_nivo_slider/js/colorpicker.js';
				// Get other files
				$files[] = JPATH_ROOT . '/media/mod_vt_nivo_slider/intro.html';
				
				// Remove deprecated files
				foreach($files as $file)
				{
					$file = JPath::clean($file);
					if( file_exists( $file ) ) JFile::delete( $file );
				}
				unset($files);
				
				// Fix old prameters
				self::fixParams($oldRelease);
			}
			else
			{
				// Todo: Warning Version
			}
			
			
		}
		else
		{
			// Not Upgrade
		}
	}
	
	/**
	 * This function is run after the extension is registered in the database.
	 * 
	 * @param	(string)	$type	The action being performed
	 * @param	(string)	$parent	The function calling this method
	 * 
	 * @return	(mixed)		Boolean false on failure, void otherwise
	 * 
	 */
	function postflight( $type, $parent )
	{
		// $link = "index.php?option=com_modules&filter_module=mod_vt_nivo_slider";
	}
	
	/**
	 * Fix some parameters of older versions
	 * 
	 * @param	(string)	$oldRelease	The old version
	 * 
	 */
	function fixParams($oldRelease)
	{
		if(version_compare($oldRelease, '2.5.25', 'ge')) return;

		// Read the existing extension value(s)
		// Get a db connection.
		$db = JFactory::getDbo();
		
		// Create a new query object.
		$query = $db->getQuery(true);
		
		$query
			->select('params')
			->from('#__extensions')
			->where('type = \'module\' AND element = \'mod_vt_nivo_slider\'');
		
		$db->setQuery($query);
		
		// Get the parameters from JSON string
		$params = json_decode( $db->loadResult(), true );
		
		// Replace the parameter 'item_dir'
		$param	= trim($params['item_dir']);
		$param	= "images/$param";
		if(file_exists(JPATH_ROOT . "/$param"))
		{
			$params['item_dir'] = $param;
		}
		
		// Store the combined new and existing values back as a JSON string
		$paramsString = json_encode( $params );
		
		$query
			->update('#__extensions')
			->set('params = ' . $db->quote( $paramsString ))
			->where('type = \'module\' AND element = \'mod_vt_nivo_slider\'');
		$db->setQuery($query);
		$db->query();
	}
	
	/*
     * Get a variable from the manifest file (actually, from the manifest cache).
     */
    function getParam( $name )
	{
		// Get a db connection.
		$db = JFactory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);

		$query
			->select('manifest_cache')
			->from('#__extensions')
			->where('name = \'mod_vt_nivo_slider\'');

		$db->setQuery($query);

		// Get the parameters from JSON string
		$params = json_decode( $db->loadResult(), true );
		return $params[ $name ];
	}
}
