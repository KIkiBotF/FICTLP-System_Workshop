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
    width: 140px;
    background-color: #ffffff;
    padding-top: 40px;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
    z-index: 10;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    font-family: 'Inter', sans-serif;
}

/* ==========================================================================
   2. GAYA NAV ITEM
   ========================================================================== */
.nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px 5px; /* 🛠️ Dikurangkan padding kiri-kanan supaya muat lebar baru */
    cursor: pointer;
    text-align: center;
    gap: 5px;
    text-decoration: none;
    color: #000;
}

/* Tulisan teks menu */
.nav-item span {
    font-size: 14px;
    font-weight: bold;
}

.nav-item:hover {
    background-color: #006699 !important; /* Warna biru pekat pensyarah */
}

.nav-item:hover span {
    color: #fff; /* Memastikan teks bertukar putih sepenuhnya */
}

.icon-placeholder {
    width: 32px;
    height: 32px;
    object-fit: contain;
}

#performanceBox span {
    margin-top: 0;
}

    </style>
</head>
<body>
    <aside class="sidebar">
     
        <a href="mainPageAdmin.php" class="nav-item">
            <img src="Aset/homeBtn.svg" alt="Home" class="icon-placeholder" id="homeIcon">
            <span>Home</span>
        </a>
                <a href="dashboardAdmin.php" class="nav-item" id="performanceBox">
    <img id="performanceIcon" src="Aset/summaryBtn.svg" alt="Performance" class="icon-placeholder">
    <span>Information</span>
</a>

        <a href="announcementAdmin.php" class="nav-item">
            <img src="Aset/annoucment.svg" alt="Report" class="icon-placeholder">
            <span>Announcement</span>
        </a>

           <a href="logout.php" class="nav-item">
            <img src="Aset/logOutBtn.svg" alt="Log Out" class="icon-placeholder" id="logOutIcon">
            <span>Log Out</span>
        </a>

    </aside>
</body>
</html>