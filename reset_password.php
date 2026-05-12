<?php
// Database configuration
$host = 'localhost';
$dbname ='project_db';
$username = 'root';
$password = '';

// Connect to database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and validate input
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Basic validation
    $errors = [];
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address';
    }

    if (strlen($new_password) < 8) {
        $errors[] = 'Password must be at least 8 characters';
    }

    if ($new_password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }

    // If no errors, update password
    if (empty($errors)) {
        try {
            // Hash the password
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            
            // Update database
            $stmt = $pdo->prepare("UPDATE customers SET password = ? WHERE email = ?");
            $stmt->execute([$hashed_password, $email]);
            
            // Check if password was actually updated
            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Password updated successfully'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No user found with that email'
                ]);
            }
        } catch(PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => implode('<br>', $errors)
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}