<?php
session_start();
$chapter_id = isset($_GET['chapter']) ? htmlspecialchars($_GET['chapter']) : '1';
$subject_code = isset($_GET['subject']) ? htmlspecialchars($_GET['subject']) : '';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Video Link</title>
    <style>
        body { font-family: sans-serif; background-color: #f7ebeb; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .upload-container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); width: 400px; text-align: center; }
        input[type="text"] { width: 90%; padding: 10px; margin: 10px 0 20px 0; border: 1px solid #ccc; border-radius: 5px; }
        button { background-color: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; width: 100%; margin-bottom: 10px;}
        button:hover { background-color: #0056b3; }
        .cancel-btn { background-color: #6c757d; }
        .cancel-btn:hover { background-color: #5a6268; }
    </style>
</head>
<body>

    <div class="upload-container">
        <h2>Add Video for Chapter <?php echo $chapter_id; ?></h2>
        
        <form action="processVideo.php" method="POST">
            
            <input type="hidden" name="chapter_name" value="<?php echo $chapter_id; ?>">
            <input type="hidden" name="subject_code" value="<?php echo $subject_code; ?>">
            <input type="hidden" name="file_type" value="video">

            <label style="text-align: left; display: block; margin-left: 5%;">Video Title:</label>
            <input type="text" name="content_name" required>

            <label style="text-align: left; display: block; margin-left: 5%;">YouTube URL:</label>
            <input type="text" name="video_url" placeholder="e.g., https://www.youtube.com/watch?v=..." required>

            <button type="submit" name="uploadBtn">Save Video to Database</button>
<a href="editSubjectContent.php?chapter=<?php echo $chapter_id; ?>&subject=<?php echo $subject_code; ?>" style="text-decoration: none;">
                <button type="button" class="cancel-btn">Cancel</button>
            </a>
        </form>
    </div>

</body>
</html>