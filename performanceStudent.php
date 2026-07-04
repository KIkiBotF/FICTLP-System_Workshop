<?php
// 1. Mulakan session
session_start();

// Semak jika user sudah login

    


// 2. Sambungan ke Database (Mengikut tetapan index.php anda)
$host = "100.81.48.34";
$port = "3307";          
$dbname = "fictlp db";  
$username = "bubustailo"; 
$password = "Student@123";

$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) { 
    die("Connection failed: " . $conn->connect_error); 
}

// 3. Ambil data prestasi kuiz pelajar dari database (3 topic sahaja ikut jumlah chapter semasa)
$performance_data = [
    'cpp' => [
        'topic1' => 0, 'topic2' => 0, 'topic3' => 0,
        'details' => ['topic1' => '0%', 'topic2' => '0%', 'topic3' => '0%']
    ],
    'db' => [
        'topic1' => 0, 'topic2' => 0, 'topic3' => 0,
        'details' => ['topic1' => '0%', 'topic2' => '0%', 'topic3' => '0%']
    ],
    'coa' => [
        'topic1' => 0, 'topic2' => 0, 'topic3' => 0,
        'details' => ['topic1' => '0%', 'topic2' => '0%', 'topic3' => '0%']
    ]
];

// Query mencantumkan table score dan quiz berdasarkan Quiz_ID
$sql = "SELECT q.Subject_Code, q.Quiz_title, q.Passing_Mark, s.Grade 
        FROM score s 
        INNER JOIN quiz q ON s.Quiz_ID = q.Quiz_ID 
        WHERE s.userID = ?";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("s", $current_user);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // FIX: Subject_Code dalam DB adalah kod PANJANG (DITP2913, DITP1113,
        // DITS1133), bukan 'cpp'/'db'/'coa'. Perlu mapping dulu sebelum
        // digunakan sebagai key array $performance_data.
        $subjectCodeMap = [
            'ditp2913' => 'db',
            'ditp1113' => 'cpp',
            'dits1133' => 'coa'
        ];
        $rawSubjectCode = strtolower($row['Subject_Code']);
        $subject = $subjectCodeMap[$rawSubjectCode] ?? $rawSubjectCode;

        $quiz_title = $row['Quiz_title'];           // Contoh: "CH01 DATABASE" / "Chapter 1: Introduction to C++"
        $grade = intval($row['Grade']);
        $passing_mark = intval($row['Passing_Mark']) > 0 ? intval($row['Passing_Mark']) : 100; // Elakkan pembahagian dengan 0
        
        // FIX: Pemetaan tajuk kuiz kepada ID Elemen (topic1 - topic5) guna
        // regex supaya berfungsi untuk semua format tajuk sedia ada
        // ("CH01 DATABASE", "Chapter 1: ...", "Topic 01: ...")
        $topic_key = '';
        if (preg_match('/(?:CH|Chapter|Topic)\s*0?(\d)/i', $quiz_title, $m)) {
            $chapterDigit = intval($m[1]);
            if ($chapterDigit >= 1 && $chapterDigit <= 3) {
                $topic_key = 'topic' . $chapterDigit;
            }
        }
        
        // Masukkan peratusan ke dalam array prestasi jika subjek wujud
        if ($topic_key && isset($performance_data[$subject])) {
            // FIX: Grade dalam table `score` SUDAH dalam bentuk peratusan
            // (0-100), jadi TIDAK perlu dibahagi dengan Passing_Mark lagi.
            // Passing_Mark cuma ambang lulus (contoh 70 = perlu 70%), bukan
            // markah penuh/max.
            $percentage = $grade > 100 ? 100 : $grade; // Hadkan maksimum 100%
            
            $performance_data[$subject][$topic_key] = $percentage;
            $performance_data[$subject]['details'][$topic_key] = "$grade%";
        }
    }
    $stmt->close();
}
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
                    <option value="cpp">C++ Programming</option>
                    <option value="db">Database</option>
                    <option value="coa">Computer Organization and Architecture</option>
                </select>
            </div>

            <div class="chart-container">
                <div class="topic-row" id="row-topic1">
                    <span class="topic-title" id="title-topic1">Topic 01</span>
                    <div class="progress-bar-wrapper">
                        <div class="bar-fill" id="fill-topic1" style="width: 0%;"></div>
                    </div>
                    <span class="percentage-value" id="text-topic1">0%</span>
                </div>

                <div class="topic-row" id="row-topic2">
                    <span class="topic-title" id="title-topic2">Topic 02</span>
                    <div class="progress-bar-wrapper">
                        <div class="bar-fill" id="fill-topic2" style="width: 0%;"></div>
                    </div>
                    <span class="percentage-value" id="text-topic2">0%</span>
                </div>

                <div class="topic-row" id="row-topic3">
                    <span class="topic-title" id="title-topic3">Topic 03</span>
                    <div class="progress-bar-wrapper">
                        <div class="bar-fill" id="fill-topic3" style="width: 0%;"></div>
                    </div>
                    <span class="percentage-value" id="text-topic3">0%</span>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Menggunakan fungsi bawaan asal PHP (json_encode) secara terus
        const dbPerformanceData = <?php echo json_encode($performance_data); ?>;
        
        // Pemetaan nama topik mengikut subjek secara dinamik
        const subjectTopics = {
            'cpp': {
                'topic1': "Topic 01: Basics & Data Types",
                'topic2': "Topic 02: Control Structures",
                'topic3': "Topic 03: Loops"
            },
            'db': {
                'topic1': "Topic 01: Introduction to Database",
                'topic2': "Topic 02: Entity-Relationship Diagram (ERD)",
                'topic3': "Topic 03: Relational Model & Constraints"
            },
            'coa': {
                'topic1': "Topic 01: Number Systems & Logic Gates",
                'topic2': "Topic 02: Central Processing Unit (CPU)",
                'topic3': "Topic 03: Memory Hierarchy & Cache"
            }
        };

        function updatePerformanceView() {
            const selectedSubject = document.getElementById('subject-select').value;
            const currentData = dbPerformanceData[selectedSubject];
            const currentSubjectTitles = subjectTopics[selectedSubject];

            if (currentData && currentSubjectTitles) {
                // Gelung pemetaan dialirkan dari topic1 sehingga topic3 (3 chapter sahaja)
                const topics = ['topic1', 'topic2', 'topic3'];
                
                topics.forEach(topic => {
                    const percentage = currentData[topic] || 0;
                    const scoreDetail = currentData['details'][topic] || '0%';
                    const baseTitle = currentSubjectTitles[topic];
                    
                    if (scoreDetail !== '0%') {
                        document.getElementById(`title-${topic}`).innerText = `${baseTitle} (${scoreDetail})`;
                    } else {
                        document.getElementById(`title-${topic}`).innerText = baseTitle;
                    }
                    
                    document.getElementById(`fill-${topic}`).style.width = `${percentage}%`;
                    document.getElementById(`text-${topic}`).innerText = `${percentage}%`;
                });
            }
        }

        window.onload = function() {
            updatePerformanceView();
        };
    </script>
</body>
</html>