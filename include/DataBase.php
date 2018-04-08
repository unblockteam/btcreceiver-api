<?php
include_once("ApiError.php");
class DataBase {
    private $connect_link = null;
    private $host;
    private $dbname;
    private $user;
    private $password;
    private $port;
    private $res_indexes = array("assoc" => PGSQL_ASSOC, 
                                 "num" => PGSQL_NUM);
    function __construct(){
        global $config;
        $this->host = $config["database"]["host"];
        $this->dbname = $config["database"]["name"];
        $this->user = $config["database"]["user"];
        $this->password = $config["database"]["password"];
        if(isset($config["database"]["port"]))
            $this->port = $config["database"]["port"];
    }
    public function connect(){
        if(is_null($this->connect_link)){
            $cnt_str = "host=$this->host dbname=$this->dbname user=$this->user password = $this->password";
            $cnt_str .= $this->port ? " port = $this->port" : "";
            $this->connect_link = pg_connect($cnt_str);
            if(!$this->connect_link)
                throw new ApiError(2, "debug");
        }
        return $this->connect_link;
    }
    public function disconnect(){
        if(!is_null($this->connect_link))
           return pg_close($this->connect_link);
    }
    public function query($q, $ind = "assoc"){
        if(is_null($this->connect_link))
            $this->connect();
        $res = pg_query($q);
        if(!$res) 
            throw new ApiError(3, "debug", pg_last_error());
        $result = array();
        while($row = pg_fetch_array($res, null, $this->res_indexes[$ind])){
            foreach($row as $key => $value)
                if($value == "null" || $value == "")
                    unset($row[$key]);
            $result[] = $row;
        }
        return $result;
    }
}
?>