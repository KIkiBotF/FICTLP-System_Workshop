<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forget Password?</title>
    <link rel="stylesheet" href="ForgetPasswordStyle.css">

</head>


<body>
    <div class="wrapper">
        <div class="header">
            <header><h1>Forgot Password?</h1></header>
            <h2>Enter your registered email address to<br>receive password recovery instruction</h2>
        </div>
        
        <div class="email-container">
            <form>
                <label for="emailInput">Email</label>
                <input type="email" id="emailInput" required>

                <div class="button-container">
                    <button class="btn-primary" type="button" name="recovery">Send Recovery Instruction</button>
                    <a class="btn-link" type="button" name="backToLogin" href="logIn.html">Back to Login Page</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>