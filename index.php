<?php
    include_once("config.php");
    include_once("include/common.php");
    include_once("include/ApiError.php");
    include_once("include/Route.php");
    include_once("include/ApiEngine.php");

    // Methods group list
    $method_groups = [
        "auth",
        "test"
    ];

    try{
        $route = new Route();
        $api_engine = new ApiEngine($method_groups);
        $api_engine->parseRequest($route->getParsedUri(), $_REQUEST);
        $api_engine->callMethod();
    } catch(ApiError $e) {
        echo $e->responseError();
    }
?>
