<?php
// --- DATABASE CONFIGURATION ---
$host = "100.81.48.34";
$port = "3307";          
$dbname = "fictlp db";  
$username = "bubustailo"; 
$password = "Student@123";

// ==========================================
// HANDLES INLINE POST ACTIONS (DELETE / UPDATE)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    // --- ACTION: DELETE ---
    if ($_POST['action'] === 'delete') {
        $subjectCode = $_POST['subject_code'] ?? '';
        try {
            $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // MATCHED TO DATABASE: table 'subject', column 'Subject_Code'
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
                Subject_Code AS subject_code, 
                Title AS title,
                COUNT(*) AS enrolled 
              FROM subject 
              GROUP BY Subject_Code, Title";
              
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
    <style>
        /* Reset and Full Screen Layout Base */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #ffffff;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            display: flex;
        }

        .window-frame {
            width: 100%;
            height: 100%;
            background-color: #ffffff;
            display: flex;
            overflow: hidden;
        }

        /* --- SIDEBAR NAVIGATION CORE --- */
        .sidebar {
            width: 15%;
            min-width: 120px;
            max-width: 160px;
            background-color: #ffffff;
            border-right: 1px solid #dcdcdc;
            display: flex;
            flex-direction: column;
            padding-top: 20px;
            align-items: center;
            flex-shrink: 0;
            height: 100%;
        }

        .sidebar nav {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .nav-item {
            width: 100%;
            height: 85px; 
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 10px 5px;
            cursor: pointer;
            text-decoration: none;
            color: #000000;
            background: none;
            border: none;
            text-align: center;
            transition: background-color 0.15s ease;
        }

        .nav-item.active {
            background-color: #d9d9d9;
        }

        .nav-item:hover:not(.active) {
            background-color: #f5f5f5;
        }

        .icon {
            width: 24px;
            height: 24px;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            flex-shrink: 0;
        }

        .icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .nav-item .label {
            font-size: 11px;
            font-weight: bold;
            line-height: 1.2;
            display: block;
            width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* --- MAIN DASHBOARD CONTENT --- */
        .main-content {
            flex: 1;
            background-color: #fdf8f5; 
            padding: 40px 60px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            overflow: hidden; 
            height: 100%;
        }

        .main-content h1 {
            font-size: 42px;
            font-family: Georgia, serif;
            font-weight: bold;
            color: #1a253c;
            margin-bottom: 4px;
        }

        .main-content .subtitle {
            font-size: 14px;
            color: #d88267;
            margin-bottom: 25px;
        }

        /* --- STRETCHED CONTROLS AND CONTAINERS --- */
        .search-wrapper {
            width: 92%; 
            margin-bottom: 20px;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 14px 20px 14px 50px;
            font-family: inherit;
            font-size: 15px;
            color: #1a253c;
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            outline: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.015);
            transition: all 0.2s ease;
        }

        .search-input:focus {
            border-color: #cbe3cc;
            box-shadow: 0 4px 16px rgba(203, 227, 204, 0.25);
        }

        .search-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            border: 2px solid #a0a0a0;
            border-radius: 50%;
            pointer-events: none;
        }

        .search-icon::after {
            content: '';
            position: absolute;
            right: -5px;
            bottom: -5px;
            width: 2px;
            height: 7px;
            background-color: #a0a0a0;
            transform: rotate(-45deg);
        }

        /* --- FULL SCREEN ADAPTIVE LAYOUT CONTAINER --- */
        .table-container {
            width: 92%; 
            background-color: #ffffff;
            border-radius: 18px; 
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.04), 0 4px 12px rgba(0, 0, 0, 0.02);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
        }

        .table-card-wrapper {
            width: 100%;
            max-height: calc(100vh - 250px); 
            overflow-y: auto;                
            overflow-x: auto;                
        }

        .subject-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 15px;
        }

        .subject-table th {
            position: sticky;
            top: 0;
            z-index: 10;
            background-color: #cbe3cc; 
            color: #1a253c;
            font-family: inherit;
            font-weight: bold;
            font-size: 16px;
            padding: 18px 30px; 
            border-bottom: none;
        }

        .subject-table td {
            padding: 18px 30px;
            color: #333333;
            border-bottom: 1px solid #f3f3f3;
            vertical-align: middle;
            height: 76px; 
        }

        .subject-table tbody tr:last-child td {
            border-bottom: none;
        }

        .col-id { width: 22%; }
        .col-name { width: 48%; }
        .col-enroll { width: 12%; }
        .col-actions { width: 18%; }

        .subject-id {
            font-weight: bold;
            color: #1a253c;
        }

        .action-group {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 20px;
        }

        .btn-action {
            font-family: inherit;
            font-size: 14px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            transition: opacity 0.15s ease;
            outline: none;
        }

        .btn-action:hover {
            opacity: 0.8;
        }

        .btn-edit {
            background: none;
            color: #1a253c;
            padding: 6px 12px;
        }

        .btn-save {
            background-color: #e2f0e3;
            color: #4a7c59;
            padding: 6px 16px;
            border-radius: 20px;
        }

        .btn-cancel {
            background: none;
            color: #6c757d;
            padding: 6px 12px;
        }

        .btn-remove {
            background-color: #eed6c5;
            color: #6e3716;
            padding: 6px 20px;
            border-radius: 20px;
        }

        .edit-input {
            width: 90%;
            padding: 8px 12px;
            border: 2px solid #cbe3cc;
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            color: #1a253c;
            outline: none;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);
            transition: border-color 0.15s ease;
        }

        .edit-input:focus {
            border-color: #4a7c59;
        }

        .empty-message {
            padding: 40px;
            text-align: center;
            color: #6c757d;
            font-style: italic;
        }

        .table-card-wrapper::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .table-card-wrapper::-webkit-scrollbar-track {
            background: transparent;
        }
        .table-card-wrapper::-webkit-scrollbar-thumb {
            background: #e0e0e0;
            border-radius: 10px;
        }
        .table-card-wrapper::-webkit-scrollbar-thumb:hover {
            background: #cbd3cb;
        }

        @media (max-width: 768px) {
            body { overflow-y: auto; }
            .window-frame { flex-direction: column; height: auto; min-height: 100vh; }
            .sidebar {
                width: 100%; max-width: 100%; height: auto; flex-direction: row;
                justify-content: space-around; padding-top: 0; border-right: none;
                border-bottom: 1px solid #dcdcdc; position: sticky; top: 0; z-index: 100;
            }
            .sidebar nav { flex-direction: row; width: 100%; justify-content: space-around; }
            .nav-item { width: auto; height: 70px; padding: 5px 10px; flex: 1; }
            .main-content { padding: 30px 20px; overflow: visible; }
            .search-wrapper, .table-container { width: 100%; }
            .table-card-wrapper { max-height: none; overflow-y: visible; }
            .main-content h1 { font-size: 32px; text-align: center; width: 100%; }
            .main-content .subtitle { text-align: center; width: 100%; margin-bottom: 25px; }
            .subject-table th, .subject-table td { padding: 12px 15px; font-size: 14px; }
            .subject-table th { position: static; }
            .action-group { gap: 10px; }
        }
    </style>
</head>
<body>

    <div class="window-frame">

        <aside class="sidebar">
            <nav>
                <div class="nav-item" data-page="logout" onclick="handleLogout(event)">
                    <div class="icon">
                        <img src="Aset/logOutBtn.svg" alt="Logout">
                    </div>
                    <span class="label">Log Out</span>
                </div>
           
                <div class="nav-item" data-page="home" onclick="window.location.href='mainPage.php'">
                    <div class="icon">
                        <img src="Aset/homeBtn.svg" alt="Home">
                    </div>
                    <span class="label">Home</span>
                </div>

                <div class="nav-item active" data-page="dashboard" onclick="window.location.href='dashboard.php'">
                    <div class="icon">
                        <img src="Aset/summaryBtn.svg" alt="Summary">
                    </div>
                    <span class="label">Information</span>
                </div>
        
                <div class="nav-item" data-page="announcement" onclick="window.location.href='announcement.php'">
                    <div class="icon">
                        <img src="Aset/annoucment.svg" alt="Announcement">
                    </div>
                    <span class="label">Announcement</span>
                </div>
            </nav>
        </aside>

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

            fetch('manageSubjects.php', {
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

                fetch('manageSubjects.php', {
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