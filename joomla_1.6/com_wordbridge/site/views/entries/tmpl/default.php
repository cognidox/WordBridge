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
<div class="wordbridge_blog blog<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
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
    <div class="wordbridge_entries">
        <?php if ($this->entries && count($this->entries)) {
            foreach ($this->entries as $entry): ?>
            <div class="wordbridge_entry">
                <h2 class="wordbridge_title contentheading<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
                <?php echo sprintf( '<a href="%s">%s</a>', 
                        JRoute::_( $this->blogLink . '&p=' . $entry['postid'] .
                                   '&slug=' . $entry['slug'] . '&view=entry' ),
                        $this->escape( $entry['title'] ) ); ?>
                </h2>
                <span class="wordbridge_date"><?php echo WordbridgeHelper::wordBridgeStrftime( '%B %e, %Y', $entry['date'], true ); ?></span>
                <div class="wordbridge_content">
                <?php 
                    $blogContent = $entry['content'];
                    if ( $this->params->get( 'wordbridge_show_links' ) == 'no' )
                    {
                        $br_pos = strrpos( $entry['content'], '<br />' );
                        if ( $br_pos > 0 )
                        {
                            $blogContent = substr( $entry['content'], 0, $br_pos );
                        }
                    }
                    // Look for more-link
                    if ( preg_match( '/^(.+?)<span\s+id="more-(\d+)"><\/span>(.*)/is', $blogContent, $matches ) )
                    {
                        $blogContent = $matches[1];
                        $blogContent .= sprintf( '<a href="%s#more-%s">%s</a>',
                                                 JRoute::_( $this->blogLink . '&p=' . $entry['postid'] .
                                                            '&slug=' . $entry['slug'] . '&view=entry' ),
                                                 $matches[2], JText::_( 'COM_WORDBRIDGE_READ_THE_REST' ) );
                        // Care needs to be taken to allow closing tags from 
                        // earler in the content to close.
                        // Strip closed block elements, which should just
                        // leave closing elements
                        $parts = preg_split( '/(<[^>]+>)/s', $matches[3], -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE );
                        $tags = array();
                        $tag_index = array();
                        foreach ( $parts as $part )
                        {
                            // Skip non HTML things
                            if ( strpos( $part, '<' ) !== 0 )
                            {
                                continue;
                            }
                            // Skip self closing elements
                            if ( preg_match( '/\/>$/', $part ) )
                                continue;
                            // Skip elements that should be self closed,
                            // but are often not
                            if ( preg_match( '/^<(hr|br|img|input|meta|link|basefont|base|area|col|frame|param)(\s|>)/is', $part ) )
                                continue;

                            preg_match( '/^<\/?(\w+)/', $part, $pmatch );
                            $tag = strtolower( $pmatch[1] );
                            if ( strpos( $part, '/' ) === 1 )
                            {
                                // This is a closing element - if we have the
                                // opening element in tag_index, we can
                                // trim the tag list
                                if ( array_key_exists( $tag, $tag_index ) )
                                {
                                    $pos = array_pop( $tag_index[$tag] );
                                    $tags = array_slice( $tags, 0, $pos );
                                    if ( count( $tag_index[$tag] ) == 0 )
                                        unset( $tag_index[$tag] );
                                }
                                else
                                {
                                    // Add the closing tag to the tags list
                                    $tags[] = $part;
                                }
                            }
                            else
                            {
                                // This is an openining element - we add
                                // it to the tags list, and store the index
                                // position
                                if ( !array_key_exists( $tag, $tag_index ) )
                                    $tag_index[$tag] = array();
                                array_push( $tag_index[$tag], count( $tags ) );
                                $tags[] = $part;
                            }
                        }
                        $blogContent .= implode( '', $tags );
                    }
                    echo $blogContent;
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
            </div>
        <?php endforeach; } ?>

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
</div>
