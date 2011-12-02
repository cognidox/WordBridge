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
class WordbridgeViewCategory extends JView
{
    /**
     * Wordbridge category view display method
     * @return void
     **/
    function display($tpl = null)
    {
        $mainframe = JFactory::getApplication();

        $params = $mainframe->getParams();
        $this->assignRef( 'params', $params );

        $category_name = JRequest::getVar( 'c', '' );
        $page = JRequest::getInt( 'page', 1 );

        $blogInfo = WordbridgeHelper::getBlogByName( $params->get( 'wordbridge_blog_name' ) );
        $this->assignRef( 'blogTitle', $blogInfo['description'] );

        $model = $this->getModel();
        $results = $model->getCategoryPosts( $page, $category_name, $blogInfo['id'] );
        $this->assignRef( 'entries', $results->entries );
        $this->assignRef( 'isTag', $results->isTag );

        $app = JFactory::getApplication();
        $menu = $app->getMenu();
        $baseUrl = $menu->getActive()->link . '&Itemid=' . $menu->getActive()->id;
        $this->assignRef( 'blogLink', $baseUrl );

        $viewable_name = trim( JRequest::getVar( 'name', '' ) );
        if ( empty( $viewable_name ) )
        {
            $viewable_name = $category_name;
        }
        $this->assignRef( 'categoryName', $viewable_name );

        $categoryUrl = $baseUrl . '&c=' . urlencode( $category_name ) .
                       '&name=' . urlencode( $viewable_name ) . 
                       '&view=category';
        if ( count( $results->entries ) == (int)$params->get( 'wordbridge_blog_entry_feed_count', 10 ) )
        {
            $older_link = $categoryUrl . "&page=" . ( $page + 1 );
            $this->assignRef( 'olderLink', $older_link );
        }
        if ( $page > 1 )
        {
            $newer_link = $categoryUrl . "&page=" . ( $page - 1 );
            $this->assignRef( 'newerLink', $newer_link );
        }

        parent::display($tpl);
    }
}

