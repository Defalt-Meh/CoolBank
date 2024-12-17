<?php
session_start();

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
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $address = $_POST['address'];
    $phone_number = $_POST['phone_number'];
    $email = $_POST['email'];
    $date_of_birth = $_POST['date_of_birth'];
    $registration_date = date('Y-m-d'); // Current date
    $branch_id = 1; // Default branch (you can change this logic to select a branch dynamically)
    $balance = 0.00; // Initial balance for the new account
    $status = "Active"; // Default status for the account

    // Generate a new customer ID (incrementing the max current customer_id)
    $id_query = "SELECT MAX(customer_id) AS max_id FROM Customer";
    $id_result = $conn->query($id_query);
    $row = $id_result->fetch_assoc();
    $new_customer_id = $row['max_id'] + 1;

    // Insert new customer into the Customer table
    $insert_customer_sql = "INSERT INTO Customer (customer_id, first_name, last_name, address, phone_number, email, date_of_birth, registration_date)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_customer_sql);
    $stmt->bind_param("isssssss", $new_customer_id, $first_name, $last_name, $address, $phone_number, $email, $date_of_birth, $registration_date);

    if ($stmt->execute()) {
        // Insert the account using the same customer_id
        $insert_account_sql = "INSERT INTO Account (account_number, customer_id, branch_id, balance, creation_date, status)
                               VALUES (?, ?, ?, ?, ?, ?)";
        $account_stmt = $conn->prepare($insert_account_sql);
        $account_stmt->bind_param("iiidss", $new_customer_id, $new_customer_id, $branch_id, $balance, $registration_date, $status);

        if ($account_stmt->execute()) {
            // Registration successful, set session and redirect to dashboard
            $_SESSION['customer_id'] = $new_customer_id;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Error creating account: " . $account_stmt->error;
        }
        $account_stmt->close();
    } else {
        $error = "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        body {
            background-image: url('background.jpg');
            background-size: cover;
            font-family: Arial, sans-serif;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 400px;
            text-align: center;
        }
        h2 {
            margin-bottom: 20px;
        }
        form input, form button {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        form button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        form button:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        <form action="" method="post">
            <input type="text" name="first_name" placeholder="First Name" required>
            <input type="text" name="last_name" placeholder="Last Name" required>
            <input type="text" name="address" placeholder="Address" required>
            <input type="text" name="phone_number" placeholder="Phone Number" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="date" name="date_of_birth" required>
            <button type="submit">Register</button>
        </form>
        <!-- Display error message if registration fails -->
        <?php if (isset($error)) { ?>
            <p class="error"><?php echo $error; ?></p>
        <?php } ?>
    </div>
</body>
</html>
