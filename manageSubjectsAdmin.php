<?php
// --- DATABASE CONFIGURATION ---
$host = "100.81.48.34";
$port = "3307";          
$dbname = "fictlp db";  
$username = "bubustailo"; 
$password = "Student@123";

// ==========================================
// HANDLES INLINE POST ACTIONS (CREATE / DELETE / UPDATE)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    // --- ACTION: CREATE SUBJECT ---
    if ($_POST['action'] === 'create') {
        $subjectCode = $_POST['subject_code'] ?? '';
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $lecturers = isset($_POST['lecturers']) ? json_decode($_POST['lecturers'], true) : [];
        
        if (empty($subjectCode) || empty($title) || empty($lecturers)) {
            echo json_encode(['success' => false, 'error' => 'Mandatory fields missing. You must provide a Subject Code, Title, and select at least one Lecturer.']);
            exit;
        }
        
        try {
            $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $conn->beginTransaction();

            // Insert new record into 'subject' table
            $stmt = $conn->prepare("INSERT INTO subject (Subject_Code, Title, Description) VALUES (:subject_code, :title, :description)");
            $stmt->bindParam(':subject_code', $subjectCode, PDO::PARAM_STR);
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->execute();
            
            // Map checked lecturers into 'lecture_subject' junction table
            $lecStmt = $conn->prepare("INSERT INTO lecture_subject (userID, Subject_Code) VALUES (:user_id, :subject_code)");
            foreach ($lecturers as $userId) {
                $lecStmt->bindValue(':user_id', $userId, PDO::PARAM_STR);
                $lecStmt->bindValue(':subject_code', $subjectCode, PDO::PARAM_STR);
                $lecStmt->execute();
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
        $subjectCode = $_POST['subject_code'] ?? '';
        try {
            $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $conn->beginTransaction();

            // Clear junction assignments first to prevent foreign key issues
            $stmtJunction = $conn->prepare("DELETE FROM lecture_subject WHERE Subject_Code = :subject_code");
            $stmtJunction->bindParam(':subject_code', $subjectCode, PDO::PARAM_STR);
            $stmtJunction->execute();

            $stmt = $conn->prepare("DELETE FROM subject WHERE Subject_Code = :subject_code");
            $stmt->bindParam(':subject_code', $subjectCode, PDO::PARAM_STR);
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
    
    // --- ACTION: UPDATE (Only Description and Lecturer Assignments) ---
    if ($_POST['action'] === 'update') {
        $subjectCode = $_POST['subject_code'] ?? '';
        $newDescription = $_POST['description'] ?? '';
        $lecturers = isset($_POST['lecturers']) ? json_decode($_POST['lecturers'], true) : [];
        
        if (empty($subjectCode)) {
            echo json_encode(['success' => false, 'error' => 'Subject identifier missing.']);
            exit;
        }
        
        try {
            $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $conn->beginTransaction();

            // Update the Description column inside subject table
            $stmt = $conn->prepare("UPDATE subject SET Description = :description WHERE Subject_Code = :subject_code");
            $stmt->bindParam(':description', $newDescription, PDO::PARAM_STR);
            $stmt->bindParam(':subject_code', $subjectCode, PDO::PARAM_STR);
            $stmt->execute();
            
            // Wipe existing junction assignments for this subject
            $delStmt = $conn->prepare("DELETE FROM lecture_subject WHERE Subject_Code = :subject_code");
            $delStmt->bindParam(':subject_code', $subjectCode, PDO::PARAM_STR);
            $delStmt->execute();

            // Remap selected ticked lecturers
            $lecStmt = $conn->prepare("INSERT INTO lecture_subject (userID, Subject_Code) VALUES (:user_id, :subject_code)");
            foreach ($lecturers as $userId) {
                $lecStmt->bindValue(':user_id', $userId, PDO::PARAM_STR);
                $lecStmt->bindValue(':subject_code', $subjectCode, PDO::PARAM_STR);
                $lecStmt->execute();
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

// --- MAIN FETCH DATA FOR THE ROSTER ---
try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Updated Query: Joins enrollment with user to count only students
    $query = "SELECT 
                s.Subject_Code AS subject_code, 
                s.Title AS title,
                s.Description AS description,
                COUNT(DISTINCT CASE WHEN u.Role = 'Student' THEN e.userID END) AS enrolled 
              FROM subject s
              LEFT JOIN enrollment e ON s.Subject_Code = e.Subject_Code
              LEFT JOIN user u ON e.userID = u.userID
              GROUP BY s.Subject_Code, s.Title, s.Description";
              
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch active available lecturers to map visually inside checking inputs
    $lecQuery = $conn->prepare("SELECT userID, Name FROM user WHERE Role = 'Lecturer'");
    $lecQuery->execute();
    $availableLecturers = $lecQuery->fetchAll(PDO::FETCH_ASSOC);

    // Map existing assignments
    $assignedQuery = $conn->prepare("SELECT Subject_Code, userID FROM lecture_subject");
    $assignedQuery->execute();
    $assignmentsRaw = $assignedQuery->fetchAll(PDO::FETCH_ASSOC);

    $subjectLecturerMap = [];
    foreach ($assignmentsRaw as $ass) {
        $subjectLecturerMap[$ass['Subject_Code']][] = $ass['userID'];
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
    <title>CoreKnowledge - Manage Subjects(Admin)</title>
    <link rel="stylesheet" href="manageSubjectsAdmin.css">
    <style>
        /* UI Layout Structure Consistency Styles */
        .controls-row {
            width: 92%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            gap: 15px;
        }
        .controls-row .search-wrapper {
            flex: 1;
            margin-bottom: 0;
        }
        .btn-add-trigger {
            background-color: #4a7c59;
            color: white;
            border: none;
            padding: 14px 24px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.15s ease;
            white-space: nowrap;
        }
        .btn-add-trigger:hover {
            background-color: #3b6347;
        }

        /* Mode Activation Toggle Button Styling */
        .btn-select-toggle {
            background-color: #132f53;
            color: white;
            border: 1px solid #cbd5e1;
            padding: 14px 24px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .btn-select-toggle.active-mode {
            background-color: #1a253c;
            color: white;
            border-color: #1a253c;
        }

        /* Bulk Action Control Layout Strip */
        .bulk-action-bar {
            display: none;
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            padding: 14px 20px;
            border-radius: 12px;
            width: 92%;
            margin-bottom: 20px;
            box-sizing: border-box;
            justify-content: space-between;
            align-items: center;
        }
        .bulk-btn-group {
            display: flex;
            gap: 10px;
        }
        .btn-bulk-action {
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            font-size: 14px;
            transition: opacity 0.15s ease;
        }
        .btn-bulk-action:hover {
            opacity: 0.9;
        }
        .btn-bulk-edit {
            background-color: #3b82f6;
            color: white;
        }
        .btn-bulk-remove {
            background-color: #ef4444;
            color: white;
        }

        /* Controlled Layout hiding of Checkboxes by Default */
        .col-checkbox-header, .cell-checkbox-container {
            display: none;
            width: 45px;
            text-align: center;
        }
        .subject-checkbox {
            transform: scale(1.25);
            cursor: pointer;
        }

        /* Modal Popup Layout Component Elements */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.4);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .modal-card {
            background: white;
            width: 540px;
            max-height: 85vh;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
        }
        .modal-card h2 {
            margin-bottom: 20px;
            color: #1a253c;
            font-size: 22px;
            text-align: left;
        }
        .modal-scroll-content {
            overflow-y: auto;
            flex: 1;
            padding-right: 5px;
            margin-bottom: 15px;
        }
        .form-group {
            margin-bottom: 16px;
            text-align: left;
        }
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: bold;
            color: #1a253c;
            margin-bottom: 6px;
        }
        .form-group input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .form-group input.readonly-field {
            background-color: #edf2f7;
            color: #4a5568;
            border-color: #cbd5e0;
            cursor: not-allowed;
        }
        .form-group input:focus:not(.readonly-field) {
            border-color: #4a7c59;
            outline: none;
        }
        
        /* Checkbox Scannable Listing Elements */
        .checkbox-container {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px;
            max-height: 140px;
            overflow-y: auto;
            background: #fdfdfd;
        }
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
            font-size: 13.5px;
            color: #2d3748;
            cursor: pointer;
        }
        .checkbox-item:last-child {
            margin-bottom: 0;
        }
        .checkbox-item input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: #4a7c59;
        }
        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 10px;
        }
        .btn-action.btn-edit {
            background-color: #3b82f6;
            color: white;
            border: 1px solid #cbd5e1;
            padding: 6px 16px;
            border-radius: 20px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-action.btn-edit:hover {
            background-color: #cbd5e1;
            color: #000;
        }

        /* Gray out inline individual buttons during select mode */
        .btn-action:disabled {
            background-color: #f1f5f9 !important;
            color: #94a3b8 !important;
            border-color: #e2e8f0 !important;
            cursor: not-allowed !important;
            opacity: 0.6;
        }

        /* Style wrapper for repeated individual courses structural blocks in bulk modal */
        .bulk-subject-block {
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
        }
        .bulk-subject-block h3 {
            margin: 0 0 14px 0;
            font-size: 15px;
            color: #1e3a8a;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 6px;
        }
    </style>
</head>
<body>
    <?php include("sidebarAdmin.php"); ?>

    <div class="window-frame">
        <main class="main-content"> <?php include 'manage_nav.php'; ?>
            <h1>Manage Subjects</h1>
            <div class="subtitle">Modify subject configuration rules and metrics:</div>

            <div class="controls-row">
                <div class="search-wrapper">
                    <div class="search-icon"></div>
                    <input type="text" id="subjectSearch" class="search-input" placeholder="Search by ID or Subject Name..." onkeyup="filterSubjects()">
                </div>
                <!-- Dynamic Select Activator Toggle Button Control -->
                <button type="button" id="selectModeBtn" class="btn-select-toggle" onclick="toggleSelectMode()">Select</button>
                <button class="btn-add-trigger" id="addSubjectTriggerBtn" onclick="openAddModal()">+ Add Subject</button>
            </div>

            <!-- Dynamic Multi-Selection Context Ribbon Toolstrip -->
            <div id="bulkActionBar" class="bulk-action-bar">
                <span id="bulkCountText" style="font-weight: bold; color: #1a253c;">0 subjects selected</span>
                <div class="bulk-btn-group">
                    <button type="button" class="btn-bulk-action btn-bulk-edit" onclick="openBulkEditModal()">Edit Selected</button>
                    <button type="button" class="btn-bulk-action btn-bulk-remove" onclick="removeBulkSubjects()">Remove Selected</button>
                </div>
            </div>

            <div class="table-container" id="tableContainer">
                <div class="table-card-wrapper">
                    <?php if (count($subjects) > 0): ?>
                    <table class="subject-table" id="subjectTable">
                        <thead>
                            <tr id="tableHeaderRow">
                                <th class="col-checkbox-header"><input type="checkbox" id="selectAllBox" onchange="toggleSelectAllRows(this)"></th>
                                <th class="col-id">ID</th>
                                <th class="col-name">Subject Name</th>
                                <th class="col-enroll">Enrolled</th>
                                <th class="col-actions" style="text-align: right; padding-right: 45px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="subjectTableBody">
                            <?php foreach ($subjects as $row): 
                                $code = $row['subject_code'];
                                $assignedList = isset($subjectLecturerMap[$code]) ? $subjectLecturerMap[$code] : [];
                                $assignedJson = htmlspecialchars(json_encode($assignedList), ENT_QUOTES, 'UTF-8');
                            ?>
                                <tr data-original-id="<?php echo htmlspecialchars($code); ?>" 
                                    data-original-name="<?php echo htmlspecialchars($row['title']); ?>"
                                    data-original-description="<?php echo htmlspecialchars($row['description'] ?? ''); ?>"
                                    data-original-lecturers="<?php echo $assignedJson; ?>">
                                    
                                    <td class="cell-checkbox-container">
                                        <input type="checkbox" class="subject-checkbox" value="<?php echo htmlspecialchars($code); ?>" onchange="updateSelectedCount()">
                                    </td>
                                    <td class="subject-id"><?php echo htmlspecialchars($code); ?></td>
                                    <td class="subject-name"><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td class="subject-enroll"><?php echo htmlspecialchars($row['enrolled']); ?></td>
                                    <td>
                                        <div class="action-group" style="padding-right: 15px;">
                                            <button class="btn-action btn-edit inline-action-btn" onclick="openEditModal(this)">Edit</button>
                                            <button class="btn-action btn-remove inline-action-btn" onclick="removeSubject(this)">Remove</button>
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

    <!-- Add Subject Form Modal Overlay Component Box -->
    <div class="modal-overlay" id="addSubjectModal">
        <div class="modal-card">
            <h2>Add New Subject</h2>
            <div class="modal-scroll-content">
                <form id="addSubjectForm" onsubmit="saveNewSubject(event)">
                    <div class="form-group">
                        <label>Subject Code</label>
                        <input type="text" id="add_code" required placeholder="e.g. DITP1113">
                    </div>
                    <div class="form-group">
                        <label>Subject Name (Title)</label>
                        <input type="text" id="add_title" required placeholder="e.g. Programming 1">
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" id="add_description" placeholder="Description details">
                    </div>
                    
                    <div class="form-group">
                        <label>Assign Lecturers *</label>
                        <div class="checkbox-container">
                            <?php if (count($availableLecturers) > 0): ?>
                                <?php foreach ($availableLecturers as $lecturer): ?>
                                    <label class="checkbox-item">
                                        <input type="checkbox" name="assigned_lecturers[]" value="<?php echo htmlspecialchars($lecturer['userID']); ?>">
                                        <span><?php echo htmlspecialchars($lecturer['userID'] . " - " . $lecturer['Name']); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div style="font-size: 13px; color: #a0aec0; padding: 5px 0;">No active lecturers found on database server.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-action btn-cancel" onclick="closeAddModal()">Cancel</button>
                <button type="button" class="btn-action btn-save" onclick="document.getElementById('addSubjectForm').requestSubmit()">Add Subject</button>
            </div>
        </div>
    </div>

    <!-- Modal Form View: Single & Multi-Form Bulk Edit Unified Layout Structure -->
    <div class="modal-overlay" id="editSubjectModal">
        <div class="modal-card">
            <h2 id="editModalHeader">Edit Subject Configuration</h2>
            <div class="modal-scroll-content">
                <form id="editSubjectForm" onsubmit="saveEditedSubject(event)">
                    <div id="editModalDynamicContainer"></div>
                </form>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-action btn-cancel" onclick="closeEditModal()">Cancel</button>
                <button type="button" class="btn-action btn-save" onclick="document.getElementById('editSubjectForm').requestSubmit()">Save Changes</button>
            </div>
        </div>
    </div>

    <!-- Hidden Server Blueprint Reference Container to serve baseline templates dynamically inside loops -->
    <div id="lecturerTemplateSource" style="display:none;">
        <?php if (count($availableLecturers) > 0): ?>
            <?php foreach ($availableLecturers as $lecturer): ?>
                <label class="checkbox-item">
                    <input type="checkbox" value="<?php echo htmlspecialchars($lecturer['userID']); ?>">
                    <span><?php echo htmlspecialchars($lecturer['userID'] . " - " . $lecturer['Name']); ?></span>
                </label>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="font-size: 13px; color: #a0aec0; padding: 5px 0;">No active lecturers found on database server.</div>
        <?php endif; ?>
    </div>

    <script>
        let isSelectModeActive = false;

        // Toggle layouts and dynamically disable/enable inline controls based on Selection mode state
        function toggleSelectMode() {
            isSelectModeActive = !isSelectModeActive;
            const toggleBtn = document.getElementById('selectModeBtn');
            const actionBar = document.getElementById('bulkActionBar');
            const addBtn = document.getElementById('addSubjectTriggerBtn');
            
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
                document.querySelectorAll('.subject-checkbox').forEach(cb => cb.checked = false);
                updateSelectedCount();
            }
        }

        function toggleSelectAllRows(master) {
            const checkboxes = document.querySelectorAll('.subject-checkbox');
            checkboxes.forEach(cb => {
                if (cb.closest('tr').style.display !== 'none') {
                    cb.checked = master.checked;
                }
            });
            updateSelectedCount();
        }

        function updateSelectedCount() {
            const count = document.querySelectorAll('.subject-checkbox:checked').length;
            document.getElementById('bulkCountText').textContent = `${count} subject(s) selected`;
        }

        function openBulkEditModal() {
            const checkedBoxes = document.querySelectorAll('.subject-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert("Please check at least one subject to edit.");
                return;
            }

            const container = document.getElementById('editModalDynamicContainer');
            container.innerHTML = ""; 

            document.getElementById('editModalHeader').textContent = "Bulk Edit Subject Configurations";
            const templateMarkup = document.getElementById('lecturerTemplateSource').innerHTML;

            checkedBoxes.forEach((cb, index) => {
                const row = cb.closest('tr');
                const code = row.getAttribute('data-original-id');
                const title = row.getAttribute('data-original-name');
                const description = row.getAttribute('data-original-description');
                const activeLecturers = JSON.parse(row.getAttribute('data-original-lecturers') || '[]');

                const blockHtml = `
                    <div class="bulk-subject-block" data-index="${index}">
                        <h3>Subject #${index + 1}: ${title} (${code})</h3>
                        <input type="hidden" class="bulk-target-code" value="${code}">
                        
                        <div class="form-group">
                            <label>Subject Code</label>
                            <input type="text" value="${code}" class="readonly-field" readonly>
                        </div>
                        <div class="form-group">
                            <label>Subject Name (Title)</label>
                            <input type="text" value="${title}" class="readonly-field" readonly>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <input type="text" class="bulk-description" value="${description}" placeholder="Modify description parameters">
                        </div>
                        <div class="form-group">
                            <label>Assign Lecturers *</label>
                            <div class="checkbox-container bulk-lecturers-group">
                                ${templateMarkup}
                            </div>
                        </div>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', blockHtml);

                const renderedBlock = container.querySelector(`.bulk-subject-block[data-index="${index}"]`);
                const checkboxes = renderedBlock.querySelectorAll('.bulk-lecturers-group input[type="checkbox"]');
                checkboxes.forEach(box => {
                    box.name = `bulk_lecturers_${index}[]`;
                    if (activeLecturers.includes(box.value)) {
                        box.checked = true;
                    }
                });
            });

            document.getElementById('editSubjectModal').style.display = 'flex';
        }

        function openEditModal(button) {
            const row = button.closest('tr');
            const code = row.getAttribute('data-original-id');
            const title = row.getAttribute('data-original-name');
            const description = row.getAttribute('data-original-description');
            const activeLecturers = JSON.parse(row.getAttribute('data-original-lecturers') || "[]");

            document.getElementById('editModalHeader').textContent = "Edit Subject Configuration";
            const container = document.getElementById('editModalDynamicContainer');
            const templateMarkup = document.getElementById('lecturerTemplateSource').innerHTML;

            container.innerHTML = `
                <input type="hidden" id="single_edit_target_code" value="${code}">
                <div class="form-group">
                    <label>Subject Code</label>
                    <input type="text" value="${code}" class="readonly-field" readonly>
                </div>
                <div class="form-group">
                    <label>Subject Name (Title)</label>
                    <input type="text" value="${title}" class="readonly-field" readonly>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <input type="text" id="single_edit_description" value="${description}" placeholder="Modify course description parameters">
                </div>
                <div class="form-group">
                    <label>Assign Lecturers *</label>
                    <div class="checkbox-container" id="singleEditLecturersGroup">
                        ${templateMarkup}
                    </div>
                </div>
            `;

            const checkboxes = container.querySelectorAll('#singleEditLecturersGroup input[type="checkbox"]');
            checkboxes.forEach(box => {
                box.name = "single_edit_lecturers[]";
                if (activeLecturers.includes(box.value)) {
                    box.checked = true;
                }
            });

            document.getElementById('editSubjectModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editSubjectModal').style.display = 'none';
            document.getElementById('editSubjectForm').reset();
            document.getElementById('editModalDynamicContainer').innerHTML = "";
        }

        function saveEditedSubject(e) {
            e.preventDefault();
            const bulkBlocks = document.querySelectorAll('.bulk-subject-block');

            if (bulkBlocks.length > 0) {
                let validationPass = true;
                const totalPayloads = [];

                bulkBlocks.forEach(block => {
                    const code = block.querySelector('.bulk-target-code').value;
                    const newDesc = block.querySelector('.bulk-description').value.trim();
                    
                    const checkedBoxes = block.querySelectorAll('.bulk-lecturers-group input[type="checkbox"]:checked');
                    if (checkedBoxes.length === 0) {
                        validationPass = false;
                    }
                    const selectedLecturers = Array.from(checkedBoxes).map(b => b.value);

                    totalPayloads.push({ code: code, description: newDesc, lecturers: selectedLecturers });
                });

                if (!validationPass) {
                    alert("Validation Error: Every modified subject block must remain mapped to at least one assigned lecturer.");
                    return;
                }

                if (!confirm(`Are you sure you want to update all ${bulkBlocks.length} modified subject configurations?`)) return;

                const promises = totalPayloads.map(payload => {
                    const formData = new FormData();
                    formData.append('action', 'update');
                    formData.append('subject_code', payload.code);
                    formData.append('description', payload.description);
                    formData.append('lecturers', JSON.stringify(payload.lecturers));

                    return fetch('', { method: 'POST', body: formData }).then(res => res.json());
                });

                Promise.all(promises).then(() => {
                    alert("All subject data configurations updated completely!");
                    window.location.reload();
                });
            } else {
                const code = document.getElementById('single_edit_target_code').value;
                const newDesc = document.getElementById('single_edit_description').value.trim();

                const checkedBoxes = document.querySelectorAll('#singleEditLecturersGroup input[type="checkbox"]:checked');
                if (checkedBoxes.length === 0) {
                    alert("Validation Error: At least one lecturer must remain assigned to this subject.");
                    return;
                }
                const selectedLecturers = Array.from(checkedBoxes).map(box => box.value).sort();

                // Equality Check Matrix Verification
                const row = document.querySelector(`tr[data-original-id="${code}"]`);
                if (row) {
                    const oldDesc = row.getAttribute('data-original-description').trim();
                    const oldLecturers = JSON.parse(row.getAttribute('data-original-lecturers') || "[]").sort();
                    const listsMatch = (selectedLecturers.length === oldLecturers.length) && 
                                       selectedLecturers.every((val, index) => val === oldLecturers[index]);

                    if (newDesc === oldDesc && listsMatch) {
                        alert("Error: No changes were detected to save.");
                        return;
                    }
                }

                const formData = new FormData();
                formData.append('action', 'update');
                formData.append('subject_code', code);
                formData.append('description', newDesc);
                formData.append('lecturers', JSON.stringify(selectedLecturers));

                fetch('', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert("Subject updated successfully!");
                        window.location.reload();
                    } else {
                        alert("Error updating record: " + data.error);
                    }
                });
            }
        }

        function removeBulkSubjects() {
            const checkedBoxes = document.querySelectorAll('.subject-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert("Please select at least one subject to remove.");
                return;
            }

            if (!confirm(`Are you sure you want to completely delete the ${checkedBoxes.length} selected subject(s) from the database?`)) {
                return;
            }

            const promises = Array.from(checkedBoxes).map(cb => {
                const code = cb.value;
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('subject_code', code);

                return fetch('', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => ({ code: code, success: data.success }));
            });

            Promise.all(promises).then(results => {
                const failed = results.filter(r => !r.success);
                if (failed.length === 0) {
                    alert("Selected subject records successfully removed from system.");
                    window.location.reload();
                } else {
                    alert(`Batch executed with warnings. System failed processing Codes: ${failed.map(f => f.code).join(', ')}`);
                }
            });
        }

        function openAddModal() {
            document.getElementById('addSubjectModal').style.display = 'flex';
        }

        function closeAddModal() {
            document.getElementById('addSubjectModal').style.display = 'none';
            document.getElementById('addSubjectForm').reset();
        }

        function saveNewSubject(e) {
            e.preventDefault();
            
            const checkedBoxes = document.querySelectorAll('input[name="assigned_lecturers[]"]:checked');
            if (checkedBoxes.length === 0) {
                alert("Validation Error: It is mandatory to select at least one lecturer to assign to this course.");
                return;
            }
            const selectedLecturers = Array.from(checkedBoxes).map(box => box.value);

            const formData = new FormData();
            formData.append('action', 'create');
            formData.append('subject_code', document.getElementById('add_code').value.trim());
            formData.append('title', document.getElementById('add_title').value.trim());
            formData.append('description', document.getElementById('add_description').value.trim());
            formData.append('lecturers', JSON.stringify(selectedLecturers));

            fetch('', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Subject created successfully.");
                    window.location.reload(); 
                } else {
                    alert("Creation Failed: " + data.error);
                }
            });
        }

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
                    const cb = row.querySelector('.subject-checkbox');
                    if(cb) cb.checked = false;
                }
            });
            updateSelectedCount();

            if (matchesFound === 0) {
                headerRow.style.display = 'none';
                emptyState.textContent = `No subjects found matching "${document.getElementById('subjectSearch').value}"`;
                emptyState.style.display = 'block';
            } else {
                headerRow.style.display = '';
                emptyState.style.display = 'none';
            }
        }

        function removeSubject(button) {
            if(button.disabled) return;
            const row = button.closest('tr');
            const subjectCode = row.querySelector('.subject-id').textContent.trim();

            if (confirm(`Are you sure you want to completely delete subject ${subjectCode} from the database?`)) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('subject_code', subjectCode);

                fetch('', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        row.remove();
                        alert("Subject successfully removed from the database.");
                        window.location.reload();
                    } else {
                        alert("Database Error: Could not delete subject. " + data.error);
                    }
                });
            }
        }
    </script>
</body>
</html>