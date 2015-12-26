-- phpMyAdmin SQL Dump
-- version 2.7.0-pl2
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Dec 26, 2006 at 12:55 AM
-- Server version: 5.0.27
-- PHP Version: 5.1.6
-- 
-- Database: `morg2`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `getid3_bitrate_mode`
-- 

CREATE TABLE `getid3_bitrate_mode` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `getid3_bitrate_mode`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `getid3_channel_mode`
-- 

CREATE TABLE `getid3_channel_mode` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `getid3_channel_mode`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `getid3_comment`
-- 

CREATE TABLE `getid3_comment` (
  `file_id` int(11) NOT NULL default '0',
  `field_id` int(11) NOT NULL default '0',
  `value_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`file_id`,`field_id`,`value_id`),
  KEY `field_id` (`field_id`),
  KEY `value_id` (`value_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `getid3_comment`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `getid3_directory`
-- 

CREATE TABLE `getid3_directory` (
  `id` int(11) NOT NULL auto_increment,
  `root_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `artwork` enum('yes','no') NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `filename` (`filename`),
  KEY `root_id` (`root_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `getid3_directory`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `getid3_encoder_options`
-- 

CREATE TABLE `getid3_encoder_options` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `getid3_encoder_options`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `getid3_encoder_version`
-- 

CREATE TABLE `getid3_encoder_version` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `getid3_encoder_version`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `getid3_field`
-- 

CREATE TABLE `getid3_field` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `getid3_field`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `getid3_file`
-- 

CREATE TABLE `getid3_file` (
  `id` int(11) NOT NULL auto_increment,
  `directory_id` int(11) NOT NULL,
  `filename` varchar(254) NOT NULL,
  `filemtime` int(11) NOT NULL default '0',
  `filesize` int(11) NOT NULL default '0',
  `format_name_id` int(11) default NULL,
  `encoder_version_id` int(11) default NULL,
  `encoder_options_id` int(11) default NULL,
  `bitrate_mode_id` int(11) default NULL,
  `channel_mode_id` int(11) default NULL,
  `sample_rate` int(11) default NULL,
  `bits_per_sample` int(11) default NULL,
  `channels` tinyint(4) default NULL,
  `lossless` tinyint(4) default NULL,
  `playtime` float default NULL,
  `avg_bit_rate` float default NULL,
  `replaygain_track_gain` float default NULL,
  `replaygain_album_gain` float default NULL,
  `md5_data` varchar(32) character set ascii collate ascii_bin default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `filename` (`directory_id`,`filename`),
  KEY `format_name_id` (`format_name_id`),
  KEY `encoder_version_id` (`encoder_version_id`),
  KEY `encoder_options_id` (`encoder_options_id`),
  KEY `bitrate_mode_id` (`bitrate_mode_id`),
  KEY `channel_mode_id` (`channel_mode_id`),
  KEY `md5_data` (`md5_data`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=41076 ;

-- 
-- Dumping data for table `getid3_file`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `getid3_format_name`
-- 

CREATE TABLE `getid3_format_name` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `mime_type` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `getid3_format_name`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `getid3_value`
-- 

CREATE TABLE `getid3_value` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) character set utf8 collate utf8_bin NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `getid3_value`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `morg_charset`
-- 

CREATE TABLE `morg_charset` (
  `charset` varchar(20) NOT NULL default '',
  `description` varchar(200) NOT NULL default '',
  PRIMARY KEY  (`charset`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `morg_charset`
-- 

INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('CP1250', 'Microsoft Windows Codepage 1250 (EE)');
INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('CP1251', 'Microsoft Windows Codepage 1251 (Cyrillic)');
INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('CP1252', 'Microsoft Windows Codepage 1252 (ANSI)');
INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('CP1253', 'Microsoft Windows Codepage 1253 (Greek)');
INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('CP1254', 'Microsoft Windows Codepage 1254 (Turkish)');
INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('CP1257', 'Microsoft Windows Codepage 1257 (Baltic)');
INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('CP850', 'MS-DOS Codepage 850 (Multilingual Latin 1)');
INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('CP866', 'MS-DOS Codepage 866 (Russia)');
INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('ISO-8859-1', 'Latin1 (Western Europe)');
INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('ISO-8859-10', 'Latin6 (Scandinavia)');
INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('ISO-8859-13', 'Latin7 (Baltic)');
INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('ISO-8859-14', 'Latin8 (Celtic)');
INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('ISO-8859-15', 'Latin9 (Euro)');
INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('ISO-8859-16', 'Latin10 (South-Eastern European)');
INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('ISO-8859-2', 'Latin2 (Central-Eastern (non-Cyrillic) Europe)');
INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('ISO-8859-3', 'Latin3 (Esperanto, Maltese, and Turkish)');
INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('ISO-8859-4', 'Latin4 (Baltic)');
INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('ISO-8859-5', '(Cyrillic)');
INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('ISO-8859-7', '(Greek)');
INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('ISO-8859-9', 'Latin5 (Turkish)');
INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('KOI8-R', '(Cyrillic)');
INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('KOI8-RU', 'RELCOM KOI-8R (Cyrillic)');
INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('KOI8-U', 'Ukrainian KOI-8U (Cyrillic)');
INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('MacCentralEurope', 'Apple Macintosh');
INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('MacCroatian', 'Apple Macintosh');
INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('MacCyrillic', 'Apple Macintosh');
INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('MacGreek', 'Apple Macintosh');
INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('MacIceland', 'Apple Macintosh');
INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('Macintosh', 'Apple Macintosh');
INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('MacRoman', 'Apple Macintosh');
INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('MacRomania', 'Apple Macintosh');
INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('MacTurkish', 'Apple Macintosh');
INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('MacUkraine', 'Apple Macintosh');
INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('UTF-16', 'UNICODE');
INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('UTF-16BE', 'UNICODE');
INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('UTF-16LE', 'UNICODE');
INSERT INTO `morg_charset` (`charset`, `description`) VALUES ('UTF-8', 'UNICODE');

-- --------------------------------------------------------

-- 
-- Table structure for table `morg_collation`
-- 

CREATE TABLE `morg_collation` (
  `collation` varchar(100) NOT NULL default '',
  `description` varchar(200) NOT NULL,
  PRIMARY KEY  (`collation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `morg_collation`
-- 

INSERT INTO `morg_collation` (`collation`, `description`) VALUES ('utf8_czech_ci', 'Czech');
INSERT INTO `morg_collation` (`collation`, `description`) VALUES ('utf8_danish_ci', 'Danish');
INSERT INTO `morg_collation` (`collation`, `description`) VALUES ('utf8_estonian_ci', 'Estonian');
INSERT INTO `morg_collation` (`collation`, `description`) VALUES ('utf8_general_ci', 'General');
INSERT INTO `morg_collation` (`collation`, `description`) VALUES ('utf8_hungarian_ci', 'Hungarian');
INSERT INTO `morg_collation` (`collation`, `description`) VALUES ('utf8_icelandic_ci', 'Icelandic');
INSERT INTO `morg_collation` (`collation`, `description`) VALUES ('utf8_latvian_ci', 'Latvian');
INSERT INTO `morg_collation` (`collation`, `description`) VALUES ('utf8_lithuanian_ci', 'Lithuanian');
INSERT INTO `morg_collation` (`collation`, `description`) VALUES ('utf8_polish_ci', 'Polish');
INSERT INTO `morg_collation` (`collation`, `description`) VALUES ('utf8_romanian_ci', 'Romanian');
INSERT INTO `morg_collation` (`collation`, `description`) VALUES ('utf8_roman_ci', 'Roman');
INSERT INTO `morg_collation` (`collation`, `description`) VALUES ('utf8_slovak_ci', 'Slovak');
INSERT INTO `morg_collation` (`collation`, `description`) VALUES ('utf8_slovenian_ci', 'Slovene');
INSERT INTO `morg_collation` (`collation`, `description`) VALUES ('utf8_spanish2_ci', 'Spanish (traditional)');
INSERT INTO `morg_collation` (`collation`, `description`) VALUES ('utf8_spanish_ci', 'Spanish (modern)');
INSERT INTO `morg_collation` (`collation`, `description`) VALUES ('utf8_swedish_ci', 'Swedish');
INSERT INTO `morg_collation` (`collation`, `description`) VALUES ('utf8_turkish_ci', 'Turkish');
INSERT INTO `morg_collation` (`collation`, `description`) VALUES ('utf8_unicode_ci', 'German');

-- --------------------------------------------------------

-- 
-- Table structure for table `morg_config`
-- 

CREATE TABLE `morg_config` (
  `pkey` enum('pkey') NOT NULL default 'pkey',
  `password` varchar(32) character set utf8 collate utf8_bin NOT NULL,
  `server_cp` varchar(20) NOT NULL default 'utf-8',
  `client_type` enum('local_fs','lan_windows','lan_unix','http') NOT NULL,
  `client_cp` varchar(20) NOT NULL default 'utf-8',
  `client_params` mediumtext,
  `locale` varchar(20) NOT NULL default 'en_GB',
  `playlist_format` enum('m3u','winamp','foobar2000') NOT NULL default 'm3u',
  `toc_columns` tinyint(4) NOT NULL default '4',
  `browser_behaviour` enum('standard','maximize') NOT NULL default 'standard',
  `assume1` enum('yes','no') NOT NULL default 'yes',
  `assume2` enum('yes','no') NOT NULL default 'yes',
  `assume3` enum('yes','no') NOT NULL default 'yes',
  `assume4` enum('yes','no') NOT NULL default 'yes',
  `assume5` enum('yes','no') NOT NULL default 'yes',
  `assume6` enum('yes','no') NOT NULL default 'yes',
  `assume7` enum('yes','no') NOT NULL default 'yes',
  `category_prefix` char(3) NOT NULL default '@',
  PRIMARY KEY  (`pkey`),
  KEY `client_cp` (`client_cp`),
  KEY `server_cp` (`server_cp`),
  KEY `locale` (`locale`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `morg_config`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `morg_config_auth`
-- 

CREATE TABLE `morg_config_auth` (
  `range` varchar(15) NOT NULL,
  PRIMARY KEY  (`range`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `morg_config_auth`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `morg_config_hidden_fields`
-- 

CREATE TABLE `morg_config_hidden_fields` (
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `morg_config_hidden_fields`
-- 

INSERT INTO `morg_config_hidden_fields` (`name`) VALUES ('charset');

-- --------------------------------------------------------

-- 
-- Table structure for table `morg_config_key_fields`
-- 

CREATE TABLE `morg_config_key_fields` (
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `morg_config_key_fields`
-- 

INSERT INTO `morg_config_key_fields` (`name`) VALUES ('artist');
INSERT INTO `morg_config_key_fields` (`name`) VALUES ('title');

-- --------------------------------------------------------

-- 
-- Table structure for table `morg_config_root`
-- 

CREATE TABLE `morg_config_root` (
  `id` int(11) NOT NULL auto_increment,
  `server_path` varchar(255) NOT NULL default '',
  `client_path` varchar(255) NOT NULL default '',
  `flag` enum('none','delete') NOT NULL default 'none',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `client_path` (`client_path`),
  UNIQUE KEY `server_path` (`server_path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `morg_config_root`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `morg_locale`
-- 

CREATE TABLE `morg_locale` (
  `locale` varchar(20) NOT NULL default '',
  `description` varchar(200) NOT NULL,
  PRIMARY KEY  (`locale`),
  UNIQUE KEY `description` (`description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `morg_locale`
-- 

INSERT INTO `morg_locale` (`locale`, `description`) VALUES ('ca_ES', 'Catalan');
INSERT INTO `morg_locale` (`locale`, `description`) VALUES ('hr_HR', 'Croatian');
INSERT INTO `morg_locale` (`locale`, `description`) VALUES ('cs_CZ', 'Czech');
INSERT INTO `morg_locale` (`locale`, `description`) VALUES ('da_DK', 'Danish');
INSERT INTO `morg_locale` (`locale`, `description`) VALUES ('nl_NL', 'Dutch');
INSERT INTO `morg_locale` (`locale`, `description`) VALUES ('en_US', 'English (American)');
INSERT INTO `morg_locale` (`locale`, `description`) VALUES ('en_GB', 'English (British)');
INSERT INTO `morg_locale` (`locale`, `description`) VALUES ('et_EE', 'Estonian');
INSERT INTO `morg_locale` (`locale`, `description`) VALUES ('fi_FI', 'Finnish');
INSERT INTO `morg_locale` (`locale`, `description`) VALUES ('fr_FR', 'French');
INSERT INTO `morg_locale` (`locale`, `description`) VALUES ('gl_ES', 'Galician');
INSERT INTO `morg_locale` (`locale`, `description`) VALUES ('de_DE', 'German');
INSERT INTO `morg_locale` (`locale`, `description`) VALUES ('el_GR', 'Greek');
INSERT INTO `morg_locale` (`locale`, `description`) VALUES ('hu_HU', 'Hungarian');
INSERT INTO `morg_locale` (`locale`, `description`) VALUES ('is_IS', 'Icelandic');
INSERT INTO `morg_locale` (`locale`, `description`) VALUES ('it_IT', 'Italian');
INSERT INTO `morg_locale` (`locale`, `description`) VALUES ('lv_LV', 'Latvian');
INSERT INTO `morg_locale` (`locale`, `description`) VALUES ('lt_LT', 'Lithuanian');
INSERT INTO `morg_locale` (`locale`, `description`) VALUES ('nb_NO', 'Norwegian (BokmÃ¥l)');
INSERT INTO `morg_locale` (`locale`, `description`) VALUES ('nn_NO', 'Norwegian (Nynorsk)');
INSERT INTO `morg_locale` (`locale`, `description`) VALUES ('pl_PL', 'Polish');
INSERT INTO `morg_locale` (`locale`, `description`) VALUES ('pt_PT', 'Portuguese');
INSERT INTO `morg_locale` (`locale`, `description`) VALUES ('ro_RO', 'Romanian');
INSERT INTO `morg_locale` (`locale`, `description`) VALUES ('ru_RU', 'Russian');
INSERT INTO `morg_locale` (`locale`, `description`) VALUES ('sk_SK', 'Slovak');
INSERT INTO `morg_locale` (`locale`, `description`) VALUES ('sl_SI', 'Slovene');
INSERT INTO `morg_locale` (`locale`, `description`) VALUES ('es_ES', 'Spanish');
INSERT INTO `morg_locale` (`locale`, `description`) VALUES ('sv_SE', 'Swedish');
INSERT INTO `morg_locale` (`locale`, `description`) VALUES ('tr_TR', 'Turkish');

-- 
-- Constraints for dumped tables
-- 

-- 
-- Constraints for table `getid3_comment`
-- 
ALTER TABLE `getid3_comment`
  ADD CONSTRAINT `getid3_comment_ibfk_13` FOREIGN KEY (`field_id`) REFERENCES `getid3_field` (`id`),
  ADD CONSTRAINT `getid3_comment_ibfk_14` FOREIGN KEY (`value_id`) REFERENCES `getid3_value` (`id`),
  ADD CONSTRAINT `getid3_comment_ibfk_9` FOREIGN KEY (`file_id`) REFERENCES `getid3_file` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 
-- Constraints for table `getid3_directory`
-- 
ALTER TABLE `getid3_directory`
  ADD CONSTRAINT `getid3_directory_ibfk_1` FOREIGN KEY (`root_id`) REFERENCES `morg_config_root` (`id`) ON DELETE CASCADE;

-- 
-- Constraints for table `getid3_file`
-- 
ALTER TABLE `getid3_file`
  ADD CONSTRAINT `getid3_file_ibfk_12` FOREIGN KEY (`directory_id`) REFERENCES `getid3_directory` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `getid3_file_ibfk_13` FOREIGN KEY (`format_name_id`) REFERENCES `getid3_format_name` (`id`),
  ADD CONSTRAINT `getid3_file_ibfk_14` FOREIGN KEY (`encoder_version_id`) REFERENCES `getid3_encoder_version` (`id`),
  ADD CONSTRAINT `getid3_file_ibfk_15` FOREIGN KEY (`encoder_options_id`) REFERENCES `getid3_encoder_options` (`id`),
  ADD CONSTRAINT `getid3_file_ibfk_16` FOREIGN KEY (`bitrate_mode_id`) REFERENCES `getid3_bitrate_mode` (`id`),
  ADD CONSTRAINT `getid3_file_ibfk_17` FOREIGN KEY (`channel_mode_id`) REFERENCES `getid3_channel_mode` (`id`);

-- 
-- Constraints for table `morg_config`
-- 
ALTER TABLE `morg_config`
  ADD CONSTRAINT `morg_config_ibfk_1` FOREIGN KEY (`server_cp`) REFERENCES `morg_charset` (`charset`),
  ADD CONSTRAINT `morg_config_ibfk_2` FOREIGN KEY (`client_cp`) REFERENCES `morg_charset` (`charset`),
  ADD CONSTRAINT `morg_config_ibfk_3` FOREIGN KEY (`locale`) REFERENCES `morg_locale` (`locale`);
