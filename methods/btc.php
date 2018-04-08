<?php
class btc extends Api {
    function __construct(){
        parent::$methods_list = [["name" => "balance"]];
        parent::__construct();
    }

    //Methods
    function balance($request){
        $this->checkParameters($request, array("wallet"));

        $request["wallet"] = is_($request["wallet"], "string", $request["wallet"], null, array("require" => true));

        global $config;
        $btc = new Bitcoin($config['rpc']['user'], $config['rpc']['password']);
        
        $list_addresses = $btc->listaddresses();

        if (isset($btc->error)) {
            exit(json_encode(['error' => $btc->error]));
        }

        $history = $btc->history();

        if (isset($btc->error)) {
            exit(json_encode(['error' => $btc->error]));
        } else {
            $special_history = [];

            foreach ($history as $tx) {
                if ($tx['confirmations'] < 1 || $tx['value'] <= 0) continue;

                // если несколько выходов, то берем тот адрес кошелька, который принадлежит нам
                $address = array_values(array_intersect($tx['output_addresses'], $list_addresses))[0];

                if ($address == $request["wallet"]) {
                    if (isset($special_history[$address])) {
                        $data = $special_history[$address];
                        unset($special_history[$address]);

                        $special_history[$address] = [
                            'hash' => $tx['txid'],
                            'to' => $address,
                            'value' => number_format($data['value'] + $tx['value'], 6)
                        ];
                    } else {
                        $special_history[$address] = [
                            'hash' => $tx['txid'],
                            'to' => $address,
                            'value' => number_format($tx['value'], 6)
                        ];
                    }
                }
            }

            $special_history = array_values($special_history);
            if (isset($special_history[0]["value"])) {
                $balance = $special_history[0]["value"];
            } else {
                $balance = 0;
            }

            return ["balance" => $balance];
        }
    }
}