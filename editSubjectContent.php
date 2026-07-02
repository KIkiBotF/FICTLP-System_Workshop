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


$stmt = $conn->prepare("SELECT Content_ID, Name, File_Type, file_path FROM content WHERE Chapter_Name = ? AND Subject_Code = ?");
$stmt->bind_param("ss", $chapter_id, $subject_code);
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
            <h1 class="chapter-title">CHAPTER <?php echo $chapter_id; ?></h1>
        </div>

        <div class="content-grid">

            <div class="column">
                <h2 class="column-title">SLIDE</h2>

                <?php if (empty($slides_data)): ?>
                    <p style="text-align: center; color: #888; font-size: 16px; margin-bottom: 15px;">No slides uploaded yet.</p>
                <?php else: ?>
                    <?php foreach ($slides_data as $slide): ?>

                        <div class="content-item">
                            <h3 style="text-align: center; font-size: 16px; margin-bottom: 5px;"><?php echo htmlspecialchars($slide['Name']); ?></h3>

                            <a href="<?php echo htmlspecialchars($slide['file_path']); ?>" target="_blank" style="text-decoration: none; color: inherit;">
                                <div class="media-box">
                                    <h3>PDF</h3>
                                </div>
                            </a>

                            <div class="item-actions">
                                <a href="deleteContent.php?id=<?php echo $slide['Content_ID']; ?>&chapter=<?php echo $chapter_id; ?>&subject=<?php echo $subject_code; ?>" onclick="return confirm('Are you sure you want to delete this slide?');">
                                    <button class="action-btn remove-btn" type="button">Remove</button>
                                </a>

                                <a href="editContent.php?id=<?php echo $slide['Content_ID']; ?>&chapter=<?php echo $chapter_id; ?>&subject=<?php echo $subject_code; ?>" style="text-decoration: none;">
                                    <button class="action-btn edit-btn" type="button">Edit</button>
                                </a>

                                <a href="uploadSlide.php?chapter=<?php echo $chapter_id; ?>&subject=<?php echo $subject_code; ?>" style="text-decoration: none; display: block; width: 100%;">
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
                                    <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#888" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polygon points="23 7 16 12 23 17 23 7"></polygon>
                                        <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
                                    </svg>
                                </div>
                            </a>

                            <div class="item-actions">
                                <a href="deleteContent.php?id=<?php echo $video['Content_ID']; ?>&chapter=<?php echo $chapter_id; ?>&subject=<?php echo $subject_code; ?>" onclick="return confirm('Are you sure you want to delete this video link?');">
                                    <button class="action-btn remove-btn" type="button">Remove</button>
                                </a>

                                <a href="editContent.php?id=<?php echo $video['Content_ID']; ?>&chapter=<?php echo $chapter_id; ?>&subject=<?php echo $subject_code; ?>" style="text-decoration: none;">
                                    <button class="action-btn edit-btn" type="button">Edit</button>
                                </a>

                                <a href="uploadVideo.php?chapter=<?php echo $chapter_id; ?>&subject=<?php echo $subject_code; ?>" style="text-decoration: none; display: block; width: 100%;">
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
                        <span class="quiz-text">10 QUESTIONS</span>
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