<?php
/* ====================
[BEGIN_SED_EXTPLUGIN]
Code=spam_protection
Part=forums
File=spam_protection.comments.send.first
Hooks=comments.send.first
Tags=
Order=99
[END_SED_EXTPLUGIN]
==================== */
defined('SED_CODE') or die('Wrong URL');

$spam_service = str_replace(' ', '_', strtolower($cfg['plugin']['spam_protection']['spam_service']));

if($spam_service!='none' && $cfg['plugin']['spam_protection']['filter_comments']=='Yes') {
	$spam_service_loader_dir = $cfg['plugins_dir'].'/spam_protection/adapters/'.$spam_service.'.php';
	
	include_once $spam_service_loader_dir;
		
	$spam_protection_result = spam_protection_check( 
		array(
			'type' => 'comment',
			'content' => $rtext,
			'author' => $usr['name'],
			'author_ip' => $usr['ip'],
			'author_email' => $usr['profile']['user_email'],
			'referrer' => $sys['referer']
		) 
	);
	
	$spam_protection_result = (isset($spam_protection_result['is_spam'])) ? $spam_protection_result : array('is_spam' => FALSE);
	$rtext = (isset($spam_protection_result['data']['content'])) ? $spam_protection_result['data']['content'] : $rtext;
	
	if($spam_protection_result['is_spam']) {
		
		$spam_data = array(
			'com_code' => $code,
			'com_author' => $usr['name'],
			'com_authorid' => (int)$usr['id'],
			'com_authorip' => $usr['ip'],
			'com_text' => $rtext,
			'com_date' => $sys['now_offset'],		
		);
		$spam_signature = isset($spam_protection_result['signature']) ? $spam_protection_result['signature'] : '';
	
		if($cfg['plugin']['spam_protection']['keep_comment_order']=='No') {
		
			global $db_x;
			$db_spam_protection = (empty($db_spam_protection)) ? $db_x."spam_protection" : $db_spam_protection;
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
		if($cfg['plugin']['spam_protection']['keep_comment_order']=='Yes') {
			
			// Reset the comment code for pages so that it doesn't update the table yet
			if (mb_substr($code, 0, 1) =='p') {
				$code_reset = TRUE;
				$code = mb_substr($code, 1);
			}			
		}
	}
}

?>