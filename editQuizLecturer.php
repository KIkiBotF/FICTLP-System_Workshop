<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Quiz - CoreKnowledge</title>
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="editQuizLecturerStyle.css"> 
</head>
<body>
    
    <?php include("sidebar.php"); ?>

    <main class="main-content">
        <h1>Edit Quiz</h1>

        <div class="quiz-wrapper">
            
            <div class="settings-container">
                <div class="input-group">
                    <label for="quizChapter">Quiz Chapter:</label>
                    <input type="number" id="quizChapter" name="quizChapter" value="4" min="1">
                </div>
                
                <div class="input-group subject-group">
                    <label for="subjectSelect">Subject:</label>
                    <select id="subjectSelect" name="subjectSelect">
                        <option value="" disabled>Select a subject...</option>
                        <option value="database" selected>Database</option>
                    </select>
                </div>

                <button type="button" class="load-btn">Load Quiz</button>
            </div>

            <div class="question-container">
                <div class="question-header">
                    <h2 class="question-label">Question 01</h2>
                    <button class="delete-question-btn" title="Remove Question">✖</button>
                </div>
                
                <textarea class="question-input" placeholder="Type your quiz question here...">What does SQL stand for?</textarea>
                
                <div class="options-group">
                    <div class="option-row">
                        <input type="radio" name="correctAnswer1" id="q1opt1" checked>
                        <input type="text" class="option-text" value="Structured Query Language">
                    </div>
                    <div class="option-row">
                        <input type="radio" name="correctAnswer1" id="q1opt2">
                        <input type="text" class="option-text" value="Strong Question Language">
                    </div>
                    <div class="option-row">
                        <input type="radio" name="correctAnswer1" id="q1opt3">
                        <input type="text" class="option-text" value="Structured Question Language">
                    </div>
                </div>
            </div>

            <div class="question-container">
                <div class="question-header">
                    <h2 class="question-label">Question 02</h2>
                    <button class="delete-question-btn" title="Remove Question">✖</button>
                </div>
                
                <textarea class="question-input" placeholder="Type your quiz question here..."></textarea>
                
                <div class="options-group">
                    <div class="option-row">
                        <input type="radio" name="correctAnswer2" id="q2opt1" checked>
                        <input type="text" class="option-text" placeholder="Option 1">
                    </div>
                    <div class="option-row">
                        <input type="radio" name="correctAnswer2" id="q2opt2">
                        <input type="text" class="option-text" placeholder="Option 2">
                    </div>
                    <div class="option-row">
                        <input type="radio" name="correctAnswer2" id="q2opt3">
                        <input type="text" class="option-text" placeholder="Option 3">
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="add-question-btn">+ Add Another Question</button>
                <button type="submit" class="save-btn">Save Entire Quiz</button>
            </div>

        </div>
    </main>
</body>
</html>