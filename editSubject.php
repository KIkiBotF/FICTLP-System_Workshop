<?php 
// Establish connection to grab the correct Subject Title
$conn = new mysqli("100.81.48.34", "bubustailo", "Student@123", "fictlp db", "3307");

// Grab the subject code from the URL
$subject_code = isset($_GET['subject']) ? $_GET['subject'] : 'DITP2913';
$subject_title = "Unknown Subject";

if ($conn->connect_error == false) {
    $stmt = $conn->prepare("SELECT Title FROM subject WHERE Subject_Code = ?");
    $stmt->bind_param("s", $subject_code);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $subject_title = $row['Title'];
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Subject - CoreKnowledge</title>
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="editSubjectStyle.css">
</head>
<body>
    <?php include("sidebar.php"); ?>
    <main class="main-content">
        
        <div class="header-container">
            <h1><?php echo htmlspecialchars($subject_title); ?></h1>
        </div>
        
        <div class="chapter-list">
            <a href="editSubjectContent.php?subject=<?php echo urlencode($subject_code); ?>&chapter=1" class="chapter-btn">CHAPTER 1</a>
            <a href="editSubjectContent.php?subject=<?php echo urlencode($subject_code); ?>&chapter=2" class="chapter-btn">CHAPTER 2</a>
            <a href="editSubjectContent.php?subject=<?php echo urlencode($subject_code); ?>&chapter=3" class="chapter-btn">CHAPTER 3</a>
        </div>
        
    </main>
</body>
</html>