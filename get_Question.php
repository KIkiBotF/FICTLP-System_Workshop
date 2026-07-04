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

// 1. Get the subject and chapter from the URL
$subjectShort = isset($_GET['subject']) ? trim($_GET['subject']) : 'cpp';
$chapterNum = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 1;

// 2. Map short code to full Subject_Code
$map = [
    'db' => 'DITP2913',
    'cpp' => 'DITP1113',
    'coa' => 'DITS1133',
];
$subjectCode = $map[strtolower(trim($subjectShort))] ?? 'DITP1113';

// 3. Resolve the ACTUAL Quiz_ID using OFFSET
$offset = $chapterNum - 1;
if ($offset < 0) $offset = 0;

$sql = "SELECT Quiz_ID FROM quiz WHERE Subject_Code = ? ORDER BY Quiz_ID ASC LIMIT 1 OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $subjectCode, $offset);
$stmt->execute();
$result = $stmt->get_result();

$actualQuizID = null;
if ($row = $result->fetch_assoc()) {
    $actualQuizID = intval($row['Quiz_ID']);
}
$stmt->close();

if (!$actualQuizID) {
    die(json_encode(["error" => "Quiz_ID tidak dijumpai untuk subjek ini."]));
}

// 4. Fetch the questions using the correct Quiz_ID
$questionSql = "SELECT Question_ID, Question_Text, OptA, OptB, OptC, Correct_Answer FROM `quiz_question` WHERE Quiz_ID = $actualQuizID";
$qResult = mysqli_query($conn, $questionSql);

$data = array();
if ($qResult) {
    while ($row = mysqli_fetch_assoc($qResult)) {
        $data[] = $row;
    }
} else {
    die(json_encode(["error" => "Ralat Pangkalan Data: " . mysqli_error($conn)]));
}

header('Content-Type: application/json');
echo json_encode($data);
?>