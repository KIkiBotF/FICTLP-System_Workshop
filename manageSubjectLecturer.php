<?php
session_start();

$lecturer_id = isset($_SESSION['userID']) ? $_SESSION['userID'] : 'LY000001';

$conn = new mysqli("100.81.48.34", "bubustailo", "Student@123", "fictlp db", "3307");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_desc'])) {
    $update_code = $_POST['subject_code'];
    $new_desc = $_POST['new_description'];

    $update_stmt = $conn->prepare("UPDATE subject SET Description = ? WHERE Subject_Code = ?");
    $update_stmt->bind_param("ss", $new_desc, $update_code);
    $update_stmt->execute();
    $update_stmt->close();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
// ------------------------------------------

// 3. Prepare the query
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
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $title = htmlspecialchars($row['Title']);
                    $code  = htmlspecialchars($row['Subject_Code']);
                    $desc  = htmlspecialchars($row['Description']);
            ?>
                    <div class="subject-card" onclick="window.location.href='editSubject.php?subject=<?php echo urlencode($code); ?>'" style="cursor: pointer;" title="Click to manage chapters">
                        <div class="card-header">
                            <h2><?php echo $title; ?></h2>
                        </div>
                        <div class="card-body">
                            <ul class="subject-topics">
                                <li><strong>Code:</strong> <?php echo $code; ?></li>

                                <li style="display: flex; flex-direction: column; gap: 8px;">
                                    <div id="display-desc-<?php echo $code; ?>" style="display: flex; align-items: flex-start; justify-content: space-between; gap: 15px;">
                                        <span><?php echo $desc; ?></span>
                                        <div onclick="toggleEdit('<?php echo $code; ?>', event)" style="display: flex; align-items: center; gap: 6px; cursor: pointer; flex-shrink: 0; color: #006699; font-weight: bold; background: #f0f7fa; padding: 4px 10px; border-radius: 6px;" title="Edit Description">
                                            <img src="Aset/editBtn.svg" alt="Edit" style="width: 22px;">
                                            <span style="font-size: 0.9rem;">Edit</span>
                                        </div>
                                    </div>

                                    <form id="edit-form-<?php echo $code; ?>" method="POST" style="display: none; width: 100%;" onclick="event.stopPropagation();">
                                        <input type="hidden" name="subject_code" value="<?php echo $code; ?>">
                                        <textarea name="new_description" rows="3" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-family: inherit; margin-bottom: 5px; resize: vertical;"><?php echo $desc; ?></textarea>
                                        <div style="display: flex; gap: 5px;">
                                            <button type="submit" name="update_desc" style="padding: 6px 12px; background-color: #006699; color: white; border: none; border-radius: 4px; cursor: pointer;">Save</button>
                                            <button type="button" onclick="toggleEdit('<?php echo $code; ?>', event)" style="padding: 6px 12px; background-color: #ccc; color: black; border: none; border-radius: 4px; cursor: pointer;">Cancel</button>
                                        </div>
                                    </form>
                                </li>

                            </ul>
                        </div>
                    </div>
            <?php
                }
            } else {
                echo "<p style='text-align: center; color: #555; font-size: 1.2rem;'>You have not been assigned any subjects yet.</p>";
            }
            ?>
        </div>
    </main>

    <script>
        function toggleEdit(code, event) {
            event.stopPropagation();

            var displayDiv = document.getElementById('display-desc-' + code);
            var editForm = document.getElementById('edit-form-' + code);

            if (editForm.style.display === 'none' || editForm.style.display === '') {
                editForm.style.display = 'block';
                displayDiv.style.display = 'none';
            } else {
                editForm.style.display = 'none';
                displayDiv.style.display = 'flex';
            }
        }
    </script>

</body>

</html>