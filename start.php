<?php

elgg_register_event_handler('init', 'system', 'elgg_endpoints_init');

function elgg_endpoints_init() {
    
    \Elgg\Includer::requireFileOnce(__DIR__ . "/api/river_activity.php");
    \Elgg\Includer::requireFileOnce(__DIR__ . "/api/thewire.php");
    \Elgg\Includer::requireFileOnce(__DIR__ . "/api/blog.php");

    
}