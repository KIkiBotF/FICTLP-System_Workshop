<?php
// Establish connection
$conn = new mysqli("100.81.48.34", "bubustailo", "Student@123", "fictlp db", "3307");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Grab the quiz_id that we passed through the hidden input
    $quiz_id = isset($_POST['quiz_id']) ? intval($_POST['quiz_id']) : 0;

    //  Update Existing Questions
    if (isset($_POST['question_ids']) && is_array($_POST['question_ids'])) {
        $updateStmt = $conn->prepare("UPDATE quiz_question SET Question_Text=?, OptA=?, OptB=?, OptC=?, Correct_Answer=? WHERE Question_ID=?");
        foreach ($_POST['question_ids'] as $qId) {
            $qText = $_POST["question_text_$qId"];
            $optA = $_POST["optA_$qId"];
            $optB = $_POST["optB_$qId"];
            $optC = $_POST["optC_$qId"];
            $correct = $_POST["correctAnswer_$qId"];

            $updateStmt->bind_param("sssssi", $qText, $optA, $optB, $optC, $correct, $qId);
            $updateStmt->execute();
        }
    }

    // ---  Insert New Questions ---
    if (isset($_POST['new_questions']) && is_array($_POST['new_questions']) && $quiz_id > 0) {
        $insertStmt = $conn->prepare("INSERT INTO quiz_question (Quiz_ID, Question_Text, OptA, OptB, OptC, Correct_Answer) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($_POST['new_questions'] as $newId) {
            $qText = $_POST["new_question_text_$newId"];
            $optA = $_POST["new_optA_$newId"];
            $optB = $_POST["new_optB_$newId"];
            $optC = $_POST["new_optC_$newId"];
            $correct = $_POST["new_correctAnswer_$newId"];
            
            // Only insert if the question text isn't empty
            if (!empty(trim($qText))) {
                $insertStmt->bind_param("isssss", $quiz_id, $qText, $optA, $optB, $optC, $correct);
                $insertStmt->execute();
            }
        }
    }

    // Redirect back to the edit page with a success message
    echo "<script>
            alert('Quiz saved successfully!');
            window.history.back();
          </script>";
}
?>