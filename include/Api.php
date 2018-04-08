<?php
include_once("DataBase.php");
include_once("ApiError.php");
include_once("Response.php");
class Api {
    protected $db = null; 
    protected static $methods_list;
    protected $session = array();
    function __construct(){
        foreach(self::$methods_list as &$item)
            $item["name"] = strtolower($item["name"]);
        $this->db = new DataBase();
        $this->db->connect();  
    }
    function checkSignature($method, $request){
        if(!isset($request["s"]))
            throw new ApiError(12);
        $signature = $request["s"];
        unset($request["s"]);
        $reqsize = count($request);
        $signhash = $method;
        if($reqsize > 0){
            $signhash .= "?"; 
            foreach($request as $key => $value)
                $signhash .= $key."=".$value."&";
            $signhash = substr($signhash, 0, strlen($signhash) - 1); 
        }
        global $config;
        $signhash = sha1($signhash.$config["api"]["client_secret"]);
        return ($signhash === $signature) ? true : false;
    }
    function checkAuthorization($request){
        if(!isset($request["access_token"]))
            throw new ApiError(6);
        $request["access_token"] = is_($request["access_token"], "token", $request["access_token"], null);
        if($request["access_token"] === false)
            throw new ApiError(13);
        global $config;
        if($config["api"]["token_expiration"] == -1)
            $q = "select 0 "; 
        else 
            $q = "select case 
                        when extract(epoch from current_timestamp(0)) - extract(epoch from s.start) > {$config["api"]["token_expiration"]}
                            then 1
                            else 0
                    end ";
            $q= "as expired, s.uid as uid, u.wallet as wallet  
                 from sessions s
                 left join users u on s.uid = u.id
                 where s.access_token = '$request[access_token]'";
        $res = $this->db->query($q);
        if(empty($res))
            throw new ApiError(13);
        if($res[0]["expired"] == "1")
            throw new ApiError(8);
        else 
            return array("uid" => $res[0]["uid"], 
                         "wallet" => $res[0]["wallet"],
                         "access_token" => $request["access_token"]);
    }
    function initSession($s){
        $this->session = $s;
    }
    function checkMethod($method_name){
        if($method_name != null)
            foreach(self::$methods_list as $method){
                if($method["name"] == strtolower($method_name)){
                    if($method["dev"] && SERVICE_MODE === "production")
                        return false;
                    return $method;
                }
            }
        return false;
    }
    function responseArray($result, $pat){
        return Response::arrayTransform($result, $pat);
    }
    function responseResultList($items){
        return Response::resultList($items);
    }
    function responseStatus($status, $add_response = null){
        return Response::resultStatus($status, $add_response);
    }
    protected function checkParameters($request, $needed, $operation = "and"){
        if(empty($request))
            throw new ApiError(3);
        if($operation == "or")
            $not_set = 0;
        foreach($needed as $value){
            if(!isset($request[$value])){
                if($operation == "or"){
                    $not_set++;
                } else {
                    throw new ApiError(4, "common", $value);
                }
            }
        }
        if($operation == "or"){
            if($not_set == count($needed))
                throw new ApiError(4, "common", $needed, "or");
        }
    }
    function __destruct(){
        if(isset($this->db))
            $this->db->disconnect(); 
    }
}
?>
