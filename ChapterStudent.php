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

    <div class="chapter-header">
      <h1 id="chapterTitleDisplay">Loading Chapter...</h1>
    </div>

    <div class="content-grid">

      <div class="section-col">
        <div class="section-label">Slide</div>
        <div class="media-card" onclick="openSlide()">
          <svg viewBox="0 0 24 24">
            <rect x="3" y="3" width="18" height="18" rx="2"/>
            <circle cx="8.5" cy="8.5" r="1.5"/>
            <polyline points="21 15 16 10 5 21"/>
          </svg>
        </div>
      </div>

      <div class="section-col video-col">
        <div class="section-label">Video</div>
        <div class="media-card" onclick="openVideo(1)">
          <svg viewBox="0 0 24 24">
            <rect x="2" y="5" width="15" height="14" rx="2"/>
            <polygon points="22 7 17 12 22 17 22 7"/>
          </svg>
        </div>
        <div class="media-card" onclick="openVideo(2)">
          <svg viewBox="0 0 24 24">
            <rect x="2" y="5" width="15" height="14" rx="2"/>
            <polygon points="22 7 17 12 22 17 22 7"/>
          </svg>
        </div>
      </div>

      <div class="section-col quiz-col">
        <div class="section-label">Quiz</div>
        <div class="quiz-box">
          <div class="quiz-questions">10 QUESTIONS</div>
          <button class="btn-quiz" onclick="startQuiz()">Start Quiz</button>
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
        let targetSubject = activeSubject;
        if (activeSubject === "db") {
          targetSubject = "database";
        }
        window.location.href = `quizStudent.html?subject=${targetSubject}&chapter=${chapterNumber}`; 
    }
    
    function openSlide() {
        const chapterNumber = currentChapterIdx + 1;
        window.location.href = `slides/${activeSubject}_chapter${chapterNumber}.pdf`;
    }

    function openVideo(videoNum) {
        const chapterNumber = currentChapterIdx + 1;
        window.location.href = `videos/${activeSubject}_chapter${chapterNumber}_video${videoNum}.mp4`;
    }
  </script>
</body>
</html>