<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer/src/Exception.php';
require 'PHPMailer/PHPMailer/src/PHPMailer.php';
require 'PHPMailer/PHPMailer/src/SMTP.php';

// Sekatan: Jika tiada email dalam session, tendang balik ke halaman awal
if (!isset($_SESSION['reset_email'])) {
    header("Location: forgetPassword.php");
    exit();
}

$host = "100.81.48.34";
$port = "3307";          
$dbname = "fictlp db";  
$username = "bubustailo"; 
$password = "Student@123";
$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) { die("Failed connection"); }

// Tetapkan zon masa Malaysia untuk keseluruhan fail ini
date_default_timezone_set('Asia/Kuala_Lumpur');

$message = ""; // Pembolehubah untuk simpan mesej notifikasi

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_SESSION['reset_email'];

    // ==========================================
    // LOGIK 1: PENGESAHAN KOD OTP (Butang Verify Code)
    // ==========================================
    if (isset($_POST['verify_otp'])) {
        $user_otp = mysqli_real_escape_string($conn, $_POST['otp']);
        $masa_sekarang = date("Y-m-d H:i:s");

        // Semak OTP mengikut masa Malaysia yang tepat
        $sql = "SELECT * FROM user WHERE Email = '$email' AND otp_code = '$user_otp' AND token_expiry >= '$masa_sekarang'";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $_SESSION['otp_verified'] = true; 
            header("Location: ResetPasswordOTP.php");
            exit();
        } else {
            $message = "<div style='color: #ef4444; margin-bottom: 15px;'>Kod OTP salah atau telah tamat tempoh!</div>";
        }
    }

    // ==========================================
    // LOGIK 2: HANTAR SEMULA OTP (Butang Request OTP Baru)
    // ==========================================
    if (isset($_POST['resend_otp'])) {
        $otp = rand(100000, 999999);
        $expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

        // Kemaskini OTP baharu di database
        $update_sql = "UPDATE user SET otp_code = '$otp', token_expiry = '$expiry' WHERE Email = '$email'";
        $conn->query($update_sql);

        // Konfigurasi PHPMailer
        $mail = new PHPMailer(true);
        try {
            $system_email = 'fictlp.system@gmail.com';       
            $system_password = 'krcicekmgivxmwmi'; 

            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            $mail->Username   = $system_email;     
            $mail->Password   = $system_password;   
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            $mail->setFrom($system_email, 'FICTLP Support System');
            $mail->addAddress($email); 

            $mail->isHTML(true);
            $mail->Subject = 'Your New Password Reset OTP Code';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #eee; border-radius: 8px; max-width: 450px; margin: 0 auto;'>
                    <h2 style='color: #6000ff; text-align: center;'>New OTP Code Requested</h2>
                    <p>Hello,</p>
                    <p>Anda telah meminta kod OTP yang baharu. Sila gunakan kod di bawah untuk pengesahan:</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <span style='background-color: #f3f0ff; color: #6000ff; font-size: 32px; font-weight: bold; padding: 10px 30px; letter-spacing: 5px; border-radius: 4px; border: 1px dashed #6000ff;'>{$otp}</span>
                    </div>
                    <p style='color: #ef4444; font-size: 13px;'>*Kod OTP baharu ini sah dalam tempoh 5 minit sahaja.</p>
                </div>";

            $mail->send();
            $message = "<div style='color: #10b981; margin-bottom: 15px;'>OTP Code sent successfully!</div>";

        } catch (Exception $e) {
            $message = "<div style='color: #ef4444; margin-bottom: 15px;'>Failed to send email: {$mail->ErrorInfo}</div>";
        }
    }
}
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - FICTLP</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Gaya tambahan khusus untuk susunan input OTP & butang resend */
        .otp-input {
            text-align: center;
            font-size: 24px !important;
            letter-spacing: 8px;
            font-weight: bold;
        }
        .resend-box {
            text-align: center;
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px dashed rgba(0, 0, 0, 0.1);
        }
        .btn-resend {
            background: none !important;
            color: #3e13b4 !important;
            box-shadow: none !important;
            min-width: auto !important;
            padding: 5px 10px !important;
            text-decoration: underline;
            font-size: 14px;
        }
        .btn-resend:hover {
            color: #683ae9 !important;
            transform: none !important;
        }
        .msg-alert {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="title-container">
            <div class="logo-container">
                <img src="Aset/LogoUtem.png" alt="Logo UTeM" class="login-logo-utem">
                <img src="Aset/FTMK2.png" alt="Logo FTMK" class="login-logo-ftmk">
            </div>
            <h1>FICTLP LMS</h1>
            <h3>OTP Verification</h3>
        </div>

        <div class="logInForm-container">
            <?php if (!empty($message)): ?>
                <div class="msg-alert"><?php echo $message; ?></div>
            <?php endif; ?>

            <p style="text-align: center; margin-bottom: 20px; color: #333; font-size: 14px;">
                Please enter the 6-digit OTP code sent to your email.
            </p>

            <form method="POST">
                <div class="input-group">
                    <label style="text-align: center; display: block;">Enter OTP Code</label>
                    <input type="text" name="otp" maxlength="6" class="otp-input" placeholder="000000" required autocomplete="off" 
                           style="width: 100%; padding: 14px; border: 1px solid rgba(0,0,0,0.08); border-radius: 12px; outline: none;">
                </div>
                <div class="button-container">
                    <button type="submit" name="verify_otp">Verify Code</button>
                </div>
            </form>

            <div class="resend-box">
                <p style="font-size: 13px; color: #555; margin-bottom: 5px;">Haven't received the code or the code has expired?</p>
                <form method="POST">
                    <button type="submit" name="resend_otp" class="btn-resend">Request New OTP</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>