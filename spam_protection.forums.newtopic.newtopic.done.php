<?php
/* ====================
[BEGIN_SED_EXTPLUGIN]
Code=spam_protection
Part=forums
File=spam_protection.forums.newtopic.newtopic.done
Hooks=forums.newtopic.newtopic.done
Tags=
Order=99
[END_SED_EXTPLUGIN]
==================== */
defined('SED_CODE') or die('Wrong URL');

if($spam_protection_result['is_spam'] && $cfg['plugin']['spam_protection']['filter_forums']=='Yes' && 
	$cfg['plugin']['spam_protection']['keep_forum_post_order']=='Yes') {

	$db_spam_protection = (empty($db_spam_protection)) ? $db_x."spam_protection" : $db_spam_protection;
		
	$spam_data['ft_id'] = $q;
	$spam_data = serialize($spam_data);				
		
	sed_sql_query("DELETE FROM $db_forum_topics WHERE ft_id='".(int)$q."'");
	sed_sql_query("DELETE FROM $db_forum_posts WHERE fp_topicid='".(int)$q."'");
	sed_sql_query("UPDATE $db_forum_sections SET fs_postcount=fs_postcount-1, fs_topiccount=fs_topiccount-1 WHERE fs_id='".(int)$s."'");	

	$newmsg = (isset($spam_protection_result['data']['content'])) ? $spam_protection_result['data']['content'] : $newmsg;
	
	sed_sql_query("INSERT INTO $db_spam_protection (sp_userid, sp_userip, sp_username, sp_spamservice, ".
	"sp_content, sp_useremail, sp_referrer, sp_date, sp_status, sp_signature, sp_data, sp_type, sp_subtype) VALUES (".
	"'".(int)$usr['id']."', '".sed_sql_prep($usr['ip'])."', '".sed_sql_prep($usr['name'])."', '".sed_sql_prep($spam_service)."', ".
	"'".sed_sql_prep($newmsg)."', '".sed_sql_prep($usr['profile']['user_email'])."', '".sed_sql_prep($sys['referer'])."', 
	'".(int)$sys['now_offset']."', '0', '".sed_sql_prep($spam_signature)."', '".sed_sql_prep($spam_data)."', 'forum', 'topic')"
	);
	
	sed_shield_update(45, "New topic");
	header("Location: " . SED_ABSOLUTE_URL . sed_url('forums', array('m' => 'topics', 's' => (int)$s), NULL, TRUE));
	exit;
}