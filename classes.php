<?php
include 'includes/db_connect.php';
include 'includes/header.php';

// Add Class Logic
if(isset($_POST['add_class'])) {
    $name = $_POST['name'];
    $conn->query("INSERT INTO classes (name) VALUES ('$name')");
    echo "<script>window.location='classes.php';</script>";
}

// Delete Logic
if(isset($_GET['del'])) {
    $id = $_GET['del'];
    $conn->query("DELETE FROM classes WHERE id=$id");
    echo "<script>window.location='classes.php';</script>";
}
?>

<div class="row">
    <div class="col-md-4">
        <div class="card-custom p-4">
            <h4>Add New Class</h4>
            <form method="POST">
                <div class="mb-3">
                    <label>Class Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. SSC, HSC" required>
                </div>
                <button type="submit" name="add_class" class="btn btn-primary-custom w-100">Add Class</button>
            </form>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card-custom p-4">
            <h4>All Classes</h4>
            <table class="table">
                <thead><tr><th>ID</th><th>Name</th><th>Action</th></tr></thead>
                <tbody>
                    <?php
                    $res = $conn->query("SELECT * FROM classes ORDER BY id DESC");
                    while($row = $res->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><span class="badge bg-primary"><?= $row['name'] ?></span></td>
                        <td><a href="?del=<?= $row['id'] ?>" class="text-danger"><i class="fas fa-trash"></i></a></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>