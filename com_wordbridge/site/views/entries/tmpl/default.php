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
<?php if ( !empty( $this->title ) ): ?>
    <?php echo $this->escape( $this->title ); ?>
<?php endif; ?>
<div class="wordbridge_entries">
    <?php foreach ($this->entries as $entry): ?>
    <div class="wordbridge_entry">
        <h2 class="wordbridge_title">
            <?php echo sprintf( '<a href="%s">%s</a>', 
                        JRoute::_( $this->blogLink . '&p=' . $entry['postid'] .
                                   '&slug=' . $entry['slug'] . '&view=entry' ),
                        $this->escape( $entry['title'] ) ); ?>
        </h2>
        <span class="wordbridge_date"><?php echo( strftime( '%B %e, %Y', $entry['date'] ) ); ?></span>
        <?php echo $entry['content']; ?>
    <?php endforeach; ?>

    <?php if ( !empty( $this->olderLink ) || !empty( $this->newerLink ) ): ?>
        <div class="wordbridge_nav">
            <?php if ( !empty( $this->olderLink ) ): ?>
                <span class="wordbridge_older">
                    <?php echo sprintf( '<a href="%s">%s</a>',
                                        JRoute::_( $this->olderLink ),
                                        JText::_( 'COM_WORDBRIDGE_OLDER_ENTRIES' ) ); ?>
                </span>
            <?php endif; ?>
            <?php if ( !empty( $this->newerLink ) ): ?>
                <span class="wordbridge_newer">
                    <?php echo sprintf( '<a href="%s">%s</a>',
                                        JRoute::_( $this->newerLink ),
                                        JText::_( 'COM_WORDBRIDGE_NEWER_ENTRIES' ) ); ?>
                </span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
