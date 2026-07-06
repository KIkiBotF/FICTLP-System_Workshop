<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Pastikan path ini betul mengikut nama folder anda
require 'PHPMailer/PHPMailer/src/Exception.php';
require 'PHPMailer/PHPMailer/src/PHPMailer.php';
require 'PHPMailer/PHPMailer/src/SMTP.php';

// Sambungan ke Database
$host = "100.81.48.34";
$port = "3307";          
$dbname = "fictlp db";  
$username = "bubustailo"; 
$password = "Student@123";
$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) { die("Connection failed:" . $conn->connect_error); }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // Semak jika emel wujud di dalam jadual user
    $sql = "SELECT * FROM user WHERE Email = '$email'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        // 1. Generate 6-Digit OTP secara rawak
        $otp = rand(100000, 999999);
        
        // Tetapkan tempoh luput OTP (Sesuai untuk 5 minit sahaja demi keselamatan)
        $expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

        // 2. Simpan OTP dan waktu luput ke dalam database
        $update_sql = "UPDATE user SET otp_code = '$otp', token_expiry = '$expiry' WHERE Email = '$email'";
        $conn->query($update_sql);

        // Simpan emel ke dalam session untuk pengesahan di halaman seterusnya
        $_SESSION['reset_email'] = $email;

        // 3. Hantar OTP Menggunakan PHPMailer
        $mail = new PHPMailer(true);

        try {
            // --- KONFIGURASI SMTP GMAIL ---
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';                     
            $mail->SMTPAuth   = true;
            $mail->Username   = 'fictlp.system@gmail.com';                
            $mail->Password   = 'krcicekmgivxmwmi'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Pengirim & Penerima
            $mail->setFrom('fictlp.system@gmail.com', 'FICTLP Support');
            $mail->addAddress($email);

            // Kandungan Emel
            $mail->isHTML(true);
            $mail->Subject = 'Your Password Reset OTP Code';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #eee; border-radius: 8px; max-width: 450px; margin: 0 auto;'>
                    <h2 style='color: #6000ff; text-align: center;'>Reset Password Request</h2>
                    <p>Hello,</p>
                    <p>You have requested to reset your account password. Use the verification code below to proceed:</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <span style='background-color: #f3f0ff; color: #6000ff; font-size: 32px; font-weight: bold; padding: 10px 30px; letter-spacing: 5px; border-radius: 4px; border: 1px dashed #6000ff;'>{$otp}</span>
                    </div>
                    <p style='color: #ef4444; font-size: 13px;'>*This OTP code is valid for 5 minutes only.</p>
                    <p>If you did not make this request, please ignore this email.</p>
                </div>";

            $mail->send();

            // Pindah terus ke halaman memasukkan nombor OTP selepas emel berjaya dihantar
            header("Location: VerifyOTP.php");
            exit();

        } catch (Exception $e) {
            // Mode Backup jika SMTP anda tidak aktif semasa pembangunan, kod akan dipapar terus di skrin
            echo "
            <div style='font-family: Arial, sans-serif; padding: 30px; max-width: 500px; margin: 50px auto; border: 1px solid #ccc; text-align: center; border-radius: 8px; box-shadow: 0px 4px 10px rgba(0,0,0,0.1);'>
                <h2 style='color: #6000ff;'> [Testing Mode] OTP Generated </h2>
                <p>SMTP configuration is not fully connected yet, here is your OTP code:</p>
                <h1 style='letter-spacing: 4px; color: #10b981;'>{$otp}</h1>
                <p><a href='VerifyOTP.php' style='background-color:#6000ff; color:white; padding:12px 25px; text-decoration:none; display:inline-block; font-weight:bold; border-radius: 4px;'>Proceed to OTP Verification</a></p>
            </div>";
        }
    } else {
        echo "<script>alert('Email not found.'); window.history.back();</script>";
    }
}
$conn->close();
?>