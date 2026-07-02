<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Subject</title>
  <link rel="stylesheet" href="subjectStudent.css">
</head>
<body>
<?php include 'sidebarStudent.php'; ?>
  
<script>
    // Fungsi navigasi
    function navigateTo(page) {
        window.location.href = page;
    }

    // Fungsi logout
    function logout() {
        // Hapus session/localStorage jika ada
        // localStorage.removeItem('user');
        window.location.href = 'index.html';
    }
</script>

  <main class="main">
    <h1 class="page-title">Subject</h1>
    <p class="page-subtitle">Choose subject:</p>

    <a href="CoursePageStudent.php" class="card teal" onclick="selectSubject('cpp')">
      <div class="card-header">
        <h2>C++ Programming</h2>
        <span class="lecturer">(Madam Rosleen)</span>
      </div>
      <div class="card-body">
        <div class="topics">
          <div class="topic-item">Basic Syntax & I/O</div>
          <div class="topic-item">Control Structures & Loops</div>
          <div class="topic-item">Functions & Scope</div>
          <div class="topic-item">Arrays & Strings</div>
          <div class="topic-item">Pointers & References</div>
        </div>
      </div>
    </a>   

    <a href="CoursePageStudent.php" class="card blue" onclick="selectSubject('db')">
      <div class="card-header">
        <h2>Database</h2>
        <span class="lecturer">(Madam Mas Aina)</span>
      </div>
      <div class="card-body">
        <div class="topics single-col">
          <div class="topic-item">Introduction to Databases & DBMS,</div>
          <div class="topic-item">Entity-Relationship (ER) Modeling</div>
          <div class="topic-item">Relational Model & Constraints</div>
          <div class="topic-item">Relational Database Normalization</div>
          <div class="topic-item">Structured Query Language (SQL)</div>
        </div>
      </div>
    </a>

    <a href="CoursePageStudent.php" class="card green" onclick="selectSubject('coa')">
      <div class="card-header">
        <h2>Computer Organization and Architecture</h2>
        <span class="lecturer">(Sir Arif)</span>
      </div>
      <div class="card-body">
        <div class="topics single-col">
          <div class="topic-item">Introduction & Von Neumann Architecture</div>
          <div class="topic-item">Computer Evolution & Performance Metrics</div>
          <div class="topic-item">Memory Hierarchy & Cache Memory</div>
          <div class="topic-item">Input/Output Organization & Interfacing</div>
          <div class="topic-item">Pipeline Architecture & Instruction Sets</div>
        </div>
      </div>
    </a>
  </main>

  <script>
    // Fungsi untuk menetapkan subjek aktif pilihan pelajar sebelum berpindah ke halaman pengajian
    // Contoh fungsi pilihan subjek di fail subjects.html
    function selectSubject(subjectCode) {
    // subjectCode mestilah bernilai 'cpp', 'db', atau 'coa'
    localStorage.setItem('selectedQuizSubject', subjectCode);
    window.location.href = 'CoursePageStudent.html';
}
  </script>
</body>
</html>