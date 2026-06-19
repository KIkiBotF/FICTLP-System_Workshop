<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Chapter Content - CoreKnowledge</title>
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="editSubjectContentStyle.css">
</head>
<body>
    
    <?php include("sidebar.php"); ?>

    <main class="main-content">
        
        <div class="chapter-header-container">
            <h1 class="chapter-title">CHAPTER 1</h1>
        </div>

        <div class="content-grid">
            
            <div class="column">
                <h2 class="column-title">SLIDE</h2>
                
                <div class="content-item">
                    <div class="media-box">
                        <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                    </div>
                    <div class="item-actions">
                        <button class="action-btn remove-btn">Remove</button>
                        <button class="action-btn edit-btn">Edit</button>
                    </div>
                </div>

                <button class="add-box">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                </button>
            </div>

            <div class="column">
                <h2 class="column-title">VIDEO</h2>
                
                <div class="content-item">
                    <div class="media-box">
                        <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#888" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg>
                    </div>
                    <div class="item-actions">
                        <button class="action-btn remove-btn">Remove</button>
                        <button class="action-btn edit-btn">Edit</button>
                    </div>
                </div>

                <div class="content-item">
                    <div class="media-box">
                        <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#888" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg>
                    </div>
                    <div class="item-actions">
                        <button class="action-btn remove-btn">Remove</button>
                        <button class="action-btn edit-btn">Edit</button>
                    </div>
                </div>

                <button class="add-box">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                </button>
            </div>

            <div class="column">
                <h2 class="column-title">QUIZ</h2>
                
                <div class="content-item">
                    <div class="media-box quiz-box">
                        <span class="quiz-text">10 QUESTIONS</span>
                    </div>
                    <div class="item-actions center-actions">
                        <button class="action-btn edit-btn" onclick="window.location.href='editQuizLecturer.php'">Edit</button>
                    </div>
                </div>
            </div>

        </div>

    </main>
</body>
</html>