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

jimport( 'joomla.application.component.model' );
require_once( JPATH_COMPONENT.DS.'libraries'.DS.'movabletypeClass'.DS.'class.wpclient.php' );

class WordbridgeModelEntry extends JModel
{

    function getEntry( $postid )
    {
        $params = &JComponentHelper::getParams( 'com_wordbridge' );
        $blogname = $params->get( 'wordbridge_blog_name', "" );
        $bloguser = $params->get( 'wordbridge_blog_user', "" );
        $blogpass = $params->get( 'wordbridge_blog_pass', "" );
        if ( empty( $blogname ) || empty( $bloguser ) ||
             empty( $blogpass) || !$postid )
        {
            return null;
        }
        $wpclient =& new wpclient($bloguser, $blogpass,
                                  $blogname.".wordpress.com", '/xmlrpc.php' );
        $entry =& $wpclient->getPost( $postid );
        $wpclient = null;
        return $entry;
    }
}

