<?php
/**
**/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

jimport( 'joomla.event.event' );

/**
 * JCKToolbarsControllerListener extends JEvent
 *
 */
 
class JCKToolbarsControllerListener extends JEvent
{
	/**
	 * A JParameter object holding the parameters for the plugin
	 *
	 * @var		A JParameter object
	 * @access	public
	 * @since	1.5
	 */
	
	function onCopy($cid)
	{
		// get details
		
		$db = JFactory::getDBO();
		
		$query = 'SELECT id,name,title' 
		. ' FROM #__jcktoolbars' 
		. ' WHERE id IN ('. implode($cid) .')';
		$db->setQuery( $query );
		$toolbars = $db->loadObjectList();
		
		$this->_createEditorToolbarOption($toolbars);
	}
	
 
	function onSave($id,$name,$oldname,$title,$isNew)
	{
		$toolbar = new stdclass;
		$toolbar->id = $id;
		$toolbar->oldname = $oldname;
		$toolbar->name = $name;
		$toolbar->title = $title;
		if(!$isNew)
		{
			$this->_createEditorToolbar($id,$name,$oldname);
			$this->_updateEditorToolbarOption(array($toolbar));
		}	
		else
		{
			$this->_createEditorToolbarOption(array($toolbar));
		}	
	 }
	 
	function onRemove($names)
	{
		//delete toolbar files
		$paths = array_map(create_function('$name','return CKEDITOR_LIBRARY.DS."toolbar".DS.$name.".php";'),$names);
		//JFile::delete($paths);
		//update Editor Manifest
		$this->_deleteEditorToolbarOption($names);
	}
		
		
		
	function _createEditorToolbarOption($toolbars)
	{
	   // get editor installfile
		$JCKManifestFile = JPATH_PLUGINS . DS . 'editors' . DS . 'jckeditor' .DS . 'jckeditor.xml';
		
		
		$jckeditorXML = JFactory::getXMLParser('Simple');
		if(!$jckeditorXML->loadFile($JCKManifestFile)) 
		{
			JError::raiseWarning(100, 'Editor Install: '.JText::_('Could not load manifest file for JoomlaFCK editor'));
			return;
		}
 
 		$JCKManifest = $jckeditorXML->document;
 
 		$paramsElement = $JCKManifest->config[0]->fields[0]->fieldset[0];
		
			
		foreach($toolbars as $toolbar)
		{
			
			$this->_createEditorToolbar($toolbar->id,$toolbar->name,$toolbar->name); //Write to toolbar file 
			foreach ($paramsElement->children() as $param)
			{
		
				if($param->attributes('name') == 'toolbar')
				{
					$child = $param->AddChild('option',array('value' => $toolbar->name));
					$child->setData($toolbar->title);
				}
				if($param->attributes('name') == 'toolbar_ft')
				{
					$child = $param->AddChild('option',array('value' => $toolbar->name));
					$child->setData($toolbar->title);
					break;
				}
				
			
			}
			
		}
		
		$JCKOutputXMl = $JCKManifest->toString();
		
		if(!JFile::write($JCKManifestFile,$JCKOutputXMl)) //Write to editor manifest file 
		{
			JError::raiseWarning(100, 'Toolbar Copy: '.JText::_('Could not write to manifest file for JoomlaCK editor'));
		}
		

	}
	
	
	function _deleteEditorToolbarOption($names)
	{
	   // get editor installfile
		$JCKManifestFile = JPATH_PLUGINS . DS . 'editors' . DS . 'jckeditor' . DS . 'jckeditor.xml';
		
		$jckeditorXML = JFactory::getXMLParser('Simple');
		if(!$jckeditorXML->loadFile($JCKManifestFile)) 
		{
			JError::raiseWarning(100, 'Editor Install: '.JText::_('Could not load manifest file for JoomlaFCK editor'));
			return;
		}
 
 		$JCKManifest = $jckeditorXML->document;
		
		$paramsElement = $JCKManifest->config[0]->fields[0]->fieldset[0];
		
		foreach($names as $name)
		{
			foreach ($paramsElement->children() as $param)
			{
				if($param->attributes('name') == 'toolbar')
				{
					foreach($param->children() as $child)
					{
					   if($child->attributes('value') == $name)
					   {
							$param->removeChild($child); 
							break;
						}
					}
				}
				if($param->attributes('name') == 'toolbar_ft')
				{
					foreach($param->children() as $child)
					{
					   if($child->attributes('value') == $name)
					   {
							$param->removeChild($child); 
							break;
						}
					}
					break;
				}
			}
		
		}
		
		$JCKOutputXMl = $JCKManifest->toString();
	
		if(!JFile::write($JCKManifestFile,$JCKOutputXMl)) //Write to editor manifest file 
		{
			JError::raiseWarning(100, 'Toolbar Delete: '.JText::_('Could not write to manifest file for JoomlaCK editor'));
		}

	}
	function _updateEditorToolbarOption($toolbars)
	{
	   // get editor installfile
		$JCKManifestFile = JPATH_PLUGINS . DS . 'editors' . DS . 'jckeditor' . DS . 'jckeditor.xml';
		
		$jckeditorXML = JFactory::getXMLParser('Simple');
		if(!$jckeditorXML->loadFile($JCKManifestFile)) 
		{
			JError::raiseWarning(100, 'Editor Install: '.JText::_('Could not load manifest file for JoomlaFCK editor'));
			return;
		}
 
 		$JCKManifest = $jckeditorXML->document;
 
 		$paramsElement = $JCKManifest->config[0]->fields[0]->fieldset[0];
		
		foreach($toolbars as $toolbar)
		{
			foreach ($paramsElement->children() as $param)
			{
				if($param->attributes('name') == 'toolbar')
				{
					foreach($param->children() as $child)
					{
					   if($child->attributes('value') == $toolbar->oldname)
					   {
							$child->removeAttribute('value');
							$child->addAttribute('value',$toolbar->name);
							$child->setData($toolbar->title); 
							break;
						}
					}
				}
				if($param->attributes('name') == 'toolbar_ft')
				{
					foreach($param->children() as $child)
					{
					   if($child->attributes('value') == $toolbar->oldname)
					   {
							$child->removeAttribute('value');
							$child->addAttribute('value',$toolbar->name);
							$child->setData($toolbar->title); 
							break;
						}
					}
					break;
				}
			}
		
		}
		
		$JCKOutputXMl = $JCKManifest->toString();
	
		if(!JFile::write($JCKManifestFile,$JCKOutputXMl)) //Write to editor manifest file 
		{
			JError::raiseWarning(100, 'Toolbar Update: '.JText::_('Could not write to manifest file for JoomlaCK editor'));
		}

	}
	
	function _createEditorToolbar($id,$name,$oldname)
	 {
		
			
		require_once(CKEDITOR_LIBRARY.DS . 'toolbar.php');
		
		$CKfolder =  CKEDITOR_LIBRARY.DS . 'toolbar'; 
		
		$newfilename = $CKfolder.DS.$name.'.php';
		$oldfilename = $CKfolder.DS.$oldname.'.php';
			
		$classname = 'JCK'. ucfirst($name);
		$toolbar = new stdclass;		
		
		
				
		$toolbarConfig = new JRegistry('toolbar');
		
		$db = JFactory::getDBO();
		
		$query = 'SELECT tp.pluginid AS id,p.title,tp.row'
		. ' FROM #__jcktoolbarplugins tp'
		. ' LEFT JOIN #__jckplugins p ON p.id = tp.pluginid'
		. ' WHERE tp.state = 1'
		. ' AND tp.toolbarid = '.$id
		. ' ORDER BY tp.row ASC,tp.ordering ASC';
		$db->setQuery( $query );
		$toolbarplugins = $db->loadObjectList();
		
				
		if($toolbarplugins) 
		{
			
			
			
			
			foreach($toolbarplugins as $plugin)
			{
				if($plugin->id < 0) // we have a break
				{
					$property = 'brk_' .($plugin->id*-1);
					$toolbar->$property = $plugin->row;
				}
				else
				{
					$property = $plugin->title;
					$toolbar->$property = $plugin->row;
				}
			
			}
		}	
			
		$toolbarConfig->loadObject($toolbar);
		
		// Get the config registry in PHP class format and write it to file
		$buffer = $toolbarConfig->toString('PHP', array('class' => $classname . ' extends JCKToolbar'));
		
		if (!JFile::write($oldfilename,$buffer)) { 	  
			JError::raiseWarning(100,'Failed to write to file for the ' . $classname .' toolbar');
		} 	

		if($newfilename != $oldfilename)
		{
			if( !JFile::move($oldfilename, $newfilename) ){
				JError::raiseWarning(100,'Failed to write to file for the ' . $classname .' toolbar');
			}
		}
	
	 }
	 
	 function onApply($id,$name,$oldname,$title,$isNew)
	{
	 	 $this->onSave($id,$name,$oldname,$title,$isNew);
	}
	 
}
