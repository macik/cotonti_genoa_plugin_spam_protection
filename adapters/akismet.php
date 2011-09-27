<?php 
defined('SED_CODE') or die('Wrong URL');
include_once $cfg['plugins_dir'].'/spam_protection/lib/akismet/akismet.php';

function spam_protection_service_connection() {
	global $cfg;
	$service = new Akismet(SED_ABSOLUTE_URL, $cfg['plugin']['spam_protection']['spam_service_api_key']);
	$status = FALSE;
	try {
		$status = $service->isKeyValid();
	} catch( Exception $e) {
		$status = FALSE;
	}
	$service = ($status===TRUE) ? $service : FALSE;
	return $service;
}

function spam_protection_check(array $data = array()) {
	global $cfg;
	
	$is_spam = FALSE;
	if(!empty($cfg['plugin']['spam_protection']['spam_service_api_key']) && !empty($data)) {
		$service = spam_protection_service_connection();
		if($service) {
			// Force all items into spam collection
			if($cfg['plugin']['spam_protection']['force_all_as_spam']=='Yes') {
				return array('is_spam' => TRUE);
			}
			if(isset($data['content'])) {
				$service->setCommentContent($data['content']);
			}
			if(isset($data['author'])) {
				if(isset($cfg['debug_spam_protection']) && $cfg['debug_spam_protection']==TRUE) {
					$data['author'] = "viagra-test-123"; // Akismet spam test - always fails
				}
				$service->setCommentAuthor($data['author']);
			}
			if(isset($data['author_email'])) {
				$service->setCommentAuthorEmail($data['author_email']);
			}
			if(isset($data['author_url'])) {
				$service->setCommentAuthorURL($data['author_email']);
			}
			if(isset($data['type'])) {
				$service->setCommentType($data['type']);
			}
			if(isset($data['author_ip'])) {
				$service->setUserIP($data['author_ip']);
			}
			if(isset($data['permalink'])) {
				$service->setPermalink($data['permalink']);
			} 
			if(isset($data['referrer'])) {
				$service->setReferrer($data['referrer']);
			}
			$is_spam = $service->isCommentSpam();
		}
	}
	
	return array('is_spam' => (bool)$is_spam);
}

?>