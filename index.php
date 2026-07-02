<?php
// Start session to store logged-in user data
session_start();

$host = "100.81.48.34";
$port = "3307";          
$dbname = "fictlp db";  
$username = "bubustailo"; 
$password = "Student@123";

$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$error_message = "";

// PROCESS LOGIN VERIFICATION WHEN BUTTON IS CLICKED
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['loginBtn'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password_input = mysqli_real_escape_string($conn, $_POST['password']);

    // Find user based on 'Email' and plain text 'Password'
    $sql = "SELECT * FROM user WHERE Email = '$email' AND Password = '$password_input'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Store user details into session variables
       $_SESSION['userID'] = $user['userID'];
        $_SESSION['role'] = $user['Role'];
        $_SESSION['name'] = $user['Name'];
        $_SESSION['email'] = $user['Email'];

        $role = $user['Role'];


        // MULTI-ROLE REDIRECTION LOGIC
        if ($role === 'Student') {
            echo "<script>
                    window.location.href = 'mainPageStudent.php'; 
                  </script>";
            exit();
        } 
        elseif ($role === 'Lecturer') {
            echo "<script>
                    window.location.href = 'mainPageLecturer.php'; 
                  </script>";
            exit();
        } 
        elseif ($role === 'Admin') {
            echo "<script>
                    window.location.href = 'mainPageAdmin.php'; 
                  </script>";
            exit();
        } 
        else {
            echo "<script>
                    alert('Your account role is invalid.');
                    window.location.href = 'index.php';
                  </script>";
            exit();
        }

    } else {
        $error_message = "Invalid Email or Password!";
    }
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FICTLP-System_Workshop - Login</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .error-msg {
            color: #d9534f;
            background-color: #f2dede;
            border: 1px solid #ebccd1;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            text-align: center;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="title-container">
            <div class="logo-container" style="padding-bottom: 10px;">    
                <img src="Aset/LogoUtem.png" alt="Logo UTeM" class="login-logo-utem">
                <img src="Aset/FTMK2.png" alt="Logo FTMK" class="login-logo-ftmk">
            </div>
            <h1>Welcome Back!</h1>
            <h3>Login to your account</h3>
        </div>

        <div class="logInForm-container">
            <?php if (!empty($error_message)): ?>
                <div class="error-msg"><?php echo $error_message; ?></div>
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

                <div class="button-container">
                    <button type="reset" class="btn-clear">Clear</button>
                    <button type="submit" class="btn-login" name="loginBtn">Log in</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>