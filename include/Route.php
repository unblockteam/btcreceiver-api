<?php
include_once("ApiError.php");
class Route {
    private $uri = array();
    private $regexp = array("uri" => "/^\/api\/method\/./",
                            "method" => "/^\/api\/method\/(?'group'[a-zA-Z_]+)\.(?'method'[a-zA-Z_]+)(\?|$)/");
    function __construct(){
        if(preg_match($this->regexp["uri"],$_SERVER["REQUEST_URI"]) === 1){
            if(preg_match($this->regexp["method"], $_SERVER["REQUEST_URI"], $matches) === 1){
                $this->uri["group"] = $matches["group"];
                $this->uri["method"] = $matches["method"];
            } else 
                throw new ApiError(2);
        } else 
            $this->ErrorPage403();
    }
    function getParsedUri(){
        if(!empty($this->uri))
            return $this->uri; 
    }
    function errorPage403(){
        header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
        header("Status: 403 Forbidden");
        exit('<h1 align="center">403 Forbidden</h1><hr>');
    }
}
?>
