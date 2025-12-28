<?php
/**
 * API lấy danh sách câu hỏi của quiz truyện dân gian
 */
header('Content-Type: application/json');
require_once 'includes/auth.php';
require_once 'config/database.php';

checkAdminAuth();

$quizId = intval($_GET['quiz_id'] ?? 0);

if (!$quizId) {
    echo json_encode(['success' => false, 'message' => 'Quiz ID không hợp lệ']);
    exit;
}

try {
    $db = Database::getInstance();
    
    // Lấy danh sách câu hỏi
    $questions = $db->query("
        SELECT * FROM cau_hoi_quiz 
        WHERE ma_quiz = ? 
        ORDER BY thu_tu
    ", [$quizId]);
    
    // Lấy đáp án cho từng câu hỏi
    $answers = [];
    foreach ($questions as $q) {
        $answers[$q['ma_cau_hoi']] = $db->query("
            SELECT * FROM dap_an_quiz 
            WHERE ma_cau_hoi = ? 
            ORDER BY thu_tu
        ", [$q['ma_cau_hoi']]);
    }
    
    echo json_encode([
        'success' => true,
        'questions' => $questions,
        'answers' => $answers
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi: ' . $e->getMessage()
    ]);
}
?>
