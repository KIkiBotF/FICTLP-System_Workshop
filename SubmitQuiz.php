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

/**
 * Mapping kod subjek pendek (cpp/db/coa) ke Subject_Code PANJANG dalam table `quiz`.
 */
function getLongSubjectCode($subjectShort) {
    $map = [
        'db'  => 'DITP2913',
        'cpp' => 'DITP1113',
        'coa' => 'DITS1133',
    ];
    return $map[strtolower(trim($subjectShort))] ?? null;
}

/**
 * Kod Subject_Code yang digunakan dalam table `achievement`.
 */
function getAchievementCode($subjectShort) {
    $map = [
       'db'  => 'DITP2913',
        'cpp' => 'DITP1113',
        'coa' => 'DITS1133',
    ];
    return $map[strtolower(trim($subjectShort))] ?? null;
}

/**
 * Cari Quiz_ID SEBENAR dalam table `quiz` ikut kedudukan (position) menaik,
 * berdasarkan kod subjek pendek + nombor chapter. Ini perlu sebab Quiz_ID
 * CPP mula dari 101 dan COA dari 201 (bukan mula dari 1 macam DB).
 */
function resolveQuizID($conn, $subjectShort, $chapterNum) {
    $subjectCode = getLongSubjectCode($subjectShort);
    if (!$subjectCode) return null;

    $offset = intval($chapterNum) - 1;
    if ($offset < 0) $offset = 0;

    $sql = "SELECT Quiz_ID FROM quiz WHERE Subject_Code = ? ORDER BY Quiz_ID ASC LIMIT 1 OFFSET ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) return null;

    $stmt->bind_param("si", $subjectCode, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $quizID = null;
    if ($row = $result->fetch_assoc()) {
        $quizID = intval($row['Quiz_ID']);
    }
    $stmt->close();
    return $quizID;
}

/**
 * Semak jika student dah LULUS SEMUA chapter untuk satu subject.
 * Jika ya dan achievement belum wujud, unlock (insert ke table `achievement`).
 * Pulangkan true jika achievement baru sahaja diunlock kali ini.
 */


function checkAndUnlockAchievement($conn, $userID, $subjectShort) {
    
    $subjectCode = getLongSubjectCode($subjectShort);
    $achievementCode = getAchievementCode($subjectShort);
    
    // Debug: Semak jumlah chapter yang patut diluluskan
    $totalStmt = $conn->prepare("SELECT COUNT(*) AS total FROM quiz WHERE Subject_Code = ?");
    $totalStmt->bind_param("s", $subjectCode);
    $totalStmt->execute();
    $totalChapters = intval($totalStmt->get_result()->fetch_assoc()['total']);
    $totalStmt->close();

    // Debug: Semak jumlah chapter yang TELAH lulus
    $passStmt = $conn->prepare(
        "SELECT COUNT(*) AS passed
         FROM score s
         INNER JOIN quiz q ON s.Quiz_ID = q.Quiz_ID
         WHERE s.userID = ? AND q.Subject_Code = ? AND s.Grade >= q.Passing_Mark"
    );
    $passStmt->bind_param("ss", $userID, $subjectCode);
    $passStmt->execute();
    $passedChapters = intval($passStmt->get_result()->fetch_assoc()['passed']);
    $passStmt->close();

    // Debug: Paparkan status sebenar (Anda akan nampak mesej ini di skrin selepas tekan Submit)
    echo " Debug Info: Perlu lulus $totalChapters chapter. Anda telah lulus $passedChapters chapter. ";

    if ($passedChapters < $totalChapters) return false;

    $checkStmt = $conn->prepare("SELECT Achievement_ID FROM achievement WHERE userID = ? AND Subject_Code = ?");
    $checkStmt->bind_param("ss", $userID, $achievementCode);
    $checkStmt->execute();
    $alreadyUnlocked = $checkStmt->get_result()->num_rows > 0;
    $checkStmt->close();

    if ($alreadyUnlocked) return false;

    // Tambah 'Date_Issued' dan fungsi NOW() untuk mengisi tarikh semasa
  $insertStmt = $conn->prepare("INSERT INTO achievement (userID, Subject_Code, Date_Issued) VALUES (?, ?, NOW())");
    $insertStmt->bind_param("ss", $userID, $achievementCode);
    
    if (!$insertStmt->execute()) {
        echo " Ralat SQL (Achievement): " . $insertStmt->error;
    } else {
        echo " Success: Achievement telah dimasukkan! ";
    }
    $insertStmt->close();

    return true;
}

$input = json_decode(file_get_contents('php://input'), true);
$answers = $input['answers'] ?? []; 
$chapterNum = $input['quiz_id'] ?? 1; // ini nombor CHAPTER (1-3), BUKAN Quiz_ID sebenar
// Menerima nilai subject secara dinamik dari front-end (kod pendek: cpp/db/coa)
$subjectShort = isset($input['subject']) ? trim($input['subject']) : 'cpp';
$subject = mysqli_real_escape_string($conn, $subjectShort);
$score = 0; // markah mentah (jumlah jawapan betul, contoh 8 daripada 10)

if (!empty($answers)) {
    foreach ($answers as $q_id => $choice) {
        $q_id = intval($q_id);
        $choice = mysqli_real_escape_string($conn, $choice);
        $res = mysqli_query($conn, "SELECT Correct_Answer FROM `quiz_question` WHERE Question_ID = $q_id");
        if ($res && $row = mysqli_fetch_assoc($res)) {
            if (trim($row['Correct_Answer']) == trim($choice)) {
                $score++;
            }
        }
    }
}

// FIX PENGIRAAN: Passing_Mark dalam table `quiz` ialah AMBANG PERATUSAN
// (contoh 70 = perlu 70% untuk lulus), BUKAN jumlah markah mentah.
// Jadi Grade yang disimpan ke table `score` MESTI dalam bentuk peratusan,
// bukan kiraan mentah jawapan betul (0-10), supaya boleh dibandingkan
// terus dengan Passing_Mark.
$totalQuestions = count($answers);
$percentageScore = $totalQuestions > 0 ? round(($score / $totalQuestions) * 100) : 0;

// Ambil email dari session, letak email dummy jika tiada session aktif
$email = isset($_SESSION['email']) ? $_SESSION['email'] : 'student@utem.edu.my'; 

// Ambil userID dari session (diperlukan untuk table `score`)
$userID = isset($_SESSION['userID']) ? mysqli_real_escape_string($conn, $_SESSION['userID']) : null;

// Simpan rekod kemajuan (tracking chapter, bukan berkaitan Quiz_ID sebenar)
$sql = "INSERT INTO `user_progress` (Email, Subject_Code, chapterIndex, completedAt) 
        VALUES ('$email', '$subject', $chapterNum, NOW())
        ON DUPLICATE KEY UPDATE 
        chapterIndex = VALUES(chapterIndex), 
        completedAt = NOW()";

if (!mysqli_query($conn, $sql)) {
    echo "Failed to update user progress: " . mysqli_error($conn);
}

// ---> FIX UTAMA: Selesaikan Quiz_ID SEBENAR dari table `quiz` (bukan guna
// nombor chapter terus), sebab Quiz_ID untuk CPP mula dari 101 dan COA dari 201.
$resolvedQuizID = resolveQuizID($conn, $subjectShort, $chapterNum);

if ($userID && $resolvedQuizID) {
    // Semak dulu jika rekod sedia ada (elak bergantung pada UNIQUE KEY di DB)
    $checkSql = "SELECT Grade FROM `score` WHERE userID = ? AND Quiz_ID = ?";
    $stmtCheck = $conn->prepare($checkSql);
    $stmtCheck->bind_param("si", $userID, $resolvedQuizID);
    $stmtCheck->execute();
    $checkResult = $stmtCheck->get_result();

    if ($existing = $checkResult->fetch_assoc()) {
        // Sudah ada rekod - update HANYA jika peratusan baru lebih tinggi
        if ($percentageScore > intval($existing['Grade'])) {
            $updateSql = "UPDATE `score` SET Grade = ? WHERE userID = ? AND Quiz_ID = ?";
            $stmtUpdate = $conn->prepare($updateSql);
            $stmtUpdate->bind_param("isi", $percentageScore, $userID, $resolvedQuizID);
            if (!$stmtUpdate->execute()) {
                echo "Failed to update score: " . $conn->error;
            }
            $stmtUpdate->close();
        }
    } else {
        // Belum ada rekod - insert baru
        $insertSql = "INSERT INTO `score` (userID, Quiz_ID, Grade) VALUES (?, ?, ?)";
        $stmtInsert = $conn->prepare($insertSql);
        $stmtInsert->bind_param("sii", $userID, $resolvedQuizID, $percentageScore);
        if (!$stmtInsert->execute()) {
            echo "Failed to save score: " . $conn->error;
        }
        $stmtInsert->close();
    }
    $stmtCheck->close();
} elseif (!$userID) {
    echo "Warning: Session userID tidak dijumpai, markah tidak disimpan ke table score.";
} elseif (!$resolvedQuizID) {
    echo "Warning: Tidak dapat cari Quiz_ID untuk subject '$subjectShort' chapter '$chapterNum', markah tidak disimpan.";
}

// ---> Semak & unlock Achievement jika student baru sahaja lulus SEMUA chapter subject ini
$achievementUnlocked = false;
if ($userID) {
    $achievementUnlocked = checkAndUnlockAchievement($conn, $userID, $subjectShort);
}

$resultMessage = "Congratulations! Quiz completed. Your score: $score / $totalQuestions ($percentageScore%)";
if ($achievementUnlocked) {
    $resultMessage .= "\n🎉 Tahniah! Anda telah unlock Achievement Certificate untuk subject ini!";
}

echo $resultMessage;
?>