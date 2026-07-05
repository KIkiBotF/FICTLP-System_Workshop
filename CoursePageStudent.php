<?php
session_start();
if (!isset($_SESSION['userID'])) {
    header("Location: index.php");
    exit();
}
$currentUserID = $_SESSION['userID'];

$host = "100.81.48.34";
$port = "3307";
$dbname = "fictlp db";
$username = "bubustailo";
$password = "Student@123";

$conn = new mysqli($host, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Mapping kod pendek URL -> Subject_Code sebenar dalam DB
$subjectMap = [
    'cpp' => 'DITP1113',
    'db'  => 'DITP2913',
    'coa' => 'DITS1133',
];
$subjectNames = [
    'DITP1113' => 'C++ Programming',
    'DITP2913' => 'Database',
    'DITS1133' => 'Computer Organization and Architecture',
];

$subjectShort = isset($_GET['subject']) ? strtolower(trim($_GET['subject'])) : '';
$dbSubjectCode = $subjectMap[$subjectShort] ?? null;

if (!$dbSubjectCode) {
    die("Invalid subject. Please choose a subject from the <a href='SubjectStudent.php'>Subject</a> page.");
}
$courseName = $subjectNames[$dbSubjectCode];

// 1. Semak status enrollment SEBENAR dari table `enrollment`
//    (sebelum ni cuma localStorage - refresh browser lain / device lain
//    akan hilang status enrolled, dan tak sync dengan DB langsung)
$enrollStmt = $conn->prepare("SELECT 1 FROM enrollment WHERE userID = ? AND Subject_Code = ?");
$enrollStmt->bind_param("ss", $currentUserID, $dbSubjectCode);
$enrollStmt->execute();
$isEnrolled = $enrollStmt->get_result()->num_rows > 0;
$enrollStmt->close();

// 2. Ambil semua chapter untuk subjek ini, ikut urutan chapter_order
$chapters = [];
$chapterStmt = $conn->prepare("SELECT Chapter_ID, Chapter_Name, chapter_order FROM chapter WHERE Subject_Code = ? ORDER BY chapter_order ASC");
$chapterStmt->bind_param("s", $dbSubjectCode);
$chapterStmt->execute();
$chapterResult = $chapterStmt->get_result();
while ($row = $chapterResult->fetch_assoc()) {
    $chapters[] = $row;
}
$chapterStmt->close();

// 3. Untuk setiap chapter, cari quiz & markah TERKINI pelajar (jika ada)
//    (sebelum ni quiz score simpan dalam localStorage terus - senang hilang
//    bila clear cache, dan tak sync antara device)
$totalChapters = count($chapters);
$completedCount = 0;
$totalScoreSum = 0;
$scoredCount = 0;

foreach ($chapters as &$ch) {
    $ch['quiz_id'] = null;
    $ch['grade'] = null;
    $ch['passing_mark'] = 70;
    $ch['passed'] = false;

    $quizStmt = $conn->prepare("SELECT Quiz_ID, Passing_Mark FROM quiz WHERE Chapter_ID = ? LIMIT 1");
    $quizStmt->bind_param("i", $ch['Chapter_ID']);
    $quizStmt->execute();
    $quizRow = $quizStmt->get_result()->fetch_assoc();
    $quizStmt->close();

    if ($quizRow) {
        $ch['quiz_id'] = intval($quizRow['Quiz_ID']);
        $ch['passing_mark'] = intval($quizRow['Passing_Mark']) > 0 ? intval($quizRow['Passing_Mark']) : 70;

        $scoreStmt = $conn->prepare(
            "SELECT Grade FROM score WHERE userID = ? AND Quiz_ID = ? ORDER BY date_taken DESC LIMIT 1"
        );
        $scoreStmt->bind_param("si", $currentUserID, $ch['quiz_id']);
        $scoreStmt->execute();
        $scoreRow = $scoreStmt->get_result()->fetch_assoc();
        $scoreStmt->close();

        if ($scoreRow) {
            $ch['grade'] = intval($scoreRow['Grade']); // Grade sudah peratusan (0-100)
            $ch['passed'] = $ch['grade'] >= $ch['passing_mark'];
            $totalScoreSum += $ch['grade'];
            $scoredCount++;
            if ($ch['passed']) {
                $completedCount++;
            }
        }
    }
}
unset($ch);

$progressPct = $totalChapters > 0 ? round(($completedCount / $totalChapters) * 100) : 0;
$avgScore = $scoredCount > 0 ? round($totalScoreSum / $scoredCount) : 0;

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Course Module</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght=400;600;700;900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="CoursePageStudent.css">
</head>
<body>

<?php include 'sidebarStudent.php'; ?>

  <main class="main">
    <div class="header-section">
      <div class="page-header">
        <h1>Welcome Back to <span id="courseNameTitle"><?php echo htmlspecialchars($courseName); ?></span></h1>
        <button class="btn-start <?php echo $isEnrolled ? 'enrolled' : ''; ?>" id="startModuleBtn" <?php echo $isEnrolled ? 'disabled' : ''; ?> onclick="enrollCourse()">
            <?php echo $isEnrolled ? 'Enrolled ✓' : 'Start Module'; ?>
        </button>
      </div>
      <p class="progress-label" id="progressLabel" style="margin-top:6px">
        📚 Current Progress: <?php echo $progressPct; ?>% (<?php echo $completedCount; ?>/<?php echo $totalChapters; ?> chapters completed)
      </p>
    </div>

    <div class="content-grid">
      <div class="chapter-list" id="chapterListContainer">
        <?php foreach ($chapters as $index => $ch): ?>
            <?php
                $displayNum = str_pad($index + 1, 2, '0', STR_PAD_LEFT);
                $isDone = $ch['passed'];
                $scoreBadge = $ch['grade'] !== null ? '<span class="score-badge">' . $ch['grade'] . '%</span>' : '';
                if ($isDone) {
                    $statusIcon = '<span class="chapter-status done">✓</span>';
                } elseif ($isEnrolled) {
                    $statusIcon = '<span class="chapter-status pending">○</span>';
                } else {
                    $statusIcon = '<span class="chapter-status locked">🔒</span>';
                }
            ?>
            <?php if ($isEnrolled): ?>
                <a class="chapter-card <?php echo $isDone ? 'completed' : ''; ?>"
                   href="ChapterStudent.php?subject=<?php echo urlencode($subjectShort); ?>&chapter=<?php echo $index + 1; ?>">
                    <span class="chapter-num"><?php echo $displayNum; ?></span>
                    <span class="chapter-name"><?php echo htmlspecialchars($ch['Chapter_Name']); ?></span>
                    <?php echo $scoreBadge; ?>
                    <?php echo $statusIcon; ?>
                </a>
            <?php else: ?>
                <a class="chapter-card locked" href="javascript:void(0)" onclick="alert('🔒 Access Denied! Please click the Start Module button above to unlock.')">
                    <span class="chapter-num"><?php echo $displayNum; ?></span>
                    <span class="chapter-name"><?php echo htmlspecialchars($ch['Chapter_Name']); ?></span>
                    <?php echo $statusIcon; ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
      </div>

      <div class="poor-panel" id="poorPanel">
        <div class="poor-panel-title">Chapter Overview</div>
        <div class="poor-panel-content" id="poorPanelContent">
            <div class="stats-summary">
                <div class="stat-card"><div class="stat-value"><?php echo $completedCount; ?>/<?php echo $totalChapters; ?></div><div class="stat-label">Done</div></div>
                <div class="stat-card"><div class="stat-value"><?php echo $progressPct; ?>%</div><div class="stat-label">Progress</div></div>
                <div class="stat-card"><div class="stat-value"><?php echo $avgScore; ?></div><div class="stat-label">Avg Score</div></div>
            </div>
            <?php
                if ($progressPct === 100) {
                    $performanceMsg = '🎉 Excellent! Mastered!';
                    $msgColor = '#10b981';
                } elseif ($progressPct >= 60) {
                    $performanceMsg = '👍 Good progress!';
                    $msgColor = '#f59e0b';
                } elseif ($progressPct > 0) {
                    $performanceMsg = '📖 Keep reading!';
                    $msgColor = '#3b82f6';
                } elseif ($isEnrolled) {
                    $performanceMsg = '🚀 Click a chapter!';
                    $msgColor = '#3b82f6';
                } else {
                    $performanceMsg = '🔒 Start Module first!';
                    $msgColor = '#3b82f6';
                }
            ?>
            <div class="performance-message" style="color:<?php echo $msgColor; ?>;"><?php echo $performanceMsg; ?></div>
            <div class="chapters-header"><span>#</span><span>Chapter</span><span>Score</span><span>Sts</span></div>
            <div class="poor-chapters-list">
                <?php foreach ($chapters as $index => $ch): ?>
                    <?php
                        $shortName = strlen($ch['Chapter_Name']) > 18 ? substr($ch['Chapter_Name'], 0, 18) . '...' : $ch['Chapter_Name'];
                        $scoreDisplay = $ch['grade'] !== null ? $ch['grade'] . '%' : '--';
                    ?>
                    <div class="poor-chapter-item <?php echo $ch['passed'] ? 'completed' : ''; ?>">
                        <span class="poor-chapter-num"><?php echo str_pad($index + 1, 2, '0', STR_PAD_LEFT); ?></span>
                        <span class="poor-chapter-name"><?php echo htmlspecialchars($shortName); ?></span>
                        <span class="poor-chapter-score"><?php echo $scoreDisplay; ?></span>
                        <span class="poor-chapter-status"><?php echo $ch['passed'] ? '✅' : '⭕'; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
      </div>
    </div>
  </main>

  <script>
    // Enrollment sekarang terus tulis ke table `enrollment` (AJAX ke
    // saveEnrollment.php yang dah wujud), bukan localStorage semata-mata.
    function enrollCourse() {
        fetch('saveEnrollment.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'subjectCode=<?php echo urlencode($dbSubjectCode); ?>'
        })
        .then(res => res.text())
        .then(msg => {
            if (msg.trim() === 'success') {
                window.location.reload();
            } else {
                alert('Failed to enroll: ' + msg);
            }
        })
        .catch(() => alert('Error occurred while enrolling.'));
    }
  </script>
</body>
</html>