<?php
include 'db.php';

$customer_phone = isset($_GET['customer_phone']) ? intval($_GET['customer_phone']) : 0;

$query = "
    SELECT o.id as order_id, o.pickup_date, o.pickup_time, o.status, o.customer_phone,
           i.item_name, i.quantity, i.service_type, i.price
    FROM orders o
    JOIN order_items i ON o.id = i.order_id
    WHERE o.customer_phone = '$customer_phone'
    ORDER BY o.pickup_date DESC, o.pickup_time DESC
";

$result = mysqli_query($conn, $query);

// Group items by order ID and calculate totals
$orders = [];
$grand_total = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $order_id = $row['order_id'];
    if (!isset($orders[$order_id])) {
        $orders[$order_id] = [
            'pickup_date' => $row['pickup_date'],
            'pickup_time' => $row['pickup_time'],
            'status' => $row['status'],
            'customer_phone' => $row['customer_phone'],
            'items' => [],
            'subtotal' => 0
        ];
    }
    
    $item_total = $row['price'] * $row['quantity'];
    $orders[$order_id]['items'][] = [
        'item_name' => $row['item_name'],
        'quantity' => $row['quantity'],
        'service_type' => $row['service_type'],
        'price' => $row['price'],
        'item_total' => $item_total
    ];
    $orders[$order_id]['subtotal'] += $item_total;
    $grand_total += $item_total;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer Orders</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 20px;
                font-size: 12px;
            }
            .order-card {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body class="bg-gray-50 p-6">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-6 no-print">
            <h2 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-list-alt mr-2 text-blue-500"></i>
                Order Details for Customer: <?= htmlspecialchars($customer_phone) ?>
            </h2>
            <button onclick="window.print()" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md transition duration-150 ease-in-out">
                <i class="fas fa-print mr-2"></i> Print
            </button>
        </div>

        <?php if(!empty($orders)): ?>
            <div class="space-y-4">
                <?php foreach ($orders as $order_id => $order): ?>
                    <div class="order-card bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="p-4 border-b border-gray-200 bg-gray-50">
                            <div class="flex flex-wrap justify-between items-center">
                                <div class="mb-2 sm:mb-0">
                                    <span class="font-semibold">Order ID:</span> 
                                    <span class="text-blue-600"><?= $order_id ?></span>
                                </div>
                                <div class="mb-2 sm:mb-0">
                                    <span class="font-semibold">Pickup:</span> 
                                    <?= date('M j, Y', strtotime($order['pickup_date'])) ?> 
                                    at <?= date('h:i A', strtotime($order['pickup_time'])) ?>
                                </div>
                                <div>
                                    <span class="font-semibold">Status:</span>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                        <?= $order['status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : '' ?>
                                        <?= $order['status'] == 'processing' ? 'bg-blue-100 text-blue-800' : '' ?>
                                        <?= $order['status'] == 'ready' ? 'bg-green-100 text-green-800' : '' ?>
                                        <?= $order['status'] == 'delivered' ? 'bg-purple-100 text-purple-800' : '' ?>
                                        <?= $order['status'] == 'cancelled' ? 'bg-red-100 text-red-800' : '' ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-4">
                            <h3 class="font-medium text-gray-700 mb-2">Items:</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($order['items'] as $item): ?>
                                            <tr>
                                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($item['item_name']) ?></td>
                                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                                        <?= $item['service_type'] == 'wash' ? 'bg-blue-100 text-blue-800' : '' ?>
                                                        <?= $item['service_type'] == 'dry_clean' ? 'bg-purple-100 text-purple-800' : '' ?>
                                                        <?= $item['service_type'] == 'iron' ? 'bg-green-100 text-green-800' : '' ?>">
                                                        <?= ucfirst(str_replace('_', ' ', $item['service_type'])) ?>
                                                    </span>
                                                </td>
                                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">₹<?= number_format($item['price'], 2) ?></td>
                                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500"><?= $item['quantity'] ?></td>
                                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">₹<?= number_format($item['item_total'], 2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="mt-4 flex justify-end">
                                <div class="w-64">
                                    <div class="flex justify-between py-2 border-b">
                                        <span class="font-medium">Subtotal:</span>
                                        <span>₹<?= number_format($order['subtotal'], 2) ?></span>
                                    </div>
                                    <div class="flex justify-between py-2 font-bold">
                                        <span>Order Total:</span>
                                        <span>₹<?= number_format($order['subtotal'], 2) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <!-- Grand Total -->
                <div class="bg-white rounded-lg shadow-md p-4">
                    <div class="flex justify-end">
                        <div class="w-64">
                            <div class="flex justify-between py-2 text-lg font-bold">
                                <span>Grand Total:</span>
                                <span>₹<?= number_format($grand_total, 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center py-8 bg-white rounded-lg shadow-md">
                <i class="fas fa-exclamation-circle text-gray-400 text-4xl mb-4"></i>
                <p class="text-gray-600 text-lg">No orders found for this customer.</p>
            </div>
        <?php endif; ?>

        <div class="mt-6 text-center no-print">
            <a href="customers.php" class="inline-flex items-center text-blue-500 hover:text-blue-700">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Customer List
            </a>
        </div>
    </div>
</body>
</html>