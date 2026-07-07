<?php
session_start();
if (!isset($_SESSION['userID'])) {
    header("Location: index.php");
    exit();
}

$host = "100.81.48.34";
$port = "3307";
$dbname = "fictlp db";
$username = "bubustailo";
$password = "Student@123";

$conn = new mysqli($host, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$subjects = [
    'cpp' => ['code' => 'DITP1113', 'title' => 'C++ Programming', 'color' => 'teal'],
    'db'  => ['code' => 'DITP2913', 'title' => 'Database', 'color' => 'blue'],
    'coa' => ['code' => 'DITS1133', 'title' => 'Computer Organization and Architecture', 'color' => 'green'],
];

foreach ($subjects as $key => &$subj) {
    $chapterStmt = $conn->prepare("SELECT Chapter_Name FROM chapter WHERE Subject_Code = ? ORDER BY chapter_order ASC");
    $chapterStmt->bind_param("s", $subj['code']);
    $chapterStmt->execute();
    $chapterResult = $chapterStmt->get_result();
    $subj['chapters'] = [];
    while ($row = $chapterResult->fetch_assoc()) {
        $subj['chapters'][] = $row['Chapter_Name'];
    }
    $chapterStmt->close();

    $lecturerStmt = $conn->prepare(
        "SELECT u.Name FROM lecture_subject ls
         INNER JOIN user u ON ls.userID = u.userID
         WHERE ls.Subject_Code = ? LIMIT 1"
    );
    $lecturerStmt->bind_param("s", $subj['code']);
    $lecturerStmt->execute();
    $lecturerRow = $lecturerStmt->get_result()->fetch_assoc();
    $lecturerStmt->close();
    $subj['lecturer'] = $lecturerRow ? $lecturerRow['Name'] : 'Not yet determined';
}
unset($subj);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Subject</title>
  <link rel="stylesheet" href="subjectStudent.css">
</head>
<body>
<?php include 'sidebarStudent.php'; ?>

  <main class="main">
    <h1 class="page-title">Subject</h1>
    <p class="page-subtitle">Choose subject:</p>

    <?php foreach ($subjects as $key => $subj): ?>
    <a href="CoursePageStudent.php?subject=<?php echo urlencode($key); ?>"
       class="card <?php echo htmlspecialchars($subj['color']); ?>"
       onclick="localStorage.setItem('selectedQuizSubject','<?php echo htmlspecialchars($key); ?>')">
      <div class="card-header">
        <h2><?php echo htmlspecialchars($subj['title']); ?></h2>
        <span class="lecturer">(<?php echo htmlspecialchars($subj['lecturer']); ?>)</span>
      </div>
      <div class="card-body">
        <?php if (count($subj['chapters']) > 0): ?>
          <div class="topics <?php echo count($subj['chapters']) <= 1 ? 'single-col' : ''; ?>">
            <?php foreach ($subj['chapters'] as $chapterName): ?>
                <div class="topic-item"><?php echo htmlspecialchars($chapterName); ?></div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="topics single-col">
            <div class="topic-item" style="color:#888; font-style:italic;"> Does not have any chapters yet.</div>
          </div>
        <?php endif; ?>
      </div>
    </a>
    <?php endforeach; ?>
  </main>

</body>
</html>