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

jimport( 'joomla.application.component.view' );
require_once( JPATH_COMPONENT.DS.'helpers'.DS.'helper.php' );
 
/**
 * Wordbridge View
 *
 * @package    Joomla.Tutorials
 * @subpackage Components
 */
class WordbridgeViewEntries extends JView
{
    /**
     * Wordbridge entries view display method
     * @return void
     **/
    function display($tpl = null)
    {
        $mainframe = &JFactory::getApplication();

        $params = &$mainframe->getParams();
        $this->assignRef( 'params', $params );

        $page = JRequest::getInt( 'page', 1 );

        // Get the total number of blog entries
        $blogInfo = WordbridgeHelper::getBlogInfo();
        $this->assignRef( 'totalEntries', $blogInfo['count'] );

        // Work out the maximum page to show
        $max_page = ceil( $blogInfo['count'] / $params->get( 'wordbridge_blog_entry_feed_count', 10 ) );
        if ( $page > $max_page )
        {
            $page = $max_page;
        }

        $baseUrl = JSite::getMenu()->getActive()->link;
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
        $model = &$this->getModel();
        $model->loadEntries( $page, $blogInfo );
        $entries = $model->getEntries();
        $this->assignRef( 'entries',   $entries );
        $title = $blogInfo['description'];
        $this->assignRef( 'title',   $title );

        parent::display($tpl);
    }
}

