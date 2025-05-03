<?php
include('dbconnection.php');

if (isset($_GET['term'])) {
    $term = $con->real_escape_string($_GET['term']);
    $query = "SELECT img_department FROM upload WHERE img_department LIKE '%$term%' group by img_department LIMIT 10";
    $result = $con->query($query);

    $data = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row['img_department'];
        }
    }

    echo json_encode($data);
}
?>