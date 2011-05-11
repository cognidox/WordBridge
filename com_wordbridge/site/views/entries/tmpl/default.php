<?php
/**
 * @version     $Id$
 * @package     Joomla
 * @subpackage  Wordbridge
 * @copyright   Copyright (C) 2010 Cognidox Ltd
 * @license  GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

defined('_JEXEC') or die( 'Restricted access' );
require_once( JPATH_COMPONENT.DS.'helpers'.DS.'helper.php' );

?>
<?php if ( $this->params->get( 'show_page_title', 1 ) ) : ?>
        <div class="componentheading<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
        <?php echo sprintf( '<a href="%s">%s</a>',
                            JRoute::_( $this->blogLink ),
                            $this->escape($this->params->get( 'page_title' ) ) ); ?>
        </div>
<?php endif; ?>
<?php if ( !empty( $this->blogTitle ) ): ?>
    <?php echo $this->escape( $this->blogTitle ); ?>
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
        <div class="wordbridge_content">
        <?php 
            if ( $this->params->get( 'wordbridge_show_links' ) == 'no' )
            {
                $br_pos = strrpos( $entry['content'], '<br />' );
                if ( $br_pos > 0 )
                {
                    echo substr( $entry['content'], 0, $br_pos );
                }
                else
                {
                    echo $entry['content'];
                }
            }
            else
            {
                echo $entry['content']; 
            }
        ?>
        </div>

        <?php if ( !empty( $entry['categories'] ) ): ?>
        <div class="wordbridge_categories">
        <?php
            $categoryLinkList = array();
            foreach ( $entry['categories'] as $category )
            {
                $slug = WordbridgeHelper::nameToSlug( $category );
                $categoryLinkList[] = sprintf( '<a href="%s" class="wordbridge_category">%s</a>',
                                               $this->blogLink . '&c=' .
                                               $slug . '&view=category' .
                                               '&name=' . urlencode( $category ),
                                               $this->escape( $category ) );
            }
            echo JText::_( 'COM_WORDBRIDGE_POSTED_IN' ). ': <span class="wordbridge_categories">' .
                 implode( ', ', $categoryLinkList ) . '</span>';
        ?>
        </div>
        <?php endif; ?>
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
