<?php
$host = "100.81.48.34";
$port = "3307";         
$dbname = "fictlp db"; 
$username = "bubustailo"; 
$password = "Student@123";

$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die(json_encode(["error" => "Sambungan gagal: " . $conn->connect_error]));
}

// Hanya bergantung kepada Quiz_ID, kerana nilai ini sudah mewakili Bab tertentu
$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 1;

$sql = "SELECT Question_ID, Question_Text, OptA, OptB, OptC, Correct_Answer 
        FROM `quiz_question` 
        WHERE Quiz_ID = $quiz_id";

$result = mysqli_query($conn, $sql);
$data = array();

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
} else {
    // Memulangkan ralat format JSON jika SQL ada masalah
    die(json_encode(["error" => "Ralat Pangkalan Data: " . mysqli_error($conn)]));
}

header('Content-Type: application/json');
echo json_encode($data);
?>