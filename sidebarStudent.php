<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <style>

/* ==========================================================================
   1. LAYOUT SIDEBAR (DESKTOP) - DIKECILKAN KELEBARAN
   ========================================================================== */
.sidebar {
    display: flex;
    flex-direction: column;
    width: 105px; /* 🛠️ Ditukar dari 150px ke 105px supaya lebih ramping/kecil */
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
    font-size: 11px; /* 🛠️ Dikecilkan sedikit tulisan (cth: 11px atau 0.75rem) supaya tidak pecah baris */
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
        <a href="index.php" class="nav-item">
            <img src="Aset/logOutBtn.svg" alt="Log Out" class="icon-placeholder" id="logOutIcon">
            <span>Log Out</span>
        </a>

        <a href="mainPageStudent.php" class="nav-item">
            <img src="Aset/homeBtn.svg" alt="Home" class="icon-placeholder" id="homeIcon">
            <span>Home</span>
        </a>

<a href="performanceStudent.php" class="nav-item" id="performanceBox">
    <!-- Removed inline style="width: 50px; height: 50px;" -->
    <img id="performanceIcon" src="Aset/performanceBtn.svg" alt="Performance" class="icon-placeholder">
    <span>Performance</span>
</a>

        <a href="SubjectStudent.php" class="nav-item">
            <img src="Aset/subjectBtn.svg" alt="Report" class="icon-placeholder">
            <span>Subjects</span>
        </a>

<a href="AchievementStudent.php" class="nav-item">
    <!-- Removed inline style="width: 40px; height: 40px;" -->
    <img src="Aset/archievementBtn.svg" alt="Archive" class="icon-placeholder">
    <span>Archivement</span>
</a>


    </aside>
</body>
</html>