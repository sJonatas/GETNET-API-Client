<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/getnet/auth.php';
class Boleto extends Auth{

    private $customerId;

    public function __construct()
    {
        parent::__construct();
        $this->customerId = base64_encode(time());
    }

    public function doPayment($data)
    {
        $data = $this->setupEntries($data);
        $bol = $this->setupBoleto();

        $body = [
                    'seller_id' => $this->sellerId,
                    'amount' => $data['amount'],
                    'currency' => 'BRL',
                    'order' => [
                        'order_id' => base64_encode(time()),
                        'sales_tax' => 0,
                        'product_type' => 'service'
                    ],
                    'boleto' => $bol,
                    'customer' => [
                        'first_name' => $data['first_name'],
                        'name' => $data['name'],
                        'document_type' => $data['document_type'],
                        'document_number'=> $data['document_number'],
                        'billing_address' => [
                            'street' => $data['street'],
                            'number' => $data['number'],
                            'complement' => $data['complement'],
                            'district' => $data['district'],
                            'city' => $data['city'],
                            'state' => $data['state'],
                            'postal_code' => $data['postal_code']
                        ]
                    ]
                ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->server . 'v1/payments/boleto');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                                                'Content-Type: application/json; charset=utf-8',
                                                'Authorization: ' . $this->getAuth()
                                            ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        $response = curl_exec($ch);
        $response = json_decode(gzdecode($response), true);

        return $response;
    }

    /*
     *
     * here you can setup your rules for boletos generation
    */
    private function setupBoleto()
    {
        $bol = [
                'our_number' => '',
                'document_number' => '',
                'expiration_date' => '', // dd/MM/yyyy format
                'instructions' => '',
                'provider' => 'santander'
            ];

        return $bol;
    }
}
