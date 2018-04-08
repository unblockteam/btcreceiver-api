<?php
class test extends Api {
    function __construct(){
        parent::$methods_list = [["name" => "test"]];
        parent::__construct();
    }

    //Methods
    function test($request){
        return $this->responseStatus(1);
    }
}