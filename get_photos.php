<?php
// Database Configuration
$dbHost = "<DB_HOST>";
$dbUser = "<DB_USER>";
$dbPass = "<DB_PASSWORD>";
$dbName = "<DB_NAME>";

$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$result = $conn->query("SELECT id, file_name, photo_url FROM photos ORDER BY uploaded_at DESC");
$photos = [];
while ($row = $result->fetch_assoc()) {
    $photos[] = $row;
}

echo json_encode($photos);

$conn->close();
?>
