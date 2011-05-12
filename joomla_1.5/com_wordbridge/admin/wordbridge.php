<?php
/**
 * @version     $Id$
 * @package  Wordbridge
 * @copyright   Copyright (C) 2011 Cognidox Ltd
 * @license  GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Require the com_content helper library
require_once (JPATH_COMPONENT.DS.'controller.php');

// Create the controller
$controller = new WordbridgeController( );

// Perform the Request task
$controller->execute( JRequest::getVar( 'task' ) );

// Redirect if set by the controller
$controller->redirect();

