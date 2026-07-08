<?php
// --- DATABASE CONFIGURATION ---
$host = "100.81.48.34";
$port = "3307";          // Explicitly targets your port 3307 setup
$dbname = "fictlp db";  // Matches your exact database layout container
$username = "bubustailo"; 
$password = "Student@123";

// ==========================================
// HANDLES INLINE POST ACTIONS (DELETE / UPDATE / INSERT)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    // --- ACTION: INSERT (ADD NEW STUDENT) ---
    if ($_POST['action'] === 'insert') {
        $studentId = $_POST['student_id'] ?? '';
        $name = $_POST['name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $email = $_POST['email'] ?? '';
        $passwordInput = $_POST['password'] ?? '';
        
        if (empty($studentId) || empty($name) || empty($email) || empty($passwordInput)) {
            echo json_encode(['success' => false, 'error' => 'Missing required fields.']);
            exit;
        }
        
        try {
            $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // MATCHED TO DATABASE: Insert student with default Role='Student' and Active user_status=0
            $stmt = $conn->prepare("INSERT INTO user (userID, Name, Phone, Email, Password, Role, user_status) VALUES (:student_id, :name, :phone, :email, :password, 'Student', 0)");
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_STR);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':password', $passwordInput, PDO::PARAM_STR);
            $stmt->execute();
            
            echo json_encode(['success' => true]);
            exit;
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    // --- ACTION: DELETE ---
    if ($_POST['action'] === 'delete') {
        $studentId = $_POST['student_id'] ?? '';
        try {
            $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
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
    
    // --- ACTION: UPDATE (Restricted to Phone & Password) ---
    if ($_POST['action'] === 'update') {
        $studentId = $_POST['student_id'] ?? '';
        $newPhone = $_POST['new_phone'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        
        try {
            $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Administrative rules: Updates only Phone and Password credentials
            $stmt = $conn->prepare("UPDATE user SET Phone = :new_phone, Password = :new_password WHERE userID = :student_id AND Role = 'Student'");
            $stmt->bindParam(':new_phone', $newPhone, PDO::PARAM_STR);
            $stmt->bindParam(':new_password', $newPassword, PDO::PARAM_STR);
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_STR);
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
    
    $stmt = $conn->prepare("SELECT userID AS student_id, Name AS name, user_status AS status, Phone AS phone, Email AS email, Password AS password FROM user WHERE Role = 'Student'");
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

        <main class="main-content"> <?php include 'manage_nav.php'; ?>
            <h1>Manage Students</h1>
            <div class="subtitle">Review academic admission rosters, system status, and student credentials:</div>

            <div class="controls-row">
                <div class="search-wrapper">
                    <div class="search-icon"></div>
                    <input type="text" id="studentSearch" class="search-input" placeholder="Search by Student ID, Name, Phone, or Email..." onkeyup="filterStudents()">
                </div>
                
                <button type="button" id="selectModeBtn" class="btn-select-toggle" onclick="toggleSelectMode()">Select</button>
                <button class="btn-add-trigger" id="addStudentTriggerBtn" onclick="openAddModal()">+ Add Student</button>
            </div>

            <div id="bulkActionBar" class="bulk-action-bar">
                <span id="bulkCountText" style="font-weight: bold; color: #1a253c;">0 students selected</span>
                <div class="bulk-btn-group">
                    <button type="button" class="btn-bulk-action btn-bulk-edit" onclick="openBulkEditModal()">Edit Selected</button>
                    <button type="button" class="btn-bulk-action btn-bulk-suspend" onclick="suspendBulkStudents()">Suspend Selected</button>
                </div>
            </div>

            <div class="table-container" id="tableContainer">
                <div class="table-card-wrapper">
                    <?php if (count($students) > 0): ?>
                    <table class="student-table" id="studentTable">
                        <thead>
                            <tr id="tableHeaderRow">
                                <th class="col-checkbox-header"><input type="checkbox" id="selectAllBox" onchange="toggleSelectAllRows(this)"></th>
                                <th class="col-id">Student ID</th>
                                <th class="col-name">Name</th>
                                <th class="col-email">Email</th>
                                <th class="col-phone">Phone</th>
                                <th class="col-status">Status</th>
                                <th class="col-actions" style="text-align: right; padding-right: 45px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="studentTableBody">
                            <?php foreach ($students as $row): ?>
                                <?php 
                                    $isOnline = (int)$row['status'] === 1;
                                    $statusText = $isOnline ? 'Online' : 'Offline';
                                    $badgeClass = $isOnline ? 'Online-status' : 'Offline-status';
                                ?>
                                <tr data-original-id="<?php echo htmlspecialchars($row['student_id']); ?>" 
                                    data-original-name="<?php echo htmlspecialchars($row['name']); ?>"
                                    data-original-email="<?php echo htmlspecialchars($row['email'] ?? ''); ?>"
                                    data-original-phone="<?php echo htmlspecialchars($row['phone'] ?? ''); ?>"
                                    data-original-password="<?php echo htmlspecialchars($row['password'] ?? ''); ?>">
                                    
                                    <td class="cell-checkbox-container">
                                        <input type="checkbox" class="student-checkbox" value="<?php echo htmlspecialchars($row['student_id']); ?>" onchange="updateSelectedCount()">
                                    </td>
                                    <td class="student-id"><?php echo htmlspecialchars($row['student_id']); ?></td>
                                    <td class="student-name"><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td class="student-email"><?php echo htmlspecialchars($row['email'] ?? ''); ?></td>
                                    <td class="student-phone"><?php echo htmlspecialchars($row['phone'] ?? ''); ?></td>
                                    <td class="student-status">
                                        <span class="status-badge <?php echo $badgeClass; ?>"><strong><?php echo $statusText; ?></strong></span>
                                    </td>
                                    <td>
                                        <div class="action-group" style="padding-right: 15px;">
                                            <button class="btn-action btn-edit inline-action-btn" onclick="openEditModal(this)">Edit</button>
                                            <button class="btn-action btn-suspend inline-action-btn" onclick="suspendStudent(this)">Suspend</button>
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

    <div class="modal-overlay" id="addStudentModal">
        <div class="modal-card">
            <h2>Add New Student</h2>
            <div class="modal-scroll-content">
                <form id="addStudentForm" onsubmit="saveNewStudent(event)">
                    <div class="form-group">
                        <label>Student ID</label>
                        <input type="text" id="add_id" required placeholder="e.g. D032410021">
                    </div>
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" id="add_name" required placeholder="Full Name">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="add_email" required placeholder="name@student.utem.edu.my">
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" id="add_password" required placeholder="Password credentials">
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" id="add_phone" placeholder="e.g. 012-3456789">
                    </div>
                </form>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-action btn-cancel" onclick="closeAddModal()">Cancel</button>
                <button type="button" class="btn-action btn-save" onclick="document.getElementById('addStudentForm').requestSubmit()">Add Student</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="editStudentModal">
        <div class="modal-card">
            <h2 id="editModalHeader">Edit Student Credentials</h2>
            <div class="modal-scroll-content">
                <form id="editStudentForm" onsubmit="saveEditedStudent(event)">
                    <div id="editModalDynamicContainer"></div>
                </form>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-action btn-cancel" onclick="closeEditModal()">Cancel</button>
                <button type="button" class="btn-action btn-save" onclick="document.getElementById('editStudentForm').requestSubmit()">Save Changes</button>
            </div>
        </div>
    </div>

    <script>
        let isSelectModeActive = false;

        // Toggle layouts and dynamically disable/enable inline controls based on Selection mode state
        function toggleSelectMode() {
            isSelectModeActive = !isSelectModeActive;
            const toggleBtn = document.getElementById('selectModeBtn');
            const actionBar = document.getElementById('bulkActionBar');
            const addBtn = document.getElementById('addStudentTriggerBtn');
            
            const headerCheckbox = document.querySelector('.col-checkbox-header');
            const cellContainers = document.querySelectorAll('.cell-checkbox-container');
            const inlineActionButtons = document.querySelectorAll('.inline-action-btn');

            if (isSelectModeActive) {
                toggleBtn.textContent = "Cancel";
                toggleBtn.classList.add('active-mode');
                actionBar.style.display = 'flex';
                headerCheckbox.style.display = 'table-cell';
                cellContainers.forEach(el => el.style.display = 'table-cell');
                
                // Temporarily disable row action controls and the "+ Add Student" trigger
                addBtn.disabled = true;
                addBtn.style.opacity = '0.5';
                addBtn.style.pointerEvents = 'none';
                inlineActionButtons.forEach(btn => btn.disabled = true);
            } else {
                toggleBtn.textContent = "Select";
                toggleBtn.classList.remove('active-mode');
                actionBar.style.display = 'none';
                headerCheckbox.style.display = 'none';
                cellContainers.forEach(el => el.style.display = 'none');
                
                // Re-enable row action controls and the "+ Add Student" trigger
                addBtn.disabled = false;
                addBtn.style.opacity = '1';
                addBtn.style.pointerEvents = 'auto';
                inlineActionButtons.forEach(btn => btn.disabled = false);
                
                document.getElementById('selectAllBox').checked = false;
                document.querySelectorAll('.student-checkbox').forEach(cb => cb.checked = false);
                updateSelectedCount();
            }
        }

        function toggleSelectAllRows(master) {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(cb => {
                if (cb.closest('tr').style.display !== 'none') {
                    cb.checked = master.checked;
                }
            });
            updateSelectedCount();
        }

        function updateSelectedCount() {
            const count = document.querySelectorAll('.student-checkbox:checked').length;
            document.getElementById('bulkCountText').textContent = `${count} student(s) selected`;
        }

        function openBulkEditModal() {
            const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert("Please check at least one student to edit.");
                return;
            }

            const container = document.getElementById('editModalDynamicContainer');
            container.innerHTML = ""; 

            document.getElementById('editModalHeader').textContent = "Bulk Edit Student Records";

            checkedBoxes.forEach((cb, index) => {
                const row = cb.closest('tr');
                const id = row.getAttribute('data-original-id');
                const name = row.getAttribute('data-original-name');
                const email = row.getAttribute('data-original-email');
                const phone = row.getAttribute('data-original-phone');
                const password = row.getAttribute('data-original-password');

                const blockHtml = `
                    <div class="bulk-student-block" data-index="${index}">
                        <h3>Student #${index + 1}: ${name} (${id})</h3>
                        <input type="hidden" class="bulk-target-id" value="${id}">
                        
                        <div class="form-group">
                            <label>Student ID</label>
                            <input type="text" value="${id}" class="readonly-field" readonly>
                        </div>
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" value="${name}" class="readonly-field" readonly>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="text" value="${email}" class="readonly-field" readonly>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="text" class="bulk-password" value="${password}" required>
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" class="bulk-phone" value="${phone}">
                        </div>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', blockHtml);
            });

            document.getElementById('editStudentModal').style.display = 'flex';
        }

        function openEditModal(button) {
            const row = button.closest('tr');
            const id = row.getAttribute('data-original-id');
            const name = row.getAttribute('data-original-name');
            const email = row.getAttribute('data-original-email');
            const phone = row.getAttribute('data-original-phone');
            const password = row.getAttribute('data-original-password');

            document.getElementById('editModalHeader').textContent = "Edit Student Credentials";
            const container = document.getElementById('editModalDynamicContainer');

            container.innerHTML = `
                <input type="hidden" id="single_edit_target_id" value="${id}">
                <div class="form-group">
                    <label>Student ID</label>
                    <input type="text" value="${id}" class="readonly-field" readonly>
                </div>
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" value="${name}" class="readonly-field" readonly>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="text" value="${email}" class="readonly-field" readonly>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="text" id="single_edit_password" value="${password}" required>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" id="single_edit_phone" value="${phone}">
                </div>
            `;

            document.getElementById('editStudentModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editStudentModal').style.display = 'none';
            document.getElementById('editStudentForm').reset();
            document.getElementById('editModalDynamicContainer').innerHTML = "";
        }

        function saveEditedStudent(e) {
            e.preventDefault();
            const bulkBlocks = document.querySelectorAll('.bulk-student-block');

            if (bulkBlocks.length > 0) {
                if (!confirm(`Are you sure you want to update all ${bulkBlocks.length} modified student configurations?`)) return;

                const promises = Array.from(bulkBlocks).map(block => {
                    const studentId = block.querySelector('.bulk-target-id').value;
                    const newPassword = block.querySelector('.bulk-password').value.trim();
                    const newPhone = block.querySelector('.bulk-phone').value.trim();

                    const formData = new FormData();
                    formData.append('action', 'update');
                    formData.append('student_id', studentId);
                    formData.append('new_password', newPassword);
                    formData.append('new_phone', newPhone);

                    return fetch('manageStudentsAdmin.php', { method: 'POST', body: formData }).then(res => res.json());
                });

                Promise.all(promises).then(() => {
                    alert("All student data profiles updated completely!");
                    window.location.reload();
                });
            } else {
                const studentId = document.getElementById('single_edit_target_id').value;
                const newPassword = document.getElementById('single_edit_password').value.trim();
                const newPhone = document.getElementById('single_edit_phone').value.trim();

                const formData = new FormData();
                formData.append('action', 'update');
                formData.append('student_id', studentId);
                formData.append('new_password', newPassword);
                formData.append('new_phone', newPhone);

                fetch('manageStudentsAdmin.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert("Student updated successfully!");
                        window.location.reload();
                    } else {
                        alert("Error updating record: " + data.error);
                    }
                });
            }
        }

        function suspendBulkStudents() {
            const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert("Please select at least one student to suspend.");
                return;
            }

            if (!confirm(`Are you sure you want to completely suspend and delete the ${checkedBoxes.length} selected student(s) from the database?`)) {
                return;
            }

            const promises = Array.from(checkedBoxes).map(cb => {
                const studentId = cb.value;
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('student_id', studentId);

                return fetch('manageStudentsAdmin.php', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => ({ id: studentId, success: data.success }));
            });

            Promise.all(promises).then(results => {
                const failed = results.filter(r => !r.success);
                if (failed.length === 0) {
                    alert("Selected batch profiles successfully removed from system.");
                    window.location.reload();
                } else {
                    alert(`Batch executed with warnings. System failed processing IDs: ${failed.map(f => f.id).join(', ')}`);
                }
            });
        }

        function openAddModal() {
            document.getElementById('addStudentModal').style.display = 'flex';
        }

        function closeAddModal() {
            document.getElementById('addStudentModal').style.display = 'none';
            document.getElementById('addStudentForm').reset();
        }

        function saveNewStudent(e) {
            e.preventDefault();
            const formData = new FormData();
            formData.append('action', 'insert');
            formData.append('student_id', document.getElementById('add_id').value.trim());
            formData.append('name', document.getElementById('add_name').value.trim());
            formData.append('email', document.getElementById('add_email').value.trim());
            formData.append('password', document.getElementById('add_password').value.trim());
            formData.append('phone', document.getElementById('add_phone').value.trim());

            fetch('manageStudentsAdmin.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) { alert("Student successfully added!"); window.location.reload(); }
                else { alert("Database Save Error: " + data.error); }
            });
        }

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
                let phoneText = row.getAttribute('data-original-phone').toLowerCase();
                let emailText = row.getAttribute('data-original-email').toLowerCase();

                if (idText.includes(query) || nameText.includes(query) || phoneText.includes(query) || emailText.includes(query)) {
                    row.style.display = '';
                    matchesFound++;
                } else {
                    row.style.display = 'none';
                    const cb = row.querySelector('.student-checkbox');
                    if(cb) cb.checked = false;
                }
            });
            updateSelectedCount();

            if (matchesFound === 0) {
                headerRow.style.display = 'none';
                emptyState.textContent = `No students found matching "${document.getElementById('studentSearch').value}"`;
                emptyState.style.display = 'block';
            } else {
                headerRow.style.display = '';
                emptyState.style.display = 'none';
            }
        }

        function suspendStudent(button) {
            if(button.disabled) return;
            const row = button.closest('tr');
            const studentId = row.querySelector('.student-id').textContent.trim();

            if (confirm(`Are you sure you want to completely delete student ${studentId} from the database?`)) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('student_id', studentId);

                fetch('manageStudentsAdmin.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        row.remove();
                        alert("Student successfully removed from the database.");
                    } else {
                        alert("Database Error: " + data.error);
                    }
                });
            }
        }
        
    </script>
</body>
</html>