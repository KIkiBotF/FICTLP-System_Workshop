<?php
session_start();
// Tetapan database
$host = "100.81.48.34";
$port = "3307";
$dbname = "fictlp db";
$username = "bubustailo";
$password = "Student@123";

$conn = new mysqli($host, $username, $password, $dbname, $port);

// Ambil parameter dari URL (?subject=CPP&chapter=1)
$subjectKey = $_GET['subject'] ?? 'CPP';
$chapterNum = $_GET['chapter'] ?? '1';

// ---> NEW LINE ADDED BELOW <---
$displaySubject = strtoupper($subjectKey) === 'CPP' ? 'C++' : htmlspecialchars($subjectKey);
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
    <h1>Quiz: <?php echo $displaySubject; ?></h1>
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
    const chapterId = <?php echo json_encode($chapterNum); ?>;
    // Ambil pembolehubah subjek dari PHP untuk kegunaan JavaScript
    const subjectKey = <?php echo json_encode($subjectKey); ?>; 

    fetch('get_Question.php?quiz_id=' + chapterId + '&subject=' + encodeURIComponent(subjectKey))
        .then(res => {
            if (!res.ok) throw new Error("Gagal sambung ke database");
            return res.json();
        })
        .then(data => {
            questions = data;
            if(questions && questions.length > 0) {
                buildSidebarNav();
                loadQuestion(0);
            } else {
                document.getElementById('qNumberAndText').innerText = "Tiada soalan dalam bab ini untuk subjek " + subjectKey + ".";
            }
        })
        .catch(err => {
            console.error("Ralat:", err);
            document.getElementById('qNumberAndText').innerText = "Ralat: Gagal memuatkan soalan.";
        });

    function buildSidebarNav() {
        const navContainer = document.getElementById("questionNavContainer");
        navContainer.innerHTML = "";
        questions.forEach((_, index) => {
            const row = document.createElement("div");
            row.className = `nav-row ${index === currentIdx ? 'active' : ''}`;
            row.id = `navRow-${index}`;
            row.onclick = () => loadQuestion(index);
            
            let qId = questions[index].Question_ID;
            let isAnswered = studentAnswers[qId] ? 'correct' : ''; 
            let tickIcon = studentAnswers[qId] ? '✔' : ''; // Masukkan icon tick
            
            row.className = "nav-item-border"; 
            row.innerHTML = `<span>Question ${index + 1}</span><div class="status-circle ${isAnswered}" id="status-${index}">${tickIcon}</div>`;
            navContainer.appendChild(row);
        });
    }

    function loadQuestion(index) {
        currentIdx = index;
        let q = questions[currentIdx];
        document.getElementById('qNumberAndText').innerText = `${currentIdx + 1}. ${q.Question_Text}`;
        
        const container = document.getElementById('optionsContainer');
        // Dipotong sehingga 'C' sahaja mengikut struktur database anda
        container.innerHTML = ['A', 'B', 'C'].map(opt => `
            <div class="option-item ${studentAnswers[q.Question_ID] == opt ? 'checked' : ''}" onclick="selectOpt('${opt}')">
                <input type="radio" name="question_${q.Question_ID}" value="${opt}" ${studentAnswers[q.Question_ID] == opt ? 'checked' : ''}>
                ${opt}. ${q['Opt' + opt]}
            </div>
        `).join('');

        // Highlight soalan aktif di sidebar
        document.querySelectorAll('.nav-row').forEach((el, idx) => {
            if(idx === currentIdx) el.classList.add('active');
            else el.classList.remove('active');
        });

        // Tukar teks butang kepada Submit jika berada di soalan terakhir
        let btn = document.getElementById('actionBtn');
        if (currentIdx === questions.length - 1) {
            btn.innerText = "Submit";
        } else {
            btn.innerText = "Next";
        }
    }

     function selectOpt(opt) {
        let qId = questions[currentIdx].Question_ID;
        studentAnswers[qId] = opt;
        
        // Update DOM terus untuk masukkan kelas dan icon tick
        let statusEl = document.getElementById(`status-${currentIdx}`);
        statusEl.className = "status-circle correct";
        statusEl.innerHTML = "✔"; 
        
        loadQuestion(currentIdx);
    }

    function clearSelection() {
        let qId = questions[currentIdx].Question_ID;
        delete studentAnswers[qId];
        
        // Buang kelas dan icon tick
        let statusEl = document.getElementById(`status-${currentIdx}`);
        statusEl.className = "status-circle";
        statusEl.innerHTML = ""; 
        
        loadQuestion(currentIdx);
    }
    

    function handleAction() {
        if(currentIdx < questions.length - 1) {
            // Jika soalan belum habis, gerak ke soalan seterusnya
            loadQuestion(currentIdx + 1);
        } else {
            // 2. Jika sudah soalan terakhir, jalankan fungsi SUBMIT ini:
            fetch('SubmitQuiz.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    answers: studentAnswers, // Object jawapan pelajar
                    uiz_id: chapterId,      // ID Bab
                    subject: subjectKey      // Kod Subjek (e.g., 'CPP' atau 'db')
                })
            })
            .then(res => res.text()) // Tukar respons dari PHP kepada teks
            .then(msg => { 
                alert(msg); // Paparkan mesej "Tahniah! Markah anda..."
                window.location.href = 'ChapterStudent.php';
            })
            .catch(error => {
                console.error('Ralat:', error);
                alert('Gagal menghantar kuiz.');
            });
        }
    }
</script>
</body>
</html>