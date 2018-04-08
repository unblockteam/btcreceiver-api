<?php
class Response{
    public static function resultList($list){
        return array("count" => count($list), "items" => $list);
    }
    public static function resultStatus($status, $add_response = null){
        $ret = array("success" => $status);
        if(isset($add_response) && !empty($add_response))
            foreach($add_response as $key => $value)
                $ret[$key] = $value;
        return $ret;
    }
    public static function resultJSON($result){
        header('Content-Type: application/json; charset=utf-8');
        return json_encode(array("response" => $result), JSON_UNESCAPED_UNICODE);
    }
    public  static function resultError($result){
        return json_encode(array("error" => $result));
    }
    public static function arrayTransform($result, $pat){
        $mod_result = array();
        foreach($pat as $key => $value)
            if(is_array($value))
                $mod_result[$key] = self::arrayTransform($result, $value);
            else{
                $res_val = (is_numeric($key)) ? $result[$value] : $result[$key];
                if($res_val != null)
                    $mod_result[$value] = $res_val;
            }
        return $mod_result;
    }
}
?>