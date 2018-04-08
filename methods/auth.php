<?php
class auth extends Api {
    function __construct(){
        parent::$methods_list = [["name" => "signUp"],
                                 ["name" => "signIn"],
                                 ["name" => "logout", "t" => true]];
        parent::__construct();
    }

    //Methods
    function signUp($request){
        $this->checkParameters($request, array("password"));

        $request["password"] = is_($request["password"], "string", $request["password"], null, array("notrim" => true, "require" => true));

        // TODO: Добавить генерацию BTC кошелька

        $wallet = "...";

        if($this->walletExists($wallet))
            throw new ApiError(11);

        // TODO: Перегенерация кошелька в случае совпадения, а не Error

        $user = $this->newUser($wallet, password_hash($request["password"], PASSWORD_DEFAULT, array("cost" => "12")));

        if(empty($user))
            throw new ApiError(9);

        return $this->responseStatus(1, array("user" => $user));
    } 

    function signIn($request){
        $this->checkParameters($request, array("wallet", "password"));

        $request["wallet"] = is_($request["wallet"], "string", $request["wallet"], null, array("require" => true));

        $uid = $this->checkUser($request["wallet"], $request["password"]);

        if($uid === false)
            throw new ApiError(10);

        $session = $this->newSession($uid);

        return array("id" => $uid, 
                     "access_token" => $session["access"]);
    }

    function logout(){
        $this->removeSession($this->session["uid"], $this->session["access_token"]);

        return $this->responseStatus(1);
    }

    //Private functions
    private function newUser($wallet, $password){
        $q = "insert into users (wallet, password) values ('$wallet', '$password') returning id,wallet";
        $res = $this->db->query($q);
        return $res[0];
    }
    
    private function walletExists($wallet){
        $q = "select * from users where wallet = '$wallet'";
        $res = $this->db->query($q);
        
        return (!empty($res)) ? true : false;
    }
    
    private function checkUser($wallet, $password){
        $q = "select id, password from users where wallet = '$wallet'";
        $res = $this->db->query($q);
        
        if(!empty($res)) 
            if(password_verify($password, $res[0]["password"]))
                return $res[0]["id"];
        
        return false;       
    }

    //sessions
    private function newSession($uid){
        $tokens = array("access" => rand_hexstr(50));
        
        $q = "insert into sessions (uid, access_token) 
              values ($uid, '$tokens[access]')";
        $this->db->query($q); 
        
        return $tokens;
    }

    private function removeSession($uid, $token){
        $q = "delete from sessions 
              where uid = $uid and access_token = '$token'";
        $this->db->query($q);
    }
}
?>