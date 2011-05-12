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
    function display($tpl = null)
    {
        $mainframe = &JFactory::getApplication();

        $params = &$mainframe->getParams();
        $this->assignRef( 'params', $params );

        $postid = JRequest::getInt( 'p', 0 );

        $blogInfo = WordbridgeHelper::getBlogByName( $params->get( 'wordbridge_blog_name' ) );
        $this->assignRef( 'blogTitle', $blogInfo['description'] );

        $model = &$this->getModel();
        $entry =& $model->getEntry( $postid, $blogInfo['id'] );

        $baseUrl = JSite::getMenu()->getActive()->link;
        $this->assignRef( 'blogLink', $baseUrl );

        $content = '<p>' . implode( '</p><p>', explode( "\n\n", $entry['content'] ) ) . '</p>';

        $this->assignRef( 'content', $content );
        $this->assignRef( 'title', $entry['title'] );
        $this->assignRef( 'slug', $entry['slug'] );
        $this->assignRef( 'categories', $entry['categories'] );
        $this->assignRef( 'postid', $entry['postid'] );
        $this->assignRef( 'date', $entry['date'] );

        parent::display($tpl);
    }
}

