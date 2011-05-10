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

        $postid = JRequest::getInt( 'p', 1 );

        $model = &$this->getModel();
        $entry =& $model->getEntry( $postid );

        $baseUrl = JSite::getMenu()->getActive()->link;
        $this->assignRef( 'blogLink', $baseUrl );

        if ( $entry['post_status'] == 'publish' )
        {
            $content = '<p>' . implode( '</p><p>', explode( "\n\n", $entry['description'] ) ) . '</p>';
            $title = $entry['title'];
            $slug = $entry['wp_slug'];
            $categories = $entry['categories'];
            $date = strtotime( $entry['dateCreated'] );

            $this->assignRef( 'content', $content );
            $this->assignRef( 'title', $title );
            $this->assignRef( 'slug', $slut );
            $this->assignRef( 'categories', $categories );
            $this->assignRef( 'postid', $postid );
            $this->assignRef( 'date', $date );
        }

        parent::display($tpl);
    }
}

