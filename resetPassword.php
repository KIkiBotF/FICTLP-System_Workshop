<?php
session_start();

// Security check: If someone tries to access this page directly without logging in first, kick them back to index.php
if (!isset($_SESSION['reset_email'])) {
    header("Location: index.php");
    exit();
}

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['resetBtn'])) {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Logic checks
        if ($new_password === '123456') {
            $error_message = "Your new password cannot be the default password.";
        } elseif ($new_password !== $confirm_password) {
            $error_message = "Passwords do not match!";
        } else {
            $host = "100.81.48.34";
            $port = "3307";
            $dbname = "fictlp db";
            $username = "bubustailo";
            $password = "Student@123";

            $conn = new mysqli($host, $username, $password, $dbname, $port);

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $email = $_SESSION['reset_email'];

            $stmt = $conn->prepare("UPDATE user SET Password = ?, Verification_Status = 'ACTIVE' WHERE Email = ?");
            $stmt->bind_param("ss", $new_password, $email);
            
            if ($stmt->execute()) {
                //Security
                session_unset();
                session_destroy();
                
                header("Location: index.php");
                exit();
            } else {
                $error_message = "Database error. Could not update password.";
            }
            
            $stmt->close();
            $conn->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mandatory Password Reset</title>
    <!-- Reusing your forget password CSS -->
    <link rel="stylesheet" href="ForgetPasswordStyle.css">
</head>

<body>
    <div class="wrapper">
        <div class="header">
            <header>
                <h1>Security Update</h1>
            </header>
            <h2>You are currently using the default password.<br>Please set a new, secure password to continue.</h2>
        </div>

        <div class="email-container">
            <?php if (!empty($error_message)): ?>
                <div style="color: red; margin-bottom: 15px; font-weight: bold; text-align: center;">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" style="width: 100%;">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required style="width: 100%; padding: 12px; margin-bottom: 15px; border: none; border-radius: 5px; background-color: #dadddf; outline: none;">

                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required style="width: 100%; padding: 12px; margin-bottom: 25px; border: none; border-radius: 5px; background-color: #dadddf; outline: none;">

                <div class="button-container">
                    <button class="btn-primary" type="submit" name="resetBtn">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>