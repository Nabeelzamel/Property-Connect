<?php
include("connect.php");

// Sanitize input data
$username = mysqli_real_escape_string($con, $_POST['username']);
$password = $_POST['password']; // The password is handled securely below
$selectedRole = $_POST["role"];

// Use a prepared statement to prevent SQL injection
$stmt = mysqli_prepare($con, "SELECT * FROM customer WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$res = mysqli_fetch_assoc($result);

// Check if the user exists and verify password
if ($res && password_verify($password, $res['password'])) {
    // Role-based access control
    if ($selectedRole == 'user' && $res['adminflag'] == 0) {
        // User-specific page
        header("Location: rental.html?customerId=" . $res['customerId']);
        exit();
    } else if ($selectedRole == 'admin' && $res['adminflag'] == 1) {
        // Admin-specific page
        header("Location: admin.html");
        exit();
    } else {
        // Role mismatch error
        echo "<script type='text/javascript'> alert('Incorrect role selected'); window.location='login.html';</script>";
    }
} else {
    // Incorrect username/password error
    echo "<script type='text/javascript'> alert('Incorrect username or password'); window.location='login.html';</script>";
}

// Close the connection
mysqli_close($con);
?>
