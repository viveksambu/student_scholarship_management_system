<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // For development purposes

require 'db.php';

$stream = isset($_GET['stream']) ? $_GET['stream'] : '';

if (empty($stream)) {
    echo json_encode(["status" => "error", "message" => "Stream is required"]);
    exit;
}

// Prepare the statement. We fetch specific stream and 'All' which applies to all streams.
$stmt = $conn->prepare("SELECT * FROM scholarships WHERE stream = ? OR stream = 'All'");
$stmt->bind_param("s", $stream);
$stmt->execute();
$result = $stmt->get_result();

$scholarships = [];
while($row = $result->fetch_assoc()) {
    $scholarships[] = $row;
}

echo json_encode([
    "status" => "success",
    "data" => $scholarships
]);

$stmt->close();
$conn->close();
?>
