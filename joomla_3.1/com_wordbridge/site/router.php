<?php
/**
 * @version     $Id$
 * @package  Wordbridge
 * @copyright   Copyright (C) 2011 Cognidox Ltd
 * @license  GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );


function WordbridgeBuildRoute( &$query )
{
    $segments = array();

    if ( isset( $query['view'] ) && $query['view'] != 'entries' )
    {
        $segments[] = $query['view'];
        switch ( $query['view'] )
        {
            case 'entry':
                $segments[0] = 'post';
                if ( strpos( $query['slug'], '.' ) !== false )
                {
                    $query['slug'] = preg_replace( '/^(.+?)\..*/', '${1}', $query['slug'] );
                }
                $segments[] = $query['p'] . '-' . $query['slug'];
                unset( $query['p'] );
                unset( $query['slug'] );
                break;
            case 'category':
                $segments[] = '1-' . $query['c'];
                unset( $query['c'] );
                $segments[] = '1-' . $query['name'];
                unset( $query['name'] );
        }
        unset( $query['view'] );
    }
    else
    {
        $segments[] = 'blog';
    }
    return $segments;
}

function WordbridgeParseRoute( $segments )
{
    $vars = array();
    switch ( $segments[0] )
    {
        case 'blog':
            $vars['view'] = 'entries';
            break;
        case 'post':
            $vars['view'] = 'entry';
            $parts = explode( '-', $segments[1], 2 );
            $vars['p'] = $parts[0];
            $vars['slug'] = $parts[1];
            break;
        case 'category':
            $vars['view'] = 'category';
            $vars['c'] = substr( $segments[1], 2 );
            $vars['name'] = substr( $segments[2], 2 );
            break;
    }
    return $vars;
}
