<?php
include 'includes/db_connect.php';
include 'includes/header.php';

// সঠিক টেবিল নাম অনুযায়ী ডেটা ফেচ করা
$class_count = $conn->query("SELECT COUNT(*) as total FROM classes")->fetch_assoc()['total'];
$subject_count = $conn->query("SELECT COUNT(*) as total FROM subjects")->fetch_assoc()['total'];
$exam_count = $conn->query("SELECT COUNT(*) as total FROM exams")->fetch_assoc()['total'];
$ques_count = $conn->query("SELECT COUNT(*) as total FROM questions")->fetch_assoc()['total'];
?>

<div class="d-flex justify-content-between align-items-center mb-5">
    <div>
        <h2 class="fw-bold mb-1">Welcome Back!</h2>
        <p class="text-muted">Education Management Overview</p>
    </div>
    <a href="add_question.php" class="btn btn-primary shadow-sm"><i class="fas fa-plus me-2"></i>New MCQ</a>
</div>

<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-primary text-white me-3"><i class="fas fa-school"></i></div>
                <div>
                    <h6 class="text-muted mb-0">Classes</h6>
                    <h3 class="fw-bold mb-0"><?= $class_count ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-success text-white me-3"><i class="fas fa-book"></i></div>
                <div>
                    <h6 class="text-muted mb-0">Subjects</h6>
                    <h3 class="fw-bold mb-0"><?= $subject_count ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-warning text-white me-3"><i class="fas fa-file-alt"></i></div>
                <div>
                    <h6 class="text-muted mb-0">Exams</h6>
                    <h3 class="fw-bold mb-0"><?= $exam_count ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-info text-white me-3"><i class="fas fa-question-circle"></i></div>
                <div>
                    <h6 class="text-muted mb-0">Total MCQ</h6>
                    <h3 class="fw-bold mb-0"><?= $ques_count ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card p-4 shadow-sm border-0">
    <h5 class="fw-bold mb-4">🚀 Quick Access</h5>
    <div class="row g-3">
        <div class="col-md-3">
            <a href="classes.php" class="btn btn-light w-100 p-3 border"><i class="fas fa-cog me-2"></i> Manage Classes</a>
        </div>
        <div class="col-md-3">
            <a href="exams.php" class="btn btn-light w-100 p-3 border"><i class="fas fa-pen-nib me-2"></i> Create Exams</a>
        </div>
        <div class="col-md-3">
            <a href="view_questions.php" class="btn btn-light w-100 p-3 border"><i class="fas fa-database me-2"></i> Question Bank</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>