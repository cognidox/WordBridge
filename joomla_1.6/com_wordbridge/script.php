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
    var $component_base = 'com_wordbridge';

    function update( $parent )
    {
        $manifest = $parent->get( 'manifest' );
        $parent2 = $parent->getParent();
        $source = $parent2->getPath( 'source' );
        $this->_installPlugins( $manifest, $source, true );

        // If a version prior to 0.5 has been installed, the database
        // tables will not have the UUID fields set, so the tables must
        // be rebuit
        $hasUUID = false;
        $db = JFactory::getDbo();
        $blogFields = $db->getTableFields( '#__' . $this->component_base . '_blogs' );
        foreach ( $blogFields[ '#__' . $this->component_base . '_blogs' ] as $fieldname => $fieldtype )
        {
            if ( $fieldname == 'blog_uuid' )
            {
                $hasUUID = true;
                break;
            }
        }
        if ( !$hasUUID )
        {
            // All the database tables need to be rebuilt at this 
            // point
            $sql = file_get_contents( dirname(__FILE__) . DS . 'admin' . DS . $manifest->install->sql->file );
            jimport( 'joomla.installer.helper' );
            $queries = JInstallerHelper::splitSql( $sql );
            if ( count( $queries ) > 0 )
            {
                foreach ( $queries as $query )
                {
                    $query = trim( $query );
                    if ( $query != '' && $query{0} != '#' )
                    {
                        $db->setQuery( $query );
                        $db->query();
                    }
                }
            }
            echo '<p>' . JText::sprintf( 'COM_WORDBRIDGE_UPDATED_DB' ) . '</p>';
        }
        echo '<p>' . JText::sprintf( 'COM_WORDBRIDGE_UPDATED_TO_VER', htmlspecialchars( $manifest->version->data() ) ) . '</p>';
    }

    function install( $parent )
    {
        $manifest = $parent->get( 'manifest' );
        $parent2 = $parent->getParent();
        $source = $parent2->getPath( 'source' );
        $plugin_names = $this->_installPlugins( $manifest, $source );
        $lang   = JFactory::getLanguage();
        $lang->load( $this->component_base.'.sys', $source.DS.'admin', $lang->getDefault(), false, false );

        $db = JFactory::getDbo();
        $tableExtensions = $db->nameQuote( '#__extensions' );
        $columnElement   = $db->nameQuote( 'element' );
        $columnType      = $db->nameQuote( 'type' );
        $columnEnabled   = $db->nameQuote( 'enabled' );

        foreach ( $plugin_names as $plugin )
        {
            $db->setQuery(
                "UPDATE $tableExtensions SET $columnEnabled=1 WHERE 
                $columnElement='$plugin' AND $columnType='plugin'" );
            $db->query();
        }
        echo JText::_( 'COM_WORDBRIDGE_INSTALLED' );
    }

    function uninstall( $parent ) 
    {
        $plugins = array();

        $xml = simplexml_load_file( JPATH_COMPONENT_ADMINISTRATOR.DS.'..'.DS.$this->component_base.DS.substr( $this->component_base, 4 ).'.xml' );
        if ( $xml )
        {
            foreach ( $xml->xpath('/extension/plugins/plugin') as $plugin )
            {
                $attributes = $plugin->attributes();
                $plugins[] = array( ''.$attributes->group, ''.$attributes->plugin );
            }
        }
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
            $installer = new JInstaller();
            foreach ( $installed_plugins as $plugin_id )
            {
                $installer->uninstall( 'plugin', $plugin_id );
            }

        }
        echo '<p>' . JText::_( 'COM_WORDBRIDGE_UNINSTALL_TEXT' ) . '</p>';
    }

    function preflight( $type, $parent ) 
    {
        $manifest = $parent->get( 'manifest' );
        $parent2 = $parent->getParent();
        $source = $parent2->getPath( 'source' );
        $lang   = JFactory::getLanguage();
        $lang->load( $this->component_base.'.sys', $source.DS.'admin', $lang->getDefault(), false, false );

        // Make sure JSON is installed
        if ( $type == 'install' )
        {
            if ( !function_exists( 'curl_init' ) )
            {
                Jerror::raiseWarning( null, JText::_( 'COM_WORDBRIDGE_NO_CURL'  ) );
                return false;
            }
            if ( !class_exists( 'DOMDocument' ) )
            {
                Jerror::raiseWarning( null, JText::_( 'COM_WORDBRIDGE_NO_DOM' ) );
                return false;
            }
        }
    }

    function _installPlugins( $manifest, $source, $upgrade = false )
    {
        $installer = new JInstaller();
        $plugin_names = array();
        // Install plugins
        foreach ( $manifest->plugins->plugin as $plugin )
        {
            $attributes = $plugin->attributes();
            $plg = $source . DS . $attributes['folder'] . DS . $attributes['plugin'];
            if ( $upgrade )
            {
                $installer->update( $plg );
            }
            else
            {
                $installer->install( $plg );
            }
            $plugin_names[] = $attributes['plugin'];
        }
        return $plugin_names;
    }
}
