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

// Process the login form if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'];
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $id = $_POST['id'];

    // Call the stored procedure for login
    $sql = "CALL UserLogin(?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $role, $name, $surname, $id);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if ($role === 'customer') {
                $_SESSION['customer_id'] = $user['customer_id'];
                header("Location: dashboard.php");
                exit();
            } elseif ($role === 'employee') {
                $_SESSION['employee_id'] = $user['employee_id'];
                header("Location: employee_dashboard.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Invalid Name, Surname, or ID.";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Error: Unable to execute login.";
        header("Location: login.php");
        exit();
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
    <title>Login Page</title>
    <style>
        body {
            font-family: 'Garamond', serif;
            background: url('background.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 50%;
            margin: 5% auto;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        h1 {
            color: #333;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        label {
            margin-top: 10px;
            font-weight: bold;
        }

        input, select {
            width: 80%;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            margin-top: 15px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        .error {
            color: red;
            font-weight: bold;
            margin-top: 15px;
        }

        .register-link {
            margin-top: 20px;
            text-decoration: none;
            color: #4CAF50;
            font-weight: bold;
        }

        .register-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to CoolBank</h1>
        <form action="login.php" method="POST">
            <label for="role">Select Role:</label>
            <select id="role" name="role" required>
                <option value="customer">Customer</option>
                <option value="employee">Employee</option>
            </select>
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>
            <label for="surname">Surname:</label>
            <input type="text" id="surname" name="surname" required>
            <label for="id">ID (Customer ID or Employee ID):</label>
            <input type="password" id="id" name="id" required>
            <button type="submit">Login</button>
        </form>
        <!-- Display error message if invalid login -->
        <?php
            if (isset($_SESSION['error'])) {
            echo '<p class="error">' . htmlspecialchars($_SESSION['error']) . '</p>';
            unset($_SESSION['error']); // Clear the error message
                }
        ?>

        <a href="register.php" class="register-link">Don't have an account? Register here</a>
    </div>
</body>
</html>
