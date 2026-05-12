<?php
session_start();
require 'db.php';

// Authentication check
if (!isset($_SESSION['admin_logged_in'])) {
    header('HTTP/1.0 403 Forbidden');
    exit();
}

// Get daily revenue for current month
$daily_query = "SELECT 
                    DATE(created_at) as day,
                    SUM(total_price) as revenue,
                    COUNT(*) as order_count
                FROM orders
                WHERE YEAR(created_at) = YEAR(CURRENT_DATE())
                AND MONTH(created_at) = MONTH(CURRENT_DATE())
                GROUP BY DATE(created_at)
                ORDER BY day ASC";
$daily_result = $conn->query($daily_query);
$daily_data = $daily_result->fetch_all(MYSQLI_ASSOC);

// Prepare chart data
$chart_labels = [];
$chart_revenue = [];

// Get current month info
$current_month = date('m');
$current_year = date('Y');
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $current_month, $current_year);
$today = date('j'); // Current day of month (1-31)

// Create array with all days initialized to 0
$daily_revenues = array_fill(1, $days_in_month, 0);

// Update with actual data
foreach ($daily_data as $data) {
    $day = date('j', strtotime($data['day']));
    $daily_revenues[$day] = $data['revenue'];
}

// Prepare chart data
for ($day = 1; $day <= $days_in_month; $day++) {
    $chart_labels[] = date('j M', strtotime("$current_year-$current_month-$day"));
    $chart_revenue[] = $daily_revenues[$day];
}

// Calculate metrics
$current_month_revenue = array_sum($chart_revenue);
$todays_revenue = $daily_revenues[$today] ?? 0;
$days_with_sales = count(array_filter($chart_revenue, function($v) { return $v > 0; }));
$daily_average = $days_with_sales > 0 ? $current_month_revenue / $days_with_sales : 0;

// Get total metrics
$metrics_query = "SELECT 
                    (SELECT COUNT(*) FROM customers) as total_customers,
                    (SELECT COUNT(*) FROM orders) as total_orders,
                    (SELECT SUM(total_price) FROM orders) as total_revenue";
$metrics_result = $conn->query($metrics_query);
$metrics = $metrics_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Sales Report</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 20px;
                font-size: 12px;
            }
            .print-table {
                width: 100% !important;
            }
            .print-table th, .print-table td {
                padding: 4px 8px !important;
            }
        }
        .chart-container {
            position: relative;
            height: 400px;
            width: 100%;
        }
        
        /* Sidebar fixes */
        .sidebar {
            width: 16rem;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 10;
        }
        
        .main-content {
            margin-left: 16rem;
            width: calc(100% - 16rem);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .main-content {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <div class="sidebar bg-gray-800 text-white">
        <?php include 'sidebar.php'; ?>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex justify-between items-center mb-6 no-print">
                <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-chart-bar mr-2 text-purple-600"></i>
                    Daily Sales Dashboard
                </h2>
                <button onclick="window.print()" class="bg-purple-600 hover:bg-purple-700 text-white py-2 px-4 rounded-md transition duration-150 ease-in-out">
                    <i class="fas fa-print mr-2"></i> Print Report
                </button>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Current Month Revenue -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                            <i class="fas fa-calendar-alt fa-lg"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-gray-500 text-sm font-medium">Month Revenue</h3>
                            <p class="text-2xl font-bold text-gray-900">
                                ₹<?= number_format($current_month_revenue, 2) ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Today's Revenue -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <i class="fas fa-sun fa-lg"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-gray-500 text-sm font-medium">Today's Revenue</h3>
                            <p class="text-2xl font-bold text-gray-900">
                                ₹<?= number_format($todays_revenue, 2) ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Daily Average -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <i class="fas fa-chart-line fa-lg"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-gray-500 text-sm font-medium">Daily Average</h3>
                            <p class="text-2xl font-bold text-gray-900">
                                ₹<?= number_format($daily_average, 2) ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daily Sales Chart -->
            <div class="bg-white p-6 rounded-lg shadow mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Daily Sales - <?= date('F Y') ?></h3>
                <div class="chart-container">
                    <canvas id="dailySalesChart"></canvas>
                </div>
            </div>

            <!-- Recent Orders Table -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Recent Orders</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 print-table">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php
                            $recent_orders = $conn->query("SELECT o.id, o.created_at, o.total_price, o.status, u.name 
                                                          FROM orders o JOIN customers u ON o.customer_id = u.customer_id 
                                                          ORDER BY o.created_at DESC LIMIT 5");
                            while ($order = $recent_orders->fetch_assoc()):
                            ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#<?= $order['id'] ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($order['name']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">₹<?= number_format($order['total_price'], 2) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                        <?= $order['status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : '' ?>
                                        <?= $order['status'] == 'processing' ? 'bg-blue-100 text-blue-800' : '' ?>
                                        <?= $order['status'] == 'shipped' ? 'bg-green-100 text-green-800' : '' ?>
                                        <?= $order['status'] == 'delivered' ? 'bg-purple-100 text-purple-800' : '' ?>
                                        <?= $order['status'] == 'cancelled' ? 'bg-red-100 text-red-800' : '' ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Daily Sales Chart
        const ctx = document.getElementById('dailySalesChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($chart_labels) ?>,
                datasets: [{
                    label: 'Daily Revenue (₹)',
                    data: <?= json_encode($chart_revenue) ?>,
                    backgroundColor: function(context) {
                        const index = context.dataIndex;
                        const today = new Date().getDate();
                        return index === today - 1 ? 'rgba(220, 38, 38, 0.7)' : 'rgba(79, 70, 229, 0.7)';
                    },
                    borderColor: function(context) {
                        const index = context.dataIndex;
                        const today = new Date().getDate();
                        return index === today - 1 ? 'rgba(220, 38, 38, 1)' : 'rgba(79, 70, 229, 1)';
                    },
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '₹' + context.raw.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Print handling
        window.beforePrint = function() {
            const canvas = document.getElementById('dailySalesChart');
            const img = canvas.toDataURL('image/png');
            const printWindow = window.open('', '', 'width=800,height=600');
            printWindow.document.write('<html><head><title>Daily Sales Report - <?= date('F Y') ?></title></head><body>');
            printWindow.document.write('<h1>Daily Sales Report - <?= date('F Y') ?></h1>');
            printWindow.document.write('<img src="' + img + '" style="max-width:100%;">');
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        };
    });
    </script>
</body>
</html>