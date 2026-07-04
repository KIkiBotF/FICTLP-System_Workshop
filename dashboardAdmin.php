<?php
$host = "100.81.48.34"; 
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
    
    $stmt = $conn->prepare("SELECT COUNT(*) FROM user WHERE Role = 'Student'");
    $stmt->execute();
    $studentCount = $stmt->fetchColumn();

    $stmt = $conn->prepare("SELECT COUNT(*) FROM user WHERE Role = 'Lecturer'");
    $stmt->execute();
    $lecturerCount = $stmt->fetchColumn();

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
    <link rel="stylesheet" href="dashboardAdmin.css">
</head>
<body>

    <div class="window-frame">
        <?php include("sidebarAdmin.php") ?>
        <main class="main-content">
            <h1>Information Dashboard</h1>
            <div class="subtitle">System overview metrics:</div>

            <div class="cards-stack">
                
                <a href="manageStudentsAdmin.php" class="subject-style-card">
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

                <a href="manageLecturersAdmin.php" class="subject-style-card">
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

                <a href="manageSubjectsAdmin.php" class="subject-style-card">
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