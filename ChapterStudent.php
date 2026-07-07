<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Sambungan database
$host = "100.81.48.34";
$port = "3307";
$dbname = "fictlp db";
$username = "bubustailo";
$password = "Student@123";

$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Sambungan gagal: " . $conn->connect_error);
}

// 0. Wajib login dulu sebelum boleh access chapter
if (!isset($_SESSION['userID'])) {
    header("Location: index.php");
    exit;
}
$currentUserID = $_SESSION['userID'];

// Mapping kod pendek URL -> Subject_Code sebenar dalam DB (satu tempat je, guna berulang-alik)
$subjectMap = [
    'db'  => 'DITP2913',
    'cpp' => 'DITP1113',
    'coa' => 'DITS1133',
];

// 1. Tangkap parameter subject dan chapter dari URL
$subjectParam = isset($_GET['subject']) ? strtolower(trim($_GET['subject'])) : 'db';
$chapterNum   = isset($_GET['chapter']) ? intval($_GET['chapter']) : 1;
if ($chapterNum < 1) $chapterNum = 1;

$dbSubjectCode = $subjectMap[$subjectParam] ?? null;

if (!$dbSubjectCode) {
    die("Subjek tidak dijumpai.");
}

// 1b. Semak student memang enrolled dalam subjek ni sebelum bagi akses content
$enrollStmt = $conn->prepare("SELECT 1 FROM enrollment WHERE userID = ? AND Subject_Code = ?");
$enrollStmt->bind_param("ss", $currentUserID, $dbSubjectCode);
$enrollStmt->execute();
$isEnrolled = $enrollStmt->get_result()->num_rows > 0;
$enrollStmt->close();

if (!$isEnrolled) {
    die("Anda belum enrol subjek ini.");
}

// 2. Cari Chapter_ID sebenar berdasarkan Subject_Code + chapter_order (bukan offset/tekaan)
$resolvedChapterID = null;
$chapterName = '';

$chapterStmt = $conn->prepare(
    "SELECT Chapter_ID, Chapter_Name FROM chapter WHERE Subject_Code = ? AND chapter_order = ?"
);
$chapterStmt->bind_param("si", $dbSubjectCode, $chapterNum);
$chapterStmt->execute();
$chapterResult = $chapterStmt->get_result();
if ($chapterRow = $chapterResult->fetch_assoc()) {
    $resolvedChapterID = intval($chapterRow['Chapter_ID']);
    $chapterName = $chapterRow['Chapter_Name'];
}
$chapterStmt->close();

if (!$resolvedChapterID) {
    die("No chapter found for this subject.");
}

// 3. Cari Quiz_ID sebenar untuk chapter ni (terus melalui Chapter_ID, bukan offset)
$resolvedQuizID = null;
$quizStmt = $conn->prepare("SELECT Quiz_ID FROM quiz WHERE Chapter_ID = ? LIMIT 1");
$quizStmt->bind_param("i", $resolvedChapterID);
$quizStmt->execute();
$quizResult = $quizStmt->get_result();
if ($quizRow = $quizResult->fetch_assoc()) {
    $resolvedQuizID = intval($quizRow['Quiz_ID']);
}
$quizStmt->close();

// 4. Semak status quiz pelajar untuk chapter ini (skor + lulus/gagal)
$quizStatus = [
    'attempted'  => false,
    'passed'     => false,
    'grade'      => 0,
    'passing'    => 0,
    'percentage' => 0
];

if ($resolvedQuizID) {
    $statusSql = "SELECT s.Grade, q.Passing_Mark
                  FROM score s
                  INNER JOIN quiz q ON s.Quiz_ID = q.Quiz_ID
                  WHERE s.userID = ? AND s.Quiz_ID = ?
                  ORDER BY s.attempt_no DESC, s.date_taken DESC 
                  LIMIT 1";
    
    $stmtStatus = $conn->prepare($statusSql);
    if ($stmtStatus) {
        $stmtStatus->bind_param("si", $currentUserID, $resolvedQuizID);
        $stmtStatus->execute();
        $statusResult = $stmtStatus->get_result();
        if ($statusRow = $statusResult->fetch_assoc()) {
            $grade = intval($statusRow['Grade']); // Grade sudah dalam bentuk peratusan (0-100)
            $passingMark = intval($statusRow['Passing_Mark']) > 0 ? intval($statusRow['Passing_Mark']) : 70;
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

// 5. Ambil content (slide/video) untuk Chapter_ID ini
$contents = [];
$contentStmt = $conn->prepare("SELECT * FROM content WHERE Chapter_ID = ?");
$contentStmt->bind_param("i", $resolvedChapterID);
$contentStmt->execute();
$contentResult = $contentStmt->get_result();
while ($row = $contentResult->fetch_assoc()) {
    $contents[] = $row;
}
$contentStmt->close();
?>


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

    <?php include 'sidebarStudent.php'; ?>
    <main class="main">

        <div style="max-width: 1100px; width: 100%; margin: 0 auto;">

            <div class="chapter-header-container" style="display: flex; justify-content: center; align-items: center; gap: 24px; width: 100%; margin-bottom: 40px;">

                <!-- The original pill title remains perfectly centered -->
                <div class="chapter-header-pill" style="margin: 0;">
                    <h1 id="chapterTitleDisplay" style="margin: 0;">
                        Chapter <?php echo $chapterNum; ?>: <?php echo htmlspecialchars($chapterName); ?>
                    </h1>
                </div>
            </div>

            <div class="content-grid">

                <div class="section-col">
                    <div class="section-label">Slide</div>
                    <?php foreach ($contents as $item): ?>
                        <?php if (strtolower($item['File_Type']) == 'slide'): ?>
                            <div style="margin-bottom: 24px; text-align: center;">

                                <!-- Title placed ABOVE the card -->
                                <p style="font-weight: bold; font-size: 16px; margin-bottom: 8px; color: #1e293b;">
                                    <?php echo htmlspecialchars($item['Name']); ?>
                                </p>

                                <!-- Media Card Container (Clickable) -->
                                <div class="media-card" onclick="window.open('<?php echo htmlspecialchars($item['file_path'], ENT_QUOTES); ?>', '_blank')">

                                    <!-- Embed the PDF document directly -->
                                    <!-- pointer-events: none; is the key to making the card clickable -->
                                    <iframe
                                        src="<?php echo htmlspecialchars($item['file_path'], ENT_QUOTES); ?>#toolbar=0&navpanes=0&scrollbar=0"
                                        scrolling="no"
                                        style="width: 100%; height: 100%; border: none; pointer-events: none; overflow: hidden;"
                                        title="<?php echo htmlspecialchars($item['Name']); ?>">
                                    </iframe>

                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <!-- VIDEO COLUMN -->
                <div class="section-col video-col">
                    <div class="section-label">Video</div>
                    <?php foreach ($contents as $item): ?>
                        <?php if (strtolower($item['File_Type']) == 'video'): ?>
                            <?php
                            $ytUrl = $item['file_path'];
                            $videoId = '';
                            // Logic to extract the exact YouTube Video ID from the URL
                            if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $ytUrl, $match)) {
                                $videoId = $match[1];
                            }
                            // Ensure it is treated as a valid web URL
                            if (strpos($ytUrl, 'http') !== 0 && $ytUrl != '') {
                                $ytUrl = 'https://' . $ytUrl;
                            }
                            ?>

                            <!-- NEW WRAPPER: Matches the Slide section perfectly -->
                            <div style="margin-bottom: 24px; text-align: center;">

                                <!-- Title placed ABOVE the card -->
                                <p style="font-weight: bold; font-size: 16px; margin-bottom: 8px; color: #1e293b;">
                                    <?php echo htmlspecialchars($item['Name']); ?>
                                </p>

                                <!-- Media Card Container -->
                                <div class="media-card" onclick="window.open('<?php echo htmlspecialchars($ytUrl, ENT_QUOTES); ?>', '_blank')">
                                    <?php if ($videoId): ?>
                                        <img src="https://img.youtube.com/vi/<?php echo htmlspecialchars($videoId, ENT_QUOTES); ?>/hqdefault.jpg" alt="Video Thumbnail">
                                    <?php else: ?>
                                        <div style="font-weight: 700; color: #64748b; font-size: 14px;">Video</div>
                                    <?php endif; ?>
                                </div>

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
        </div>
    </main>

    <script>
        const activeSubject = <?php echo json_encode($subjectParam); ?>;
        const currentChapterNum = <?php echo json_encode($chapterNum); ?>;

        function startQuiz() {
            window.location.href = `quizStudent.php?subject=${activeSubject}&chapter=${currentChapterNum}`;
        }
    </script>
</body>

</html>