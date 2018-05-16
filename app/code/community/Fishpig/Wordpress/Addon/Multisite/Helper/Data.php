<?php
/*
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 * @Obfuscate
 */

class Fishpig_Wordpress_Addon_Multisite_Helper_Data extends Fishpig_Wordpress_Helper_Abstract
{
	/**
	 * Determine whether the extension can run
	 *
	 * @return bool
	 */
	public function canRun()
	{
		return Mage::getStoreConfigFlag('wordpress/mu/enabled', Mage::helper('wordpress/app')->getStore()->getId())
			&& Mage::helper('wordpress')->isEnabled();
	}
	
	/**
	 * Determine whether the current site is the default site
	 *
	 * @return bool
	 */
	public function isDefaultBlog()
	{
		return $this->getBlogId() <= 1;
	}

	/**
	 * Retrieve the current blog ID
	 * If null returned, this is the default site
	 *
	 * @return int|null
	 */
	public function getBlogId()
	{
		return Mage::helper('wordpress/app')->getBlogId();
	}
	
	/**
	 * Retrieve the current site ID
	 *
	 * @return int|null
	 */
	public function getSiteId()
	{
		return 1;
	}
	
	/**
	 * Retrieve a WordPress site option
	 *
	 * @param string $key
	 * @param mixed $default = null
	 * @return mixed
	 */
	public function getWpSiteOption($key, $default = null)
	{
		$helper = Mage::helper('wordpress/app');
		
		if ($helper->getDbConnection() === false) {
			return false;
		}
		
		try {
			$select = $helper->getDbConnection()
				->select()
				->from($helper->getTableName('sitemeta'), 'meta_value')
				->where('meta_key = ?', $key)
				->where('site_id=?', $this->getSiteId())
				->limit(1);

			if ($value = $helper->getDbConnection()->fetchOne($select)) {
				return $value;
			}
		}
		catch (Exception $e) {
			$this->log($e->getMessage());
		}
		
		return $default;
	}
	
	public function getSiteAndBlogObjects()
	{
		$domain = $this->getBlogValue('domain');
		$path  = $this->getBlogValue('path');

		if (!$domain || !$path) {
			throw new Exception('Invalid domain and/or path for blog.');	
		}
		
		$current_site = new stdClass;
		$current_site->id = $this->getSiteId();
		$current_site->blog_id = 1;
		$current_site->domain = $domain;
		$current_site->path = $path;


		$current_blog = new stdClass;
		$current_blog->id = $this->getBlogId();
		$current_blog->blog_id = $this->getBlogId();
		$current_blog->site_id = $this->getSiteId();
		$current_blog->domain = $domain;
		$current_blog->path = $path;
		
		return array(
			$current_site,
			$current_blog,
		);
	}
	
	public function getBlogValue($key)
	{
		$helper = Mage::helper('wordpress/app');
		
		if (($dbConnection = $helper->getDbConnection()) === false) {
			throw new Exception('Unable to connect to the database');
		}
		
		$select = $dbConnection->select()
			->from($helper->getTableName('blogs'), $key)
			->where('site_id=?', $this->getSiteId())
			->where('blog_id=?', $this->getBlogId())
			->limit(1);

		return $dbConnection->fetchOne($select);
	}
}
