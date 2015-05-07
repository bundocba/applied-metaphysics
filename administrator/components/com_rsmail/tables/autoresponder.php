<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class rsmailTableAutoresponder extends JTable
{
	/**
	 * @param	JDatabase	A database connector object
	 */
	public function __construct($db) {
		parent::__construct('#__rsmail_autoresponders', 'IdAutoresponder', $db);
	}
	
	/**
	 * Overloaded check function
	 *
	 * @return	boolean
	 * @see		JTable::check
	 * @since	1.5
	 */
	public function check() {
		if (empty($this->IdAutoresponder)) {
			$this->DateCreated = JFactory::getDate()->toSql();
		}
		
		if ($cids = JFactory::getApplication()->input->get('cid',array(),'array')) {
			$this->IdLists = implode(',',$cids);
		} else $this->IdLists = '';
		
		return true;
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
			->select($db->qn('IdAutoresponderMessage'))
			->from($db->qn('#__rsmail_ar_messages'))
			->where($db->qn('IdAutoresponder').' = '.(int) $pk);
		
		$db->setQuery($query);
		if ($ids = $db->loadColumn()) {
			foreach($ids as $id) {
				$query->clear()->delete()->from($db->qn('#__rsmail_ar_message_details'))->where($db->qn('IdAutoresponderMessage').' = '.(int) $id);
				$db->setQuery($query);
				$db->execute();
				
				$query->clear()->delete()->from($db->qn('#__rsmail_ar_details'))->where($db->qn('IdAutoresponderMessage').' = '.(int) $id);
				$db->setQuery($query);
				$db->execute();
			}
		}
		
		$query->clear()->delete()->from($db->qn('#__rsmail_ar_messages'))->where($db->qn('IdAutoresponder').' = '.(int) $pk);
		$db->setQuery($query);
		$db->execute();
		
		return parent::delete($pk, $children);
	}
}