<?php
session_start();
$conn = new mysqli("100.81.48.34", "bubustailo", "Student@123", "fictlp db", "3307");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$lecturer_id = isset($_SESSION['userID']) ? $_SESSION['userID'] : 'L1006001';

// 2. Corrected the table aliases to consistently use 'ls'
$subject_stmt = $conn->prepare("SELECT s.Subject_Code, s.Title FROM subject s JOIN lecture_subject ls ON s.Subject_Code = ls.Subject_Code WHERE ls.userID = ?");

if (!$subject_stmt) {
    die("Database Error: " . $conn->error);
}

$subject_stmt->bind_param("s", $lecturer_id);
$subject_stmt->execute();
$lecturer_subjects = $subject_stmt->get_result();
$subject_stmt->close();

$quiz_id = '';
$questions = null;

$selected_chapter = isset($_GET['chapter']) ? $_GET['chapter'] : '1';
$selected_subject = isset($_GET['subject']) ? strtoupper($_GET['subject']) : '';

if ($selected_chapter != '' && $selected_subject != '') {
    $chapNum = intval($selected_chapter);

    // 1. Find the exact Chapter_ID for this subject and chapter number
    // We assume the chapter order matches the selected chapter number
    $chapStmt = $conn->prepare("SELECT Chapter_ID FROM chapter WHERE Subject_Code = ? AND chapter_order = ? LIMIT 1");
    $chapStmt->bind_param("si", $selected_subject, $chapNum);
    $chapStmt->execute();
    $chapResult = $chapStmt->get_result();

    if ($chapRow = $chapResult->fetch_assoc()) {
        $actual_chapter_id = $chapRow['Chapter_ID'];

        // 2. Check if a quiz already exists for this Chapter_ID
        $quizStmt = $conn->prepare("SELECT Quiz_ID FROM quiz WHERE Chapter_ID = ? LIMIT 1");
        $quizStmt->bind_param("i", $actual_chapter_id);
        $quizStmt->execute();
        $quizResult = $quizStmt->get_result();

        if ($quizRow = $quizResult->fetch_assoc()) {
            // Quiz exists! Grab the ID
            $quiz_id = $quizRow['Quiz_ID'];
        } else {
            // Quiz DOES NOT exist! Auto-create it so the lecturer can add questions.
            $quizTitle = "Chapter " . $chapNum . " Quiz";
            $passingMark = 70; // Default passing mark for new quizzes

            $createQuizStmt = $conn->prepare("INSERT INTO quiz (Chapter_ID, Quiz_title, Passing_Mark) VALUES (?, ?, ?)");
            $createQuizStmt->bind_param("isi", $actual_chapter_id, $quizTitle, $passingMark);
            
            if ($createQuizStmt->execute()) {
                // Get the ID of the newly created quiz
                $quiz_id = $conn->insert_id; 
            }
            $createQuizStmt->close();
        }
        $quizStmt->close();

        // 3. If we successfully found or created a Quiz_ID, fetch its questions
        if (!empty($quiz_id)) {
            $stmt_qs = $conn->prepare("SELECT * FROM quiz_question WHERE Quiz_ID = ?");
            $stmt_qs->bind_param("i", $quiz_id);
            $stmt_qs->execute();
            $questions = $stmt_qs->get_result();
            $stmt_qs->close();
        }
    }
    $chapStmt->close();
}
?>
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Quiz CoreKnowledge</title>
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="editQuizLecturerStyle.css">
</head>
<body>

<?php include("sidebar.php"); ?>

<main class="main-content">
    <h1>Edit Quiz</h1>
    
    <form action="saveQuizEdit.php" method="POST" class="quiz-wrapper">
        
        <div class="settings-container">
            <div class="input-group">
                <label for="quizChapter">Quiz Chapter: </label>
                <input type="number" id="quizChapter" name="quizChapter" value="<?php echo htmlspecialchars($selected_chapter); ?>" min="1">
            </div>
            <div class="input-group subject-group">
                <label for="subjectSelect">Subject:</label>
                <select id="subjectSelect" name="subjectSelect">
                    <option value="" disabled <?php echo ($selected_subject == '') ? 'selected' : ''; ?>>Select a subject...</option>
                    <?php
                    if ($lecturer_subjects->num_rows > 0) {
                        while ($sub = $lecturer_subjects->fetch_assoc()) {
                            $code = htmlspecialchars($sub['Subject_Code']);
                            $title = htmlspecialchars($sub['Title']);
                            $is_selected = ($selected_subject == $code) ? 'selected' : '';
                            echo "<option value=\"$code\" $is_selected>$title ($code)</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <button type="button" class="load-btn">Load Quiz</button>
        </div>

        <?php
        echo "<h3>DEBUG QUIZ ID: " . $quiz_id . "</h3>";
        echo '<input type="hidden" name="quiz_id" value="' . htmlspecialchars($quiz_id) . '">';

        if ($questions !== null && $questions->num_rows > 0) {
            $questionCount = 1;
            while ($row = $questions->fetch_assoc()) {
                $qId = $row['Question_ID'];
                $qText = htmlspecialchars($row['Question_Text']);
                $optA = htmlspecialchars($row['OptA']);
                $optB = htmlspecialchars($row['OptB']);
                $optC = htmlspecialchars($row['OptC'] ?? '');
                $optD = htmlspecialchars($row['OptD'] ?? '');
                $correctAnswer = $row['Correct_Answer'];
        ?>
                <div class="question-container">
                    <div class="question-header">
                        <h2 class="question-label">Question <?php echo str_pad($questionCount, 2, '0', STR_PAD_LEFT); ?></h2>
                        <button type="button" class="delete-question-btn" title="Remove Question" onclick="this.closest('.question-container').remove()">✖</button>
                    </div>
                    
                    <input type="hidden" name="question_ids[]" value="<?php echo $qId; ?>">
                    
                    <textarea class="question-input" name="question_text_<?php echo $qId; ?>" placeholder="Type your quiz question here..."><?php echo $qText; ?></textarea>
                    
                    <div class="options-group">
                        <div class="option-row">
                            <input type="radio" name="correctAnswer_<?php echo $qId; ?>" id="q<?php echo $qId; ?>optA" value="A" <?php echo ($correctAnswer === 'A') ? 'checked' : ''; ?>>
                            <input type="text" class="option-text" name="optA_<?php echo $qId; ?>" value="<?php echo $optA; ?>">
                        </div>
                        <div class="option-row">
                            <input type="radio" name="correctAnswer_<?php echo $qId; ?>" id="q<?php echo $qId; ?>optB" value="B" <?php echo ($correctAnswer === 'B') ? 'checked' : ''; ?>>
                            <input type="text" class="option-text" name="optB_<?php echo $qId; ?>" value="<?php echo $optB; ?>">
                        </div>
                        <div class="option-row">
                            <input type="radio" name="correctAnswer_<?php echo $qId; ?>" id="q<?php echo $qId; ?>optC" value="C" <?php echo ($correctAnswer === 'C') ? 'checked' : ''; ?>>
                            <input type="text" class="option-text" name="optC_<?php echo $qId; ?>" value="<?php echo $optC; ?>">
                        </div>
                        <div class="option-row">
                            <input type="radio" name="correctAnswer_<?php echo $qId; ?>" id="q<?php echo $qId; ?>optD" value="D" <?php echo ($correctAnswer === 'D') ? 'checked' : ''; ?>>
                            <input type="text" class="option-text" name="optD_<?php echo $qId; ?>" value="<?php echo $optD; ?>" placeholder="Option D">
                        </div>
                    </div>
                </div>
        <?php
                $questionCount++;
            }
        } else {
            echo "<p style='padding: 20px; text-align: center; color: #666;'>No questions found for this Chapter and Subject.</p>";
        }
        ?>

        <div class="form-actions">
            <button type="button" class="add-question-btn">+ Add Another Question</button>
            <button type="submit" class="save-btn">Save Entire Quiz</button>
        </div>
    </form>
</main>

<script>
    document.querySelector('.load-btn').addEventListener('click', function () {
        let chapter = document.getElementById('quizChapter').value;
        let subject = document.getElementById('subjectSelect').value;
        if (chapter && subject) {
            window.location.href = `editQuizLecturer.php?chapter=${chapter}&subject=${subject}`;
        } else {
            alert("Please select both a chapter and a subject.");
        }
    });

    let newQuestionCounter = 1; // Declared missing variable

    document.querySelector('.add-question-btn').addEventListener('click', function() {
        const questionHTML = `
        <div class="question-container">
            <div class="question-header">
                <h2 class="question-label">New Question</h2>
                <button type="button" class="delete-question-btn" onclick="this.closest('.question-container').remove()">✖</button>
            </div>
            <input type="hidden" name="new_questions[]" value="${newQuestionCounter}">
            <textarea class="question-input" name="new_question_text_${newQuestionCounter}" placeholder="Type your quiz question here..."></textarea>
            
            <div class="options-group">
                <div class="option-row">
                    <input type="radio" name="new_correctAnswer_${newQuestionCounter}" value="A" checked>
                    <input type="text" class="option-text" name="new_optA_${newQuestionCounter}" placeholder="Option A">
                </div>
                <div class="option-row">
                    <input type="radio" name="new_correctAnswer_${newQuestionCounter}" value="B">
                    <input type="text" class="option-text" name="new_optB_${newQuestionCounter}" placeholder="Option B">
                </div>
                <div class="option-row">
                    <input type="radio" name="new_correctAnswer_${newQuestionCounter}" value="C">
                    <input type="text" class="option-text" name="new_optC_${newQuestionCounter}" placeholder="Option C">
                </div>
                <div class="option-row">
                    <input type="radio" name="new_correctAnswer_${newQuestionCounter}" value="D">
                    <input type="text" class="option-text" name="new_optD_${newQuestionCounter}" placeholder="Option D">
                </div>
            </div>
        </div>`;
        
        document.querySelector('.form-actions').insertAdjacentHTML('beforebegin', questionHTML);
        newQuestionCounter++;
    });
</script>
</body>
</html>