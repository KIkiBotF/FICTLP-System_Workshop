<?php
session_start();

if (isset($_POST['editBtn'])) {
    $content_id = intval($_POST['content_id']);
    $new_name = $_POST['new_name'];
    $chapter_name = $_POST['chapter_name'];
    $subject_code = $_POST['subject_code'];

    $conn = new mysqli("100.81.48.34", "bubustailo", "Student@123", "fictlp db", "3307");
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error); 
    }

    $stmt = $conn->prepare("UPDATE content SET Name = ? WHERE Content_ID = ?");
    $stmt->bind_param("si", $new_name, $content_id);
    
    if ($stmt->execute()) {
        // Success! Go back to the main page
        header("Location: editSubjectContent.php?chapter=" . $chapter_name . "&subject=" . $subject_code);
        exit();
    } else {
        echo "Database error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: editSubjectContent.php");
    exit();
}
?>