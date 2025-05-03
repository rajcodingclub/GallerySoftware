<?php
include('dbconnection.php');

if (isset($_POST['sno']) && isset($_POST['like_status'])) {
    $sno = $_POST['sno'];
    $like_status = $_POST['like_status']; // This will be 1 or 0

    $query = "UPDATE images SET like_status = ? WHERE sno = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param('ii', $like_status, $sno);

    if ($stmt->execute()) {
        echo "Success";
    } else {
        echo "Error: " . $con->error;
    }

    $stmt->close();
    $con->close();
}
?>



