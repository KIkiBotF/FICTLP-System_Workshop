<?php
// 1. Mulakan session di baris paling pertama
session_start();

// Semak jika user sudah login, jika belum tendang ke login
if (!isset($_SESSION['userID'])) {
    header("Location: index.php");
    exit();
}

$current_user = $_SESSION['userID'];

// 2. Sambungan ke Database (Mengikut tetapan index.php anda)
$host = "100.81.48.34";
$port = "3307";          
$dbname = "fictlp db";  
$username = "bubustailo"; 
$password = "Student@123";

$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) { 
    die("Connection failed: " . $conn->connect_error); 
}

// 3. SEMAK STATUS LOCK/UNLOCK SECARA TERUS (Tanpa Function)

// Semak untuk C++ (CPP101)
$is_cpp_unlocked = false;
$stmt1 = $conn->prepare("SELECT Achievement_ID FROM achievement WHERE userID = ? AND Subject_Code = 'CPP101'");
if ($stmt1) {
    $stmt1->bind_param("s", $current_user);
    $stmt1->execute();
    $is_cpp_unlocked = $stmt1->get_result()->num_rows > 0;
    $stmt1->close();
}

// Semak untuk COA (COA202)
$is_coa_unlocked = false;
$stmt2 = $conn->prepare("SELECT Achievement_ID FROM achievement WHERE userID = ? AND Subject_Code = 'COA202'");
if ($stmt2) {
    $stmt2->bind_param("s", $current_user);
    $stmt2->execute();
    $is_coa_unlocked = $stmt2->get_result()->num_rows > 0;
    $stmt2->close();
}

// Semak untuk Database (DB303)
$is_db_unlocked = false;
$stmt3 = $conn->prepare("SELECT Achievement_ID FROM achievement WHERE userID = ? AND Subject_Code = 'DB303'");
if ($stmt3) {
    $stmt3->bind_param("s", $current_user);
    $stmt3->execute();
    $is_db_unlocked = $stmt3->get_result()->num_rows > 0;
    $stmt3->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Achievements - Student LMS</title>
    <link rel="stylesheet" href="AchievementStudent.css">
    <style>
        /* CSS Tambahan untuk kesan "Locked" */
        .cert-card-wrapper.locked {
            opacity: 0.5; /* Kaburkan kad */
            cursor: not-allowed;
            position: relative;
        }

        .cert-card-wrapper.locked .cert-frame:hover {
            transform: none; /* Matikan kesan float hover jika locked */
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        }

        /* Lencana Mangga Lock */
        .lock-overlay {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.7);
            color: #fff;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            z-index: 10;
        }
        
        .unlocked-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: #fff;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            z-index: 10;
        }
    </style>
</head>
<body>
    <?php include("sidebarStudent.php"); ?>
    <main class="main-content">
        <header class="content-header">
            <h1>Achievement</h1>
            <p class="congrats-msg">Congratulation, <strong><?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Student'; ?></strong> !</p>
        </header>

        <section class="certificates-grid">
            
            <div class="cert-card-wrapper <?php echo !$is_cpp_unlocked ? 'locked' : ''; ?>">
                <?php if (!$is_cpp_unlocked): ?>
                    <div class="lock-overlay">🔒 Locked</div>
                    <div class="cert-frame">
                <?php else: ?>
                    <div class="unlocked-badge">✅ Unlocked</div>
                    <a href="certificate-view.php?subject_code=CPP101" class="cert-thumbnail-link">
                    <div class="cert-frame">
                <?php endif; ?>
                        <img src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='280' height='200' viewBox='0 0 280 200'><rect width='280' height='200' fill='white'/><rect x='10' y='10' width='260' height='180' fill='none' stroke='%23d4af37' stroke-width='3'/><text x='140' y='50' font-family='serif' font-size='11' text-anchor='middle' font-weight='bold' fill='%23222'>CERTIFICATE OF COMPLETION COURSE</text><text x='140' y='95' font-family='sans-serif' font-size='14' text-anchor='middle' fill='%23444'><?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Student'; ?></text><text x='140' y='130' font-family='sans-serif' font-size='10' text-anchor='middle' font-style='italic' fill='%23666'>has successfully completed 100% of</text><text x='140' y='145' font-family='sans-serif' font-size='11' text-anchor='middle' font-weight='bold' fill='%235c6bcb'>C++ Programming</text><path d='M15,15 L45,15 L15,45 Z' fill='%230d47a1'/><path d='M265,15 L235,15 L265,45 Z' fill='%230d47a1'/></svg>" alt="C++ Certificate Mockup">
                    </div>
                <?php if ($is_cpp_unlocked): ?></a><?php endif; ?>
                <p class="cert-title">C++ Programming Certificate</p>
            </div>

            <div class="cert-card-wrapper <?php echo !$is_coa_unlocked ? 'locked' : ''; ?>">
                <?php if (!$is_coa_unlocked): ?>
                    <div class="lock-overlay">🔒 Locked</div>
                    <div class="cert-frame">
                <?php else: ?>
                    <div class="unlocked-badge">✅ Unlocked</div>
                    <a href="certificate-view.php?subject_code=COA202" class="cert-thumbnail-link">
                    <div class="cert-frame">
                <?php endif; ?>
                        <img src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='280' height='200' viewBox='0 0 280 200'><rect width='280' height='200' fill='white'/><rect x='10' y='10' width='260' height='180' fill='none' stroke='%23d4af37' stroke-width='3'/><text x='140' y='50' font-family='serif' font-size='11' text-anchor='middle' font-weight='bold' fill='%23222'>CERTIFICATE OF COMPLETION COURSE</text><text x='140' y='95' font-family='sans-serif' font-size='14' text-anchor='middle' fill='%23444'><?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Student'; ?></text><text x='140' y='130' font-family='sans-serif' font-size='10' text-anchor='middle' font-style='italic' fill='%23666'>has successfully completed 100% of</text><text x='140' y='145' font-family='sans-serif' font-size='11' text-anchor='middle' font-weight='bold' fill='%235c6bcb'>Computer Organization and Architecture</text><path d='M15,15 L45,15 L15,45 Z' fill='%230d47a1'/><path d='M265,15 L235,15 L265,45 Z' fill='%230d47a1'/></svg>" alt="COA Certificate Mockup">
                    </div>
                <?php if ($is_coa_unlocked): ?></a><?php endif; ?>
                 <p class="cert-title">Computer Organization and Architecture Certificate</p>
            </div>

            <div class="cert-card-wrapper <?php echo !$is_db_unlocked ? 'locked' : ''; ?>">
                <?php if (!$is_db_unlocked): ?>
                    <div class="lock-overlay">🔒 Locked</div>
                    <div class="cert-frame">
                <?php else: ?>
                    <div class="unlocked-badge">✅ Unlocked</div>
                    <a href="certificate-view.php?subject_code=DB303" class="cert-thumbnail-link">
                    <div class="cert-frame">
                <?php endif; ?>
                        <img src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='280' height='200' viewBox='0 0 280 200'><rect width='280' height='200' fill='white'/><rect x='10' y='10' width='260' height='180' fill='none' stroke='%23d4af37' stroke-width='3'/><text x='140' y='50' font-family='serif' font-size='11' text-anchor='middle' font-weight='bold' fill='%23222'>CERTIFICATE OF COMPLETION COURSE</text><text x='140' y='95' font-family='sans-serif' font-size='14' text-anchor='middle' fill='%23444'><?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Student'; ?></text><text x='140' y='130' font-family='sans-serif' font-size='10' text-anchor='middle' font-style='italic' fill='%23666'>has successfully completed 100% of</text><text x='140' y='145' font-family='sans-serif' font-size='11' text-anchor='middle' font-weight='bold' fill='%235c6bcb'>Database</text><path d='M15,15 L45,15 L15,45 Z' fill='%230d47a1'/><path d='M265,15 L235,15 L265,45 Z' fill='%230d47a1'/></svg>" alt="Database Certificate Mockup">
                    </div>
                <?php if ($is_db_unlocked): ?></a><?php endif; ?>
                <p class="cert-title">Database Certificate</p>
            </div>
        </section>
    </main>
</body>
</html>