<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Sambungan database (contoh)
$host = "100.81.48.34";
$port = "3307";          
$dbname = "fictlp db";  
$username = "bubustailo"; 
$password = "Student@123";

$conn = new mysqli($host, $username, $password, $dbname, $port);

$sql = "SELECT * FROM `content` WHERE `Subject_Code` = 'DITP2913' AND `Chapter_Name` = '1'";
$result = $conn->query($sql);



$contents = [];
while($row = $result->fetch_assoc()) {
    $contents[] = $row;
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
            <button class="btn-quiz" onclick="startQuiz()">Start Quiz</button>
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
    const chapterNumber = currentChapterIdx + 1; // Contoh: 1, 2, 3
    // Hantar parameter 'subject' dan 'chapter' ke fail php baharu
    window.location.href = `quizStudent.php?subject=${activeSubject}&chapter=${chapterNumber}`; 
}

    
  </script>
</body>
</html>