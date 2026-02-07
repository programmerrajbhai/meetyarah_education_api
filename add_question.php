<?php
include 'includes/db_connect.php';
include 'includes/header.php';

// AJAX Handler (Same as before)
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
    exit;
}

// Insert Question Logic
$msg = "";
if(isset($_POST['submit_q'])) {
    $exam_id = $_POST['exam_id'];
    $q = $conn->real_escape_string($_POST['question']);
    $a = $conn->real_escape_string($_POST['opt_a']);
    $b = $conn->real_escape_string($_POST['opt_b']);
    $c = $conn->real_escape_string($_POST['opt_c']);
    $d = $conn->real_escape_string($_POST['opt_d']);
    $cor = $_POST['correct'];
    
    if($conn->query("INSERT INTO questions (exam_id, question, option_a, option_b, option_c, option_d, correct) VALUES ('$exam_id', '$q', '$a', '$b', '$c', '$d', '$cor')")) {
        $msg = "<div class='alert alert-success alert-dismissible fade show fw-bold'><i class='fas fa-check-circle me-2'></i> Question Saved Successfully! Add Next... <button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    }
}

// Get current question count for auto-numbering (Optional - needs selected exam_id via JS)
$q_count = 1; 
?>

<style>
    .mcq-creator-card { background: #fff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); overflow: hidden; }
    .mcq-header { background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); color: white; padding: 25px; }
    .form-label-custom { font-weight: 600; color: #344767; margin-bottom: 8px; }
    .form-control-custom { border: 2px solid #e9ecef; border-radius: 12px; padding: 12px 15px; font-size: 15px; transition: 0.3s; }
    .form-control-custom:focus { border-color: #2575fc; box-shadow: 0 0 0 4px rgba(37, 117, 252, 0.1); }
    .option-input-group { position: relative; margin-bottom: 15px; }
    .option-badge { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); background: #e9ecef; color: #6c757d; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; }
    .option-input { padding-left: 55px; }
    .preview-card { background: #f8f9fa; border-radius: 25px; border: 8px solid #344767; height: 600px; overflow-y: auto; position: relative; }
    .preview-header { background: #fff; padding: 15px; text-align: center; font-weight: bold; border-bottom: 1px solid #eee; position: sticky; top: 0; z-index: 10; }
    .preview-body { padding: 20px; }
    .preview-question { font-weight: bold; font-size: 18px; margin-bottom: 20px; color: #344767; }
    .preview-option { background: #fff; padding: 12px 15px; border-radius: 12px; margin-bottom: 10px; border: 1px solid #e9ecef; display: flex; align-items: center; }
    .preview-option-badge { width: 25px; height: 25px; background: #e9ecef; border-radius: 50%; margin-right: 10px; }
    .preview-correct { border-color: #28a745; background: #e6ffed; }
    .selection-locked { background: #f8f9fa; pointer-events: none; opacity: 0.8; }
    .btn-lock { background: #fff; color: #2575fc; border: none; font-weight: 600; padding: 8px 15px; border-radius: 10px; cursor: pointer; }
</style>

<div class="container-fluid p-0">
    <?= $msg ?>
    
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="mcq-creator-card">
                <div class="mcq-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1 fw-bold"><i class="fas fa-layer-group me-2"></i> 1. Select Exam Context</h4>
                        <p class="mb-0 opacity-75">Choose class, group, subject & exam first.</p>
                    </div>
                    <button type="button" id="lockBtn" class="btn-lock" onclick="toggleLock()"><i class="fas fa-lock me-2"></i> Lock Selection</button>
                </div>
                
                <div class="p-4 pb-2" id="selectionArea">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label-custom">Class</label>
                            <select id="class_select" class="form-control-custom w-100" onchange="loadGroups(this.value)">
                                <option value="">Select Class</option>
                                <?php $cls = $conn->query("SELECT * FROM classes"); while($c = $cls->fetch_assoc()) { echo "<option value='".$c['id']."'>".$c['name']."</option>"; } ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Group</label>
                            <select id="group_select" class="form-control-custom w-100" onchange="loadSubjects(this.value)" disabled>
                                <option>Select Class First</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Subject</label>
                            <select id="subject_select" class="form-control-custom w-100" onchange="loadExams(this.value)" disabled>
                                <option>Select Group First</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom text-primary">Exam/Chapter</label>
                            <select id="exam_select" class="form-control-custom w-100 fw-bold" required disabled onchange="updatePreviewHeader()">
                                <option value="">Select Subject First</option>
                            </select>
                        </div>
                    </div>
                </div>

                <hr class="my-0" style="opacity: 0.1;">

                <div class="p-4">
                    <h5 class="fw-bold mb-4 text-primary"><i class="fas fa-edit me-2"></i> 2. Create Question #<span id="qNum">1</span></h5>
                    
                    <form method="POST" id="mcqForm">
                        <input type="hidden" name="exam_id" id="hidden_exam_id">

                        <div class="mb-4">
                            <label class="form-label-custom">Question Title</label>
                            <textarea name="question" id="qInput" class="form-control-custom w-100" rows="3" placeholder="Type your question here..." required oninput="updatePreview()"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label-custom">Options</label>
                                <div class="option-input-group">
                                    <span class="option-badge">A</span>
                                    <input type="text" name="opt_a" id="optA" class="form-control-custom option-input w-100" placeholder="Option A" required oninput="updatePreview()">
                                </div>
                                <div class="option-input-group">
                                    <span class="option-badge">B</span>
                                    <input type="text" name="opt_b" id="optB" class="form-control-custom option-input w-100" placeholder="Option B" required oninput="updatePreview()">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom">&nbsp;</label>
                                <div class="option-input-group">
                                    <span class="option-badge">C</span>
                                    <input type="text" name="opt_c" id="optC" class="form-control-custom option-input w-100" placeholder="Option C" required oninput="updatePreview()">
                                </div>
                                <div class="option-input-group">
                                    <span class="option-badge">D</span>
                                    <input type="text" name="opt_d" id="optD" class="form-control-custom option-input w-100" placeholder="Option D" required oninput="updatePreview()">
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 mb-4">
                            <label class="form-label-custom text-success"><i class="fas fa-check-circle me-1"></i> Correct Answer</label>
                            <select name="correct" id="correctSelect" class="form-control-custom w-25 bg-success bg-opacity-10 fw-bold" required onchange="updatePreview()">
                                <option value="A">Option A</option>
                                <option value="B">Option B</option>
                                <option value="C">Option C</option>
                                <option value="D">Option D</option>
                            </select>
                        </div>

                        <button type="submit" name="submit_q" class="btn btn-primary-custom py-3 px-5 fw-bold fs-5 rounded-pill shadow-sm">
                            <i class="fas fa-save me-2"></i> Save & Add Next Question
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <h5 class="fw-bold mb-3 text-center"><i class="fas fa-mobile-alt me-2"></i> Student App Preview</h5>
            <div class="preview-card">
                <div class="preview-header">
                    <span id="previewExamTitle">Select Exam...</span>
                </div>
                <div class="preview-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="badge bg-primary">Q <span id="previewQNum">1</span>/10</span>
                        <span class="text-muted"><i class="far fa-clock me-1"></i> 00:20</span>
                    </div>
                    
                    <div class="preview-question" id="previewQText">Your question will appear here...</div>
                    
                    <div class="preview-option" id="prevOptA"><div class="preview-option-badge"></div> <span id="txtA">Option A</span></div>
                    <div class="preview-option" id="prevOptB"><div class="preview-option-badge"></div> <span id="txtB">Option B</span></div>
                    <div class="preview-option" id="prevOptC"><div class="preview-option-badge"></div> <span id="txtC">Option C</span></div>
                    <div class="preview-option" id="prevOptD"><div class="preview-option-badge"></div> <span id="txtD">Option D</span></div>

                    <div class="text-center mt-4 opacity-50">
                        <button class="btn btn-primary rounded-pill px-4 py-2" disabled>Submit Answer</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// AJAX Loaders
function loadGroups(classId) { /* Same as before */ if(!classId) return; document.getElementById('group_select').disabled = false; fetch(`add_question.php?ajax_action=get_groups&id=${classId}`).then(res => res.text()).then(data => document.getElementById('group_select').innerHTML = '<option value="">Select Group</option>' + data); }
function loadSubjects(groupId) { /* Same as before */ if(!groupId) return; document.getElementById('subject_select').disabled = false; fetch(`add_question.php?ajax_action=get_subjects&id=${groupId}`).then(res => res.text()).then(data => document.getElementById('subject_select').innerHTML = '<option value="">Select Subject</option>' + data); }
function loadExams(subId) { /* Same as before */ if(!subId) return; document.getElementById('exam_select').disabled = false; fetch(`add_question.php?ajax_action=get_exams&id=${subId}`).then(res => res.text()).then(data => document.getElementById('exam_select').innerHTML = '<option value="">Select Exam</option>' + data); }

// Lock Selection & Auto-Set Hidden ID
let isLocked = false;
function toggleLock() {
    const examSelect = document.getElementById('exam_select');
    const hiddenId = document.getElementById('hidden_exam_id');
    const selectionArea = document.getElementById('selectionArea');
    const lockBtn = document.getElementById('lockBtn');

    if (examSelect.value === "") { alert("Please select an Exam/Chapter first!"); return; }

    isLocked = !isLocked;
    if (isLocked) {
        selectionArea.classList.add('selection-locked');
        lockBtn.innerHTML = '<i class="fas fa-unlock me-2"></i> Unlock';
        hiddenId.value = examSelect.value; // Set hidden ID
        document.getElementById('qInput').focus();
    } else {
        selectionArea.classList.remove('selection-locked');
        lockBtn.innerHTML = '<i class="fas fa-lock me-2"></i> Lock Selection';
        hiddenId.value = "";
    }
}

// Live Preview & Auto Numbering Update
let questionCounter = 1; // In real app, fetch count from DB

function updatePreview() {
    document.getElementById('previewQText').innerText = document.getElementById('qInput').value || "Your question will appear here...";
    document.getElementById('txtA').innerText = document.getElementById('optA').value || "Option A";
    document.getElementById('txtB').innerText = document.getElementById('optB').value || "Option B";
    document.getElementById('txtC').innerText = document.getElementById('optC').value || "Option C";
    document.getElementById('txtD').innerText = document.getElementById('optD').value || "Option D";

    // Highlight Correct Option in Preview
    const correct = document.getElementById('correctSelect').value;
    ['A', 'B', 'C', 'D'].forEach(opt => {
        document.getElementById(`prevOpt${opt}`).classList.remove('preview-correct');
    });
    document.getElementById(`prevOpt${correct}`).classList.add('preview-correct');
}

function updatePreviewHeader() {
    const examSelect = document.getElementById('exam_select');
    const selectedText = examSelect.options[examSelect.selectedIndex].text;
    document.getElementById('previewExamTitle').innerText = selectedText !== "Select Exam" ? selectedText : "Select Exam...";
}

// Auto Increment Q Number on Submit Success (Simulated here for UI)
if (window.history.replaceState) { window.history.replaceState(null, null, window.location.href); } // Prevent resubmit
</script>

<?php include 'includes/footer.php'; ?>