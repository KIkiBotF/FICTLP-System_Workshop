<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <style>

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
@media (max-width: 780px) {
    body {
        display: block !important;
        padding-bottom: 80px; 
    }
    
    .sidebar {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 75px; /* Increased slightly to give the larger icons room */
        flex-direction: row;
        justify-content: space-around;
        align-items: center; /* Ensures items don't stretch */
        padding-top: 0;
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        z-index: 1000;
    }
    
    .nav-item {
        padding: 5px; /* Reduced padding to fit smaller screens better */
        flex: 1; /* Distributes space evenly across all 6 buttons */
    }
    
    /* Make all icons uniformly larger */
    .icon-placeholder, .nav-item img {
        width: 32px !important;
        height: 32px !important;
        margin-bottom: 3px; /* Small gap between icon and text */
    }
    
    /* Make text smaller and handle long text like "Add new subject" */
    .nav-item span {
        font-size: 10px !important;
        line-height: 1.1; 
        display: block;
    }

    /* Reset the desktop negative margin so it aligns with other text */
    #manageSubjectBox span {
        margin-top: 0;
    }
}

    </style>
</head>
<body>
    <aside class="sidebar">
        <a href="index.php" class="nav-item">
            <img src="Aset/logOutBtn.svg" alt="Log Out" class="icon-placeholder" id="logOutIcon">
            <span>Log Out</span>
        </a>

        <a href="mainPage.php" class="nav-item">
            <img src="Aset/homeBtn.svg" alt="Home" class="icon-placeholder" id="homeIcon">
            <span>Home</span>
        </a>

<a href="manageSubjectLecturer.php" class="nav-item" id="manageSubjectBox">
    <!-- Removed inline style="width: 50px; height: 50px;" -->
    <img id="manageSubjectIcon" src="Aset/manageSubjectBtn.svg" alt="Manage Subject" class="icon-placeholder">
    <span>Manage Subject</span>
</a>

        <a href="reportLecturer.php" class="nav-item">
            <img src="Aset/reportBtn.svg" alt="Report" class="icon-placeholder">
            <span>Report</span>
        </a>

<a href="editQuizLecturer.php" class="nav-item">
    <!-- Removed inline style="width: 40px; height: 40px;" -->
    <img src="Aset/editQuizBtn.svg" alt="Edit Quiz" class="icon-placeholder">
    <span>Edit Quiz</span>
</a>

        <a href="addSubject.php" class="nav-item">
            <img src="Aset/addNewSubject.svg" alt="Add New Subject" class="icon-placeholder">
            <span>Add new subject</span>
        </a>
    </aside>
</body>
</html>