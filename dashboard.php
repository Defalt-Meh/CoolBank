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

// Fetch customer details
$customer_id = $_SESSION['customer_id'];
$sql = "SELECT * FROM Customer WHERE customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();
$stmt->close();

// Fetch account summary
$accounts_sql = "SELECT * FROM Account WHERE customer_id = ?";
$stmt = $conn->prepare($accounts_sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$accounts = $stmt->get_result();
$stmt->close();

// Fetch loans for the logged-in customer
$loans_sql = "SELECT * FROM Loan WHERE customer_id = ?";
$stmt = $conn->prepare($loans_sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$loans = $stmt->get_result();
$stmt->close();

// Fetch branches for dropdown
$branches_sql = "SELECT branch_id, branch_name FROM Branch";
$branches_result = $conn->query($branches_sql);
$branches = [];
while ($row = $branches_result->fetch_assoc()) {
    $branches[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
    <style>
        body {
            font-family: 'Garamond', serif;
            background: url('background.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 70%;
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

        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>!</h1>
        <p>Your Customer ID: <?php echo htmlspecialchars($customer['customer_id']); ?></p>

        <div class="section">
            <h2>Your Account Summary</h2>
            <table>
                <thead>
                    <tr>
                        <th>Account Number</th>
                        <th>Branch ID</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Creation Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($account = $accounts->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($account['account_number']); ?></td>
                            <td><?php echo htmlspecialchars($account['branch_id']); ?></td>
                            <td><?php echo number_format($account['balance'], 2); ?></td>
                            <td><?php echo htmlspecialchars($account['status']); ?></td>
                            <td><?php echo htmlspecialchars($account['creation_date']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2>Your Loan Details</h2>
            <table>
                <thead>
                    <tr>
                        <th>Loan ID</th>
                        <th>Loan Amount</th>
                        <th>Interest Rate (%)</th>
                        <th>Loan Term (Years)</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Branch ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($loan = $loans->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($loan['loan_id']); ?></td>
                            <td><?php echo number_format($loan['loan_amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($loan['interest_rate']); ?></td>
                            <td><?php echo htmlspecialchars($loan['loan_term']); ?></td>
                            <td><?php echo htmlspecialchars($loan['start_date']); ?></td>
                            <td><?php echo htmlspecialchars($loan['end_date']); ?></td>
                            <td><?php echo htmlspecialchars($loan['branch_id']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2>Apply for a Loan</h2>
            <form action="apply_loan.php" method="POST">
                <label for="loan_amount">Loan Amount:</label>
                <input type="number" id="loan_amount" name="loan_amount" step="0.01" placeholder="Enter Loan Amount" required>

                <label for="interest_rate">Interest Rate (%):</label>
                <input type="number" id="interest_rate" name="interest_rate" step="0.01" placeholder="Enter Interest Rate" required>

                <label for="loan_term">Loan Term (Years):</label>
                <input type="number" id="loan_term" name="loan_term" placeholder="Enter Loan Term" required>

                <label for="branch_id">Select Branch:</label>
                <select id="branch_id" name="branch_id" required>
                    <?php foreach ($branches as $branch): ?>
                        <option value="<?php echo htmlspecialchars($branch['branch_id']); ?>">
                            <?php echo htmlspecialchars($branch['branch_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">Apply for Loan</button>
            </form>
        </div>
    </div>
</body>
</html>
