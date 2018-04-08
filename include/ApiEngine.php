<?php
include_once("ApiError.php");
include_once("Response.php");
class ApiEngine {
    private $config = null;
    private $method_name = null;
    private $method_args = null;
    public static $method_groups = array();
    function __construct($groups){
        global $config;
        $this->config = $config;
        if(isset($groups))
            self::$method_groups = $groups;
    }
    function __destruct(){
    }
    function parseRequest($uri, $request){
        $this->method_name = $uri; 
        $this->method_args = $request; 
    }
    function callMethod(){
        $api_name = strtolower($this->method_name["group"]);
        if(in_array($api_name, self::$method_groups)){
            if(file_exists($this->config["api"]["root"]."/methods/".$api_name.'.php')){
                $api = $this->getApi($api_name);
                $api_method = $this->method_name["method"]; 
                $method_settings = $api->checkMethod($api_method);
                if($method_settings){
                    if($method_settings["s"])
                        if(!$api->checkSignature($api_name.".".$api_method, $this->method_args))
                            throw new ApiError(7);
                    if($method_settings["t"]){
                        $session = $api->checkAuthorization($this->method_args);
                        $api->initSession($session); 
                    }
                    $result = $api->$api_method($this->method_args);
                    $this->responseResult($result);
                } else {
                    throw new ApiError(2); 
                }
            } else {
                throw new ApiError(1, "debug");
            }
        } else {
            throw new ApiError(2); 
        }
    }
    private function getApi($group_name){
        include_once($this->config["modules"]."Api.php");
        include_once($this->config["api"]["root"]."/methods/".$group_name.".php");
        return new $group_name();
    }
    private function checkMethodGroup($group_name){
        return ($group_name && in_array($group_name, self::$method_groups)) ? true : false;
    }
    private function responseResult($resp){
        echo Response::resultJSON($resp);
    }
}
?>