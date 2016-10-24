<?php
	/*
	 * Author: National Research Council Canada
	 * Website: http://www.nrc-cnrc.gc.ca/eng/rd/ict/
	 *
	 * License: Creative Commons Attribution 3.0 Unported License
	 * Copyright: Her Majesty the Queen in Right of Canada, 2015
	 */

	/*
	 * The view which display the opt-in choices that the user has saved.
	 * If no choices have been made it will display a message.
	 * This view is inside a section wrapper as described in wrapper.php.
	 */

	if (elgg_is_xhr()) {
    	$user_guid = $_GET["guid"];
	}
	else {
    	
        //Nick - changing this to page owner so users don't see their own opt in status
        $user_guid = elgg_get_page_owner_guid();

	}
	
	// Gets the opt_in_set from the user's profile.
	$user = get_user($user_guid);
	

    //Nick - created arrays of the opp types and their status
    $at_level_array = array(elgg_echo('gcconnex_profile:opt:micro_missionseek')=>elgg_echo($user->opt_in_missions),elgg_echo('gcconnex_profile:opt:micro_mission')=>elgg_echo($user->opt_in_missionCreate), elgg_echo('gcconnex_profile:opt:assignment_deployment_seek') =>elgg_echo($user->opt_in_assignSeek),elgg_echo('gcconnex_profile:opt:assignment_deployment_create') =>elgg_echo($user->opt_in_assignCreate) , elgg_echo('gcconnex_profile:opt:deployment_seek')=>elgg_echo($user->opt_in_deploySeek),elgg_echo('gcconnex_profile:opt:deployment_create')=>elgg_echo($user->opt_in_deployCreate),elgg_echo('gcconnex_profile:opt:job_swap')=>elgg_echo($user->opt_in_swap), elgg_echo('gcconnex_profile:opt:job_rotate')=>elgg_echo($user->opt_in_rotation), );
    $developmental_array = array(elgg_echo('gcconnex_profile:opt:mentored')=>elgg_echo($user->opt_in_mentored),elgg_echo('gcconnex_profile:opt:mentoring')=>elgg_echo($user->opt_in_mentoring), elgg_echo('gcconnex_profile:opt:shadowed') =>elgg_echo($user->opt_in_shadowed), elgg_echo('gcconnex_profile:opt:shadowing')=>elgg_echo($user->opt_in_shadowing),elgg_echo('gcconnex_profile:opt:job_sharing')=>elgg_echo($user->opt_in_jobshare), elgg_echo('gcconnex_profile:opt:peer_coached')=>elgg_echo($user->opt_in_pcSeek), elgg_echo('gcconnex_profile:opt:peer_coaching')=>elgg_echo($user->opt_in_pcCreate),elgg_echo('gcconnex_profile:opt:skill_sharing')=>elgg_echo($user->opt_in_ssSeek),elgg_echo('gcconnex_profile:opt:skill_sharing_create')=>elgg_echo($user->opt_in_ssCreate), );

	echo '<a class="opt-in-anchor"></a>';
	echo '<div class="gcconnex-profile-opt-in-display" style="padding:20px 20px 10px 0px;">';
	if($user->canEdit() && false) {
		echo elgg_echo('gcconnex_profile:opt:set_empty');
	}
	else {


		echo '<div class="gcconnex-profile-opt-in-display-table" style="margin: 10px;">';
        echo '<div class="col-sm-6 "><h4 class="mrgn-tp-0">'. elgg_echo('gcconnex_profile:opt:atlevel').'</h4>';
			echo '<ul class="list-unstyled">';
            foreach ($at_level_array as $k => $v){
                if($v == elgg_echo("gcconnex_profile:opt:yes")){ //Nick - don't show opportunities they are not opted in for (this is to save space on the profile)
                    $status = '<span><i class="fa fa-check text-success" aria-hidden="true"></i> <span class="wb-inv">'.$v.'</span></span>';
                    $list_item =  $k .' '. $status;
                    echo elgg_format_element('li', array(), $list_item);
                }
                
            }
                
        echo '</ul></div>';
        echo '<div class="col-sm-6 "><h4 class="mrgn-tp-0">'. elgg_echo('gcconnex_profile:opt:development').'</h4>';
        echo '<ul class="list-unstyled">';
                foreach($developmental_array as $k=>$v){
                    if($v == elgg_echo("gcconnex_profile:opt:yes")){
                        $status = '<span><i class="fa fa-check text-success" aria-hidden="true"></i> <span class="wb-inv">'.$v.'</span></span>';
                        $list_item =  $k .' '. $status;
                        echo elgg_format_element('li', array(), $list_item);
                    }
                    
                }
                        
                echo '</ul></div>';

			echo '</div>';
	}
	echo '</div>';
?>