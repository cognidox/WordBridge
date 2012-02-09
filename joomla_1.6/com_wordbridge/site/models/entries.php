<?php
/**
 * @version     $Id$
 * @package  Wordbridge
 * @copyright   Copyright (C) 2011 Cognidox Ltd
 * @license  GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );
require_once( JPATH_COMPONENT.DS.'helpers'.DS.'helper.php' );

class WordbridgeModelEntries extends JModel
{

    var $_entries;
    var $_title;

    /*
     * loadEntries
     * Get a set of blog entries from wordpress based on the page
     * passed in. Returns true if loaded
     * @return bool
     */
    function loadEntries( $page = 1, $blogInfo, $nocache = false )
    {
        // Look up the local cache for this page
        $db = JFactory::getDBO();

        // Determine if we can remove the cache
        $app = JFactory::getApplication();
        $params = $app->getParams();
        $cacheTime = (int) $params->get( 'wordbridge_cachetime', 300 );
        if ( $cacheTime < 0 )
        {
            $cacheTime = 0;
        }
        $expiredTime = time() - $cacheTime;
        $clearSql = sprintf( 'DELETE FROM #__com_wordbridge_cache WHERE blog_uuid = %s AND UNIX_TIMESTAMP(%s) < %d', $db->quote( $blogInfo['uuid'], true ), $db->nameQuote( 'update_time' ), $expiredTime );
        $db->setQuery( $clearSql );
        $db->query();

        $query = sprintf( 'SELECT id FROM #__com_wordbridge_cache WHERE blog_uuid = %s AND statuses_count = %d AND last_post_id = %d AND page_num = %d',
                    $db->quote( $blogInfo['uuid'], true ), $blogInfo['count'], 
                    $blogInfo['last_post_id'], $page );
        $db->setQuery( $query );
        $cache_id = $db->loadResult();
        if ( $cache_id != null && !$nocache )
        {
            // We have a cached version of this content, so we
            // can load the entries up into _entries and return
            $this->_loadEntriesFromDB( $cache_id, $blogInfo['uuid'] );
        }
        else
        {
            // There's no locally cached version of the page that's still
            // valid. As a result, the page cache for this blog can be
            // trashed, new versions loaded, and this page cached.
            $this->_storeEntriesInDB( $page, $blogInfo );
        }
        return true;
    }

    /**
     * _storeEntriesInDB
     * Gets the blog posts from wordpress, and stores them locally
     */
    function _storeEntriesInDB( $page, $blogInfo )
    {
        $db = JFactory::getDBO();
        // First, clean the cache, by looking up all the current
        // cached entries, removing the page entries, then deleting
        // the main cache mapping
        $id_query = sprintf( 'SELECT DISTINCT id FROM #__com_wordbridge_cache WHERE blog_uuid = %s AND page_num = %d', $db->quote( $blogInfo['uuid'], true ), $page );
        $db->setQuery( $id_query );
        $id_rows = $db->loadRowList();
        if ( count( $id_rows ) )
        {
            $cache_ids = array();
            foreach ( $id_rows as $row )
            {
                $cache_ids[] = $row[0];
            }
            $del_cache_query = 'DELETE FROM #__com_wordbridge_pages WHERE cache_id IN (' . implode( ',', $cache_ids ) . ')';
            $db->setQuery( $del_cache_query );
            $db->query();
        }
        $del_query = sprintf( 'DELETE FROM #__com_wordbridge_cache WHERE blog_uuid = %s AND page_num = %d', $db->quote( $blogInfo['uuid'], true ), $page );
        $db->setQuery( $del_query );
        $db->query();

        // Load up the entries
        $this->_loadEntriesFromWeb( $page );

        // Create a cache map for this result set
        $add_query = sprintf( 'INSERT INTO #__com_wordbridge_cache (blog_uuid, statuses_count, last_post_id, page_num) VALUES (%s, %d, %d, %d)',
                              $db->quote( $blogInfo['uuid'], true ), $blogInfo['count'],
                              $blogInfo['last_post_id'], $page );
        $db->setQuery( $add_query );
        $db->query();

        // Grab the newly created cache map, and create page entries
        $query = sprintf( 'SELECT id FROM #__com_wordbridge_cache WHERE blog_uuid = %s AND statuses_count = %d AND last_post_id = %d AND page_num = %d',
                          $db->quote( $blogInfo['uuid'], true ), $blogInfo['count'], 
                          $blogInfo['last_post_id'], $page );
        $db->setQuery( $query );
        $cache_id = $db->loadResult();
        if ( $cache_id )
        {
            $post_order = 1;

            WordbridgeHelper::storeBlogEntries( $this->_entries, $blogInfo['uuid'] );
            foreach ( $this->_entries as $entry )
            {
                // Update the locally cached page mapping
                $page_query = sprintf(
                    'INSERT INTO #__com_wordbridge_pages VALUES (%d, %d, %d)',
                    $cache_id, $post_order++, $entry['postid'] );
                $db->setQuery( $page_query );
                $db->query();
            }
        }
    }

    function _loadEntriesFromWeb( $page = 1 )
    {
        $app = JFactory::getApplication();
        $params = $app->getParams();
        $blogname = $params->get( 'wordbridge_blog_name' );
        if ( empty( $blogname ) || ! function_exists ( 'curl_init' ) )
        {
            return false;
        }

        $url = sprintf( 'http://%s/?feed=rss2&paged=%d',
                         WordbridgeHelper::fqdnBlogName( $blogname ), (int) $page );
        
        $this->_entries = WordbridgeHelper::getEntriesFromUrl( $url );
        return true;
    }

    function _loadEntriesFromDB( $cache_id, $blog_uuid )
    {
        $this->_entries = array();
        $db = JFactory::getDBO();
        $query = sprintf( 'SELECT p.post_id, p.title, p.content, UNIX_TIMESTAMP(p.post_date), p.slug, p.blog_uuid FROM #__com_wordbridge_pages AS pages LEFT JOIN #__com_wordbridge_posts AS p ON pages.post_id = p.post_id WHERE pages.cache_id = %d AND p.blog_uuid = %s ORDER BY pages.post_order ASC', $cache_id, $db->quote( $blog_uuid, true ) );
        $db->setQuery( $query );
        $rows = $db->loadRowList();
        foreach ( $rows as $row )
        {
            $entry = array();
            $entry['postid'] = $row[0];
            $entry['title'] = $row[1];
            $entry['content'] = $row[2];
            $entry['date'] = $row[3];
            $entry['slug'] = $row[4];
            $entry['categories'] = array();

            $cat_query = sprintf( 'SELECT DISTINCT category FROM #__com_wordbridge_post_categories WHERE post_id = %d AND blog_uuid = %s', $entry['postid'], $db->quote( $row[5], true ) );
            $db->setQuery( $cat_query );
            $cat_rows = $db->loadRowList();
            foreach ( $cat_rows as $cat )
            {
                $entry['categories'][] = $cat[0];
            }
            $this->_entries[] = $entry;
        }
    }

    function getEntries()
    {
        return $this->_entries;
    }

    function getTitle()
    {
        return $this->_title;
    }
}

