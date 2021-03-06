<?php

elgg_load_library('elgg:event_calendar');
$fd = $vars['event'];
$group_id = $vars['group_guid'];
$site_calendar = elgg_get_plugin_setting('site_calendar', 'event_calendar');
$group_calendar = elgg_get_plugin_setting('group_calendar', 'event_calendar');
$admin = elgg_is_admin_logged_in();
$containers = array();

$user = elgg_get_logged_in_user_entity();

$groups = $user->getGroups(array('limit' => false));

foreach ($groups as $group) {
	if (event_calendar_activated_for_group($group)) {
		if ($admin || !$group_calendar || $group_calendar == 'members') {
			if ($group->canWriteToContainer($user->guid)) {
				$containers[$group->guid] = $group->name;
			}
		} else if ($group->canEdit()) {
			$containers[$group->guid] = $group->name;
		}
	}
}
$value = $vars['container_guid'];
if ($vars['container_guid']) {
	$value = $vars['container_guid'];

} else {
	$value = 0;
}

natcasesort($containers);

if (($site_calendar != 'no') && ($admin || !$site_calendar || ($site_calendar == 'loggedin'))) {
	$containersFirst = elgg_echo('event_calendar:site_calendar');
	array_unshift($containers, $containersFirst);

}

//print_r($containers);
/*
foreach ($containers as $k => $v) {
    echo $v. '='.$k;
}
echo'----->'.$group_id;*/
echo elgg_view('input/select', array('name' => 'group_guid', 'value' => 'group_guid', 'options_values' => $containers));
