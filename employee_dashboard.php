<?php
session_start();

// Check if the user is logged in as an employee
if (!isset($_SESSION['employee_id'])) {
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

// Fetch all accounts and their associated customer details
$sql = "SELECT Account.account_number, Customer.customer_id, Customer.first_name, Customer.last_name, 
               Account.branch_id, Account.balance, Account.status, Account.creation_date 
        FROM Account 
        JOIN Customer ON Account.customer_id = Customer.customer_id 
        ORDER BY Account.creation_date DESC";
$result = $conn->query($sql);

// Fetch all loans and associated customer and branch details
$loans_sql = "SELECT Loan.loan_id, Loan.customer_id, Customer.first_name AS customer_first_name, 
                     Customer.last_name AS customer_last_name, Loan.loan_amount, Loan.interest_rate, 
                     Loan.loan_term, Loan.start_date, Loan.end_date, Loan.branch_id, Branch.branch_name
              FROM Loan 
              JOIN Customer ON Loan.customer_id = Customer.customer_id
              JOIN Branch ON Loan.branch_id = Branch.branch_id
              ORDER BY Loan.start_date DESC";
$loans = $conn->query($loans_sql);

// Fetch all customers
$customers_sql = "SELECT * FROM Customer ORDER BY registration_date DESC";
$customers = $conn->query($customers_sql);

// Handle account update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_account'])) {
    $account_number = $_POST['account_number'];
    $new_balance = $_POST['balance'];
    $new_status = $_POST['status'];

    // Update account balance and/or status
    $update_sql = "UPDATE Account SET balance = IFNULL(?, balance), status = IFNULL(?, status) WHERE account_number = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("dsi", $new_balance, $new_status, $account_number);

    if ($stmt->execute()) {
        $update_message = "Account updated successfully for Account Number $account_number.";
    } else {
        $update_error = "Failed to update account: " . $conn->error;
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
    <title>Employee Dashboard</title>
    <style>
        body {
            font-family: 'Garamond', serif;
            background: url('background.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 5% auto;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        h1, h2 {
            color: #333;
            text-align: center;
        }

        .section {
            margin-bottom: 30px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin: 20px 0;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ccc;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        td {
            background-color: #f9f9f9;
        }

        form {
            margin-top: 20px;
        }

        input, select, button {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        .message {
            color: green;
            font-weight: bold;
            text-align: center;
        }

        .error {
            color: red;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Employee Dashboard</h1>

        <!-- Display success or error messages -->
        <?php if (isset($update_message)): ?>
            <p class="message"><?php echo $update_message; ?></p>
        <?php endif; ?>
        <?php if (isset($update_error)): ?>
            <p class="error"><?php echo $update_error; ?></p>
        <?php endif; ?>

        <!-- Accounts Summary -->
        <div class="section">
            <h2>All Account Summaries</h2>
            <table>
                <thead>
                    <tr>
                        <th>Account Number</th>
                        <th>Customer ID</th>
                        <th>Customer Name</th>
                        <th>Branch ID</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Creation Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['account_number']; ?></td>
                            <td><?php echo $row['customer_id']; ?></td>
                            <td><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                            <td><?php echo $row['branch_id']; ?></td>
                            <td><?php echo number_format($row['balance'], 2); ?></td>
                            <td><?php echo $row['status']; ?></td>
                            <td><?php echo $row['creation_date']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Loans Summary -->
        <div class="section">
            <h2>All Customer Loan Details</h2>
            <table>
                <thead>
                    <tr>
                        <th>Loan ID</th>
                        <th>Customer ID</th>
                        <th>Customer Name</th>
                        <th>Loan Amount</th>
                        <th>Interest Rate (%)</th>
                        <th>Loan Term (Years)</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Branch Name</th>
                        <th>Branch ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($loan = $loans->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $loan['loan_id']; ?></td>
                            <td><?php echo $loan['customer_id']; ?></td>
                            <td><?php echo $loan['customer_first_name'] . ' ' . $loan['customer_last_name']; ?></td>
                            <td><?php echo number_format($loan['loan_amount'], 2); ?></td>
                            <td><?php echo $loan['interest_rate']; ?></td>
                            <td><?php echo $loan['loan_term']; ?></td>
                            <td><?php echo $loan['start_date']; ?></td>
                            <td><?php echo $loan['end_date']; ?></td>
                            <td><?php echo $loan['branch_name']; ?></td>
                            <td><?php echo $loan['branch_id']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Customers Summary -->
        <div class="section">
            <h2>All Customer Details</h2>
            <table>
                <thead>
                    <tr>
                        <th>Customer ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Address</th>
                        <th>Phone Number</th>
                        <th>Email</th>
                        <th>Date of Birth</th>
                        <th>Registration Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($customer = $customers->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $customer['customer_id']; ?></td>
                            <td><?php echo $customer['first_name']; ?></td>
                            <td><?php echo $customer['last_name']; ?></td>
                            <td><?php echo $customer['address']; ?></td>
                            <td><?php echo $customer['phone_number']; ?></td>
                            <td><?php echo $customer['email']; ?></td>
                            <td><?php echo $customer['date_of_birth']; ?></td>
                            <td><?php echo $customer['registration_date']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Update Account Form -->
        <div class="section">
            <h2>Update Account Details</h2>
            <form action="employee_dashboard.php" method="POST">
                <label for="account_number">Account Number:</label>
                <input type="number" id="account_number" name="account_number" placeholder="Enter Account Number" required>

                <label for="balance">New Balance (Optional):</label>
                <input type="number" id="balance" name="balance" step="0.01" placeholder="Enter New Balance">

                <label for="status">Update Status (Optional):</label>
                <select id="status" name="status">
                    <option value="">Select Status</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                    <option value="Closed">Closed</option>
                </select>

                <button type="submit" name="update_account">Update Account</button>
            </form>
        </div>
    </div>
</body>
</html>
