<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class ToolbarsController extends JCKController
{
	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct( $default );

		$this->registerTask( 'apply', 		'save');
		$this->registerTask( 'edit' , 		'display' );
		$this->registerTask( 'add' , 		'display' );
		$this->registerTask( 'remove' , 	'remove' );

	}

	function display($cachable = false, $urlparams = false )
	{
	
		
	  switch($this->getTask())
		{
			case 'add'     :
			case 'edit'    :
			{
				JRequest::setVar( 'hidemainmenu', 1 );
				JRequest::setVar( 'layout', 'form' );
				JRequest::setVar( 'view', 'toolbar' );
			}	break;
			case 'preview'	:
			{
				JRequest::setVar( 'view', 'toolbar' );
				JRequest::setVar( 'layout', 'popup' );
			} 
			
		}
	
		parent::display($cachable, $urlparams);
	}

		/**
	* Compiles information to add or edit a toolbar
	* @param string The current GET/POST option
	* @param integer The unique id of the record to edit
	*/
	function copy()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );

		// Initialize some variables
		$db 	=& JFactory::getDBO();
			
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		$n		= count( $cid );

		if ($n == 0) {
			return JError::raiseWarning( 500, JText::_( 'No items selected' ) );
		}

		$row 	=& JCKHelper::getTable('toolbar');
		$toolbarpugins	= array();
		
			
		$i = 1;	
		
		$ncid = array();
			
		
		foreach ($cid as $id)
		{
		
			
						
			// load the row from the db table
			$row->load( (int) $id );
			$row->title 		= 'Copy of ' . $row->title;
			$row->id 			= 0;
			$row->iscore 		= 0;
			$row->published 	= 1;

						
			//get offset for name of copy
			$query = 'SELECT count(1)'
			. ' FROM #__jcktoolbars'
			. ' WHERE title = "'. $row->title . '"';
			$db->setQuery( $query );
		
			$offset = $db->loadResult();
			
			$row->name 	= $row->name . ($offset +1);			
			
			
			if (!$row->check()) {
				return JError::raiseWarning( 500, $row->getError() );
			}
			if (!$row->store()) {
				return JError::raiseWarning( 500, $row->getError() );
			}
						
			$row->checkin();
			
			$ncid[] = $row->id; 
		
			$query = 'SELECT pluginid,row,ordering,state'
			. ' FROM #__jcktoolbarplugins'
			. ' WHERE toolbarid = '.(int) $id;
			$db->setQuery( $query );
			$rows = $db->loadObjectList();

			foreach ($rows as $toolbar_plugin_row) {
				$toolbarpugins[] = '('.(int) $row->id. ',' .(int) $toolbar_plugin_row->pluginid. ',' .(int) $toolbar_plugin_row->row. ','
				.(int) $toolbar_plugin_row->ordering. ','.(int) $toolbar_plugin_row->state.')';
			}
			
		}
             

		$this->event_args = array('cid' => $ncid);
		

		
		if (!empty( $toolbarpugins ))
		{
			// Toolbar-Plugin Mapping: Do it in one query
			$query = 'INSERT INTO #__jcktoolbarplugins (toolbarid,pluginid,row,ordering,state) VALUES '.implode( ',', $toolbarpugins );
			$db->setQuery( $query );
			if (!$db->query()) {
				return JError::raiseWarning( 500, $row->getError() );
			}
		}
		
		
		
		$msg = JText::sprintf( 'Items Copied', $n );
		$this->setRedirect( 'index.php?option=com_jckman&controller=toolbars', $msg );
	}
			
			
	function save()
	{
		
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );
		
		$mainframe =& JFactory::getApplication();	
	
    	$db = &JFactory::getDBO();
		$row 	=& JCKHelper::getTable('toolbar');
		$task 	= $this->getTask();
		
		$id = JRequest::getInt('id',0);
		
		$oldname = '';
		$isNew = false;
		
		if(!$id)
		{
			$name = JRequest::getVar('name');
			$isNew = true;
		}
		else
		{
			$row->load($id);
			$oldname = $row->name;
		}

		if (!$row->bind(JRequest::get('post'))) {
			JError::raiseError(500, $row->getError() );
		}
		if (!$row->check()) {
			JError::raiseError(500, $row->getError() );
		}
		if (!$row->store()) {
			JError::raiseError(500, $row->getError() );
		}
		$row->checkin();
		
		//code to add plugins from layout
		
		$rows  = JRequest::getVar( 'rows', '', 'post');
		$rows = str_replace( ',/,,/,', ',/,', $rows );
		$rows = explode('/',$rows);
			
		if($rows[count($rows) -1] == ',')
			array_pop($rows);
		
		for($i = 0;$i < count($rows); $i++) $rows[$i] = explode(',',$rows[$i]);
			
		$values = array();
		$k = 1;
		$j = 1;
		$l = 1;
		
		$rowcount = count($rows );
		foreach($rows as $toolbar)
		{
			
			if(empty($toolbar))
				continue;
			
			foreach($toolbar as $icon)
			{
			   
			   if($icon =='')
				continue;
			   
			   if($icon ==';')
			   {
				$k++;
				$j = 1;
			   }
			   else
				{
					$pluginid = str_replace('icon','',$icon);
					$values[] = '('.(int)$row->id.','.(int)$pluginid.','.$k.','.$j.',1)';
					$j++;
				}
			}
			$breakid = $l * -1;
			if($l < $rowcount)
				$values[] = '('.(int)$row->id.','.$breakid.','.$k.','.$j.',1)';
			$l++;
		}
		
		//first delete dependencies
	
		$query = 'DELETE FROM #__jcktoolbarplugins'
			. ' WHERE toolbarid = '.$row->id;
	
		$db->setQuery( $query );
		if (!$db->query()) {
			JError::raiseError(500, $db->getErrorMsg() );
		}
		
		
		if(!empty($values))
		{
			$query = 'INSERT INTO #__jcktoolbarplugins(toolbarid,pluginid,row,ordering,state) VALUES ' . implode(',',$values);
			$db->setQuery( $query );
			if(!$db->query()) 
			{
				JError::raiseWarning( 500, $db->ErrorMsg() );
			}
		}
	

		
		//arguments for onSave Event
		$this->event_args = array('id' =>  $row->id,'name'=>$row->name,'oldname'=>$oldname,'title'=>$row->title,'isNew'=>$isNew);
	
		switch ( $task )
		{
			case 'apply':
				$msg = JText::sprintf( 'Successfully Saved changes to Toolbar %s', $row->title );
				$this->setRedirect( 'index.php?option=com_jckman&controller=toolbars&task=edit&cid[]='. $row->id, $msg );
				break;

			case 'save':
			default:
				$msg = JText::sprintf( 'Successfully Saved Toolbar %s', $row->title );
				$this->setRedirect( 'index.php?option=com_jckman&controller=toolbars', $msg );
				break;
		}
	}	
	

	function cancelEdit( )
	{
	  // Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );

		$row 	=& JCKHelper::getTable('toolbar');
		$row->bind(JRequest::get('post'));
		$row->checkin();
       	$this->setRedirect( 'index.php?option=com_jckman&controller=toolbars');

	}
		
	function remove()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );
		
		
	
		$db		=& JFactory::getDBO();
		$cid  = JRequest::getVar( 'cid', array(0), 'post', 'array' );
		JArrayHelper::toInteger($cid, array(0));

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select a Toolbar to delete' ) );
		}

		if (empty( $cid )) {
			return JError::raiseWarning( 500, 'No items selected' );
		}

		$cids = implode( ',', $cid );
		
			
		$query = 'SELECT count(1)'
		. ' FROM #__jcktoolbars WHERE id IN ('.$cids.') AND iscore = 1';
		$db->setQuery( $query );
		$total = $db->loadResult();
		if($msg = $db->getErrorMsg())
		{
			return JError::raiseError(500, $msg);
		}
		
		if($total > 0){
			$this->setRedirect( 'index.php?option=com_jckman&controller=toolbars');
			return JError::raiseWarning( 500, 'Core Toolbars cannot to be deleted' );
		}
		
		
		$query = 'SELECT name'
		. ' FROM #__jcktoolbars'
		. ' WHERE id IN (' . $cids . ') ';
		
		$db->setQuery( $query );
		$rows = $db->loadResultArray();
		if (!$db->query()) {
			return JError::raiseError(500, $db->getErrorMsg() );
		}
					
		$this->event_args = array('names' => $rows);	
					
		//first delete dependencies
		
		$query = 'DELETE FROM #__jcktoolbarplugins'
			. ' WHERE toolbarid IN ( '.$cids.' )'
			;
		$db->setQuery( $query );
		if (!$db->query()) {
			JError::raiseError(500, $db->getErrorMsg() );
		}
		
		//delete toolbars
				
		$query = 'DELETE FROM #__jcktoolbars'
			. ' WHERE id IN ( '.$cids.' )'
			;
		$db->setQuery( $query );
		if (!$db->query()) {
			JError::raiseError(500, $db->getErrorMsg() );
		}
     	
		$msg = JText::sprintf( 'Successfully Deleted Toolbars: %s', implode(',',$rows) );
		$this->setRedirect( 'index.php?option=com_jckman&controller=toolbars',$msg );
	}
	
	function checkin()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );
		
		
		$canDo = JCKHelper::getActions();

		if( !$canDo->get('core.edit.state') )
		{
			$this->setRedirect( JRoute::_( 'index.php?option=com_jckman&view=toolbars', false ), JText::_( 'COM_JCKMAN_PLUGIN_PERM_NO_CHECK' ), 'error' );
			return false;
		}

		$db		= JFactory::getDBO();
		$user	= JFactory::getUser();
		$app	= JFactory::getApplication();
		$cid    = $app->input->get( 'cid', array(0), 'array' );
		$sql	= $db->getQuery( true );
		JArrayHelper::toInteger($cid, array(0));

		if(count( $cid ) < 1)
		{
			JError::raiseWarning(101, JText::_( 'Select a toolbar to checkin' ) );
		}

		$cids = implode( ',', $cid );
		$sql->update( '#__jcktoolbars' )
			->set( array( 'checked_out = 0', 'checked_out_time = "0000-00-00 00:00:00"' ) )
			->where( 'id IN ( ' . $cids . ' )' )
			->where( 'checked_out = ' . (int)$user->get('id') );
		$db->setQuery( $sql );

		if( !$db->query() )
		{
			JError::raiseError(500, $db->getErrorMsg() );
		}

		$this->event_args 	= array('cid' => $cid,'value'=> true );
		$plural				= ( count( $cid ) > 1 ) ? '(s)' : '';

		$msg = (int)count( $cid ) . chr( 32 ) . 'plugin' . $toolbar . ' checked in';

		$this->setRedirect( JRoute::_( 'index.php?option=com_jckman&view=' . $app->input->get( 'view', 'toolbars' ), false ),$msg );
	}
	
}