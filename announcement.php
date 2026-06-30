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
    <style>
        /* Reset and Full Screen Layout Base */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #ffffff;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            display: flex;
        }

        /* App container takes up 100% of the screen space */
        .window-frame {
            width: 100%;
            height: 100%;
            background-color: #ffffff;
            display: flex;
            overflow: hidden;
        }

        /* --- SIDEBAR NAVIGATION CORE --- */
        .sidebar {
            width: 15%;
            min-width: 120px;
            max-width: 160px;
            background-color: #ffffff;
            border-right: 1px solid #dcdcdc;
            display: flex;
            flex-direction: column;
            padding-top: 20px;
            align-items: center;
            flex-shrink: 0;
            height: 100%;
        }

        .sidebar nav {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .nav-item {
            width: 100%;
            height: 85px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 10px 5px;
            cursor: pointer;
            text-decoration: none;
            color: #000000;
            background: none;
            border: none;
            text-align: center;
            transition: background-color 0.15s ease;
        }

        .nav-item.active {
            background-color: #d9d9d9;
        }

        .nav-item:hover:not(.active) {
            background-color: #f5f5f5;
        }

        .icon {
            width: 24px;
            height: 24px;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            flex-shrink: 0;
        }

        .icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .nav-item .label {
            font-size: 11px;
            font-weight: bold;
            line-height: 1.2;
            display: block;
            width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* --- MAIN DASHBOARD CONTENT AREA --- */
        .main-content {
            flex: 1;
            background-color: #fdf8f5;
            padding: 40px 60px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            overflow: hidden;
            height: 100%;
        }

        .main-content h1 {
            font-size: 42px;
            font-family: Georgia, serif;
            font-weight: bold;
            color: #1a253c;
            margin-bottom: 4px;
        }

        .main-content .subtitle {
            font-size: 14px;
            color: #d88267;
            margin-bottom: 30px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            width: 100%;
            max-width: 680px;
            font-size: 14px;
            font-weight: 500;
            border: 1px solid #c3e6cb;
        }

        /* --- ANNOUNCEMENT CONTAINER MODULE --- */
        .announcement-box {
            background-color: #e9ecef;
            width: 100%;
            max-width: 680px;
            height: 310px;
            border-radius: 16px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08), 
                        0 4px 14px rgba(0, 0, 0, 0.04);
            border: 1px solid #ced4da;
        }
        
        .announcement-box:focus-within {
            border-color: #adb5bd;
            background-color: #e2e6ea;
            box-shadow: 0 14px 35px rgba(0, 0, 0, 0.1), 
                        0 4px 16px rgba(0, 0, 0, 0.05);
        }

        .input-container {
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 16px;
            height: 190px;
            width: 100%;
        }

        .announcement-input {
            width: 100%;
            height: 100%;
            background: transparent;
            border: none;
            outline: none;
            resize: none;
            color: #212529;
            font-family: inherit;
            font-size: 16px;
            font-weight: 500;
            line-height: 1.5;
        }
        
        .announcement-input::placeholder {
            color: #868e96;
            font-weight: 400;
        }

        .action-row {
            display: flex;
            justify-content: flex-end;
            padding-top: 12px;
        }

        .btn-send {
            background-color: #cbe3cc;
            color: #234e25;
            font-family: inherit;
            font-size: 13px;
            font-weight: bold;
            border: none;
            border-radius: 20px;
            padding: 10px 24px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            transition: background-color 0.2s ease, transform 0.1s, box-shadow 0.2s;
        }

        .btn-send:hover {
            background-color: #b9d7ba;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }

        .btn-send:active {
            transform: scale(0.97);
        }

        .icon-send-arrow {
            font-size: 11px;
            transform: rotate(-15deg);
            display: inline-block;
        }

        @media (max-width: 768px) {
            body { overflow-y: auto; }
            .window-frame { flex-direction: column; height: auto; min-height: 100vh; }
            .sidebar {
                width: 100%; max-width: 100%; height: auto; flex-direction: row;
                justify-content: space-around; padding-top: 0; border-right: none;
                border-bottom: 1px solid #dcdcdc; position: sticky; top: 0; z-index: 100;
            }
            .sidebar nav {
                flex-direction: row; width: 100%; justify-content: space-around;
            }
            .nav-item { width: auto; height: 70px; padding: 5px 10px; flex: 1; }
            .main-content { padding: 30px 20px; height: auto; }
            .main-content h1 { font-size: 32px; }
            .announcement-box { height: 270px; padding: 16px; }
            .input-container { height: 160px; }
            .announcement-input { font-size: 14px; }
        }
    </style>
</head>
<body>

    <div class="window-frame">
        
        <aside class="sidebar">
            <nav>
                <div class="nav-item" data-page="logout" onclick="logout()">
                    <div class="icon">
                        <img src="Aset/logOutBtn.svg" alt="Logout">
                    </div>
                    <span class="label">Log Out</span>
                </div>
           
                <div class="nav-item" data-page="home" onclick="window.location.href='mainPage.php'">
                    <div class="icon">
                        <img src="Aset/homeBtn.svg" alt="Home">
                    </div>
                    <span class="label">Home</span>
                </div>

                <div class="nav-item" data-page="dashboard" onclick="window.location.href='dashboard.php'">
                    <div class="icon">
                        <img src="Aset/summaryBtn.svg" alt="Summary">
                    </div>
                    <span class="label">Information</span>
                </div>
        
                <div class="nav-item active" data-page="announcement">
                    <div class="icon">
                        <img src="Aset/annoucment.svg" alt="Announcement">
                    </div>
                    <span class="label">Announcement</span>
                </div>
            </nav>
        </aside>

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