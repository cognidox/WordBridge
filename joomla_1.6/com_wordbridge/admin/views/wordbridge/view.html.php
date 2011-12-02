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
require_once( JPATH_SITE.DS.'components'.DS.'com_wordbridge'.DS.'helpers'.DS.'helper.php' );

class WordbridgeViewWordbridge extends JView
{
    /**
     * Wordbridge entry view display method
     * @return void
     **/
    function display($tpl = null)
    {
        JHTML::_( 'stylesheet', 'admin.css', 'media/com_wordbridge/css/' );
        JToolBarHelper::title( JText::_( 'COM_WORDBRIDGE' ), 'wordbridge' );
        $document = JFactory::getDocument();
        $document->setTitle( JText::_( 'COM_WORDBRIDGE' ) );

        $model = $this->getModel();
        $stats = $model->getBlogStats();
        $this->assignRef( 'stats', $stats );

        parent::display($tpl);
    }
}
