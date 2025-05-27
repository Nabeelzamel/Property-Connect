<?php
include("connect.php");

// Get user input from the POST request
$name = $_POST['username'];
$email = $_POST['email'];
$password = $_POST['password'];
$fname = $_POST['fname'];
$lname = $_POST['lname'];
$phone = $_POST['phone'];

// Validate phone number (11 digits)
if (strlen($phone) != 11 || !preg_match('/^[0-9]{11}$/', $phone)) {
    echo "<script type='text/javascript'> alert('Please enter a valid phone number (11 digits).'); window.location='registration.html';</script>";
    exit();
}

// Hash the password securely
$encryptedPassword = password_hash($password, PASSWORD_DEFAULT);

// Check if the email already exists in the 'customer' table
$checkEmailQuery = mysqli_prepare($con, "SELECT * FROM customer WHERE email = ?");
mysqli_stmt_bind_param($checkEmailQuery, 's', $email);
mysqli_stmt_execute($checkEmailQuery);
mysqli_stmt_store_result($checkEmailQuery);

// If email already exists
if (mysqli_stmt_num_rows($checkEmailQuery) > 0) {
    echo "<script type='text/javascript'> alert('Email Already Exists'); window.location='registration.html';</script>";
} else {
    // Use a prepared statement to insert data into the 'customer' table
    // Note: Don't include the primary key column in the insert query (assuming it's auto-increment)
    $sql = "INSERT INTO customer (username, password, fname, lname, email, phone, adminflag) 
            VALUES (?, ?, ?, ?, ?, ?, 0)";

    $stmt = mysqli_prepare($con, $sql);
    if ($stmt) {
        // Bind parameters and execute the query
        mysqli_stmt_bind_param($stmt, 'ssssss', $name, $encryptedPassword, $fname, $lname, $email, $phone);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: login.html");
        } else {
            echo "Error: " . mysqli_error($con);
        }

        // Close the statement
        mysqli_stmt_close($stmt);
    } else {
        echo "Error: " . mysqli_error($con);
    }
}

// Close the connection
mysqli_close($con);

?>
