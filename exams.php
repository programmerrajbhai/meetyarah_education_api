<?php
include 'includes/db_connect.php';
include 'includes/header.php';

// --- ১. এক্সাম অ্যাড করার লজিক ---
if(isset($_POST['add_exam'])) {
    $subject_id = $_POST['subject_id'];
    $title = $_POST['title'];
    $time = $_POST['time_limit']; // সময় (মিনিটে)

    $sql = "INSERT INTO exams (subject_id, title, time_limit) VALUES ('$subject_id', '$title', '$time')";
    if($conn->query($sql)) {
        echo "<script>window.location='exams.php';</script>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}

// --- ২. ডিলিট লজিক ---
if(isset($_GET['del'])) {
    $id = $_GET['del'];
    $conn->query("DELETE FROM exams WHERE id=$id");
    echo "<script>window.location='exams.php';</script>";
}
?>

<div class="row">
    <div class="col-md-4">
        <div class="card-custom p-4">
            <h4 class="mb-3">Create New Exam</h4>
            <form method="POST">
                <div class="mb-3">
                    <label class="fw-bold">Select Subject</label>
                    <select name="subject_id" class="form-control" required>
                        <option value="">-- Select Subject --</option>
                        <?php 
                        // সাবজেক্ট লিস্ট (সাথে গ্রুপ ও ক্লাসের নাম)
                        $subjects = $conn->query("SELECT s.id, s.name, g.name as group_name, c.name as class_name 
                                                FROM subjects s 
                                                JOIN groups g ON s.group_id = g.id 
                                                JOIN classes c ON g.class_id = c.id 
                                                ORDER BY c.name, g.name ASC");
                        while($row = $subjects->fetch_assoc()): ?>
                            <option value="<?= $row['id'] ?>">
                                <?= $row['name'] ?> (<?= $row['group_name'] ?> - <?= $row['class_name'] ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="fw-bold">Exam/Chapter Title</label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. Chapter 1: Motion" required>
                </div>

                <div class="mb-3">
                    <label class="fw-bold">Time Limit (Minutes)</label>
                    <input type="number" name="time_limit" class="form-control" value="20">
                </div>

                <button type="submit" name="add_exam" class="btn btn-primary-custom w-100">Create Exam</button>
            </form>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card-custom p-4">
            <h4 class="mb-3">Exams List</h4>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Exam Title</th>
                            <th>Subject</th>
                            <th>Time</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // এক্সাম, সাবজেক্ট, গ্রুপ এবং ক্লাসের জয়েন কুয়েরি
                        $sql = "SELECT e.*, s.name as sub_name, g.name as group_name, c.name as class_name 
                                FROM exams e 
                                JOIN subjects s ON e.subject_id = s.id 
                                JOIN groups g ON s.group_id = g.id 
                                JOIN classes c ON g.class_id = c.id 
                                ORDER BY e.id DESC";
                        $res = $conn->query($sql);
                        
                        while($row = $res->fetch_assoc()):
                        ?>
                        <tr>
                            <td class="fw-bold text-success"><?= $row['title'] ?></td>
                            <td>
                                <?= $row['sub_name'] ?> <br>
                                <small class="text-muted" style="font-size: 11px;">
                                    <?= $row['group_name'] ?> > <?= $row['class_name'] ?>
                                </small>
                            </td>
                            <td><span class="badge bg-warning text-dark"><?= $row['time_limit'] ?> min</span></td>
                            <td>
                                <a href="?del=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>