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

            position: fixed;
            top: 0;
            left: 0;

            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
            border-right: none;
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
            color: #000000;
            width: 100%;
            box-sizing: border-box;
        }

        .nav-item span {
            font-size: 0.85rem;
            font-weight: bold;
            display: block;
            width: 100%;
            word-wrap: break-word;
        }

        .nav-item:hover {
            background-color: #006699;
        }

        .nav-item:hover span {
            color: #ffffff;
        }

        .icon-placeholder {
            width: 32px;
            height: 32px;
            object-fit: contain;
        }

        #manageSubjectBox span {
            margin-top: -5px;
        }

        #manageSubjectIcon {
            height: 42px !important;
            width: 42px !important;
        }


        @media (max-width: 780px) {
            body {
                display: block !important;
                padding-bottom: 80px;
            }

            .sidebar {
                position: fixed;
                bottom: 0;
                top: auto;
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

            .icon-placeholder,
            .nav-item img {
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


        <?php
        if (basename($_SERVER['PHP_SELF']) === 'editSubjectContent.php'):
            // Grab the subject code directly from the URL parameters
            $safeSubject = isset($_GET['subject']) ? $_GET['subject'] : '';
        ?>
            <a href="EditSubject.php?subject=<?php echo urlencode($safeSubject); ?>" class="nav-item">
                <img src="Aset/returnPage.svg" alt="Return" class="icon-placeholder">
                <span>Return</span>
            </a>
        <?php endif; ?>

        <a href="mainPageLecturer.php" class="nav-item">
            <img src="Aset/homeBtn.svg" alt="Home" class="icon-placeholder" id="homeIcon">
            <span>Home</span>
        </a>

        <a href="manageSubjectLecturer.php" class="nav-item" id="manageSubjectBox">
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
        <a href="index.php" class="nav-item">
            <img src="Aset/logOutBtn.svg" alt="Log Out" class="icon-placeholder" id="logOutIcon">
            <span>Log Out</span>
        </a>
    </aside>

</body>

</html>