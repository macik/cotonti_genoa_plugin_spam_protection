<?php
/* ====================
[BEGIN_SED_EXTPLUGIN]
Code=spam_protection
Part=admin
File=spam_protection.tool
Hooks=tools
Tags=
Order=10
[END_SED_EXTPLUGIN]
==================== */
defined('SED_CODE') or die('Wrong URL.');

define('SP_DEFAULT_SECTION', 'comment');

$plugin_body = "";
$plugin_title .= "Spam Protection Moderation Tool";
$section = sed_import('section', 'G', 'SLU');
$section = !empty($section) ? trim($section) : SP_DEFAULT_SECTION;
$action = sed_import('action', 'G', 'SLU');
$action = !empty($action) ? trim($action) : 'view';
$toolurl = array('m' => 'tools', 'p' => 'spam_protection');
$sp_tool_items_per_page = (is_int((int)$cfg['plugin']['spam_protection']['tool_items_per_page'])>0) ? (int)$cfg['plugin']['spam_protection']['tool_items_per_page'] : 10;
$active_service = str_replace(' ', '_', strtolower($cfg['plugin']['spam_protection']['spam_service']));
if($active_service=='none' || empty($cfg['plugin']['spam_protection']['spam_service_api_key'])) {
	sed_redirect(sed_url('admin', 'm=config&n=edit&o=plug&p=spam_protection', NULL, TRUE));
}

DEFINE('SP_TOOL_CONTENT_DISPLAY_LENGTH_DEFAULT', 32); 
DEFINE('SP_TOOL_ITEMS_PER_PAGE', (int)$sp_tool_items_per_page);

$t = new XTemplate($cfg['plugins_dir']."/spam_protection/tpl/spam_protection.tool.main.tpl");
$tool_path = $cfg['plugins_dir'].'/spam_protection';

require_once $cfg['plugins_dir']."/spam_protection/adapters/".$active_service.".php";
require_once $cfg['plugins_dir']."/spam_protection/inc/spam_protection.tool.inc.php";

$total_spam = array(
	'forum' => spam_protection_get_spam_count($active_service, 'forum'),
	'comment' => spam_protection_get_spam_count($active_service, 'comment')
);

if($total_spam['comment']==0 && $total_spam['forum']>0 && empty($_GET['section'])) {
	$section = 'forum';
}

switch($action) {
	case 'with_selected':
		spam_protection_action_with_selected();
	break;
	case 'view':
		spam_protection_action_view_section_spam($section, $total_spam);
	break;
	case 'error_message':
		$error_status = sed_import('status', 'G', 'ALP');
		spam_protection_action_view_error_message($error_status);
	break;
	case 'mark_item_as':
		spam_protection_action_mark_item_as($active_service);
	break;
	case 'view_item':
		$id = sed_import('id', 'G', 'INT');
		spam_protection_action_view_item($id);
	break;
	case 'check_key':
		spam_protection_action_check_key($active_service);
	break;
}

$t->assign(array(
	'SPAM_PROTECTION_TOOL_TITLE' => $L['plu_sp_tool_title'],
	'SPAM_PROTECTION_TOOL_PATH' => $cfg['plugins_dir'].'/spam_protection',
	'SPAM_PROTECTION_SECTION_LINK_COMMENTS' => '<a href="'.sed_url('admin', $toolurl + array('action' => 'view', 'section' => 'comment')).'">'.$L['Comments'].' ('.$total_spam['comment'].')</a>',
	'SPAM_PROTECTION_SECTION_LINK_FORUMS' => '<a href="'.sed_url('admin', $toolurl + array('action' => 'view', 'section' => 'forum')).'">'.$L['Forums'].' ('.$total_spam['forum'].')</a>', 
	'SPAM_PROTECTION_LINK_CHECK_API_KEY' => sed_url('admin', $toolurl + array('action' => 'check_key')),
	'SPAM_PROTECTION_LINK_CONFIGURATION' => sed_url('admin', array('m' => 'config', 'n' => 'edit', 'o' => 'plug', 'p' => 'spam_protection')),
));

$t->parse("MAIN");
$plugin_body .= $t->text("MAIN");

?>
