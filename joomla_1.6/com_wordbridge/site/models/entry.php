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

class WordbridgeModelEntry extends JModel
{
    /**
     * We should load entries off the DB
     */
    function getEntry( $postid, $blog_uuid )
    {
        $db = JFactory::getDBO();
        $query = sprintf( 'SELECT post_id, title, content, UNIX_TIMESTAMP(post_date), slug FROM #__com_wordbridge_posts WHERE post_id = %d AND blog_uuid = %s', $postid, $db->quote( $blog_uuid, true ) );
        $db->setQuery( $query );
        $entry = $db->loadRow();
        if ( !$entry )
        {
            return null;
        }
        $result = array();
        $result['postid'] = $entry[0];
        $result['title'] = $entry[1];
        $result['content'] = $entry[2];
        $result['date'] = $entry[3];
        $result['slug'] = $entry[4];
        $result['categories'] = array();
        $cat_query = sprintf( 'SELECT DISTINCT category from #__com_wordbridge_post_categories WHERE post_id = %d AND blog_uuid = %s', $entry[0], $db->quote( $blog_uuid, true ) );
        $db->setQuery( $cat_query );
        $categories = $db->loadRowList();
        foreach ( $categories as $cat )
        {
            $result['categories'][] = $cat[0];
        }
        return $result;
    }
}

