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