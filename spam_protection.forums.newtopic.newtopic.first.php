<?php
/* ====================
[BEGIN_SED_EXTPLUGIN]
Code=spam_protection
Part=admin
File=spam_protection.forums.newtopic.newtopic.first
Hooks=forums.newtopic.newtopic.first
Tags=
Order=99
[END_SED_EXTPLUGIN]
==================== */
defined('SED_CODE') or die('Wrong URL');
$spam_service = str_replace(' ', '_', strtolower($cfg['plugin']['spam_protection']['spam_service']));

if($spam_service!='none' && $cfg['plugin']['spam_protection']['filter_forums']=='Yes') {
	
	$newtopictitle = sed_import('newtopictitle','P','TXT', 255);
	$newtopicdesc = sed_import('newtopicdesc','P','TXT', 255);
	$newprvtopic = sed_import('newprvtopic','P','BOL');
	$newmsg = sed_import('newmsg','P','HTM');
	$newprvtopic = (!$fs_allowprvtopics) ? 0 : $newprvtopic;
	
	$is_really_privatetopic = $newprvtopic;
	
	$spam_service_loader_dir = $cfg['plugins_dir'].'/spam_protection/adapters/'.$spam_service.'.php';
	include_once $spam_service_loader_dir;
		
	$spam_protection_result = array('is_spam' => FALSE);
	$spam_protection_result = spam_protection_check( 
		array(
			'type' => 'forum',
			'title' => $newtopictitle,
			'content' => $newmsg,
			'description' => $newtopicdesc,
			'author' => $usr['name'],
			'author_ip' => $usr['ip'],
			'author_email' => $usr['profile']['user_email'],
			'referrer' => $sys['referer']
		) 
	);

	$newmsg = (isset($spam_protection_result['data']['content'])) ? $spam_protection_result['data']['content'] : $newmsg;
	$newtopictitle = (isset($spam_protection_result['data']['title'])) ? $spam_protection_result['data']['title'] : $newtopictitle;
	$newtopicdesc = (isset($spam_protection_result['data']['description'])) ? $spam_protection_result['data']['description'] : $newtopicdesc;
	$newtopicpreview = mb_substr(htmlspecialchars($newmsg), 0, 128);
	
	if($spam_protection_result['is_spam']) {
		$_POST['newprvtopic'] = 1;
				
		$spam_data = array(
			'fs_masterid' => $fs_masterid,
			'fs_countposts' => $fs_countposts,
			'fs_autoprune' => $fs_autoprune,
			'ft_state' => 0,
			'ft_mode' => (int)$is_really_privatetopic,
			'ft_sticky' => 0,
			'ft_sectionid' => (int)$s,
			'ft_title' => $newtopictitle, 
			'ft_desc' => $newtopicdesc,
			'ft_preview' => $newtopicpreview,
			'ft_creationdate' => (int)$sys['now_offset'],
			'ft_updated' =>  (int)$sys['now_offset'],
			'ft_postcount' => 1,
			'ft_viewcount' => 0,
			'ft_firstposterid' => (int)$usr['id'],
			'ft_firstpostername' => $usr['name'],
			'ft_lastposterid' => (int)$usr['id'],
			'ft_lastpostername' => $usr['name'],
			'fp_posterid' => (int)$usr['id'],
			'fp_posterip' => $usr['ip'],
			'fp_text' => $newmsg,
		);

		if($poll) {
			sed_poll_check();
			
			$spam_data += array(
				'poll_id' => $poll_id,
				'poll_text' => $poll_text,
				'poll_option_id' => $poll_option_id,
				'poll_multiple' => $poll_multiple,
				'poll_state' => $poll_state,
				'poll_option_text' => $poll_option_text
			);       
		}
		
		$spam_signature = isset($spam_protection_result['signature']) ? $spam_protection_result['signature'] : '';
		
		if($cfg['plugin']['spam_protection']['keep_forum_post_order']=='No') {
	
			$spam_data = serialize($spam_data);
			$db_spam_protection = (empty($db_spam_protection)) ? $db_x."spam_protection" : $db_spam_protection;
			
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
		else {
			// forces update counts to be skipped
			$fs_countposts = FALSE;
			$fs_masterid = 0;
			$fs_allowprvtopics = 1;
			$_POST['newprvtopic'] = 1;
			$newprvtopic = 1; // to skip updating forum section latest post
		}
	}	
	
}

