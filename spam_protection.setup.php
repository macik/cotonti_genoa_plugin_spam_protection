<?php 
/* ====================
[BEGIN_SED_EXTPLUGIN]
Code=spam_protection
Name=Spam Protection
Description=Protects against spam with multiple different spam APIs
Version=1.0
Date=2011-aug-11
Author=Xerora
Copyright=
Notes=
SQL=
Auth_guests=R
Lock_guests=W12345A
Auth_members=RW
Lock_members=12345A
[END_SED_EXTPLUGIN]

[BEGIN_SED_EXTPLUGIN_CONFIG]
spam_service=01:select:None,Akismet,TypePad AntiSpam,Defensio:None:Spam service
spam_service_api_key=02:string:::Spam service API key
force_all_as_spam=03:select:Yes,No:No:Send all items to spam collection no matter what?
filter_comments=04:select:Yes,No:Yes:Filter comment spam ?
filter_forums=05:select:Yes,No:No:Filter forum spam ?
tool_content_display_length=06:string::50:Content display length in moderation tool
filter_profanity_comments=07:select:Yes,No:No:Enable profanity filter on comments (Defensio only) ?
filter_profanity_forums=08:select:Yes,No:No:Enable profanity filter on forums (Defensio only) ?
keep_comment_order=09:select:Yes,No:Yes:Keep comment order ?
keep_forum_post_order=091:select:Yes,No:Yes:Keep forum post order ?
tool_items_per_page=092:select:5,10,15,20,25,30,35,40,45,50,55,60:15:Spam items per page for tool 
tool_prompt_actions=093:select:Yes,No:Yes:Prompt when ever taking database altering actions in the tool ? 
prune_spam_time=094:select:Never,1,2,3,4,5,6,7,8,9,10,15,20,25,30,60,90:Never:Prune spam from database after how many days ?
[END_SED_EXTPLUGIN_CONFIG]

==================== */
defined('SED_CODE') or die('Wrong URL');

if($action=='install') {
	$db_spam_protection = (empty($db_spam_protection)) ? $db_x."spam_protection" : $db_spam_protection;
	$query = "
		CREATE TABLE IF NOT EXISTS $db_spam_protection (
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
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;";
	sed_sql_query($query);
}

?>