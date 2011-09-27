<?php 
defined('SED_CODE') or die('Wrong URL');
include_once $cfg['plugins_dir'].'/spam_protection/lib/defensio/Defensio.php';

function spam_protection_service_connection() {
	global $cfg;
	$service = new Defensio($cfg['plugin']['spam_protection']['spam_service_api_key']);
	$status = FALSE;
	
	try {
		$status = (int)array_shift($service->getUser());
	} catch(DefensioError $e) {
		$status = FALSE;
	}
	$service = ($status==200) ? $service : FALSE;
	return $service;
}

function spam_protection_check(array $data = array()) {
	global $cfg, $usr;
	
	$is_spam = FALSE;
	$presets = array(
		'type' => 'comment',
		'platform' => 'cotonti',
		'client' => 'Spam Protection for Cotonti Genoa | 1.0 | xerora | contact@tylerfulham.com'
	);
	$service = spam_protection_service_connection();
	$profanityfilterlist = array();

	if($service) {
	
		$document = array();
		$verify_document = array();
		
		if(isset($data['type'])) {
			$document['type'] = $data['type'];
		}
		if(isset($data['title'])) {
			$profanityfilterlist['title'] = $data['title'];
		}		
		if(isset($data['description'])) {
			$profanityfilterlist['description'] = $data['description'];
		}
		if(isset($data['content'])) {
			$document['content'] = $data['content'];
			$profanityfilterlist['content'] = $data['content'];
		}
		if(isset($data['author'])) {
			$document['author-name'] = $data['author'];
		}
		if(isset($data['author_email'])) {
			$document['author-email'] = $data['author_email'];
		}
		if(isset($data['author_ip'])) {
			$document['author-ip'] = $data['author_ip'];
		}
		if(isset($data['author_url'])) {
			$document['author-url'] = $data['author_url'];
		}
		if(isset($data['permalink'])) {
			$document['document-permalink'] = $data['permalink'];
		}
		if(isset($data['referrer'])) {
			$document['referrer'] = $data['referrer'];
		}
		
		$document['author-logged-in'] = (int)$usr['id'] > 0 ? TRUE : FALSE;
		if(isset($cfg['debug_spam_protection']) && $cfg['debug_spam_protection']===TRUE) {		
			$document['author-name'] = "viagra-test-123"; // always fails ( for testing )
		}
		
		$verify_document = array_merge($presets, $document);
		
		// Force all items into spam collection
		if($cfg['plugin']['spam_protection']['force_all_as_spam']=='Yes') {
			return array('is_spam' => TRUE, 'data' => $verify_document, 'spam_signature' => "");
		}			
		
		try {
			$result = $service->postDocument($verify_document);
			$is_spam = $result[1]->allow=="true" ? FALSE : TRUE;
			$spam_signature = $result[1]->signature;
			
			if(($data['type']=='comment' && $cfg['plugin']['spam_protection']['filter_profanity_comments']=='Yes') 
					|| ($data['type']=='forum' && $cfg['plugin']['spam_protection']['filter_profanity_forums']=='Yes')) {
				$profanityresult = $service->postProfanityFilter($profanityfilterlist);
				$filtereditems = (array)$profanityresult[1]->filtered;
			
				if(is_array($filtereditems) && !empty($filtereditems)) {
					foreach($filtereditems as $name => $value) {
						if(isset($verify_document[$name])) unset($verify_document[$name]);
						$verify_document[(string)$name] = (string)$value;
					}
				}
			}			
		} catch (DefensioError $e) {
			// Continue working and just allow item to pass
			$is_spam = FALSE;
			$spam_signature = "";
		}
		
		$return = array('data' => $verify_document, 'is_spam' => (bool)$is_spam, 'signature' => $spam_signature);
		return $return;
	}
}

?>