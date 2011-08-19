<?php
/**
 * Copyright (c) 2010 Cognidox Ltd 
 * http://www.cognidox.com/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
/**
 * Script file of HelloWorld component
 */
class com_WordbridgeInstallerScript
{
    function update( $parent )
    {
        $this->install( $parent );
        // Alter the cache table if need be to add the cache time column
        $db = JFactory::getDbo();
        $fields = $db->getTableFields( '#__com_wordbridge_cache' );
        if ( ! array_key_exists ( 'update_time', $fields['#__com_wordbridge_cache'] ) )
        {
            // Add the update_time column
            $alterSql = sprintf( 'ALTER TABLE #__com_wordbridge_cache ADD COLUMN %s TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP', $db->nameQuote( 'update_time' ) );
            $db->setQuery( $alterSql );
            $db->query();
        }
    }

    function install($parent) 
    {
        $manifest = $parent->get("manifest");
        $parent2 = $parent->getParent();
        $source = $parent2->getPath("source");
        $lang   = JFactory::getLanguage();
        $lang->load( 'com_wordbridge.sys', $source.DS.'admin', $lang->getDefault(), false, false);

        $installer = new JInstaller();
        $plugin_names = array();
        // Install plugins
        foreach ($manifest->plugins->plugin as $plugin)
        {
            $attributes = $plugin->attributes();
            $plg = $source . DS . $attributes['folder'] . DS . $attributes['plugin'];
            $installer->install($plg);
            $plugin_names[] = $attributes['plugin'];
        }

        //
        $db = JFactory::getDbo();
        $tableExtensions = $db->nameQuote( "#__extensions" );
        $columnElement   = $db->nameQuote( "element" );
        $columnType      = $db->nameQuote( "type" );
        $columnEnabled   = $db->nameQuote( "enabled" );

        foreach ($plugin_names as $plugin)
        {
            $db->setQuery(
                "UPDATE $tableExtensions SET $columnEnabled=1 WHERE 
                $columnElement='$plugin' AND $columnType='plugin'" );
            $db->query();
        }
        echo JText::_( 'COM_WORDBRIDGE_INSTALLED' );
    }

    function uninstall($parent) 
    {
        $plugins = array(
                    array( 'search', 'wordbridge' ),
                    );

        $where = array();
        foreach ( $plugins as $plugin )
        {
            $where[] = vsprintf("(type='plugin' AND folder='%s' AND element='%s')", $plugin);
        }

        $query = 'SELECT extension_id FROM #__extensions WHERE '.implode( ' OR ', $where );

        $dbo = JFactory::getDBO();
        $dbo->setQuery($query);
        $installed_plugins = $dbo->loadResultArray();
        if ( is_array( $installed_plugins ) && count( $installed_plugins ) )
        {
            $installer =& new JInstaller();
            foreach ( $installed_plugins as $plugin_id )
            {
                $installer->uninstall( 'plugin', $plugin_id );
            }

        }
        echo '<p>' . JText::_( 'COM_WORDBRIDGE_UNINSTALL_TEXT' ) . '</p>';
    }

    function preflight($type, $parent) 
    {
        $manifest = $parent->get("manifest");
        $parent2 = $parent->getParent();
        $source = $parent2->getPath("source");
        $lang   = JFactory::getLanguage();
        $lang->load( 'com_wordbridge.sys', $source.DS.'admin', $lang->getDefault(), false, false);

        // Make sure JSON is installed
        if ( $type == 'install' )
        {
            if ( !function_exists( 'curl_init' ) )
            {
                echo '<p>' . JText::_( 'COM_WORDBRIDGE_NO_CURL' ) . '</p>';
                return false;
            }
            if ( !class_exists( 'DOMDocument' ) )
            {
                echo '<p>' . JText::_( 'COM_WORDBRIDGE_NO_DOM' ) . '</p>';
                return false;
            }
        }
    }
}
