<?php
// Connect to the database
$conn = new mysqli("100.81.48.34", "bubustailo", "Student@123", "fictlp db", "3307");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_code = $_POST['subject_code'];
    $chapter_name = $_POST['chapter_name'];
    
    // Default chapter_order to 1 (you can build a more complex sorting system later if needed)
    $chapter_order = 1;

    // Prepare and execute the insert query
    $stmt = $conn->prepare("INSERT INTO chapter (Subject_Code, Chapter_Name, chapter_order) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $subject_code, $chapter_name, $chapter_order);
    
    if ($stmt->execute()) {
        // Success! Send them back to the subject page
        header("Location: editSubject.php?subject=" . urlencode($subject_code));
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    
    $stmt->close();
}

$conn->close();
?>