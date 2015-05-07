<?php
/**
* @version 1.0.0
* @package RSMail! 1.0.0
* @copyright (C) 2009 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class rsmailTableTemplate extends JTable
{
	/**
	 * @param	JDatabase	A database connector object
	 */
	public function __construct($db) {
		parent::__construct('#__rsmail_templates', 'IdTemplate', $db);
	}
	
	/**
	 * Overloaded check function
	 *
	 * @return	boolean
	 * @see		JTable::check
	 * @since	1.5
	 */
	public function check() {
		if (empty($this->TemplateText) && $this->MessageType == 1) {
			$string = $this->TemplateBody;
			
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
			
			$this->TemplateText = strip_tags($string);
		}
		
		return true;
	}
}