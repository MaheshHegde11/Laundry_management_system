<?php
ob_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require 'config.php';

$response = [
    'success' => false,
    'message' => '',
    'redirect' => ''
];

try {
    // Validate required fields
    $required = ['pickup_date', 'pickup_time', 'pickup_address', 'total_price', 'items', 'payment_method'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Decode JSON items
    $items = json_decode($_POST['items'], true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid items data format");
    }

    // Validate session
    if (empty($_SESSION['name']) || empty($_SESSION['email']) || empty($_SESSION['phone'])) {
        throw new Exception("Session expired. Please login again.");
    }

    // Start transaction
    $pdo->beginTransaction();

    // 1. Insert order (without payment details)
    $order_stmt = $pdo->prepare("INSERT INTO orders 
        (customer_id,customer_name, customer_email, customer_phone, total_price, 
         pickup_date, pickup_time, pickup_address, created_at) 
        VALUES (:id , :name, :email, :phone, :price, :date, :time, :address,  NOW())");
    
    $orderData = [
        ':id' => $_SESSION['user_id'],
        ':name' => $_SESSION['name'],
        ':email' => $_SESSION['email'],
        ':phone' => $_SESSION['phone'],
        ':price' => floatval($_POST['total_price']),
        ':date' => $_POST['pickup_date'],
        ':time' => $_POST['pickup_time'],
        ':address' => $_POST['pickup_address'],
        
    ];
    
    if (!$order_stmt->execute($orderData)) {
        throw new Exception("Failed to create order record");
    }

    $order_id = $pdo->lastInsertId();

    // 2. Insert order items with comments
    $item_stmt = $pdo->prepare("INSERT INTO order_items 
        (order_id, item_name, quantity, price, comments, service_type) 
        VALUES (:order_id, :name, :qty, :price, :comments, :service)");
    
    $hasValidItems = false;
    foreach ($items as $item) {
        if (empty($item['quantity']) || $item['quantity'] <= 0) continue;
        
        $hasValidItems = true;
        $itemData = [
            ':order_id' => $order_id,
            ':name' => $item['name'],
            ':qty' => intval($item['quantity']),
            ':price' => floatval($item['price']),
            ':comments' => !empty($item['comments']) ? $item['comments'] : null,
            ':service' => $item['service_type'] ?? 'wash_iron'
        ];
        
        if (!$item_stmt->execute($itemData)) {
            throw new Exception("Failed to save order item: " . implode(', ', $item_stmt->errorInfo()));
        }
    }

    if (!$hasValidItems) {
        throw new Exception("No valid items were selected");
    }

    // 3. Insert payment details (using your exact table structure)
    $payment_method = $_POST['payment_method'];
        $payment_map = [
            'gpay' => 'upi',
            'paytm' => 'upi',
            'phonepe' => 'upi',
            'cod' => 'Cash'
        ];
        $payment_type = $payment_map[$payment_method] ?? 'Cash';
    $payment_stmt = $pdo->prepare("
        INSERT INTO payments (
            order_id, 
            amount, 
            payment_method
        ) VALUES (:order_id, :amount, :method)
    ");
    
    $paymentData = [
        ':order_id' => $order_id,
        ':amount' => floatval($_POST['total_price']),
        ':method' => $payment_type
    ];
    
    if (!$payment_stmt->execute($paymentData)) {
        throw new Exception("Failed to save payment details: " . implode(', ', $payment_stmt->errorInfo()));
    }

    // Commit transaction if all operations succeeded
    $pdo->commit();
    unset($_SESSION['cart']);

    $response = [
        'success' => true,
        'message' => 'Order placed successfully',
        'redirect' => 'order_confirmation.php?order_id=' . $order_id,
        'order_id' => $order_id
    ];

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $response['message'] = "Database error: " . $e->getMessage();
    error_log("PDO Error: " . $e->getMessage());
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    $response['message'] = $e->getMessage();
    error_log("Order Error: " . $e->getMessage());
}

ob_end_clean();
echo json_encode($response);
exit;