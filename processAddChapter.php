<?php
// Connect to the database
$conn = new mysqli("100.81.48.34", "bubustailo", "Student@123", "fictlp db", "3307");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form was submitted
// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_code = $_POST['subject_code'];
    $chapter_name = $_POST['chapter_name'];

    // --- NEW LOGIC START ---
    // Find the highest existing chapter_order for this specific subject
    $order_stmt = $conn->prepare("SELECT MAX(chapter_order) as max_order FROM chapter WHERE Subject_Code = ?");
    $order_stmt->bind_param("s", $subject_code);
    $order_stmt->execute();
    $order_result = $order_stmt->get_result();
    $order_row = $order_result->fetch_assoc();
    
    // Set the new chapter_order to the highest existing order + 1. 
    // If no chapters exist yet, default to 1.
    $chapter_order = ($order_row['max_order'] !== null) ? $order_row['max_order'] + 1 : 1;
    $order_stmt->close();
    // --- NEW LOGIC END ---

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