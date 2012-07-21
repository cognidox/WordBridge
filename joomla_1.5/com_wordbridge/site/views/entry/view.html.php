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
        $entry =& $model->getEntry( $postid, $blogInfo['uuid'] );

        $baseUrl = JSite::getMenu()->getActive()->link . '&Itemid=' . JSite::getMenu()->getActive()->id;
        $this->assignRef( 'blogLink', $baseUrl );

        // Determine if we'll convert links
        $convertLinks = ( $params->get( 'wordbridge_convert_links', 'no' ) == 'yes' ? true : false );
        $this->assignRef( 'convertLinks', $convertLinks );

        $this->assignRef( 'content', $entry['content'] );
        $this->assignRef( 'title', $entry['title'] );
        $this->assignRef( 'slug', $entry['slug'] );
        $this->assignRef( 'categories', $entry['categories'] );
        $this->assignRef( 'postid', $entry['postid'] );
        $this->assignRef( 'date', $entry['date'] );

        // Allow JComments to be added to blog entries
        $jcomments = false;
        $jcommentsPath =  JPATH_SITE . DS .'components' . DS . 'com_jcomments' . DS;
        $jcommentFile = $jcommentsPath . 'jcomments.php';
        if ( $params->get( 'wordbridge_show_jcomments' ) == 'yes' &&
             file_exists( $jcommentFile ) )
        {
            $jbase = JPATH_SITE . DS .'components' . DS;
            $jPlgSrc = $jbase .  'com_wordbridge' . DS . 'assets' . DS . 'com_wordbridge.plugin.php';
            $jPlgDst = $jbase .  'com_jcomments' . DS . 'plugins' . DS . 'com_wordbridge.plugin.php';
            // Check to see if the integration is installed
            $copyRes = true;
            if ( !file_exists( $jPlgDst ) ||
                ( filemtime( $jPlgSrc ) > filemtime( $jPlgDst ) ) )
            {
                // Copy the wordbridge plugin over to jcomments
                $copyRes = JFile::copy( $jPlgSrc, $jPlgDst );
            }
            // Only set up JComments if the wordbridge plugin is
            // installed OK
            if ( $copyRes )
            {
                require_once( $jcommentFile );
                $jid = ( $item->id * 10000000 ) + $entry['postid'];
                $jcomments = JComments::showComments( $jid, 'com_wordbridge', $entry['title'] );
            }
        }
        $this->assignRef( 'jcomments', $jcomments );

        $document =& JFactory::getDocument();
        $document->setTitle( $document->getTitle() . ' - ' . $entry['title'] );

        parent::display($tpl);
    }
}

