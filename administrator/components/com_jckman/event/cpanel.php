<?php
/**
**/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

jimport( 'joomla.event.event' );

/**
 * JCKListControllerListener extends JEvent
 *
 */
class JCKCpanelControllerListener extends JEvent
{
	/**
	 * A JParameter object holding the parameters for the plugin
	 *
	 * @var		A JParameter object
	 * @access	public
	 * @since	1.5
	 */
	 
	 public function onCheck()
	 {
		
		$mainframe = JFactory::getApplication();	
		
		
		//Check System requirements for the editor 
		define('JCK_BASE',JPATH_CONFIGURATION .DS.'plugins'.DS.'editors'.DS.'jckeditor');
			
		if(!JFolder::exists(JCK_BASE))
			return;
	
		
		$perms  = fileperms(JPATH_CONFIGURATION.DS.'index.php');
		$perms = (decoct($perms & 0777));
			
		$default_fperms = '0644';
		$default_dperms = '0755'; 
			
		if($perms == 777 || $perms == 666)
		{
			$default_fperms = '0666';
			$default_dperms = '0777'; 
		}
		
		$fperms = JCK_BASE.DS.'config.js';
		
		if(!stristr(PHP_OS,'WIN') && JPath::canChmod(JCK_BASE)  && $perms != decoct(fileperms($fperms) & 0777))
		{
			
		  $path = JCK_BASE.DS.'plugins';
		 
		  if(!JPath::setPermissions($path,$default_fperms,$default_dperms))
		  {
			$mainframe->enqueueMessage( JText::_('Auto correction failed for incorrect file permissions for the JCK Editor'),'error'  );
		  }
		}
	 		
		$mainframe->enqueueMessage( JText::_('System checked and updated'));
		
	}//end function onCheck	
	
		
	public function onSync()
	{
		
		$mainframe = JFactory::getApplication();	
		
		jimport('joomla.filesystem.file');
		
		$src 	= JPATH_ADMINISTRATOR.DS.'components' .DS. 'com_jckman' .DS. 'editor' .DS. 'pluginoverrides.php';
		$dest 	= JPATH_PLUGINS.DS.'editors'.DS.'jckeditor'.DS.'jckeditor'.DS.'includes'.DS.'ckeditor'.DS.'plugins'.DS.'editor'.DS. 'pluginoverrides.php';
		
		
		if( !JFile::copy( $src, $dest) ){
			$mainframe->enqueueMessage( JText::_('Unable to move pluginoverrides JCK plugin!') );
		}
		
		
		$src 	= JPATH_ADMINISTRATOR.DS.'components' .DS. 'com_jckman' .DS. 'editor' .DS. 'acl.php';
		$dest 	= JPATH_PLUGINS.DS.'editors'.DS.'jckeditor'.DS.'jckeditor'.DS.'includes'.DS.'ckeditor'.DS.'plugins'.DS.'editor'.DS. 'acl.php';
		
		
		if( !JFile::copy( $src, $dest) ){
			$mainframe->enqueueMessage( JText::_('Unable to move ACL JCK plugin!') );
		}
		
					
		$src 	= JPATH_ADMINISTRATOR.DS .'components' .DS. 'com_jckman' .DS. 'editor'.DS.'plugins.php';
		$dest 	= JPATH_PLUGINS.DS.'editors'.DS.'jckeditor'.DS.'jckeditor'.DS.'includes'.DS.'ckeditor'.DS.'plugins.php';
		
		
		if( !JFile::copy( $src, $dest) ){
			$mainframe->enqueueMessage( JText::_('Unable to move base plugins file to JCK library!') );
		}
		

		$src 	= JPATH_ADMINISTRATOR.DS.'components' .DS. 'com_jckman' .DS. 'editor' .DS. 'scayt.xml';
		$dest 	= JPATH_PLUGINS.DS.'editors'.DS.'jckeditor'.DS.'plugins'.DS.'scayt'.DS. 'scayt.xml';
		
		
		if( !JFile::copy( $src, $dest) ){
			$mainframe->enqueueMessage( JText::_('Unable to move scayt JCK plugin!') );
		}		
		
		//Lets try and restore  broken or removed plugins from backup
		require_once( JPATH_ADMINISTRATOR.DS.'components' .DS. 'com_jckman' .DS. 'helpers' .DS.'restorer.php' );
		$restorer = JCKRestorer::getInstance();
		
		$srcBase = JPATH_ADMINISTRATOR.DS.'components' .DS. 'com_jckman' .DS. 'editor' .DS.'plugins'.DS;
        $destBase = JPATH_PLUGINS.DS.'editors'.DS.'jckeditor'.DS.'plugins'.DS;
			
		$folders = JFolder::folders($srcBase);
		
		foreach($folders as $folder)
		{
			
			$src = $srcBase.$folder;

			if (!$restorer->install($src)) 
			{
				$mainframe->enqueueMessage( JText::_('Unable to restore '.$folder.' JCK plugin!'), 'error' );
			}
			else
			{
				$mainframe->enqueueMessage( JText::_('Sucessfully restored '.$folder.' JCK plugin!'));
			}
            
		}
			
                       
		//check whether plugin is not a core plugin
		//if its not iterate through and see if there are files missing
		//then delete the plugin if there are
		$db = JFactory::getDBO();
		$query = 'SELECT p.id, p.name FROM `#__jckplugins` p WHERE p.iscore = 0';
		$db->setQuery( $query );
		$results = $db->loadObjectList();
	
		if(!empty($results))
		{
			for($i = 0; $i < count($results);$i++)
			{
				if(!JFolder::exists(JPATH_PLUGINS.DS.'editors'.DS.'jckeditor'.DS.'plugins'.DS.$results[$i]->name))
				{
					$query = 'DELETE FROM #__jcktoolbarplugins
								WHERE pluginid ='. $results[$i]->id;
					$db->setQuery( $query );
					$db->query();
					
					$query = 'DELETE FROM #__jckplugins
								WHERE id ='. $results[$i]->id;
					$db->setQuery( $query );
					$db->query();
				}
			}//end for loop
		}
				
		//check for plugins that have not been added to layout -- legacy check
				
	
		
		$query = 'SELECT id,name FROM `#__jcktoolbars`';
		$db->setQuery( $query );
		$toolbars = $db->loadObjectList();
		
		
		$JCKfolder =  CKEDITOR_LIBRARY.DS . 'toolbar'; 
		
		
		$values = array();
		
		
		if(!empty($toolbars))
		{
			require_once(CKEDITOR_LIBRARY.DS . 'toolbar.php');
				
			//update core plugins layout if needed
			foreach($toolbars as $row)
			{
			
				// get the total number of core plugin records
				$query = 'SELECT COUNT(*)'
				. ' FROM #__jcktoolbarplugins tp'
				. ' JOIN #__jckplugins p ON tp.pluginid = p.id'
				. ' WHERE tp.toolbarid ='.(int) $row->id
				. ' AND p.iscore = 1';
				$db->setQuery( $query );
				$totalRows = $db->loadResult();
				
			
				if(!$totalRows) //lets get plugins from class file
				{
					$filename = $JCKfolder.DS.$row->name.'.php';	
					require_once($filename);
					$classname = 'JCK'. ucfirst($row->name);
					$toolbar = new $classname();
					
					$query = 'SELECT p.id, p.title'
					. ' FROM #__jckplugins p'
					. ' LEFT JOIN #__jckplugins parent on parent.id = p.parentid'
					. ' AND parent.published = 1'
					. ' WHERE p.title != ""'
					. ' AND p.published = 1'
					. ' AND p.iscore = 1'
					. ' AND(p.parentid IS NULL OR parent.published = 1)'
					;
					$db->setQuery( $query );
					$allplugins = $db->loadObjectList();
					
					
					$values = array();
					//fix toolbar values or they will get wiped out
					$l = 1;
					$n = 1;
					$j = 1;
					
					foreach (get_object_vars( $toolbar ) as $k => $v)
					{
						
						if($v) 
						{
							$n = ($n > $v ? $n :  $v);
						}
						if($l < $n)
						{
							$l = $n;
							$j = 1;
						}	
	
						for($m = 0; $m< count($allplugins); $m++)
						{
							
							if($k == $allplugins[$m]->title)
							{
								$values[] = '('.(int)$row->id.','.(int)$allplugins[$m]->id.','.$n.','.$j.',1)';
								break;
							}	
							
							if(strpos($k,'brk_') !== false)
							{
								$id = preg_match('/[0-9]+$/',$k);
								$id = $id * -1;
								$values[] = '('.(int)$row->id.','.$id.','.$n.','.$j.',1)';
								$n++;
								break;
							}	
						}
						$j++;
					}
					
					if(!empty($values))
					{
						$query = 'INSERT INTO #__jcktoolbarplugins(toolbarid,pluginid,row,ordering,state) VALUES ' . implode(',',$values);
						$db->setQuery( $query );
						if(!$db->query()) 
						{
							JError::raiseWarning( 500, $db->getErrorMsg() );
						}
					}
				}
			}
					
			//update non core plugins layout 										
			$values = array();
			
			foreach($toolbars as $row)
			{
			
				$query = 'SELECT id,title FROM `#__jckplugins` p WHERE p.title != "" AND p.iscore = 0  AND p.published = 1'
						.' AND NOT EXISTS(SELECT 1 FROM  #__jcktoolbarplugins tp WHERE tp.pluginid = p.id AND tp.toolbarid = ' .$row->id. ')';
				$db->setQuery( $query );
				$plugins = $db->loadObjectList();
								
				$tmpfilename = $JCKfolder.DS.$row->name.'.php';
				
				if(!file_exists($tmpfilename))
					continue; //skip
				
				require_once($tmpfilename);
				
				$classname = 'JCK'. ucfirst($row->name);
				
				$toolbar = new $classname();
						
				$rowDetail = JCKHelper::getNextLayoutRow($row->id);
										
				foreach (get_object_vars( $toolbar ) as $k => $v)
				{
									
					foreach($plugins as $plugin)
					{
									
						if($plugin->title == $k)
						{
						
							$values[] = '('.$row->id.','. $plugin->id.','.$rowDetail->rowid.','.$rowDetail->rowordering.',1)';
							$rowDetail->rowordering++;
							
						}
					}
				}
				
			}
		}

		//Now add plugins to layouts
		if(!empty($values))
		{
			$query = 'INSERT INTO #__jcktoolbarplugins(toolbarid,pluginid,row,ordering,state) VALUES ' . implode(',',$values)
					.' ON DUPLICATE KEY UPDATE toolbarid = VALUES(toolbarid),pluginid = VALUES(pluginid)';
			$db->setQuery( $query );
			if(!$db->query()) 
			{
				JError::raiseWarning( 100, $db->getErrorMsg() );
			}
		}
	
   
		//Reload Toolbar if editor is re-installed
		jckimport( 'event.observable.editor' );
		
		$obs	= new JCKEditorObservable( 'toolbars' );
		$handle = $obs->getEventHandler();
		
		$query = 'SELECT * FROM `#__jcktoolbars` t WHERE exists(SELECT 1 FROM  #__jcktoolbarplugins tp WHERE tp.toolbarid  = t.id)';
		$db->setQuery( $query );
		$rowresults = $db->loadObjectList();
		
		foreach($rowresults as $row)
		{
			$id 	= $row->id;
			$name 	= $row->name;
			$title 	= $row->title;
			
			switch( $name )
			{
				case 'publisher' :
				case 'full':
				case 'basic' :
				case 'blog' :
					$isNew = false;
					break;
				default :
					$isNew = true;
					break;
			}//end switch
			
			$handle->onSave( $id, $name, $name, $title, $isNew );
		}//end forloop		
        
        //restore state of published/unpublished plugins
		$obs	= new JCKEditorObservable( 'list' );
		$handle = $obs->getEventHandler();
			
		$where = array();
		$where[] = ' WHERE p.published = 1';
		$where[] = ' WHERE p.published = 0';
		$state = array(1,0);
		$count = count($where);
		for( $i = 0; $i < $count; $i++ )
		{
				
			$db = JFactory::getDBO();
			$query = 'SELECT id FROM `#__jckplugins` p' . $where[$i] . ' AND p.iscore = 1 AND type="plugin"';
			$db->setQuery( $query );
			$results = $db->loadResultArray();

			$handle->onPublish($results, $state[$i]);
		}//end for loop
			
		$mainframe->enqueueMessage( JText::_('Editor has been synchronized'));

	}//end function onSync	
	
	
	public function onImport()
	{
	
	
	}//end function onSync	
	
	
	public function onExport()
	{
		
        require(JPATH_COMPONENT.'/helpers/archive.php');
		
		//copy XML file
		jimport('joomla.filesystem.file');
			
		$src 	= JPATH_PLUGINS.DS.'editors'.DS.'jckeditor'.DS.'jckeditor.xml';
		$dest 	= JPATH_ADMINISTRATOR.DS.'components' .DS. 'com_jckman' .DS. 'editor'.DS.'plugins'.DS.'jckeditor.xml';
		
		if( !JFile::copy( $src, $dest) ){
			$mainframe->enqueueMessage( JText::_('Unable to copy JCK Editor\'s Manifest file') );
		}
		
		$src 	= JPATH_PLUGINS.DS.'editors'.DS.'jckeditor'.DS.'jckeditor'.DS.'includes'.DS.'ckeditor'.DS.'toolbar';
		$dest 	= JPATH_ADMINISTRATOR.DS.'components' .DS. 'com_jckman' .DS. 'editor'.DS.'toolbar';
		
		if( !JFolder::copy( $src, $dest,'',true) ){
			$mainframe->enqueueMessage( JText::_('Unable to copy JCK Editor\'s toolbars') );
		}
				
		//process SQL
		if($this->_createSQL())
		{
		
			// Create a new gzip file test.tgz in htdocs/test
			$backup_file_name = 'bak_jckeditor'.date('dmyHis') . '.tar.gz';
			
      		$tgz = new gzip_file($backup_file_name);
			$tgz->set_options(array('basedir' => JPATH_COMPONENT."/editor", 'overwrite' =>1,'inmemory'=>1,level=>5));
			$tgz->add_files('import.xml');
			$tgz->add_files('toolbar');
			$tgz->add_files('plugins');
			$tgz->create_archive();
			$tgz->download_file();
			exit;
     	}
		else
		{
			JError::raiseWarning( 100, "Could not create SQL file");
		}
	
	}//end function onSync
	
	private function _createSQL()
	{
		
		$tables = array('#__jckplugins','#__jcktoolbars','#__jcktoolbarplugins');
	
		$db = JFactory::getDBO();
	
		$sql = array();
	
		foreach($tables as $table)
		{
			 $sql[] = 'DROP TABLE IF EXISTS '. $table.';'.chr(13);
			 $query = 'SHOW CREATE TABLE '. $table;
			 $db->setQuery($query);
       		 $row = $db->loadRow();
 			 $struct = str_replace($db->getPrefix(),'#__',$row[1]);
			 $sql[] = $struct.';'.chr(13);
			 $query = 'SELECT * FROM '. $table;
			 $db->setQuery($query);
			 $rows = $db-> loadRowList();
	
			if(!empty($rows))
			{
				
				$sql[] = 'INSERT INTO '. $table. ' VALUES ';
				
				$fieldcount = count($rows[0]);
				$rowcount = count($rows);
				$fieldcount--;
				$rowcount--;
				foreach($rows as $k=>$row)
				{
					if(!$row[$fieldcount])
						$row[$fieldcount] = 'NULL';
					if(!$row[$fieldcount-2])
						$row[$fieldcount-2] = 'NULL';	
						
					if($k < $rowcount)	
						$tupples =	"('".implode("','",$row)."'),";
					else
						$tupples =	"('".implode("','",$row)."');";
					$tupples = str_replace("'NULL'","NULL",$tupples);
					$sql[] = $tupples;
				}
				$sql[] = chr(13);
			}
		}
		
		$query = "SELECT params FROM #__extensions WHERE folder='editors' AND element = 'jckeditor'";
		$db->setQuery($query);
		$result = $db-> loadResult();
		
		$sql[] = "UPDATE #__extensions";
		$sql[] = "SET params = '".$db->escape($result)."'";
		$sql[] = "WHERE folder='editors' AND element = 'jckeditor'";
		$sql[] = chr(13);	
		
		$buffer = implode(chr(13),$sql);
		$file = JPATH_COMPONENT.'/editor/plugins/sql.sql';
		return JFile::write($file, $buffer);
	}
	
}//end class