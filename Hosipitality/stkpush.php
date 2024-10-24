<?php
// INCLUDE THE ACCESS TOKEN FILE
include 'accessToken.php';

date_default_timezone_set('Africa/Nairobi');

$processrequestUrl = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
$callbackurl = 'https://e05b-102-209-18-26.ngrok-free.app/Room_system/callback.php'; 
$passkey = "bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919";
$BusinessShortCode = '174379';
$Timestamp = date('YmdHis');
// ENCRYPT DATA TO GET PASSWORD
$Password = base64_encode($BusinessShortCode . $passkey . $Timestamp);

// Retrieve phone number from the booking form
$phone = isset($_POST['phone']) ? $_POST['phone'] : '';

// Validate phone number format if needed
if (!preg_match('/^\d{12}$/', $phone)) {
    die('Invalid phone number format.');
}

$money = '1';  
$PartyA = $phone;
$PartyB = '174379';
$AccountReference = 'ZETECH UNIVERSITY';
$TransactionDesc = 'Food payment';
$Amount = $money;
$stkpushheader = ['Content-Type: application/json', 'Authorization: Bearer ' . $access_token];

// INITIATE CURL
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $processrequestUrl);
curl_setopt($curl, CURLOPT_HTTPHEADER, $stkpushheader); // Setting custom header
$curl_post_data = array(
    // Fill in the request parameters with valid values
    'BusinessShortCode' => $BusinessShortCode,
    'Password' => $Password,
    'Timestamp' => $Timestamp,
    'TransactionType' => 'CustomerPayBillOnline',
    'Amount' => $Amount,
    'PartyA' => $PartyA,
    'PartyB' => $PartyB,
    'PhoneNumber' => $PartyA,
    'CallBackURL' => $callbackurl,
    'AccountReference' => $AccountReference,
    'TransactionDesc' => $TransactionDesc
);

$data_string = json_encode($curl_post_data);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

$curl_response = curl_exec($curl);
if ($curl_response === false) {
    echo "Curl error: " . curl_error($curl);
} else {
    echo "Response: " . $curl_response;
    // Parse the JSON response
    $data = json_decode($curl_response);
    if (isset($data->ResponseCode) && $data->ResponseCode == "0") {
        echo "The CheckoutRequestID for this transaction is : " . $data->CheckoutRequestID;
    } else {
        echo "Error: " . $data->errorMessage;
    }
}

// CLOSE CURL
curl_close($curl);
?>
