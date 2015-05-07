<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class rsmailTableList extends JTable
{
	/**
	 * @param	JDatabase	A database connector object
	 */
	public function __construct($db) {
		parent::__construct('#__rsmail_lists', 'IdList', $db);
	}
	
	/**
	 * Method to delete a node and, optionally, its child nodes from the table.
	 *
	 * @param   integer  $pk        The primary key of the node to delete.
	 * @param   boolean  $children  True to delete child nodes, false to move them up a level.
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     http://docs.joomla.org/JTable/delete
	 * @since   2.5
	 */
	public function delete($pk = null, $children = false) {
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);
		
		$query->clear()
			->delete()
			->from($db->qn('#__rsmail_list_fields'))
			->where($db->qn('IdList').' = '.(int) $pk);
		$db->setQuery($query);
		$db->execute();
		
		$query->clear()
			->delete()
			->from($db->qn('#__rsmail_subscribers'))
			->where($db->qn('IdList').' = '.(int) $pk);
		$db->setQuery($query);
		$db->execute();
		
		$query->clear()
			->delete()
			->from($db->qn('#__rsmail_subscriber_details'))
			->where($db->qn('IdList').' = '.(int) $pk);
		$db->setQuery($query);
		$db->execute();
		
		return parent::delete($pk, $children);
	}
}