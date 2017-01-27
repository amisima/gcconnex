<?php
/*
* wire_posts.php
*
* Calls The elgg 
*/



$content = elgg_list_entities(array(
	'type' => 'object',
	'subtype' => 'thewire',
	'limit' => get_input('limit'),
	'preload_owners' => true,
    'pagination' =>false,
    'list_class'=>'newest-wire-list',
));

echo $content;
?>