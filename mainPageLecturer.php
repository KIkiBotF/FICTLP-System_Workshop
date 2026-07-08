<?php
session_start();

if (!isset($_SESSION['userID'])) {
    header("Location: index.php");
    exit();
}

// === UPDATE USER STATUS TO ACTIVE (1) ===
$host = "100.81.48.34";
$port = "3307";
$dbname = "fictlp db";
$username = "bubustailo";
$db_password = "Student@123";

$conn = new mysqli($host, $username, $db_password, $dbname, $port);
if (!$conn->connect_error) {
    $statusStmt = $conn->prepare("UPDATE user SET user_status = 1 WHERE userID = ?");
    $statusStmt->bind_param("s", $_SESSION['userID']);
    $statusStmt->execute();
    $statusStmt->close();
    $conn->close();
}
// ========================================

if (file_exists('announcement_data.php')) {
    include('announcement_data.php');
    if (!empty($currentAnnouncement)) {
?>
    <div class="global-announcement-banner" style="
        position: absolute;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 9999;
        background-color: #fff3cd; 
        color: #856404; 
        border: 1px solid #ffeeba; 
        padding: 12px 24px; 
        border-radius: 30px; 
        display: flex; 
        align-items: center; 
        justify-content: center;
        font-weight: 500; 
        font-family: sans-serif;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        white-space: nowrap;
        max-width: 90%;
    ">
        <span style="font-size: 18px; margin-right: 10px; display: inline-block; vertical-align: middle;">📢</span>
        <div style="display: inline-block; vertical-align: middle;">
            <strong style="color: #533f03;">System Announcement:</strong> 
            <?php echo htmlspecialchars(stripslashes($currentAnnouncement)); ?>
        </div>
    </div>
<?php 
    }
} 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Page</title>
    <link rel="stylesheet" href="mainPageLecturer.css">
</head>
<body>
     <div class="logout-wrapper">
        <a href="logout.php" class="logout-action">
            <img src="Aset/logOutBtn.svg" alt="Logout" class="icon-exit">
            <span class="logout-text">Log Out</span>
        </a>
    </div>

    <div class="center-content">
        
        <div class="grey-banner">
            <h1>FICTLP-System</h1>
        </div>

        <h2 class="msg-welcome">Welcome Back!</h2>
        <p class="msg-user"><?php echo htmlspecialchars($_SESSION['name']); ?></p>

        <a href="manageSubjectLecturer.php" class="btn-subject">Subject</a>

    </div>

</body>
</html>