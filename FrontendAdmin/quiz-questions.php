<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

checkAdminAuth();
refreshAdminInfo();

$db = Database::getInstance();
$quizId = intval($_GET['quiz_id'] ?? 0);

if (!$quizId) {
    header('Location: quiz-vanhoa.php');
    exit;
}

// Lấy thông tin quiz
$quiz = $db->querySingle("
    SELECT q.*, v.tieu_de as ten_van_hoa
    FROM quiz_van_hoa q
    JOIN van_hoa v ON q.ma_van_hoa = v.ma_van_hoa
    WHERE q.ma_quiz = ?
", [$quizId]);

if (!$quiz) {
    header('Location: quiz-vanhoa.php');
    exit;
}

// Xử lý actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch($action) {
            case 'add_questions':
                // Thêm 10 câu hỏi cùng lúc
                for ($i = 1; $i <= 10; $i++) {
                    $noiDung = trim($_POST["cau_hoi_$i"] ?? '');
                    if (empty($noiDung)) continue;
                    
                    // Thêm câu hỏi
                    $sql = "INSERT INTO cau_hoi_quiz (ma_quiz, noi_dung, thu_tu, diem, giai_thich) VALUES (?, ?, ?, 10, ?)";
                    $db->query($sql, [$quizId, $noiDung, $i, $_POST["giai_thich_$i"] ?? '']);
                    $cauHoiId = $db->getLastInsertId();
                    
                    // Thêm 4 đáp án
                    for ($j = 1; $j <= 4; $j++) {
                        $dapAn = trim($_POST["dap_an_{$i}_{$j}"] ?? '');
                        if (empty($dapAn)) continue;
                        
                        $laDung = isset($_POST["dap_an_dung_$i"]) && $_POST["dap_an_dung_$i"] == $j ? 1 : 0;
                        
                        $sqlDapAn = "INSERT INTO dap_an_quiz (ma_cau_hoi, noi_dung, la_dap_an_dung, thu_tu) VALUES (?, ?, ?, ?)";
                        $db->query($sqlDapAn, [$cauHoiId, $dapAn, $laDung, $j]);
                    }
                }
                
                $_SESSION['flash_message'] = 'Thêm câu hỏi thành công!';
                $_SESSION['flash_type'] = 'success';
                break;
                
            case 'delete_question':
                $maCauHoi = intval($_POST['ma_cau_hoi']);
                $db->query("DELETE FROM cau_hoi_quiz WHERE ma_cau_hoi = ?", [$maCauHoi]);
                
                $_SESSION['flash_message'] = 'Xóa câu hỏi thành công!';
                $_SESSION['flash_type'] = 'success';
                break;
        }
    } catch (Exception $e) {
        $_SESSION['flash_message'] = 'Lỗi: ' . $e->getMessage();
        $_SESSION['flash_type'] = 'error';
    }
    
    header('Location: quiz-questions.php?quiz_id=' . $quizId);
    exit;
}

// Lấy danh sách câu hỏi
$questions = $db->query("
    SELECT ch.*,
           (SELECT COUNT(*) FROM dap_an_quiz WHERE ma_cau_hoi = ch.ma_cau_hoi) as so_dap_an
    FROM cau_hoi_quiz ch
    WHERE ch.ma_quiz = ?
    ORDER BY ch.thu_tu
", [$quizId]);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Câu Hỏi Quiz</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
body { background: #f8f9fc; }
.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    margin-bottom: 2rem;
    border-radius: 0 0 20px 20px;
}
.btn-add-new {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}
.btn-add-new:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
}
.card { border-radius: 16px; border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
<style>
.question-card {
    border-left: 4px solid #4e73df;
    margin-bottom: 1rem;
}
.answer-item {
    padding: 0.5rem;
    margin: 0.25rem 0;
    border-radius: 0.25rem;
    background: #f8f9fc;
}
.answer-item.correct {
    background: #d4edda;
    border-left: 3px solid #28a745;
}
.form-section {
    background: #f8f9fc;
    padding: 1.5rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
}
</style>
</head>
<body>
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-2" style="font-size: 2rem; font-weight: 800;">
                <i class="fas fa-list-ul"></i> Câu Hỏi Quiz
            </h1>
            <p class="mb-0" style="opacity: 0.9;">
                <strong><?= htmlspecialchars($quiz['tieu_de']) ?></strong>
                <br><small>Bài viết: <?= htmlspecialchars($quiz['ten_van_hoa']) ?></small>
            </p>
        </div>
        <div>
            <a href="quiz-vanhoa.php" class="btn btn-light mr-2" style="border-radius: 12px; font-weight: 600;">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <button class="btn-add-new" data-toggle="modal" data-target="#addQuestionsModal">
                <i class="fas fa-plus"></i> Thêm 10 Câu Hỏi
            </button>
        </div>
    </div>
</div>

<div class="container-fluid" style="padding: 2rem;">
    <div class="mb-4">

    <?php if (isset($_SESSION['flash_message'])): ?>
    <div class="alert alert-<?= $_SESSION['flash_type'] ?> alert-dismissible fade show">
        <?= $_SESSION['flash_message'] ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php 
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    endif; 
    ?>

    <!-- Danh sách câu hỏi -->
    <div class="row">
        <?php if (empty($questions)): ?>
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Chưa có câu hỏi nào. Nhấn "Thêm 10 Câu Hỏi" để bắt đầu!
            </div>
        </div>
        <?php else: ?>
        <?php foreach ($questions as $index => $q): 
            // Lấy đáp án
            $answers = $db->query("SELECT * FROM dap_an_quiz WHERE ma_cau_hoi = ? ORDER BY thu_tu", [$q['ma_cau_hoi']]);
        ?>
        <div class="col-md-6">
            <div class="card question-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Câu <?= $q['thu_tu'] ?>: <?= $q['diem'] ?> điểm</strong>
                    <button class="btn btn-sm btn-danger" onclick="deleteQuestion(<?= $q['ma_cau_hoi'] ?>)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="card-body">
                    <p class="mb-3"><strong><?= nl2br(htmlspecialchars($q['noi_dung'])) ?></strong></p>
                    
                    <div class="answers">
                        <?php foreach ($answers as $ans): ?>
                        <div class="answer-item <?= $ans['la_dap_an_dung'] ? 'correct' : '' ?>">
                            <?= $ans['la_dap_an_dung'] ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="far fa-circle"></i>' ?>
                            <?= htmlspecialchars($ans['noi_dung']) ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if ($q['giai_thich']): ?>
                    <div class="mt-3 p-2 bg-light rounded">
                        <small class="text-muted">
                            <i class="fas fa-lightbulb"></i> <strong>Giải thích:</strong><br>
                            <?= nl2br(htmlspecialchars($q['giai_thich'])) ?>
                        </small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Thêm 10 Câu Hỏi -->
<div class="modal fade" id="addQuestionsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="POST" action="" id="questionsForm">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle"></i> Thêm 10 Câu Hỏi
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <input type="hidden" name="action" value="add_questions">
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Hướng dẫn:</strong> Nhập 10 câu hỏi, mỗi câu có 4 đáp án. Chọn đáp án đúng bằng radio button.
                    </div>
                    
                    <?php for ($i = 1; $i <= 10; $i++): ?>
                    <div class="form-section">
                        <h6 class="text-primary">
                            <i class="fas fa-question-circle"></i> Câu hỏi <?= $i ?>
                        </h6>
                        
                        <div class="form-group">
                            <label>Nội dung câu hỏi <span class="text-danger">*</span></label>
                            <textarea name="cau_hoi_<?= $i ?>" class="form-control" rows="2" 
                                      placeholder="Nhập câu hỏi..." required></textarea>
                        </div>
                        
                        <div class="row">
                            <?php for ($j = 1; $j <= 4; $j++): ?>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>
                                        <input type="radio" name="dap_an_dung_<?= $i ?>" value="<?= $j ?>" <?= $j === 1 ? 'checked' : '' ?>>
                                        Đáp án <?= chr(64 + $j) ?> <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="dap_an_<?= $i ?>_<?= $j ?>" class="form-control" 
                                           placeholder="Nhập đáp án <?= chr(64 + $j) ?>..." required>
                                </div>
                            </div>
                            <?php endfor; ?>
                        </div>
                        
                        <div class="form-group">
                            <label>Giải thích (tùy chọn)</label>
                            <textarea name="giai_thich_<?= $i ?>" class="form-control" rows="2" 
                                      placeholder="Giải thích tại sao đáp án này đúng..."></textarea>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Lưu 10 Câu Hỏi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function deleteQuestion(id) {
    if (confirm('Bạn có chắc muốn xóa câu hỏi này?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_question">
            <input type="hidden" name="ma_cau_hoi" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Validate form trước khi submit
document.getElementById('questionsForm').addEventListener('submit', function(e) {
    let hasContent = false;
    for (let i = 1; i <= 10; i++) {
        const question = document.querySelector(`[name="cau_hoi_${i}"]`).value.trim();
        if (question) {
            hasContent = true;
            
            // Kiểm tra 4 đáp án
            for (let j = 1; j <= 4; j++) {
                const answer = document.querySelector(`[name="dap_an_${i}_${j}"]`).value.trim();
                if (!answer) {
                    alert(`Vui lòng nhập đầy đủ 4 đáp án cho câu hỏi ${i}!`);
                    e.preventDefault();
                    return false;
                }
            }
        }
    }
    
    if (!hasContent) {
        alert('Vui lòng nhập ít nhất 1 câu hỏi!');
        e.preventDefault();
        return false;
    }
    
    return confirm('Bạn có chắc muốn thêm các câu hỏi này?');
});
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
