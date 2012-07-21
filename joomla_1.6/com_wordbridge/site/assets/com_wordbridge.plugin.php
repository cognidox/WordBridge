<?php

// JComments plugin for WordBridge
// http://www.cognidox.com/opensource/wordbridge-wordpress-to-joomla-bridge
// (c) 2012 Cognidox Ltd

class jc_com_wordbridge extends JCommentsPlugin
{
 
    function getObjectTitle( $id ) {
        // Data load from database by given id 
        $db = & JFactory::getDBO();

        // Need to get the blog uuid based on the current menu item
        $params = false;
        if ( version_compare( JVERSION, '1.6.0', 'lt' ) )
        {
            $mainframe = &JFactory::getApplication();
            $params = &$mainframe->getParams();
        }
        else
        {
            $app = JFactory::getApplication();
            $menu = $app->getMenu();
            $item = $menu->getActive();
            if ( !$item )
            {
                $item = $menu->getItem( JCommentsPlugin::getItemid( 'com_wordbridge' ) );
            }
            $params = $item->params;
        }
        require_once( JPATH_COMPONENT.DS.'helpers'.DS.'helper.php' );
        $blogInfo = WordbridgeHelper::getBlogByName( $params->get( 'wordbridge_blog_name' ) );
        $db->setQuery( sprintf( "SELECT title FROM #__com_wordbridge_posts WHERE blog_uuid='%s' AND post_id = %d", $db->quote( $blogInfo['uuid'] ), $id % 10000000 ) );
        return $db->loadResult();
    }
 
    function getObjectLink( $id ) {
        // Itemid meaning of our component
        $_Itemid = JCommentsPlugin::getItemid( 'com_wordbridge' );
 
        // url link creation for given object by id 
        $link = JRoute::_( 'index.php?option=com_wordbridge&task=view&p='. $id .'&Itemid='. $_Itemid );
        return $link;
    }
 
    function getObjectOwner( $id ) {
        $db = & JFactory::getDBO();
        $db->setQuery( sprintf( "SELECT id, %s FROM #__user_usergroup_map AS map LEFT JOIN #__usergroups AS ug ON map.group_id = ug.id LEFT JOIN #__users AS u ON map.user_id = u.id WHERE u.block = 0 AND ug.id = 8 LIMIT 1", $db->quote( 'admin' ), $id ) );
        return $db->loadResult();
    }
}

