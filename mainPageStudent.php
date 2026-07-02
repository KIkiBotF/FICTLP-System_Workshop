<?php
// 1. MUST start the session at the very top to read session variables
session_start();

// 2. Security Check: If the user is not logged in or is not a Student, kick them back to login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Student') {
    header("Location: logIn.php");
    exit();
}

// 3. Store the session name in a variable (fallback to 'Guest' if empty)
$student_name = isset($_SESSION['name']) ? $_SESSION['name'] : 'Guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FICTLP-System</title>
    <link rel="stylesheet" href="mainPageStudent.css">
</head>
<body>

    <div class="logout-wrapper">
        <a href="index.php" class="logout-action">
            <img src="Aset/logOutBtn.svg" alt="Logout" class="icon-exit">
            <span class="logout-text">Log Out</span>
        </a>
    </div>

    <div class="center-content">
        
        <div class="grey-banner">
            <h1>FICTLP-System</h1>
        </div>

        <h2 class="msg-welcome">Welcome Back!</h2>
        
        <p class="msg-user"><?php echo htmlspecialchars($student_name); ?></p>

        <a href="SubjectStudent.php" class="btn-subject">Subject</a>

    </div>

</body>
</html>