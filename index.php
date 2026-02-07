<?php
include 'includes/db_connect.php';
include 'includes/header.php';

// Stats Counting
$cat_count = $conn->query("SELECT COUNT(*) as total FROM edu_categories")->fetch_assoc()['total'];
$sub_count = $conn->query("SELECT COUNT(*) as total FROM edu_subjects")->fetch_assoc()['total'];
$ques_count = $conn->query("SELECT COUNT(*) as total FROM edu_questions")->fetch_assoc()['total'];
?>

<h2 class="mb-4 fw-bold">Dashboard Overview</h2>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card-custom stat-card">
            <div>
                <h5 class="text-muted">Total Categories</h5>
                <h2 class="fw-bold mb-0"><?= $cat_count ?></h2>
            </div>
            <div class="stat-icon bg-primary">
                <i class="fas fa-th-large"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card-custom stat-card">
            <div>
                <h5 class="text-muted">Total Subjects</h5>
                <h2 class="fw-bold mb-0"><?= $sub_count ?></h2>
            </div>
            <div class="stat-icon bg-success">
                <i class="fas fa-book"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card-custom stat-card">
            <div>
                <h5 class="text-muted">Total MCQ Questions</h5>
                <h2 class="fw-bold mb-0"><?= $ques_count ?></h2>
            </div>
            <div class="stat-icon bg-warning">
                <i class="fas fa-question"></i>
            </div>
        </div>
    </div>
</div>

<div class="mt-5">
    <div class="card-custom p-4">
        <h4>🚀 Quick Actions</h4>
        <hr>
        <a href="add_question.php" class="btn btn-primary-custom me-2"><i class="fas fa-plus"></i> Add New Question</a>
        <a href="categories.php" class="btn btn-outline-dark"><i class="fas fa-edit"></i> Manage Categories</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>