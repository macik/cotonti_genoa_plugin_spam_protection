
CREATE TABLE IF NOT EXISTS `sed_spam_protection` (
  `sp_id` int(11) NOT NULL AUTO_INCREMENT,
  `sp_type` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `sp_subtype` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `sp_userid` int(11) NOT NULL,
  `sp_userip` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `sp_username` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `sp_spamservice` varchar(35) COLLATE utf8_unicode_ci NOT NULL,
  `sp_signature` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `sp_content` text COLLATE utf8_unicode_ci NOT NULL,
  `sp_useremail` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `sp_referrer` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sp_date` int(11) NOT NULL,
  `sp_status` tinyint(1) NOT NULL,
  `sp_data` blob,
  PRIMARY KEY (`sp_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;