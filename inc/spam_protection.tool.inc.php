<?php
defined('SED_CODE') or die('Wrong URL');

DEFINE('DEBUG_DONT_SEND', FALSE);
$db_spam_protection = (empty($db_spam_protection)) ? $db_x."spam_protection" : $db_spam_protection;

function spam_protection_display_empty_queue_message($count = 0) {
	global $t;
	if($count==0) {
		$t->assign('MESSAGE', $L['plu_empty_message']);
		$t->parse("MAIN.VIEW_FORUM_SPAM.EMPTY_MESSAGE");
	}
}

function spam_protection_get_spam_count($service, $type) {
	global $db_spam_protection;
	$sql_total_spam = sed_sql_query("SELECT COUNT(*) FROM $db_spam_protection WHERE sp_spamservice='".sed_sql_prep($service)."' AND sp_type='".sed_sql_prep($type)."'");
	$total_spam = sed_sql_result($sql_total_spam, 0, "COUNT(*)");
	return (int)$total_spam;
}

function spam_protection_data_insert($table, array $data) {
	$columns = array();
	$values = array();
	$sql_completed = FALSE;
	if(is_array($data) && !empty($data)) {
		foreach($data as $field => $value) {
			$columns[] = $field;
			$values[] = "'".sed_sql_prep($value)."'";
		}
		$insertcolums = implode(',', $columns);
		$insertcolums = rtrim($insertcolums, ',');
		$insertvalues = implode(',', $values);
		$insertvalues = rtrim($insertvalues, ',');
		$sql_completed = sed_sql_query("INSERT INTO $table (".$insertcolums.") VALUES (".$insertvalues.")");
	}
	return $sql_completed;
}

function spam_protection_set_forum_post_as_ham($item, array $data) {
	global $db_forum_topics, $db_forum_posts, $db_forum_sections, $db_spam_protection;
	global $sys, $db_users;
	$postid = (isset($data['fp_id'])) ? $data['fp_id'] : '';
	$update_latest = FALSE;
	
	$sql_forum_latest = sed_sql_query("SELECT fs_id, fs_lt_date FROM $db_forum_sections WHERE fs_id='".(int)$data['fp_sectionid']."' LIMIT 1");
	$result = sed_sql_fetchassoc($sql_forum_latest);
	
	$sql_completed_post = spam_protection_data_insert($db_forum_posts, array(
		'fp_id' => $postid,
		'fp_topicid' => $data['fp_topicid'],
		'fp_sectionid' => $data['fp_sectionid'],
		'fp_posterid' => $data['fp_posterid'],
		'fp_postername' => $data['fp_postername'],
		'fp_creation' => $data['fp_creation'],
		'fp_updated' => $data['fp_updated'],
		'fp_updater' => 0,
		'fp_text' => $data['fp_text'],
		'fp_posterip' => $data['fp_posterip']
	));

	$p = sed_sql_insertid();
	
	if($data['fp_creation']>$result['fs_lt_date']) {
		$update_latest = TRUE;
	}	
	
	if($data['fs_countposts']=='1') {
		sed_sql_query("UPDATE $db_users SET user_postcount=user_postcount+1 WHERE user_id='".(int)$data['fp_posterid']."'");
	}
	
	sed_sql_query("UPDATE $db_forum_topics SET ft_postcount=ft_postcount+1, ft_updated='".(int)$sys['now_offset']."', ".
	"ft_lastposterid='".sed_sql_prep($data['fp_posterid'])."', ft_lastpostername='".sed_sql_prep($data['fp_postername'])."' ".
	"WHERE ft_id='".(int)$data['fp_topicid']."'");

	if($update_latest) {
		sed_forum_sectionsetlast($data['fp_sectionid']);		
	}
	
	sed_sql_query("UPDATE $db_forum_sections SET fs_postcount=fs_postcount+1 WHERE fs_id='".(int)$data['fp_sectionid']."'");
	if ((int)$data['fs_masterid']>0) sed_sql_query("UPDATE $db_forum_sections SET fs_postcount=fs_postcount+1 WHERE fs_id='".(int)$data['fs_masterid']."'");
	sed_sql_query("DELETE FROM $db_spam_protection WHERE sp_id='".(int)$item."'");
}

function spam_protection_set_forum_topic_as_ham($item, array $data) {
	global $db_spam_protection, $db_forum_topics, $db_forum_posts, $db_forum_sections;
	global $db_users;
	
	$topicid = (isset($data['ft_id']) && (int)$data['ft_id']>0) ? (int)$data['ft_id'] : '';
	
	$sql_completed_topic = spam_protection_data_insert($db_forum_topics, array(
		'ft_id' => $topicid,
		'ft_state' => 0,
		'ft_mode' => $data['ft_mode'],
		'ft_sticky' => 0,
		'ft_sectionid' => $data['ft_sectionid'],
		'ft_title' => $data['ft_title'],
		'ft_desc' => $data['ft_desc'],
		'ft_preview' => $data['ft_preview'],
		'ft_creationdate' => $data['ft_creationdate'],
		'ft_updated' => $data['ft_updated'],
		'ft_postcount' => 1,
		'ft_viewcount' => 0,
		'ft_firstposterid' => $data['ft_firstposterid'],
		'ft_firstpostername' => $data['ft_firstpostername'],
		'ft_lastposterid' => $data['ft_lastposterid'],
		'ft_lastpostername' => $data['ft_lastpostername'],
	));

	$newtopicid = (is_int($topicid) && $topicid>0) ? $topicid : sed_sql_insertid();		
	
	if(isset($data['poll_id'])) {
			
		$poll_id = $data['poll_id'];
		$poll_text = $data['poll_text'];
		$poll_option_id = $data['poll_option_id'];
		$poll_multiple = $data['poll_multiple'];
		$poll_state = $data['poll_state'];
		$poll_option_text = $data['poll_option_text'];
			
		sed_poll_save('forum', $newtopicid);
	}

	$sql_completed_post = spam_protection_data_insert($db_forum_posts, array(
		'fp_topicid' => $newtopicid,
		'fp_sectionid' => $data['ft_sectionid'],
		'fp_posterid' => $data['fp_posterid'],
		'fp_postername' => $data['ft_firstpostername'],
		'fp_creation' => $data['ft_creationdate'],
		'fp_updated' => $data['ft_updated'],
		'fp_text' => $data['fp_text'],
		'fp_posterip' => $data['fp_posterip'],
	));
	
	sed_sql_query("UPDATE $db_forum_sections SET fs_postcount=fs_postcount+1, ".
	"fs_topiccount=fs_topiccount+1 WHERE fs_id='".(int)$data['ft_sectionid']."'");		

	if ($data['fs_masterid']>0) { 
		sed_sql_query("UPDATE $db_forum_sections SET fs_postcount=fs_postcount+1, ".
		"fs_topiccount=fs_topiccount+1 WHERE fs_id='".(int)$data['fs_masterid']."'"); 
	}
	
	if ($data['fs_countposts']==1) { 
		sed_sql_query("UPDATE $db_users SET user_postcount=user_postcount+1 WHERE user_id='".(int)$data['fp_posterid']."'"); 
	}

    if($data['ft_mode']!=1) {
    	sed_forum_sectionsetlast($data['ft_sectionid']);
    }
	
    if($sql_completed_topic && $sql_completed_post) {
		sed_sql_query("DELETE FROM $db_spam_protection WHERE sp_id='".(int)$item."'");
		return TRUE;
    }
	
	return FALSE;
}

function spam_protection_set_comment_as_ham($item, array $data ) {
	global $db_spam_protection, $db_com, $db_pages;
	
	$comid = ((int)$data['com_id']>0) ? (int)$data['com_id'] : '';
	
	$insert_completed = spam_protection_data_insert($db_com, array(
		'com_id' => $comid,
		'com_code' => $data['com_code'],
		'com_author' => $data['com_author'],
		'com_authorid' => $data['com_authorid'],
		'com_authorip' => $data['com_authorip'],
		'com_text' => $data['com_text'],
		'com_date' => $data['com_date']
	)); 
									
	if($insert_completed) {
		if (mb_substr($data['com_code'], 0, 1) =='p')
		{
			$page_id = mb_substr($data['com_code'], 1, 11);
			$sql = sed_sql_query("UPDATE $db_pages SET page_comcount='".sed_get_comcount($data['com_code'])."' WHERE page_id='".(int)$page_id."'");
		}		
		sed_sql_query("DELETE FROM $db_spam_protection WHERE sp_id='".(int)$item."'");
	}	

	return $insert_completed;
}

function spam_protection_error_redirect($status) {
	sed_redirect(sed_url('admin', array('m' => 'tools', 'p' => 'spam_protection', 'action' => 'error_message', 'status' => $status), NULL, TRUE));
	exit();	
}

function spam_protection_action_check_key($activeservice) {
	global $t, $cfg;
	
	$keystatus = FALSE;

	switch($activeservice) {
		case 'akismet':
			try {
				$service = new Akismet(SED_ABSOLUTE_URL, $cfg['plugin']['spam_protection']['spam_service_api_key']);
				$keystatus = $service->isKeyValid();
			} catch (Exception $e) {
				$keystatus = FALSE;	
			}
		break;
		case 'typepad_antispam':
			try {
				$service = new Akismet(SED_ABSOLUTE_URL, $cfg['plugin']['spam_protection']['spam_service_api_key']);
				$service->setAkismetServer('api.antispam.typepad.com');
				$keystatus = $service->isKeyValid();
			} catch(Exception $e) {
				$keystatus = FALSE;	
			}
		break;
		case 'defensio':
			try {
				$service = new Defensio($cfg['plugin']['spam_protection']['spam_service_api_key']);
				$keystatus = ((int)array_shift($service->getUser())===200) ? TRUE : FALSE;

			} catch(Exception $e) {
				$keystatus = FALSE;
			}
		break;	
	}
	
	if($keystatus===TRUE) {
		$t->parse('MAIN.CHECK_KEY.IS_VALID');	
	} else {
		$t->parse('MAIN.CHECK_KEY.IS_INVALID');	
	}
	$t->parse("MAIN.CHECK_KEY");
}

function spam_protection_action_view_item($id) {
	global $L, $t, $db_spam_protection;
	if(empty($id) || !is_numeric($id)) {
		spam_protection_error_redirect('400');
	}
	
	$section = sed_import('section', 'G', 'ALP');
	$page = sed_import('page', 'G', 'INT');
	
	$sql = sed_sql_query("SELECT * FROM $db_spam_protection WHERE sp_id='".(int)$id."' LIMIT 1");
	$row = sed_sql_fetchassoc($sql);
	$data = unserialize($row['sp_data']);
	
	foreach($data as $name => $value) {
		if(!is_array($value)) {
			$t->assign(array(
				'SPAM_PROTECTION_DATA_NAME' => htmlspecialchars($name)."&nbsp;",
				'SPAM_PROTECTION_DATA_VALUE' => nl2br(htmlspecialchars(trim($value)))."&nbsp;",
			));
			$t->parse("MAIN.VIEW_SPAM_ITEM.LIST_SPAM_DATA");
		}
	}
	
	$toolurl = array('m' => 'tools', 'p' => 'spam_protection');
	$data_url_markas_ham = sed_url('admin', $toolurl + array('action' => 'mark_item_as', 'type' => 'ham', 'id' => (int)$row['sp_id']));
	$data_url_markas_spam = sed_url('admin', $toolurl + array('action' => 'mark_item_as', 'type' => 'spam', 'id' => (int)$row['sp_id']));	
	
	$goback_display = "hidden";
	if(!empty($section) && !empty($page)) {
		$goback_display = "visible";
		$goback = sed_url('admin', $toolurl + array('action' => 'view', 'section' => $section, 'page' => $page));
	} else {
		$goback = "#";
		$goback_display = "none";
	}
	
	$t->assign(array(
		'SPAM_PROTECTION_DATA_URL_MARKAS_HAM' => $data_url_markas_ham,
		'SPAM_PROTECTION_DATA_URL_MARKAS_SPAM' => $data_url_markas_spam,
		'SPAM_PROTECTION_DATA_GOBACK' => '<a style="display: '.$goback_display.';" href="'.$goback.'">'.$L['plu_sp_goback'].'</a>',
		'SPAM_PROTECTION_DATA_MARKAS_HAM_LINK' => '<a class="confirm_mark_as_ham" href="'.$data_url_markas_ham.'">'.$L['plu_sp_markas_ham'].'</a>',
		'SPAM_PROTECTION_DATA_MARKAS_SPAM_LINK' => '<a class="confirm_mark_as_spam" href="'.$data_url_markas_spam.'">'.$L['plu_sp_markas_spam'].'</a>',
	));
	
	$t->parse("MAIN.VIEW_SPAM_ITEM");
}

function spam_protection_action_view_error_message($status) {
	global $t, $L;
	
	$error_message = $L['plu_sp_error_message_'.$status];
	
	if(!empty($error_message)) {
		$t->assign(array(
			'SPAM_PROTECTION_ERROR_MESSAGE' => $error_message,
		));
		$t->parse("MAIN.ERROR_MESSAGE");
	}
}

function spam_protection_action_mark_item_as($active_service) {
	$id = sed_import('id', 'G', 'INT');
	$type = sed_import('type', 'G', 'ALP');
	$section = sed_import('section', 'G', 'ALP');
	$section = (!empty($section)) ? $section : SP_DEFAULT_SECTION;
	
	switch($active_service) { 
		case 'defensio':
			$service = spam_protection_setup_service_api_defensio();
			spam_protection_markas_defensio($service, $type, $id);
		break;
		case 'akismet':
			$service = spam_protection_setup_service_api_akismet();
			spam_protection_markas_akismet($service, $type, $id);
		break;
		case 'typepad_antispam':
			$service = spam_protection_setup_service_api_typepad();
			spam_protection_markas_typepad($service, $type, $id);
		break;
	}		
	sed_redirect(sed_url('admin', array('m' => 'tools', 'p' => 'spam_protection', 'section' => $section), NULL, TRUE));
}

function spam_protection_setup_service_api_defensio() {
	global $cfg;
	
	require_once $cfg['plugins_dir'].'/spam_protection/lib/defensio/Defensio.php';
	$status = FALSE;
	$service = new Defensio($cfg['plugin']['spam_protection']['spam_service_api_key']);
	try {
		$status = (int)array_shift($service->getUser());
	} catch(DefensioError $e) {
		$service = FALSE;		
		$status = FALSE;
	}
	
	$service = ($status!==FALSE) ? $service : $status;
	return $service;
}

function spam_protection_setup_service_api_akismet() {
	global $cfg;
	
	require_once $cfg['plugins_dir'].'/spam_protection/lib/akismet/akismet.php';
	$service = new Akismet(SED_ABSOLUTE_URL, $cfg['plugin']['spam_protection']['spam_service_api_key']);
	$status = FALSE;
	
	try {
		$status = $service->isKeyValid();
	} catch (Exception $e) {
		$service = FALSE;		
		$status = FALSE;
	}
	
	$service = ($status!==FALSE) ? $service : $status;
	return $service;
}

function spam_protection_setup_service_api_typepad() {
	global $cfg;
	
	require_once $cfg['plugins_dir'].'/spam_protection/lib/akismet/akismet.php';
	$service = new Akismet(SED_ABSOLUTE_URL, $cfg['plugin']['spam_protection']['spam_service_api_key']);
	$service->setAkismetServer('api.antispam.typepad.com');
	$status = FALSE;
	
	try {
		$status = $service->isKeyValid();
	} 
	catch(Exception $e) {
		$service = FALSE;		
		$status = FALSE;
	}
	
	$service = ($status!==FALSE) ? $service : $status;
	return $service;
}

function spam_protection_global_markas_spam($item) {
	global $db_spam_protection;
	sed_sql_query("DELETE FROM $db_spam_protection WHERE sp_id='".(int)$item."' LIMIT 1");
}

function spam_protection_update_section_database($result, $item, $data) {
	switch($result['sp_type']) {
		case 'comment':
			spam_protection_set_comment_as_ham($item, $data);
		break;
		case 'forum':
			switch($result['sp_subtype']) {
				case 'topic':
					require_once './system/core/polls/polls.functions.php';
					spam_protection_set_forum_topic_as_ham($item, $data);
				break;
				case 'post':
					spam_protection_set_forum_post_as_ham($item, $data);
				break;
			}
		break;
	}	
}

function spam_protection_markas_defensio($service, $markas, $item) {
	global $db_spam_protection;
	switch($markas) {
		case 'spam':
			spam_protection_global_markas_spam($item);
		break;
		case 'ham':
			$sql = sed_sql_query("SELECT * FROM $db_spam_protection WHERE sp_id='".(int)$item."' LIMIT 1");
			$result = sed_sql_fetchassoc($sql);
			$data = unserialize($result['sp_data']);
			
			if(!DEBUG_DONT_SEND) {
				try {
					$document_result = $service->putDocument($result['sp_signature'], array('allow' => 'true'));
				} catch(DefensioError $e) {
					// Continue no matter what
					$document_result = FALSE;									
				}
			}

			spam_protection_update_section_database($result, $item, $data);
			spam_protection_global_markas_spam($item);
		break;
	}
}

function spam_protection_markas_akismet($service, $markas, $item) {
	global $db_spam_protection;
	switch($markas) {
		case 'spam':
			spam_protection_global_markas_spam($item);
		break;
		case 'ham':
			$sql = sed_sql_query("SELECT * FROM $db_spam_protection WHERE sp_id='".(int)$item."' LIMIT 1");
			$result = sed_sql_fetchassoc($sql);
			$data = unserialize($result['sp_data']);

			if($service) {
				$service->setCommentType('comment');
				$service->setCommentContent($data['com_text']);
				$service->setCommentAuthor($result['sp_author']);
				$service->setCommentAuthorEmail($result['sp_useremail']);
				$service->setReferrer($result['sp_referrer']);
				$service->setUserIP($result['sp_userip']);
				
				if(!DEBUG_DONT_SEND) {
					try {
						$document_result = $service->submitHam();
					} catch(Exception $e) {
						$document_result = FALSE;
					}
				}				
			}			
			
			spam_protection_update_section_database($result, $item, $data);
			spam_protection_global_markas_spam($item);			
		break;
	}
}

function spam_protection_markas_typepad($service, $markas, $item) {
	global $db_spam_protection;
	switch($markas) {
		case 'spam':
			spam_protection_global_markas_spam($item);
		break;
		case 'ham':
			$sql = sed_sql_query("SELECT * FROM $db_spam_protection WHERE sp_id='".(int)$item."' LIMIT 1");
			$result = sed_sql_fetchassoc($sql);
			$data = unserialize($result['sp_data']);

			if($service) {
				$service->setCommentType('comment');
				$service->setCommentContent($data['com_text']);
				$service->setCommentAuthor($result['sp_author']);
				$service->setCommentAuthorEmail($result['sp_useremail']);
				$service->setReferrer($result['sp_referrer']);
				$service->setUserIP($result['sp_userip']);
				
				if(!DEBUG_DONT_SEND) {
					try {
						$document_result = $service->submitHam();
					} catch(Exception $e) {
						$document_result = FALSE;
					}
				}				
			}			
			
			spam_protection_update_section_database($result, $item, $data);
			spam_protection_global_markas_spam($item);			
		break;
	}
}

function spam_protection_action_with_selected() {
	global $cfg, $db_spam_protection, $db_com, $db_forum_posts, $db_forum_topics;
		
	$accept = array('mark_as_spam', 'mark_as_ham');
	$toolurl = array('m' => 'tools', 'p' => 'spam_protection');
	
	$section = sed_import('sp_section', 'P', 'ALP');
	$items = sed_import('sp_items', 'P', 'ARR');
	$with_selected = sed_import('sp_with_selected', 'P', 'SLU');

	asort($items, SORT_NUMERIC);

	if(empty($with_selected) || !in_array($with_selected, $accept) || !sed_check_xp() || count($items)==0) {
		sed_redirect(sed_url('admin', array('m' => 'tools', 'p' => 'spam_protection'), NULL, TRUE));
		exit();		
	}
	
	switch($with_selected) {
		case 'mark_as_spam':
			foreach($items as $item) {
				spam_protection_global_markas_spam($item);
			}
			sed_redirect(sed_url('admin', $toolurl + array('action' => 'view', 'section' => $section), NULL, TRUE));
		break;
		case 'mark_as_ham':
			$spam_service = str_replace(' ', '_', strtolower(trim($cfg['plugin']['spam_protection']['spam_service'])));
			switch($spam_service) {
				case 'defensio':
					$service = spam_protection_setup_service_api_defensio();
					foreach($items as $item) {
						spam_protection_markas_defensio($service, 'ham', $item);
					}
				break;
				case 'akismet':
					$service = spam_protection_setup_service_api_akismet();
					foreach($items as $item) {
						spam_protection_markas_akismet($service, 'ham', $item);
					} 
				break;
				case 'typepad_antispam':
					$service = spam_protection_setup_service_api_typepad(); 
					foreach($items as $item) {
						spam_protection_markas_typepad($service, 'ham', $item);
					}
 				break;
			}
			sed_redirect(sed_url('admin', $toolurl + array('action' => 'view', 'section' => $section), NULL, TRUE));
		break;
		
	}
}

function spam_protection_create_pagination( $section, $page, $total_spam ) {
	global $tool_path;
	$limit = SP_TOOL_ITEMS_PER_PAGE;
	$offset_page = $page-1;
	$total_pages = ($total_spam[$section]!=0) ? ceil($total_spam[$section]/$limit) : 0;
	$baseurl = array('m' => 'tools', 'p' => 'spam_protection', 'action' => 'view', $section => $section);
	
	if($offset_page!=0) {
		$prev_page = $page-1;
		$output = '<a style="text-decoration: underline;" href="'.sed_url('admin', $baseurl+array('page' => $prev_page)).'"><img src="'.$tool_path.'/img/arrow-left.png" /></a> &nbsp; ';
	}
	for($i=0; $total_pages>$i; $i++) {
		$page_out = $i+1;
		if($page_out!=$page) {
			$output .= '<a class="sp_spam_item_link" style="text-decoration: underline;" href="'.sed_url('admin', $baseurl+array('page' => $page_out)).'">'.$page_out.'</a> ';
		}
		else {
			$output .= '<b>'.$page_out.'</b> ';
		}
		if($page_out!=$total_pages) {
			$output .= '&nbsp;';
		}
	}
	
	if((int)$page!=(int)$total_pages) {
		$next_page = $page+1;
		$output .= ' &nbsp; <a style="text-decoration: underline;" href="'.sed_url('admin', $baseurl+array('page' => $next_page)).'"><img src="'.$tool_path.'/img/arrow-right.png" /></a> ';
	}
	return $output;
}

function spam_protection_display_with_selected( $section ) {
	global $L;
	$output = '<select name="sp_with_selected" id="sp_action_with_selected">';
	$output .= '<option value="null">'.$L['plu_sp_with_selected'].'</option>';
	$output .= '<option value="null">'.$L['plu_sp_select_sep'].'</option>';
	$output .= '<option value="mark_as_spam">'.$L['plu_sp_markas_spam'].'</option>';
	$output .= '<option value="mark_as_ham">'.$L['plu_sp_markas_ham'].'</option>';
	$output .= '</select>';
	return $output;
}

function spam_protection_action_view_section_spam( $section, $totalspam ) {
	global $t, $L, $cfg, $db_spam_protection;
	
	$page = sed_import('page', 'G', 'INT', 3);
	$order = sed_import('order', 'G', 'ALP', 15);
	$sort = strtoupper(sed_import('sort',  'G', 'ALP', 4));
	
	$sort_allowed = array('DESC', 'ASC');
	$order_allowed = array('id', 'username');
	$sort = (empty($sort) || !in_array($sort, $sort_allowed)) ? "DESC" : $sort;
	$order = (empty($order) || !in_array($order, $order_allowed)) ? "sp_id" : "sp_".$order;
	$section = trim(strtolower($section));
	$section = htmlentities($section);
	$section_upper = strtoupper($section);
	$spamservice = str_replace(' ', '_', trim($cfg['plugin']['spam_protection']['spam_service']));
	$page = !empty($page) ? (int)$page : 1;
	$realpage = $page;
	$limit = SP_TOOL_ITEMS_PER_PAGE;
	$page = ($page!=0) ? $page-1: 0;
	$offset = $page*$limit;

	if($totalspam[$section]==0) {
		$t->parse("MAIN.EMPTY_MESSAGE");
	}
	
	$sql = sed_sql_query("SELECT * FROM $db_spam_protection WHERE sp_type='".sed_sql_prep($section)."' AND ".
	"sp_spamservice='".sed_sql_prep($spamservice)."' ORDER BY ".sed_sql_prep($order)." ".sed_sql_prep($sort)." LIMIT ".(int)$offset.", ".(int)$limit."");
	$current_items_on_pages_count = 0;

	while($result = sed_sql_fetchassoc($sql)) {
		
		$content_display_length = (!empty($cfg['plugin']['spam_protection']['tool_content_display_length']) &&
			is_numeric($cfg['plugin']['spam_protection']['tool_content_display_length'])) ? 
			$cfg['plugin']['spam_protection']['tool_content_display_length'] : SP_TOOL_CONTENT_DISPLAY_LENGTH_DEFAULT;
		$content_sanitized = htmlspecialchars($result['sp_content']);
		
		if(strlen($result['sp_content']) > (int)$content_display_length) {
			$content_reduced = substr($content_sanitized, 0, $content_display_length)." ...";
		}
		else {
			$content_reduced = !empty($content_sanitized) ? $content_sanitized : '&nbsp;';
		}
		
		$username = htmlspecialchars($result['sp_username']);
		if(empty($username)) {
			$username = $L['Guest'];
		}
		$orderby_baseurl = array('m' => 'tools', 'p' => 'spam_protection', 'section' => $section, 'page' => $realpage);

		$t->assign(array(
			"SPAM_PROTECTION_SPAM_ID" => (int)$result['sp_id'],
			"SPAM_PROTECTION_SPAM_CHECKBOX" => '<input name="sp_items[]" value="'.(int)$result['sp_id'].'" type="checkbox" class="sp_checkbox checkbox" />',
			"SPAM_PROTECTION_SPAM_USERNAME" => $username,
			"SPAM_PROTECTION_SPAM_CONTENT" => $content_sanitized,
			"SPAM_PROTECTION_SPAM_CONTENT_REDUCED" => trim($content_reduced),
			"SPAM_PROTECTION_SPAM_VIEW_ITEM_URL" => sed_url('admin', array('m' => 'tools', 'p' => 'spam_protection', 'action' => 'view_item', 'id' => (int)$result['sp_id'], 'section' => $section, 'page' => $realpage)),
			"SPAM_PROTECTION_SPAM_MARKAS_SPAM_URL" => sed_url('admin', array('m' => 'tools', 'p' => 'spam_protection', 'action' => 'mark_item_as', 'type' => 'spam', 'id' => (int)$result['sp_id'])),
			"SPAM_PROTECTION_SPAM_MARKAS_HAM_URL" => sed_url('admin', array('m' => 'tools', 'p' => 'spam_protection', 'action' => 'mark_item_as', 'type' => 'ham', 'id' => (int)$result['sp_id'])),
			"SPAM_PROTECTION_SPAM_PAGINATION" => spam_protection_create_pagination($section, $realpage, $totalspam),
			"SPAM_PROTECTION_SPAM_ORDERBY_ID_ASC" => sed_url('admin', $orderby_baseurl+array('order' => 'id', 'sort' => 'asc')),
			"SPAM_PROTECTION_SPAM_ORDERBY_ID_DESC" => sed_url('admin', $orderby_baseurl+array('order' => 'id', 'sort' => 'desc')),
			"SPAM_PROTECTION_SPAM_ORDERBY_USERNAME_ASC" => sed_url('admin', $orderby_baseurl+array('order' => 'username', 'sort' => 'asc')),
			"SPAM_PROTECTION_SPAM_ORDERBY_USERNAME_DESC" => sed_url('admin', $orderby_baseurl+array('order' => 'username', 'sort' => 'desc')),		
		));
		$t->parse("MAIN.VIEW_SECTION_SPAM.LIST_SPAM");
		
		$current_items_on_pages_count++;
	}
	
	$t->assign(array(
		"SPAM_PROTECTION_FORM_START" => '<form id="sp_with_selected" name="sp_with_selected" method="post" action="'.sed_url('admin', array('m' => 'tools', 'p' => 'spam_protection', 'action' => 'with_selected')).'"><input type="hidden" name="sp_section" value="'.$section.'" />',
		"SPAM_PROTECTION_FORM_END" => '</form>',
		"SPAM_PROTECTION_SECTION_TITLE" => $L['plu_sp_section_'.$section.'_title'],
		"SPAM_PROTECTION_SPAM_LIMIT" => (int)$limit,
		"SPAM_PROTECTION_SPAM_TOTAL_COUNT" => (int)$totalspam[$section],
		"SPAM_PROTECTION_SPAM_ITEMS_SELECTED_COUNT" => (int)$current_items_on_pages_count,
		"SPAM_PROTECTION_SPAM_PAGE" => (int)$realpage,
		"SPAM_PROTECTION_WITH_SELECTED" => spam_protection_display_with_selected($section),
		"SPAM_PROTECTION_WITH_SELECTED_SUBMIT" => '<input id="with_selected_button" type="submit" value="'.$L['sp_Commit_action'].'" />',
	));
	
	if($totalspam[$section]>0) {
		$t->parse("MAIN.VIEW_SECTION_SPAM");
	}
	
	$t->parse("MAIN.IS_IN_VIEW_SECTION");
}
