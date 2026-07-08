<?php
// Identify the current page to handle active states dynamically
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
    .top-right-nav-container {
        position: absolute;
        top: 25px;
        right: 40px;
        display: flex;
        align-items: center;
        gap: 14px;
        z-index: 100;
    }

    /* Outer Boundary Circle - Filled with Black Background and Black Border */
    .nav-circle-btn {
        position: relative;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background-color: #000000;
        border: 2px solid #000000; /* Border color now matches background exactly */
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.2s ease-in-out;
        box-sizing: border-box;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    /* Standardizes the bounding frame size for all 3 icons */
    .nav-icon-box {
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease-in-out;
        pointer-events: none;
    }

    /* Forces all 3 custom SVG icons to scale identically and stay inside the circle boundaries */
    .nav-icon-box img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
        /* Converts base black icons into crisp white initially */
        filter: invert(1) brightness(2);
        transition: filter 0.2s ease-in-out;
    }

    /* HOVER HIGHLIGHT STATE - Turns background and border to Light Blue */
    .nav-circle-btn:hover {
        background-color: #64b5f6; /* Clean light blue highlight */
        border-color: #64b5f6;     /* Border matches the light blue highlight on hover */
    }

    /* Keeps the icon looking crisp when hovering or active */
    .nav-circle-btn:hover .nav-icon-box img {
        filter: invert(1) brightness(2);
    }

    /* ACTIVE STATE - Persistent color to highlight the currently active page */
    .nav-circle-btn.active-view {
        background-color: #1565c0; /* Distinct blue to focus currently viewed module */
        border-color: #1565c0;
    }
    
    .nav-circle-btn.active-view .nav-icon-box img {
        filter: invert(1) brightness(2);
    }

    /* Tooltip Label Positioned Directly Below the Nav Element */
    .nav-circle-btn .nav-tooltip {
        position: absolute;
        top: 50px;
        left: 50%;
        transform: translateX(-50%) translateY(-4px);
        background-color: #000000;
        color: #ffffff;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        font-size: 11px;
        font-weight: 500;
        padding: 4px 9px;
        border-radius: 4px;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.15s ease, transform 0.15s ease;
        pointer-events: none;
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        border: 1px solid rgba(255,255,255,0.15);
    }

    /* Up-facing arrow indicator for the tooltip label box */
    .nav-circle-btn .nav-tooltip::before {
        content: '';
        position: absolute;
        top: -4px;
        left: 50%;
        transform: translateX(-50%);
        border-width: 0 4px 4px 4px;
        border-style: solid;
        border-color: transparent transparent #000000 transparent;
    }

    /* Triggers text fade-in and smooth slide on hover */
    .nav-circle-btn:hover .nav-tooltip {
        opacity: 1;
        visibility: visible;
        transform: translateX(-50%) translateY(0);
    }
</style>

<div class="top-right-nav-container">
    <a href="manageStudentsAdmin.php" class="nav-circle-btn <?php echo ($current_page == 'manageStudentsAdmin.php') ? 'active-view' : ''; ?>">
        <div class="nav-icon-box">
            <img src="Aset/StudentIcon.svg" alt="Student">
        </div>
        <span class="nav-tooltip">Student</span>
    </a>

    <a href="manageLecturersAdmin.php" class="nav-circle-btn <?php echo ($current_page == 'manageLecturersAdmin.php') ? 'active-view' : ''; ?>">
        <div class="nav-icon-box">
            <img src="Aset/LecturerIcon.svg" alt="Lecturer">
        </div>
        <span class="nav-tooltip">Lecturer</span>
    </a>

    <a href="manageSubjectsAdmin.php" class="nav-circle-btn <?php echo ($current_page == 'manageSubjectsAdmin.php') ? 'active-view' : ''; ?>">
        <div class="nav-icon-box">
            <img src="Aset/SubjectIcon.svg" alt="Subject">
        </div>
        <span class="nav-tooltip">Subject</span>
    </a>
</div>