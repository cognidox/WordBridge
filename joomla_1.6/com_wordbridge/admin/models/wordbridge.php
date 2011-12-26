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
require_once( JPATH_SITE.DS.'components'.DS.'com_wordbridge'.DS.'helpers'.DS.'helper.php' );

class WordbridgeModelWordbridge extends JModel
{
    /**
     * Return an array of stats about a blog
     */
    function getBlogStats( $blog_name = null )
    {
        // Lookup the menus that we're using
        $menuIDs = WordbridgeHelper::getWordbridgeMenuIDs();
        $menuToBlogMap = array();
        $app = JFactory::getApplication();
        $menu = $app->getMenu( 'site' );
        foreach ( $menuIDs as $itemid )
        {
            $params = $menu->getParams( $itemid );
            $menu_blog_name = $params->get( 'wordbridge_blog_name' );
            if ( empty( $menu_blog_name ) )
            {
                continue;
            }
            if ( !array_key_exists( $menu_blog_name, $menuToBlogMap ) )
            {
                $menuToBlogMap[$menu_blog_name] = array();
            }
            $menuToBlogMap[$menu_blog_name][] = $menu->getItem( $itemid );
        }

        $db = JFactory::getDBO();
        foreach ( array_keys( $menuToBlogMap ) as $blog )
        {
            $item = (object) array(
                        'blog_id' => '',
                        'blog_uuid' => '',
                        'blog_name' => $blog,
                        'description' => '',
                        'last_post' => '',
                        'updated' => null
                        );
            $query = 'SELECT blog_id, blog_uuid, blog_name, description, last_post, UNIX_TIMESTAMP(updated) FROM #__com_wordbridge_blogs WHERE blog_name = ' . $db->quote( $blog, true );
            $db->setQuery( $query );
            $row = $db->loadRow();
            if ( $row )
            {
                $item->blog_id = $row[0];
                $item->blog_uuid = $row[1];
                $item->blog_name = $row[2];
                $item->description = $row[3];
                $item->last_post = $row[4];
                $item->updated = $row[5];
            }

            // Look up the number of posts that are cached for this
            // blog
            $post_query = sprintf( 'SELECT COUNT(*) FROM #__com_wordbridge_posts WHERE blog_uuid = %s', $db->quote( $item->blog_uuid, true ) );
            $db->setQuery( $post_query );
            $item->post_count = $db->loadResult();

            // Look up the number of pages cached for this blog
            $page_query = sprintf( 'SELECT COUNT(*) FROM #__com_wordbridge_cache WHERE blog_uuid = %s', $db->quote( $item->blog_uuid, true ) );
            $db->setQuery( $page_query );
            $item->page_count = $db->loadResult();

            $item->menus = array();
            if ( array_key_exists( $item->blog_name, $menuToBlogMap ) )
            {
                $item->menus = $menuToBlogMap[$item->blog_name];
            }
            $result[] = $item;
        }
        return $result;
    }
}

