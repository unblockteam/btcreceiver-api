<?php
function rand_hexstr($len){
    return bin2hex(openssl_random_pseudo_bytes(floor($len/2)));
}
function esc($str, $no_trim){
    if(isset($no_trim) && $no_trim !== true)
        $str = trim($str);
    $str = htmlspecialchars($str);
    $str = pg_escape_string($str);
    return $str;
}
function is_($var, $type, $ret_t, $ret_f, $opts = null){
    if(!isset($ret_t))
            $ret_t = true;
    if(!isset($ret_f))
            $ret_f = false;
    $types = array("id", "string", "token", "rettype", "date",
                   "int", "pint", "pzint", "boolint", "array",
                   "email", "list", "hexstring");
    $patterns = array("id" => '/^[1-9]+$/',
                      "int" => '/^[+-]{0,1}[\d]+$/',
                      "date_iso8601" => '/^\d{4}-\d{2}-\d{2}$/',
                      "token" => '/^[\da-f]{50}$/',
                      "email" => '/^([\-a-zA-Z0-9_\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\-a-zA-Z0-9]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/');
/*
 *
 *    if(isset($type) && array_key_exists($type, $patterns)){
 *        if(preg_match($patterns[$type], $var))
 *            return $ret_t;
 *    }
 *    return $ret_f;
 *
 */
    if(isset($type) && in_array($type, $types)){
        switch($type){
            case 'pint':
                $type = "int";
                $opts["min"] = 1;
                break;
            case 'pzint':
                $type = "int";
                $opts["min"] = 0;
                break;
            case 'boolint':
                $type = "int";
                $opts["min"] = 0;
                $opts["max"] = 1;
                break;
        }
        if(isset($var)){
            switch($type){
            case 'id':
                if(preg_match($patterns["id"], $var))
                    return $ret_t;
                break;
            case 'int':
                if(preg_match($patterns["int"], $var)){
                    if($opts["zero"] && $var === 0)
                        return $ret_t;
                    if(isset($opts["min"]) && $var < $opts["min"])
                        return $ret_f;
                    if(isset($opts["max"]) && $var > $opts["max"])
                        return $ret_f;
                    return $ret_t;
                }
                break;
            case 'string':
                $ntr = false;
                if($opts){
                    $ntr = (isset($opts["notrim"]) && $opts["notrim"] === true) ? true : false;
                    if(isset($opts["require"]) && $opts["require"] == true)
                            if ($var === "")
                                return $ret_f;
                    if(isset($opts["min_len"]) && is_numeric($opts["min_len"])){
                        if(!$ntr){
                            $ret_t = trim($ret_t);
                            $ntr = false;
                        }
                        $len = mb_strlen($ret_t, 'utf-8');
                        if($len != 0 && $len < $opts["min_len"])
                            return $ret_f;
                    }
                    if(isset($opts["max_len"]) && is_numeric($opts["max_len"])){
                        if(!$ntr){
                            $ret_t = trim($ret_t);
                            $ntr = false;
                        }
                        if($len != 0 && $len > $opts["max_len"])
                            return $ret_f;
                    }
                }
                return esc($ret_t, $ntr);
                break;
            case 'token':
                if(preg_match($patterns["token"], $var))
                    return $ret_t;
                break;
            case 'email':
                if(filter_var($var, FILTER_VALIDATE_EMAIL))
                    return $ret_t;
                break;
            case 'date':
                if(preg_match($patterns["date_iso8601"], $var)){
                    $dt = explode('-', $var);
                    if(checkdate($dt[1],$dt[2], $dt[0]))
                        return $ret_t;
                }
                break;
            case 'rettype':
                if($var === 'full')
                    return $ret_t;
                break;
            case 'array':
                if($opts && is_array($opts)){
                    if(in_array($var, $opts))
                        return $ret_t;
                }
                break;
            case 'list':
                $var = explode(',', $var);
                if($opts){
                    if(isset($opts["values"]) && is_array($opts["values"])){
                        foreach ($var as $item) {
                            if(!in_array($item, $opts["values"]))
                                return $ret_f;
                        }
                    }
                }
                return $var;
                break;
            case 'hexstring':
                $length_pattern = "+";
                if($opts && isset($opts["length"]))
                    $length_pattern = '{'.$opts["length"]."}";
                if(preg_match('/^[\da-f]'.$length_pattern.'$/', $var))
                    return $ret_t;
                break;
            }
            return $ret_f;
        } else 
            return $ret_f;
    }
}
?>