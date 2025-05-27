<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Output Arrays</title>
    <style>
        body {
            background-image: url('background.jpg'); /* Replace with your image path */
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
            color: white;
        }

        table {
            border-collapse: collapse;
            width: 80%;
            margin: 20px auto;
            background-color: rgba(255, 0, 0, 0.7); /* Red background with some transparency */
        }

        th, td {
            border: 1px solid white;
            text-align: left;
            padding: 10px;
        }

        th {
            background-color: rgba(255, 0, 0, 0.9); /* Darker red for header */
        }

        h2 {
            text-align: center;
        }
    </style>
</head>
<body>

<?php
include("connect.php");
header('Content-Type: application/json'); 


if (
    isset($_POST['propertytype']) &&
    isset($_POST['year']) &&
    isset($_POST['price']) &&
    isset($_POST['space']) &&
    isset($_POST['imagepath']) &&
    isset($_POST['location']) &&
    isset($_POST['address'])
) {
    $propertytype = $_POST['propertytype'];
    $year = $_POST['year'];
    $price = $_POST['price'];
    $space = $_POST['space'];
    $imagepath = $_POST['imagepath'];
    $officeId = intval($_POST['location']); // location from form is officeId in DB
    $address = $_POST['address'];


    $sql = "INSERT INTO property (propertytype, year, price, space, imagepath, officeId, address, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'Available')";
    $stmt = mysqli_prepare($con, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sidisss", $propertytype, $year, $price, $space, $imagepath, $officeId, $address);
        if (mysqli_stmt_execute($stmt)) {
            // echo json_encode(['success' => true]);
            echo json_encode(['success' => true]); // 
            exit;
        } else {
            echo json_encode(['error' => 'Insert failed: ' . mysqli_stmt_error($stmt)]);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(['error' => 'Prepare failed: ' . mysqli_error($con)]);
    }
    mysqli_close($con);
    exit;
}

$startDate = $_POST['startDate'] ?? '';
$endDate = $_POST['endDate'] ?? '';


//delete reservation  
$customerId = isset($_POST['customerId']) ? $_POST['customerId'] : '';
$propertyid = isset($_POST['propertyid']) ? $_POST['propertyid'] : '';
$pickupdate = isset($_POST['pickupDate']) ? $_POST['pickupDate'] : '';
$formattedPickupDate = formatDate($pickupdate);
if (!empty($customerId)) {
    $checkreservation= mysqli_query($con, "SELECT * FROM reservation WHERE customerId = '$customerId' AND propertyid = '$propertyid' AND pickupdate = '$formattedPickupDate' ");
    $reser = mysqli_fetch_assoc($checkreservation);

    if ($reser) {
        // $updateQuery = "DELETE FROM reservation WHERE customerId = '$customerId' AND propertyid = '$propertyid' AND pickupdate = '$formattedPickupDate'";
        // mysqli_query($con, $updateQuery);
        $updateQuery = "UPDATE property SET status = 'Available' WHERE propertyid = '$propertyid'";
        if (mysqli_query($con, $updateQuery)) {
            header("Location: admin.html");
        } else {
            echo "Error: " . mysqli_error($con);
        }
        // $updateQuery = " DELETE reservation WHERE  customerId = '$customerId' and carId = '$carId' and pickupdate = '$pickupdate'";
        // mysqli_query($con, $updateQuery);
    }
}

function formatDate($date) {
    $formattedDate = date_create($date);
    $year = date_format($formattedDate, 'Y');
    $month = date_format($formattedDate, 'm');
    $day = date_format($formattedDate, 'd');
    return $year . '-' . $month . '-' . $day;
}

function displayTable($data) {
    if (empty($data)) {
        echo "<p>No data to display.</p>";
        return;
    }

    echo "<table>";
    echo "<tr>";
    foreach ($data[0] as $key => $value) {
        echo "<th>$key</th>";
    }
    echo "</tr>";

    foreach ($data as $row) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>$value</td>";
        }
        echo "</tr>";
    }

    echo "</table>";
}

// Function 1 to fetch reservations based on the date range

    global $con;
    $query = "SELECT * FROM reservation 
          NATURAL JOIN property 
          NATURAL JOIN customer 
          WHERE pickupdate BETWEEN ? AND ? 
          ORDER BY pickupdate";

$stmt = mysqli_prepare($con, $query);
if (!$stmt) {
    die('Prepare failed: ' . mysqli_error($con));
}

mysqli_stmt_bind_param($stmt, 'ss', $startDate, $endDate);

if (!mysqli_stmt_execute($stmt)) {
    die('Execution failed: ' . mysqli_stmt_error($stmt));
}

$result = mysqli_stmt_get_result($stmt);

$reservations = [];
while ($row = mysqli_fetch_assoc($result)) {
    $reservations[] = $row;
}

// Display the data (you should define this function)
displayTable($reservations);
    mysqli_stmt_close($stmt);

    mysqli_close($con);


?>
</body>
</html>