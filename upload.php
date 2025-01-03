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

// Handle file upload
if ($_FILES['photo']) {
    $fileName = $_FILES['photo']['name'];
    $fileTmp = $_FILES['photo']['tmp_name'];
    $fileKey = time() . '_' . $fileName;

    try {
        // Upload to S3
        $result = $s3->putObject([
            'Bucket' => $bucket,
            'Key'    => $fileKey,
            'SourceFile' => $fileTmp,
            'ACL'    => 'public-read',
        ]);

        $photoUrl = $result['ObjectURL'];

        // Save metadata to database
        $stmt = $conn->prepare("INSERT INTO photos (file_name, photo_url) VALUES (?, ?)");
        $stmt->bind_param("ss", $fileName, $photoUrl);
        $stmt->execute();

        echo "Photo uploaded successfully!";
    } catch (Exception $e) {
        echo "Error uploading photo: " . $e->getMessage();
    }
}
$conn->close();
?>
