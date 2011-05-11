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
        $info['last_post_id'] = substr( $info['last_post_id'], strlen( $info['id'] ) );

        // Update the stored blog basic details if need be
        if ( !empty( $info['description'] ) )
        {
            $stored_blog = WordbridgeHelper::getBlogByID( $info['id'] );
            if ( $stored_blog )
            {
                if ( $stored_blog['description'] != $info['description'] )
                {
                    WordbridgeHelper::storeBlog( $info['id'], $info['description'] );
                }
            }
            else
            {
                // Store the blog data locally
                WordbridgeHelper::storeBlog( $info['id'], $info['description'] );
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
        $query = sprintf( 'SELECT blog_id, description FROM #__com_wordbridge_blogs WHERE blog_id = %d', (int)$id );
        $db->setQuery( $query );
        $blog = $db->loadRow();
        if ( $blog == null )
        {
            return null;
        }
        return array( 'id' => $blog[0], 'description' => $blog[1] );
    }


    /**
     * storeBlog
     * Store the ID and description of a blog
     */
    function storeBlog( $id, $description )
    {
        $db =& JFactory::getDBO();
        $query = sprintf( 'REPLACE INTO #__com_wordbridge_blogs VALUES(%d, %s)', (int)$id, $db->Quote( $description, true ) );
        $db->Execute( $query );
    }
}
