<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'];
    $balance = $_POST['balance'];
    $status = $_POST['status'];

    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "CoolBank";

    $conn = new mysqli($servername, $username, $password, $database);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Update account logic
    $sql = "UPDATE Account SET ";
    $updates = [];
    if (!empty($balance)) {
        $updates[] = "balance = $balance";
    }
    if (!empty($status)) {
        $updates[] = "status = '$status'";
    }
    $sql .= implode(", ", $updates);
    $sql .= " WHERE customer_id = $customer_id";

    if ($conn->query($sql) === TRUE) {
        echo "Account updated successfully!";
    } else {
        echo "Error: " . $conn->error;
    }

    $conn->close();
}
?>
