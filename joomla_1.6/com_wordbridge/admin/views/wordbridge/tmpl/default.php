<?php
/**
 * @version     $Id$
 * @package     Wordbridge
 * @copyright   Copyright (C) 2011 Cognidox Ltd
 * @license  GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

defined('_JEXEC') or die( 'Restricted access' );
require_once( JPATH_SITE.DS.'components'.DS.'com_wordbridge'.DS.'helpers'.DS.'helper.php' );

?>
<h2><?php echo JText::_( 'COM_WORDBRIDGE' ); ?></h2>

<?php if ( count( $this->stats ) ): ?>
    <p>
    <?php echo JText::_( 'COM_WORDBRIDGE_BRIDGE_MSG' ); ?>
    </p>
    <?php foreach ( $this->stats as $blog ): ?>
        <?php echo sprintf( '<h3><a href="http://%s/" target="_blank">%s</a></h3>', $this->escape( WordbridgeHelper::fqdnBlogName( $blog->blog_name ) ), $this->escape( WordbridgeHelper::fqdnBlogName( $blog->blog_name ) ) ); ?>
        <blockquote id="wordbridge_blog_<?php echo $blog->blog_name; ?>">
        <span class="wordbridge_updated"><?php echo JText::sprintf( 'COM_WORDBRIDGE_LAST_UPDATED', 
                ( $blog->updated == null ? JText::_( 'COM_WORDBRIDGE_NEVER' ) :
                   WordbridgeHelper::wordBridgeStrftime( '%c', $blog->updated, true ) ) ); ?></span><br />
        <?php
        // Truncate the last post value, so it doesn't swamp the page
        $last_post = '';
        if ( strlen( $blog->last_post ) > 51 )
        {
            $last_post = $this->escape( substr( $blog->last_post, 0, 50 ) ) . '&#0133;';
        }
        else
        {
            $last_post = $this->escape( $blog->last_post );
        }
        ?>
        <span class="wordbridge_last_post"><?php echo JText::sprintf( 'COM_WORDBRIDGE_LAST_POST',
                    $last_post ); ?></span><br />
        <span class="wordbridge_cached_pages"><?php echo JText::sprintf( 'COM_WORDBRIDGE_CACHED_PAGES',
                    $blog->page_count  ); ?></span><br />
        <span class="wordbridge_cached_posts"><?php echo JText::sprintf( 'COM_WORDBRIDGE_CACHED_POSTS',
                    $blog->post_count  ); ?></span><br />
        <?php echo JText::_( 'COM_WORDBRIDGE_USED_MENUS' ); ?><br /><ul>
        <?php foreach ( $blog->menus as $menu ): ?>
            <li>
            <?php echo $this->escape( $menu->title ); ?> -
            <?php echo sprintf( '<a href="%s">%s</a>',
                                JRoute::_( 'index.php?option=com_menus&task=item.edit&id=' . $menu->id ), JText::_( 'COM_WORDBRIDGE_ADMIN' ) ); ?> /
            <?php echo sprintf( '<a href="%s" target="_blank">%s</a>',
                                JRoute::_( JURI::root() . $menu->link . '&Itemid=' . $menu->id ), JText::_( 'COM_WORDBRIDGE_SITE' ) ); ?>
            </li>
        <?php endforeach; ?></ul>
        </blockquote>
    <?php endforeach; ?>
<?php else: ?>
    <?php echo JText::_( 'COM_WORDBRIDGE_NOTHING_BRIDGED' ); ?>
<?php endif; ?>


