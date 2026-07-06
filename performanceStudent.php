<?php
// 1. Mulakan session
session_start();

if (!isset($_SESSION['userID'])) {
    header("Location: index.php");
    exit();
}

$current_user = $_SESSION['userID'];

$host = "100.81.48.34";
$port = "3307";
$dbname = "fictlp db";
$username = "bubustailo";
$password = "Student@123";

$conn = new mysqli($host, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Mapping Subject_Code sebenar -> kod pendek untuk key array
$subjectMap = [
    'DITP1113' => 'cpp',
    'DITP2913' => 'db',
    'DITS1133' => 'coa',
];
$subjectTitles = [
    'cpp' => 'C++ Programming',
    'db'  => 'Database',
    'coa' => 'Computer Organization and Architecture',
];

$performance_data = [
    'cpp' => ['chapters' => []],
    'db'  => ['chapters' => []],
    'coa' => ['chapters' => []],
];


$chapterStmt = $conn->prepare("SELECT Subject_Code, chapter_order, Chapter_Name FROM chapter ORDER BY Subject_Code, chapter_order");
$chapterStmt->execute();
$chapterResult = $chapterStmt->get_result();
while ($row = $chapterResult->fetch_assoc()) {
    $subject = $subjectMap[$row['Subject_Code']] ?? null;
    if (!$subject) continue;

    $order = intval($row['chapter_order']);
    $performance_data[$subject]['chapters'][$order] = [
        'name'       => $row['Chapter_Name'],
        'percentage' => 0,
        'detail'     => '0%',
    ];
}
$chapterStmt->close();

// guna `chapter.chapter_order` untuk overlay markah
$sql = "SELECT c.Subject_Code, c.chapter_order, s.Grade
        FROM score s
        INNER JOIN quiz q ON s.Quiz_ID = q.Quiz_ID
        INNER JOIN chapter c ON q.Chapter_ID = c.Chapter_ID
        WHERE s.userID = ?
        ORDER BY s.date_taken DESC";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("s", $current_user);
    $stmt->execute();
    $result = $stmt->get_result();

    // ORDER BY date_taken DESC -> row PERTAMA untuk setiap chapter
    // adalah attempt TERKINI, guna 'seen' untuk skip attempt lama.
    $seen = [];

    while ($row = $result->fetch_assoc()) {
        $subject = $subjectMap[$row['Subject_Code']] ?? null;
        $order = intval($row['chapter_order']);

        // Skip kalau chapter ni tak wujud dalam senarai chapter aktif
        // (contoh markah lama untuk quiz yang chapter dia dah dibuang)
        if (!$subject || !isset($performance_data[$subject]['chapters'][$order])) continue;

        $seenKey = $subject . '_' . $order;
        if (isset($seen[$seenKey])) continue;
        $seen[$seenKey] = true;

        $grade = intval($row['Grade']); // Grade sudah dalam bentuk peratusan (0-100)
        $percentage = $grade > 100 ? 100 : $grade;

        $performance_data[$subject]['chapters'][$order]['percentage'] = $percentage;
        $performance_data[$subject]['chapters'][$order]['detail'] = "$grade%";
    }
    $stmt->close();
}

// Susun ikut chapter_order dan reindex jadi array berturutan (0,1,2...)
// supaya senang di-loop dalam JS tanpa 'gap' pada key.
foreach ($performance_data as &$subj) {
    ksort($subj['chapters']);
    $subj['chapters'] = array_values($subj['chapters']);
}
unset($subj);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Metrics - Student LMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght=400;600;700;900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="performanceStudent.css">
</head>
<body>

<?php include 'sidebarStudent.php'; ?>
    <main class="main-content">
        <header class="content-header">
            <h1>Performance</h1>
        </header>

        <div class="metrics-card">
            <div class="selector-wrapper">
                <label for="subject-select">Subject:</label>
                <select id="subject-select" class="custom-select" onchange="updatePerformanceView()">
                    <?php foreach ($subjectTitles as $key => $title): ?>
                        <option value="<?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($title); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="chart-container" id="chartContainer"></div>
        </div>
    </main>

    <script>
        const dbPerformanceData = <?php echo json_encode($performance_data); ?>;

        function updatePerformanceView() {
            const selectedSubject = document.getElementById('subject-select').value;
            const chapters = (dbPerformanceData[selectedSubject] && dbPerformanceData[selectedSubject].chapters) || [];
            const container = document.getElementById('chartContainer');

            if (chapters.length === 0) {
                container.innerHTML = '<p style="text-align:center; color:#888; padding:20px 0;">Tiada chapter untuk subjek ini lagi.</p>';
                return;
            }

            container.innerHTML = chapters.map((ch, idx) => {
                const num = String(idx + 1).padStart(2, '0');
                const titleText = ch.detail !== '0%'
                    ? `Topic ${num}: ${ch.name} (${ch.detail})`
                    : `Topic ${num}: ${ch.name}`;

                return `
                    <div class="topic-row">
                        <span class="topic-title">${titleText}</span>
                        <div class="progress-bar-wrapper">
                            <div class="bar-fill" style="width: ${ch.percentage}%;"></div>
                        </div>
                        <span class="percentage-value">${ch.percentage}%</span>
                    </div>
                `;
            }).join('');
        }

        window.onload = function() {
            updatePerformanceView();
        };
    </script>
</body>
</html>