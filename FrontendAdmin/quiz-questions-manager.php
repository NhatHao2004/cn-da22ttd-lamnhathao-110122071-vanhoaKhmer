<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

checkAdminAuth();
refreshAdminInfo();

$db = Database::getInstance();
$quizId = intval($_GET['quiz_id'] ?? 0);

if (!$quizId) {
    header('Location: quiz-manager.php');
    exit;
}

// Lấy thông tin quiz
$quiz = $db->querySingle("SELECT * FROM bai_kiem_tra WHERE ma_bai_kiem_tra = ?", [$quizId]);

if (!$quiz) {
    header('Location: quiz-manager.php');
    exit;
}

// Xử lý actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch($action) {
            case 'add_question':
                $noiDung = trim($_POST['noi_dung']);
                $giaiThich = trim($_POST['giai_thich'] ?? '');
                $diem = intval($_POST['diem'] ?? 10);
                
                // Lấy thứ tự tiếp theo
                $maxOrder = $db->querySingle("SELECT COALESCE(MAX(thu_tu), 0) as max_order FROM cau_hoi WHERE ma_bai_kiem_tra = ?", [$quizId]);
                $thuTu = ($maxOrder['max_order'] ?? 0) + 1;
                
                $sql = "INSERT INTO cau_hoi (ma_bai_kiem_tra, noi_dung, giai_thich, diem, thu_tu, ngay_tao) VALUES (?, ?, ?, ?, ?, NOW())";
                $db->execute($sql, [$quizId, $noiDung, $giaiThich, $diem, $thuTu]);
                $cauHoiId = $db->lastInsertId();
                
                // Thêm đáp án
                for ($i = 1; $i <= 4; $i++) {
                    $dapAn = trim($_POST["dap_an_$i"] ?? '');
                    if (empty($dapAn)) continue;
                    
                    $laDung = isset($_POST['dap_an_dung']) && $_POST['dap_an_dung'] == $i ? 1 : 0;
                    $sqlDapAn = "INSERT INTO dap_an (ma_cau_hoi, noi_dung, la_dap_an_dung, thu_tu) VALUES (?, ?, ?, ?)";
                    $db->execute($sqlDapAn, [$cauHoiId, $dapAn, $laDung, $i]);
                }
                
                $_SESSION['flash_message'] = 'Thêm câu hỏi thành công!';
                $_SESSION['flash_type'] = 'success';
                break;
                
            case 'edit_question':
                $maCauHoi = intval($_POST['ma_cau_hoi']);
                $noiDung = trim($_POST['noi_dung']);
                $giaiThich = trim($_POST['giai_thich'] ?? '');
                $diem = intval($_POST['diem'] ?? 10);
                
                $sql = "UPDATE cau_hoi SET noi_dung = ?, giai_thich = ?, diem = ? WHERE ma_cau_hoi = ?";
                $db->execute($sql, [$noiDung, $giaiThich, $diem, $maCauHoi]);
                
                // Cập nhật đáp án
                for ($i = 1; $i <= 4; $i++) {
                    $maDapAn = intval($_POST["ma_dap_an_$i"]);
                    $dapAn = trim($_POST["dap_an_$i"]);
                    $laDung = isset($_POST['dap_an_dung']) && $_POST['dap_an_dung'] == $i ? 1 : 0;
                    
                    $sqlDapAn = "UPDATE dap_an SET noi_dung = ?, la_dap_an_dung = ? WHERE ma_dap_an = ?";
                    $db->execute($sqlDapAn, [$dapAn, $laDung, $maDapAn]);
                }
                
                $_SESSION['flash_message'] = 'Cập nhật câu hỏi thành công!';
                $_SESSION['flash_type'] = 'success';
                break;
                
            case 'delete_question':
                $maCauHoi = intval($_POST['ma_cau_hoi']);
                $db->execute("DELETE FROM cau_hoi WHERE ma_cau_hoi = ?", [$maCauHoi]);
                
                $_SESSION['flash_message'] = 'Xóa câu hỏi thành công!';
                $_SESSION['flash_type'] = 'success';
                break;
        }
    } catch (Exception $e) {
        $_SESSION['flash_message'] = 'Lỗi: ' . $e->getMessage();
        $_SESSION['flash_type'] = 'error';
    }
    
    header('Location: quiz-questions-manager.php?quiz_id=' . $quizId);
    exit;
}

// Lấy danh sách câu hỏi
$questions = $db->query("
    SELECT ch.*,
           (SELECT COUNT(*) FROM dap_an WHERE ma_cau_hoi = ch.ma_cau_hoi) as so_dap_an
    FROM cau_hoi ch
    WHERE ch.ma_bai_kiem_tra = ?
    ORDER BY ch.thu_tu
", [$quizId]);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Câu Hỏi - <?= htmlspecialchars($quiz['tieu_de']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh; }
        
        .main-container { max-width: 1400px; margin: 0 auto; padding: 2rem; }
        
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 24px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 20px 60px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .page-header h1 { font-size: 2rem; font-weight: 800; margin-bottom: 0.5rem; }
        .page-header p { opacity: 0.9; margin: 0; }
        
        .btn-add-new {
            background: white;
            color: #667eea;
            border: none;
            padding: 1rem 2rem;
            border-radius: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .btn-add-new:hover { transform: translateY(-3px); box-shadow: 0 12px 35px rgba(0,0,0,0.2); }
        
        .btn-back {
            background: rgba(255,255,255,0.2);
            color: white;
            backdrop-filter: blur(10px);
        }
        
        .question-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            border-left: 5px solid #667eea;
            transition: all 0.3s;
        }
        
        .question-card:hover { transform: translateX(5px); box-shadow: 0 15px 50px rgba(0,0,0,0.12); }
        
        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f1f5f9;
        }
        
        .question-number {
            font-size: 1.25rem;
            font-weight: 800;
            color: #667eea;
        }
        
        .question-content {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }
        
        .answers-list { display: grid; gap: 0.75rem; }
        
        .answer-item {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s;
        }
        
        .answer-item.correct {
            background: rgba(16, 185, 129, 0.1);
            border-color: #10b981;
        }
        
        .answer-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }
        
        .answer-item.correct .answer-icon {
            background: #10b981;
            color: white;
        }
        
        .answer-item:not(.correct) .answer-icon {
            background: #e2e8f0;
            color: #64748b;
        }
        
        .explanation-box {
            margin-top: 1.5rem;
            padding: 1.25rem;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
            border-radius: 12px;
            border-left: 4px solid #667eea;
        }
        
        .explanation-box strong { color: #667eea; }
        
        .action-btn-group { display: flex; gap: 0.5rem; }
        
        .action-btn {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            color: white;
        }
        
        .action-btn.warning { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .action-btn.danger { background: linear-gradient(135deg, #ef4444, #dc2626); }
        
        .action-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.15); }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #667eea;
            opacity: 0.3;
            margin-bottom: 1.5rem;
        }
        
        .alert-modern {
            padding: 1.25rem 1.5rem;
            border-radius: 16px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        
        .alert-success { background: rgba(16, 185, 129, 0.1); color: #10b981; border-left: 4px solid #10b981; }
        .alert-error { background: rgba(239, 68, 68, 0.1); color: #ef4444; border-left: 4px solid #ef4444; }
        
        .modal-content { border-radius: 20px; border: none; }
        .modal-header { background: linear-gradient(135deg, #667eea, #764ba2); color: white; border-radius: 20px 20px 0 0; }
        .modal-title { font-weight: 700; }
        
        .form-group label { font-weight: 600; color: #334155; margin-bottom: 0.5rem; }
        .form-control { border: 2px solid #e2e8f0; border-radius: 12px; padding: 0.75rem 1rem; }
        .form-control:focus { border-color: #667eea; box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1); }
        
        .radio-group { display: flex; gap: 1rem; flex-wrap: wrap; }
        .radio-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .radio-label:has(input:checked) {
            background: rgba(16, 185, 129, 0.1);
            border-color: #10b981;
            color: #10b981;
            font-weight: 600;
        }
        
        .radio-label input[type="radio"] { margin: 0; }
    </style>
</head>
<body>
    <div class="main-container">
