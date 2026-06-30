<?php
// --- DATABASE CONFIGURATION ---
$host = "100.81.48.34"; // Your Tailscale IP
$port = "3307";          
$dbname = "fictlp db";  
$username = "bubustailo"; 
$password = "Student@123";

// Initializing counts
$studentCount = 0;
$lecturerCount = 0;
$subjectCount = 0;

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 1. Fetch Students Count (Matched to your 'user' table layout)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM user WHERE Role = 'Student'");
    $stmt->execute();
    $studentCount = $stmt->fetchColumn();

    // 2. Fetch Lecturers Count (Assumed 'Lecturer' role configuration)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM user WHERE Role = 'Lecturer'");
    $stmt->execute();
    $lecturerCount = $stmt->fetchColumn();

    // 3. Fetch Course/Subject Count 
    // Note: Change 'course' to your exact table name if it differs (e.g., 'subject')
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM subject");
        $stmt->execute();
        $subjectCount = $stmt->fetchColumn();
    } catch (PDOException $e) {
        // Fallback placeholder value if your course table has a different name
        $subjectCount = 0; 
    }

} catch(PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CoreKnowledge - Dashboard Summary(Admin)</title>
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
            overflow-x: hidden;
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

        /* --- MAIN DASHBOARD CONTENT --- */
        .main-content {
            flex: 1;
            background-color: #fdf8f5; 
            padding: 40px 60px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            overflow-y: auto;
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
            margin-bottom: 35px;
        }

        /* Subject-Style Stacked Card Setup */
        .cards-stack {
            width: 100%;
            max-width: 900px;
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .subject-style-card {
            background-color: #ffffff;
            border-radius: 14px;
            overflow: hidden;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            width: 100%;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06), 0 4px 12px rgba(0, 0, 0, 0.03);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .subject-style-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 32px rgba(0, 0, 0, 0.1), 0 6px 16px rgba(0, 0, 0, 0.04);
        }

        .card-header {
            padding: 18px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #1a253c;
        }

        .card-header h2 {
            font-size: 22px;
            font-family: Georgia, serif;
            font-weight: bold;
        }

        .header-students { background-color: #eed6c5; }
        .header-lecturers { background-color: #c9e6f2; }
        .header-subjects { background-color: #cbe3cc; }

        .card-body {
            padding: 24px;
            background-color: #ffffff;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .details-list {
            display: flex;
            gap: 40px;
            list-style: none;
        }

        .details-list li {
            font-size: 15px;
            color: #555555;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .details-list li::before {
            content: "●";
            color: #9cbca4;
            font-size: 12px;
        }

        .metric-badge {
            font-size: 32px;
            font-weight: bold;
            color: #1a253c;
            background-color: #f7f7f7;
            padding: 6px 20px;
            border-radius: 20px;
            min-width: 65px;
            text-align: center;
        }

        @media (max-width: 768px) {
            body { overflow-y: auto; }
            .window-frame { flex-direction: column; height: auto; min-height: 100vh; }
            .sidebar {
                width: 100%; max-width: 100%; height: auto; flex-direction: row;
                justify-content: space-around; padding-top: 0; border-right: none;
                border-bottom: 1px solid #dcdcdc; position: sticky; top: 0; z-index: 100;
            }
            .sidebar nav { flex-direction: row; width: 100%; justify-content: space-around; }
            .nav-item { width: auto; height: 70px; padding: 5px 10px; flex: 1; }
            .main-content { padding: 30px 20px; align-items: center; height: auto; }
            .main-content h1 { font-size: 32px; text-align: center; }
            .main-content .subtitle { margin-bottom: 25px; text-align: center; }
            .cards-stack { gap: 20px; }
            .card-header { padding: 14px 20px; flex-direction: column; align-items: flex-start; gap: 4px; }
            .card-header h2 { font-size: 18px; }
            .card-body { padding: 20px; flex-direction: column-reverse; gap: 15px; align-items: center; text-align: center; }
            .details-list { flex-direction: column; gap: 10px; width: 100%; }
            .details-list li { justify-content: center; font-size: 14px; }
            .metric-badge { font-size: 28px; width: 100%; max-width: 120px; text-align: center; }
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

                <div class="nav-item active" data-page="Information">
                    <div class="icon">
                        <img src="Aset/summaryBtn.svg" alt="Summary">
                    </div>
                    <span class="label">Information</span>
                </div>
        
                <div class="nav-item" data-page="announcement" onclick="window.location.href='announcement.php'">
                    <div class="icon">
                        <img src="Aset/annoucment.svg" alt="Announcement">
                    </div>
                    <span class="label">Announcement</span>
                </div>
            </nav>
        </aside>

        <main class="main-content">
            <h1>Information Dashboard</h1>
            <div class="subtitle">System overview metrics:</div>

            <div class="cards-stack">
                
                <a href="manageStudents.php" class="subject-style-card">
                    <div class="card-header header-students">
                        <h2>Students List</h2>
                    </div>
                    <div class="card-body">
                        <ul class="details-list">
                            <li>Manage Students</li>
                        </ul>
                        <div class="metric-badge" id="student-count"><?php echo (int)$studentCount; ?></div>
                    </div>
                </a>

                <a href="manageLecturers.php" class="subject-style-card">
                    <div class="card-header header-lecturers">
                        <h2>Lecturer List</h2>
                    </div>
                    <div class="card-body">
                        <ul class="details-list">
                            <li>Manage Lecturers</li>
                        </ul>
                        <div class="metric-badge" id="lecturer-count"><?php echo (int)$lecturerCount; ?></div>
                    </div>
                </a>

                <a href="manageSubjects.php" class="subject-style-card">
                    <div class="card-header header-subjects">
                        <h2>Subjects List</h2>
                    </div>
                    <div class="card-body">
                        <ul class="details-list">
                            <li>Manage Subjects</li>
                        </ul>
                        <div class="metric-badge" id="subject-count"><?php echo (int)$subjectCount; ?></div>
                    </div>
                </a>

            </div>
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