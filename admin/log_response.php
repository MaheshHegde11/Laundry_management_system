<?php
//session_start();
require_once 'config.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// --- Early checks ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    file_put_contents('log.txt', "Failed at POST check\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_SESSION['admin_id'])) {
    file_put_contents('log.txt', "Failed at SESSION check\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    file_put_contents('log.txt', "Failed at JSON check\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

// Log incoming data
file_put_contents('log.txt', "DATA: " . print_r($data, true), FILE_APPEND);

try {
    // Make sure all required fields are present
    if (empty($data['feedback_id']) || empty($data['subject']) || empty($data['message'])) {
        file_put_contents('log.txt', "Missing required fields\n", FILE_APPEND);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    // Insert into DB (add sent_by if your table requires it)
    $stmt = $pdo->prepare("INSERT INTO feedback_responses 
        (feedback_id, subject, message, sent_by, sent_at) 
        VALUES (?, ?, ?, ?, ?)");
    $success = $stmt->execute([
        $data['feedback_id'],
        $data['subject'],
        $data['message'],
        $_SESSION['admin_id'],
        $data['sent_at'] ?? date('Y-m-d H:i:s')
    ]);

    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database insertion failed']);
    }
} catch (PDOException $e) {
    file_put_contents('log.txt', "DB ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}