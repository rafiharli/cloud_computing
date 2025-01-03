<?php
require 'vendor/autoload.php'; // AWS SDK for PHP

use Aws\S3\S3Client;

// AWS S3 Configuration
$s3 = new S3Client([
    'version' => 'latest',
    'region'  => '<AWS_REGION>',
    'credentials' => [
        'key'    => '<AWS_ACCESS_KEY>',
        'secret' => '<AWS_SECRET_KEY>',
    ],
]);

$bucket = '<S3_BUCKET_NAME>';

// Database Configuration
$dbHost = "<DB_HOST>";
$dbUser = "<DB_USER>";
$dbPass = "<DB_PASSWORD>";
$dbName = "<DB_NAME>";

$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$photoId = intval($_GET['id']);

// Get photo details
$stmt = $conn->prepare("SELECT file_name FROM photos WHERE id = ?");
$stmt->bind_param("i", $photoId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 1) {
    $photo = $result->fetch_assoc();
    $fileName = $photo['file_name'];

    try {
        // Delete from S3
        $s3->deleteObject([
            'Bucket' => $bucket,
            'Key'    => $fileName,
        ]);

        // Delete from database
        $stmt = $conn->prepare("DELETE FROM photos WHERE id = ?");
        $stmt->bind_param("i", $photoId);
        $stmt->execute();

        echo "Photo deleted successfully!";
    } catch (Exception $e) {
        echo "Error deleting photo: " . $e->getMessage();
    }
} else {
    echo "Photo not found.";
}

$conn->close();
?>
