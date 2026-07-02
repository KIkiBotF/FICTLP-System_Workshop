<?php
session_start();

// 1. Get the variables we passed through the URL safely
// We use intval() for the ID to ensure it's strictly a number, which prevents SQL injection.
$content_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$chapter_id = isset($_GET['chapter']) ? htmlspecialchars($_GET['chapter']) : '1';
$subject_code = isset($_GET['subject']) ? ($_GET['subject']) : '';


// Only proceed if we actually have a valid ID
if ($content_id > 0) {
    
    // 2. Connect to the FICTP Database
    $conn = new mysqli("100.81.48.34", "bubustailo", "Student@123", "fictlp db", "3307");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // 3. Find the file path BEFORE we delete the row
    $stmt = $conn->prepare("SELECT file_path FROM content WHERE Content_ID = ?");
    $stmt->bind_param("i", $content_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $file_to_delete = $row['file_path'];

        // 4. Delete the physical PDF file from your server using unlink()
        if (file_exists($file_to_delete)) {
            unlink($file_to_delete);
        }

        // 5. Delete the actual row from phpMyAdmin
        $delete_stmt = $conn->prepare("DELETE FROM content WHERE Content_ID = ?");
        $delete_stmt->bind_param("i", $content_id);
        $delete_stmt->execute();
        $delete_stmt->close();
    }
    
    $stmt->close();
    $conn->close();
}

header("Location: editSubjectContent.php?chapter=" . $chapter_id . "&subject=" . $subject_code);
exit();
?>