<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'No ID provided']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT f.*, c.name as customer_name, c.email as customer_email, o.id AS order_id
                          FROM feedback f
                          LEFT JOIN customers c ON f.customer_id = c.customer_id
                          LEFT JOIN orders o ON f.order_id = o.id
                          WHERE f.id = ?");
    $stmt->execute([$_GET['id']]);
    $feedback = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$feedback) {
        echo json_encode(['error' => 'Feedback not found']);
        exit;
    }
    
    // Format created_at date for better display
    $feedback['formatted_date'] = date('F j, Y \a\t g:i a', strtotime($feedback['created_at']));
    
    echo json_encode($feedback);
    
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}