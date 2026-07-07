<?php
// Pass the user's email to the reset page securely
session_start();

$host = "100.81.48.34";
$port = "3307";
$dbname = "fictlp db";
$username = "bubustailo";
$db_password = "Student@123";

$conn = new mysqli($host, $username, $db_password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['loginBtn'])) {
    $email = trim($_POST['email']);
    $enteredPassword = $_POST['password'];

    $stmt = $conn->prepare("SELECT userID, Name, Email, Password, Role FROM user WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if ($enteredPassword === $row['Password']) {

            $_SESSION['userID'] = $row['userID'];
            $_SESSION['name']   = $row['Name'];
            $_SESSION['email']  = $row['Email'];
            $_SESSION['role']   = $row['Role'];

            // === UPDATE STATUS TO ACTIVE (1) ===
            $statusStmt = $conn->prepare("UPDATE user SET user_status = 1 WHERE userID = ?");
            $statusStmt->bind_param("s", $row['userID']);
            $statusStmt->execute();
            $statusStmt->close();
            // ===================================

            if ($enteredPassword === '123456') {
                $_SESSION['reset_email'] = $email;
                header("Location: resetPassword.php");
                exit();
            }

            switch ($row['Role']) {
                case 'Lecturer':
                    header("Location: mainPageLecturer.php");
                    exit();
                case 'Admin':
                    header("Location: mainPageAdmin.php");
                    exit();
                case 'Student':
                    header("Location: mainPageStudent.php");
                    exit();
                default:
                    $error_message = "Invalid user role configuration.";
                    break;
            }
        } else {
            $error_message = "Invalid email or password!";
        }
    } else {
        $error_message = "Invalid email or password!";
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FICTLP-System_Workshop</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="wrapper">
        <div class="title-container">
            <div class="logo-container" style="padding-bottom: 10px;">
                <img src="Aset/LogoUtem.png" alt="Logo UTeM" class="login-logo-utem">
                <img src="Aset/FTMK2.png" alt="Logo FTMK" class="login-logo-ftmk">
            </div>
            <h1>Welcome Back!</h1>
            <h3>login to your account</h3>
        </div>

        <div class="logInForm-container">
            <?php if (!empty($error_message)): ?>
                <div class="error-msg" style="color: red; margin-bottom: 15px; font-weight: bold;">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <form id="details-container" action="" method="POST">
                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="input-group">
                    <div class="label-row">
                        <label for="password">Password</label>
                        <a href="forgetPassword.php" class="forgot-password">Forget password?</a>
                    </div>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="button-container2">
                    <button type="reset" class="btn-clear">Clear</button>
                    <button type="submit" class="btn-login" name="loginBtn">Log in</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>