<?php
session_start();

// Define the file where the global announcement will be saved
$announcementFile = 'announcement.txt';

// Handle form submission to update the announcement
$successMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['announcementText'])) {
    $message = trim($_POST['announcementText']);
    if (!empty($message)) {
        file_put_contents($announcementFile, $message);
        $successMessage = "Success! The announcement has been updated.";
    }
}

// Read current announcement or set a fallback default
$currentAnnouncement = file_exists($announcementFile) ? file_get_contents($announcementFile) : "Welcome to our website";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CoreKnowledge - Global Announcement(Admin)</title>
    <link rel="stylesheet" href="annoucementAdmin.css">
</head>
<body>

    <div class="window-frame">
        <?php include("sidebarAdmin.php") ?>
        <main class="main-content">
            <h1>Global Announcement</h1>
            <div class="subtitle">Broadcast important system-wide headers, maintenance alerts, or welcoming updates here:</div>

            <?php if (!empty($successMessage)): ?>
                <div class="alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
            <?php endif; ?>

            <form action="announcement.php" method="POST" class="announcement-box">
                <div class="input-container">
                    <textarea
                        class="announcement-input" 
                        name="announcementText" 
                        id="announcementText" 
                        placeholder="Type your system message here..."><?php echo htmlspecialchars($currentAnnouncement); ?></textarea>
                </div>
                
                <div class="action-row">
                    <button type="submit" class="btn-send">
                        <span class="icon-send-arrow">➔</span> Send to All Users
                    </button>
                </div>
            </form>
        </main>

    </div>

    <script>
        function logout() {
            if (confirm("Are you sure you want to logout?")) {
                window.location.href = "logIn.html";
            }
        }
    </script>
</body>
</html>