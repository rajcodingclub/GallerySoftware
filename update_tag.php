<?php
include('scripts/settings.php');
validate(); // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sno = intval($_POST['sno']);
    $tag = mysqli_real_escape_string($con, trim($_POST['tag']));

    $query = "UPDATE images SET img_tag = '$tag' WHERE sno = $sno";
    if (mysqli_query($con, $query)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($con)]);
    }
}
?>


