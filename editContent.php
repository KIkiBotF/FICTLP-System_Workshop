<?php
session_start();

$content_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$chapter_id = isset($_GET['chapter']) ? htmlspecialchars($_GET['chapter']) : '1';
$subject_code = isset($_GET['subject']) ? htmlspecialchars($_GET['subject']) : '';


// Redirect back if no ID is provided
if ($content_id === 0) {
    header("Location: editSubjectContent.php?chapter=" . $chapter_id);
    exit();
}

$conn = new mysqli("100.81.48.34", "bubustailo", "Student@123", "fictlp db", "3307");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// Fetch the current name of the slide
$stmt = $conn->prepare("SELECT Name FROM content WHERE Content_ID = ?");
$stmt->bind_param("i", $content_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $current_name = $row['Name'];
} else {
    die("Content not found.");
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Slide Name</title>
    <style>
        body { font-family: sans-serif; background-color: #f7ebeb; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .edit-container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); width: 400px; text-align: center; }
        input[type="text"] { width: 90%; padding: 10px; margin: 10px 0 20px 0; border: 1px solid #ccc; border-radius: 5px; }
        button { background-color: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; width: 100%; margin-bottom: 10px; }
        button:hover { background-color: #0056b3; }
        .cancel-btn { background-color: #6c757d; }
        .cancel-btn:hover { background-color: #5a6268; }
    </style>
</head>
<body>

    <div class="edit-container">
        <h2>Edit Slide Title</h2>
        
        <form action="processEdit.php" method="POST">
            <input type="hidden" name="content_id" value="<?php echo $content_id; ?>">
            <input type="hidden" name="chapter_name" value="<?php echo $chapter_id; ?>">
            <input type="hidden" name="subject_code" value="<?php echo $subject_code; ?>">

            <label style="text-align: left; display: block; margin-left: 5%;">Slide Title:</label>
            <input type="text" name="new_name" value="<?php echo htmlspecialchars($current_name); ?>" required>

            <button type="submit" name="editBtn">Save Changes</button>
            <a href="editSubjectContent.php?chapter=<?php echo $chapter_id; ?>&subject=<?php echo $subject_code; ?>" style="text-decoration: none;">
                <button type="button" class="cancel-btn">Cancel</button>
            </a>
        </form>
    </div>

</body>
</html>