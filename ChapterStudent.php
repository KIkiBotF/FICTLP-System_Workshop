<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Sambungan database (contoh)
$host = "100.81.48.34";
$port = "3307";          
$dbname = "fictlp db";  
$username = "bubustailo"; 
$password = "Student@123";

$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Sambungan gagal: " . $conn->connect_error);
}

/**
 * Cari Quiz_ID SEBENAR dalam table `quiz` ikut kedudukan (position) menaik,
 * berdasarkan kod subjek pendek + nombor chapter. Ini perlu sebab Quiz_ID
 * CPP mula dari 101 dan COA dari 201 (bukan mula dari 1 macam DB).
 */
function resolveQuizID($conn, $subjectShort, $chapterNum) {
    $map = [
        'db'  => 'DITP2913',
        'cpp' => 'DITP1113',
        'coa' => 'DITS1133',
    ];
    $subjectCode = $map[strtolower(trim($subjectShort))] ?? null;
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

// 1. Tangkap parameter subject dan chapter dari URL
$subjectParam = isset($_GET['subject']) ? $_GET['subject'] : 'db';
$chapterNum = isset($_GET['chapter']) ? mysqli_real_escape_string($conn, $_GET['chapter']) : '1';

// 1b. Semak status quiz pelajar untuk chapter ini (skor + lulus/gagal)
$quizStatus = [
    'attempted'   => false,
    'passed'      => false,
    'grade'       => 0,
    'passing'     => 0,
    'percentage'  => 0
];

$resolvedQuizID = resolveQuizID($conn, $subjectParam, $chapterNum);

if (isset($_SESSION['userID']) && $resolvedQuizID) {
    $currentUserID = $_SESSION['userID'];

    $statusSql = "SELECT s.Grade, q.Passing_Mark
                  FROM score s
                  INNER JOIN quiz q ON s.Quiz_ID = q.Quiz_ID
                  WHERE s.userID = ? AND s.Quiz_ID = ?";
    $stmtStatus = $conn->prepare($statusSql);
    if ($stmtStatus) {
        $stmtStatus->bind_param("si", $currentUserID, $resolvedQuizID);
        $stmtStatus->execute();
        $statusResult = $stmtStatus->get_result();
        if ($statusRow = $statusResult->fetch_assoc()) {
            $grade = intval($statusRow['Grade']); // Grade SUDAH dalam bentuk peratusan (0-100)
            $passingMark = intval($statusRow['Passing_Mark']) > 0 ? intval($statusRow['Passing_Mark']) : 70;

            // FIX: Grade ialah peratusan terus, TIDAK perlu dibahagi dengan
            // Passing_Mark lagi (Passing_Mark cuma ambang lulus, bukan markah penuh)
            $percentage = $grade > 100 ? 100 : $grade;

            $quizStatus['attempted']  = true;
            $quizStatus['passed']     = $grade >= $passingMark;
            $quizStatus['grade']      = $grade;
            $quizStatus['passing']    = $passingMark;
            $quizStatus['percentage'] = $percentage;
        }
        $stmtStatus->close();
    }
}

// 2. MAPPING: Tukar kod pendek dari URL kepada Kod Subjek sebenar dalam Database
$dbSubjectCode = '';
if (strtolower($subjectParam) == 'db') {
    $dbSubjectCode = 'DITP2913';
} elseif (strtolower($subjectParam) == 'cpp') {
    $dbSubjectCode = 'DITP1113';
} elseif (strtolower($subjectParam) == 'coa') {
    $dbSubjectCode = 'DITS1133';
} else {
    // Jika tiada padanan, gunakan parameter asal sebagai langkah berjaga-jaga
    $dbSubjectCode = mysqli_real_escape_string($conn, $subjectParam);
}

// 3. Gunakan pembolehubah $dbSubjectCode dan $chapterNum di dalam arahan SQL
$sql = "SELECT * FROM `content` WHERE `Subject_Code` = '$dbSubjectCode' AND `Chapter_Name` = '$chapterNum'";
$result = $conn->query($sql);

$contents = [];
if ($result) {
    while($row = $result->fetch_assoc()) {
        $contents[] = $row;
    }
}
?>

<?php include 'sidebarStudent.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Chapter Module</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght=400;600;700;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="ChapterStudent.css" />
</head>
<body>


  <main class="main">

    <div class="chapter-header">
      <h1 id="chapterTitleDisplay">Loading Chapter...</h1>
    </div>

    <div class="content-grid">
    <div class="section-col">
        <div class="section-label">Slide</div>
        <?php foreach($contents as $item): ?>
            <?php if(strtolower($item['File_Type']) == 'slide'): ?>
                <div class="media-card" onclick="window.open('<?php echo $item['file_path']; ?>', '_blank')">
                    <p><?php echo htmlspecialchars($item['Name']); ?></p>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <div class="section-col video-col">
        <div class="section-label">Video</div>
        <?php foreach($contents as $item): ?>
            <?php if(strtolower($item['File_Type']) == 'video'): ?>
                <?php 
                    $ytUrl = $item['file_path'];
                    $videoId = '';
                    
                    // Logic to extract the exact YouTube Video ID from the URL
                    if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $ytUrl, $match)) {
                        $videoId = $match[1];
                    }
                    
                    // Ensure it is treated as a valid web URL
                    if (strpos($ytUrl, 'http') !== 0 && $ytUrl != '') {
                        $ytUrl = 'https://' . $ytUrl;
                    }
                ?>
                
                <div class="media-card" style="padding: 10px; cursor: pointer; text-align: center; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); transition: transform 0.2s;" onclick="window.open('<?php echo $ytUrl; ?>', '_blank')" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                    
                    <?php if ($videoId): ?>
                        <img src="https://img.youtube.com/vi/<?php echo $videoId; ?>/hqdefault.jpg" alt="Video Thumbnail" style="width: 100%; border-radius: 8px; margin-bottom: 10px;">
                    <?php else: ?>
                        <div style="width: 100%; height: 120px; background: #e0e0e0; border-radius: 8px; margin-bottom: 10px; display: flex; align-items: center; justify-content: center;">▶️ Video</div>
                    <?php endif; ?>
                    
                    <p style="font-weight: bold; font-size: 14px; margin: 0; color: #333;"><?php echo htmlspecialchars($item['Name']); ?></p>
                
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <div class="section-col quiz-col">
        <div class="section-label">Quiz</div>
        <div class="quiz-box">
            <div class="quiz-questions">10 QUESTIONS</div>

            <?php if ($quizStatus['attempted']): ?>
                <?php if ($quizStatus['passed']): ?>
                    <div style="display:flex; align-items:center; justify-content:center; gap:8px; margin:10px 0; padding:8px; border-radius:8px; background:#e6f7ec; color:#1e7a3d; font-weight:bold;">
                        <span style="font-size:20px;">✓</span>
                        <span>Pass &mdash; <?php echo $quizStatus['grade']; ?>% (Need <?php echo $quizStatus['passing']; ?>%)</span>
                    </div>
                <?php else: ?>
                    <div style="display:flex; align-items:center; justify-content:center; gap:8px; margin:10px 0; padding:8px; border-radius:8px; background:#fdeaea; color:#c0392b; font-weight:bold;">
                        <span style="font-size:20px;">✗</span>
                        <span>Fail &mdash; <?php echo $quizStatus['grade']; ?>% (Need <?php echo $quizStatus['passing']; ?>%)</span>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div style="text-align:center; margin:10px 0; padding:8px; border-radius:8px; background:#f0f0f0; color:#666; font-weight:bold;">
                    Does not attempt yet!
                </div>
            <?php endif; ?>

            <button class="btn-quiz" onclick="startQuiz()">
                <?php echo $quizStatus['attempted'] ? 'Retake Quiz' : 'Start Quiz'; ?>
            </button>
        </div>
    </div>
</div>

    </div>
  </main>

  <script>
    let currentChapterIdx = 0;
    let activeSubject = "db"; 

    document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    const subjectParam = urlParams.get('subject');  
    const chapterParam = urlParams.get('chapter');  

    // Jika subject ada dalam URL, gunakan itu. Jika tiada, baru guna localStorage.
    if (subjectParam) {
        activeSubject = subjectParam;
    } else {
        activeSubject = localStorage.getItem('selectedQuizSubject') || 'db';
    }

    if (chapterParam) {
        currentChapterIdx = parseInt(chapterParam) - 1; 
    } else {
        currentChapterIdx = parseInt(localStorage.getItem("current_reading_chapter_idx")) || 0;
    }
        const chapterName = localStorage.getItem("current_reading_chapter_name") || "Chapter Module";
        document.getElementById("chapterTitleDisplay").innerText = `Chapter ${currentChapterIdx + 1}: ${chapterName}`;
    });

    function startQuiz() {
    const chapterNumber = currentChapterIdx + 1;
    // Pastikan 'activeSubject' mengandungi kod subjek yang betul (contoh: 'DITP2913')
    window.location.href = `quizStudent.php?subject=${activeSubject}&chapter=${chapterNumber}`;
}

    
  </script>
</body>
</html>