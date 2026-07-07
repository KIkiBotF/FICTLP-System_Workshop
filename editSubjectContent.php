<?php
// 1. Define the chapter ID FIRST
$chapter_id = isset($_GET['chapter']) ? $_GET['chapter'] : '1';
$chapter_id = htmlspecialchars($chapter_id);

// 2. NOW connect to the database and run the query
$host = "100.81.48.34";
$port = "3307";
$dbname = "fictlp db";
$username = "bubustailo";
$password = "Student@123";
$conn = new mysqli($host, $username, $password, $dbname, $port);

$subject_code = isset($_GET['subject']) ? $_GET['subject'] : '';

$chapter_name_display = "CHAPTER " . $chapter_id; // Fallback just in case
$stmt_chap = $conn->prepare("SELECT Chapter_Name FROM chapter WHERE Chapter_ID = ?");
$stmt_chap->bind_param("s", $chapter_id);
$stmt_chap->execute();
$result_chap = $stmt_chap->get_result();

if ($row_chap = $result_chap->fetch_assoc()) {
    $chapter_name_display = $row_chap['Chapter_Name'];
    // If the database just has a number (like "1"), format it nicely
    if (is_numeric($chapter_name_display)) {
        $chapter_name_display = "CHAPTER " . $chapter_name_display;
    }
}
$stmt_chap->close();


$stmt = $conn->prepare("SELECT Content_ID, Name, File_Type, file_path FROM content WHERE Chapter_ID = ?");
$stmt->bind_param("s", $chapter_id);
$stmt->execute();
$result = $stmt->get_result();


// --- NEW LOGIC: Sort the data before the HTML starts ---
$slides_data = [];
$videos_data = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if ($row['File_Type'] === 'slide') {
            $slides_data[] = $row;
        } elseif ($row['File_Type'] === 'video') {
            $videos_data[] = $row;
        }
    }
}
$stmt->close();

$total_questions = 0; // Default to 0 if no quiz or questions exist

$quiz_count_stmt = $conn->prepare("
    SELECT COUNT(qq.Question_ID) AS total 
    FROM quiz_question qq
    JOIN quiz q ON qq.Quiz_ID = q.Quiz_ID
    WHERE q.Chapter_ID = ?
");

if ($quiz_count_stmt) {
    $quiz_count_stmt->bind_param("s", $chapter_id);
    $quiz_count_stmt->execute();
    $quiz_count_result = $quiz_count_stmt->get_result();
    
    if ($quiz_count_row = $quiz_count_result->fetch_assoc()) {
        $total_questions = $quiz_count_row['total'];
    }
    $quiz_count_stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Chapter Content - CoreKnowledge</title>
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="editSubjectContentStyle.css">
</head>

<body>

    <?php
    include("sidebar.php");
    $chapter_id = isset($_GET['chapter']) ? $_GET['chapter'] : '1';
    $chapter_id = htmlspecialchars($chapter_id);


    ?>

    <main class="main-content">

<div class="chapter-header-container">
<h1 class="chapter-title"><?php echo htmlspecialchars($chapter_name_display); ?></h1>
</div>

        <div class="content-grid">

            <div class="column">
                <h2 class="column-title">SLIDE</h2>

                <?php if (empty($slides_data)): ?>
                    <p style="text-align: center; color: #888; font-size: 16px; margin-bottom: 15px;">No slides uploaded yet.</p>
                <?php else: ?>

                    <?php foreach ($slides_data as $slide): ?>
                        <div class="content-item">
                            <h3 style="text-align: center; font-size: 16px; margin-bottom: 5px;">
                                <?php echo htmlspecialchars($slide['Name']); ?>
                            </h3>
                            <a href="<?php echo htmlspecialchars($slide['file_path']); ?>" target="_blank" style="text-decoration: none; color: inherit;">
                                <div class="media-box" style="overflow: hidden; padding: 0;">
                                    <iframe
                                        src="<?php echo htmlspecialchars($slide['file_path']); ?>#toolbar=0&navpanes=0&scrollbar=0"
                                        scrolling="no"
                                        style="width: 100%; height: 100%; border: none; pointer-events: none; overflow: hidden;">
                                    </iframe>
                                </div>
                            </a>
                            <div class="item-actions">
                                <!-- Your existing Edit and Remove buttons stay here -->
                                <a href="deleteContent.php?id=<?php echo $slide['Content_ID']; ?>&chapter=<?php echo $chapter_id; ?>&subject=<?php echo $subject_code; ?>" onclick="return confirm('Are you sure you want to delete this slide?');">
                                    <button class="action-btn remove-btn" type="button">Remove</button>
                                </a>
                                <a href="editContent.php?id=<?php echo $slide['Content_ID']; ?>&chapter=<?php echo $chapter_id; ?>&subject=<?php echo $subject_code; ?>" style="text-decoration: none;">
                                    <button class="action-btn edit-btn" type="button">Edit</button>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <a href="uploadSlide.php?chapter=<?php echo $chapter_id; ?>" style="text-decoration: none; display: block; width: 100%;">
                    <button class="add-box" type="button" style="cursor: pointer; width: 100%;">
                        <?php include('Aset/add_icon.svg'); ?>
                    </button>
                </a>
            </div>

            <div class="column">
                <h2 class="column-title">VIDEO</h2>

                <?php if (empty($videos_data)): ?>
                    <p style="text-align: center; color: #888; font-size: 16px; margin-bottom: 15px;">No videos uploaded yet.</p>
                <?php else: ?>
                    <?php foreach ($videos_data as $video): ?>
                        <div class="content-item">
                            <h3 style="text-align: center; font-size: 16px; margin-bottom: 5px;"><?php echo htmlspecialchars($video['Name']); ?></h3>

                            <a href="<?php echo htmlspecialchars($video['file_path']); ?>" target="_blank" style="text-decoration: none; color: inherit;">
                                <div class="media-box">
                                    <?php
                                    $video_url = $video['file_path'];
                                    $youtube_id = '';

                                    // A robust regex that catches standard, embed, and short (youtu.be) links
                                    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $video_url, $match)) {
                                        $youtube_id = $match[1];
                                    }

                                    if (!empty($youtube_id)) {
                                        // Using hqdefault.jpg instead of mqdefault for a slightly higher quality preview
                                        $thumbnail_url = "https://img.youtube.com/vi/" . $youtube_id . "/hqdefault.jpg";
                                        echo '<img src="' . htmlspecialchars($thumbnail_url) . '" alt="Video Thumbnail" style="width: 100%; height: 100%; object-fit: cover; border-radius: 15px;">';
                                    } else {
                                        // A much cleaner fallback if the URL is completely invalid
                                        echo '<div style="display: flex; flex-direction: column; align-items: center; color: #888;">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m22 8-6 4 6 4V8Z"/><rect x="2" y="6" width="14" height="12" rx="2" ry="2"/>
                </svg>
                <span style="font-size: 12px; margin-top: 5px;">Invalid Link</span>
            </div>';
                                    }
                                    ?>
                                </div>
                            </a>

                            <div class="item-actions">
                                <a href="deleteContent.php?id=<?php echo $video['Content_ID']; ?>&chapter=<?php echo $chapter_id; ?>&subject=<?php echo $subject_code; ?>" onclick="return confirm('Are you sure you want to delete this video link?');">
                                    <button class="action-btn remove-btn" type="button">Remove</button>
                                </a>
                                <a href="editContent.php?id=<?php echo $video['Content_ID']; ?>&chapter=<?php echo $chapter_id; ?>&subject=<?php echo $subject_code; ?>" style="text-decoration: none;">
                                    <button class="action-btn edit-btn" type="button">Edit</button>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <a href="uploadVideo.php?chapter=<?php echo $chapter_id; ?>&subject=<?php echo $subject_code; ?>" style="text-decoration: none; display: block; width: 100%;">
                    <button class="add-box" type="button" style="cursor: pointer; width: 100%;">
                        <?php include('Aset/add_icon.svg'); ?>
                    </button>
                </a>
            </div>

            <div class="column">
                <h2 class="column-title">QUIZ</h2>

                <p style="visibility: hidden; font-size: 16px; margin-bottom: 15px;">Spacer</p>

                <div class="content-item">
                    <div class="media-box quiz-box">
                        <span class="quiz-text"><?php echo htmlspecialchars($total_questions); ?> QUESTIONS</span>
                    </div>
                    <div class="item-actions center-actions">
                        <button class="action-btn edit-btn" onclick="window.location.href='editQuizLecturer.php'">Edit</button>
                    </div>
                </div>
            </div>

        </div>

    </main>
</body>

</html>