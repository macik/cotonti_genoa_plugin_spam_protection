<?php 
/* ====================
[BEGIN_SED_EXTPLUGIN]
Code=spam_protection
Part=comments
File=spam_protection.admin.main
Hooks=admin.main
Tags=
Order=10
[END_SED_EXTPLUGIN]
==================== */
defined('SED_CODE') or die('Wrong URL');

$spam_protection_prune = (is_numeric($cfg['plugin']['spam_protection']['prune_spam_time'])) ? (int)$cfg['plugin']['spam_protection']['prune_spam_time'] : 0;
if($spam_protection_prune!="never" && (int)$spam_protection_prune>0) {
	$prune_timestamp = $sys['now_offset']-(86400*$spam_protection_prune);
	$db_spam_protection = (empty($db_spam_protection)) ? $db_x."spam_protection" : $db_spam_protection;
	sed_sql_query("DELETE FROM $db_spam_protection WHERE sp_date<'".(int)$prune_timestamp."' LIMIT 100");
	$sp_rows_affected = sed_sql_affectedrows();
	if($sp_rows_affected>0) {
		sed_log(sed_sql_affectedrows()." spam items deleted from the database", "adm");
	}
}



?>
