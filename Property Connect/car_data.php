<?php
include("connect.php");

// Handle property submission by user
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
            header("Location: rental.html?success=1");
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

$selectedLocation = isset($_POST['location']) ? $_POST['location'] : '';
$space = isset($_POST['space']) ? $_POST['space'] : '';
$shape = isset($_POST['shape']) ? $_POST['shape'] : '';
$price = isset($_POST['price']) ? $_POST['price'] : '';
$propertyid = isset($_POST['propertyid']) ? $_POST['propertyid'] : '';
$pickupDate = isset($_POST['pickupDate']) ? $_POST['pickupDate'] : '';
$returnDate = isset($_POST['returnDate']) ? $_POST['returnDate'] : '';
$paymentMethod = isset($_POST['paymentMethod']) ? $_POST['paymentMethod'] : '';
$paymentAmount = isset($_POST['paymentAmount']) ? $_POST['paymentAmount'] : '';
$customerId =isset($_POST['customerId']) ? $_POST['customerId'] : '';
// $price1=$price-20;
// $price2=$price+20;
// Fetch car data
if(empty($selectedLocation)){
$checkCarQuery = mysqli_query($con, "SELECT * FROM property WHERE status='Available'");
}
if(!empty($space) || !empty($shape)|| !empty($price)){
    // $checkCarQuery = mysqli_query($con, "SELECT * FROM (SELECT * FROM car NATURAL JOIN office WHERE location='$selectedLocation'  AND status='active' ) WHERE (color='$color' OR bodyshape='$shape' OR price='$price')  AND status='active'  ");
     $checkCarQuery = mysqli_query($con, "SELECT * FROM property NATURAL JOIN office WHERE (propertytype='$shape' OR space='$space' OR price='$price')  AND status='Available' UNION SELECT * FROM property NATURAL JOIN office WHERE location='$selectedLocation'  AND status='Available' ");
// $checkCarQuery = mysqli_query($con, "SELECT * FROM car WHERE (color='$color' OR bodyshape='$shape' OR price='$price') AND status='active'");
}
else{
    $checkCarQuery = mysqli_query($con, "SELECT * FROM property WHERE status='Available'");
}
$cars = array();

if (!$checkCarQuery) {
    $error = ['error' => 'Query failed: ' . mysqli_error($con)];
    echo json_encode($error);
    exit;  // Terminate script execution
}

while ($row = mysqli_fetch_assoc($checkCarQuery)) {
    $cars[] = array(
        'propertytype' => $row['propertytype'],
        'year' => $row['year'],
        'price'=> $row['price'],
        'imagepath'=>$row['imagepath'],
        'propertyid' => $row['propertyid']  // Add carId to the response
   );
}

// If pickupDate is provided, update reservation status && !empty($customerId)
if (!empty($pickupDate) && !empty($returnDate) && !empty($customerId) && !empty($propertyid)) {
    $sql =  "INSERT INTO reservation  (customerId, propertyid, pickupdate, returndate, payment) VALUES ('$customerId', '$propertyid', '$pickupDate', '$returnDate', '$paymentAmount')";
    mysqli_query($con, $sql);

    $updateQuery = "UPDATE property SET status = 'Reserved' WHERE propertyid = '$propertyid'";
    mysqli_query($con, $updateQuery);
}
// Return JSON response
header('Content-Type: application/json');
echo json_encode($cars);
mysqli_close($con);
?>