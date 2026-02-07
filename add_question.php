<?php
include 'includes/db_connect.php';
include 'includes/header.php';

// AJAX Handler (PHP Code inside same file for simplicity)
if(isset($_GET['ajax_action'])) {
    $id = $_GET['id'];
    $action = $_GET['ajax_action'];
    
    if($action == 'get_groups') {
        $res = $conn->query("SELECT * FROM groups WHERE class_id=$id");
        while($r = $res->fetch_assoc()) { echo "<option value='".$r['id']."'>".$r['name']."</option>"; }
    }
    if($action == 'get_subjects') {
        $res = $conn->query("SELECT * FROM subjects WHERE group_id=$id");
        while($r = $res->fetch_assoc()) { echo "<option value='".$r['id']."'>".$r['name']."</option>"; }
    }
    if($action == 'get_exams') {
        $res = $conn->query("SELECT * FROM exams WHERE subject_id=$id");
        while($r = $res->fetch_assoc()) { echo "<option value='".$r['id']."'>".$r['title']."</option>"; }
    }
    exit; // Stop executing rest of the page for AJAX
}

// Insert Question
if(isset($_POST['submit_q'])) {
    $exam_id = $_POST['exam_id'];
    $q = $conn->real_escape_string($_POST['question']);
    $a = $_POST['opt_a']; $b = $_POST['opt_b']; $c = $_POST['opt_c']; $d = $_POST['opt_d'];
    $cor = $_POST['correct'];
    
    $conn->query("INSERT INTO questions (exam_id, question, option_a, option_b, option_c, option_d, correct) VALUES ('$exam_id', '$q', '$a', '$b', '$c', '$d', '$cor')");
    echo "<div class='alert alert-success'>Question Added!</div>";
}
?>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card-custom p-4">
            <h3 class="mb-4 fw-bold text-primary">Add MCQ to Question Bank</h3>
            
            <form method="POST">
                <div class="row mb-4 bg-light p-3 rounded">
                    <div class="col-md-3">
                        <label class="fw-bold">1. Select Class</label>
                        <select id="class_select" class="form-control" onchange="loadGroups(this.value)">
                            <option value="">Select Class</option>
                            <?php 
                            $cls = $conn->query("SELECT * FROM classes");
                            while($c = $cls->fetch_assoc()) { echo "<option value='".$c['id']."'>".$c['name']."</option>"; } 
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="fw-bold">2. Select Group</label>
                        <select id="group_select" class="form-control" onchange="loadSubjects(this.value)" disabled>
                            <option>Select Class First</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="fw-bold">3. Select Subject</label>
                        <select id="subject_select" class="form-control" onchange="loadExams(this.value)" disabled>
                            <option>Select Group First</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="fw-bold text-danger">4. Select Exam/Chapter</label>
                        <select name="exam_id" id="exam_select" class="form-control" required disabled>
                            <option value="">Select Subject First</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="fw-bold">Question Title</label>
                    <textarea name="question" class="form-control" rows="2" placeholder="Enter question here..." required></textarea>
                </div>

                <div class="row g-3">
                    <div class="col-md-6"><input type="text" name="opt_a" class="form-control" placeholder="Option A" required></div>
                    <div class="col-md-6"><input type="text" name="opt_b" class="form-control" placeholder="Option B" required></div>
                    <div class="col-md-6"><input type="text" name="opt_c" class="form-control" placeholder="Option C" required></div>
                    <div class="col-md-6"><input type="text" name="opt_d" class="form-control" placeholder="Option D" required></div>
                </div>

                <div class="mt-3">
                    <label class="fw-bold text-success">Correct Answer</label>
                    <select name="correct" class="form-control w-25" required>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                    </select>
                </div>

                <button type="submit" name="submit_q" class="btn btn-primary-custom w-100 mt-4 py-2 fw-bold">SAVE QUESTION</button>
            </form>
        </div>
    </div>
</div>

<script>
function loadGroups(classId) {
    if(!classId) return;
    document.getElementById('group_select').disabled = false;
    fetch(`add_question.php?ajax_action=get_groups&id=${classId}`)
    .then(res => res.text())
    .then(data => document.getElementById('group_select').innerHTML = '<option value="">Select Group</option>' + data);
}

function loadSubjects(groupId) {
    if(!groupId) return;
    document.getElementById('subject_select').disabled = false;
    fetch(`add_question.php?ajax_action=get_subjects&id=${groupId}`)
    .then(res => res.text())
    .then(data => document.getElementById('subject_select').innerHTML = '<option value="">Select Subject</option>' + data);
}

function loadExams(subId) {
    if(!subId) return;
    document.getElementById('exam_select').disabled = false;
    fetch(`add_question.php?ajax_action=get_exams&id=${subId}`)
    .then(res => res.text())
    .then(data => document.getElementById('exam_select').innerHTML = '<option value="">Select Exam</option>' + data);
}
</script>

<?php include 'includes/footer.php'; ?>