<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subject - CoreKnowledge</title>
    <link rel="stylesheet" href="Aset/sidebar.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: sans-serif;
        }

        body {
            display: flex;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
        }
        /* --- Main Content Styling --- */
        .main-content {
            flex: 1;
            background-color: #f9eaed;
            display: flex;
            flex-direction: column;
            align-items: center; /* Centers content horizontally */
            padding-top: 50px;
        }

        .header-container h1 {
            font-size: 2.5rem;
            color: #000;
            margin-bottom: 50px;
        }

        /* --- Subject Card Styling --- */
        .card-container {
            width: 80%; /* Adjust width as needed */
            max-width: 900px;
        }

        .subject-card {
            background-color: #fff;
            border-radius: 15px;
            overflow: hidden; /* Ensures the blue header stays within rounded corners */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            background-color: #a8ddec; /* Light blue from your image */
            padding: 15px 25px;
        }

        .card-header h2 {
            font-size: 1.8rem;
            color: #000;
        }

        .card-body {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 25px;
        }

        .subject-topics {
            list-style-type: disc;
            padding-left: 20px; /* Indent bullets */
        }

        .subject-topics li {
            font-size: 1.1rem;
            margin-bottom: 5px;
            color: #333;
        }

        .card-actions {
            display: flex;
            gap: 20px;
        }

        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
            background: none;
            border: none;
        }

        .action-btn span {
            margin-top: 5px;
            font-size: 0.75rem;
            font-weight: normal;
            color: #333;
        }
        
        /* Placeholder styling for icons if assets are missing */
        .icon-placeholder {
            width: 32px;
            height: 32px;
            object-fit: contain;
        }
    </style>
</head>

<body>

    <aside class="sidebar">
    <div class="nav-item" onclick="window.location.href='logIn.php'">
        <img src="Aset/logOutBtn.svg" alt="Log Out" class="icon-placeholder">
        <span>Log Out</span>
    </div>

    <div class="nav-item" onclick="window.location.href='mainPage.php'">
        <img src="Aset/homeBtn.svg" alt="Home" class="icon-placeholder">
        <span>Home</span>
    </div>

    <div class="nav-item" onclick="window.location.href='manageSubjectLecturer.php'">
        <img style="width: 50px;height: 50px;" src="Aset/manageSubjectBtn.svg" alt="Manage Subject" class="icon-placeholder">
        <span>Manage Subject</span>
    </div>

    <div class="nav-item">
        <img src="Aset/reportBtn.svg" alt="Report" class="icon-placeholder">
        <span>Report</span>
    </div>

    <div class="nav-item">
        <img style="width: 40px;height: 40px;" src="Aset/editQuizBtn.svg" alt="Edit Quiz" class="icon-placeholder">
        <span>Edit Quiz</span>
    </div>
    
    <div class="nav-item" onclick="window.location.href='addSubject.php'">
        <img src="Aset/addNewSubject.svg" alt="Add New Subject" class="icon-placeholder">
        <span>Add new subject</span>
    </div>
</aside>

    <main class="main-content">
        <div class="header-container">
            <h1>Manage Subject</h1>
        </div>

        <div class="card-container">
            <div class="subject-card">
                
                <div class="card-header">
                    <h2>Database</h2>
                </div>
                
                <div class="card-body">
                    <ul class="subject-topics">
                        <li>SQL (Structured Query Language)</li>
                        <li>Database Design</li>
                    </ul>
                    
                    <div class="card-actions">
                        <button class="action-btn" onclick="window.location.href='editSubject.php'">
    <img src="Aset/editBtn.svg" alt="Edit" class="icon-placeholder"> 
    <span>Edit</span>
</button>
                        <button class="action-btn">
                            <img src="Aset/removeBtn.svg" alt="Remove" class="icon-placeholder">
                            <span>Remove</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
        </main>

</body>

</html>