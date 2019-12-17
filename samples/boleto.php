<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/GETNET-API-Client/Boleto.php';

$boleto = new Boleto();

$data = [
    'amount' => '1000',
    'name' => 'Payer Name',
    'email' => 'payer@mail.com',
    'document_type' => 'CPF', // CPF or CNPJ
    'document_number' => '00000000000',
    'phone_number' => '99999999999',
    'street' => 'Street name',
    'number' => '22',
    'complement' => 'complement',
    'district' => 'district name',
    'city' => 'city name',
    'state' => 'state name',
    'country' => 'Brazil',
    'postal_code' => 'postal_code',     
    'cardholder_name' => 'cardholder name',
    'security_code' => 'security code',
    'brand' => 'brand',
    'expiration_date' => '12/23',
    'card_number' => 'card number'
];

$retData = $boleto->doPayment($data);

$err = false;
$message = '';
if(!empty($retData['payment_id'])) {
    $message = '<b>The billing was successfully generated! <br> You can download your billing by clicking ate the following link: <b> <br><br>';
    $message .= '<a href="' . $boleto->getServer(). $retData['boleto']['_links'][0]['href'] . '" target="_BLANK"> Download! </a>';
}
else {
    $message = 'Payment unauthorized';
}

echo $message;