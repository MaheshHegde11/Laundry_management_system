<?php
session_start();
require_once 'db.php';

// Initialize variables
$error = '';
$success = '';
$orders = [];

// Get customer's orders for dropdown
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT id, created_at FROM orders WHERE customer_id = ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate input
        if (empty($_POST['order_id']) || empty($_POST['rating']) || empty($_POST['comments'])) {
            throw new Exception("All fields are required");
        }

        // Check if this order belongs to the customer
        if (isset($_SESSION['user_id'])) {
            $stmt = $pdo->prepare("SELECT id FROM orders WHERE id = ? AND customer_id = ?");
            $stmt->execute([$_POST['order_id'], $_SESSION['user_id']]);
            if (!$stmt->fetch()) {
                throw new Exception("Invalid order selected");
            }
        }

        // Insert feedback
        $stmt = $pdo->prepare("
            INSERT INTO feedback (
                order_id,
                customer_id,
                rating,
                comments,
                created_at
            ) VALUES (?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $_POST['order_id'],
            $_SESSION['user_id'] ?? null,
            $_POST['rating'],
            $_POST['comments']
        ]);

        $success = "Thank you for your feedback!";
        $_POST = []; // Clear form

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laundry Techs - Feedback</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary: #2575fc;
            --secondary: #6a11cb;
            --accent: #ff5e62;
            --light: #f8f9fa;
            --dark: #212529;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            min-height: 100vh;
        }
        
        .feedback-container {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            max-width: 800px;
            margin: 60px auto;
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        
        .feedback-container:hover {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
        }
        
        .rating-stars {
            font-size: 2.5rem;
            cursor: pointer;
            margin: 15px 0;
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        
        .rating-stars .star {
            transition: all 0.2s ease;
            color: #e0e0e0;
        }
        
        .rating-stars .star:hover,
        .rating-stars .star.active {
            transform: scale(1.2);
            color: var(--warning);
        }
        
        .rating-stars .star.hover {
            transform: scale(1.1);
            color: var(--warning);
            opacity: 0.7;
        }
        
        textarea {
            min-height: 150px;
            border-radius: 8px;
            padding: 15px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        
        textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(37, 117, 252, 0.25);
        }
        
        .btn-submit {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(106, 17, 203, 0.2);
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(106, 17, 203, 0.3);
        }
        
        .form-select {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        
        .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(37, 117, 252, 0.25);
        }
        
        .header-icon {
            color: var(--primary);
            margin-right: 10px;
        }
        
        .alert {
            border-radius: 8px;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
        }
        
        @media (max-width: 768px) {
            .feedback-container {
                padding: 25px;
                margin: 30px auto;
            }
            
            .rating-stars {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="feedback-container animate__animated animate__fadeIn">
            <h2 class="mb-4"><i class="fas fa-comment-alt header-icon"></i>Share Your Feedback</h2>
            <p class="text-muted mb-4">We value your opinion! Please let us know about your experience with our service.</p>
            
            <?php if ($error): ?>
                <div class="alert alert-danger animate__animated animate__shakeX"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success animate__animated animate__bounceIn"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <form method="post" id="feedbackForm">
                <div class="mb-4">
                    <label for="order_id" class="form-label">Select Order</label>
                    <select class="form-select" id="order_id" name="order_id" required>
                        <option value="" disabled selected>-- Select an order --</option>
                        <?php foreach ($orders as $order): ?>
                            <option value="<?= $order['id'] ?>" <?= isset($_POST['order_id']) && $_POST['order_id'] == $order['id'] ? 'selected' : '' ?>>
                                Order #<?= $order['id'] ?> - <?= date('M j, Y', strtotime($order['created_at'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-4 text-center">
                    <label class="form-label">How would you rate your experience?</label>
                    <div class="rating-stars mb-2" id="starContainer">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star <?= isset($_POST['rating']) && $_POST['rating'] >= $i ? 'active' : '' ?>" 
                                  data-value="<?= $i ?>">
                                <i class="fas fa-star"></i>
                            </span>
                        <?php endfor; ?>
                    </div>
                    <div id="ratingText" class="text-muted small">
                        <?= isset($_POST['rating']) ? 'You rated this ' . $_POST['rating'] . ' star(s)' : 'Click to rate' ?>
                    </div>
                    <input type="hidden" name="rating" id="rating" value="<?= $_POST['rating'] ?? '' ?>">
                </div>
                
                <div class="mb-4">
                    <label for="comments" class="form-label">Tell us more about your experience</label>
                    <textarea class="form-control" id="comments" name="comments" placeholder="What did you like? What could we improve?" required><?= $_POST['comments'] ?? '' ?></textarea>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-submit">
                        <i class="fas fa-paper-plane me-2"></i> Submit Feedback
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const stars = document.querySelectorAll('.star');
            const ratingInput = document.getElementById('rating');
            const ratingText = document.getElementById('ratingText');
            const starContainer = document.getElementById('starContainer');
            
            // Star rating functionality with hover effects
            stars.forEach(star => {
                star.addEventListener('click', function() {
                    const value = this.getAttribute('data-value');
                    ratingInput.value = value;
                    updateStars(value);
                    ratingText.textContent = getRatingText(value);
                });
                
                star.addEventListener('mouseover', function() {
                    const hoverValue = this.getAttribute('data-value');
                    stars.forEach((s, index) => {
                        s.classList.toggle('hover', index < hoverValue);
                    });
                });
            });
            
            starContainer.addEventListener('mouseleave', function() {
                const currentValue = ratingInput.value || 0;
                updateStars(currentValue);
            });
            
            function updateStars(value) {
                stars.forEach((star, index) => {
                    star.classList.toggle('active', index < value);
                    star.classList.remove('hover');
                });
            }
            
            function getRatingText(value) {
                const ratingTexts = {
                    1: "Poor - We're sorry to hear that",
                    2: "Fair - We'll try to do better",
                    3: "Good - We appreciate your feedback",
                    4: "Very Good - We're glad you had a good experience",
                    5: "Excellent - We're thrilled to hear that!"
                };
                return ratingTexts[value] || `You rated this ${value} star(s)`;
            }
            
            // Form validation
            const form = document.getElementById('feedbackForm');
            form.addEventListener('submit', function(e) {
                if (!ratingInput.value) {
                    e.preventDefault();
                    ratingText.textContent = "Please select a rating";
                    ratingText.style.color = "var(--danger)";
                    setTimeout(() => {
                        ratingText.style.color = "";
                    }, 2000);
                }
            });
        });
    </script>
</body>
</html>