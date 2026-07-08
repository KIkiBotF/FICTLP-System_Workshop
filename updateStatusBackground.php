<?php
session_start();

// Check if the user is authenticated in the session
if (isset($_SESSION['userID'])) {
    $host = "100.81.48.34";
    $port = "3307";          
    $dbname = "fictlp db";  
    $username = "bubustailo"; 
    $password = "Student@123";

    try {
        $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Target the authenticated user and update their status safely to 0 (Offline)
        $stmt = $conn->prepare("UPDATE user SET user_status = 0 WHERE userID = ?");
        $stmt->execute([$_SESSION['userID']]);
    } catch (PDOException $e) {
        // Asynchronous background requests should fail silently without outputting visible error streams
    }
}
?>