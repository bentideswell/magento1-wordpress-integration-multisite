<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Addon_Multisite_Model_System_Config_Source_Blog extends Varien_Object
{
	/**
	 * Options cache
	 *
	 * @var array
	 */
	protected $_options = null;
	
	/**
	 * Generate and retrieve the options array
	 *
	 * @return array
	 */
	public function toOptionArray()
	{
		if (!is_null($this->_options)) {
			return $this->_options;
		}
		
		$this->_options = array();
		
		try {
			$helper = Mage::helper('wordpress/app');
			$db = $helper->getDbConnection();
			
			if ($db === false) {
				throw new Exception('WordPress Integration not setup.');
			}

			$select = $db->select()
				->from($helper->getTableName('blogs'), 'blog_id');
				
			$blogIds = $db->fetchCol($select);
			
			foreach($blogIds as $blogId) {
				if ($blogId === '1') {
					$table = $helper->getTablePrefix() . 'options';
				}
				else {
					$table = $helper->getTablePrefix() . $blogId . '_options';
				}
	
				$select = $db->select()
					->from($table, 'option_value')
					->where('option_name=?', 'blogname')
					->limit(1);
					
				$this->_options[] = array('value' => $blogId, 'label' => $blogId . ': ' . $db->fetchOne($select));	
			}
		}
		catch (Exception $e) {
			Mage::helper('wordpress')->log($e->getMessage());
			
			$this->_options = array(array(
				'value' => 1, 'label' => Mage::helper('wordpress')->__('WordPress Multisite not installed'),
			));
		}
		
		return $this->_options;
	}
}