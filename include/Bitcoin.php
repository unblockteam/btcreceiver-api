<?php

class Bitcoin
{
    private $host;
    private $port;
    private $user;
    private $password;

    public $status;
    public $error;
    public $raw_response;
    public $response;

    private $id = 0;

    public function __construct($user, $password, $host = '127.0.0.1', $port = 7777)
    {
        $this->user = $user;
        $this->password = $password;

        $this->host = $host;
        $this->port = $port;
    }

    public function __call($method, $params = [])
    {
        $this->status = null;
        $this->error = null;
        $this->raw_response = null;
        $this->response = null;

        $params = array_values($params);

        $this->id++;

        $request = json_encode([
            'method' => $method,
            'params' => $params,
            'id' => $this->id
        ]);

        $curl = curl_init("http://{$this->user}:{$this->password}@{$this->host}:{$this->port}");
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-type: application/json'],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $request
        ];

        curl_setopt_array($curl, $options);

        $this->raw_response = curl_exec($curl);
        $this->response = json_decode($this->raw_response, true);
        $this->status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        $curl_error = curl_error($curl);
        curl_close($curl);

        if (!empty($curl_error)) {
            $this->error = $curl_error;
        }

        if ($this->response['error']) {
            $this->error = $this->response['error']['message'];
        } elseif ($this->status != 200) {
            switch ($this->status) {
                case 400:
                    $this->error = 'HTTP_BAD_REQUEST';
                    break;
                case 401:
                    $this->error = 'HTTP_UNAUTHORIZED';
                    break;
                case 403:
                    $this->error = 'HTTP_FORBIDDEN';
                    break;
                case 404:
                    $this->error = 'HTTP_NOT_FOUND';
                    break;
            }
        }

        if ($this->error) {
            return false;
        }

        return $this->response['result'];
    }
}
