<?php

/**
 *
 * User setting for Notification options. Displays different options for subscription settings, how to be notified and who or what to subscribe
 * @author Christine Yu <internalfire5@live.com>
 *
 */

/* name - name attribute for the checkbox
 * values - default and value
 * label - label for checkbox
 */
function create_checkboxes($user_id, $name, $values, $label) {

	$user_option = elgg_get_plugin_user_setting($name, $user_id, 'cp_notifications');
	error_log("user option: {$user_option} / name: {$name}");

/*

[Thu Jan 12 16:33:22.652228 2017] [:error] [pid 2701] [client 192.168.2.22:51779] user option: set_digest_yes / name: cpn_set_digest
[Thu Jan 12 16:33:22.653739 2017] [:error] [pid 2701] [client 192.168.2.22:51779] user option:  / name: cpn_set_digest_freq_daily
[Thu Jan 12 16:33:22.654544 2017] [:error] [pid 2701] [client 192.168.2.22:51779] user option:  / name: cpn_set_digest_freq_weekly
[Thu Jan 12 16:33:22.655293 2017] [:error] [pid 2701] [client 192.168.2.22:51779] user option:  / name: cpn_set_digest_lang_en
[Thu Jan 12 16:33:22.656107 2017] [:error] [pid 2701] [client 192.168.2.22:51779] user option:  / name: cpn_set_digest_lang_fr
[Thu Jan 12 16:33:22.658270 2017] [:error] [pid 2701] [client 192.168.2.22:51779] user option: likes_email / name: cpn_likes_email
[Thu Jan 12 16:33:22.658824 2017] [:error] [pid 2701] [client 192.168.2.22:51779] user option: likes_site / name: cpn_likes_site
[Thu Jan 12 16:33:22.666418 2017] [:error] [pid 2701] [client 192.168.2.22:51779] user option: mentions_email / name: cpn_mentions_email
[Thu Jan 12 16:33:22.667933 2017] [:error] [pid 2701] [client 192.168.2.22:51779] user option: mentions_site / name: cpn_mentions_site
[Thu Jan 12 16:33:22.670782 2017] [:error] [pid 2701] [client 192.168.2.22:51779] user option: content_email / name: cpn_content_email
[Thu Jan 12 16:33:22.671449 2017] [:error] [pid 2701] [client 192.168.2.22:51779] user option: content_site / name: cpn_content_site
[Thu Jan 12 16:33:22.676638 2017] [:error] [pid 2701] [client 192.168.2.22:51779] user option: opportunities_email / name: cpn_opportunities_email
[Thu Jan 12 16:33:22.678035 2017] [:error] [pid 2701] [client 192.168.2.22:51779] user option: opportunities_site / name: cpn_opportunities_site


*/

	$chkbox = elgg_view('input/checkbox', array(
		'name' => "params[{$name}]",
		'value' => $values[0],
		'default' => $values[1],
		'checked' => (strcmp($user_option, $name) == 0  || !$user_option) ? true : false,
		'label' => $label ));

	return $chkbox;
}


gatekeeper();
$user = elgg_get_page_owner_entity();
$plugin = elgg_extract("entity", $vars);
$dbprefix = elgg_get_config('dbprefix');
$site = elgg_get_site_entity();

$title = elgg_echo('cp_notify:panel_title',array("<a href='".elgg_get_site_url()."settings/user/'>".elgg_echo('label:email')."</a>"));


// DIGEST OPTION FOR USER NOTIFICATIONS
$enable_digest = elgg_get_plugin_setting('cp_notifications_enable_bulk','cp_notifications');
if (strcmp($enable_digest, 'yes') == 0) {
	
	$chk_email = create_checkboxes($user->getGUID(), 'cpn_set_digest', array('set_digest_yes', 'set_digest_no'), elgg_echo('label:email'));
	/// enable notifications digest
	$content .= "<section id='notificationstable' cellspacing='0' cellpadding='4' width='100%' class='clearfix'>";
	$content .= '<div class="col-sm-12 clearfix"> <h3 class="well">'.elgg_echo('cp_notify:NewsletterSettings').'</h3>'; 
	$content .= '<div class="col-sm-8">'.elgg_echo('cp_notify:enable_digest').'</div>';
	$content .= "<div class='col-sm-2'>{$chk_email}</div> <div class='col-sm-2'>     </div>";

	/// select daily or weekly notification
	$chk_occur_daily = create_checkboxes($user->getGUID(), 'cpn_set_digest_freq_daily', array('set_digest_daily', 'set_digest_no'), elgg_echo('label:daily'));
	$chk_occur_weekly = create_checkboxes($user->getGUID(), 'cpn_set_digest_freq_weekly', array('set_digest_daily', 'set_digest_no'), elgg_echo('label:weekly'));
	$content .= '<div class="col-sm-8">'.elgg_echo('cp_notify:set_frequency').'</div>';
	$content .= "<div class='col-sm-2'>{$chk_occur_daily}</div> <div class='col-sm-2'>{$chk_occur_weekly}</div>";	
	
	/// select language preference
	$chk_language_en = create_checkboxes($user->getGUID(), 'cpn_set_digest_lang_en', array('set_digest_en', 'set_digest_no'), elgg_echo('label:english'));
	$chk_language_fr = create_checkboxes($user->getGUID(), 'cpn_set_digest_lang_fr', array('set_digest_fr', 'set_digest_no'), elgg_echo('label:french'));
	$content .= '<div class="col-sm-8">'.elgg_echo('cp_notify:set_language').'</div>';
	$content .= "<div class='col-sm-2'>{$chk_language_en}</div> <div class='col-sm-2'>{$chk_language_fr}</div>";

	$content .= "</div>";
	$content .= "</section>";

}



// PERSONAL NOTIFICATIONS (NOTIFY FOR LIKES, @MENTIONS AND MAYBE SHARES)
$content .= "<section id='notificationstable' cellspacing='0' cellpadding='4' width='100%' class='clearfix'>";
$content .= '<div class="col-sm-12 clearfix"> <h3 class="well">'.elgg_echo('cp_notify:personalNotif').'</h3>';

$personal_notifications = array('likes','mentions','content', 'opportunities');
foreach ($personal_notifications as $label) {

	$chk_email = create_checkboxes($user->getGUID(), "cpn_{$label}_email", array("{$label}_email", "{$label}_email_none"), elgg_echo('label:email'));
	$chk_site = create_checkboxes($user->getGUID(), "cpn_{$label}_site", array("{$label}_site", "{$label}_site_none"), elgg_echo('label:site'));
	$content .= '<div class="col-sm-8">'.elgg_echo("cp_notify:personal_{$label}").'</div>';
	$content .= "<div class='col-sm-2'>{$chk_email}</div> <div class='col-sm-2'>{$chk_site}</div>";
}

$content .= '</div>';
$content .= '</section>';


 
// SUBSCRIBE TO COLLEAGUE NOTIFICATIONS
$colleagues = $user->getFriends(array('limit' => false));
$subbed_colleagues = elgg_get_entities_from_relationship(array(
	'relationship' => 'cp_subscribed_to_site_mail',
	'relationship_guid' => $user->guid,
	'type' => 'user',
	'limit' => 0,
));

foreach($subbed_colleagues as $c)
	$subbed_colleague_guids[] = $c->getGUID();

$cpn_coll_notif_checkbox = elgg_view('input/checkboxes', array(
    'name' => "params[cpn_notif_{$user->getGUID()}]",
    'value' => 'subscribed',
    'options' => $cpn_notification_options,
    'multiple' => true,
    'checked' => true,
    'class' => 'list-unstyled list-inline notif-ul',
));

$colleague_picker = elgg_view('input/friendspicker', array(
	'entities' => $colleagues, 
	'name' => 'colleagues_notify_sub', 
	'value' => $subbed_colleague_guids
));

$content .= "<section id='notificationstable' cellspacing='0' cellpadding='4' width='100%' class='clearfix'>";
$content .= '	<div class="col-sm-12"><h3 class="well">'.elgg_echo('cp_notify:collNotif').'</h3>';
$content .= '		<div class="col-sm-8">'.elgg_echo('cp_notify:colleagueContent').'</div>';
$content .= "		<div class='col-sm-4'>{$cpn_coll_notif_checkbox}</div>";
$content .= '		<div class="accordion col-sm-12 clearfix mrgn-bttm-sm">';
$content .= '			<div class="tgl-panel clearfix">';
$content .= '				<details class="acc-group" style="width:100%; display:inline-block;" ><br/>';
$content .= '					<summary class="wb-toggle tgl-tab">'.elgg_echo('cp_notify:pickColleagues').'</summary>';
$content .= "					<div style='padding:5px 15px 0px 5px;'> {$colleague_picker} </div>";
$content .= '				</details>';
$content .= '			</div>';	
$content .= '		</div>';		
$content .= '	</div>';		
$content .= "</section>";






// SUBSCRIBED TO GROUP AND GROUP CONTENT
$url_sub = elgg_add_action_tokens_to_url(elgg_get_site_url()."action/cp_notifications/user_autosubscription?sub=sub");
$url_unsub = elgg_add_action_tokens_to_url(elgg_get_site_url()."action/cp_notifications/user_autosubscription?sub=unsub");

$chk_all_email = elgg_view('input/checkbox', array(
	'name' => "params[cpn_group_email_{$user->getGUID()}]",
	'label'=>elgg_echo('cp_notify:emailsForGroup'),
	'class'=>'all-email'
));

$chk_all_site_mail = elgg_view('input/checkbox', array(
	'name' => "params[cpn_group_site_{$user->getGUID()}]",
	'label' => elgg_echo('cp_notify:siteForGroup'),
	'class'=>'all-site'
));

$options = array(
	'relationship' => 'member',
	'relationship_guid' => $user->guid,
	'type' => 'group',
	'joins' => array("INNER JOIN {$dbprefix}groups_entity g ON (e.guid = g.guid)"),
	'order_by' => 'g.name',
	'offset' => $group_offset,
	'limit' => false,
);
$groups = elgg_get_entities_from_relationship($options);


// SUBSCRIBE OR UNSUBSCRIBE TO ALL GROUP AND GROUP CONTENT NOTIFICATIONS 
$content .= "<section id='notificationstable' cellspacing='0' cellpadding='4' width='100%' class='clearfix'>";
$content .= '	<div class="col-sm-12 group-notification-options"><h3 class="well">'.elgg_echo('cp_notify:groupNotif').'</h3>';
$content .= "		<div style='padding-bottom:50px;'>";
$content .= "			<div style='border:1px solid black; padding: 2px 2px 2px 10px;'> <center><a href='{$url_sub}'> ".elgg_echo('cp_notify:subscribe_all_label',array($url_sub,$url_unsub))." </a></center></div>";
$content .= "		</div>";

$content .= "		<div class='clearfix brdr-bttm mrgn-bttm-sm'>";
$content .= "			<div class='col-sm-2 col-sm-offset-8 mrgn-bttm-md'>{$chk_all_email}</div>";
$content .= "			<div class='col-sm-2 mrgn-bttm-md'>{$chk_all_site_mail}</div>";

// script to check all email and site mail's group checkboxes
$content .='<script>$(".all-email").click(function(){$(".group-check").prop("checked", this.checked);$(".group-check").trigger("change")})</script>';
$content .='<script>$(".all-site").click(function(){$(".group-site").prop("checked", this.checked);$(".group-site").trigger("change")})</script>';

foreach ($groups as $group) {
	
    // Nick - This asks for the inputs of the checkboxes. If the checkbox is checked it will save it's value. else it will return 'unSub' or 'site_unSub'
	$cpn_set_subscription_email = $plugin->getUserSetting("cpn_email_{$group->getGUID()}", $user->getGUID());	// setting email notification

	// Update Relationship table as per selection by user
    $get_relationship_data = get_data("SELECT count(*) AS subed FROM {$dbprefix}entity_relationships WHERE guid_one = {$user->guid} AND relationship = 'cp_subscribed_to_email' AND guid_two = {$group->guid}")[0]->subed;
    $cpn_grp_email_checked = ($get_relationship_data) ? true : false;
	$cpn_set_subscription_email = (empty($cpn_set_subscription_email)) ? "sub_{$group->getGUID()}" : ""; 
	

	$get_relationship_data =  get_data("SELECT count(*) AS subed FROM {$dbprefix}entity_relationships WHERE guid_one = {$user->guid} AND relationship = 'cp_subscribed_to_site_mail' AND guid_two={$group->guid}")[0]->subed;
	$cpn_grp_site_mail_checked = ($get_relationship_data) ? true : false;
	$cpn_set_subscription_site_mail = (empty($cpn_set_subscription_site_mail)) ? "sub_site_{$group->getGUID()}" : "";
	$cpn_set_subscription_site_mail = $plugin->getUserSetting("cpn_site_mail_{$group->getGUID()}", $user->getGUID());

	$options = array(
		'container_guid' => $group->guid,
		'type' => 'object',
		'limit' => false,
	);
	$group_contents = elgg_get_entities($options);


    // Nick - checkboxes for email and site. if they are checked they will send 'sub_groupGUID' if not checked they will send 'unSub'
    $cpn_grp_email_checkbox = elgg_view('input/checkbox', array(
        'name' => "params[cpn_email_{$group->getGUID()}]",
        'value'=> "sub_{$group->getGUID()}",
        'label'=> elgg_echo('label:email'),
        'default'=>'unSub',
        'checked'=> $cpn_grp_email_checked ));

    $cpn_grp_site_checkbox = elgg_view('input/checkbox', array(
        'name' => "params[cpn_site_mail_{$group->getGUID()}]",
        'value'=> 'sub_site_'.$group->getGUID(),
        'label'=> elgg_echo('label:site'),
        'default'=>'site_unSub',
        'checked'=> $cpn_grp_site_mail_checked ));


    $content .= "		<div class='list-break clearfix'>";
	$content .= "			<div class='namefield col-sm-8'> <strong> <a href='{$group->getURL()}' id='group-{$group->guid}'>{$group->name}</a> </strong> </div>";
    $content .= "			<div class='col-sm-2'>{$cpn_grp_email_checkbox}</div>";
    $content .= "			<div class='col-sm-2'>{$cpn_grp_site_checkbox}</div>";
	$content .= "		</div>";


	// GROUP CONTENT SUBSCRIPTIONS
    $content .= '		<div class="accordion col-sm-12 clearfix mrgn-bttm-sm">';
	$content .= '			<details class="acc-group" onClick="return create_group_content_item('.$group->getGUID().', '.$user->getGUID().')">';
	$content .= "				<summary id='group_item_container-{$group->getGUID()}' class='wb-toggle tgl-tab'>".elgg_echo('cp_notify:groupContent').'</summary>';
    $content .= "				<div id='group-content-{$group->getGUID()}' class='tgl-panel clearfix'></div>";
    $content .= '			</details>';	
    $content .= '		</div>';			
    $content .= '	</div>';				
} 
$content .= "</section>";



// PERSONAL SUBSCRIPTIONS (DISPLAYS ALL ITEMS THAT DO NOT BELONG IN GROUP NOTIFICATIONS)
$current_user = elgg_get_logged_in_user_entity();

// build a base query (so we can use it to count all the results, and display all the items)
// cyu - patched issue with personal subscription that contains group content
$content_arr = array('blog','bookmark','event_calendar','file','hjforumtopic','hjforum','photo','album','task','page','page_top','task_top','idea','thewire');
$query_base = " FROM {$dbprefix}entity_subtypes es, {$dbprefix}entities e, {$dbprefix}entity_relationships r, {$dbprefix}objects_entity o WHERE e.container_guid  NOT IN (SELECT guid FROM {$dbprefix}groups_entity) AND e.subtype = es.id AND o.description <> '' AND o.guid = e.guid AND o.guid = r.guid_two AND r.guid_one = {$current_user->getGUID()} AND r.relationship = 'cp_subscribed_to_email' AND ( es.subtype = 'poll' ";
foreach ($content_arr as $content_element)
	$query_base .= " OR es.subtype = '{$content_element}'";
$query_base .= " ) ";



$query_select = "SELECT e.guid, e.subtype as entity_subtype, es.subtype, o.title, o.description {$query_base}";
$query_select .= " ORDER BY e.subtype ASC ";//LIMIT {$personal_limit} OFFSET {$personal_offset}";
$subbed_contents = get_data($query_select);


// update only the div when you want to view the next page
$content .= "<section id='notificationstable' cellspacing='0' cellpadding='4' width='100%' class='clearfix'>";
$content .= '<div class="col-sm-12 group-notification-options"><h3 class="well">'.elgg_echo('cp_notify:personal_setting').'</h3></div>';


foreach ($subbed_contents as $subbed_content) {
	$content_title = $subbed_content->title;
	if (!$content_title) $content_title = elgg_echo('cp_notify:wirepost_generic_title');

	// cyu - clean up the html tags from the description
	$content_desc = trim($subbed_content->description);
	$content_desc = str_replace('\r\n', '', $content_desc);
	$content_desc = str_replace('\n', '', $content_desc);
	$content_desc = str_replace('\r', '', $content_desc);
	$content_desc = preg_replace('/[\s]+/','',$content_desc);
	$content_desc = preg_replace("/<[a-zA-Z ]+>|<\/[a-zA-Z]+>/",'',$content_desc);
	$content_desc = strip_tags($content_desc);

	if (strlen($content_desc) >= 40)
		$content_desc = substr($content_desc, 0, 45).'...';


	$cpn_unsub_btn = elgg_view('input/button', array(
		'class'=> 'btn btn-default unsub-button',
		'id'=> $subbed_content->guid.'_unsub',
		'value'=> elgg_echo("cp_notify:unsubscribe"),
		'align' => 'right',
	));

	// form the url
	$entity_content = get_entity($subbed_content->guid);

	if (strcmp($subbed_content->subtype,'hjforum') == 0) {
		$group_id = get_forum_in_group($subbed_content->guid, $subbed_content->guid);
		$url = "{$site->getURL()}gcforums/group/{$group_id}/{$subbed_content->guid}";
	} else if (strcmp($subbed_content->subtype,'hjforumtopic') == 0)
		$url = "{$site->getURL()}gcforums/group/{$group_id}/{$subbed_content->guid}/{$subbed_content->subtype}";
	else
		$url = $entity_content->getURL();

	$content .= "<div class='clearfix col-sm-12 list-break'>";
	$content .= "<div class='togglefield col-sm-10'> <a href='{$url}'><strong>{$content_title}</strong></a> - {$content_desc}  <sup>{$subbed_content->subtype}</sup> </div>";
	$content .= "<div class=' col-sm-2'> {$cpn_unsub_btn} </div>";
	$content .= "</div>";

	$cp_count++;
}

if ($cp_count <= 0)
	$content .= elgg_echo('cp_notify:no_subscription')."<br/>";


if (strcmp(elgg_get_plugin_setting('cp_notifications_sidebar','cp_notifications'), 'yes') == 0)
	echo elgg_extend_view('page/elements/sidebar','cp_notifications/sidebar');

echo elgg_view_module('info', $title, $content);









?>

<script>

/// Uses Ajax to dynamically create and display the list of group content that the user has subscribed to 
///
	function create_group_content_item(grp_guid, usr_guid) {
		
		if ($('#group-content-' + grp_guid).is(':visible')) {
			// do nothing
		} else {
			var loading_text = elgg.echo('cp_notify:setting:loading');
			// // assuming this is doing what i think it is doing + loading indicator
			$('#group-content-' + grp_guid).children().remove();
			$('#group-content-' + grp_guid).append("<div class='clearfix col-sm-12 list-break'>" + loading_text + "<div>");

			// jquery - retrieve the group content on user click (this way it doesn't try and load everything at once)
			elgg.action('cp_notify/retrieve_group_contents', {
				data: {
					group_guid: grp_guid,
					user_guid: usr_guid,
				},
				success: function (content_arr) {
					
					$('#group-content-' + grp_guid).children().remove();

					// create a list of all the content in the group that you are subscribed to
					for (var item in content_arr.output.text3)
						$('#group-content-' + grp_guid).append("<div class='clearfix col-sm-12 list-break'>" + content_arr.output.text3[item] + "<div>");

					// jquery - when the unsubscribe button is clicked, remove entry from the subscribed to content
				    $('.unsub-button').on('click', function() {
				        var this_thing = $(this);
				        var guid = parseInt($(this_thing).attr('id'));
				        
				        elgg.action('cp_notify/unsubscribe', {
			                data: {
			                	'guid':guid,
			                },
			                success: function(data) {
			                  $(this_thing).closest('.list-break').fadeOut();
			                }
				        }); 

				    });		
				},
				error:function(xhr, status, error) {
					alert('Error: ' + status + '\nError Text: ' + error + '\nResponse Text: ' + xhr.responseText);
				}
			}); 
		
		}
	}



/// Uses Ajax to dynamically create and display the list of personal content that the user has subscribed to 
///
	function create_content_item(page_num,usr_guid) {
		// loading indicator
		$('#group-content-' + grp_guid).append("<div class='clearfix col-sm-12 list-break'>LOADING...<div>");

		// jquery - retrieve the group content on user click (this way it doesn't try and load everything at once)
		elgg.action('cp_notify/retrieve_personal_content', {
			data: {
				page_number: personal_sub_page,
				user_guid: usr_guid,
			},
			success: function (sample_text) {
				// assuming this is doing what i think it is doing 
				$('#group-content-' + grp_guid).children().remove();

				// create a list of all the content in the group that you are subscribed to
				for (var item in sample_text.output.text3)
					$('#group-content-' + grp_guid).append("<div class='clearfix col-sm-12 list-break'>" + sample_text.output.text3[item] + "<div>");

				// jquery - when the unsubscribe button is clicked, remove entry from the subscribed to content
			    $('.unsub-button').on('click', function() {
			        var this_thing = $(this);
			        var guid = parseInt($(this_thing).attr('id'));
			        
			        elgg.action('cp_notify/unsubscribe', {
		                data: {'guid':guid},
		                success: function(data) {
		                  $(this_thing).closest('.list-break').fadeOut();
		                }
			        }) 	// close jquery action line 286
			    });
			}
		}); 			// close jquery action line 271
	}



</script>

<?php



/// recursive, to get group id
///
function get_forum_in_group($entity_guid_static, $entity_guid) {
	$entity = get_entity($entity_guid);
	// (base) stop recursing when we reach group guid
	if ($entity instanceof ElggGroup)  
		return $entity_guid;
	else 
		return get_forum_in_group($entity_guid_static, $entity->getContainerGUID());
}

