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

function com_uninstall()
{
    $plugins = array(
                array('search', 'wordbridge'),
                );

    $where = array();
    foreach ( $plugins as $plugin )
    {
        $where[] = vsprintf("(folder='%s' AND element='%s')", $plugin);
    }

    $query = 'SELECT id FROM #__plugins WHERE '.implode( ' OR ', $where );

    $dbo = JFactory::getDBO();
    $dbo->setQuery($query);
    $tmp = $dbo->loadResultArray();
    $plugins = array();
    foreach ( $tmp as $plugin )
    {
        $plugins[$plugin] = 0;
    }

    $model = JModel::getInstance( 'Plugins', 'InstallerModel' );
    $model->remove( $plugins );
    return true;
}

?>
