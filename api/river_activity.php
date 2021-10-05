<?php

function my_echo() {
	$test = get_entity(165);

	$content = elgg_get_entities(array(
		'type' => 'object',
		'subtype' => 'debates',
		'full_view' => false,
		'view_toggle_type' => false,
		'no_results' => elgg_echo('legislation:none'),
		'preload_owners' => true,
		'preload_containers' => true,
		'distinct' => false,
	));

	$payment = array();
foreach($content as $row) {
	get_entity($row['guid']);
    $payment[] = array(
		'guid' => $row->guid,
        'Title' => $row->title,
        'Description' => strip_tags($row->description),
        'Topics' => $row->tags,
    );
}
	//var_dump($content);
//	$payment = json_encode($payment);
    return  $payment;
}

elgg_ws_expose_function(
        "debates.all",
        "my_echo",
        [
			
        ],
        'A testing method which echos back a string',
        'GET',
        true,
        true
);

function getObject($guid){
	$object = get_entity($guid);

	return $object;
}

//This function gets all activity items
//Sends a JSON with the specified quantity of items
function getRiverActivityItems($filter, $limit) {
	$result = false;
	$offset = 0;
	$dbprefix = elgg_get_config("dbprefix");
	
	// default options
	$options = array(
		"offset" => $offset,
		"limit" => $limit,
		"joins" => array(
			"JOIN " . $dbprefix . "entities sue ON rv.subject_guid = sue.guid",
			"JOIN " . $dbprefix . "entities obe ON rv.object_guid = obe.guid"
		),
		"wheres" => array(
			"(sue.enabled = 'yes' AND obe.enabled = 'yes')"
		)
	);
	
	//Get the filter from the request and send the items accordingly
	switch ($filter) {
		case "mine":
			$options["subject_guid"] = elgg_get_logged_in_user_guid();
			
			break;
		case "friends":
			$options["relationship_guid"] = elgg_get_logged_in_user_guid();
			$options["relationship"] = "friend";
			
			break;
		case "groups":
			if (empty($guids)) {
				$group_options = array(
					"type" => "group",
					"relationship" => "member",
					"relationship_guid" => elgg_get_logged_in_user_guid(),
					"limit" => false,
					"callback" => "ws_pack_row_to_guid"
				);
				
				$guids = elgg_get_entities_from_relationship($group_options);
			}
			
			if (!empty($guids)) {
				$options["joins"] = array("JOIN " . $dbprefix . "entities e ON rv.object_guid = e.guid");
				$options["wheres"] = array("(rv.object_guid IN (" . implode(",", $guids) . ") OR e.container_guid IN (" . implode(",", $guids) . "))");
			} else {
				
				$options = false;
			}
			
			break;
		case "all":
		default:
			// list everything
			break;
	}
	
	// get river items
	if ($options && ($items = elgg_get_river($options))) {
		$result =$items;
	}
	
	// did we get river items
	if ($result === false) {
		$result = new ErrorResult(elgg_echo("river:none"), WS_PACK_API_NO_RESULTS);
	}
	
	//Extract the information of the river items
	//Right now we only have guids
	$payload = extractRiverItems($result);

	return $payload;
//return $result;
}

function extractRiverItems($result){
	$payload = array();
	foreach($result as $row) {
		$test = get_entity($row->object_guid);

		$objectType = trim(substr($row->view, strpos($row->view, '/') + 8));
		
		if($objectType == 'thewire/create'){
		$payload[] = array(
			'action_type' => $row->action_type,
			'object_type' => 'thewire',
			'guid' => $test->guid,
			//'title' => $test->title,
			'description' => $test->description,
			
		);
	}

	//Format the comment activity so that the app can display it correctly
	if($objectType == 'comment/create'){
		$target_object = get_entity($row->target_guid);

		//Strip all HTML tags from the description for proper visualization in the app
		$commentDescription = strip_tags($test->description);

		$payload[] = array(
			'action_type' => $row->action_type,
			'object_type' => $test->getSubtype(),
			'guid' => $test->guid,
			'target_name' =>  $target_object->title,
			'target_guid' =>  $target_object->guid,
			//Strip new lines from the description for proper visualization in the app
			'description' =>  trim(preg_replace('/\s+/', ' ', $commentDescription)),
			
		);
	}

	if($objectType == 'blog/create'){
		$payload[] = array(
			'guid' => $test->guid,
			'action_type' => $row->action_type,
			'object_type' => $test->getSubtype(),
			'title' => $test->title,
			'description' => strip_tags($test->excerpt),
			
		);
	}

	//For files?
	if($test->getSubtype() == 'file'){
		$fileDescription = strip_tags($test->description);
		$payload[] = array(
			'guid' => $test->guid,
			'action_type' => $row->action_type,
			'object_subtype' => $test->getSubtype(),
		//	'target_guid' =>  $test->getSubtype(),
			'title' => $test->title,
			'description' => trim(preg_replace('/\s+/', ' ', $fileDescription)),
			
		);
	}
	}

	return $payload;
}


//River activity Endpoint
//It handles: all/mine/friends/groups
elgg_ws_expose_function(
		"river.get",
		"getRiverActivityItems",
		array(
			"filter" => array(
				"type" => "string",
				"required" => true
			),

			"limit" => array(
				"type" => "int",
				"required" => true,
				//"default" => 25
			),

		),
		elgg_echo("elgg_points:api:river:get"),
		"GET",
		true,
		true
	);
/*
	function ws_pack_export_river_items($items) {
		$result = false;
		
		if (!empty($items) && is_array($items)) {
			$result = array();
			
			foreach ($items as $item) {
				if ($item instanceof ElggRiverItem) {
					$tmp_result = array();
					
					// default export values
					$export_values = array("id", "subject_guid", "object_guid", "annotation_id", "type", "subtype", "action_type", "posted");
					
					foreach ($export_values as $field_name) {
						$tmp_result[$field_name] = $item->$field_name;
					}
					
					// add object and subject entities
				//	$tmp_result["object"] = ws_pack_export_entity($item->getObjectEntity());
				//	$tmp_result["subject"] = ws_pack_export_entity($item->getSubjectEntity());
					
					// add some html views
					// set viewtype to default
					$viewtype = elgg_get_viewtype();
					elgg_set_viewtype("default");
					
					$tmp_result["html_view"] = elgg_view_river_item($item);
					
					// parse the html to get some usefull information
			 
					// add friendly time
					$friendly_time = elgg_view_friendly_time($item->posted);
					$tmp_result["friendly_time"] = trim(elgg_strip_tags($friendly_time));
					
					// restore viewtype
					elgg_set_viewtype($viewtype);
					
					// add this item to the result set
					$result[] = $tmp_result;
				}
			}
		}
		
		return $result;
	}
	*/
	/**
	 * Converts rows to guids
	 *
	 * @param stdClass $row database row
	 *
	 * @return int
	 */
	function ws_pack_row_to_guid($row) {
		return (int) $row->guid;
	}