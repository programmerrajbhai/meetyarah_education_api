<div class="sidebar">
    <div class="brand">🎓 Super Admin</div>
    <a href="index.php" class="<?= basename($_SERVER['PHP_SELF'])=='index.php'?'active':'' ?>">
        <i class="fas fa-home me-2"></i> Dashboard
    </a>
    <hr style="border-color: rgba(255,255,255,0.2);">
    
    <a href="classes.php" class="<?= basename($_SERVER['PHP_SELF'])=='classes.php'?'active':'' ?>">
        <i class="fas fa-school me-2"></i> 1. Manage Classes
    </a>
    <a href="groups.php" class="<?= basename($_SERVER['PHP_SELF'])=='groups.php'?'active':'' ?>">
        <i class="fas fa-users me-2"></i> 2. Manage Groups
    </a>
    <a href="subjects.php" class="<?= basename($_SERVER['PHP_SELF'])=='subjects.php'?'active':'' ?>">
        <i class="fas fa-book me-2"></i> 3. Manage Subjects
    </a>
    <a href="exams.php" class="<?= basename($_SERVER['PHP_SELF'])=='exams.php'?'active':'' ?>">
        <i class="fas fa-file-alt me-2"></i> 4. Create Exams
    </a>
    
    <hr style="border-color: rgba(255,255,255,0.2);">
    
    <a href="add_question.php" class="<?= basename($_SERVER['PHP_SELF'])=='add_question.php'?'active':'' ?>">
        <i class="fas fa-plus-circle me-2"></i> Add MCQ
    </a>
    <a href="view_questions.php" class="<?= basename($_SERVER['PHP_SELF'])=='view_questions.php'?'active':'' ?>">
        <i class="fas fa-list me-2"></i> Question Bank
    </a>
</div>