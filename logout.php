<?php
session_start();

$host = "100.81.48.34";
$port = "3307";
$dbname = "fictlp db";
$username = "bubustailo";
$db_password = "Student@123";

if (isset($_SESSION['userID'])) {
    $conn = new mysqli($host, $username, $db_password, $dbname, $port);
    
    if (!$conn->connect_error) {
        // Update user status to Inactive (0)
        $stmt = $conn->prepare("UPDATE user SET user_status = 0 WHERE userID = ?");
        $stmt->bind_param("s", $_SESSION['userID']);
        $stmt->execute();
        $stmt->close();
    }
    $conn->close();
}

// Clear and terminate authorization context values
$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

header("Location: index.php");
exit();
?>