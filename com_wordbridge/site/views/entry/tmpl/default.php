<?php
/**
 * @version     $Id$
 * @package     Joomla
 * @subpackage  Wordbridge
 * @copyright   Copyright (C) 2010 Cognidox Ltd
 * @license  GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

defined('_JEXEC') or die( 'Restricted access' );

?>
<?php if ( $this->params->get( 'show_page_title', 1 ) ) : ?>
        <div class="componentheading<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
        <?php echo sprintf( '<a href="%s">%s</a>',
                            JRoute::_( $this->blogLink ),
                            $this->escape($this->params->get( 'page_title' ) ) ); ?>
        </div>
<?php endif; ?>
    <div class="wordbridge_entry">
        <h2 class="wordbridge_title">
            <?php echo $this->escape( $this->title ); ?>
        </h2>
        <span class="wordbridge_date"><?php echo( strftime( '%B %e, %Y', $this->date ) ); ?></span>
        <?php echo $this->content; ?>
    </div>
