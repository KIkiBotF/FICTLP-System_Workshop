<?php
session_start();

// Security Check
if (!isset($_SESSION['user_id'])) {
    die("Error: Unauthorized access. Please log in.");
}

// Database Connection
$conn = new mysqli("100.81.48.34", "bubustailo", "Student@123", "fictlp db", "3307");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $quiz_id = isset($_POST['quiz_id']) ? intval($_POST['quiz_id']) : 0;
    
    // Get the IDs of the questions that are still on the screen
    $submitted_ids = isset($_POST['question_ids']) && is_array($_POST['question_ids']) ? $_POST['question_ids'] : [];

    // ---------------------------------------------------------
    // 1. Delete Removed Questions
    // ---------------------------------------------------------
    if ($quiz_id > 0) {
        // Fetch all question IDs currently attached to this quiz in the database
        $stmt_get = $conn->prepare("SELECT Question_ID FROM quiz_question WHERE Quiz_ID = ?");
        $stmt_get->bind_param("i", $quiz_id);
        $stmt_get->execute();
        $result = $stmt_get->get_result();

        $existing_ids = [];
        while ($row = $result->fetch_assoc()) {
            $existing_ids[] = $row['Question_ID'];
        }
        $stmt_get->close();

        // Find which IDs are in the database but are missing from the submitted form
        $ids_to_delete = array_diff($existing_ids, $submitted_ids);

        // If there are missing IDs, delete them from the database
        if (!empty($ids_to_delete)) {
            $deleteStmt = $conn->prepare("DELETE FROM quiz_question WHERE Question_ID = ?");
            foreach ($ids_to_delete as $del_id) {
                $deleteStmt->bind_param("i", $del_id);
                $deleteStmt->execute();
            }
            $deleteStmt->close();
        }
    }

    // ---------------------------------------------------------
    // 2. Update Existing Questions
    // ---------------------------------------------------------
    if (!empty($submitted_ids)) {
        
        $updateStmt = $conn->prepare("UPDATE quiz_question SET Question_Text=?, OptA=?, OptB=?, OptC=?, OptD=?, Correct_Answer=? WHERE Question_ID=?");
        
        foreach ($submitted_ids as $qId) {
            $qText   = $_POST["question_text_" . $qId] ?? '';
            $optA    = $_POST["optA_" . $qId] ?? '';
            $optB    = $_POST["optB_" . $qId] ?? '';
            $optC    = $_POST["optC_" . $qId] ?? '';
            $optD    = $_POST["optD_" . $qId] ?? ''; 
            $correct = $_POST["correctAnswer_" . $qId] ?? '';

            $updateStmt->bind_param("ssssssi", $qText, $optA, $optB, $optC, $optD, $correct, $qId);
            $updateStmt->execute();
        }
        $updateStmt->close();
    }

    // ---------------------------------------------------------
    // 3. Insert New Questions
    // ---------------------------------------------------------
    if (isset($_POST['new_questions']) && is_array($_POST['new_questions']) && $quiz_id > 0) {
        
        $insertStmt = $conn->prepare("INSERT INTO quiz_question (Quiz_ID, Question_Text, OptA, OptB, OptC, OptD, Correct_Answer) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($_POST['new_questions'] as $newId) {
            $qText   = $_POST["new_question_text_" . $newId] ?? '';
            $optA    = $_POST["new_optA_" . $newId] ?? ''; 
            $optB    = $_POST["new_optB_" . $newId] ?? '';
            $optC    = $_POST["new_optC_" . $newId] ?? '';
            $optD    = $_POST["new_optD_" . $newId] ?? ''; 
            $correct = $_POST["new_correctAnswer_" . $newId] ?? '';

            if (!empty(trim($qText))) {
                $insertStmt->bind_param("issssss", $quiz_id, $qText, $optA, $optB, $optC, $optD, $correct);
                $insertStmt->execute();
            }
        }
        $insertStmt->close();
    }
}

$conn->close();

echo "<script>
    alert('Quiz saved successfully!');
    window.history.back();
</script>";
?>