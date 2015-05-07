<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.controller' );


class ListController extends JCKController
{
	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		parent::__construct( $default );

		$this->registerTask( 'apply', 		'save');
		$this->registerTask( 'unpublish', 	'publish');
		$this->registerTask( 'edit' , 		'display' );
		$this->registerTask( 'add' , 		'display' );
		$this->registerTask( 'orderup'   , 	'order' );
		$this->registerTask( 'orderdown' , 	'order' );

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
				JRequest::setVar( 'view', 'editplugin' );
			}
		}
	
		parent::display($cachable, $urlparams);
	}

			
			
	function save()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );
		
		$db = &JFactory::getDBO();
		$row 	=& JCKHelper::getTable('plugin');
		$task 	= $this->getTask();
		
		$post = JRequest::get('post');
		
		$groups = JRequest::getVar('groups',array(),'post','array');
		JArrayHelper::toInteger($groups);
			
		if (!$row->bind($post)) {
			JError::raiseError(500, $row->getError() );
		}
		
		$row->acl = json_encode($groups); //AW
		JArrayHelper::toInteger();
		
		if (!$row->check()) {
			JError::raiseError(500, $row->getError() );
		}
		
		if (!$row->store()) {
			JError::raiseError(500, $row->getError() );
		}
		$row->checkin();
				
		//$row->reorder( 'type = '.$db->Quote($row->type).' AND ordering > -10000 AND ordering < 10000');
		
		//update toolbar selections so set args for event
		$selections = JRequest::getVar( 'selections', array(), 'post', 'array' );
		$this->event_args = array('plugin' => $row,'pluginToolbarnames'=>$selections );
				
		switch ( $task )
		{
			case 'apply':
				$msg = JText::sprintf( 'Successfully Saved changes to Plugin', $row->title );
				$this->setRedirect( 'index.php?option=com_jckman&controller=list&task=edit&cid[]='. $row->id, $msg );
				break;

			case 'jcksave':
			default:
				
				$msg = JText::sprintf( 'Successfully Saved Plugin', $row->title );
				$this->setRedirect('index.php?option=com_jckman&controller=list', $msg );
				break;
		}
	}	
	

	function publish( ){
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );

		$db		=& JFactory::getDBO();
		$user	=& JFactory::getUser();
		$cid     = JRequest::getVar( 'cid', array(0), 'post', 'array' );
		JArrayHelper::toInteger($cid, array(0));
		$publish = ( $this->getTask() == 'publish' ? 1 : 0 );

		if (count( $cid ) < 1) {
			$action = $publish ? JText::_( 'publish' ) : JText::_( 'unpublish' );
			JError::raiseError(500, JText::_( 'Select a plugin to '.$action ) );
		}

		$cids = implode( ',', $cid );

		$query = 'UPDATE #__jckplugins SET published = '.(int) $publish
			. ' WHERE id IN ( '.$cids.' )'
			. ' AND ( checked_out = 0 OR ( checked_out = '.(int) $user->get('id').' ))'
			;
		$db->setQuery( $query );
		if (!$db->query()) {
			JError::raiseError(500, $db->getErrorMsg() );
		}
		
		$this->event_args = array('cid' => $cid,'value'=>$publish );

		$this->setRedirect( 'index.php?option=com_jckman&controller=list' );
	}
		
	function cancel( ){
		$this->setRedirect( JRoute::_( 'index.php', false ) );
	}

	function cancelEdit( )
	{
	  // Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );

		$row 	=& JCKHelper::getTable('plugin');
		$row->bind(JRequest::get('post'));
		$row->checkin();

		$this->setRedirect( JRoute::_( 'index.php?option=com_jckman&controller=list', false ) );
	}
	
	function order( )
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );
		$this->setRedirect( 'index.php?option=com_jckman&controller=list' );
	}

	function saveorder( )
	{
		//Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );
		$this->setRedirect( 'index.php?option=com_jckman&controller=List', $msg );
	}
	
	function checkin()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );

		$canDo = JCKHelper::getActions();
				
		if( !$canDo->get('core.edit.state') )
		{
			$this->setRedirect( JRoute::_( 'index.php?option=com_jckman&view=list', false ), JText::_( 'COM_JCKMAN_PLUGIN_PERM_NO_CHECK' ), 'error' );
			return false;
		}

		$db		= JFactory::getDBO();
		$user	= JFactory::getUser();
		$cid    = JRequest::getVar( 'cid', array(0), 'post', 'array' );
		$sql	= $db->getQuery( true );
		JArrayHelper::toInteger($cid, array(0));

		if(count( $cid ) < 1)
		{
			JError::raiseWarning(101, JText::_( 'Select a plugin to checkin' ) );
		}

		$cids = implode( ',', $cid );
		$sql->update( '#__jckplugins' )
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

		JError::raiseWarning(100, (int)count( $cid ) . chr( 32 ) . 'plugin' . $plural . ' checked in');
		
		$msg = (int)count( $cid ) . chr( 32 ) . 'plugin' . $plural . ' checked in';

		$this->setRedirect( JRoute::_( 'index.php?option=com_jckman&view=list', false ),$msg );
	}
}	