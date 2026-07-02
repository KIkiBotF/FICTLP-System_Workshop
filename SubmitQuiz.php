<?php
session_start();

$host = "100.81.48.34";
$port = "3307";         
$dbname = "fictlp db";  
$username = "bubustailo"; 
$password = "Student@123";

$conn = new mysqli($host, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Sambungan gagal: " . $conn->connect_error);
}

$input = json_decode(file_get_contents('php://input'), true);
$answers = $input['answers'] ?? []; 
$quiz_id = $input['quiz_id'] ?? 1;
// Menerima nilai subject secara dinamik dari front-end
$subject = isset($input['subject']) ? mysqli_real_escape_string($conn, $input['subject']) : 'CPP';
$score = 0;

if (!empty($answers)) {
    foreach ($answers as $q_id => $choice) {
        $q_id = intval($q_id);
        $choice = mysqli_real_escape_string($conn, $choice);
        $res = mysqli_query($conn, "SELECT Correct_Answer FROM `quiz_question` WHERE Question_ID = $q_id");
        if ($res && $row = mysqli_fetch_assoc($res)) {
            if (trim($row['Correct_Answer']) == trim($choice)) {
                $score++;
            }
        }
    }
}

// Ambil email dari session, letak email dummy jika tiada session aktif
$email = isset($_SESSION['email']) ? $_SESSION['email'] : 'student@utem.edu.my'; 

// Simpan rekod kemajuan berdasarkan subjek dinamik yang dipilih user
$sql = "INSERT INTO `user_progress` (Email, Subject_Code, chapterIndex, completedAt) 
        VALUES ('$email', '$subject', $quiz_id, NOW())";
mysqli_query($conn, $sql);

echo "Tahniah! Kuiz selesai. Skor anda: $score / " . count($answers);
?>