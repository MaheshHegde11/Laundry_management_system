<?php
session_start();
$err = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Laundry Techs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#667eea',
                        secondary: '#764ba2',
                        success: '#2ecc71',
                        danger: '#e74c3c',
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #ffffff 0%, #f2f683 100%);
        }
        
        .form-input:focus + .form-label,
        .form-input:not(:placeholder-shown) + .form-label {
            top: -0.5rem;
            left: 0.8rem;
            font-size: 0.75rem;
            color: #667eea;
            background-color: white;
            padding: 0 0.25rem;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white bg-opacity-95 rounded-2xl shadow-xl backdrop-blur-sm p-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Welcome Back</h1>
            <p class="text-gray-600">Login to your laundry service account</p>
            <?php if (!empty($err)): ?>
                <div class="mt-3 p-3 bg-danger-100 text-danger-700 rounded-lg text-sm">
                    <?php echo htmlspecialchars($err); ?>
                </div>
            <?php endif; ?>
        </div>
        
        <form id="loginForm" action="validate_login.php" method="POST" class="space-y-5">
            <!-- Phone Field -->
            <div class="relative">
                <i class="fas fa-phone absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input 
                    type="text" 
                    id="phone" 
                    name="phone" 
                    placeholder=" "
                    class="form-input w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-2 focus:ring-primary focus:ring-opacity-20 transition-all"
                    required
                >
                <label for="phone" class="form-label absolute left-10 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none transition-all duration-200">Phone Number</label>
            </div>
            
            <!-- Password Field with Toggle -->
            <div class="relative">
                <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder=" "
                    class="form-input w-full pl-10 pr-12 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-2 focus:ring-primary focus:ring-opacity-20 transition-all"
                    required
                >
                <label for="password" class="form-label absolute left-10 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none transition-all duration-200">Password</label>
                <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 cursor-pointer" onclick="togglePassword()">
                    <i id="eyeIcon" class="far fa-eye"></i>
                </span>
            </div>
            
            <!-- Forgot Password Link -->
            <div class="text-right">
                <a href="forgot_password.html" class="text-primary text-sm hover:underline">Forgot Password?</a>
            </div>
            
            <!-- Google reCAPTCHA -->
            <div class="g-recaptcha" data-sitekey="6LdUtTgrAAAAAApdE3kz9yTZVtETc_1YJS3L-LVw"></div>
            <div id="recaptcha-error" class="hidden text-danger-700 text-sm mt-1"></div>
            
            <!-- Submit Button -->
            <button type="submit" class="w-full bg-gradient-to-r from-primary to-secondary text-white py-3 px-4 rounded-xl font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                Login
            </button>
        </form>
        
        <div class="mt-6 text-center text-gray-600">
            Don't have an account? 
            <a href="sign_in.php" class="text-primary font-semibold hover:underline">Register here</a>
        </div>
    </div>

    <script>
        // Password toggle functionality
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }

        // reCAPTCHA validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const response = grecaptcha.getResponse();
            const errorElement = document.getElementById('recaptcha-error');
            
            if (response.length === 0) {
                e.preventDefault();
                errorElement.textContent = 'Please verify you are not a robot';
                errorElement.classList.remove('hidden');
                // Scroll to the reCAPTCHA widget
                document.querySelector('.g-recaptcha').scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            } else {
                errorElement.classList.add('hidden');
            }
        });
    </script>
</body>
</html>