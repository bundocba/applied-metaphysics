<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class rsmailTableMessage extends JTable
{
	/**
	 * @param	JDatabase	A database connector object
	 */
	public function __construct($db) {
		parent::__construct('#__rsmail_messages', 'IdMessage', $db);
	}
	
	/**
	 * Overloaded check function
	 *
	 * @return	boolean
	 * @see		JTable::check
	 * @since	1.5
	 */
	public function check() {
		if (empty($this->MessageBodyNoHTML) && $this->MessageType == 1) {
			$string = $this->MessageBody;
			
			$hrefs = '#href="(.*?)"#is';
			$links = '#<a(.*?)<\/a>#is';
			preg_match_all($links,$string,$matches);

			if (!empty($matches) && !empty($matches[0])) {
				foreach ($matches[0] as $i => $link) {
					$text = strip_tags($link);
					preg_match($hrefs,$link,$match);
					
					if (!empty($match) && !empty($match[1]))
						$string = str_replace($matches[0][$i], $text. ' ('.$match[1].') ',$string);
				}
			}
			
			$this->MessageBodyNoHTML = strip_tags($string);
		}
		
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
			->delete()
			->from($db->qn('#__rsmail_files'))
			->where($db->qn('IdMessage').' = '.(int) $pk);
		$db->setQuery($query);
		$db->execute();
		
		return parent::delete($pk, $children);
	}
}