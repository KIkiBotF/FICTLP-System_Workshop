<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar Lecturer</title>

    <style>

.sidebar {
    display: flex;
    flex-direction: column;
    width: 105px; 
    height: 100vh;
    background-color: #ffffff;
    padding-top: 40px;
    z-index: 10;

    /* 🌟 SOLUSI UTAMA: Menggunakan 'fixed' supaya ia duduk di lapisan atas dan shadow tidak ditindih */
    position: fixed;
    top: 0;
    left: 0;

    /* 🌟 KUNCI UTAMA: Kesan bayang lembut 100% sama seperti versi Student */
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05); 
    border-right: none; 
}

/* ==========================================================================
   2. GAYA NAV ITEM (BUTANG MENU) - KEKAL ASAL & SERAGAM
   ========================================================================== */
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
    color: #000000;
    width: 100%; 
    box-sizing: border-box; 
}

/* Tulisan teks di bawah ikon */
.nav-item span {
    font-size: 0.85rem; 
    font-weight: bold;
    display: block;
    width: 100%;
    word-wrap: break-word; 
}

/* Kesan Hover (Biru) */
.nav-item:hover {
    background-color: #006699;
}

.nav-item:hover span {
    color: #ffffff;
}

/* Saiz Ikon */
.icon-placeholder {
    width: 32px;
    height: 32px;
    object-fit: contain;
}

/* Larasan khas asal untuk menu Manage Subject desktop */
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
    #manageSubjectIcon{
        width: 50px !important;
        height: 50px !important;
    }
    #manageSubjectBox{
        margin-top: 23px;
    }
    #manageSubjectBox span{
        background-color: #006699;
    }
}

/* ==========================================================================
   3. RESPONSIVE DESIGN (MOBILE & TABLET <= 780px) - DIKEKALKAN 100% ASAL
   ========================================================================== */
@media (max-width: 780px) {
    body {
        display: block !important;
        padding-bottom: 80px; 
    }
    
    .sidebar {
        position: fixed;
        bottom: 0;
        top: auto; /* Reset kedudukan top pada mobile */
        left: 0;
        width: 100%;
        height: 75px; 
        flex-direction: row;
        justify-content: space-around;
        align-items: center; 
        padding-top: 0;
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        z-index: 1000;
    }
    
    .nav-item {
        padding: 5px; 
        flex: 1; 
        width: auto; 
    }
    
    .icon-placeholder, .nav-item img {
        width: 32px !important;
        height: 32px !important;
        margin-bottom: 3px; 
    }
    
    .nav-item span {
        font-size: 10px !important;
        line-height: 1.1; 
        display: block;
    }

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