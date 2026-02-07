<?php
include 'includes/db_connect.php';
include 'includes/header.php';

if(isset($_POST['add_group'])) {
    $class_id = $_POST['class_id'];
    $name = $_POST['name'];
    $conn->query("INSERT INTO groups (class_id, name) VALUES ('$class_id', '$name')");
    echo "<script>window.location='groups.php';</script>";
}
?>

<div class="row">
    <div class="col-md-4">
        <div class="card-custom p-4">
            <h4>Add Group</h4>
            <form method="POST">
                <div class="mb-3">
                    <label>Select Class</label>
                    <select name="class_id" class="form-control" required>
                        <option value="">-- Select --</option>
                        <?php 
                        $classes = $conn->query("SELECT * FROM classes");
                        while($c = $classes->fetch_assoc()): ?>
                            <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Group Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Science, Arts" required>
                </div>
                <button type="submit" name="add_group" class="btn btn-primary-custom w-100">Add Group</button>
            </form>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card-custom p-4">
            <h4>Groups List</h4>
            <table class="table">
                <thead><tr><th>Group</th><th>Under Class</th><th>Action</th></tr></thead>
                <tbody>
                    <?php
                    $res = $conn->query("SELECT g.*, c.name as class_name FROM groups g JOIN classes c ON g.class_id = c.id ORDER BY g.id DESC");
                    while($row = $res->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= $row['name'] ?></td>
                        <td><span class="badge bg-info"><?= $row['class_name'] ?></span></td>
                        <td><a href="#" class="text-danger"><i class="fas fa-trash"></i></a></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>