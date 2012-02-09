<?php
/**
 * @version     $Id$
 * @package  Wordbridge
 * @copyright   Copyright (C) 2011 Cognidox Ltd
 * @license  GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );
require_once( JPATH_COMPONENT.DS.'helpers'.DS.'helper.php' );
 
/**
 * Wordbridge View
 *
 * @package Wordbridge
 */
class WordbridgeViewEntries extends JView
{
    /**
     * Wordbridge entries view display method
     * @return void
     **/
    function display( $tpl = null )
    {
        $app = JFactory::getApplication();
        $menu = $app->getMenu();
        $item = $menu->getActive();
        if ( !$item )
        {
            $item = $menu->getItem( JRequest::getInt( 'Itemid' ) );
        }

        $params = $item->params;
        $this->assignRef( 'params', $params );

        $page = JRequest::getInt( 'page', 1 );
        $nocache = JRequest::getBool( 'nocache', false );

        // Get the total number of blog entries
        $blogInfo = WordbridgeHelper::getBlogInfo();
        $this->assignRef( 'totalEntries', $blogInfo['count'] );

        // Work out the maximum page to show
        $max_page = ceil( $blogInfo['count'] / $params->get( 'wordbridge_blog_entry_feed_count', 10 ) );
        if ( $page > $max_page )
        {
            $page = $max_page;
        }

        $baseUrl = $item->link . '&Itemid=' . $item->id;
        $this->assignRef( 'blogLink', $baseUrl );
        if ( $page < $max_page )
        {
            $older_link = $baseUrl . "&page=" . ( $page + 1 );
            $this->assignRef( 'olderLink', $older_link );
        }
        if ( $page > 1 )
        {
            $newer_link = $baseUrl . "&page=" . ( $page - 1 );
            $this->assignRef( 'newerLink', $newer_link );
        }

        // Load the model for the desired page
        $model = $this->getModel();
        $model->loadEntries( $page, $blogInfo, $nocache );
        $entries = $model->getEntries();
        $this->assignRef( 'entries',   $entries );
        $title = $blogInfo['description'];
        $this->assignRef( 'blogTitle',   $title );

        $document = JFactory::getDocument();

        // Set the title to place above the blog
        $blog_title = $params->get( 'page_title' );
        if ( !$blog_title )
            $blog_title = $document->getTitle();
        $this->assignRef( 'blog_title', $blog_title );

        parent::display( $tpl );
    }
}

