<?php
session_start();
// Default to a test ID if the session isn't set
$lecturer_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'LY000001';

// Establish connection
$conn = new mysqli("100.81.48.34", "bubustailo", "Student@123", "fictlp db", "3307");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Fetch the subjects assigned ONLY to this lecturer for the dropdown menu
$dropdown_stmt = $conn->prepare("
    SELECT s.Subject_Code, s.Title
    FROM subject s
    JOIN lecture_subject ls ON s.Subject_Code = ls.Subject_Code
    WHERE ls.userID = ?
");
$dropdown_stmt->bind_param("s", $lecturer_id);
$dropdown_stmt->execute();
$lecturer_subjects = $dropdown_stmt->get_result();
$dropdown_stmt->close();

// 2. Safely check if a subject was selected from the dropdown
$selected_subject = isset($_GET['subjectSelect']) ? $_GET['subjectSelect'] : '';

// Initialize variables so they don't throw errors if empty
$total_enrolled = 0;
$pass_count = 0;
$fail_count = 0;

// 3. If a subject is selected, run the pass/fail queries
if ($selected_subject !== '') {

    // --- Get Total Enrolled ---
    $stmt = $conn->prepare("SELECT COUNT(userID) AS total FROM enrollment WHERE Subject_Code = ?");
    $stmt->bind_param("s", $selected_subject);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $total_enrolled = $row['total'];
    }
    $stmt->close();

    // --- Get Total Passes ---
    $pass_stmt = $conn->prepare("
        SELECT COUNT(s.userID) AS pass_count 
        FROM score s 
        JOIN quiz q ON s.Quiz_ID = q.Quiz_ID 
        WHERE q.Subject_Code = ? AND s.Grade >= q.Passing_Mark
    ");
    $pass_stmt->bind_param("s", $selected_subject);
    $pass_stmt->execute();
    $pass_result = $pass_stmt->get_result();
    if ($pass_row = $pass_result->fetch_assoc()) {
        $pass_count = $pass_row['pass_count'];
    }
    $pass_stmt->close();

    // --- Get Total Fails ---
    $fail_stmt = $conn->prepare("
        SELECT COUNT(s.userID) AS fail_count 
        FROM score s
        JOIN quiz q ON s.Quiz_ID = q.Quiz_ID 
        WHERE q.Subject_Code = ? AND s.Grade < q.Passing_Mark
    ");
    $fail_stmt->bind_param("s", $selected_subject);
    $fail_stmt->execute();
    $fail_result = $fail_stmt->get_result();
    if ($fail_row = $fail_result->fetch_assoc()) {
        $fail_count = $fail_row['fail_count'];
    }
    $fail_stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report - CoreKnowledge</title>
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="reportLecturerStyle.css">
</head>

<body>

    <?php include("sidebar.php"); ?>

    <main class="main-content">
        <h1>Report</h1>

        <div class="report-wrapper">
            <div class="report-container">

                <form method="GET" action="reportLecturer.php" class="filter-row">
                    <label for="subjectSelect">Subject:</label>

                    <select id="subjectSelect" name="subjectSelect" onchange="this.form.submit()">
                        <option value="" disabled <?php echo ($selected_subject == '') ? 'selected' : ''; ?>>Select a subject...</option>

                        <?php
                        // Loop through the subjects this lecturer teaches and build the options
                        if ($lecturer_subjects->num_rows > 0) {
                            while ($sub_row = $lecturer_subjects->fetch_assoc()) {
                                $code = htmlspecialchars($sub_row['Subject_Code']);
                                $title = htmlspecialchars($sub_row['Title']);

                                // Keep the option highlighted if it's the one currently selected
                                $is_selected = ($selected_subject === $code) ? 'selected' : '';

                                echo "<option value=\"$code\" $is_selected>$title ($code)</option>";
                            }
                        } else {
                            echo "<option value=\"\" disabled>No subjects assigned</option>";
                        }
                        ?>

                    </select>
                </form>

                <div class="summary-cards">
                    <div class="card card-total">
                        <h3>Total student enroll</h3>
                        <p class="card-value"><?php echo htmlspecialchars($total_enrolled); ?></p>
                    </div>

                    <div class="card card-pass">
                        <h3>Pass</h3>
                        <p class="card-value"><?php echo htmlspecialchars($pass_count); ?></p>
                    </div>

                    <div class="card card-fail">
                        <h3>Fail</h3>
                        <p class="card-value"><?php echo htmlspecialchars($fail_count); ?></p>
                    </div>
                </div>

            </div>
        </div>
    </main>
</body>

</html>