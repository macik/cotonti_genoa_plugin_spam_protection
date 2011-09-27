<?php
/* ====================
[BEGIN_SED_EXTPLUGIN]
Code=spam_protection
Part=comments
File=spam_protection.comments.send.new
Hooks=comments.send.new
Tags=
Order=99
[END_SED_EXTPLUGIN]
==================== */
defined('SED_CODE') or die('Wrong URL');
/**
 * Keep ID order hook
 */
if($spam_protection_result['is_spam'] && $cfg['plugin']['spam_protection']['filter_comments']=='Yes' && 
	$cfg['plugin']['spam_protection']['keep_comment_order']=='Yes') {
	
	global $db_x;
	$db_spam_protection = (empty($db_spam_protection)) ? $db_x."spam_protection" : $db_spam_protection;
	/** 
	 * Comment needs to be deleted so it can keep its place but not show up
	 * in any feeds
	 */
	sed_sql_query("DELETE FROM $db_com WHERE com_id='".(int)$id."'");
	
	if($code_reset) {
		$code = 'p'.$code;
	}

	$spam_data['com_id'] = (int)$id;
	$spam_data = serialize($spam_data);

	sed_sql_query("INSERT INTO $db_spam_protection (sp_userid, sp_userip, sp_username, sp_spamservice, ".
	"sp_content, sp_useremail, sp_referrer, sp_date, sp_status, sp_signature, sp_data, sp_type) VALUES (".
	"'".(int)$usr['id']."', '".sed_sql_prep($usr['ip'])."', '".sed_sql_prep($usr['name'])."', '".sed_sql_prep($spam_service)."', ".
	"'".sed_sql_prep($rtext)."', '".sed_sql_prep($usr['profile']['user_email'])."', '".sed_sql_prep($sys['referer'])."', 
	'".(int)$sys['now_offset']."', '0', '".sed_sql_prep($spam_signature)."', '".sed_sql_prep($spam_data)."', 'comment')"
	);
	
	sed_shield_update(20, 'New comment');
	header('Location: ' . SED_ABSOLUTE_URL . str_replace('&amp;', '&', $url));
	exit;
}


?>