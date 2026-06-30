<?php
// --- DATABASE CONFIGURATION ---
$host = "100.81.48.34";
$port = "3307";          
$dbname = "fictlp db";  
$username = "bubustailo"; 
$password = "Student@123";


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    // --- ACTION: SUSPEND (DELETE) ---
    if ($_POST['action'] === 'delete') {
        $lecturerId = $_POST['lecturer_id'] ?? '';
        try {
            $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // MATCHED TO DATABASE: table 'user', column 'userID' filtering for Role='Lecturer'
            $stmt = $conn->prepare("DELETE FROM user WHERE userID = :lecturer_id AND Role = 'Lecturer'");
            $stmt->bindParam(':lecturer_id', $lecturerId, PDO::PARAM_STR);
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
        $newStatus = ($_POST['new_status'] === 'Active') ? 1 : 0;
        
        try {
            $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $conn->prepare("UPDATE user SET userID = :new_id, Name = :new_name, user_status = :new_status WHERE userID = :old_id AND Role = 'Lecturer'");
            $stmt->bindParam(':new_id', $newId, PDO::PARAM_STR);
            $stmt->bindParam(':new_name', $newName, PDO::PARAM_STR);
            $stmt->bindParam(':new_status', $newStatus, PDO::PARAM_INT);
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

// --- MAIN FETCH DATA FOR THE ROSTER ---
try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // FETCH userID, Name, user_status filtering for Role='Lecturer'
    $stmt = $conn->prepare("SELECT userID AS lecturer_id, Name AS name, user_status AS status FROM user WHERE Role = 'Lecturer'");
    $stmt->execute();
    $lecturers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CoreKnowledge - Manage Lecturers(Admin)</title>
    <link rel="stylesheet" href="manageLecturersAdmin.css">
</head>
<body>
    <?php include("sidebarAdmin.php"); ?>
    <div class="window-frame">

        <main class="main-content">
            <h1>Manage Lecturers</h1>
            <div class="subtitle">Review academic instructor rosters, authorization levels, and profile statuses:</div>

            <div class="search-wrapper">
                <div class="search-icon"></div>
                <input type="text" id="lecturerSearch" class="search-input" placeholder="Search by Lecturer ID or Name..." onkeyup="filterLecturers()">
            </div>

            <div class="table-container" id="tableContainer">
                <div class="table-card-wrapper">
                    <?php if (count($lecturers) > 0): ?>
                    <table class="lecturer-table" id="lecturerTable">
                        <thead>
                            <tr id="tableHeaderRow">
                                <th class="col-id">Lecturer ID</th>
                                <th class="col-name">Name</th>
                                <th class="col-status">Status</th>
                                <th class="col-actions" style="text-align: right; padding-right: 45px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="lecturerTableBody">
                            <?php foreach ($lecturers as $row): ?>
                                <?php 
                                    $isActive = (int)$row['status'] === 1;
                                    $statusText = $isActive ? 'Active' : 'Inactive';
                                    $badgeClass = $isActive ? 'active-status' : 'inactive-status';
                                ?>
                                <tr data-original-id="<?php echo htmlspecialchars($row['lecturer_id']); ?>" 
                                    data-original-name="<?php echo htmlspecialchars($row['name']); ?>"
                                    data-original-status="<?php echo $statusText; ?>">
                                    <td class="lecturer-id"><?php echo htmlspecialchars($row['lecturer_id']); ?></td>
                                    <td class="lecturer-name"><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td class="lecturer-status">
                                        <span class="status-badge <?php echo $badgeClass; ?>"><?php echo $statusText; ?></span>
                                    </td>
                                    <td>
                                        <div class="action-group" style="padding-right: 15px;">
                                            <button class="btn-action btn-edit" onclick="toggleEdit(this)">Edit</button>
                                            <button class="btn-action btn-suspend" onclick="suspendLecturer(this)">Suspend</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div id="searchEmptyState" class="empty-message" style="display: none;"></div>
                    <?php else: ?>
                        <div class="empty-message">No active lecturer credentials found on the system server.</div>
                    <?php endif; ?>
                </div>
            </div>
        </main>

    </div>

    <script>
        function filterLecturers() {
            const query = document.getElementById('lecturerSearch').value.toLowerCase().trim();
            const table = document.getElementById('lecturerTable');
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
                emptyState.textContent = `No lecturers found matching "${document.getElementById('lecturerSearch').value}"`;
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
            const idCell = row.querySelector('.lecturer-id');
            const nameCell = row.querySelector('.lecturer-name');
            const statusCell = row.querySelector('.lecturer-status');
            const actionGroup = row.querySelector('.action-group');

            if (button.classList.contains('btn-edit')) {
                const currentId = idCell.textContent.trim();
                const currentName = nameCell.textContent.trim();
                const currentStatus = statusCell.textContent.trim();

                idCell.innerHTML = `<input type="text" class="edit-input id-field" value="${currentId}">`;
                nameCell.innerHTML = `<input type="text" class="edit-input name-field" value="${currentName}">`;
                statusCell.innerHTML = `
                    <select class="edit-select status-field">
                        <option value="Active" ${currentStatus === 'Active' ? 'selected' : ''}>Active</option>
                        <option value="Inactive" ${currentStatus === 'Inactive' ? 'selected' : ''}>Inactive</option>
                    </select>
                `;
                
                actionGroup.innerHTML = `
                    <button class="btn-action btn-save" onclick="saveChanges(this)">Save</button>
                    <button class="btn-action btn-cancel" onclick="cancelEdit(this)">Cancel</button>
                `;
            }
        }

        function cancelEdit(button) {
            const row = button.closest('tr');
            const idCell = row.querySelector('.lecturer-id');
            const nameCell = row.querySelector('.lecturer-name');
            const statusCell = row.querySelector('.lecturer-status');
            const actionGroup = row.querySelector('.action-group');

            const origStatus = row.getAttribute('data-original-status');
            const badgeClass = origStatus === 'Active' ? 'active-status' : 'inactive-status';

            idCell.textContent = row.getAttribute('data-original-id');
            nameCell.textContent = row.getAttribute('data-original-name');
            statusCell.innerHTML = `<span class="status-badge ${badgeClass}">${origStatus}</span>`;

            actionGroup.innerHTML = `
                <button class="btn-action btn-edit" onclick="toggleEdit(this)">Edit</button>
                <button class="btn-action btn-suspend" onclick="suspendLecturer(this)">Suspend</button>
            `;
        }

        function saveChanges(button) {
            const row = button.closest('tr');
            const oldId = row.getAttribute('data-original-id');
            const oldName = row.getAttribute('data-original-name');
            const oldStatus = row.getAttribute('data-original-status');

            const newId = row.querySelector('.id-field').value.trim();
            const newName = row.querySelector('.name-field').value.trim();
            const newStatus = row.querySelector('.status-field').value;

            if (!newId || !newName) {
                alert("Fields cannot be saved completely empty.");
                return;
            }

            if (newId === oldId && newName === oldName && newStatus === oldStatus) {
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
            formData.append('new_status', newStatus);

            fetch('manageLecturers.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    row.setAttribute('data-original-id', newId);
                    row.setAttribute('data-original-name', newName);
                    row.setAttribute('data-original-status', newStatus);

                    row.querySelector('.lecturer-id').textContent = newId;
                    row.querySelector('.lecturer-name').textContent = newName;

                    const badgeClass = newStatus === 'Active' ? 'active-status' : 'inactive-status';
                    row.querySelector('.lecturer-status').innerHTML = `<span class="status-badge ${badgeClass}">${newStatus}</span>`;

                    row.querySelector('.action-group').innerHTML = `
                        <button class="btn-action btn-edit" onclick="toggleEdit(this)">Edit</button>
                        <button class="btn-action btn-suspend" onclick="suspendLecturer(this)">Suspend</button>
                    `;

                    alert("Success! Lecturer records have been updated in phpMyAdmin.");
                    filterLecturers();
                } else {
                    alert("Database Save Error: " + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert("Network Error: Could not connect to write update.");
            });
        }

        function suspendLecturer(button) {
            const row = button.closest('tr');
            const lecturerId = row.querySelector('.lecturer-id').textContent.trim();

            if (confirm(`Are you sure you want to completely delete lecturer ${lecturerId} from the database?`)) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('lecturer_id', lecturerId);

                fetch('manageLecturers.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        row.remove();
                        alert("Lecturer successfully removed from the database.");

                        const table = document.getElementById('lecturerTable');
                        const remainingRows = table.querySelectorAll('tbody tr');
                        if (remainingRows.length === 0) {
                            const container = document.getElementById('tableContainer');
                            container.innerHTML = '<div class="empty-message">No active lecturer credentials found on the system server.</div>';
                        } else {
                            filterLecturers();
                        }
                    } else {
                        alert("Database Error: Could not delete lecturer profile. " + data.error);
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