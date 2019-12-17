<?php
abstract class Auth {

    protected $debug;
    protected $auth;
    protected $server;
    protected $sellerId;
    protected $clientId;
    protected $clientSecret;

    public function __construct()
    {
        $this->debug = true;

        if($this->debug) { //sandbox
            $this->server = 'https://api-sandbox.getnet.com.br/';
            $this->sellerId = 'your-seller-id';
            $this->clientId = 'your-client-id';
            $this->clientSecret = 'your-client-secret';
        }
        else { // homolog - prod
            $this->server = 'https://api-homologacao.getnet.com.br/';
            $this->sellerId = 'your-seller-id';
            $this->clientId = 'your-client-id';
            $this->clientSecret = 'your-client-secret';
        }

        $this->auth = $this->getAuth();

    }

    protected function getAuth()
    {
        if(!empty($this->auth)) {
            return $this->auth;
        }
        else {

            $ch = curl_init();
            $auth = base64_encode($this->clientId . ':' . $this->clientSecret);
            curl_setopt($ch, CURLOPT_URL, $this->server . 'auth/oauth/v2/token');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                                                    'Content-Type: application/x-www-form-urlencoded',
                                                    'Authorization: Basic ' . $auth,
                                                ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, 'escope=oob&grant_type=client_credentials');

            //$ret = simplexml_load_string(curl_exec($ch), 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS);
            $ret = json_decode(curl_exec($ch), true);
            curl_close ($ch);

            $this->auth = $ret['token_type'] . ' ' . $ret['access_token'];
            return $this->auth;
        }
    }

    protected function setupEntries($entrie)
    {
        $arr = explode(' ', $entrie['name']);
        $entrie['first_name'] = $arr[0];
        $entrie['last_name'] = end($arr);
		$entrie['transaction_type'] => 'FULL';
		$entrie['number_installments'] => '1';

        return $entrie;
    }

    public function getServer()
    {
        return $this->server;
    }

    public abstract function doPayment($data);
}
