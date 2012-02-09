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
    <div class="wordbridge_entry">
        <h2 class="wordbridge_title contentheading<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
            <?php echo $this->escape( $this->title ); ?>
        </h2>
        <span class="wordbridge_date"><?php echo WordbridgeHelper::wordBridgeStrftime( '%B %e, %Y', $this->date, true ); ?></span>
        <div class="wordbridge_content">
        <?php 
            if ( $this->params->get( 'wordbridge_show_links' ) == 'no' )
            {
                $br_pos = strrpos( $this->content, '<br />' );
                if ( $br_pos > 0 )
                {
                    echo substr( $this->content, 0, $br_pos );
                }
                else
                {
                    echo $this->content;
                }
            }
            else
            {
                echo $this->content;
            }
        ?>
        </div>
        <?php if ( !empty( $this->categories ) ): ?>
        <div class="wordbridge_categories">
        <?php
            $categoryLinkList = array();
            foreach ( $this->categories as $category )
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
</div>
