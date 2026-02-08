<?php
include 'includes/db_connect.php';
include 'includes/header.php';

// Pagination
$limit = 20;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch Logic with Exam Name Join
$sql = "SELECT q.*, e.title as exam_title, s.name as sub_name 
        FROM questions q 
        JOIN exams e ON q.exam_id = e.id 
        JOIN subjects s ON e.subject_id = s.id 
        ORDER BY q.id DESC LIMIT $offset, $limit";
$result = $conn->query($sql);

// Count for pagination
$total_rows = $conn->query("SELECT COUNT(*) FROM questions")->fetch_row()[0];
$total_pages = ceil($total_rows / $limit);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-primary"><i class="fas fa-database me-2"></i> Question Bank</h3>
    <a href="add_question.php" class="btn btn-primary-custom shadow-sm"><i class="fas fa-plus-circle me-2"></i> Add New MCQ</a>
</div>

<div class="card-custom p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-secondary">
                <tr>
                    <th class="ps-4">ID</th>
                    <th>Question</th>
                    <th>Subject / Exam</th>
                    <th>Options</th>
                    <th>Answer</th>
                    <th class="text-end pe-4">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td class="ps-4 fw-bold">#<?= $row['id'] ?></td>
                    <td style="max-width: 300px;">
                        <span class="fw-bold d-block text-truncate"><?= substr($row['question'], 0, 80) ?>...</span>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="badge bg-primary bg-opacity-10 text-primary mb-1"><?= $row['sub_name'] ?></span>
                            <span class="text-muted small"><?= $row['exam_title'] ?></span>
                        </div>
                    </td>
                    <td class="small text-muted">
                        A: <?= substr($row['option_a'],0,10) ?>..<br>
                        B: <?= substr($row['option_b'],0,10) ?>..
                    </td>
                    <td><span class="badge bg-success"><?= $row['correct'] ?></span></td>
                    <td class="text-end pe-4">
                        <a href="delete.php?type=question&id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('Delete this question?')">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <div class="p-3 border-top d-flex justify-content-end">
        <nav>
            <ul class="pagination mb-0">
                <?php for($i=1; $i<=$total_pages; $i++): ?>
                <li class="page-item <?= $i==$page ? 'active':'' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
</div>

<?php include 'includes/footer.php'; ?>