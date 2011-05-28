<?php
/**
 * @version     $Id$
 * @package  Wordbridge
 * @copyright   Copyright (C) 2011 Cognidox Ltd
 * @license  GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');
require_once( JPATH_SITE.DS.'components'.DS.'com_wordbridge'.DS.'helpers'.DS.'helper.php' );

JPlugin::loadLanguage( 'plg_search_wordbridge', JPATH_ADMINISTRATOR );
class plgSearchWordbridge extends JPlugin
{
    function onContentSearchAreas()
    {
        return $this->onSearchAreas();
    }

    function onContentSearch( $text, $phrase = '', $ordering = '', $areas = null )
    {
        return $this->onSearch( $text, $phrase = '', $ordering = '', $areas = null );
    }

    function onSearchAreas() {
        $areas = array( 'wordbridge' => 'PLG_SEARCH_WORDBRIDGE_AREA' );
        return $areas;
    }

    function onSearch( $text, $phrase = '', $ordering = '', $areas = null )
    {
        // Need to look up all the menu items that are linked to blogs
        $results = array();

        $text = trim( $text );
        if ( empty( $text ) )
        {
            return $results;
        }

        if (is_array( $areas )) {
            if (!array_intersect( $areas, array_keys( $this->onSearchAreas() ) )) {
                return array();
            }
        }

        // We want to keep an eye on any blogs we've seen before, as
        // they may be linked in as multiple menus
        $seenBlogs = array();
        $menuIDs = WordbridgeHelper::getWordbridgeMenuIDs();
        $menu =& JSite::getMenu();
        foreach ( $menuIDs as $itemid )
        {
            $params =& $menu->getParams( $itemid );
            $blog_name = $params->get( 'wordbridge_blog_name' );
            if ( !$params || $params->get( 'wordbridge_searchable' ) == 'no' ||
                 empty( $blog_name ) )
            {
                continue;
            }
            if ( array_key_exists( $blog_name, $seenBlogs ) )
            {
                continue;
            }
            $seenBlogs[$blog_name] = 1;

            // Create a curl request for the search
            $blogInfo = WordbridgeHelper::getBlogInfo( $blog_name, true );
            if ( !$blogInfo )
            {
                continue;
            }
            $url = sprintf( 'http://%s.wordpress.com/?s=%s&feed=rss2', $blog_name, urlencode( $text ) );
            $entries = WordbridgeHelper::getEntriesFromUrl( $url );
            WordbridgeHelper::storeBlogEntries( $entries, $blogInfo['id'] );
            foreach ( $entries as $entry )
            {
                $results[] = (object) array( 'href' => sprintf( 'index.php?option=com_wordbridge&Itemid=%d&view=entry&p=%d&slug=%s',
                                                $itemid, $entry['postid'], urlencode( $entry['slug'] ) ),
                                             'title' => $entry['title'],
                                             'section' => JText::_( 'PLG_SEARCH_WORDBRIDGE_AREA' ),
                                             'created' => $entry['date'],
                                             'text' => strip_tags( $entry['content'] )
                                            );
            }
        }

        // Results really should be sorted
        switch( $ordering )
        {
            case 'oldest':
                usort( $results, array( 'plgSearchWordbridge', '_sortByOldest' ) );
                break;
            case 'alpha':
                usort( $results, array( 'plgSearchWordbridge', '_sortByName' ) );
                break;
            case 'newest':
            default:
                usort( $results, array( 'plgSearchWordbridge', '_sortByNewest' ) );
                break;
        }
        return $results;
    }

    static function _sortByName( $a, $b )
    {
        return strcasecmp( $a->title, $b->title );
    }

    static function _sortByOldest( $a, $b )
    {
        if ( $a->created == $b->created )
        {
            return 0;
        }
        if ( $a->created < $b->created )
        {
            return -1;
        }
        return 1;
    }

    static function _sortByNewest( $a, $b )
    {
        if ( $a->created == $b->created )
        {
            return 0;
        }
        if ( $a->created < $b->created )
        {
            return 1;
        }
        return -1;
    }
}

