<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report - CoreKnowledge</title>
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="reportLecturerStyle.css"> 
</head>
<body>
    
    <?php include("sidebar.php"); ?>

<main class="main-content">
        <h1>Report</h1>

        <div class="report-wrapper">
            <div class="report-container">
                
                <div class="filter-row">
                    <label for="subjectSelect">Subject:</label>
                    <select id="subjectSelect" name="subjectSelect">
                        <option value="" disabled selected>Select a subject...</option>
                        <option value="database">Database</option>
                    </select>
                </div>

                <div class="summary-cards">
                    <div class="card card-total">
                        <h3>Total student enroll</h3>
                        <p class="card-value">30</p>
                    </div>
                    
                    <div class="card card-pass">
                        <h3>Pass</h3>
                        <p class="card-value">1</p>
                    </div>
                    
                    <div class="card card-fail">
                        <h3>Fail</h3>
                        <p class="card-value">2</p>
                    </div>
                </div>

            </div>
        </div>
    </main>
</body>
</html>