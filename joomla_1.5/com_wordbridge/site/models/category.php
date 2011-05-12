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

class WordbridgeModelCategory extends JModel
{

    /**
     * getCategoryPosts
     * Gets the blog posts from wordpress for a specific category, 
     * and stores the blog posts locally
     */
    function getCategoryPosts( $page, $category_name, $blog_id )
    {
        // Load up the entries
        $results = $this->_loadEntriesFromWeb( $page, $category_name );
        WordbridgeHelper::storeBlogEntries( $results->entries, $blog_id );

        return $results;
    }

    function _loadEntriesFromWeb( $page = 1, $category_name )
    {
        $params = &JComponentHelper::getParams( 'com_wordbridge' );
        $blogname = $params->get( 'wordbridge_blog_name' );
        if ( empty( $blogname ) || ! function_exists ( 'curl_init' ) )
        {
            return null;
        }

        $isTag = false;
        $url = sprintf( 'http://%s.wordpress.com/category/%s/feed/?paged=%d',
                         $blogname, urlencode( strtolower( $category_name ) ), (int) $page );
        $tagUrl = sprintf( 'http://%s.wordpress.com/tag/%s/feed/?paged=%d',
                         $blogname, urlencode( strtolower( $category_name ) ), (int) $page );
        
        $blogInfo = WordbridgeHelper::getBlogByName( $blogname );
        if ( $blogInfo['id'] && 
             WordbridgeHelper::isTag( $blogInfo['id'], $category_name ) )
        {
            $isTag = true;
            $url = $tagUrl;
        }

        // Use curl to get the data
        $curl = curl_init();
        curl_setopt( $curl, CURLOPT_URL, $url );
        curl_setopt( $curl, CURLOPT_HEADER, false );
        curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );

        $xml = curl_exec( $curl );
        if ( empty( $xml ) )
        {
            curl_close( $curl );
            return null;
        }
        // If we were looking for a category, and got a not found,
        // call this a tag and try with the tag URL
        if ( !$isTag &&
             strpos( $xml, 'Page not found</title>', 500 ) !== false )
        {
            if ( $blogInfo['id'] )
            {
                WordbridgeHelper::addTag( $blogInfo['id'], $category_name );
            }
            curl_setopt( $curl, CURLOPT_URL, $tagUrl );
            $xml = curl_exec( $curl );
            $isTag = true;
        }
        curl_close( $curl );
        if ( empty( $xml ) )
        {
            return null;
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

            // Add the new entry to our blog entry list
            $results[] = array( 'title' => $title,
                                'postid' => $postid,
                                'categories' => $categories,
                                'slug' => $slug,
                                'date' => strtotime( $date ),
                                'content' => $content );
        }
        return (object) array( 'isTag' => $isTag,
                               'entries' => $results );
    }

}
