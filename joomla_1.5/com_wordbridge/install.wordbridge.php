<?php
/**
 * @version     $Id$
 * @package  Wordbridge
 * @copyright   Copyright (C) 2011 Cognidox Ltd
 * @license  GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.installer.installer');

// Install the plugin
$db = & JFactory::getDBO();
$plugins = &$this->manifest->getElementByPath('plugins');
if ( is_a( $plugins, 'JSimpleXMLElement' ) && count( $plugins->children() ) )
{
    foreach ( $plugins->children() as $plugin )
    {
        $pname = $plugin->attributes( 'plugin' );
        $pgroup = $plugin->attributes('group');
        $path = $this->parent->getPath('source').DS.'plugins'.DS.$pgroup;
        $installer = new JInstaller;
        $result = $installer->install( $path );

        $query = "UPDATE #__plugins SET published=1 WHERE element=".$db->Quote($pname)." AND folder=".$db->Quote($pgroup);
        $db->setQuery($query);
        $db->query();
    }
}

// Verify the update field exists in the cache
$fields = $db->getTableFields( '#__com_wordbridge_cache' );
if ( ! array_key_exists ( 'update_time', $fields['#__com_wordbridge_cache'] ) )
{
    // Add the update_time column
    $alterSql = sprintf( 'ALTER TABLE #__com_wordbridge_cache ADD COLUMN %s TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP', $db->nameQuote( 'update_time' ) );
    $db->setQuery( $alterSql );
    $db->query();
}

