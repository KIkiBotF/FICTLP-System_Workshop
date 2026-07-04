<?php
$host = "100.81.48.34";
$port = "3307";          
$dbname = "fictlp db";  
$username = "bubustailo"; 
$password = "Student@123";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    // --- ACTION: DELETE ---
    if ($_POST['action'] === 'delete') {
        $subjectCode = $_POST['subject_code'] ?? '';
        try {
            $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("DELETE FROM subject WHERE Subject_Code = :subject_code");
            $stmt->bindParam(':subject_code', $subjectCode, PDO::PARAM_STR);
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
        $oldCode = $_POST['old_code'] ?? '';
        $newCode = $_POST['new_code'] ?? '';
        $newTitle = $_POST['new_title'] ?? '';
        
        try {
            $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // MATCHED TO DATABASE: table 'subject', columns 'Subject_Code', 'Title'
          $stmt = $conn->prepare("UPDATE subject SET Subject_Code = :new_code, Title = :new_title WHERE Subject_Code = :old_code");
            $stmt->bindParam(':new_code', $newCode, PDO::PARAM_STR);
            $stmt->bindParam(':new_title', $newTitle, PDO::PARAM_STR);
            $stmt->bindParam(':old_code', $oldCode, PDO::PARAM_STR);
            $stmt->execute();
            
            echo json_encode(['success' => true]);
            exit;
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
}

// --- MAIN FETCH DATA FOR THE ROSTER ---
try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Changed to COUNT(*) to accurately count how many rows exist per subject code
    $query = "SELECT 
        s.Subject_Code AS subject_code, 
        s.Title AS title, 
        COUNT(e.userID) AS enrolled
    FROM subject s
    LEFT JOIN enrollment e ON s.Subject_Code = e.Subject_Code
    GROUP BY s.Subject_Code, s.Title";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CoreKnowledge - Manage Subjects(Admin)</title>
    <link rel="stylesheet" href="manageSubjectsAdmin.css">
</head>
<?php include("sidebarAdmin.php"); ?>
<body>
    

    <div class="window-frame">

        <main class="main-content">
            <h1>Manage Subjects</h1>
            <div class="subtitle">Modify subject configuration rules and metrics:</div>

            <div class="search-wrapper">
                <div class="search-icon"></div>
                <input type="text" id="subjectSearch" class="search-input" placeholder="Search by ID or Subject Name..." onkeyup="filterSubjects()">
            </div>

            <div class="table-container" id="tableContainer">
                <div class="table-card-wrapper">
                    <?php if (count($subjects) > 0): ?>
                    <table class="subject-table" id="subjectTable">
                        <thead>
                            <tr id="tableHeaderRow">
                                <th class="col-id">ID</th>
                                <th class="col-name">Subject Name</th>
                                <th class="col-enroll">Enrolled</th>
                                <th class="col-actions" style="text-align: right; padding-right: 45px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="subjectTableBody">
                            <?php foreach ($subjects as $row): ?>
                                <tr data-original-id="<?php echo htmlspecialchars($row['subject_code']); ?>" 
                                    data-original-name="<?php echo htmlspecialchars($row['title']); ?>">
                                    <td class="subject-id"><?php echo htmlspecialchars($row['subject_code']); ?></td>
                                    <td class="subject-name"><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td class="subject-enroll"><?php echo htmlspecialchars($row['enrolled']); ?></td>
                                    <td>
                                        <div class="action-group" style="padding-right: 15px;">
                                            <button class="btn-action btn-edit" onclick="toggleEdit(this)">Edit</button>
                                            <button class="btn-action btn-remove" onclick="removeSubject(this)">Remove</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div id="searchEmptyState" class="empty-message" style="display: none;"></div>
                    <?php else: ?>
                        <div class="empty-message">No subjects are currently configured in the catalog.</div>
                    <?php endif; ?>
                </div>
            </div>
        </main>

    </div>

    <script>
        function filterSubjects() {
            const query = document.getElementById('subjectSearch').value.toLowerCase().trim();
            const table = document.getElementById('subjectTable');
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
                emptyState.textContent = `No subjects found matching "${document.getElementById('subjectSearch').value}"`;
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
            const idCell = row.querySelector('.subject-id');
            const nameCell = row.querySelector('.subject-name');
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
            const idCell = row.querySelector('.subject-id');
            const nameCell = row.querySelector('.subject-name');
            const actionGroup = row.querySelector('.action-group');

            idCell.textContent = row.getAttribute('data-original-id');
            nameCell.textContent = row.getAttribute('data-original-name');

            actionGroup.innerHTML = `
                <button class="btn-action btn-edit" onclick="toggleEdit(this)">Edit</button>
                <button class="btn-action btn-remove" onclick="removeSubject(this)">Remove</button>
            `;
        }

        function saveChanges(button) {
            const row = button.closest('tr');
            const oldCode = row.getAttribute('data-original-id');
            const oldName = row.getAttribute('data-original-name');

            const newCode = row.querySelector('.id-field').value.trim();
            const newName = row.querySelector('.name-field').value.trim();

            if (!newCode || !newName) {
                alert("Fields cannot be saved completely empty.");
                return;
            }

            if (newCode === oldCode && newName === oldName) {
                alert("Error: No changes were detected to save.");
                return;
            }

            if (!confirm("Are you sure you want to make changes?")) {
                return; 
            }

            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('old_code', oldCode);
            formData.append('new_code', newCode);
            formData.append('new_title', newName);

            fetch('manageSubjectsAdmin.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    row.setAttribute('data-original-id', newCode);
                    row.setAttribute('data-original-name', newName);

                    row.querySelector('.subject-id').textContent = newCode;
                    row.querySelector('.subject-name').textContent = newName;

                    row.querySelector('.action-group').innerHTML = `
                        <button class="btn-action btn-edit" onclick="toggleEdit(this)">Edit</button>
                        <button class="btn-action btn-remove" onclick="removeSubject(this)">Remove</button>
                    `;

                    alert("Success! Subject records have been updated in phpMyAdmin.");
                    filterSubjects();
                } else {
                    alert("Database Save Error: " + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert("Network Error: Could not connect to write update.");
            });
        }

        function removeSubject(button) {
            const row = button.closest('tr');
            const subjectCode = row.querySelector('.subject-id').textContent.trim();

            if (confirm(`Are you sure you want to completely delete subject ${subjectCode} from the database?`)) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('subject_code', subjectCode);

                fetch('manageSubjectsAdmin.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        row.remove();
                        alert("Subject successfully removed from the database.");

                        const table = document.getElementById('subjectTable');
                        const remainingRows = table.querySelectorAll('tbody tr');
                        if (remainingRows.length === 0) {
                            const container = document.getElementById('tableContainer');
                            container.innerHTML = '<div class="empty-message">No subjects are currently configured in the catalog.</div>';
                        } else {
                            filterSubjects();
                        }
                    } else {
                        alert("Database Error: Could not delete subject. " + data.error);
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