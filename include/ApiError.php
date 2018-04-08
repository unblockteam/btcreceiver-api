<?php
include_once("Response.php");
class ApiError extends Exception{
    public static $errors_list = array("common" => array(0 => "",
                                                         1 => "Required parameters",
                                                         2 => "Method does not exist",
                                                         3 => "Required parameters of method",
                                                         4 => "Required parameter",
                                                         5 => "Incorrect parameter",
                                                         6 => "Required access token",
                                                         7 => "Incorrect signature",
                                                         8 => "Token is expired",
                                                         9 => "Internal server error", 
                                                        10 => "Incorrect login or password",
                                                        11 => "Login already exists",
                                                        12 => "Required signature",
                                                        13 => "Incorrect access token",
                                                        14 => "Access denied",
                                                        15 => "Required authorization",
                                                        16 => "Already authorized"
                                                        ),
                                        "debug" => array(1 => "File with required api does not exist",
                                                         2 => "Unable connect to database",
                                                         3 => "SQL-query error")
                                                        );
    private $status;
    function __construct($code, $stat = "common", $add_msg = "", $add_msg_delimeter = ""){
        if(array_key_exists($stat, self::$errors_list) && array_key_exists($code, self::$errors_list[$stat])){
            $this->code = $code;
            $this->message = self::$errors_list[$stat][$code];
            if($add_msg != "") {
                if($stat === "debug"){
                    $this->message .= " ( $add_msg )";
                } else {
                    if (is_array($add_msg)) {
                        foreach ($add_msg as $value) {
                            $this->message .= " $value $add_msg_delimeter";
                        }
                        $this->message = substr($this->message, 0, (strlen($add_msg_delimeter) + 1) * -1);
                    } else {
                        $this->message .= " $add_msg";
                    }
                }
            }
            $this->status = ($stat != "common") ? $stat : null;
        }
    }
    function responseError(){
        $error = array();
        if($this->status)
            $error["status"] = $this->status;
        $error["code"] = $this->code; 
        $error["description"] = $this->message;
        return Response::resultError($error);
    }
}
?>
