<?php
    $config = [
        "root" => $_SERVER['DOCUMENT_ROOT'],
        "modules" => $_SERVER['DOCUMENT_ROOT']."/include/",
        "api" => [
            "root" => $_SERVER['DOCUMENT_ROOT'],
            "version" => "1.0",
            "token_expiration" => -1,
            "client_secret" => ""
        ],
        "database" => [
            "host" => "",
            "user" => "",
            "password" => "",
            "name" => "",
            "port" => ""
        ]];
?>