<script type="text/javascript">
<!--
window.addEvent('domready',function() {
    var wp_link_class = 'wordbridge_wp_link';
    var wp_link_map = { 'comments' : '<?php echo JText::_( 'COM_WORDBRIDGE_LINK_ADD_COMMENT' ); ?>', 'facebook' : '<?php echo JText::_( 'COM_WORDBRIDGE_LINK_ADD_FACEBOOK' ); ?>',
                        'twitter' : '<?php echo JText::_( 'COM_WORDBRIDGE_LINK_ADD_TWEET' ); ?>', 'stumble' : '<?php echo JText::_( 'COM_WORDBRIDGE_LINK_ADD_STUMBLE' ); ?>', 'digg' : '<?php echo JText::_( 'COM_WORDBRIDGE_LINK_ADD_DIGG' ); ?>',
                        'reddit': '<?php echo JText::_( 'COM_WORDBRIDGE_LINK_ADD_REDDIT' ); ?>' };
    var wp_link_str = 'https?://feeds.wordpress.com/1.0/(';
    for (var k in wp_link_map) { wp_link_str += k + '|'; }
    var wp_link_re = new RegExp(wp_link_str.substr(0, wp_link_str.length - 1) + ')/');
    var wp_link_set_idx = 0;
    $$('div.wordbridge_content img').each(function(img){
        var wp_link_match = img.src.match(wp_link_re);
        if (wp_link_match != null) {
            var el = new Element('span', { html: wp_link_map[wp_link_match[1]] });
            el.replaces(img);
            var link = el.getParent();
            link.addClass(wp_link_class);
            if (!link.getParent().hasClass('wordbridge_converted_links')) {
                link.getParent().addClass('wordbridge_converted_links');
                wp_link_set_idx++;
                var container = new Element("span", { id: 'wordbridge_wp_link_set_' + wp_link_set_idx, class: 'wordbridge_link_set' }).inject(link, "before");
                link.inject(container, 'bottom');
            } else {
                $('wordbridge_wp_link_set_' + wp_link_set_idx).grab(link, 'bottom');
            }
        }
    });
});
// -->
</script>
