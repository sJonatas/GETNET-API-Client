<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/GETNET-API-Client/Credit.php';
$credit = new Credit();
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


// credito call
$retData = $credit->doPayment($data);

$err = false;
$message = '';
if(!empty($retData['payment_id'])) {
    $message = 'Your billing was successfully generated! <br> Your payment id is: <b>' 
		. $retData['payment_id'] 
		. '</b>. <br> Hold it to check with the seller.';
}
else {
    $error = true;
    $message = 'Payment unauthorized. ';
}

echo $message;