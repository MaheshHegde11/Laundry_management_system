<?php
session_start();
require_once 'db.php';

if (!isset($_GET['order_id'])) {
    header("Location: place_order.php");
    exit();
}

$order_id = $_GET['order_id'];

// Fetch order details with customer email
$stmt = $pdo->prepare("
    SELECT o.*, 
           p.payment_method,
           COUNT(oi.id) as item_count,
           c.email as customer_email,
           c.name as customer_name
    FROM orders o
    LEFT JOIN payments p ON o.id = p.order_id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN customers c ON o.customer_id = c.customer_id
    WHERE o.id = ?
    GROUP BY o.id
");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch order items
$stmt_items = $pdo->prepare("
    SELECT * FROM order_items
    WHERE order_id = ?
");
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

// Prepare items for email template
$order_items_html = '';
foreach ($items as $item) {
    $order_items_html .= "
        <tr>
            <td>{$item['item_name']}</td>
            <td>{$item['quantity']}</td>
            <td>₹".number_format($item['price'], 2)."</td>
            <td>₹".number_format($item['price'] * $item['quantity'], 2)."</td>
        </tr>
    ";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js"></script>
    <style>
        .order-item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
        .receipt-card {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .divider {
            border-top: 1px dashed #dee2e6;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card receipt-card">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0"><i class="fas fa-check-circle me-2"></i> Order Confirmed</h4>
                            <span class="badge bg-light text-dark fs-6">#<?= htmlspecialchars($order['id']) ?></span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-success">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle fa-2x me-3"></i>
                                <div>
                                    <h5 class="alert-heading mb-1">Thank you for your order!</h5>
                                    <p class="mb-0">A confirmation has been sent to <?= htmlspecialchars($order['customer_email']) ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title"><i class="fas fa-receipt me-2"></i>Order Summary</h5>
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span>Order ID:</span>
                                                <span class="fw-bold">#<?= htmlspecialchars($order['id']) ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span>Date:</span>
                                                <span><?= date('F j, Y, g:i a', strtotime($order['created_at'])) ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span>Items:</span>
                                                <span><?= htmlspecialchars($order['item_count']) ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span>Total:</span>
                                                <span class="fw-bold">₹<?= number_format($order['total_price'], 2) ?></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title"><i class="fas fa-truck me-2"></i>Pickup Information</h5>
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item">
                                                <div class="fw-bold mb-1"><?= htmlspecialchars($order['customer_name']) ?></div>
                                                <div><?= htmlspecialchars($order['customer_phone']) ?></div>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span>Pickup Date:</span>
                                                <span><?= date('F j, Y', strtotime($order['pickup_date'])) ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span>Pickup Time:</span>
                                                <span><?= htmlspecialchars($order['pickup_time']) ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span>Payment:</span>
                                                <span class="text-capitalize"><?= htmlspecialchars($order['payment_method']) ?></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h5 class="mb-3"><i class="fas fa-list-ul me-2"></i>Order Items</h5>
                        <div class="table-responsive mb-4">
                            <table class="table align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if (!empty($item['image_url'])): ?>
                                                <img src="<?= htmlspecialchars($item['image_url']) ?>" class="order-item-image me-3" alt="<?= htmlspecialchars($item['item_name']) ?>">
                                                <?php endif; ?>
                                                <div>
                                                    <div class="fw-bold"><?= htmlspecialchars($item['item_name']) ?></div>
                                                    <?php if (!empty($item['description'])): ?>
                                                    <small class="text-muted"><?= htmlspecialchars($item['description']) ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center"><?= htmlspecialchars($item['quantity']) ?></td>
                                        <td class="text-end">₹<?= number_format($item['price'], 2) ?></td>
                                        <td class="text-end">₹<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="3" class="text-end">Subtotal:</th>
                                        <th class="text-end">₹<?= number_format($order['total_price'], 2) ?></th>
                                    </tr>
                                    <tr>
                                        <th colspan="3" class="text-end">Tax:</th>
                                        <th class="text-end">₹0.00</th>
                                    </tr>
                                    <tr>
                                        <th colspan="3" class="text-end">Total:</th>
                                        <th class="text-end">₹<?= number_format($order['total_price'], 2) ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between flex-wrap">
                            <a href="my_orders.php" class="btn btn-outline-primary mb-2">
                                <i class="fas fa-clipboard-list me-2"></i> View Order History
                            </a>
                            <a href="place_order.php" class="btn btn-primary mb-2">
                                <i class="fas fa-plus me-2"></i> Place Another Order
                            </a>
                            <button id="printBtn" class="btn btn-outline-secondary mb-2">
                                <i class="fas fa-print me-2"></i> Print Receipt
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize EmailJS with your User ID
        emailjs.init('KfNi5kDBnXxhv5cJ8');
        
        // Function to send order confirmation email
        function sendOrderConfirmationEmail() {
            const emailParams = {
                to_name: "<?= addslashes($order['customer_name']) ?>",
                to_email: "<?= addslashes($order['customer_email']) ?>",
                from_name: "Your Company Name",
                order_id: "<?= $order['id'] ?>",
                order_date: "<?= date('F j, Y, g:i a', strtotime($order['created_at'])) ?>",
                pickup_date: "<?= date('F j, Y', strtotime($order['pickup_date'])) ?>",
                pickup_time: "<?= $order['pickup_time'] ?>",
                payment_method: "<?= $order['payment_method'] ?>",
                total_amount: "₹<?= number_format($order['total_price'], 2) ?>",
                order_items: `<?= addslashes($order_items_html) ?>`
            };

            emailjs.send('service_meex13o', 'template_d8q3299', emailParams)
                .then(function(response) {
                    console.log('Email sent successfully', response.status, response.text);
                }, function(error) {
                    console.error('Failed to send email', error);
                });
        }
        
        // Send email when page loads
        window.addEventListener('load', function() {
            // Only send if this is the first view (not a refresh)
            if (!sessionStorage.getItem('order_<?= $order['id'] ?>_email_sent')) {
                sendOrderConfirmationEmail();
                sessionStorage.setItem('order_<?= $order['id'] ?>_email_sent', 'true');
            }
        });

        // Print receipt functionality
        document.getElementById('printBtn').addEventListener('click', function() {
            window.print();
        });
    </script>
</body>
</html>