<?php
$host = "100.81.48.34";
$port = "3307";
$dbname = "fictlp db";
$username = "bubustailo";
$password = "Student@123";

$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection Failed: " . $conn->connect_error]));
}

$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 1;

// Guna prepared statement - lebih selamat dari string interpolation terus
$stmt = $conn->prepare("SELECT Question_ID, Question_Text, OptA, OptB, OptC, OptD
                        FROM quiz_question
                        WHERE Quiz_ID = ?
                        ORDER BY RAND() LIMIT 10");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$result = $stmt->get_result();

$data = array();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Correct_Answer SENGAJA tak di-select/return ke client
        $data[] = $row;
    }
} else {
    die(json_encode(["error" => "Database Error:" . $conn->error]));
}

header('Content-Type: application/json');
echo json_encode($data);

$stmt->close();
$conn->close();
?>