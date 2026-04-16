<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $provider = $_POST['provider'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $stream = $_POST['stream'] ?? '';
    $deadline = $_POST['deadline'] ?? '';
    $apply_url = $_POST['apply_url'] ?? '';
    $description = $_POST['description'] ?? '';

    $stmt = $conn->prepare("INSERT INTO suggested_scholarships (name, provider, amount, stream, deadline, apply_url, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("sssssss", $name, $provider, $amount, $stream, $deadline, $apply_url, $description);
    
    if ($stmt->execute()) {
        echo "<script>alert('Scholarship suggested successfully! It will be reviewed by our admins.'); window.location.href='portal.html';</script>";
    } else {
        echo "<script>alert('Error submitting suggestion: " . $stmt->error . "'); window.history.back();</script>";
    }
    $stmt->close();
}
$conn->close();
?>
