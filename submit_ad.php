<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_name = $_POST['company_name'] ?? '';
    $target_url = $_POST['target_url'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';

    // Handle File Upload
    $banner_image = '';
    if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $filename = time() . '_' . basename($_FILES['banner_image']['name']);
        $target_path = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['banner_image']['tmp_name'], $target_path)) {
            $banner_image = $target_path;
        } else {
            die("<script>alert('Error uploading file.'); window.history.back();</script>");
        }
    } else {
        die("<script>alert('No valid banner image uploaded.'); window.history.back();</script>");
    }

    $stmt = $conn->prepare("INSERT INTO ads (company_name, target_url, banner_image, start_date, end_date) VALUES (?, ?, ?, ?, ?)");
    
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("sssss", $company_name, $target_url, $banner_image, $start_date, $end_date);
    
    if ($stmt->execute()) {
        echo "<script>alert('Ad submitted successfully! It will be reviewed by our admins.'); window.location.href='portal.html';</script>";
    } else {
        echo "<script>alert('Error submitting ad: " . $stmt->error . "'); window.history.back();</script>";
    }
    $stmt->close();
}
$conn->close();
?>
