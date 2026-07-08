<?php
// --- DATABASE CONFIGURATION ---
$host = "100.81.48.34";
$port = "3307";          
$dbname = "fictlp db";  
$username = "bubustailo"; 
$password = "Student@123";

// ==========================================
// HANDLES INLINE POST ACTIONS (DELETE / UPDATE / INSERT)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    // --- ACTION: INSERT (ADD NEW LECTURER) ---
    if ($_POST['action'] === 'insert') {
        $lecturerId = $_POST['lecturer_id'] ?? '';
        $name = $_POST['name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $email = $_POST['email'] ?? '';
        $passwordInput = $_POST['password'] ?? '';
        $subjects = isset($_POST['subjects']) ? json_decode($_POST['subjects'], true) : [];
        
        if (empty($lecturerId) || empty($name) || empty($email) || empty($passwordInput) || empty($subjects)) {
            echo json_encode(['success' => false, 'error' => 'Missing required fields. At least one subject must be assigned.']);
            exit;
        }
        
        try {
            $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $conn->beginTransaction();

            // MATCHED TO DATABASE: Insert lecturer with default Role='Lecturer' and Active user_status=0
            $stmt = $conn->prepare("INSERT INTO user (userID, Name, Phone, Email, Password, Role, user_status) VALUES (:lecturer_id, :name, :phone, :email, :password, 'Lecturer', 0)");
            $stmt->bindParam(':lecturer_id', $lecturerId, PDO::PARAM_STR);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':password', $passwordInput, PDO::PARAM_STR);
            $stmt->execute();
            
            // Map selected checked topics inside relational junction table
            $subStmt = $conn->prepare("INSERT INTO lecture_subject (userID, Subject_Code) VALUES (:user_id, :subject_code)");
            foreach ($subjects as $code) {
                $subStmt->bindValue(':user_id', $lecturerId, PDO::PARAM_STR);
                $subStmt->bindValue(':subject_code', $code, PDO::PARAM_STR);
                $subStmt->execute();
            }

            $conn->commit();
            echo json_encode(['success' => true]);
            exit;
        } catch(PDOException $e) {
            if ($conn->inTransaction()) { $conn->rollBack(); }
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    // --- ACTION: DELETE ---
    if ($_POST['action'] === 'delete') {
        $lecturerId = $_POST['lecturer_id'] ?? '';
        try {
            $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $conn->beginTransaction();

            // Wipe out allocated topics tracking assignments first
            $stmtJunction = $conn->prepare("DELETE FROM lecture_subject WHERE userID = :lecturer_id");
            $stmtJunction->bindParam(':lecturer_id', $lecturerId, PDO::PARAM_STR);
            $stmtJunction->execute();

            $stmt = $conn->prepare("DELETE FROM user WHERE userID = :lecturer_id AND Role = 'Lecturer'");
            $stmt->bindParam(':lecturer_id', $lecturerId, PDO::PARAM_STR);
            $stmt->execute();
            
            $conn->commit();
            echo json_encode(['success' => true]);
            exit;
        } catch(PDOException $e) {
            if ($conn->inTransaction()) { $conn->rollBack(); }
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
    
    // --- ACTION: UPDATE (Restricted to Phone, Password, and Assigned Subjects) ---
    if ($_POST['action'] === 'update') {
        $lecturerId = $_POST['lecturer_id'] ?? '';
        $newPhone = $_POST['new_phone'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $subjects = isset($_POST['subjects']) ? json_decode($_POST['subjects'], true) : [];
        
        if (empty($lecturerId) || empty($newPassword) || empty($subjects)) {
            echo json_encode(['success' => false, 'error' => 'Password and at least one subject assignment are mandatory fields.']);
            exit;
        }

        try {
            $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $conn->beginTransaction();

            // Administrative rules: Updates Phone and Password credentials
            $stmt = $conn->prepare("UPDATE user SET Phone = :new_phone, Password = :new_password WHERE userID = :lecturer_id AND Role = 'Lecturer'");
            $stmt->bindParam(':new_phone', $newPhone, PDO::PARAM_STR);
            $stmt->bindParam(':new_password', $newPassword, PDO::PARAM_STR);
            $stmt->bindParam(':lecturer_id', $lecturerId, PDO::PARAM_STR);
            $stmt->execute();
            
            // Clean slate wipe of prior subject junction mappings
            $stmtDel = $conn->prepare("DELETE FROM lecture_subject WHERE userID = :lecturer_id");
            $stmtDel->bindParam(':lecturer_id', $lecturerId, PDO::PARAM_STR);
            $stmtDel->execute();

            // Write newly verified active parameters securely 
            $subStmt = $conn->prepare("INSERT INTO lecture_subject (userID, Subject_Code) VALUES (:user_id, :subject_code)");
            foreach ($subjects as $code) {
                $subStmt->bindValue(':user_id', $lecturerId, PDO::PARAM_STR);
                $subStmt->bindValue(':subject_code', $code, PDO::PARAM_STR);
                $subStmt->execute();
            }

            $conn->commit();
            echo json_encode(['success' => true]);
            exit;
        } catch(PDOException $e) {
            if ($conn->inTransaction()) { $conn->rollBack(); }
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
}

// --- MAIN FETCH DATA FOR THE VIEW ROSTER ---
try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Core Lecturers Fetch
    $stmt = $conn->prepare("SELECT userID AS lecturer_id, Name AS name, user_status AS status, Phone AS phone, Email AS email, Password AS password FROM user WHERE Role = 'Lecturer'");
    $stmt->execute();
    $lecturers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all cataloged subject variables for choices mapping checkboxes
    $subjectStmt = $conn->prepare("SELECT Subject_Code, Title FROM subject");
    $subjectStmt->execute();
    $availableSubjects = $subjectStmt->fetchAll(PDO::FETCH_ASSOC);

    // Build map of currently linked topics mapped per individual lecturer
    $junctionStmt = $conn->prepare("SELECT userID, Subject_Code FROM lecture_subject");
    $junctionStmt->execute();
    $junctionRows = $junctionStmt->fetchAll(PDO::FETCH_ASSOC);

    $lecturerSubjectMap = [];
    foreach ($junctionRows as $jRow) {
        $lecturerSubjectMap[$jRow['userID']][] = $jRow['Subject_Code'];
    }
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

        <main class="main-content"> <?php include 'manage_nav.php'; ?>
            <h1>Manage Lecturers</h1>
            <div class="subtitle">Review academic instructor rosters, authorization levels, and profile statuses:</div>

            <div class="controls-row">
                <div class="search-wrapper">
                    <div class="search-icon"></div>
                    <input type="text" id="lecturerSearch" class="search-input" placeholder="Search by Lecturer ID, Name, Phone, or Email..." onkeyup="filterLecturers()">
                </div>
                
                <!-- Dynamic Select Activator Toggle Button Control -->
                <button type="button" id="selectModeBtn" class="btn-select-toggle" onclick="toggleSelectMode()">Select</button>
                <button class="btn-add-trigger" id="addLecturerTriggerBtn" onclick="openAddModal()">+ Add Lecturer</button>
            </div>

            <!-- Dynamic Multi-Selection Context Ribbon Toolstrip -->
            <div id="bulkActionBar" class="bulk-action-bar">
                <span id="bulkCountText" style="font-weight: bold; color: #1a253c;">0 lecturers selected</span>
                <div class="bulk-btn-group">
                    <button type="button" class="btn-bulk-action btn-bulk-edit" onclick="openBulkEditModal()">Edit Selected</button>
                    <button type="button" class="btn-bulk-action btn-bulk-suspend" onclick="suspendBulkLecturers()">Suspend Selected</button>
                </div>
            </div>

            <div class="table-container" id="tableContainer">
                <div class="table-card-wrapper">
                    <?php if (count($lecturers) > 0): ?>
                    <table class="lecturer-table" id="lecturerTable">
                        <thead>
                            <tr id="tableHeaderRow">
                                <th class="col-checkbox-header"><input type="checkbox" id="selectAllBox" onchange="toggleSelectAllRows(this)"></th>
                                <th class="col-id">Lecturer ID</th>
                                <th class="col-name">Name</th>
                                <th class="col-email">Email</th>
                                <th class="col-phone">Phone</th>
                                <th class="col-status">Status</th>
                                <th class="col-actions" style="text-align: right; padding-right: 45px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="lecturerTableBody">
                            <?php foreach ($lecturers as $row): ?>
                                <?php 
                                    $isActive = (int)$row['status'] === 1;
                                    $statusText = $isActive ? 'Online' : 'Offline';
                                    $badgeClass = $isActive ? 'Online-status' : 'Offline-status';
                                    
                                    // Map cataloged arrays assigned to individual instructor profiles
                                    $currentAssigned = $lecturerSubjectMap[$row['lecturer_id']] ?? [];
                                    $jsonSubjects = json_encode($currentAssigned);
                                ?>
                                <tr data-original-id="<?php echo htmlspecialchars($row['lecturer_id']); ?>" 
                                    data-original-name="<?php echo htmlspecialchars($row['name']); ?>"
                                    data-original-email="<?php echo htmlspecialchars($row['email'] ?? ''); ?>"
                                    data-original-phone="<?php echo htmlspecialchars($row['phone'] ?? ''); ?>"
                                    data-original-password="<?php echo htmlspecialchars($row['password'] ?? ''); ?>"
                                    data-original-subjects="<?php echo htmlspecialchars($jsonSubjects); ?>">
                                    
                                    <td class="cell-checkbox-container">
                                        <input type="checkbox" class="lecturer-checkbox" value="<?php echo htmlspecialchars($row['lecturer_id']); ?>" onchange="updateSelectedCount()">
                                    </td>
                                    <td class="lecturer-id"><?php echo htmlspecialchars($row['lecturer_id']); ?></td>
                                    <td class="lecturer-name"><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td class="lecturer-email"><?php echo htmlspecialchars($row['email'] ?? ''); ?></td>
                                    <td class="lecturer-phone"><?php echo htmlspecialchars($row['phone'] ?? ''); ?></td>
                                    <td class="lecturer-status">
                                        <span class="status-badge <?php echo $badgeClass; ?>"><?php echo $statusText; ?></span>
                                    </td>
                                    <td>
                                        <div class="action-group" style="padding-right: 15px;">
                                            <button class="btn-action btn-edit inline-action-btn" onclick="openEditModal(this)">Edit</button>
                                            <button class="btn-action btn-suspend inline-action-btn" onclick="suspendLecturer(this)">Suspend</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div id="searchEmptyState" class="empty-message" style="display: none;"></div>
                    <?php else: ?>
                        <div class="empty-message">No active lecturer profiles remain in this section.</div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Form View: Add Lecturer Layout -->
    <div class="modal-overlay" id="addLecturerModal">
        <div class="modal-card">
            <h2>Add New Lecturer</h2>
            <div class="modal-scroll-content">
                <form id="addLecturerForm" onsubmit="saveNewLecturer(event)">
                    <div class="form-group">
                        <label>Lecturer ID</label>
                        <input type="text" id="add_id" required placeholder="e.g. L1006001">
                    </div>
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" id="add_name" required placeholder="Full Name">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="add_email" required placeholder="lecturer@domain.com">
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" id="add_password" required placeholder="Password credentials">
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" id="add_phone" placeholder="e.g. +60178901234">
                    </div>
                    <div class="form-group">
                        <label>Register Subjects *</label>
                        <div class="checkbox-container">
                            <?php foreach ($availableSubjects as $subject): ?>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="registered_subjects[]" value="<?php echo htmlspecialchars($subject['Subject_Code']); ?>">
                                    <span><?php echo htmlspecialchars($subject['Subject_Code'] . " - " . $subject['Title']); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-action btn-cancel" onclick="closeAddModal()">Cancel</button>
                <button type="button" class="btn-action btn-save" onclick="document.getElementById('addLecturerForm').requestSubmit()">Add Lecturer</button>
            </div>
        </div>
    </div>

    <!-- Modal Form View: Single & Multi-Form Bulk Edit Unified Layout Structure -->
    <div class="modal-overlay" id="editLecturerModal">
        <div class="modal-card">
            <h2 id="editModalHeader">Edit Lecturer Credentials</h2>
            <div class="modal-scroll-content">
                <form id="editLecturerForm" onsubmit="saveEditedLecturer(event)">
                    <div id="editModalDynamicContainer"></div>
                </form>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-action btn-cancel" onclick="closeEditModal()">Cancel</button>
                <button type="button" class="btn-action btn-save" onclick="document.getElementById('editLecturerForm').requestSubmit()">Save Changes</button>
            </div>
        </div>
    </div>

    <!-- Hidden Server Blueprint Reference Container to serve baseline templates dynamically inside loops -->
    <div id="subjectTemplateSource" style="display:none;">
        <?php foreach ($availableSubjects as $subject): ?>
            <label class="checkbox-item">
                <input type="checkbox" value="<?php echo htmlspecialchars($subject['Subject_Code']); ?>">
                <span><?php echo htmlspecialchars($subject['Subject_Code'] . " - " . $subject['Title']); ?></span>
            </label>
        <?php endforeach; ?>
    </div>

    <script>
        let isSelectModeActive = false;

        // Toggle layouts and dynamically disable/enable inline controls based on Selection mode state
        function toggleSelectMode() {
            isSelectModeActive = !isSelectModeActive;
            const toggleBtn = document.getElementById('selectModeBtn');
            const actionBar = document.getElementById('bulkActionBar');
            const addBtn = document.getElementById('addLecturerTriggerBtn');
            
            const headerCheckbox = document.querySelector('.col-checkbox-header');
            const cellContainers = document.querySelectorAll('.cell-checkbox-container');
            const inlineActionButtons = document.querySelectorAll('.inline-action-btn');

            if (isSelectModeActive) {
                toggleBtn.textContent = "Cancel";
                toggleBtn.classList.add('active-mode');
                actionBar.style.display = 'flex';
                headerCheckbox.style.display = 'table-cell';
                cellContainers.forEach(el => el.style.display = 'table-cell');
                
                // Temporarily disable row action controls and the add trigger
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
                
                // Re-enable row action controls and the add trigger
                addBtn.disabled = false;
                addBtn.style.opacity = '1';
                addBtn.style.pointerEvents = 'auto';
                inlineActionButtons.forEach(btn => btn.disabled = false);
                
                document.getElementById('selectAllBox').checked = false;
                document.querySelectorAll('.lecturer-checkbox').forEach(cb => cb.checked = false);
                updateSelectedCount();
            }
        }

        function toggleSelectAllRows(master) {
            const checkboxes = document.querySelectorAll('.lecturer-checkbox');
            checkboxes.forEach(cb => {
                if (cb.closest('tr').style.display !== 'none') {
                    cb.checked = master.checked;
                }
            });
            updateSelectedCount();
        }

        function updateSelectedCount() {
            const count = document.querySelectorAll('.lecturer-checkbox:checked').length;
            document.getElementById('bulkCountText').textContent = `${count} lecturer(s) selected`;
        }

        function openBulkEditModal() {
            const checkedBoxes = document.querySelectorAll('.lecturer-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert("Please check at least one lecturer to edit.");
                return;
            }

            const container = document.getElementById('editModalDynamicContainer');
            container.innerHTML = ""; 

            document.getElementById('editModalHeader').textContent = "Bulk Edit Lecturer Records";

            const templateMarkup = document.getElementById('subjectTemplateSource').innerHTML;

            checkedBoxes.forEach((cb, index) => {
                const row = cb.closest('tr');
                const id = row.getAttribute('data-original-id');
                const name = row.getAttribute('data-original-name');
                const email = row.getAttribute('data-original-email');
                const phone = row.getAttribute('data-original-phone');
                const password = row.getAttribute('data-original-password');
                const activeSubjects = JSON.parse(row.getAttribute('data-original-subjects') || '[]');

                const blockHtml = `
                    <div class="bulk-lecturer-block" data-index="${index}">
                        <h3>Lecturer #${index + 1}: ${name} (${id})</h3>
                        <input type="hidden" class="bulk-target-id" value="${id}">
                        
                        <div class="form-group">
                            <label>Lecturer ID</label>
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
                        <div class="form-group">
                            <label>Assign Subjects *</label>
                            <div class="checkbox-container bulk-subjects-group">
                                ${templateMarkup}
                            </div>
                        </div>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', blockHtml);

                // Map assigned items on checkboxes rendered for individual index loop block explicitly
                const renderedBlock = container.querySelector(`.bulk-lecturer-block[data-index="${index}"]`);
                const checkboxes = renderedBlock.querySelectorAll('.bulk-subjects-group input[type="checkbox"]');
                checkboxes.forEach(box => {
                    box.name = `bulk_subjects_${index}[]`;
                    if (activeSubjects.includes(box.value)) {
                        box.checked = true;
                    }
                });
            });

            document.getElementById('editLecturerModal').style.display = 'flex';
        }

        function openEditModal(button) {
            const row = button.closest('tr');
            const id = row.getAttribute('data-original-id');
            const name = row.getAttribute('data-original-name');
            const email = row.getAttribute('data-original-email');
            const phone = row.getAttribute('data-original-phone');
            const password = row.getAttribute('data-original-password');
            const activeSubjects = JSON.parse(row.getAttribute('data-original-subjects') || '[]');

            document.getElementById('editModalHeader').textContent = "Edit Lecturer Credentials";
            const container = document.getElementById('editModalDynamicContainer');

            const templateMarkup = document.getElementById('subjectTemplateSource').innerHTML;

            container.innerHTML = `
                <input type="hidden" id="single_edit_target_id" value="${id}">
                <div class="form-group">
                    <label>Lecturer ID</label>
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
                <div class="form-group">
                    <label>Assign Subjects *</label>
                    <div class="checkbox-container" id="singleEditSubjectsGroup">
                        ${templateMarkup}
                    </div>
                </div>
            `;

            // Check matching courses checkboxes inside the wrapper block
            const checkboxes = container.querySelectorAll('#singleEditSubjectsGroup input[type="checkbox"]');
            checkboxes.forEach(box => {
                box.name = "single_edit_subjects[]";
                if (activeSubjects.includes(box.value)) {
                    box.checked = true;
                }
            });

            document.getElementById('editLecturerModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editLecturerModal').style.display = 'none';
            document.getElementById('editLecturerForm').reset();
            document.getElementById('editModalDynamicContainer').innerHTML = "";
        }

        function saveEditedLecturer(e) {
            e.preventDefault();
            const bulkBlocks = document.querySelectorAll('.bulk-lecturer-block');

            if (bulkBlocks.length > 0) {
                let validationPass = true;
                const totalPayloads = [];

                // Frontend validation sweep ensuring no blocks are left with zero subject topics checked
                bulkBlocks.forEach(block => {
                    const studentId = block.querySelector('.bulk-target-id').value;
                    const newPassword = block.querySelector('.bulk-password').value.trim();
                    const newPhone = block.querySelector('.bulk-phone').value.trim();
                    
                    const checkedBoxes = block.querySelectorAll('.bulk-subjects-group input[type="checkbox"]:checked');
                    if (checkedBoxes.length === 0) {
                        validationPass = false;
                    }
                    const selectedSubjects = Array.from(checkedBoxes).map(b => b.value);

                    totalPayloads.push({ id: studentId, pass: newPassword, phone: newPhone, subs: selectedSubjects });
                });

                if (!validationPass) {
                    alert("Validation Error: Every checked lecturer profile block must remain mapped to at least one active subject.");
                    return;
                }

                if (!confirm(`Are you sure you want to update all ${bulkBlocks.length} modified lecturer configurations?`)) return;

                const promises = totalPayloads.map(payload => {
                    const formData = new FormData();
                    formData.append('action', 'update');
                    formData.append('lecturer_id', payload.id);
                    formData.append('new_password', payload.pass);
                    formData.append('new_phone', payload.phone);
                    formData.append('subjects', JSON.stringify(payload.subs));

                    return fetch('manageLecturersAdmin.php', { method: 'POST', body: formData }).then(res => res.json());
                });

                Promise.all(promises).then(() => {
                    alert("All lecturer data profiles updated completely!");
                    window.location.reload();
                });
            } else {
                const lecturerId = document.getElementById('single_edit_target_id').value;
                const newPassword = document.getElementById('single_edit_password').value.trim();
                const newPhone = document.getElementById('single_edit_phone').value.trim();

                const checkedBoxes = document.querySelectorAll('#singleEditSubjectsGroup input[type="checkbox"]:checked');
                if (checkedBoxes.length === 0) {
                    alert("Validation Error: A lecturer must be assigned to at least one subject.");
                    return;
                }
                const selectedSubjects = Array.from(checkedBoxes).map(box => box.value);

                const formData = new FormData();
                formData.append('action', 'update');
                formData.append('lecturer_id', lecturerId);
                formData.append('new_password', newPassword);
                formData.append('new_phone', newPhone);
                formData.append('subjects', JSON.stringify(selectedSubjects));

                fetch('manageLecturersAdmin.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert("Lecturer updated successfully!");
                        window.location.reload();
                    } else {
                        alert("Error updating record: " + data.error);
                    }
                });
            }
        }

        function suspendBulkLecturers() {
            const checkedBoxes = document.querySelectorAll('.lecturer-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert("Please select at least one lecturer to suspend.");
                return;
            }

            if (!confirm(`Are you sure you want to completely suspend and delete the ${checkedBoxes.length} selected lecturer(s) from the database?`)) {
                return;
            }

            const promises = Array.from(checkedBoxes).map(cb => {
                const lecturerId = cb.value;
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('lecturer_id', lecturerId);

                return fetch('manageLecturersAdmin.php', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => ({ id: lecturerId, success: data.success }));
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
            document.getElementById('addLecturerModal').style.display = 'flex';
        }

        function closeAddModal() {
            document.getElementById('addLecturerModal').style.display = 'none';
            document.getElementById('addLecturerForm').reset();
        }

        function saveNewLecturer(e) {
            e.preventDefault();

            const checkedBoxes = document.querySelectorAll('input[name="registered_subjects[]"]:checked');
            if (checkedBoxes.length === 0) {
                alert("Validation Error: It is mandatory to select at least one subject for registration.");
                return;
            }
            const selectedSubjects = Array.from(checkedBoxes).map(box => box.value);

            const formData = new FormData();
            formData.append('action', 'insert');
            formData.append('lecturer_id', document.getElementById('add_id').value.trim());
            formData.append('name', document.getElementById('add_name').value.trim());
            formData.append('email', document.getElementById('add_email').value.trim());
            formData.append('password', document.getElementById('add_password').value.trim());
            formData.append('phone', document.getElementById('add_phone').value.trim());
            formData.append('subjects', JSON.stringify(selectedSubjects));

            fetch('manageLecturersAdmin.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) { alert("Lecturer successfully added!"); window.location.reload(); }
                else { alert("Database Save Error: " + data.error); }
            });
        }

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
                let phoneText = row.getAttribute('data-original-phone').toLowerCase();
                let emailText = row.getAttribute('data-original-email').toLowerCase();

                if (idText.includes(query) || nameText.includes(query) || phoneText.includes(query) || emailText.includes(query)) {
                    row.style.display = '';
                    matchesFound++;
                } else {
                    row.style.display = 'none';
                    const cb = row.querySelector('.lecturer-checkbox');
                    if(cb) cb.checked = false;
                }
            });
            updateSelectedCount();

            if (matchesFound === 0) {
                headerRow.style.display = 'none';
                emptyState.textContent = `No lecturers found matching "${document.getElementById('lecturerSearch').value}"`;
                emptyState.style.display = 'block';
            } else {
                headerRow.style.display = '';
                emptyState.style.display = 'none';
            }
        }

        function suspendLecturer(button) {
            if(button.disabled) return;
            const row = button.closest('tr');
            const lecturerId = row.querySelector('.lecturer-id').textContent.trim();

            if (confirm(`Are you sure you want to completely delete lecturer ${lecturerId} from the database?`)) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('lecturer_id', lecturerId);

                fetch('manageLecturersAdmin.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        row.remove();
                        alert("Lecturer successfully removed from the database.");
                    } else {
                        alert("Database Error: " + data.error);
                    }
                });
            }
        }
    </script>
</body>
</html>