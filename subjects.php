<?php
include 'includes/db_connect.php';
include 'includes/header.php';

// --- ১. সাবজেক্ট অ্যাড করার লজিক ---
if(isset($_POST['add_subject'])) {
    $group_id = $_POST['group_id'];
    $name = $_POST['name'];
    $code = $_POST['code']; // সাবজেক্ট কোড (অপশনাল)

    $sql = "INSERT INTO subjects (group_id, name, code) VALUES ('$group_id', '$name', '$code')";
    if($conn->query($sql)) {
        echo "<script>window.location='subjects.php';</script>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}

// --- ২. ডিলিট লজিক ---
if(isset($_GET['del'])) {
    $id = $_GET['del'];
    $conn->query("DELETE FROM subjects WHERE id=$id");
    echo "<script>window.location='subjects.php';</script>";
}
?>

<div class="row">
    <div class="col-md-4">
        <div class="card-custom p-4">
            <h4 class="mb-3">Add New Subject</h4>
            <form method="POST">
                <div class="mb-3">
                    <label class="fw-bold">Select Group</label>
                    <select name="group_id" class="form-control" required>
                        <option value="">-- Select Group --</option>
                        <?php 
                        // গ্রুপ লিস্ট (সাথে ক্লাসের নামও দেখাবে)
                        $groups = $conn->query("SELECT g.id, g.name, c.name as class_name 
                                              FROM groups g 
                                              JOIN classes c ON g.class_id = c.id 
                                              ORDER BY c.name ASC");
                        while($row = $groups->fetch_assoc()): ?>
                            <option value="<?= $row['id'] ?>">
                                <?= $row['name'] ?> (<?= $row['class_name'] ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="fw-bold">Subject Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Physics" required>
                </div>

                <div class="mb-3">
                    <label class="fw-bold">Subject Code</label>
                    <input type="text" name="code" class="form-control" placeholder="e.g. 101">
                </div>

                <button type="submit" name="add_subject" class="btn btn-primary-custom w-100">Add Subject</button>
            </form>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card-custom p-4">
            <h4 class="mb-3">All Subjects List</h4>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Subject</th>
                            <th>Code</th>
                            <th>Group (Class)</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // সাবজেক্ট, গ্রুপ এবং ক্লাসের জয়েন কুয়েরি
                        $sql = "SELECT s.*, g.name as group_name, c.name as class_name 
                                FROM subjects s 
                                JOIN groups g ON s.group_id = g.id 
                                JOIN classes c ON g.class_id = c.id 
                                ORDER BY s.id DESC";
                        $res = $conn->query($sql);
                        
                        while($row = $res->fetch_assoc()):
                        ?>
                        <tr>
                            <td class="fw-bold text-primary"><?= $row['name'] ?></td>
                            <td><span class="badge bg-secondary"><?= $row['code'] ?></span></td>
                            <td><?= $row['group_name'] ?> <small class="text-muted">(<?= $row['class_name'] ?>)</small></td>
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