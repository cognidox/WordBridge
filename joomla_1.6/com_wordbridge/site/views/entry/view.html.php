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
 * @package    Wordbridge
 */
class WordbridgeViewEntry extends JView
{
    /**
     * Wordbridge entry view display method
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

        $postid = JRequest::getInt( 'p', 0 );
        $blogInfo = WordbridgeHelper::getBlogByName( $params->get( 'wordbridge_blog_name' ) );
        $this->assignRef( 'blogTitle', $blogInfo['description'] );

        $model = $this->getModel();
        $entry = $model->getEntry( $postid, $blogInfo['uuid'] );

        $baseUrl = $item->link . '&Itemid=' . $item->id;
        $this->assignRef( 'blogLink', $baseUrl );

        $this->assignRef( 'content', $entry['content'] );
        $this->assignRef( 'title', $entry['title'] );
        $this->assignRef( 'slug', $entry['slug'] );
        $this->assignRef( 'categories', $entry['categories'] );
        $this->assignRef( 'postid', $entry['postid'] );
        $this->assignRef( 'date', $entry['date'] );

        $document = JFactory::getDocument();

        // Set the title to place above the blog
        $blog_title = $params->get( 'page_title' );
        if ( !$blog_title )
            $blog_title = $document->getTitle();
        $this->assignRef( 'blog_title', $blog_title );

        // Set the page title
        $document->setTitle( $document->getTitle() . ' - ' . $entry['title'] );

        parent::display($tpl);
    }
}

