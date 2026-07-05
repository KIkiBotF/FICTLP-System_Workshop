<?php
// 1. Mulakan session di baris paling pertama
session_start();

// Semak jika user sudah login, jika belum tendang ke login
if (!isset($_SESSION['userID'])) {
    header("Location: index.php");
    exit();
}

$current_user = $_SESSION['userID'];

$host = "100.81.48.34";
$port = "3307";
$dbname = "fictlp db";
$username = "bubustailo";
$password = "Student@123";

$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$subjects = [
    'DITP1113' => 'C++ Programming',
    'DITS1133' => 'Computer Organization and Architecture',
    'DITP2913' => 'Database',
];

$unlockStatus = [];
foreach ($subjects as $code => $name) {
    $stmt = $conn->prepare("SELECT Achievement_ID FROM achievement WHERE userID = ? AND Subject_Code = ?");
    $stmt->bind_param("ss", $current_user, $code);
    $stmt->execute();
    $unlockStatus[$code] = $stmt->get_result()->num_rows > 0;
    $stmt->close();
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
            opacity: 0.5;
            cursor: not-allowed;
            position: relative;
        }

        .cert-card-wrapper.locked .cert-frame:hover {
            transform: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        }

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
            <?php foreach ($subjects as $code => $name): ?>
                <?php $isUnlocked = $unlockStatus[$code]; ?>
                <div class="cert-card-wrapper <?php echo !$isUnlocked ? 'locked' : ''; ?>">
                    <?php if (!$isUnlocked): ?>
                        <div class="lock-overlay">🔒 Locked</div>
                        <div class="cert-frame">
                    <?php else: ?>
                        <div class="unlocked-badge">✅ Unlocked</div>
                        <a href="CertificateView.php?subject_code=<?php echo urlencode($code); ?>" class="cert-thumbnail-link">
                        <div class="cert-frame">
                    <?php endif; ?>
                            <img src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='280' height='200' viewBox='0 0 280 200'><rect width='280' height='200' fill='white'/><rect x='10' y='10' width='260' height='180' fill='none' stroke='%23d4af37' stroke-width='3'/><text x='140' y='50' font-family='serif' font-size='11' text-anchor='middle' font-weight='bold' fill='%23222'>CERTIFICATE OF COMPLETION COURSE</text><text x='140' y='95' font-family='sans-serif' font-size='14' text-anchor='middle' fill='%23444'><?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Student'; ?></text><text x='140' y='130' font-family='sans-serif' font-size='10' text-anchor='middle' font-style='italic' fill='%23666'>has successfully completed 100% of</text><text x='140' y='145' font-family='sans-serif' font-size='11' text-anchor='middle' font-weight='bold' fill='%235c6bcb'><?php echo htmlspecialchars($name); ?></text><path d='M15,15 L45,15 L15,45 Z' fill='%230d47a1'/><path d='M265,15 L235,15 L265,45 Z' fill='%230d47a1'/></svg>" alt="<?php echo htmlspecialchars($name); ?> Certificate Mockup">
                        </div>
                    <?php if ($isUnlocked): ?></a><?php endif; ?>
                    <p class="cert-title"><?php echo htmlspecialchars($name); ?> Certificate</p>
                </div>
            <?php endforeach; ?>
        </section>
    </main>
</body>
</html>