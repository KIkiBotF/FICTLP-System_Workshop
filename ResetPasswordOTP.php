<?php
session_start();

// SEKATAN KESELAMATAN: Menghalang bypass URL secara haram
if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true || !isset($_SESSION['reset_email'])) {
    header("Location: ForgotPassword.php");
    exit();
}

$host = "100.81.48.34";
$port = "3307";          
$dbname = "fictlp db";  
$username = "bubustailo"; 
$password = "Student@123";
$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password_baru = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = $_SESSION['reset_email'];

    if ($password_baru !== $confirm_password) {
        echo "<script>alert('Passwords do not match! Please ensure both entries are the same.');</script>";
    } else {
        // Kemaskini kata laluan dan kosongkan semula data OTP di database
        $update_sql = "UPDATE user SET password = '$password_baru', otp_code = NULL, token_expiry = NULL WHERE Email = '$email'";
        
        if ($conn->query($update_sql)) {
            // Padam status kebenaran session recovery supaya sesi ini mati (Mencegah guna semula)
            unset($_SESSION['otp_verified']);
            unset($_SESSION['reset_email']);
            
            echo "<script>
                    alert('Password successfully changed! Please log in using a new password.');
                    window.location.href = 'index.php';
                  </script>";
            exit();
        } else {
            echo "<script>alert('Failed to change password:" . $conn->error . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - FICTLP</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrapper">
        <div class="title-container">
            <div class="logo-container">
                <img src="Aset/LogoUtem.png" alt="Logo UTeM" class="login-logo-utem">
                <img src="Aset/FTMK2.png" alt="Logo FTMK" class="login-logo-ftmk">
            </div>
            <h1>FICTLP LMS</h1>
            <h3>Create New Password</h3>
        </div>

        <div class="logInForm-container">
            <p style="text-align: center; margin-bottom: 25px; color: #333; font-size: 14px;">
                Please set a new password for your account.
            </p>
            <form method="POST">
                <div class="input-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" required minlength="6" placeholder="Enter new password">
                </div>
                
                <div class="input-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6" placeholder="Confirm new password">
                </div>

                <div class="button-container">
                    <button type="submit">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>