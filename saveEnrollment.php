<?php
session_start();
if (!isset($_SESSION['userID'])) { die("Unauthorized"); }

$host = "100.81.48.34";
$port = "3307";          
$dbname = "fictlp db";  
$username = "bubustailo"; 
$password = "Student@123";

$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_SESSION['email'] ?? '';
    $subjectCode = mysqli_real_escape_string($conn, $_POST['subjectCode']);

    if (empty($email)) {
        // Fallback jika session email tiada, dapatkan dari database guna userID
        $userID = $_SESSION['userID'];
        $userQuery = $conn->query("SELECT Email FROM user WHERE userID = '$userID'");
        if($userQuery && $userQuery->num_rows > 0) {
            $uRow = $userQuery->fetch_assoc();
            $email = $uRow['Email'];
        }
    }

    // Masukkan data terus ke dalam jadual 'enrollment' sedia ada anda
    $sql = "INSERT IGNORE INTO enrollment (Email, Subject_Code) VALUES ('$email', '$subjectCode')";
    
    if ($conn->query($sql)) {
        echo "success";
    } else {
        echo "error";
    }
}
?>