<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subject - CoreKnowledge</title>
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="manageSubject.css"> 
</head>

<body>
<?php include("sidebar.php") ?>;
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