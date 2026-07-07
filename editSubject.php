<?php
// Establish connection to grab the correct Subject Title
$conn = new mysqli("100.81.48.34", "bubustailo", "Student@123", "fictlp db", "3307");

// Grab the subject code from the URL
$subject_code = isset($_GET['subject']) ? $_GET['subject'] : 'DITP2913';
$subject_title = "Unknown Subject";
$chapters_data = []; // Initialize the array here

if ($conn->connect_error == false) {
    // 1. Fetch Subject Title
    $stmt = $conn->prepare("SELECT Title FROM subject WHERE Subject_Code = ?");
    if ($stmt) {
        $stmt->bind_param("s", $subject_code);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $subject_title = $row['Title'];
        }
        $stmt->close();
    }

    // 2. Fetch Chapters for this Subject (Fetching both ID and Name)
    $chapter_stmt = $conn->prepare("SELECT Chapter_ID, Chapter_Name FROM chapter WHERE Subject_Code = ? ORDER BY chapter_order ASC");

    if (!$chapter_stmt) {
        die("SQL Prepare Error (Chapters): " . $conn->error);
    }

    $chapter_stmt->bind_param("s", $subject_code);
    $chapter_stmt->execute();
    $chapter_result = $chapter_stmt->get_result();

    $chapters_data = []; // Store everything in this array
    while ($chapter_row = $chapter_result->fetch_assoc()) {
        $chapters_data[] = $chapter_row;
    }
    $chapter_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Subject CoreKnowledge</title>
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="editSubjectStyle.css">
</head>

<body>
    <?php include("sidebar.php"); ?>
    <main class="main-content">
        <div class="header-container">
            <h1><?php echo htmlspecialchars($subject_title); ?></h1>
        </div>

        <div class="chapter-list" style="display: flex; flex-direction: column; align-items: center; width: 100%;">
            <?php
            // Loop through the array we created at the top of the file
            foreach ($chapters_data as $chapter_row):
                $actual_chapter_id = $chapter_row['Chapter_ID'];
                $display_name = (is_numeric($chapter_row['Chapter_Name'])) ? "CHAPTER " . $chapter_row['Chapter_Name'] : $chapter_row['Chapter_Name'];
            ?>
                <!-- Unified Pill Container -->
                <div style="display: flex; align-items: center; background-color: #fff; border-radius: 50px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); width: 100%; max-width: 550px; margin-bottom: 15px; padding: 5px 20px; transition: transform 0.2s ease;">

                    <!-- Invisible spacer to keep text perfectly centered (balances the X on the right) -->
                    <div style="width: 30px;"></div>

                    <!-- Main Chapter Link -->
                    <a href="editSubjectContent.php?subject=<?php echo urlencode($subject_code); ?>&chapter=<?php echo $actual_chapter_id; ?>"
                        style="flex: 1; margin: 0; background: none; box-shadow: none; padding: 15px 0; text-decoration: none; color: #1a1a1a; font-weight: 800; font-size: 1.1rem; text-align: center;">
                        <?php echo htmlspecialchars($display_name); ?>
                    </a>

                    <!-- Integrated Delete Button (&times; creates a clean 'X' symbol) -->
                    <a href="processDeleteChapter.php?chapter=<?php echo $actual_chapter_id; ?>&subject=<?php echo urlencode($subject_code); ?>"
                        onclick="return confirm('Are you sure you want to delete this chapter?');"
                        style="color: #ff7675; text-decoration: none; font-weight: bold; font-size: 1.5rem; width: 30px; text-align: center;">
                        &times;
                    </a>
                </div>
            <?php endforeach; ?>

            <!-- Add Chapter Form (Width perfectly matched to max-width: 550px) -->
            <form action="processAddChapter.php" method="POST" style="margin-top: 20px; display: flex; gap: 10px; width: 100%; max-width: 550px;">
                <input type="hidden" name="subject_code" value="<?php echo htmlspecialchars($subject_code); ?>">

                <input type="text" name="chapter_name" placeholder="New Chapter Name..." required
                    style="flex: 1; padding: 15px 20px; border-radius: 50px; border: none; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); text-align: center; font-weight: bold; font-size: 1rem; outline: none; color: #1a1a1a;">

                <button type="submit"
                    style="padding: 0 30px; border-radius: 50px; background-color: #0984e3; color: white; border: none; cursor: pointer; font-weight: bold; font-size: 1rem; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                    + Add
                </button>
            </form>
        </div>
</body>

</html>