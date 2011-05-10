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

class WordbridgeModelEntries extends JModel
{

    var $_entries;
    var $_title;
    var $_loaded;

    /*
     * loadEntries
     * Get a set of blog entries from wordpress based on the page
     * passed in. Returns true if loaded
     * @return bool
     */
    function loadEntries( $page = 1 )
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

    function getEntries()
    {
        return $this->_entries;
    }

    function getTitle()
    {
        return $this->_title;
    }
}

