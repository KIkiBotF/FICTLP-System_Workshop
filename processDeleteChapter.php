<?php
// Connect to the database
$conn = new mysqli("100.81.48.34", "bubustailo", "Student@123", "fictlp db", "3307");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the required parameters are in the URL
if (isset($_GET['chapter']) && isset($_GET['subject'])) {
    $chapter_id = $_GET['chapter'];
    $subject_code = $_GET['subject'];

    // Prepare and execute the delete query
    $stmt = $conn->prepare("DELETE FROM chapter WHERE Chapter_ID = ?");
    $stmt->bind_param("s", $chapter_id);
    
    if ($stmt->execute()) {
        // Optional: If you also want to delete all slides/videos inside this chapter automatically, 
        // you would add a second query here: DELETE FROM content WHERE Chapter_ID = ?
        
        // Success! Redirect back to the subject page
        header("Location: editSubject.php?subject=" . urlencode($subject_code));
        exit();
    } else {
        echo "Database error: " . $stmt->error;
    }
    
    $stmt->close();
} else {
    echo "Invalid request. Missing chapter ID or subject code.";
}

$conn->close();
?>