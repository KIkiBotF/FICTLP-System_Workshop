<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer/src/Exception.php';
require 'PHPMailer/PHPMailer/src/PHPMailer.php';
require 'PHPMailer/PHPMailer/src/SMTP.php';

$host = "100.81.48.34";
$port = "3307";          
$dbname = "fictlp db";  
$username = "bubustailo"; 
$password = "Student@123";
$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) { die("Database connection failed."); }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // Semak sama ada email pelajar wujud dalam database
    $sql = "SELECT * FROM user WHERE Email = '$email'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        
        // ==========================================
        // TETAPKAN DI SINI (Sebelum jana masa luput)
        // ==========================================
        date_default_timezone_set('Asia/Kuala_Lumpur');

        // 1. Jana 6-Digit OTP rawak & Set masa luput (5 minit)
        $otp = rand(100000, 999999);
        $expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

        // 2. Kemaskini database jadual user dengan OTP baharu
        $update_sql = "UPDATE user SET otp_code = '$otp', token_expiry = '$expiry' WHERE Email = '$email'";
        $conn->query($update_sql);

        // Simpan email dalam session untuk digunakan pada halaman VerifyOTP.php nanti
        $_SESSION['reset_email'] = $email;

        // 3. Konfigurasi dan hantar email melalui PHPMailer
        $mail = new PHPMailer(true);
        try {
            // --- TETAPAN EMEL SISTEM ---
            $system_email = 'fictlp.system@gmail.com';       
            $system_password = 'krcicekmgivxmwmi'; 

            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            $mail->Username   = $system_email;     
            $mail->Password   = $system_password;   
            
            // --- KEMASKINI PENYELESAIAN DI SINI ---
            // Tukar ke ENCRYPTION_SMTPS (SSL) dan Port 465 untuk kestabilan rangkaian local
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            // Logik bypass pengesahan SSL tempatan (Mengatasi isu isu XAMPP/Localhost)
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            // --------------------------------------

            // Pengirim & Penerima
            $mail->setFrom($system_email, 'FICTLP Support System');
            $mail->addAddress($email); 

            // Kandungan Emel OTP
            $mail->isHTML(true);
            $mail->Subject = 'Your Password Reset OTP Code';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #eee; border-radius: 8px; max-width: 450px; margin: 0 auto;'>
                    <h2 style='color: #6000ff; text-align: center;'>Reset Password Request</h2>
                    <p>Hello,</p>
                    <p>You have requested to reset your account password. Please use the OTP code below to proceed with the verification process:</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <span style='background-color: #f3f0ff; color: #6000ff; font-size: 32px; font-weight: bold; padding: 10px 30px; letter-spacing: 5px; border-radius: 4px; border: 1px dashed #6000ff;'>{$otp}</span>
                    </div>
                    <p style='color: #ef4444; font-size: 13px;'>*This OTP code is valid for 5 minutes only.</p>
                    <p>If you did not make this request, please ignore this email.</p>
                </div>";

            $mail->send();

            // Berjaya! Pindah terus ke halaman VerifyOTP.php
            header("Location: VerifyOTP.php");
            exit();

        } catch (Exception $e) {
            echo "<script>alert('Failed to send email. SMTP Error: {$mail->ErrorInfo}'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Email not found in the system!'); window.history.back();</script>";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - FICTLP</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrapper">

        <div class="title-container">
            <div class="logo-container">
                <img src="Aset/LogoUtem.png" alt="Logo UTeM" class="login-logo-utem">
                <img src="Aset/FTMK2.png" alt="Logo FTMK" class="login-logo-ftmk">
            </div>
            <h1>FICTLP SYSTEM</h1>
            <h3>Forgot Password</h3>
        </div>

        <div class="header">
            <header>
                <h1>Forgot Password?</h1>
            </header>
            <h2>Enter your registered email address to<br>receive password recovery instruction</h2>
        </div>

        <div class="email-container">
            <form>
                <label for="emailInput">Email</label>
                <input type="email" id="emailInput" required>
                <button type="submit" name="recovery">Send OTP</button>
            </form>

        <div class="logInForm-container">
            <p style="text-align: center; margin-bottom: 25px; color: #333; font-size: 14px; line-height: 1.5;">
                Enter your registered email to receive the OTP verification code.
            </p>
            <form method="POST">
                <div class="input-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="example@student.utem.edu.my" required autocomplete="off">
                </div>
                <div class="button-container">

                    <button type="submit">Request OTP</button>

                    <button class="btn-primary" type="button" name="recovery">Send Recovery Instruction</button>
                    <a class="btn-link" href="index.php">Back to Login Page</a>
                    
                </div>
            </form>
        </div>
    </div>
</body>
</html>