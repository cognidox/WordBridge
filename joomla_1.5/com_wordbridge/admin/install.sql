DROP TABLE IF EXISTS `#__com_wordbridge_blogs`;
DROP TABLE IF EXISTS `#__com_wordbridge_cache`;
DROP TABLE IF EXISTS `#__com_wordbridge_pages`;
DROP TABLE IF EXISTS `#__com_wordbridge_posts`;
DROP TABLE IF EXISTS `#__com_wordbridge_post_categories`;
DROP TABLE IF EXISTS `#__com_wordbridge_blog_categories`;
DROP TABLE IF EXISTS `#__com_wordbridge_blog_tags`;

CREATE TABLE `#__com_wordbridge_blogs` (
    `blog_id` INT(11) unsigned NOT NULL,
    `blog_name` VARCHAR(200) NOT NULL DEFAULT '',
    `description` text NOT NULL DEFAULT '',
    `last_post` text NOT NULL DEFAULT '',
    `updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`blog_id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

CREATE TABLE `#__com_wordbridge_cache` (
    `id` INT(11) unsigned NOT NULL auto_increment,
    `blog_id` INT(11) unsigned NOT NULL,
    `statuses_count` INT(11) unsigned NOT NULL,
    `last_post_id` INT(11) unsigned NOT NULL,
    `page_num` INT(11) unsigned NOT NULL,
    PRIMARY KEY (`id`),
    KEY `page_lookup` (`blog_id`, `statuses_count`, `last_post_id`, `page_num`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

CREATE TABLE `#__com_wordbridge_pages` (
    `cache_id` INT(11) unsigned NOT NULL,
    `post_order` INT(11) unsigned NOT NULL,
    `post_id` INT(11) unsigned NOT NULL,
    PRIMARY KEY (`cache_id`, `post_order`, `post_id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

CREATE TABLE `#__com_wordbridge_posts` (
    `post_id` INT(11) unsigned NOT NULL,
    `blog_id` INT(11) unsigned NOT NULL,
    `title` text NOT NULL,
    `content` longtext NOT NULL,
    `post_date` datetime NOT NULL default '0000-00-00 00:00:00',
    `slug` varchar(200) NOT NULL default '',
    PRIMARY KEY (`post_id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

CREATE TABLE `#__com_wordbridge_post_categories` (
    `post_id` INT(11) unsigned NOT NULL,
    `blog_id` INT(11) unsigned NOT NULL,
    `category` varchar(200) NOT NULL DEFAULT '',
    PRIMARY KEY (`post_id`, `blog_id`, `category`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

CREATE TABLE `#__com_wordbridge_blog_categories` (
    `blog_id` INT(11) unsigned NOT NULL,
    `category` varchar(200) NOT NULL DEFAULT '',
    PRIMARY KEY (`blog_id`, `category`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

CREATE TABLE `#__com_wordbridge_blog_tags` (
    `blog_id` INT(11) unsigned NOT NULL,
    `tag` varchar(200) NOT NULL DEFAULT '',
    PRIMARY KEY (`blog_id`, `tag`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

