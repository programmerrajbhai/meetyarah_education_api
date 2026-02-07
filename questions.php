<?php
include 'includes/db_connect.php';
include 'includes/header.php';

// Fetch Questions
$sql = "SELECT q.*, c.title as chapter_name FROM edu_questions q 
        JOIN edu_chapters c ON q.chapter_id = c.id 
        ORDER BY q.id DESC";
$result = $conn->query($sql);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold">Question Bank</h3>
    <a href="add_question.php" class="btn btn-primary-custom">+ Add Question</a>
</div>

<div class="card-custom p-4">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Question</th>
                <th>Chapter</th>
                <th>Correct Ans</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td>#<?= $row['id'] ?></td>
                <td><?= substr($row['question_text'], 0, 50) ?>...</td>
                <td><span class="badge bg-info"><?= $row['chapter_name'] ?></span></td>
                <td class="fw-bold text-success"><?= $row['correct_option'] ?></td>
                <td>
                    <a href="delete.php?type=question&id=<?= $row['id'] ?>" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>