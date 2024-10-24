<?php
// Database configuration
$servername = "localhost"; // Change if necessary
$username = "root"; // Change if necessary
$password = ""; // Change if necessary
$dbname = "zetech"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $waiterName = $_POST['waiterName'];
    $mealTaken = $_POST['mealTaken'];
    $paymentDate = $_POST['date'];
    $paymentMethod = $_POST['paymentMethod'];
    $totalAmount = $_POST['totalAmount'];
    $phoneNumber = $_POST['phoneNumber'];

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO meal_payments (waiter_name, meal_taken, payment_date, payment_method, total_amount, phone_number) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssds", $waiterName, $mealTaken, $paymentDate, $paymentMethod, $totalAmount, $phoneNumber);

    // Execute the statement
    if ($stmt->execute()) {
        echo "New record created successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();

    // Check payment method
    if ($paymentMethod === 'mpesa') {
        // M-Pesa STK Push Integration
        include 'accessToken.php';

        date_default_timezone_set('Africa/Nairobi');

        $processrequestUrl = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
        $callbackurl = 'https://e05b-102-209-18-26.ngrok-free.app/Room_system/callback.php'; 
        $passkey = "bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919";
        $BusinessShortCode = '174379';
        $Timestamp = date('YmdHis');

        // ENCRYPT DATA TO GET PASSWORD
        $Password = base64_encode($BusinessShortCode . $passkey . $Timestamp);

        // Validate phone number format (allowing "07" numbers)
        if (!preg_match('/^07\d{8}$/', $phoneNumber)) {
            die('Invalid phone number format. Please use a number starting with 07.');
        }

        // Convert "07" number to "254" format
        $phone = '254' . substr($phoneNumber, 1);

        // Set the amount from the form
        $money = $totalAmount;  
        $PartyA = $phone; // This will now be in "254" format
        $PartyB = '174379';
        $AccountReference = 'ZETECH UNIVERSITY';
        $TransactionDesc = 'Food payment';
        $stkpushheader = ['Content-Type: application/json', 'Authorization: Bearer ' . $access_token];

        // INITIATE CURL
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $processrequestUrl);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $stkpushheader); // Setting custom header
        $curl_post_data = array(
            'BusinessShortCode' => $BusinessShortCode,
            'Password' => $Password,
            'Timestamp' => $Timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $money,
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
                echo "The CheckoutRequestID for this transaction is: " . $data->CheckoutRequestID;
            } else {
                echo "Error: " . $data->errorMessage;
            }
        }

        // CLOSE CURL
        curl_close($curl);
    } else {
        echo "Payment method is cash.";
    }
}

// Close the connection
$conn->close();
?>
