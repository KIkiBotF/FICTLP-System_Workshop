<?php
session_start();

$conn = new mysqli("100.81.48.34", "bubustailo", "Student@123", "fictlp db", "3307");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

if (isset($_POST['uploadBtn'])) {
    $content_name = $_POST['content_name'];
    $chapter_name = $_POST['chapter_name'];
    $subject_code = $_POST['subject_code'];
    $file_type = $_POST['file_type'];

    // 1. Where to save the file
    $target_dir = "uploads/";
    
    // Create a safe, unique filename
    $original_filename = basename($_FILES["slide_file"]["name"]);
    $safe_filename = str_replace(' ', '_', $original_filename);
    $target_file = $target_dir . time() . "_" . $safe_filename; 

    // 2. Security Check: Ensure it is a PDF
    $file_extension = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    if ($file_extension != "pdf") {
        die("Error: Only PDF files are allowed.");
    }

    // 3. Move the file and Insert into Database
    if (move_uploaded_file($_FILES["slide_file"]["tmp_name"], $target_file)) {
        
        // Insert into the content table!
        $stmt = $conn->prepare("INSERT INTO content (Name, Chapter_Name, File_Type, Subject_Code, File_Path) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $content_name, $chapter_name, $file_type, $subject_code, $target_file);

        if ($stmt->execute()) {
            // Success! Send them back to the chapter page to see their new slide.
           // Success! Send them back to the chapter page with the subject code included.
        header("Location: editSubjectContent.php?chapter=" . $chapter_name . "&subject=" . $subject_code);
exit();
            exit();
        } else {
            echo "Database error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error moving uploaded file. Does the 'uploads' folder exist?";
    }
}
$conn->close();
?>