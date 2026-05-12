<?php
require_once 'config.php';
require_once 'auth.php';

// Check if order ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: orders.php?error=Invalid order ID");
    exit();
}

$order_id = intval($_GET['id']);

// Fetch order details
$order_query = "SELECT * FROM orders WHERE id = :order_id";
$order_stmt = $pdo->prepare($order_query);
$order_stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
$order_stmt->execute();
$order = $order_stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header("Location: orders.php?error=Order not found");
    exit();
}

// Fetch order items with special instructions
$items_query = "SELECT *, 
               (SELECT comments FROM order_items WHERE order_id = :order_id LIMIT 1) AS special_instructions 
               FROM order_items WHERE order_id = :order_id";
$items_stmt = $pdo->prepare($items_query);
$items_stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
$items_stmt->execute();
$items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$subtotal = 0;
foreach ($items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$total_amount = $subtotal ;

// Get special instructions (from first item)
$special_instructions = $items[0]['special_instructions'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - #<?= $order['id'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-pending {
            background-color: #FEF3C7;
            color: #92400E;
        }
        .status-processing {
            background-color: #DBEAFE;
            color: #1E40AF;
        }
        .status-completed {
            background-color: #D1FAE5;
            color: #065F46;
        }
        .service-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .service-wash {
            background-color: #E0F2FE;
            color: #0369A1;
        }
        .service-dryclean {
            background-color: #EDE9FE;
            color: #5B21B6;
        }
        .service-iron {
            background-color: #DCFCE7;
            color: #166534;
        }
        .instructions-box {
            background-color: #F3F4F6;
            border-left: 4px solid #3B82F6;
            padding: 12px;
            margin-top: 16px;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include 'sidebar.php'; ?>
    
    <div class="ml-64 p-8">
        <div class="max-w-6xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Order Details</h1>
                    <p class="text-gray-600">Order #<?= $order['id'] ?></p>
                </div>
                <div>
                    <a href="orders.php" class="text-blue-500 hover:text-blue-700 font-medium">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Orders
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Order Summary -->
                <div class="bg-white rounded-lg shadow p-6 lg:col-span-1">
                    <h2 class="text-lg font-semibold mb-4">Order Summary</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-500">Order Status</p>
                            <span class="status-badge status-<?= $order['status'] ?>">
                                <?= ucfirst($order['status']) ?>
                            </span>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500">Order Date</p>
                            <p class="font-medium"><?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500">Pickup Date</p>
                            <p class="font-medium"><?= date('M j, Y', strtotime($order['pickup_date'])) ?></p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500">Pickup Time</p>
                            <p class="font-medium"><?= $order['pickup_time'] ?></p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500">Payment Method</p>
                            <p class="font-medium"><?= ucfirst($order['payment_method'] ?? 'Cash') ?></p>
                        </div>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="bg-white rounded-lg shadow p-6 lg:col-span-1">
                    <h2 class="text-lg font-semibold mb-4">Customer Information</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-500">Customer Name</p>
                            <p class="font-medium"><?= htmlspecialchars($order['customer_name']) ?></p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500">Phone Number</p>
                            <p class="font-medium"><?= htmlspecialchars($order['customer_phone']) ?></p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500">Email Address</p>
                            <p class="font-medium"><?= htmlspecialchars($order['customer_email']) ?></p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500">Pickup Address</p>
                            <p class="font-medium"><?= htmlspecialchars($order['pickup_address']) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="bg-white rounded-lg shadow p-6 lg:col-span-1">
                    <h2 class="text-lg font-semibold mb-4">Order Items</h2>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Service</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td class="px-4 py-2 text-sm"><?= htmlspecialchars($item['item_name']) ?></td>
                                        <td class="px-4 py-2">
                                            <span class="service-badge service-<?= str_replace('_', '', $item['service_type']) ?>">
                                                <?= ucfirst(str_replace('_', ' ', $item['service_type'])) ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 text-sm"><?= $item['quantity'] ?></td>
                                        <td class="px-4 py-2 text-sm">₹<?= number_format($item['price'], 2) ?></td>
                                        <td class="px-4 py-2 text-sm">₹<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Special Instructions -->
                    <?php if (!empty($special_instructions)): ?>
                        <div class="instructions-box mt-4">
                            <h3 class="text-sm font-semibold text-gray-700 mb-2">Special Instructions:</h3>
                            <p class="text-gray-600"><?= htmlspecialchars($special_instructions) ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Order Totals -->
                    <div class="mt-6 border-t border-gray-200 pt-4">
                        <div class="flex justify-between py-2 text-lg font-bold">
                            <span>Total Amount:</span>
                            <span>₹<?= number_format($total_amount, 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Actions -->
            <div class="mt-6 bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4">Order Actions</h2>
                <div class="flex flex-wrap gap-4">
                    <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-print mr-2"></i> Print Invoice
                    </button>
                  
                    <form id="statusForm" class="flex items-center">
                        <select name="status" class="border rounded-lg px-4 py-2 mr-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>Processing</option>
                            <option value="completed" <?= $order['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                        </select>
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                            <i class="fas fa-save mr-2"></i> Update Status
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Status form submission
            $('#statusForm').submit(function(e) {
                e.preventDefault();
                const formData = $(this).serialize();
                
                $.ajax({
                    url: 'update_order.php?id=<?= $order['id'] ?>',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        alert('Order status updated successfully');
                        // Update status badge
                        const newStatus = $('#statusForm select').val();
                        $('.status-badge')
                            .removeClass('status-pending status-processing status-completed')
                            .addClass('status-' + newStatus)
                            .text(newStatus.charAt(0).toUpperCase() + newStatus.slice(1));
                    },
                    error: function(xhr, status, error) {
                        alert('Error updating status: ' + error);
                    }
                });
            });
        });
    </script>
</body>
</html>