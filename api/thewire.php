<?php

elgg_ws_expose_function(
    "create_thewire.post",
    "createWireStatus",
    array(
        "body" => array(
            "type" => "string",
            "required" => true
        ),

        

    ),
    elgg_echo("elgg_endpoints:api:thewire:create"),
    "POST",
    true,
    true
);

elgg_ws_expose_function(
    "get_wire_posts.get",
    "getWirePosts",
    array(
        "filter" => array(
            "type" => "string",
            "required" => true
        ),
    ),
    elgg_echo("elgg_endpoints:api:thewire:activity"),
    "GET",
    true,
    true
);

function createWireStatus($body) {

    $user = get_entity(elgg_get_logged_in_user_guid());

    $parent = 0;

    $guid = thewire_save_post($body, elgg_get_logged_in_user_guid(), ACCESS_PUBLIC, $parent, 'site');

    if ($guid === false) {
        return elgg_error_response(elgg_echo('thewire:notsaved'));
    }

    

    $post = get_entity($guid);
    
    $payload = [
        'wirepost' => $post->description
    ];
    return $payload;
}


function getWirePosts($filter){

    $payload = array();
    $user = get_entity(elgg_get_logged_in_user_guid());
    if($filter == 'all'){
        $content = elgg_get_entities([
            'type' => 'object',
            'subtype' => 'thewire',
            
            'limit' => 0,
        ]);
    }



    if($filter == 'mine'){
        
        $content = elgg_get_entities([
            'type' => 'object',
            'subtype' => 'thewire',
            'owner_guid' => $user->guid,
            'limit' => 0,
        ]);

    }


    foreach($content as $row) {
        $owner = get_entity($row->owner_guid);
        $payload[] = [
            'guid' => $row->guid,
            'owner_guid' => $owner->guid,
            'owner_name' => $owner->name,
            'owner_username' => $owner->username,
            'description' => strip_tags($row->description),
            'time_created' => $row->time_created,
       
        ];
    }
        
    return $payload;
}

