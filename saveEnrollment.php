<?php
session_start();
if (!isset($_SESSION['userID'])) { die("Unauthorized"); }


$host = "100.81.48.34";
$port = "3307";
$dbname = "fictlp db";
$username = "bubustailo";
$password = "Student@123";

$conn = new mysqli($host, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['subjectCode']) || trim($_POST['subjectCode']) === '') {
        die("error: subjectCode missing");
    }

    $userID = $_SESSION['userID'];
    $subjectCode = trim($_POST['subjectCode']);

    $stmt = $conn->prepare("INSERT IGNORE INTO enrollment (userID, Subject_Code) VALUES (?, ?)");
    $stmt->bind_param("ss", $userID, $subjectCode);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error: " . $stmt->error;
    }
    $stmt->close();
}
$conn->close();
?>