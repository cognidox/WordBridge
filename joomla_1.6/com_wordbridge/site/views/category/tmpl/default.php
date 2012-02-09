<?php
/**
 * @version     $Id$
 * @package  Wordbridge
 * @copyright   Copyright (C) 2010 Cognidox Ltd
 * @license  GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

defined('_JEXEC') or die( 'Restricted access' );
require_once( JPATH_COMPONENT.DS.'helpers'.DS.'helper.php' );

?>
<div class="wordbridge_blog_header">
    <?php if ( $this->params->get( 'show_page_heading', 1 ) ) : ?>
        <h2><span class="componentheading<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
        <?php echo sprintf( '<a href="%s">%s</a>',
                            JRoute::_( $this->blogLink ),
                            $this->escape( $this->blog_title ) ); ?>
        </span></h2>
        <?php if ( !empty( $this->blogTitle ) ): ?>
            <?php echo $this->escape( $this->blogTitle ); ?>
        <?php endif; ?>
    <?php endif; ?>
</div>
<div class="wordbridge_categories">
    <table class="wordbridge_category_table" width="100%">
        <thead>
            <tr>
                <th colspan="2">
                    <?php echo JText::sprintf( 
                        $this->isTag ? 'COM_WORDPRESS_TAG_TITLE' : 'COM_WORDPRESS_CATEGORY_TITLE', $this->escape( $this->categoryName ) ); ?>
                </th>
            </tr>
        </thead>
        <tbody>
        <?php 
            $i = 0;
            if ( $this->entries != null ):
                foreach( $this->entries as $entry ): 
        ?>
            <tr class="<?php echo ($i++ % 2) ? "even" : "odd"; ?>">
                <td class="wordbridge_col1"><?php echo WordbridgeHelper::wordBridgeStrftime( '%e.%m.%y', $entry['date'], true ); ?></td>
                <td class="wordbridge_col2"><?php echo sprintf( '<a href="%s">%s</a>',
                                        JRoute::_( $this->blogLink . 
                                            '&p=' . $entry['postid'] .
                                            '&slug=' . $entry['slug'] . 
                                            '&view=entry' ),
                                        $this->escape( $entry['title'] ) ); ?></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>

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
