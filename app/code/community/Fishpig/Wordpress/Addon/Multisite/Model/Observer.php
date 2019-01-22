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
	public function addPostViewHrefLangTagsObserver(Varien_Event_Observer $observer)
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
				'rel="alternate" hreflang="' . $this->cleanLang($htmlLang) . '"'
			);
		}

		foreach($hrefTags as $lang => $url) {
			$headBlock->addItem(
				'link_rel', 
				$url, 
				'rel="alternate" hreflang="' . str_replace('hreflang-', '', $this->cleanLang($lang)) . '"'
			);
		}
		
		return $this;
	}

	/*
	 * Adds support for https://www.hreflangtags.com/
	 *
	 * @param  Varien_Event_Observer $observer
	 * @return $this
	 */	
	public function addPostListHrefLangTagsObserver(Varien_Event_Observer $observer)
	{
		if ((int)Mage::helper('wordpress')->getWpOption('hreflang_pro_allow_blog_tags') === 0) {
			return $this;
		}

		$layout = Mage::getSingleton('core/layout');
		
		if (!($headBlock = $layout->getBlock('head'))) {
			return $this;
		}
		
		if (($tags = trim(Mage::helper('wordpress')->getWpOption('hreflang_pro_blog_tags'))) === '') {
			return $this;
		}
		
		if (!($tags = @unserialize($tags))) {
			return $this;
		}

		$tags = array_combine($tags['hreflang'], $tags['href']);
		
		foreach($tags as $lang => $url) {
			$headBlock->addItem(
				'link_rel', 
				$url, 
				'rel="alternate" hreflang="' . $this->cleanLang($lang) . '"'
			);
		}
		
		return $this;
	}
	
	/**
	 * Clean a $lang string
	 *
	 * @param  string $lang
	 * @return string
	 */
	protected function cleanLang($lang)
	{
		return str_replace('_', '-', $lang);		
	}
}
