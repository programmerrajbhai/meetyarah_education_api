<?php
// ১. ডাটাবেস কানেকশন (সবার আগে)
include 'includes/db_connect.php';

// =======================================================
// ২. AJAX HANDLER (JSON রেসপন্সের জন্য)
// =======================================================
if(isset($_GET['ajax_action'])) {
    // বাফার ক্লিয়ার (যাতে অন্য কোনো HTML না আসে)
    if (ob_get_length()) ob_clean();
    
    $action = $_GET['ajax_action'];
    $id = intval($_GET['id']);

    // প্রশ্নের ডাটা লোড
    if($action == 'get_questions_data') {
        // এক্সাম ইনফো (ক্লাস/গ্রুপ সহ)
        $exam_sql = "SELECT e.title, e.time_limit, s.name as subject, c.name as class_name 
                     FROM exams e 
                     LEFT JOIN subjects s ON e.subject_id = s.id 
                     LEFT JOIN groups g ON s.group_id = g.id 
                     LEFT JOIN classes c ON g.class_id = c.id
                     WHERE e.id = $id";
        $exam_res = $conn->query($exam_sql);

        if($exam_res->num_rows > 0) {
            $exam_info = $exam_res->fetch_assoc();
            
            // সাবজেক্ট ডিলিট হলে হ্যান্ডেল করা
            if($exam_info['subject'] == null) { $exam_info['subject'] = 'Subject Deleted'; }

            // প্রশ্ন লোড
            $q_sql = "SELECT * FROM questions WHERE exam_id = $id ORDER BY id ASC";
            $q_res = $conn->query($q_sql);
            
            $questions = [];
            while($row = $q_res->fetch_assoc()) {
                $questions[] = $row;
            }
            
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'info' => $exam_info, 'questions' => $questions]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Exam not found']);
        }
        exit; 
    }
}

// =======================================================
// ৩. HTML পেজ শুরু
// =======================================================
include 'includes/header.php';

// এক্সাম লিস্ট লোড (কার্ড ভিউয়ের জন্য)
$list_sql = "SELECT e.id, e.title, e.time_limit, s.name as subject, c.name as class_name, g.name as group_name 
             FROM exams e 
             LEFT JOIN subjects s ON e.subject_id = s.id 
             LEFT JOIN groups g ON s.group_id = g.id 
             LEFT JOIN classes c ON g.class_id = c.id 
             ORDER BY e.id DESC";
$exam_list = $conn->query($list_sql);
?>

<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    body { background-color: #f3f4f6; font-family: 'Hind Siliguri', sans-serif; }

    /* --- EXAM LIST CARD DESIGN --- */
    .exam-list-container { max-width: 1000px; margin: 0 auto; }
    
    .exam-card {
        background: white;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 15px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        border: 1px solid #f0f0f0;
        transition: all 0.3s ease;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        overflow: hidden;
    }

    .exam-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        border-color: #4361ee;
    }

    .exam-card::before {
        content: '';
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 5px;
        background: #e5e7eb;
        transition: 0.3s;
    }

    .exam-card:hover::before { background: #4361ee; }

    .exam-info h4 { margin: 0 0 5px 0; font-weight: 700; color: #1f2937; font-size: 18px; }
    
    .meta-badges { display: flex; gap: 10px; margin-bottom: 8px; }
    .badge-custom {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    .badge-class { background: #e0f2fe; color: #0369a1; }
    .badge-group { background: #f3e8ff; color: #7e22ce; }
    .badge-subject { background: #dcfce7; color: #15803d; }

    .time-info { font-size: 14px; color: #6b7280; font-weight: 500; }
    .time-info i { color: #f59e0b; margin-right: 5px; }

    .action-btn {
        background: #4361ee;
        color: white;
        border: none;
        padding: 10px 25px;
        border-radius: 50px;
        font-weight: 600;
        transition: 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }
    .action-btn:hover { background: #3a50d6; color: white; transform: scale(1.05); }

    /* --- PAPER PREVIEW STYLES (Print) --- */
    #paperArea { 
        display: none; 
        background: #525659; 
        padding: 40px; 
        min-height: 100vh; 
        position: fixed; 
        top: 0; left: 0; right: 0; bottom: 0; 
        z-index: 9999; 
        overflow-y: auto; 
    }

    .exam-sheet {
        background: white;
        width: 210mm;
        min-height: 297mm;
        padding: 40px 50px; /* Standard Print Margin */
        margin: 0 auto;
        box-shadow: 0 0 20px rgba(0,0,0,0.5);
        font-family: 'Hind Siliguri', sans-serif;
        color: #000;
        box-sizing: border-box;
    }

    .sheet-header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 30px; }
    .inst-name { font-size: 26px; font-weight: 700; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 1px; }
    .exam-name { font-size: 20px; font-weight: 600; margin-bottom: 10px; }
    .meta-info { 
        display: flex; justify-content: space-between; 
        font-weight: 600; font-size: 16px; 
        border-top: 1px dashed #ccc; 
        padding-top: 10px;
    }

    /* 2-Column Layout for Questions */
    .question-grid {
        column-count: 2;
        column-gap: 50px;
        column-rule: 1px solid #e5e5e5;
    }

    .q-item {
        break-inside: avoid;
        margin-bottom: 20px;
        font-size: 15px;
        line-height: 1.5;
        text-align: justify;
    }

    .q-text { font-weight: 700; display: block; margin-bottom: 6px; }
    
    .options-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 5px 15px;
        margin-left: 18px;
        font-size: 14px;
    }
    
    .opt-row { display: flex; align-items: baseline; }
    .opt-idx { font-weight: 700; margin-right: 5px; font-size: 13px; }

    /* Controls */
    .preview-controls { width: 210mm; margin: 0 auto 20px auto; display: flex; justify-content: space-between; }

    /* PRINT QUERY */
    @media print {
        body * { visibility: hidden; }
        #paperArea { display: block !important; position: absolute; top: 0; left: 0; background: white; padding: 0; margin: 0; width: 100%; height: 100%; overflow: visible; }
        .exam-sheet { visibility: visible; box-shadow: none; margin: 0; width: 100%; padding: 0 30px; }
        .exam-sheet * { visibility: visible; }
        .preview-controls { display: none !important; }
    }
</style>

<div class="container-fluid p-0">

    <div id="examListArea" class="exam-list-container">
        <div class="d-flex justify-content-between align-items-center mb-5 mt-3">
            <div>
                <h3 class="fw-bold text-dark mb-1"><i class="fas fa-layer-group me-2 text-primary"></i>Exam Dashboard</h3>
                <p class="text-muted mb-0">Select an exam to view or print the question paper</p>
            </div>
            <a href="add_question.php" class="btn btn-dark rounded-pill px-4"><i class="fas fa-plus me-2"></i>Create New</a>
        </div>
        
        <div class="list-wrapper">
            <?php if($exam_list->num_rows > 0): ?>
                <?php while($row = $exam_list->fetch_assoc()): ?>
                <div class="exam-card">
                    <div class="exam-info">
                        <div class="meta-badges">
                            <span class="badge-custom badge-class"><i class="fas fa-school me-1"></i> <?= $row['class_name'] ?? 'Class N/A' ?></span>
                            <span class="badge-custom badge-group"><?= $row['group_name'] ?? 'General' ?></span>
                            <span class="badge-custom badge-subject"><?= $row['subject'] ?? 'Subject N/A' ?></span>
                        </div>
                        <h4><?= $row['title'] ?></h4>
                        <div class="time-info">
                            <i class="far fa-clock"></i> Time: <?= $row['time_limit'] ?> Minutes
                        </div>
                    </div>
                    <div class="action">
                        <button onclick="loadExamData(<?= $row['id'] ?>)" class="action-btn shadow-sm">
                            <i class="fas fa-eye me-2"></i> View Paper
                        </button>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486754.png" width="100" class="opacity-50 mb-3">
                    <h5 class="text-muted">No Exams Found</h5>
                    <a href="exams.php" class="btn btn-outline-primary rounded-pill mt-2">Create Exam</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="paperArea">
        <div class="preview-controls">
            <button onclick="closePaperView()" class="btn btn-light fw-bold shadow-lg text-dark"><i class="fas fa-arrow-left me-2"></i> Back to Dashboard</button>
            <button onclick="window.print()" class="btn btn-success fw-bold shadow-lg px-4"><i class="fas fa-print me-2"></i> Print Paper</button>
        </div>

        <div class="exam-sheet">
            <div class="sheet-header" contenteditable="true" title="Click to edit header">
                <div class="inst-name">Vitargarh Ideal Coaching Center</div>
                <div class="exam-name" id="pExamName">Loading Name...</div>
                <div class="meta-info">
                    <span id="pSubject">Subject: ...</span>
                    <span>Marks: 100 | Time: <span id="pTime">00</span> min</span>
                </div>
            </div>
            
            <div class="question-grid" id="sheetBody">
                </div>
        </div>
    </div>

</div>

<script>
// --- VIEW SWITCHING ---
function closePaperView() {
    document.getElementById('paperArea').style.display = 'none';
    document.getElementById('examListArea').style.display = 'block';
}

// --- DATA LOADING ---
function loadExamData(examId) {
    // Show loading state (Optional UI feedback)
    const btn = document.activeElement;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
    
    fetch(`view_questions.php?ajax_action=get_questions_data&id=${examId}`)
    .then(res => res.json())
    .then(data => {
        if(data.status === 'error') {
            alert(data.message);
            btn.innerHTML = originalText;
            return;
        }

        // 1. Set Header Info
        document.getElementById('pExamName').innerText = data.info.title;
        document.getElementById('pSubject').innerText = "Subject: " + data.info.subject + " (" + (data.info.class_name || '') + ")";
        document.getElementById('pTime').innerText = data.info.time_limit;

        // 2. Render Paper Content
        renderPaper(data.questions);

        // 3. Switch View
        document.getElementById('examListArea').style.display = 'none';
        document.getElementById('paperArea').style.display = 'block';
        
        btn.innerHTML = originalText; // Reset button
    })
    .catch(err => {
        console.error(err);
        alert("Failed to load questions. Check console.");
        btn.innerHTML = originalText;
    });
}

// Render Questions (Bangla Format)
function renderPaper(questions) {
    const container = document.getElementById('sheetBody');
    
    if(questions.length === 0) {
        container.innerHTML = '<div style="text-align:center; padding:50px; color:#999;">No questions added to this exam yet.</div>';
        return;
    }

    // Bangla Number Converter
    const toBn = n => n.toString().replace(/\d/g, d => "০১২৩৪৫৬৭৮৯"[d]);
    let html = '';

    questions.forEach((q, index) => {
        html += `
        <div class="q-item">
            <span class="q-text">${toBn(index + 1)}. ${q.question}</span>
            <div class="options-grid">
                <div class="opt-row"><span class="opt-idx">ক.</span> ${q.option_a}</div>
                <div class="opt-row"><span class="opt-idx">খ.</span> ${q.option_b}</div>
                <div class="opt-row"><span class="opt-idx">গ.</span> ${q.option_c}</div>
                <div class="opt-row"><span class="opt-idx">ঘ.</span> ${q.option_d}</div>
            </div>
        </div>`;
    });
    
    container.innerHTML = html;
}
</script>

<?php include 'includes/footer.php'; ?>