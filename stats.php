<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require 'db.php';

// Increment total students slightly on each visit to simulate live active traffic
$conn->query("UPDATE site_stats SET total_students = total_students + FLOOR(RAND() * 3) + 1 WHERE id=1");

// Get total active scholarships
$scholarships_query = $conn->query("SELECT COUNT(*) as count FROM scholarships WHERE status='Active'");
$scholarships_count = $scholarships_query->fetch_assoc()['count'];

// Get total students
$students_query = $conn->query("SELECT total_students FROM site_stats WHERE id=1");
$row = $students_query->fetch_assoc();
$students_count = $row ? $row['total_students'] : 15420;

echo json_encode([
    "status" => "success",
    "data" => [
        "total_scholarships" => $scholarships_count,
        "total_students" => $students_count
    ]
]);

$conn->close();
?>
