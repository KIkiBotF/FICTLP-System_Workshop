<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <style> 

/* --- Sidebar Styling --- */
.sidebar {
    display: flex;
    flex-direction: column;
    width: 150px;
    background-color: #ffffff;
    padding-top: 40px;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
    z-index: 10;
    height: 100vh; 
}

.nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px 10px;
    cursor: pointer;
    text-align: center;
    gap: 5px;
    text-decoration: none; 
    color: #000; 
}

.nav-item span {
    font-size: 0.85rem;
    font-weight: bold;
}

.nav-item:hover {
    background-color: #006699;
}

.nav-item:hover span {
    color: #fff; 
}

.icon-placeholder {
    width: 32px;
    height: 32px;
    object-fit: contain;
}

#manageSubjectBox span {
    margin-top: -10px;
}
    </style>
</head>
<body>
    <aside class="sidebar">
        <a href="index.php" class="nav-item"> 
            <img src="Aset/logOutBtn.svg" alt="Log Out" class="icon-placeholder">
            <span>Log Out</span>
        </a>

        <a href="mainPage.php" class="nav-item">
            <img src="Aset/homeBtn.svg" alt="Home" class="icon-placeholder">
            <span>Home</span>
        </a>

        <a href="manageSubjectLecturer.php" class="nav-item" id="manageSubjectBox">
            <img id="manageSubjectIcon" style="width: 50px; height: 50px;" src="Aset/manageSubjectBtn.svg" alt="Manage Subject" class="icon-placeholder">
            <span>Manage Subject</span>
        </a>

        <a href="reportLecturer.php" class="nav-item">
            <img src="Aset/reportBtn.svg" alt="Report" class="icon-placeholder">
            <span>Report</span>
        </a>

        <a href="editQuizLecturer.php" class="nav-item">
            <img style="width: 40px; height: 40px;" src="Aset/editQuizBtn.svg" alt="Edit Quiz" class="icon-placeholder">
            <span>Edit Quiz</span>
        </a>

        <a href="addSubject.php" class="nav-item">
            <img src="Aset/addNewSubject.svg" alt="Add New Subject" class="icon-placeholder">
            <span>Add new subject</span>
        </a>
    </aside>
</body>
</html>