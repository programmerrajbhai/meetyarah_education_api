<?php
session_start();
include 'includes/db_connect.php';
include 'includes/header.php';

// --- ১. আনলক লজিক (Unlock Logic) ---
if(isset($_GET['action']) && $_GET['action'] == 'unlock') {
    unset($_SESSION['locked_exam_id']);
    echo "<script>window.location='add_question.php';</script>";
    exit;
}

// --- ২. AJAX হ্যান্ডলার ---
if(isset($_GET['ajax_action'])) {
    $id = intval($_GET['id']);
    $action = $_GET['ajax_action'];
    
    if($action == 'get_groups') {
        $res = $conn->query("SELECT * FROM groups WHERE class_id=$id");
        echo "<option value=''>Select Group</option>";
        while($r = $res->fetch_assoc()) { echo "<option value='".$r['id']."'>".$r['name']."</option>"; }
    }
    if($action == 'get_subjects') {
        $res = $conn->query("SELECT * FROM subjects WHERE group_id=$id");
        echo "<option value=''>Select Subject</option>";
        while($r = $res->fetch_assoc()) { echo "<option value='".$r['id']."'>".$r['name']."</option>"; }
    }
    if($action == 'get_exams') {
        $res = $conn->query("SELECT * FROM exams WHERE subject_id=$id");
        echo "<option value=''>Select Exam</option>";
        while($r = $res->fetch_assoc()) { echo "<option value='".$r['id']."'>".$r['title']."</option>"; }
    }
    exit;
}

// --- ৩. প্রশ্ন সাবমিশন লজিক ---
if(isset($_POST['submit_q'])) {
    $exam_id = $_POST['final_exam_id']; 
    $q = $conn->real_escape_string($_POST['question']);
    $a = $conn->real_escape_string($_POST['opt_a']);
    $b = $conn->real_escape_string($_POST['opt_b']);
    $c = $conn->real_escape_string($_POST['opt_c']);
    $d = $conn->real_escape_string($_POST['opt_d']);
    $cor = $_POST['correct'];
    $should_lock = $_POST['lock_state']; // ১ হলে লক থাকবে

    if(!empty($exam_id) && !empty($q)) {
        $conn->query("INSERT INTO questions (exam_id, question, option_a, option_b, option_c, option_d, correct) 
                      VALUES ('$exam_id', '$q', '$a', '$b', '$c', '$d', '$cor')");
        
        $_SESSION['msg'] = "Question added successfully!";
        $_SESSION['msg_type'] = "success";

        // যদি ইউজার লক করে থাকে, তাহলে সেশনে আইডি সেভ করো
        if($should_lock == '1') {
            $_SESSION['locked_exam_id'] = $exam_id;
        }
    } else {
        $_SESSION['msg'] = "Error: Exam context missing!";
        $_SESSION['msg_type'] = "error";
    }
    echo "<script>window.location='add_question.php';</script>";
    exit;
}

// --- ৪. লকড ডেটা চেক করা ---
$locked_context = null;
if(isset($_SESSION['locked_exam_id'])) {
    $lid = $_SESSION['locked_exam_id'];
    // জয়েন কুয়েরি দিয়ে ক্লাসের নাম, সাবজেক্ট সব নিয়ে আসা হচ্ছে
    $sql = "SELECT e.title as exam, s.name as subject, g.name as group_name, c.name as class 
            FROM exams e 
            JOIN subjects s ON e.subject_id = s.id 
            JOIN groups g ON s.group_id = g.id 
            JOIN classes c ON g.class_id = c.id 
            WHERE e.id = $lid";
    $locked_context = $conn->query($sql)->fetch_assoc();
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php if(isset($_SESSION['msg'])): ?>
<script>
    Swal.fire({
        toast: true, position: 'top-end', icon: '<?= $_SESSION['msg_type'] ?>', 
        title: '<?= $_SESSION['msg'] ?>', showConfirmButton: false, timer: 3000
    });
</script>
<?php unset($_SESSION['msg']); endif; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card p-4 border-0 shadow-sm rounded-4">
            
            <form method="POST">
                <input type="hidden" name="final_exam_id" id="hidden_exam_id" value="<?= isset($_SESSION['locked_exam_id']) ? $_SESSION['locked_exam_id'] : '' ?>">
                <input type="hidden" name="lock_state" id="lock_state" value="<?= isset($_SESSION['locked_exam_id']) ? '1' : '0' ?>">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold text-primary mb-0"><i class="fas fa-plus-circle me-2"></i>Add Question</h4>
                    
                    <?php if($locked_context): ?>
                        <a href="add_question.php?action=unlock" class="btn btn-danger rounded-pill px-4 shadow-sm">
                            <i class="fas fa-unlock me-2"></i> Unlock Context
                        </a>
                    <?php else: ?>
                        <button type="button" id="lockBtn" class="btn btn-outline-primary rounded-pill px-4" onclick="toggleLock()">
                            <i class="fas fa-lock me-2"></i> Lock Context
                        </button>
                    <?php endif; ?>
                </div>

                <?php if($locked_context): ?>
                    <div class="alert alert-success border-0 shadow-sm d-flex align-items-center p-3 rounded-3 mb-4" role="alert" style="background: #d1e7dd;">
                        <div class="fs-1 text-success me-3"><i class="fas fa-check-circle"></i></div>
                        <div>
                            <h6 class="fw-bold text-success mb-1">Context Locked Successfully!</h6>
                            <small class="text-dark opacity-75">
                                <?= $locked_context['class'] ?> <i class="fas fa-angle-right mx-1"></i> 
                                <?= $locked_context['group_name'] ?> <i class="fas fa-angle-right mx-1"></i> 
                                <?= $locked_context['subject'] ?> <i class="fas fa-angle-right mx-1"></i> 
                                <strong><?= $locked_context['exam'] ?></strong>
                            </small>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row g-3 mb-4 p-3 bg-light rounded-4 border" id="contextArea">
                        <div class="col-md-3">
                            <select id="class_select" class="form-control" onchange="loadGroups(this.value)">
                                <option value="">Select Class</option>
                                <?php $cls = $conn->query("SELECT * FROM classes"); while($c = $cls->fetch_assoc()) { echo "<option value='".$c['id']."'>".$c['name']."</option>"; } ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="group_select" class="form-control" onchange="loadSubjects(this.value)" disabled><option>Group</option></select>
                        </div>
                        <div class="col-md-3">
                            <select id="subject_select" class="form-control" onchange="loadExams(this.value)" disabled><option>Subject</option></select>
                        </div>
                        <div class="col-md-3">
                            <select id="exam_select" class="form-control fw-bold text-primary" disabled onchange="setExamId(this.value)">
                                <option value="">Exam</option>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="mb-4">
                    <label class="fw-bold mb-2">Question Title</label>
                    <textarea name="question" id="qInp" class="form-control" rows="3" placeholder="Type your question here..." oninput="updateLive()" required autofocus></textarea>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6"><input type="text" name="opt_a" id="opA" class="form-control" placeholder="Option A" oninput="updateLive()" required></div>
                    <div class="col-md-6"><input type="text" name="opt_b" id="opB" class="form-control" placeholder="Option B" oninput="updateLive()" required></div>
                    <div class="col-md-6"><input type="text" name="opt_c" id="opC" class="form-control" placeholder="Option C" oninput="updateLive()" required></div>
                    <div class="col-md-6"><input type="text" name="opt_d" id="opD" class="form-control" placeholder="Option D" oninput="updateLive()" required></div>
                </div>

                <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3 shadow-sm border">
                    <div class="d-flex align-items-center w-50">
                        <label class="fw-bold me-3 text-success">Answer:</label>
                        <select name="correct" id="corAns" class="form-control w-50 border-success fw-bold text-success" onchange="updateLive()">
                            <option value="A">Option A</option><option value="B">Option B</option>
                            <option value="C">Option C</option><option value="D">Option D</option>
                        </select>
                    </div>
                    <button type="submit" name="submit_q" class="btn btn-primary px-5 py-2 fw-bold rounded-pill shadow">
                        <i class="fas fa-save me-2"></i> Save & Next
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-lg rounded-5 overflow-hidden" style="height: 600px; border: 12px solid #2d3436;">
            <div class="bg-primary p-3 text-white">
                <div class="d-flex justify-content-between small opacity-75 mb-1"><span>9:41</span><span><i class="fas fa-battery-full"></i></span></div>
                <h6 class="mb-0 fw-bold text-truncate" id="prevTitle">
                    <?= $locked_context ? $locked_context['exam'] : 'Select Exam First...' ?>
                </h6>
            </div>
            <div class="p-4 bg-light h-100 overflow-auto">
                <h5 class="fw-bold mb-4 text-dark" id="pQ">Question will appear here...</h5>
                
                <div class="p-3 bg-white border rounded mb-2 shadow-sm transition-all" id="pA"><span class="badge bg-light text-dark me-2">A</span> <span id="txtA">...</span></div>
                <div class="p-3 bg-white border rounded mb-2 shadow-sm transition-all" id="pB"><span class="badge bg-light text-dark me-2">B</span> <span id="txtB">...</span></div>
                <div class="p-3 bg-white border rounded mb-2 shadow-sm transition-all" id="pC"><span class="badge bg-light text-dark me-2">C</span> <span id="txtC">...</span></div>
                <div class="p-3 bg-white border rounded mb-2 shadow-sm transition-all" id="pD"><span class="badge bg-light text-dark me-2">D</span> <span id="txtD">...</span></div>
                
                <button class="btn btn-primary w-100 rounded-pill mt-4 shadow-sm" disabled>Submit Answer</button>
            </div>
        </div>
    </div>
</div>

<script>
// --- JS Logic ---

// Exam ID & Name Set (Only for Unlocked Mode)
function setExamId(val) {
    document.getElementById('hidden_exam_id').value = val;
    let sel = document.getElementById('exam_select');
    document.getElementById('prevTitle').innerText = sel.options[sel.selectedIndex].text;
}

// Client-side Lock Toggle (Before Submit)
// এটি শুধু ভিজ্যুয়াল এফেক্ট দেয় এবং lock_state কে 1 করে দেয়
let isLocked = false;
function toggleLock() {
    let examVal = document.getElementById('hidden_exam_id').value;
    if(!examVal) { Swal.fire('Wait!', 'Please select an exam first to lock it.', 'warning'); return; }

    isLocked = !isLocked;
    const area = document.getElementById('contextArea');
    const btn = document.getElementById('lockBtn');
    const lockInput = document.getElementById('lock_state');

    if(isLocked) {
        // Lock Mode Active
        area.style.pointerEvents = 'none';
        area.style.opacity = '0.5';
        btn.innerHTML = '<i class="fas fa-check me-2"></i> Locked (Will Save)';
        btn.classList.replace('btn-outline-primary', 'btn-success');
        lockInput.value = '1'; // এই ভ্যালুটি সার্ভারে যাবে
        document.getElementById('qInp').focus();
    } else {
        // Unlock
        area.style.pointerEvents = 'auto';
        area.style.opacity = '1';
        btn.innerHTML = '<i class="fas fa-lock me-2"></i> Lock Context';
        btn.classList.replace('btn-success', 'btn-outline-primary');
        lockInput.value = '0';
    }
}

// Live Preview Update
function updateLive() {
    let qVal = document.getElementById('qInp').value;
    document.getElementById('pQ').innerText = qVal ? qVal : "Question will appear here...";
    
    ['A','B','C','D'].forEach(o => {
        let val = document.getElementById('op'+o).value;
        document.getElementById('txt'+o).innerText = val ? val : "...";
        
        // Correct Answer Highlight
        let pDiv = document.getElementById('p'+o);
        pDiv.classList.remove('border-success','bg-success','bg-opacity-10', 'fw-bold');
        if(document.getElementById('corAns').value == o) {
            pDiv.classList.add('border-success','bg-success','bg-opacity-10', 'fw-bold');
        }
    });
}

// AJAX Loaders
function loadGroups(id) { fetch(`add_question.php?ajax_action=get_groups&id=${id}`).then(r=>r.text()).then(d=>{document.getElementById('group_select').innerHTML=d;document.getElementById('group_select').disabled=false;}); }
function loadSubjects(id) { fetch(`add_question.php?ajax_action=get_subjects&id=${id}`).then(r=>r.text()).then(d=>{document.getElementById('subject_select').innerHTML=d;document.getElementById('subject_select').disabled=false;}); }
function loadExams(id) { fetch(`add_question.php?ajax_action=get_exams&id=${id}`).then(r=>r.text()).then(d=>{document.getElementById('exam_select').innerHTML=d;document.getElementById('exam_select').disabled=false;}); }
</script>

<?php include 'includes/footer.php'; ?>