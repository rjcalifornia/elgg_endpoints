<?php

elgg_ws_expose_function(
    "create_blog.post",
    "createBlogPost",
    array(
        "title" => [
            "type" => "string",
            "required" => true
        ],

        "excerpt" => [
            "type" => "string",
            "required" => false
        ],

        "body" => [
            "type" => "string",
            "required" => true
        ],


        "tags" => [
            "type" => "string",
            "required" => false
        ],

        "status" => [
            "type" => "string",
            "required" => true
        ],

        "comments_on" => [
            "type" => "string",
            "required" => true
        ],


        "privacy" => [
            "type" => "string",
            "required" => true
        ],

        

    ),
    elgg_echo("elgg_endpoints:api:blog:create"),
    "POST",
    true,
    true
);


elgg_ws_expose_function(
    "get_site_blogs.get",
    "getSiteBlogs",
    array(
        "filter" => array(
            "type" => "string",
            "required" => true
        ),
    ),
    elgg_echo("elgg_endpoints:api:blogs:activity"),
    "GET",
    true,
    true
);



elgg_ws_expose_function(
    "view_single_blog.get",
    "viewSingleBlog",
    array(
        "guid" => array(
            "type" => "string",
            "required" => true
        ),
    ),
    elgg_echo("elgg_endpoints:api:blogs:single:_view"),
    "GET",
    true,
    true
);



function createBlogPost($title, $excerpt, $body, $tags, $status, $comments_on, $privacy) {

    $blog = new \ElggBlog();
    $blog->title = $title;
    $blog->description = $body;
    $blog->excerpt = $excerpt;
    $blog->access_id = $privacy;
    $blog->comments_on = $comments_on;
    $blog->tags = $tags;
    $blog->status = $status;
    $blog->save();

    $payload = [
        'blog_title' => $blog->title,
        'blog_description' => $blog->description,
        'blog_tags' => $blog->tags

    ];

    return $payload;
}

function getSiteBlogs($filter){
    
    $payload = array();

    $user = get_entity(elgg_get_logged_in_user_guid());

    if($filter == 'all'){
        $content = elgg_get_entities([
            'type' => 'object',
            'subtype' => 'blog',
            
            'limit' => 0,
        ]);
    }


    
    if($filter == 'mine'){
        
        $content = elgg_get_entities([
            'type' => 'object',
            'subtype' => 'blog',
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
            'blog_title' => $row->title,
            'blog_excerpt' => strip_tags($row->excerpt),
            'blog_privacy' => $row->access_id,
            'blog_tags' => $row->tags,
            'time_created' => $row->time_created,
       
        ];
    }
        
    return $payload;
}

function viewSingleBlog($guid){
    $payload = [];
    $blog = get_entity($guid);

    if($blog){
    $payload['guid'] = $blog->guid;
    $payload['subtype'] = $blog->subtype;
    $payload['owner_guid'] = $blog->owner_guid;
    $payload['time_created'] = $blog->time_created;
    $payload['time_updated'] = $blog->time_updated;
    $payload['read_access'] = $blog->access_id;
    $payload['description'] = strip_tags($blog->description);
    $payload['tags'] = $blog->tags;
    }
   
    return $payload;
}