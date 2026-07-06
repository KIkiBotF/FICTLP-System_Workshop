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

$subjectShort = isset($_GET['subject']) ? strtolower(trim($_GET['subject'])) : 'cpp';
$chapterNum = isset($_GET['chapter']) ? intval($_GET['chapter']) : 1;
if ($chapterNum < 1) $chapterNum = 1;

$dbSubjectCode = $subjectMap[$subjectShort] ?? null;
$displaySubject = $dbSubjectCode ? $subjectNames[$dbSubjectCode] : $subjectShort;

$resolvedQuizID = null;
if ($dbSubjectCode) {
    $stmt = $conn->prepare(
        "SELECT q.Quiz_ID FROM quiz q
         INNER JOIN chapter c ON q.Chapter_ID = c.Chapter_ID
         WHERE c.Subject_Code = ? AND c.chapter_order = ?
         LIMIT 1"
    );
    $stmt->bind_param("si", $dbSubjectCode, $chapterNum);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row) {
        $resolvedQuizID = intval($row['Quiz_ID']);
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Quiz System</title>
    <link rel="stylesheet" href="quizStudent.css">
</head>
<body>

<header class="quiz-header">
    <h1>Quiz: <?php echo htmlspecialchars($displaySubject); ?></h1>
</header>

<main class="quiz-container">
    <aside class="question-nav" id="questionNavContainer"></aside>
    <section class="question-box">
        <h2 id="qNumberAndText">Memuatkan soalan...</h2>
        <div class="options-container" id="optionsContainer"></div>
        <footer class="action-footer">
            <button class="btn-clear" onclick="clearSelection()">Clear</button>
            <button class="btn-next" id="actionBtn" onclick="handleAction()">Next</button>
        </footer>
    </section>
</main>

<script>
    let questions = [];
    let currentIdx = 0;
    let studentAnswers = {};
    const resolvedQuizID = <?php echo json_encode($resolvedQuizID); ?>;
    const chapterNum = <?php echo json_encode($chapterNum); ?>;
    const subjectKey = <?php echo json_encode($subjectShort); ?>;

    if (!resolvedQuizID) {
        document.getElementById('qNumberAndText').innerText = "Quiz for this chapter is not available.";
    } else {
        // Hantar Quiz_ID SEBENAR (bukan nombor chapter) ke get_Question.php
        fetch('get_Question.php?quiz_id=' + resolvedQuizID)
            .then(res => {
                if (!res.ok) throw new Error("Failed to connect to database");
                return res.json();
            })
            .then(data => {
                questions = data;
                if (questions && questions.length > 0) {
                    buildSidebarNav();
                    loadQuestion(0);
                } else {
                    document.getElementById('qNumberAndText').innerText = "No questions available for this chapter.";
                }
            })
            .catch(err => {
                console.error("Error:", err);
                document.getElementById('qNumberAndText').innerText = "Error: Failed to load questions.";
            });
    }

    function buildSidebarNav() {
        const navContainer = document.getElementById("questionNavContainer");
        navContainer.innerHTML = "";
        questions.forEach((_, index) => {
            const row = document.createElement("div");
            row.className = "nav-item-border";
            row.id = `navRow-${index}`;
            row.onclick = () => loadQuestion(index);

            let qId = questions[index].Question_ID;
            let tickIcon = studentAnswers[qId] ? '✔' : '';
            let isAnswered = studentAnswers[qId] ? 'correct' : '';

            row.innerHTML = `<span>Question ${index + 1}</span><div class="status-circle ${isAnswered}" id="status-${index}">${tickIcon}</div>`;
            navContainer.appendChild(row);
        });
    }

    function loadQuestion(index) {
        currentIdx = index;
        let q = questions[currentIdx];
        document.getElementById('qNumberAndText').innerText = `${currentIdx + 1}. ${q.Question_Text}`;

        const container = document.getElementById('optionsContainer');
        // FIX: sokong sehingga OptD (4 pilihan) - sebelum ni dipotong
        // kepada A/B/C sahaja walaupun DB dah ada OptD.
        const opts = ['A', 'B', 'C', 'D'].filter(opt => q['Opt' + opt] !== null && q['Opt' + opt] !== undefined && q['Opt' + opt] !== '');
        container.innerHTML = opts.map(opt => `
            <div class="option-item ${studentAnswers[q.Question_ID] == opt ? 'checked' : ''}" onclick="selectOpt('${opt}')">
                <input type="radio" name="question_${q.Question_ID}" value="${opt}" ${studentAnswers[q.Question_ID] == opt ? 'checked' : ''}>
                ${opt}. ${q['Opt' + opt]}
            </div>
        `).join('');

        document.querySelectorAll('.nav-item-border').forEach((el, idx) => {
            if (idx === currentIdx) el.classList.add('active');
            else el.classList.remove('active');
        });

        let btn = document.getElementById('actionBtn');
        btn.innerText = (currentIdx === questions.length - 1) ? "Submit" : "Next";
    }

    function selectOpt(opt) {
        let qId = questions[currentIdx].Question_ID;
        studentAnswers[qId] = opt;

        let statusEl = document.getElementById(`status-${currentIdx}`);
        statusEl.className = "status-circle correct";
        statusEl.innerHTML = "✔";

        loadQuestion(currentIdx);
    }

    function clearSelection() {
        let qId = questions[currentIdx].Question_ID;
        delete studentAnswers[qId];

        let statusEl = document.getElementById(`status-${currentIdx}`);
        statusEl.className = "status-circle";
        statusEl.innerHTML = "";

        loadQuestion(currentIdx);
    }
    

    function handleAction() {
        if (currentIdx < questions.length - 1) {
            loadQuestion(currentIdx + 1);
        } else {
            // NOTA: key 'quiz_id' di sini bermaksud NOMBOR CHAPTER (1,2,3),
            // bukan Quiz_ID sebenar - SubmitQuiz.php akan resolve Quiz_ID
            // sebenar sendiri melalui subject + chapter number ini.
            fetch('SubmitQuiz.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    answers: studentAnswers,
                    quiz_id: chapterNum,
                    subject: subjectKey
                })
            })
            .then(res => res.text())
            .then(msg => {
                alert(msg);
                window.location.href = `ChapterStudent.php?subject=${subjectKey}&chapter=${chapterNum}`;
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to submit quiz.');
            });
        }
    }
</script>
</body>
</html>