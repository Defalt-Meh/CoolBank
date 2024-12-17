<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php?error=1");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "CoolBank";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loan_amount = $_POST['loan_amount'];
    $interest_rate = $_POST['interest_rate'];
    $loan_term = $_POST['loan_term'];
    $branch_id = $_POST['branch_id'];
    $customer_id = $_SESSION['customer_id'];

    // Validate inputs
    if ($loan_amount <= 0 || $interest_rate <= 0 || $loan_term <= 0) {
        $error_message = "All fields must have positive values.";
    } else {
        // Call the ReserveLoan stored procedure
        $sql = $conn->prepare("CALL ReserveLoan(?, ?, ?, ?, ?)");
        $sql->bind_param("idddi", $customer_id, $loan_amount, $interest_rate, $loan_term, $branch_id);

        if ($sql->execute()) {
            $success_message = "Loan successfully applied!";
        } else {
            $error_message = "Error: " . $conn->error;
        }
        $sql->close();
    }
}

// Fetch branches for dropdown
$branches_query = "SELECT branch_id, branch_name FROM Branch";
$branches_result = $conn->query($branches_query);
$branches = [];
while ($row = $branches_result->fetch_assoc()) {
    $branches[] = $row;
}

$conn->close();
?>
