<?php
// Handle form submission
$error_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check hardcoded credentials
    if (isset($_POST['loginBtn'])) {
        if ($email === 'lecturer123@gmail.com' && $password === 'abc123') {
            header("Location: mainPage.php");
            exit();
        } else if ($email == 'admin123@gmail.com' && $password == 'abc123') {
            header("mainPageAdmin.php");
            exit();
        } else if ($email == 'student123@gmail.com' && $password == 'abc123') {
            header("mainPageStudent.php");
            exit();
        } else {
            $error_message = "Invalid email or password!";
        }
    }
}
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
            <h1>Welcome Back!</h1>
            <h3>login to your account</h3>
        </div>

        <div class="logInForm-container">
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