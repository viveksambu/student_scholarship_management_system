<?php
require 'db.php';

header('Content-Type: application/json');

$current_date = date('Y-m-d');
// Fetch the first approved ad whose current date is within the timeline
$sql = "SELECT id, company_name, target_url, banner_image, start_date, end_date FROM ads WHERE status = 'approved' AND start_date <= ? AND end_date >= ? ORDER BY id DESC LIMIT 1";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $conn->error]);
    exit;
}
$stmt->bind_param("ss", $current_date, $current_date);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode(['success' => true, 'ad' => $row]);
} else {
    echo json_encode(['success' => false, 'message' => 'No active ads found.']);
}

$stmt->close();
$conn->close();
?>
