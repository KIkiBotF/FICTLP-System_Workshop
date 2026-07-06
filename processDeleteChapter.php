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

    // 1. Find all Quizzes associated with this Chapter
    $getQuizStmt = $conn->prepare("SELECT Quiz_ID FROM quiz WHERE Chapter_ID = ?");
    $getQuizStmt->bind_param("s", $chapter_id);
    $getQuizStmt->execute();
    $quizResult = $getQuizStmt->get_result();
    
    // Loop through any found quizzes and delete their child records first
    while ($quizRow = $quizResult->fetch_assoc()) {
        $quiz_id = $quizRow['Quiz_ID'];
        
        // Delete student scores for this quiz
        $delScoreStmt = $conn->prepare("DELETE FROM score WHERE Quiz_ID = ?");
        $delScoreStmt->bind_param("i", $quiz_id);
        $delScoreStmt->execute();
        $delScoreStmt->close();
        
        // Delete quiz questions for this quiz
        $delQQStmt = $conn->prepare("DELETE FROM quiz_question WHERE Quiz_ID = ?");
        $delQQStmt->bind_param("i", $quiz_id);
        $delQQStmt->execute();
        $delQQStmt->close();
    }
    $getQuizStmt->close();

    // 2. Delete the Quiz itself
    $delQuizStmt = $conn->prepare("DELETE FROM quiz WHERE Chapter_ID = ?");
    $delQuizStmt->bind_param("s", $chapter_id);
    $delQuizStmt->execute();
    $delQuizStmt->close();

    // 3. Delete any Slides or Videos (Content) tied to the chapter
    $delContentStmt = $conn->prepare("DELETE FROM content WHERE Chapter_ID = ?");
    $delContentStmt->bind_param("s", $chapter_id);
    $delContentStmt->execute();
    $delContentStmt->close();

    // 4. Finally, safely delete the Chapter
    $stmt = $conn->prepare("DELETE FROM chapter WHERE Chapter_ID = ?");
    $stmt->bind_param("s", $chapter_id);
    
    if ($stmt->execute()) {
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