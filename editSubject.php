<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Subject - CoreKnowledge</title>
    <link rel="stylesheet" href="EditPageStyle.css">
</head>
</head>

<body>
    <header class="top-bar">
        <h1>Edit Subject</h1>
    </header>

    <main class="main-content">
        <div class="form-wrapper">
            <div class="form-container">
                <div class="form-row">
                    <label for="subjectCode">Subject Code:</label>
                    <input type="text" id="subjectCode" name="subjectCode" placeholder="Enter code (e.g., BITS 1234)">
                </div>

                <div class="form-row">
                    <label for="subjectTitle">Subject Title:</label>
                    <input type="text" id="subjectTitle" name="subjectTitle" placeholder="Enter title">
                </div>

                <div class="form-row">
                    <label for="subjectDesc">Subject Description:</label>
                    <textarea id="subjectDesc" name="subjectDesc" placeholder="Update the description of the subject..."></textarea>
                </div>
            </div>

            <div class="button-container">
                <button type="submit" class="save-btn">Save</button>
            </div>
        </div>
    </main>

</body>

</html>