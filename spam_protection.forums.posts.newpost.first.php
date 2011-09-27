<?php
/* ====================
[BEGIN_SED_EXTPLUGIN]
Code=spam_protection
Part=forums
File=spam_protection.forums.posts.newpost.first
Hooks=forums.posts.newpost.first
Tags=
Order=99
[END_SED_EXTPLUGIN]
==================== */
defined('SED_CODE') or die('Wrong URL');

$spam_service = str_replace(' ', '_', strtolower($cfg['plugin']['spam_protection']['spam_service']));

if($spam_service!='none' && $cfg['plugin']['spam_protection']['filter_forums']=='Yes' && !$merge) {
	$spam_service_loader_dir = $cfg['plugins_dir'].'/spam_protection/adapters/'.$spam_service.'.php';
	include_once $spam_service_loader_dir;
	
	$newmsg = sed_import('newmsg','P','HTM');
			
	$spam_protection_result = spam_protection_check(
		array(
			'type' => 'forum',
			'content' => $newmsg,
			'author' => $usr['name'],
			'author_ip' => $usr['ip'],
			'author_email' => $usr['profile']['email'],
			'referrer' => $sys['referer'],
		)
	);
	
	if($spam_protection_result['is_spam']) {
		
		if($cfg['plugin']['spam_protection']['kee_forum_post_order']=='Yes') {
			$sql = sed_sql_query("SELECT ft_updated, ft_lastposterid, ft_lastpostername FROM $db_forum_topics WHERE ft_id='".(int)$q."' LIMIT 1");
			$last_stats = sed_sql_fetchassoc($sql);
			
			$fs_masterid = 0;
			$fs_countposts = 0;
		}
	
		if($cfg['parser_cache'])
		{
			$rhtml = sed_sql_prep(sed_parse(htmlspecialchars($newmsg), $cfg['parsebbcodeforums'] && $fs_allowbbcodes, $cfg['parsesmiliesforums'] && $fs_allowsmilies, 1));
		}
		else
		{
			$rhtml = '';
		}	
		
		$spam_data = array(
			'fp_topicid' => (int)$q,
			'fp_sectionid' => (int)$s,
			'fp_posterid' => (int)$usr['id'],
			'fp_postername' => $usr['name'],
			'fp_creation' => (int)$sys['now_offset'],
			'fp_updated' => (int)$sys['now_offset'],
			'fp_updater' => 0,
			'fp_text' => $newmsg,
			'fp_html' => $rhtml,
			'fp_posterip' => $usr['ip'],
			'fs_masterid' => $fs_masterid,
			'fs_countposts' => $fs_countposts
		);

		$spam_signature = isset($spam_protection_result['signature']) ? $spam_protection_result['signature'] : '';
		
		if($cfg['plugin']['spam_protection']['keep_forum_post_order']=='No') {
			$spam_data = serialize($spam_data);
			$db_spam_protection = (empty($db_spam_protection)) ? $db_x."spam_protection" : $db_spam_protection;
			
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
	}

}