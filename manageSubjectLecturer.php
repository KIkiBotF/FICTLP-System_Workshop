<?php
// 1. Start the session to track who is logged in
session_start();

// For this to work, you must set $_SESSION['user_id'] on your login page.
// Example: $_SESSION['user_id'] = 'LY000001'; 
// If no one is logged in, you should ideally redirect them back to login.
$lecturer_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'LY000001'; // Defaulting to LY000001 for testing purposes

// 2. Establish database connection (remember to move this to db_connect.php later!)
$conn = new mysqli("100.81.48.34", "bubustailo", "Student@123", "fictlp db", "3307");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 3. Query the database to find subjects linked ONLY to this specific lecturer
// We join the 'subject' table with the 'lecture_subject' table
$query = "SELECT s.Subject_Code, s.Title, s.Description 
        FROM subject s
        JOIN lecture_subject ls ON s.Subject_Code = ls.Subject_Code 
        WHERE ls.userID = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $lecturer_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subject - CoreKnowledge</title>
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="manageSubject.css">
</head>

<body>

    <?php include("sidebar.php"); ?>

    <main class="main-content">
        <div class="header-container">
            <h1>Manage Subject</h1>
        </div>

        <div class="card-container">
            <?php
            // 4. Check if the lecturer has any subjects assigned
            if ($result->num_rows > 0) {
                // 5. Loop through each subject and create a card
                // 5. Loop through each subject and create a card
                while ($row = $result->fetch_assoc()) {
                    $title = htmlspecialchars($row['Title']);
                    $code  = htmlspecialchars($row['Subject_Code']);
                    $desc  = htmlspecialchars($row['Description']);
            ?>
                    <!-- The entire card is now clickable -->
                    <div class="subject-card" onclick="window.location.href='editSubject.php?subject=<?php echo urlencode($code); ?>'" style="cursor: pointer;" title="Click to manage chapters">
                        <div class="card-header">
                            <h2><?php echo $title; ?></h2>
                        </div>
                        <div class="card-body">
                            <ul class="subject-topics">
                                <li><strong>Code:</strong> <?php echo $code; ?></li>
                                <li><?php echo $desc; ?></li>
                            </ul>
                            <!-- The card-actions div and pencil icon have been completely removed -->
                        </div>
                    </div>
            <?php
                } // End while loop
            } else {
                // Display a friendly message if they have no subjects assigned yet
                echo "<p style='text-align: center; color: #555; font-size: 1.2rem;'>You have not been assigned any subjects yet.</p>";
            }
            ?>
        </div>
    </main>
</body>

</html>