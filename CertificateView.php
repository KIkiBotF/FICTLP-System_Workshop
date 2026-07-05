<?php
session_start();

if (!isset($_SESSION['userID'])) {
    header("Location: index.php");
    exit();
}

$current_user = $_SESSION['userID'];
$studentName = isset($_SESSION['name']) ? $_SESSION['name'] : 'Student';

$host = "100.81.48.34";
$port = "3307";
$dbname = "fictlp db";
$username = "bubustailo";
$password = "Student@123";

$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// FIX KRITIKAL: guna Subject_Code SEBENAR (DITP1113/DITP2913/DITS1133)
// - sama dengan yang di-insert oleh SubmitQuiz.php, bukan kod custom
// CPP101/COA202/DB303 yang tak wujud dalam table `subject`.
$subjectDisplayNames = [
    'DITP1113' => 'C++ Programming',
    'DITS1133' => 'Computer Organization and Architecture',
    'DITP2913' => 'Database',
];

$subjectCode = isset($_GET['subject_code']) ? trim($_GET['subject_code']) : '';

if (!array_key_exists($subjectCode, $subjectDisplayNames)) {
    die("Sijil tidak sah.");
}

// SAHKAN student ini MEMANG telah unlock achievement untuk subjek ini
$stmt = $conn->prepare("SELECT * FROM achievement WHERE userID = ? AND Subject_Code = ?");
$stmt->bind_param("ss", $current_user, $subjectCode);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: AchievementStudent.php");
    exit();
}

$achievementRow = $result->fetch_assoc();
$stmt->close();

$completionDate = !empty($achievementRow['Date_Issued'])
    ? date("d F Y", strtotime($achievementRow['Date_Issued']))
    : date("d F Y");

$subjectFullName = $subjectDisplayNames[$subjectCode];
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate - <?php echo htmlspecialchars($subjectFullName); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold: #d4af37;
            --navy: #0d47a1;
            --text-dark: #222;
            --text-muted: #555;
            --bg: #fcf0f0;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .toolbar {
            width: 100%;
            max-width: 900px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .btn {
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            font-weight: 600;
            padding: 10px 22px;
            border-radius: 30px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .btn:hover { transform: translateY(-2px); }

        .btn-back {
            background: #fff;
            color: var(--text-dark);
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .btn-print {
            background: var(--navy);
            color: #fff;
            box-shadow: 0 4px 14px rgba(13,71,161,0.3);
        }

        .certificate {
            background: #fff;
            width: 100%;
            max-width: 900px;
            aspect-ratio: 1.414 / 1;
            padding: 50px 60px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .certificate::before {
            content: "";
            position: absolute;
            inset: 18px;
            border: 3px solid var(--gold);
            border-radius: 6px;
            pointer-events: none;
        }

        .corner {
            position: absolute;
            width: 60px;
            height: 60px;
            background: var(--navy);
        }
        .corner.tl { top: 0; left: 0; clip-path: polygon(0 0, 100% 0, 0 100%); }
        .corner.tr { top: 0; right: 0; clip-path: polygon(100% 0, 100% 100%, 0 0); }
        .corner.bl { bottom: 0; left: 0; clip-path: polygon(0 100%, 0 0, 100% 100%); }
        .corner.br { bottom: 0; right: 0; clip-path: polygon(100% 100%, 0 100%, 100% 0); }

        .cert-heading {
            font-family: 'Playfair Display', serif;
            font-size: 18px;
            letter-spacing: 3px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 6px;
        }

        .cert-subheading {
            font-size: 13px;
            letter-spacing: 2px;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-bottom: 36px;
        }

        .cert-presented-to {
            font-size: 13px;
            color: var(--text-muted);
            font-style: italic;
            margin-bottom: 10px;
        }

        .student-name {
            font-family: 'Playfair Display', serif;
            font-size: 42px;
            font-weight: 900;
            color: var(--navy);
            margin-bottom: 20px;
            border-bottom: 2px solid var(--gold);
            padding-bottom: 14px;
            display: inline-block;
        }

        .cert-body-text {
            font-size: 14px;
            color: var(--text-muted);
            max-width: 520px;
            line-height: 1.6;
            margin-bottom: 6px;
        }

        .subject-name {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            font-weight: 700;
            color: #5c6bcb;
            margin: 14px 0 30px;
        }

        .cert-footer {
            display: flex;
            justify-content: space-between;
            width: 100%;
            max-width: 560px;
            margin-top: auto;
        }

        .cert-footer-item {
            text-align: center;
        }

        .cert-footer-item .line {
            width: 160px;
            border-top: 1.5px solid #999;
            margin-bottom: 6px;
        }

        .cert-footer-item .label {
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .cert-footer-item .value {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-dark);
        }

        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none; }
            .certificate { box-shadow: none; max-width: 100%; }
        }

        @media (max-width: 600px) {
            .student-name { font-size: 28px; }
            .certificate { aspect-ratio: auto; padding: 30px 24px; }
        }
    </style>
</head>
<body>

    <div class="toolbar">
        <a href="AchievementStudent.php" class="btn btn-back">← Kembali</a>
        <button class="btn btn-print" onclick="window.print()">🖨️ Print / Save as PDF</button>
    </div>

    <div class="certificate">
        <div class="corner tl"></div>
        <div class="corner tr"></div>
        <div class="corner bl"></div>
        <div class="corner br"></div>

        <div class="cert-heading">CERTIFICATE OF COMPLETION</div>
        <div class="cert-subheading">Student Learning Management System</div>

        <div class="cert-presented-to">This certificate is proudly presented to</div>
        <div class="student-name"><?php echo htmlspecialchars($studentName); ?></div>

        <div class="cert-body-text">
            for successfully completing all chapters and passing every quiz assessment in
        </div>
        <div class="subject-name"><?php echo htmlspecialchars($subjectFullName); ?></div>

        <div class="cert-footer">
            <div class="cert-footer-item">
                <div class="line"></div>
                <div class="value"><?php echo htmlspecialchars($completionDate); ?></div>
                <div class="label">Date Completed</div>
            </div>
            <div class="cert-footer-item">
                <div class="line"></div>
                <div class="value"><?php echo htmlspecialchars($current_user); ?></div>
                <div class="label">Student ID</div>
            </div>
        </div>
    </div>

</body>
</html>