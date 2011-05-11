<?php
/**
 * @version     $Id$
 * @package     Joomla
 * @subpackage  Wordbridge
 * @copyright   Copyright (C) 2011 Cognidox Ltd
 * @license  GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class WordbridgeHelper {

    /**
     * getTotalBlogPosts
     * @return int the total number of blog posts for the blog
     */
    function getBlogInfo()
    {
        $info = array( 'count' => 0,
                       'description' => '',
                       'last_post_id' => 0,
                       'id' => '' );

        $params = &JComponentHelper::getParams( 'com_wordbridge' );
        $blogname = $params->get( 'wordbridge_blog_name' );
        if ( empty( $blogname ) || ! function_exists( 'curl_init' ) )
        {
            return $info;
        }

        $url = sprintf( 'http://twitter-api.wordpress.com/users/show.xml?screen_name=%s', $blogname );
        $curl = curl_init();
        curl_setopt( $curl, CURLOPT_URL, $url );
        curl_setopt( $curl, CURLOPT_HEADER, false );
        curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );

        $xml = curl_exec( $curl );
        curl_close( $curl );
        if ( empty( $xml ) )
        {
            return $info;
        }

        $doc = new DOMDocument();
        $doc->loadXML( $xml );
        $info['count'] = $doc->getElementsByTagName( 'statuses_count' )->item( 0 )->textContent;
        $info['description'] = $doc->getElementsByTagName( 'description' )->item( 0 )->textContent;
        $info['id'] = $doc->getElementsByTagName( 'id' )->item( 0 )->textContent;

        // Get the last post information, removing the blog ID that
        // comes out when using the twitter API
        $info['last_post_id'] = $doc->getElementsByTagName( 'status' )->item( 0 )->getElementsByTagName( 'id' )->item( 0 )->textContent;
        $info['last_post_id'] = (int)substr( $info['last_post_id'], strlen( $info['id'] ) );

        // Update the stored blog basic details if need be
        if ( !empty( $info['description'] ) )
        {
            $stored_blog = WordbridgeHelper::getBlogByID( $info['id'] );
            if ( $stored_blog )
            {
                if ( $stored_blog['description'] != $info['description'] ||
                     $stored_blog['name'] != $blogname )
                {
                    WordbridgeHelper::storeBlog( $info['id'], $blogname, $info['description'] );
                }
            }
            else
            {
                // Store the blog data locally
                WordbridgeHelper::storeBlog( $info['id'], $blogname, $info['description'] );
            }
        }
        return $info;
    }


    /**
     * getBlogByID
     * Look up the locally stored blog details
     * @return array containing id and description if found, or null if not
     */
    function getBlogByID( $id )
    {
        $db =& JFactory::getDBO();
        $query = sprintf( 'SELECT blog_id, blog_name, description FROM #__com_wordbridge_blogs WHERE blog_id = %d', (int)$id );
        $db->setQuery( $query );
        $blog = $db->loadRow();
        if ( $blog == null )
        {
            return null;
        }
        return array( 'id' => $blog[0], 
                      'name' => $blog[1],
                      'description' => $blog[2] );
    }

    /**
     * getBlogByName
     * Look up the locally stored blog details by name
     * @return array containing id, name and description if found, or null if not
     */
    function getBlogByName( $name )
    {
        $db =& JFactory::getDBO();
        $query = sprintf( 'SELECT blog_id, blog_name, description FROM #__com_wordbridge_blogs WHERE blog_name = %s', $db->Quote( $name, true ) );
        $db->setQuery( $query );
        $blog = $db->loadRow();
        if ( $blog == null )
        {
            return null;
        }
        return array( 'id' => $blog[0], 
                      'name' => $blog[1],
                      'description' => $blog[2] );
    }

    /**
     * storeBlog
     * Store the ID, name and description of a blog
     */
    function storeBlog( $id, $name, $description )
    {
        $db =& JFactory::getDBO();
        $query = sprintf( 'REPLACE INTO #__com_wordbridge_blogs VALUES(%d, %s, %s)', (int)$id, $db->Quote( $name, true ), $db->Quote( $description, true ) );
        $db->Execute( $query );
    }

    function nameToSlug( $name )
    {
        $name = strtolower( trim ( $name ) );
        $name = preg_replace( '/[\.\s]/', '-', $name );
        $name = preg_replace( '/[^\-a-z0-9]/', '', $name );
        $name = preg_replace( '/--+/', '-', $name );
        $name = preg_replace( '/^-|_$/', '', $name );
        return $name;
    }

    function storeBlogEntries( $entries, $blog_id )
    {
        $db =& JFactory::getDBO();
        foreach ( $entries as $entry )
        {
            // Update the locally cached post
            $post_query = sprintf( 
                'REPLACE INTO #__com_wordbridge_posts VALUES (%d, %d, %s, %s, %s, %s)', 
                $entry['postid'],
                $blog_id,
                $db->Quote( $entry['title'], true ),
                $db->Quote( $entry['content'], true ),
                $db->Quote( strftime( '%F %T %Z', $entry['date'] ), true),
                $db->Quote( $entry['slug'], true ) );
            $db->Execute( $post_query );

            // Update the post category settings
            $db->Execute( sprintf( 'DELETE FROM #__com_wordbridge_post_categories WHERE post_id = %d AND blog_id = %d', $entry['postid'], $blog_id ) );
            if ( count( $entry['categories'] ) )
            {
                foreach ( $entry['categories'] as $category )
                {
                    $db->Execute( 
                        sprintf( 'INSERT INTO #__com_wordbridge_post_categories VALUES (%d, %d, %s)', $entry['postid'], $blog_id, $db->Quote( $category, true ) ) );
                }
            }
        }
    }

    function getEntriesFromUrl( $url )
    {
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
            return array();
        }

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
        return $results;
    }
}
