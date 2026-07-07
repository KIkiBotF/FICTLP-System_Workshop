<?php
session_start();

$host = "100.81.48.34";
$port = "3307";
$dbname = "fictlp db";
$username = "bubustailo";
$password = "Student@123";

$conn = new mysqli($host, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$subjectMap = [
    'cpp' => 'DITP1113',
    'db'  => 'DITP2913',
    'coa' => 'DITS1133',
];

$input = json_decode(file_get_contents('php://input'), true);
$answers = $input['answers'] ?? [];
$chapterNum = isset($input['quiz_id']) ? intval($input['quiz_id']) : 1; // nombor CHAPTER (1-3), bukan Quiz_ID sebenar
$subjectShort = isset($input['subject']) ? strtolower(trim($input['subject'])) : 'cpp';

$dbSubjectCode = $subjectMap[$subjectShort] ?? null;
if (!$dbSubjectCode) {
    die("Ralat: subjek tidak sah.");
}

$userID = isset($_SESSION['userID']) ? $_SESSION['userID'] : null;
if (!$userID) {
    die("Ralat: sila log masuk semula.");
}

// 1. Cari Chapter_ID sebenar berdasarkan Subject_Code + chapter_order
$resolvedChapterID = null;
$chapterStmt = $conn->prepare("SELECT Chapter_ID FROM chapter WHERE Subject_Code = ? AND chapter_order = ?");
$chapterStmt->bind_param("si", $dbSubjectCode, $chapterNum);
$chapterStmt->execute();
$chapterRow = $chapterStmt->get_result()->fetch_assoc();
$chapterStmt->close();
if ($chapterRow) {
    $resolvedChapterID = intval($chapterRow['Chapter_ID']);
}


$resolvedQuizID = null;
if ($resolvedChapterID) {
    $quizStmt = $conn->prepare("SELECT Quiz_ID FROM quiz WHERE Chapter_ID = ? LIMIT 1");
    $quizStmt->bind_param("i", $resolvedChapterID);
    $quizStmt->execute();
    $quizRow = $quizStmt->get_result()->fetch_assoc();
    $quizStmt->close();
    if ($quizRow) {
        $resolvedQuizID = intval($quizRow['Quiz_ID']);
    }
}

// 3. Kira markah - bandingkan jawapan pelajar dengan Correct_Answer sebenar
//    (guna prepared statement, bukan query string terus)
$score = 0;
if (!empty($answers)) {
    $ansStmt = $conn->prepare("SELECT Correct_Answer FROM quiz_question WHERE Question_ID = ?");
    foreach ($answers as $q_id => $choice) {
        $q_id = intval($q_id);
        $choice = trim($choice);
        $ansStmt->bind_param("i", $q_id);
        $ansStmt->execute();
        $ansRow = $ansStmt->get_result()->fetch_assoc();
        if ($ansRow && trim($ansRow['Correct_Answer']) === $choice) {
            $score++;
        }
    }
    $ansStmt->close();
}

$totalQuestions = count($answers);
$percentageScore = $totalQuestions > 0 ? round(($score / $totalQuestions) * 100) : 0;


if ($resolvedChapterID) {
    $progressStmt = $conn->prepare(
        "INSERT INTO user_progress (userID, Subject_Code, chapterIndex, completedAt)
         VALUES (?, ?, ?, NOW())
         ON DUPLICATE KEY UPDATE completedAt = NOW()"
    );
    $progressStmt->bind_param("ssi", $userID, $dbSubjectCode, $chapterNum);
    $progressStmt->execute();
    $progressStmt->close();
}

if ($resolvedQuizID) {
    $checkStmt = $conn->prepare("SELECT Grade, attempt_no FROM score WHERE userID = ? AND Quiz_ID = ?");
    $checkStmt->bind_param("si", $userID, $resolvedQuizID);
    $checkStmt->execute();
    $existing = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();

    if ($existing) {
        $newAttemptNo = intval($existing['attempt_no']) + 1;
        $bestGrade = max($percentageScore, intval($existing['Grade']));
        $updateStmt = $conn->prepare(
            "UPDATE score SET Grade = ?, attempt_no = ?, date_taken = NOW() WHERE userID = ? AND Quiz_ID = ?"
        );
        $updateStmt->bind_param("iisi", $bestGrade, $newAttemptNo, $userID, $resolvedQuizID);
        $updateStmt->execute();
        $updateStmt->close();
    } else {
        $insertStmt = $conn->prepare(
            "INSERT INTO score (userID, Quiz_ID, attempt_no, Grade, date_taken) VALUES (?, ?, 1, ?, NOW())"
        );
        $insertStmt->bind_param("sii", $userID, $resolvedQuizID, $percentageScore);
        $insertStmt->execute();
        $insertStmt->close();
    }
} else {
    echo "Warning: Could not find Quiz_ID for subject '$subjectShort' chapter '$chapterNum'; score not saved.";
}

$achievementUnlocked = false;

$totalStmt = $conn->prepare(
    "SELECT COUNT(*) AS total FROM quiz q INNER JOIN chapter c ON q.Chapter_ID = c.Chapter_ID WHERE c.Subject_Code = ?"
);
$totalStmt->bind_param("s", $dbSubjectCode);
$totalStmt->execute();
$totalChapters = intval($totalStmt->get_result()->fetch_assoc()['total']);
$totalStmt->close();

if ($totalChapters > 0) {
    $passStmt = $conn->prepare(
        "SELECT COUNT(*) AS passed
         FROM score s
         INNER JOIN quiz q ON s.Quiz_ID = q.Quiz_ID
         INNER JOIN chapter c ON q.Chapter_ID = c.Chapter_ID
         WHERE s.userID = ? AND c.Subject_Code = ? AND s.Grade >= q.Passing_Mark"
    );
    $passStmt->bind_param("ss", $userID, $dbSubjectCode);
    $passStmt->execute();
    $passedChapters = intval($passStmt->get_result()->fetch_assoc()['passed']);
    $passStmt->close();

    if ($passedChapters >= $totalChapters) {
        $checkAchStmt = $conn->prepare("SELECT Achievement_ID FROM achievement WHERE userID = ? AND Subject_Code = ?");
        $checkAchStmt->bind_param("ss", $userID, $dbSubjectCode);
        $checkAchStmt->execute();
        $alreadyUnlocked = $checkAchStmt->get_result()->num_rows > 0;
        $checkAchStmt->close();

        if (!$alreadyUnlocked) {
            $insertAchStmt = $conn->prepare("INSERT INTO achievement (userID, Subject_Code, Date_Issued) VALUES (?, ?, CURDATE())");
            $insertAchStmt->bind_param("ss", $userID, $dbSubjectCode);
            $insertAchStmt->execute();
            $insertAchStmt->close();
            $achievementUnlocked = true;
        }
    }
}

$resultMessage = "Congratulations! Quiz completed. Your score: $score / $totalQuestions ($percentageScore%)";
if ($achievementUnlocked) {
    $resultMessage .= "\n🎉 Congrats! You have unlocked the Achievement Certificate for this subject!";
}

echo $resultMessage;
$conn->close();
?>