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
                       'id' => '' );

        $params = &JComponentHelper::getParams( 'com_wordbridge' );
        $blogname = $params->get( 'wordbridge_blog_name' );
        $bloguser = $params->get( 'wordbridge_blog_user' );
        $blogpass = $params->get( 'wordbridge_blog_pass' );
        if ( empty( $blogname ) || empty( $bloguser ) ||
             empty( $blogpass) || ! function_exists( 'curl_init' ) )
        {
            return $info;
        }

        $url = sprintf( 'http://twitter-api.wordpress.com/users/show.xml?screen_name=%s', $blogname );
        $curl = curl_init();
        curl_setopt( $curl, CURLOPT_URL, $url );
        curl_setopt( $curl, CURLOPT_HEADER, false );
        curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
        curl_setopt( $curl, CURLOPT_USERPWD, sprintf( '%s:%s', $bloguser, $blogpass ) );

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
        return $info;
    }
}
