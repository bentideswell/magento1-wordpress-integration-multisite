<?php
/*
 *
 */
class Fishpig_Wordpress_Addon_Multisite_Model_Observer
{
	/*
	 * Adds support for https://www.hreflangtags.com/
	 *
	 * @param  Varien_Event_Observer $observer
	 * @return $this
	 */
	public function addHrefLangTagsObserver(Varien_Event_Observer $observer)
	{
		$post = $observer->getEvent()->getObject();
		$db   = Mage::helper('wordpress/app')->getDbConnection();

		$select = $db->select()
			->from($post->getMetaTable(), ['meta_key', 'meta_value'])
			->where('post_id = ?', (int)$post->getId())
			->where('meta_key LIKE ?', 'hreflang-%');
			
		if (!($hrefTags = $db->fetchPairs($select))) {
			return $this;
		}

		$layout = Mage::getSingleton('core/layout');
		
		if (!($headBlock = $layout->getBlock('head'))) {
			return $this;
		}
	
		if ($htmlLang = $post->getMetaValue('html_lang')) {
			$headBlock->addItem(
				'link_rel', 
				$post->getPermalink(), 
				'rel="alternate" hreflang="' . $htmlLang . '"'
			);
		}

		foreach($hrefTags as $lang => $url) {
			$headBlock->addItem(
				'link_rel', 
				$url, 
				'rel="alternate" hreflang="' . str_replace('hreflang-', '', $lang) . '"'
			);
		}
		
		return $this;
	}
}
