<?php
include("connect.php");

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $startDate = $_POST['startDate'] ?? '';
    $endDate = $_POST['endDate'] ?? '';

    if (!$startDate || !$endDate) {
        echo "<p>Please provide both start and end dates.</p>";
    } else {
        // Prepare the query
        $query = "SELECT * FROM reservation 
                  NATURAL JOIN property 
                  NATURAL JOIN customer 
                  WHERE pickupdate BETWEEN ? AND ? 
                  ORDER BY pickupdate";

        $stmt = mysqli_prepare($con, $query);
        if (!$stmt) {
            die("Prepare failed: " . mysqli_error($con));
        }

        mysqli_stmt_bind_param($stmt, 'ss', $startDate, $endDate);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $reservations = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $reservations[] = $row;
        }

        displayTable($reservations);

        mysqli_stmt_close($stmt);
    }

    mysqli_close($con);
}

// Helper function to display results as an HTML table
function displayTable($data) {
    if (empty($data)) {
        echo "<p>No reservations found in the selected date range.</p>";
        return;
    }

    echo "<table border='1' cellpadding='5'>";
    echo "<tr>";
    foreach (array_keys($data[0]) as $key) {
        echo "<th>" . htmlspecialchars($key) . "</th>";
    }
    echo "</tr>";

    foreach ($data as $row) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value) . "</td>";
        }
        echo "</tr>";
    }

    echo "</table>";
}
?>
