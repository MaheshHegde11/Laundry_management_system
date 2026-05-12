<?php
require_once 'config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

try {
    $feedback_query = "SELECT f.id, f.order_id, f.customer_id, f.rating, f.comments, f.created_at, 
                      c.name as customer_name, c.email as customer_email, o.id AS order_id,
                      (SELECT COUNT(*) FROM feedback_responses WHERE feedback_id = f.id) as response_count
                      FROM feedback f
                      LEFT JOIN customers c ON f.customer_id = c.customer_id
                      LEFT JOIN orders o ON f.order_id = o.id
                      ORDER BY f.created_at DESC";
    
    $stmt = $pdo->prepare($feedback_query);
    $stmt->execute();
    $feedback_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
    error_log($error_message);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Customer Feedback</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js"></script>
    <style>
        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 10;
        }
        .main-content {
            margin-left: 250px;
            width: calc(100% - 250px);
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
        .loading {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .responded-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            background-color: #f0fdf4;
            color: #16a34a;
            font-size: 0.875rem;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex">
        <?php include 'sidebar.php'; ?>

        <div class="main-content p-8">
            <div class="flex items-center mb-8">
                <i class="fas fa-comment-alt text-2xl text-blue-500 mr-3"></i>
                <h1 class="text-2xl font-bold text-gray-800">Customer Feedback</h1>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6" role="alert">
                    <strong>Error!</strong> <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>
            
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <?php if (empty($feedback_data)): ?>
                    <div class="p-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-4 text-gray-300"></i>
                        <p class="text-lg">No feedback records found</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comments</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($feedback_data as $feedback): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($feedback['id']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <?= htmlspecialchars($feedback['order_id']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($feedback['customer_name']) ?></div>
                                        <div class="text-sm text-gray-500"><?= htmlspecialchars($feedback['customer_email']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <?php 
                                            $rating = (int)$feedback['rating'];
                                            for ($i = 1; $i <= 5; $i++): 
                                                if ($i <= $rating): ?>
                                                    <i class="fas fa-star text-yellow-400 mx-0.5"></i>
                                                <?php else: ?>
                                                    <i class="far fa-star text-gray-300 mx-0.5"></i>
                                                <?php endif;
                                            endfor; ?>
                                            <span class="ml-2 text-sm text-gray-500">(<?= $rating ?>/5)</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="<?= htmlspecialchars($feedback['comments']) ?>">
                                        <?= htmlspecialchars(substr($feedback['comments'], 0, 50)) ?>
                                        <?= strlen($feedback['comments']) > 50 ? '...' : '' ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= date('M d, Y', strtotime($feedback['created_at'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        <button onclick="viewFeedbackDetails(<?= $feedback['id'] ?>)" 
                                                class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <?php if ($feedback['response_count'] > 0): ?>
                                            <span class="responded-badge">
                                                <i class="fas fa-check-circle mr-1"></i> Responded
                                            </span>
                                        <?php else: ?>
                                            <button onclick="openResponseModal(<?= $feedback['id'] ?>, '<?= htmlspecialchars($feedback['customer_name']) ?>', '<?= htmlspecialchars($feedback['customer_email']) ?>')" 
                                                    class="text-indigo-600 hover:text-indigo-900">
                                                <i class="fas fa-reply"></i> Reply
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- View Feedback Modal -->
    <div id="viewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Feedback Details</h3>
                <button onclick="closeModal('viewModal')" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="viewModalContent" class="space-y-4">
                <!-- Content loaded via JavaScript -->
            </div>
        </div>
    </div>

    <!-- Email Response Modal -->
    <div id="responseModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Send Response</h3>
                <button onclick="closeModal('responseModal')" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="responseForm" class="space-y-4">
                <input type="hidden" id="feedbackId">
                <div>
                    <label for="customerName" class="block text-sm font-medium text-gray-700">To</label>
                    <input type="text" id="customerName" readonly 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-100">
                </div>
                <div>
                    <label for="customerEmail" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="customerEmail" readonly 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-100">
                    <p id="emailWarning" class="hidden text-sm text-red-600 mt-1">No email available for this customer</p>
                </div>
                <div>
                    <label for="responseSubject" class="block text-sm font-medium text-gray-700">Subject</label>
                    <input type="text" id="responseSubject" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           value="Regarding Your Feedback">
                </div>
                <div>
                    <label for="responseMessage" class="block text-sm font-medium text-gray-700">Message</label>
                    <textarea id="responseMessage" rows="5" required
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('responseModal')" 
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" id="sendResponseBtn"
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        Send Response
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Initialize EmailJS with your User ID
        emailjs.init("yMHuGT7IsQdYzpuoc");
        
        // View feedback details
        function viewFeedbackDetails(id) {
            fetch('get_feedback.php?id=' + id)
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(text || 'Network response was not ok');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    const formattedDate = new Date(data.created_at).toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    
                    let stars = '';
                    for (let i = 1; i <= 5; i++) {
                        stars += `<i class="${i <= data.rating ? 'fas' : 'far'} fa-star ${i <= data.rating ? 'text-yellow-400' : 'text-gray-300'} mx-0.5"></i>`;
                    }
                    
                    document.getElementById('viewModalContent').innerHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">Feedback ID</p>
                                <p class="font-medium">${data.id}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Order Number</p>
                                <p class="font-medium">${data.order_id || 'N/A'}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Customer</p>
                                <p class="font-medium">${data.customer_name || 'N/A'}</p>
                                <p class="text-sm text-gray-500">${data.customer_email || 'No email available'}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Date Submitted</p>
                                <p class="font-medium">${formattedDate}</p>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-sm text-gray-500">Rating</p>
                                <div class="flex items-center mt-1">
                                    ${stars}
                                    <span class="ml-2 text-sm text-gray-500">(${data.rating || 0}/5)</span>
                                </div>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-sm text-gray-500">Comments</p>
                                <div class="mt-1 p-3 bg-gray-50 rounded whitespace-pre-wrap">${data.comments || 'No comments provided'}</div>
                            </div>
                        </div>
                    `;
                    document.getElementById('viewModal').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading feedback details: ' + error.message);
                });
        }

        // Open response modal
        function openResponseModal(feedbackId, customerName, customerEmail) {
            document.getElementById('feedbackId').value = feedbackId;
            document.getElementById('customerName').value = customerName;
            
            const emailField = document.getElementById('customerEmail');
            const emailWarning = document.getElementById('emailWarning');
            const sendBtn = document.getElementById('sendResponseBtn');
            
            if (customerEmail && customerEmail.trim() !== '') {
                emailField.value = customerEmail;
                emailWarning.classList.add('hidden');
                sendBtn.disabled = false;
            } else {
                emailField.value = 'No email available';
                emailWarning.classList.remove('hidden');
                sendBtn.disabled = true;
            }
            
            document.getElementById('responseModal').classList.remove('hidden');
        }

        // Close modal
        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
            if (modalId === 'responseModal') {
                document.getElementById('responseForm').reset();
            }
        }

        // Send response email
        document.getElementById('responseForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const feedbackId = document.getElementById('feedbackId').value;
            const customerName = document.getElementById('customerName').value;
            const customerEmail = document.getElementById('customerEmail').value;
            const subject = document.getElementById('responseSubject').value;
            const message = document.getElementById('responseMessage').value;

            if (!customerEmail || customerEmail.trim() === '' || customerEmail === 'No email available') {
                alert('Cannot send response - no customer email available');
                return;
            }

            const submitBtn = event.target.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner loading"></i> Sending...';

            // Get full feedback details
            fetch('get_feedback.php?id=' + feedbackId)
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(text || 'Failed to fetch feedback details');
                        });
                    }
                    return response.json();
                })
                .then(feedback => {
                    if (feedback.error) {
                        throw new Error(feedback.error);
                    }
                    if (!feedback.customer_email) {
                        throw new Error('Customer email not available in feedback data');
                    }

                    // Prepare email data for EmailJS
                    const emailData = {
                        to_name: customerName,
                        to_email: feedback.customer_email,
                        from_name: "Your Company Name",
                        from_email: "noreply@yourcompany.com",
                        subject: subject,
                        message: message,
                        feedback_id: feedbackId,
                        original_feedback: feedback.comments || 'No comments provided',
                        rating: feedback.rating || 0
                    };

                    // Send email via EmailJS
                    return emailjs.send("service_odhv81g", "template_v6o910q", emailData)
                        .then(() => emailData);
                })
                .then(emailData => {
                    // Log the response in your database
                    return fetch('log_response.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            feedback_id: emailData.feedback_id,
                            subject: emailData.subject,
                            message: emailData.message,
                            sent_at: new Date().toISOString(),
                            to_name: emailData.to_name,
                            to_email: emailData.to_email
                        })
                    })
                    .then(response => {
                        // First check if response is OK
                        if (!response.ok) {
                            return response.text().then(text => {
                                throw new Error(text || 'Failed to log response');
                            });
                        }
                        // Then try to parse as JSON
                        return response.text().then(text => {
                            try {
                                const jsonStart = text.indexOf('{');
                                const jsonEnd = text.lastIndexOf('}') + 1;
                                const jsonString = text.substring(jsonStart, jsonEnd);
                                return JSON.parse(jsonString);
                            } catch (e) {
                                console.error('Failed to parse JSON:', text);
                                throw new Error('Server returned invalid JSON');
                            }
                        });
                    });
                })
                .then(data => {
                    if (!data || !data.success) {
                        throw new Error(data?.message || 'Failed to log response');
                    }
                    alert('Response sent and logged successfully!');
                    closeModal('responseModal');
                    // Refresh the page to update the status
                    location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error: ' + error.message);
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Send Response';
                });
        });
    </script>
</body>
</html>