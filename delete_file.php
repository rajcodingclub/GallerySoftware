<?php
include('scripts/settings.php');
validate(); // Database connection

if (isset($_POST['file_id'])) {
    $fileId = $_POST['file_id'];

    // Fetch file path
    $query = "SELECT file_path FROM images WHERE sno = '$fileId'";
    $result = mysqli_query($con, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        $filePath = $row['file_path'];

        // Delete file from the database
      echo  $deleteQuery = "DELETE FROM images WHERE sno = '$fileId'";
        if (mysqli_query($con, $deleteQuery)) {
            // Delete the physical file
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            echo 'success';
        } else {
            echo 'error';
        }
    } else {
        echo 'error';
    }
}
?>


