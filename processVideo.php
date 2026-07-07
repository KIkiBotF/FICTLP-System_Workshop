<?php
session_start();

$conn = new mysqli("100.81.48.34", "bubustailo", "Student@123", "fictlp db", "3307");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

if (isset($_POST['uploadBtn'])) {
    $content_name = $_POST['content_name'];
    $chapter_name = $_POST['chapter_name'];
    $subject_code = $_POST['subject_code'];
    $file_type = $_POST['file_type'];
    
    // Grab the YouTube URL from the form
    $video_url = $_POST['video_url'];

    // Insert directly into the content table
   // Insert directly into the content table using the correct column names
$stmt = $conn->prepare("INSERT INTO content (Name, Chapter_ID, File_Type, file_path) VALUES (?, ?, ?, ?)");

// Bind only the 4 variables that match the columns above
// (Note: $chapter_name here actually holds your chapter_id number from the form)
$stmt->bind_param("ssss", $content_name, $chapter_name, $file_type, $video_url);

    if ($stmt->execute()) {
        // Success! Send them back to the chapter page.
        header("Location: editSubjectContent.php?chapter=" . $chapter_name . "&subject=" . $subject_code);
        exit();
    } else {
        echo "Database error: " . $stmt->error;
    }
    $stmt->close();
}
$conn->close();
?>