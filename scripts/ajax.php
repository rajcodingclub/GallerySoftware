<?php
include('dbconnection.php');
$searchTerm = isset($_GET['term']) ? $_GET['term'] : '';

// Prepare the SQL statement to prevent SQL injection
$stmt = $con->prepare("SELECT tag FROM upload_trans WHERE tag LIKE ? group by tag");
$likeTerm = '%' . $searchTerm . '%';
$stmt->bind_param("s", $likeTerm);
$stmt->execute();
$result = $stmt->get_result();
// Fetch all tags
$tags = [];
while ($row = $result->fetch_assoc()) {
    $tags[] = $row['tag'];
}
// Close the statement and connection

echo json_encode($tags);


?>
