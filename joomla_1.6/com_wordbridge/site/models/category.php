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
    function getCategoryPosts( $page, $category_name, $blog_uuid )
    {
        // Load up the entries
        $results = $this->_loadEntriesFromWeb( $page, $category_name );
        WordbridgeHelper::storeBlogEntries( $results->entries, $blog_uuid );

        return $results;
    }

    function _loadEntriesFromWeb( $page = 1, $category_name )
    {
        $app = JFactory::getApplication();
        $params = $app->getParams();
        $blogname = $params->get( 'wordbridge_blog_name' );
        if ( empty( $blogname ) || ! function_exists ( 'curl_init' ) )
        {
            return null;
        }

        $isTag = false;
        $ucategory = urlencode( strtolower( $category_name ) );
        $pageParam = '';
        if ( $page > 1 )
        {
            $pageParam = '&paged=' . (int) $page;
        }
        $url = sprintf( 'http://%s/?feed=rss2&category_name=%s%s',
                         WordbridgeHelper::fqdnBlogName( $blogname ), $ucategory, $pageParam );
        $tagUrl = sprintf( 'http://%s/?feed=rss2&tag=%s%s',
                         WordbridgeHelper::fqdnBlogName( $blogname ), $ucategory, $pageParam );
        
        $blogInfo = WordbridgeHelper::getBlogByName( $blogname );
        if ( $blogInfo['uuid'] && 
             WordbridgeHelper::isTag( $blogInfo['uuid'], $category_name ) )
        {
            $isTag = true;
            $url = $tagUrl;
        }

        $results = WordbridgeHelper::getEntriesFromUrl( $url );
        if ( !$isTag && !count( $results ) && $page <= 1 )
        {
            if ( $blogInfo['uuid'] )
            {
                WordbridgeHelper::addTag( $blogInfo['uuid'], $category_name );
            }
            $isTag = true;
            $results = WordbridgeHelper::getEntriesFromUrl( $tagUrl );
        }
        return (object) array( 'isTag' => $isTag,
                               'entries' => $results );
    }

}

