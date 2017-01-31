<?php

/**
 * recursively search for the group guid
 *
 * @param integer 	$entity_guid_static
 * @param integer 	$entity_guid
 *
 */
function get_forum_in_group($entity_guid_static, $entity_guid) {
	$entity = get_entity($entity_guid);
	// (base) stop recursing when we reach group guid
	if ($entity instanceof ElggGroup)  
		return $entity_guid;
	else 
		return get_forum_in_group($entity_guid_static, $entity->getContainerGUID());
}


/**
 * @param integer 			$user_id 
 * @param string 			$name 
 * @param array <string> 	$values
 * @param string 			$label
 */
function create_checkboxes($user_id, $name, $values, $label, $id='') {
	$user_option = elgg_get_plugin_user_setting($name, $user_id, 'cp_notifications');
	$is_checked = (strcmp($user_option, 'set_digest_no') == 0 || strcmp($user_option, 'set_notify_off') == 0) ? false : true;

	$chkbox = elgg_view('input/checkbox', array(
		'name' => 		"params[{$name}]",
		'value' => 		$values[0],
		'default' => 	$values[1],
		'checked' => 	(strcmp($user_option, $name) == 0  || !$user_option) ? true : false,
		'label' => 		$label,
		'checked' => 	$is_checked,
		'id' =>		$id
	));

	return $chkbox;
}


/**
 * check if object is: public, logged in, in group (check if user is in group), in friend-circle (check if user is in friend-circle)
 * 
 * @param ElggObject $entity		the entity we will check permissions of
 * @param int $recipient_user_id	the user guid.. to check if user is author's circle or friend
 */
function cp_check_permissions($entity, $recipient_user_id = 0) {
	$access_id = $entity->access_id;
	if ($access_id == 2 || $access_id == 1) // public or logged-in access
		return true;

	if ($access_id == -2) { // author or author's friends
		return check_entity_relationship($recipient_user_id, 'friend', $entity->getOwnerGUID()); // returns object
	}

	// Note: non-group members cannot subscribe to a group...
	// check if user is in friend's circle
	if ($access_id > 2) {
		$user_id_list = get_members_of_access_collection($access_id, true); // returns list of id in collection
		if (in_array($recipient_user_id, $user_id_list))
			return true;
	}

	return false;
}



/**
 * We need to modify the headers so that emails can go out (header spoofing)
 *
 * @param string 	$event
 */
function cp_get_headers($event) { 	// $event will be null if nothing is passed into it (no default value set)

	$email_address = elgg_get_plugin_setting('cp_notifications_email_addr','cp_notifications');
	if (!$email_address || $email_address === '')
		$email_address = 'admin.gcconnex@tbs-sct.gc.ca'; // default if nothing is set
	$php_version = phpversion();

	$headers =  "From: GCconnex <{$email_address}> \r\n";
	$headers .= "Reply-To: GCconnex <{$email_address}> \r\n";
	$headers .= "Return-Path: GCconnex <{$email_address}> \r\n";
	$headers .= "X-Mailer: PHP/{$php_version} \r\n";
	$headers .= "MIME-Version: 1.0 \r\n";
	$headers .= "Content-type: text/html; charset=utf-8 \r\n";
   
	if ($event === 'event') {
		$mime_boundary = "----Meeting Booking----".MD5(TIME());
		$headers .= 'Content-Type: multipart/alternative; boundary='.$mime_boundary."\r\n";
		$headers .= "Content-class: urn:content-classes:calendarmessage\n";
	}

	return $headers;
}



/**
 * scans the text object for any @mentions
 * 
 * @param string 	$cp_object
 */
function cp_scan_mentions($cp_object) {
	$fields = array('title','description','value');
	foreach($fields as $field) {
		$content = $cp_object->$field;													// pull the information from the fields saved to object
		if (preg_match_all("/\@([A-Za-z1-9]*).?([A-Za-z1-9]*)/", $content, $matches)) { // find all the string that matches: @christine.yu
			$users_found = array();
			foreach ($matches[0] as $match) {
				if (preg_match('/\s/',$match)) { 										// what if no space found? check for space
					$user_found = explode(' ',$match);
					$users_found[] = $user_found[0];
				}
			}
			return $users_found;
		}
	}
	return false;
}



/**
 * assembles the digest then encodes the array into JSON to be saved
 *
 * @param ElggUser 		$invoked_by
 * @param string 		$subtype
 * @param ElggEntity 	$entity
 * @param ElggUser 		$send_to
 * @return Success 		true/false
 */
function temp_digest($invoked_by, $subtype, $entity, $send_to) {
	elgg_load_library('elgg:gc_notification:functions'); 
	$digest = get_entity($send_to->cpn_newsletter);
	$digest_collection = json_decode($digest->description,true);

	$content_array = array(
		'content_title' => $entity->title,
		'content_url' => $entity->getURL(),
		'subtype' => $entity->getSubtype()
	);

	switch ($subtype) {
		case 'mission':
			if (!is_array($digest_collection['mission']['new_post']))
				$digest_collection['mission']['new_post'] = array();
			$digest_collection['mission']['new_post'][] = json_encode($content_array);
			break;

		case 'comment':
		case 'discussion_reply':

			if ($entity->getContainerEntity() instanceof ElggGroup) {
				if (!is_array($digest_collection['group']["<a href='{$entity->getContainerEntity()->getURL()}'>{$entity->getContainerEntity()->title}</a>"]['response']))
					$digest_collection['group']["<a href='{$entity->getContainerEntity()->getURL()}'>{$entity->getContainerEntity()->title}</a>"]['response'] = array();
				$digest_collection['group']["<a href='{$entity->getContainerEntity()->getURL()}'>{$entity->getContainerEntity()->title}</a>"]['response'][] = json_encode($content_array);

			} else {
				if (!is_array($digest_collection['personal']['response']))
					$digest_collection['personal']['response'] = array();
				$digest_collection['personal']['response'][] = json_encode($content_array);
			}

			
			break;


		case 'cp_friend_request':
			if (!is_array($digest_collection['personal']['friend_request']))
				$digest_collection['personal']['friend_request'] = array();
			$digest_collection['personal']['friend_request'][] = "{$invoked_by->username} has sent you a friend request";
		 	break;


		case 'cp_messageboard':
			if (!is_array($digest_collection['personal']['profile_message']))
				$digest_collection['personal']['profile_message'] = array();
			$digest_collection['personal']['profile_message'][] = "{$invoked_by->username} has left u a msg on your profile => sending to... {$send_to->guid} / {$send_to->username}";
			break;


		case 'cp_hjtopic':
		case 'cp_hjpost':

			if ($subtype === 'cp_hjtopic') {
				if (!is_array($digest_collection['personal']['forum_topic']))
					$digest_collection['group'][get_entity(get_forum_in_group($entity->guid, $entity->guid))->title]['forum_topic'] = array();
				$digest_collection['group'][get_entity(get_forum_in_group($entity->guid, $entity->guid))->title]['forum_topic'][] = json_encode($content_array);
			
			} else {
				if (!is_array($digest_collection['personal']['forum_reply']))
					$digest_collection['group'][get_entity(get_forum_in_group($entity->guid, $entity->guid))->title]['forum_reply'] = array();
				$digest_collection['group'][get_entity(get_forum_in_group($entity->guid, $entity->guid))->title]['forum_reply'][] = json_encode($content_array);
			}
			break;


		case 'post_likes':
			if (!is_array($digest_collection['personal']['likes']))
				$digest_collection['personal']['likes'] = array();

			$digest_collection['personal']['likes'][$entity->guid] = json_encode($content_array);
			break;


		case 'content_revision':
			if (!is_array($digest_collection['personal']['content_revision']))
				$digest_collection['personal']['content_revision'] = array();
		
			$digest_collection['personal']['content_revision'][] = json_encode($content_array);
			break;

		case 'cp_wire_share':
			if (!is_array($digest_collection['personal']['cp_wire_share']))
				$digest_collection['personal']['cp_wire_share'] = array();

			$digest_collection['personal']['cp_wire_share'][] = json_encode($content_array);
			
			break;

		default:
			$entity = get_entity($entity->guid);
			
			if ($entity->getContainerEntity() instanceof ElggGroup) {

				if (!is_array($digest_collection['group']["<a href='{$entity->getContainerEntity()->getURL()}'>{$entity->getContainerEntity()->title}</a>"]['new_post']))
					$digest_collection['group']["<a href='{$entity->getContainerEntity()->getURL()}'>{$entity->getContainerEntity()->title}</a>"]['new_post'] = array();
				
				$digest_collection['group']["<a href='{$entity->getContainerEntity()->getURL()}'>{$entity->getContainerEntity()->title}</a>"]['new_post'][$entity->guid] = json_encode($content_array);

			} else {

				if (!is_array($digest_collection['personal']['new_post']))
					$digest_collection['personal']['new_post'] = array();

				$digest_collection['personal']['new_post'][] = json_encode($content_array);				
			
			}
			break;
	}

	// save the information to the digest object for later access
	$ia = elgg_set_ignore_access(true);
	$digest->description = json_encode($digest_collection);
	$digest->save();
	elgg_set_ignore_access($ia);

	return true;
}



/**
 * renders the correct subtype for the notification to display
 * 
 * @param string 			$subtype_name
 * @param optional string 	$english
 */
function cp_translate_subtype($subtype_name, $english = true) {
	$label = '';
	switch($subtype_name) {
		case 'blog':
			$label = 'blog';
			break;
		case 'bookmarks':
			$label = 'bookmark';
			break;
		case 'file':
			$label = 'file';
			break;
		case 'poll':
			$label = 'poll';
			break;
		case 'event_calendar':
			$label = 'event';
			break;
		case 'album':
			$label = 'album';
			break;
		case 'groupforumtopic':
			$label = 'discussion';
			break;
		case 'image':
			$label = 'photo';
			break;
		case 'idea':
			$label = 'idea';
			break;
		case 'page_top':
		case 'page':
				$label = 'page';
			break;
		case 'hjforumtopic':
			$label = 'forum topic';
			break;
		case 'hjforum':
			$label = 'forum';
			break;
		case 'thewire':
			$label = 'wire';
			if (!$english)
				$label = 'fil';
			break;
		case 'task_top':
			$label = 'task';
			break;
		case 'mission':
			$label = 'opportunity';
			break;
		default:
			$label = $subtype_name;
		break;
	}
	return $label;
}




/**
 * Helper function for notifications about new opportunities
 *
 * @param ElggUser 	$user_obj
 * @param string 	$mission_type
 */
function userOptedIn( $user_obj, $mission_type ) {
	$typemap = array(
		'missions:micro_mission' => 'opt_in_missions',
		'missions:job_swap'	=> 'opt_in_swap',
		'missions:mentoring' => 'opt_in_mentored',
		'missions:job_shadowing' => 'opt_in_shadowed',
		'missions:assignment' => 'opt_in_assignSeek',
		'missions:deployment' => 'opt_in_deploySeek',
		'missions:job_rotation'	=> 'opt_in_rotation',
		'missions:skill_share' => 'opt_in_ssSeek',
		'missions:peer_coaching' => 'opt_in_pcSeek',
		'missions:job_share' => 'opt_in_jobshare',
		);

	$typemap2 = array(
		'missions:micro-mission' => 'opt_in_missionCreate',
		'missions:job_swap' => 'opt_in_swap',
		'missions:mentoring' =>	'opt_in_mentoring',
		'missions:job_shadowing'	=>	'opt_in_shadowing',
		'missions:assignment'	=> 'opt_in_assignCreate',
		'missions:deployment'	=>	'opt_in_deployCreate',
		'missions:job_rotation'	=>	'opt_in_rotation',
		'missions:skill_share'	=>	'opt_in_ssCreate',
		'missions:peer_coaching'	=>	'opt_in_pcCreate',
		'missions:job_share'	=>	'opt_in_jobshare',
		);
	return $user_obj->$typemap[$mission_type] == 'gcconnex_profile:opt:yes' || $user_obj->$typemap2[$mission_type] == 'gcconnex_profile:opt:yes';
}




