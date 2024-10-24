<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

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

// Fetch records
$result = $conn->query("SELECT * FROM meal_payments");
$totalAmount = 0;
$records = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
        $totalAmount += $row['total_amount'];
    }
}

// Function to generate Excel
function downloadExcel($data) {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=data.xls");
    
    $output = '<table border="1">';
    $output .= '<tr><th>Waiter Name</th><th>Meal Taken</th><th>Payment Date</th><th>Payment Method</th><th>Total Amount</th><th>Phone Number</th></tr>';
    
    foreach ($data as $row) {
        $output .= '<tr>';
        $output .= '<td>' . $row['waiter_name'] . '</td>';
        $output .= '<td>' . $row['meal_taken'] . '</td>';
        $output .= '<td>' . $row['payment_date'] . '</td>';
        $output .= '<td>' . $row['payment_method'] . '</td>';
        $output .= '<td>' . $row['total_amount'] . '</td>';
        $output .= '<td>' . $row['phone_number'] . '</td>';
        $output .= '</tr>';
    }
    
    $output .= '</table>';
    echo $output;
}

// Check if download is requested
if (isset($_GET['download'])) {
    downloadExcel($records);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Records</title>
    <link rel="stylesheet" href="fetch_data.css">
</head>
<body>
<div class="container">
    <div class="button-container">
        <a href="?download=1" class="download-button">Download as Excel</a>
        <a href="logout.php" class="logout-button">Logout</a>
    </div>
    
    <h1>Records of Meal Payments</h1>
    
    <div class="table-container">
        <table>
            <tr>
                <th>Waiter Name</th>
                <th>Meal Taken</th>
                <th>Payment Date</th>
                <th>Payment Method</th>
                <th>Total Amount</th>
                <th>Phone Number</th>
            </tr>
            <?php foreach ($records as $record): ?>
            <tr>
                <td><?php echo $record['waiter_name']; ?></td>
                <td><?php echo $record['meal_taken']; ?></td>
                <td><?php echo $record['payment_date']; ?></td>
                <td><?php echo $record['payment_method']; ?></td>
                <td><?php echo $record['total_amount']; ?></td>
                <td><?php echo $record['phone_number']; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <h3 class="total-amount">Total Amount Paid: KSh <?php echo number_format($totalAmount, 2); ?></h3>
</div>

</body>
</html>
