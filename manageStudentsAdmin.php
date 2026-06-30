<?php
// --- DATABASE CONFIGURATION ---
$host = "100.81.48.34";
$port = "3307";          // Explicitly targets your port 3307 setup
$dbname = "fictlp db";  // Matches your exact database layout container
$username = "bubustailo"; 
$password = "Student@123";

// ==========================================
// HANDLES INLINE POST ACTIONS (DELETE / UPDATE)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    // --- ACTION: DELETE ---
    if ($_POST['action'] === 'delete') {
        $studentId = $_POST['student_id'] ?? '';
        try {
            $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // MATCHED TO DATABASE: table 'user', column 'userID'
            $stmt = $conn->prepare("DELETE FROM user WHERE userID = :student_id AND Role = 'Student'");
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_STR);
            $stmt->execute();
            
            echo json_encode(['success' => true]);
            exit;
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
    
    // --- ACTION: UPDATE ---
    if ($_POST['action'] === 'update') {
        $oldId = $_POST['old_id'] ?? '';
        $newId = $_POST['new_id'] ?? '';
        $newName = $_POST['new_name'] ?? '';
        
        try {
            $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // MATCHED TO DATABASE: table 'user', columns 'userID', 'Name'
            $stmt = $conn->prepare("UPDATE user SET userID = :new_id, Name = :new_name WHERE userID = :old_id AND Role = 'Student'");
            $stmt->bindParam(':new_id', $newId, PDO::PARAM_STR);
            $stmt->bindParam(':new_name', $newName, PDO::PARAM_STR);
            $stmt->bindParam(':old_id', $oldId, PDO::PARAM_STR);
            $stmt->execute();
            
            echo json_encode(['success' => true]);
            exit;
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
}

// --- MAIN FETCH DATA FOR THE VIEW ROSTER ---
try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // MATCHED TO DATABASE: Selected userID, Name, user_status from table 'user' filtering for Role='Student'
    $stmt = $conn->prepare("SELECT userID AS student_id, Name AS name, user_status AS status FROM user WHERE Role = 'Student'");
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CoreKnowledge - Manage Students(Admin)</title>
    <link rel="stylesheet" href="manageStudentsAdmin.css">
</head>
<body>
<?php include("sidebarAdmin.php"); ?>
    <div class="window-frame">

        <main class="main-content">
            <h1>Manage Students</h1>
            <div class="subtitle">Review academic admission rosters, system status, and student credentials:</div>

            <div class="search-wrapper">
                <div class="search-icon"></div>
                <input type="text" id="studentSearch" class="search-input" placeholder="Search by Student ID or Name..." onkeyup="filterStudents()">
            </div>

            <div class="table-container" id="tableContainer">
                <div class="table-card-wrapper">
                    <?php if (count($students) > 0): ?>
                    <table class="student-table" id="studentTable">
                        <thead>
                            <tr id="tableHeaderRow">
                                <th class="col-id">Student ID</th>
                                <th class="col-name">Name</th>
                                <th class="col-status">Status</th>
                                <th class="col-actions" style="text-align: right; padding-right: 45px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="studentTableBody">
                            <?php foreach ($students as $row): ?>
                                <?php 
                                    // Use 'status' directly due to the SQL alias configuration setup above
                                    $isOnline = (int)$row['status'] === 1;
                                    $statusText = $isOnline ? 'Active' : 'Inactive';
                                    $badgeClass = $isOnline ? 'active-status' : 'inactive-status';
                                ?>
                                <tr data-original-id="<?php echo htmlspecialchars($row['student_id']); ?>" data-original-name="<?php echo htmlspecialchars($row['name']); ?>">
                                    <td class="student-id"><?php echo htmlspecialchars($row['student_id']); ?></td>
                                    <td class="student-name"><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td class="student-status">
                                        <span class="status-badge <?php echo $badgeClass; ?>"><?php echo $statusText; ?></span>
                                    </td>
                                    <td>
                                        <div class="action-group" style="padding-right: 15px;">
                                            <button class="btn-action btn-edit" onclick="toggleEdit(this)">Edit</button>
                                            <button class="btn-action btn-suspend" onclick="suspendStudent(this)">Suspend</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div id="searchEmptyState" class="empty-message" style="display: none;"></div>
                    <?php else: ?>
                        <div class="empty-message">No active student profiles remain in this section.</div>
                    <?php endif; ?>
                </div>
            </div>
        </main>

    </div>

    <script>
        function filterStudents() {
            const query = document.getElementById('studentSearch').value.toLowerCase().trim();
            const table = document.getElementById('studentTable');
            if (!table) return; 
            
            const rows = table.querySelectorAll('tbody tr');
            const headerRow = document.getElementById('tableHeaderRow');
            const emptyState = document.getElementById('searchEmptyState');
            let matchesFound = 0;

            rows.forEach(row => {
                let idText = row.getAttribute('data-original-id').toLowerCase();
                let nameText = row.getAttribute('data-original-name').toLowerCase();

                if (idText.includes(query) || nameText.includes(query)) {
                    row.style.display = '';
                    matchesFound++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (matchesFound === 0) {
                headerRow.style.display = 'none';
                emptyState.textContent = `No students found matching "${document.getElementById('studentSearch').value}"`;
                emptyState.style.display = 'block';
            } else {
                headerRow.style.display = '';
                emptyState.style.display = 'none';
            }
        }

        function handleLogout(event) {
            event.preventDefault(); 
            if (confirm("Are you sure you want to logout?")) {
                window.location.href = "logIn.html";
            }
        }
        
        function toggleEdit(button) {
            const row = button.closest('tr');
            const idCell = row.querySelector('.student-id');
            const nameCell = row.querySelector('.student-name');
            const actionGroup = row.querySelector('.action-group');

            if (button.classList.contains('btn-edit')) {
                const currentId = idCell.textContent.trim();
                const currentName = nameCell.textContent.trim();

                idCell.innerHTML = `<input type="text" class="edit-input id-field" value="${currentId}">`;
                nameCell.innerHTML = `<input type="text" class="edit-input name-field" value="${currentName}">`;
                
                actionGroup.innerHTML = `
                    <button class="btn-action btn-save" onclick="saveChanges(this)">Save</button>
                    <button class="btn-action btn-cancel" onclick="cancelEdit(this)">Cancel</button>
                `;
            }
        }

        function cancelEdit(button) {
            const row = button.closest('tr');
            const idCell = row.querySelector('.student-id');
            const nameCell = row.querySelector('.student-name');
            const actionGroup = row.querySelector('.action-group');

            idCell.textContent = row.getAttribute('data-original-id');
            nameCell.textContent = row.getAttribute('data-original-name');

            actionGroup.innerHTML = `
                <button class="btn-action btn-edit" onclick="toggleEdit(this)">Edit</button>
                <button class="btn-action btn-suspend" onclick="suspendStudent(this)">Suspend</button>
            `;
        }

        function saveChanges(button) {
            const row = button.closest('tr');
            const oldId = row.getAttribute('data-original-id');
            const oldName = row.getAttribute('data-original-name');
            const newId = row.querySelector('.id-field').value.trim();
            const newName = row.querySelector('.name-field').value.trim();

            if (!newId || !newName) {
                alert("Fields cannot be saved completely empty.");
                return;
            }

            if (newId === oldId && newName === oldName) {
                alert("Error: No changes were detected to save.");
                return;
            }

            if (!confirm("Are you sure you want to make changes?")) {
                return; 
            }

            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('old_id', oldId);
            formData.append('new_id', newId);
            formData.append('new_name', newName);

            fetch('manageStudents.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    row.setAttribute('data-original-id', newId);
                    row.setAttribute('data-original-name', newName);

                    row.querySelector('.student-id').textContent = newId;
                    row.querySelector('.student-name').textContent = newName;

                    row.querySelector('.action-group').innerHTML = `
                        <button class="btn-action btn-edit" onclick="toggleEdit(this)">Edit</button>
                        <button class="btn-action btn-suspend" onclick="suspendStudent(this)">Suspend</button>
                    `;

                    alert("Success! Student records have been updated in phpMyAdmin.");
                    filterStudents();
                } else {
                    alert("Database Save Error: " + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert("Network Error: Could not connect to write update.");
            });
        }

        function suspendStudent(button) {
            const row = button.closest('tr');
            const studentId = row.querySelector('.student-id').textContent.trim();

            if (confirm(`Are you sure you want to completely delete student ${studentId} from the database?`)) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('student_id', studentId);

                fetch('manageStudents.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        row.remove();
                        alert("Student successfully removed from the database.");

                        const table = document.getElementById('studentTable');
                        const remainingRows = table.querySelectorAll('tbody tr');
                        if (remainingRows.length === 0) {
                            const container = document.getElementById('tableContainer');
                            container.innerHTML = '<div class="empty-message">No active student profiles remain in this section.</div>';
                        } else {
                            filterStudents();
                        }
                    } else {
                        alert("Database Error: Could not delete student profile. " + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("Network Error: Could not process deletion request.");
                });
            }
        }
    </script>
</body>
</html>