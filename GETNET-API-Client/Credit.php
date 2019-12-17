<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/getnet/auth.php';
class Credit extends Auth{

    private $customerId;

    public function __construct()
    {
        parent::__construct();
        $this->customerId = base64_encode(time());
    }

    private function tokenizeCard($card)
    {
        $ch = curl_init();

        $body = json_encode(['card_number' => $card, 'customer_id' => $this->customerId]);

        curl_setopt($ch, CURLOPT_URL, $this->server . 'v1/tokens/card');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                                                'Content-Type: application/json; charset=utf-8',
                                                'Authorization: ' . $this->getAuth()
                                            ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

        $response = curl_exec($ch);

        $response = json_decode(gzdecode($response), true);

        if(!empty($response['status_code'])) {
            echo '[ERR: ' . $response['status_code']
                          . '] ' . $response['name']
                          . ' - ' . $response['message']
                          . ' DETAULS: ' . json_encode($response['details']);
            echo '<br> Application terminated';
            die;
        }
        else {
            return $response['number_token'];
        }
    }

    public function doPayment($data)
    {

        $data = $this->setupEntries($data);

        $body = [
                'seller_id' => $this->sellerId,
                'amount' => $data['amount'],
                'currency' => 'BRL',
                'order' => [
                    'order_id' => base64_encode(time()),
                    'sales_tax' => 0,
                    'product_type' => 'service'
                ],
                'customer' => [
                    'customer_id' => $this->customerId,
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'document_type' => $data['document_type'],
                    'document_number'=> $data['document_number'],
                    'phone_number' => '12982065621',
                    'billing_address' => [
                        'street' => $data['street'],
                        'number' => $data['number'],
                        'complement' => $data['complement'],
                        'district' => $data['district'],
                        'city' => $data['city'],
                        'state' => $data['state'],
                        'country' => $data['country'],
                        'postal_code' => $data['postal_code']
                    ]
                ],
                'credit' => [
                    'delayed' => false,
                    'authenticated' => true,
                    'pre_authorization' => false,
                    'save_card_data' => false,
                    'transaction_type' => $data['transaction_type'],
                    'number_installments' => $data['number_installments'],
                    'soft_descriptor' => 'RESERVA POUSADA',
                    'card' => [
                        'number_token' => $this->tokenizeCard($data['card_number']),
                        'cardholder_name' => $data['cardholder_name'],
                        'security_code' => $data['security_code'],
                        //'brand' => $data['brand'],
                        'expiration_month' => explode('/', $data['expiration_date'])[0],
                        'expiration_year' => explode('/', $data['expiration_date'])[1]
                    ]
                ]
            ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->server . 'v1/payments/credit');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                                                'Content-Type: application/json; charset=utf-8',
                                                'Authorization: ' . $this->getAuth()
                                            ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        $response = curl_exec($ch);
        $response = json_decode(gzdecode($response), true);

        if(!empty($response['status_code'])) {
            echo '[Err ' . $response['status_code']
                        . '] ' . $response['name']
                        . ' - ' . $response['message']
                        . '. ' . ' DETAILS: ' . json_encode($response['details']) . '<br>';

            echo 'Application terminated';
            die;
        }
        else {
            // if authenticated is seted up to true, your proccess ends here.
            // so, just use $response variable to manage the output.
            // if if delayed or pre_authorization is seted to true is set to true, you need follow the next step to finish the proccess
            if($body['credit']['authenticated']) {
                return $response;
            }
            else {
                if($response['status'] == 'AUTHORIZED') {
                    return $this->lateCOnfirm($response);
                }
                else {
                    return $response;
                }
            }
        }

    }

    private function lateCOnfirm($data)
    {
        $body = ['amount', $data['amount']];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->server . 'v1/payments/credit/' . $data['payment_id']. '/confirm');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                                                'Content-Type: application/json; charset=utf-8',
                                                'Authorization: ' . $this->getAuth()
                                            ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        $response = json_decode(curl_exec($ch), true);

        return $response;
    }

}
