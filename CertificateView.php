<?php
// 1. Mulakan sesi
session_start();

// Semak jika pelajar sudah login
if (!isset($_SESSION['userID'])) { // Mengikut nama ruangan 'userID' anda
    header("Location: index.php");
    exit();
}
$current_user = $_SESSION['userID'];

// Ambil Kod Subjek daripada URL (Contoh: certificate-view.php?subject_code=BIT2113)
if (!isset($_GET['subject_code'])) {
    die("Ralat: Kod Subjek tidak dinyatakan.");
}
$subject_code = $_GET['subject_code']; 

// 2. Sambungan ke Database
$host = "100.81.48.34";
$port = "3307";          
$dbname = "fictlp db";  
$username = "bubustailo"; 
$password = "Student@123";

$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Sambungan database gagal: " . $conn->connect_error);
}

// 3. LOGIK SEMAKAN: Semak jika rekod pencapaian wujud dalam table achievement
$sql_check = "SELECT Date_Issued FROM achievement WHERE userID = ? AND Subject_Code = ?";
$stmt = $conn->prepare($sql_check);
$stmt->bind_param("ss", $current_user, $subject_code);
$stmt->execute();
$result = $stmt->get_result();

// Jika tiada rekod dalam table achievement, bermakna mereka belum layak dapat sijil
if ($result->num_rows === 0) {
    echo "<script>
            alert('Maaf! Anda belum menamatkan subjek ini atau belum layak menerima sijil.');
            window.location.href = 'AchievementStudent.php';
          </script>";
    exit();
}

// Ambil tarikh asal sijil dikeluarkan daripada database
$achievement_data = $result->fetch_assoc();
$date_issued = date('d M Y', strtotime($achievement_data['Date_Issued']));

// 4. Ambil Nama Pelajar & Nama Subjek secara dinamik daripada table pelajar & subjek masing-masing
// (Sila sesuaikan nama table 'user' dan 'subject' mengikut database anda)
$nama_pelajar = "Noor Iman Nabil"; // Default jika tiada query, digalakkan ambil dari session/table user
$nama_subjek = $subject_code;     // Default guna kod subjek

// Contoh Query Tambahan (Optional jika anda mahu tarik nama penuh dari table lain):

$sql_details = "SELECT u.fullname, s.subject_name FROM users u, subjects s WHERE u.userID = ? AND s.Subject_Code = ?";
$stmt_d = $conn->prepare($sql_details);
$stmt_d->bind_param("ss", $current_user, $subject_code);
$stmt_d->execute();
$res_d = $stmt_d->get_result()->fetch_assoc();
if($res_d) {
    $nama_pelajar = $res_d['fullname'];
    $nama_subjek = $res_d['subject_name'];
}


$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sijil Penghargaan - LMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Inter:wght@400;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        :root {
            --bg-color: #fcf0f0;
            --primary-color: #5c6bcb;
            --dark: #2d3748;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .action-container {
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
        }

        .btn {
            padding: 12px 25px;
            font-size: 14px;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-download {
            background-color: var(--primary-color);
            color: white;
            box-shadow: 0 4px 12px rgba(92, 107, 203, 0.3);
        }

        .btn-download:hover {
            background-color: #4a57a9;
            transform: translateY(-2px);
        }

        .btn-back {
            background-color: #718096;
            color: white;
        }

        .btn-back:hover {
            background-color: #4a5568;
        }

        /* ── Reka Bentuk Sijil A4 Landskap ── */
        .certificate-container {
            width: 842px;  
            height: 595px; 
            background: #ffffff;
            padding: 40px;
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            box-sizing: border-box;
        }

        .cert-border {
            border: 4px double #d4af37; 
            height: 100%;
            width: 100%;
            padding: 30px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            text-align: center;
            position: relative;
        }

        .corner-deco {
            position: absolute;
            width: 40px;
            height: 40px;
            border: 4px solid #0d47a1;
        }
        .top-left { top: 10px; left: 10px; border-right: none; border-bottom: none; }
        .top-right { top: 10px; right: 10px; border-left: none; border-bottom: none; }
        .bottom-left { bottom: 10px; left: 10px; border-right: none; border-top: none; }
        .bottom-right { bottom: 10px; right: 10px; border-left: none; border-top: none; }

        .cert-header {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            letter-spacing: 2px;
            color: #1a202c;
            margin-top: 20px;
        }

        .cert-sub {
            font-size: 14px;
            font-style: italic;
            color: #718096;
            margin-top: 5px;
        }

        .student-name {
            font-family: 'Great Vibes', cursive;
            font-size: 48px;
            color: #0d47a1;
            margin: 20px 0;
        }

        .cert-text {
            font-size: 14px;
            color: #4a5568;
            max-width: 550px;
            line-height: 1.6;
        }

        .course-title {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 18px;
            display: block;
            margin-top: 5px;
        }

        .cert-footer {
            width: 100%;
            display: flex;
            justify-content: space-around;
            align-items: flex-end;
            margin-bottom: 20px;
        }

        .signature-area {
            border-top: 1px solid #cbd5e0;
            width: 180px;
            font-size: 12px;
            color: #718096;
            padding-top: 8px;
        }

        .badge-area {
            width: 70px;
            height: 70px;
            background: #d4af37;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 10px;
            font-weight: bold;
            box-shadow: 0 0 0 4px #fff, 0 0 0 6px #d4af37;
        }
    </style>
</head>
<body>

    <div class="action-container">
        <a href="AchievementStudent.php" class="btn btn-back">Kembali</a>
        <button onclick="downloadPDF()" class="btn btn-download">Download PDF</button>
    </div>

    <div id="certificate" class="certificate-container">
        <div class="cert-border">
            <div class="corner-deco top-left"></div>
            <div class="corner-deco top-right"></div>
            <div class="corner-deco bottom-left"></div>
            <div class="corner-deco bottom-right"></div>

            <div class="cert-header">CERTIFICATE OF COMPLETION</div>
            <div class="cert-sub">This is proudly presented to</div>
            
            <div class="student-name"><?php echo htmlspecialchars($nama_pelajar); ?></div>
            
            <div class="cert-text">
                has successfully completed 100% of the chapters and passed all required assessment modules for the course
                <span class="course-title"><?php echo htmlspecialchars($nama_subjek); ?></span>
            </div>

            <div class="cert-footer">
                <div class="signature-area">
                    <p><strong>LMS Administrator</strong></p>
                    <p>Sistem ULearn</p>
                </div>
                <div class="badge-area">PASSED</div>
                <div class="signature-area">
                    <p><strong><?php echo $date_issued; ?></strong></p>
                    <p>Date Issued</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function downloadPDF() {
            const element = document.getElementById('certificate');
            const opt = {
                margin:       0,
                filename:     'Sijil_<?php echo str_replace(' ', '_', $nama_subjek); ?>.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' }
            };
            html2pdf().set(opt).from(element).save();
        }
    </script>
</body>
</html>