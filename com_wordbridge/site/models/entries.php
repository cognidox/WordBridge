<?php
/**
 * @version     $Id$
 * @package     Joomla
 * @subpackage  Wordbridge
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
    function loadEntries( $page = 1, $blogInfo )
    {
        // Look up the local cache for this page
        $db =& JFactory::getDBO();
        $query = sprintf( 'SELECT id FROM #__com_wordbridge_cache WHERE blog_id = %d AND statuses_count = %d AND last_post_id = %d AND page_num = %d',
                    $blogInfo['id'], $blogInfo['count'], 
                    $blogInfo['last_post_id'], $page );
        $db->setQuery( $query );
        $cache_id = $db->loadResult();
        if ( $cache_id != null)
        {
            // We have a cached version of this content, so we
            // can load the entries up into _entries and return
            $this->_loadEntriesFromDB( $cache_id );
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
        $db =& JFactory::getDBO();
        // First, clean the cache, by looking up all the current
        // cached entries, removing the page entries, then deleting
        // the main cache mapping
        $id_query = sprintf( 'SELECT DISTINCT id FROM #__com_wordbridge_cache WHERE blog_id = %d AND page_num = %d', $blogInfo['id'], $page );
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
            $db->Execute( $del_cache_query );
        }
        $del_query = sprintf( 'DELETE FROM #__com_wordbridge_cache WHERE blog_id = %d AND page_num = %d', $blogInfo['id'], $page );
        $db->Execute( $del_query );

        // Load up the entries
        $this->_loadEntriesFromWeb( $page );

        // Create a cache map for this result set
        $add_query = sprintf( 'INSERT INTO #__com_wordbridge_cache (blog_id, statuses_count, last_post_id, page_num) VALUES (%d, %d, %d, %d)',
                              $blogInfo['id'], $blogInfo['count'],
                              $blogInfo['last_post_id'], $page );
        $db->Execute( $add_query );

        // Grab the newly created cache map, and create page entries
        $query = sprintf( 'SELECT id FROM #__com_wordbridge_cache WHERE blog_id = %d AND statuses_count = %d AND last_post_id = %d AND page_num = %d',
                          $blogInfo['id'], $blogInfo['count'], 
                          $blogInfo['last_post_id'], $page );
        $db->setQuery( $query );
        $cache_id = $db->loadResult();
        if ( $cache_id )
        {
            $post_order = 1;

            WordbridgeHelper::storeBlogEntries( $this->_entries, $blogInfo['id'] );
            foreach ( $this->_entries as $entry )
            {
                // Update the locally cached page mapping
                $page_query = sprintf(
                    'INSERT INTO #__com_wordbridge_pages VALUES (%d, %d, %d)',
                    $cache_id, $post_order++, $entry['postid'] );
                $db->Execute( $page_query );
            }
        }
    }

    function _loadEntriesFromWeb( $page = 1 )
    {
        $params = &JComponentHelper::getParams( 'com_wordbridge' );
        $blogname = $params->get( 'wordbridge_blog_name' );
        if ( empty( $blogname ) || ! function_exists ( 'curl_init' ) )
        {
            return false;
        }

        $url = sprintf( 'http://%s.wordpress.com/feed/?paged=%d',
                         $blogname, (int) $page );
        
        // Use curl to get the data
        $curl = curl_init();
        curl_setopt( $curl, CURLOPT_URL, $url );
        curl_setopt( $curl, CURLOPT_HEADER, false );
        curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );

        $xml = curl_exec( $curl );
        curl_close( $curl );
        if ( empty( $xml ) )
        {
            return false;
        }

        $params = &JComponentHelper::getParams( 'com_wordbridge' );
        $show_links = $params->get( 'wordbridge_show_links' ) == 'yes' ? true : false;

        $results = array();
        $doc = new DOMDocument();
        $doc->loadXML( $xml );
        $this->_title = $doc->getElementsByTagName( 'description' )->item( 0 )->textContent;
        $entries = $doc->getElementsByTagName( 'item' );
        foreach ( $entries as $item )
        {
            $title = $item->getElementsByTagName( 'title' )->item( 0 )->textContent;
            $date = $item->getElementsByTagName( 'pubDate' )->item( 0 )->textContent;
            $content = $item->getElementsByTagNameNS( 'http://purl.org/rss/1.0/modules/content/', 'encoded' )->item( 0 )->textContent;

            // Work out the wordpress ID for this blog entry
            $postid = null;
            $guid = $item->getElementsByTagName( 'guid' )->item( 0 )->textContent;
            $guid_parts = explode( 'p=', $guid );
            if ( count( $guid_parts ) == 2 )
            {
                $postid = $guid_parts[1];
            }

            // Enumerate the wordpress categories for this entry
            $categories = array();
            foreach ( $item->getElementsByTagName( 'category' ) as $category )
            {
                $categories[] = $category->textContent;
            }

            // Get the human readable slug for this entry (may need for SEF)
            $slug = '';
            $feed_link = $item->getElementsByTagName( 'link' )->item( 0 )->textContent;
            if ( !empty( $feed_link ) )
            {
                $link_parts = explode( '/', $feed_link );
                $slug = $link_parts[ count( $link_parts ) - 2 ];
            }

            // Trim the links if need be
            if ( !$show_links )
            {
                $content = substr( $content, 0, strrpos( $content, '<br />' ) );
            }

            // Add the new entry to our blog entry list
            $results[] = array( 'title' => $title,
                                'postid' => $postid,
                                'categories' => $categories,
                                'slug' => $slug,
                                'date' => strtotime( $date ),
                                'content' => $content );
        }
        $this->_entries = $results;
        return true;
    }

    function _loadEntriesFromDB( $cache_id )
    {
        $this->_entries = array();
        $db =& JFactory::getDBO();
        $query = sprintf( 'SELECT p.post_id, p.title, p.content, UNIX_TIMESTAMP(p.post_date), p.slug FROM #__com_wordbridge_pages AS pages LEFT JOIN #__com_wordbridge_posts AS p ON pages.post_id = p.post_id WHERE pages.cache_id = %d ORDER BY pages.post_order ASC', $cache_id );
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

            $cat_query = 'SELECT category FROM #__com_wordbridge_post_categories WHERE post_id = ' . $entry['postid'];
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

