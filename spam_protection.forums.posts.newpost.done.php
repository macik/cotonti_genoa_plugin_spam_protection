<?php 
/* ====================
[BEGIN_SED_EXTPLUGIN]
Code=spam_protection
Part=admin
File=spam_protection.forums.posts.newpost.done
Hooks=forums.posts.newpost.done
Tags=
Order=99
[END_SED_EXTPLUGIN]
==================== */
defined('SED_CODE') or die('Wrong URL');

if($spam_protection_result['is_spam'] && $cfg['plugin']['spam_protection']['filter_forums']=='Yes' &&
	$cfg['plugin']['spam_protection']['keep_forum_post_order']=='Yes') {

	$spam_data['fp_id'] = (int)$p;
	sed_sql_query("DELETE FROM $db_forum_posts WHERE fp_id='".(int)$p."'");
	
	sed_sql_query("UPDATE $db_forum_topics SET ft_postcount=ft_postcount-1, ft_updated='".sed_sql_prep($last_stats['ft_updated'])."', ".
	"ft_lastposterid='".sed_sql_prep($last_stats['ft_lasterposterid'])."', ft_lastpostername='".sed_sql_prep($last_stats['ft_lastpostername'])."' WHERE ft_id='$q'");

	sed_sql_query("UPDATE $db_forum_sections SET fs_postcount=fs_postcount-1 WHERE fs_id='".(int)$s."'");
	sed_sql_query("UPDATE $db_users SET user_postcount=user_postcount-1 WHERE user_id='".(int)$usr['id']."'");
	
	$spam_data = serialize($spam_data);
	$db_spam_protection = (empty($db_spam_protection)) ? $db_x."spam_protection" : $db_spam_protection;	
	
	// Replaces message body with profanity filtered text ( if defensio and profanity enabled )
	$newmsg = (isset($spam_protection_result['data']['content'])) ? $spam_protection_result['data']['content'] : $newmsg;
	
	sed_sql_query("INSERT INTO $db_spam_protection (sp_userid, sp_userip, sp_username, sp_spamservice, ".
	"sp_content, sp_useremail, sp_referrer, sp_date, sp_status, sp_signature, sp_data, sp_type, sp_subtype) VALUES (".
	"'".(int)$usr['id']."', '".sed_sql_prep($usr['ip'])."', '".sed_sql_prep($usr['name'])."', '".sed_sql_prep($spam_service)."', ".
	"'".sed_sql_prep($newmsg)."', '".sed_sql_prep($usr['profile']['user_email'])."', '".sed_sql_prep($sys['referer'])."', 
	'".(int)$sys['now_offset']."', '0', '".sed_sql_prep($spam_signature)."', '".sed_sql_prep($spam_data)."', 'forum', 'post')"
	);	
	
	sed_shield_update(30, "New post");
	header("Location: " . SED_ABSOLUTE_URL . sed_url('forums', array('m' => 'posts', 'q' => $q, 'n' => 'last'), '#bottom', true));
	exit;	
}