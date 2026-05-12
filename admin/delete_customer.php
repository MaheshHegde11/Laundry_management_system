<?php
session_start();
include 'db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('HTTP/1.0 403 Forbidden');
    exit();
}

$success = false;
$error_message = '';

// Process deletion if POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['customer_id'])) {
    $customer_id = intval($_POST['customer_id']);
    
    try {
        // Check if customer exists
        $check_query = "SELECT * FROM customers WHERE customer_id = $customer_id";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) === 0) {
            $error_message = "Customer not found!";
        } else {
            // Check for existing orders
            $orders_check = "SELECT * FROM orders WHERE customer_id = $customer_id";
            $orders_result = mysqli_query($conn, $orders_check);
            
            if (mysqli_num_rows($orders_result) > 0) {
                $error_message = "Cannot delete customer with existing orders!";
            } else {
                // Perform deletion
                $delete_query = "DELETE FROM customers WHERE customer_id = $customer_id";
                
                if (mysqli_query($conn, $delete_query)) {
                    $success = true;
                } else {
                    $error_message = "Error deleting customer: " . mysqli_error($conn);
                }
            }
        }
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
    
    // Return JSON response for AJAX
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $success ? 'Customer deleted successfully!' : $error_message
    ]);
    exit();
}

// GET request handling (show confirmation)
if (isset($_GET['id'])) {
    $customer_id = intval($_GET['id']);
    
    // Verify customer exists
    $check_query = "SELECT name FROM customers WHERE customer_id = $customer_id";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) === 0) {
        header("Location: customers.php?error=Customer not found");
        exit();
    }
    
    $customer = mysqli_fetch_assoc($check_result);
} else {
    header("Location: customers.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Customer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-red-600 py-4 px-6">
                <h1 class="text-xl font-semibold text-white">
                    <i class="fas fa-trash-alt mr-2"></i> Delete Customer
                </h1>
            </div>
            
            <div class="p-6">
                <div class="mb-4 text-center">
                    <i class="fas fa-exclamation-triangle text-4xl text-yellow-500 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-800">Delete <?= htmlspecialchars($customer['name']) ?>?</h3>
                    <p class="text-gray-600 mt-2">This action cannot be undone.</p>
                </div>
                
                <form id="deleteForm" method="POST">
                    <input type="hidden" name="customer_id" value="<?= $customer_id ?>">
                    
                    <div class="flex justify-between mt-6">
                        <a href="customers.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </a>
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                            <i class="fas fa-trash-alt mr-1"></i> Confirm Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('deleteForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const button = this.querySelector('button[type="submit"]');
            const originalButtonText = button.innerHTML;
            
            // Show loading state
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Deleting...';
            
            fetch('delete_customer.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message and redirect
                    const container = document.querySelector('.p-6');
                    container.innerHTML = `
                        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
                            <i class="fas fa-check-circle mr-2"></i> ${data.message}
                        </div>
                        <div class="text-center mt-4">
                            <a href="customers.php" class="text-blue-500 hover:text-blue-700 font-medium">
                                <i class="fas fa-arrow-left mr-1"></i> Back to Customers
                            </a>
                        </div>
                    `;
                } else {
                    // Show error message
                    const container = document.querySelector('.p-6');
                    container.innerHTML = `
                        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
                            <i class="fas fa-exclamation-circle mr-2"></i> ${data.message}
                        </div>
                        <div class="text-center mt-4">
                            <a href="customers.php" class="text-blue-500 hover:text-blue-700 font-medium">
                                <i class="fas fa-arrow-left mr-1"></i> Back to Customers
                            </a>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                button.innerHTML = originalButtonText;
                button.disabled = false;
                alert('An error occurred. Please try again.');
            });
        });
    </script>
</body>
</html>