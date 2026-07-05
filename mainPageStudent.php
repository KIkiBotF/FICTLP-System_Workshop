<?php
// 1. MUST start the session at the very top to read session variables
session_start();

// FIX: tambah login check - sebelum ni page ni boleh diakses tanpa login
// langsung (cuma tunjuk 'Guest' je, tak redirect).
if (!isset($_SESSION['userID'])) {
    header("Location: index.php");
    exit();
}

// FIX: guna 'name' (bukan 'username') - konsisten dengan session key
// yang di-set dalam index.php.
$Name = isset($_SESSION['name']) ? $_SESSION['name'] : 'Guest';
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

        <p class="msg-user"><?php echo htmlspecialchars($Name); ?></p>

        <a href="SubjectStudent.php" class="btn-subject">Subject</a>

    </div>

</body>
</html>