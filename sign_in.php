<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Laundry Techs</title>
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
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Create Account</h1>
            <p class="text-gray-600">Join our laundry service today</p>
        </div>
        
        <!-- Error/Success Messages -->
        <?php if (isset($_GET['error'])): ?>
            <div class="mb-4 p-3 bg-danger-100 text-danger-700 rounded-lg text-center text-sm">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
            <div class="mb-4 p-3 bg-success-100 text-success-700 rounded-lg text-center text-sm">
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>
        
        <form id="registerForm" autocomplete="off" action="validate_sign_in.php" method="POST" class="space-y-5">
            <!-- Username Field -->
            <div class="relative">
                <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    placeholder=" "
                    value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                    class="form-input w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-2 focus:ring-primary focus:ring-opacity-20 transition-all"
                    required
                >
                <label for="username" class="form-label absolute left-10 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none transition-all duration-200">Username</label>
            </div>
            
            <!-- Email Field -->
            <div class="relative">
                <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder=" "
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                    class="form-input w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-2 focus:ring-primary focus:ring-opacity-20 transition-all"
                    required
                >
                <label for="email" class="form-label absolute left-10 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none transition-all duration-200">Email</label>
            </div>
            
            <!-- Phone Field -->
            <div class="relative">
                <i class="fas fa-phone absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input 
                    type="text" 
                    id="phone" 
                    name="phone" 
                    placeholder=" "
                    maxlength="10"
                    value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" 
                    class="form-input w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-2 focus:ring-primary focus:ring-opacity-20 transition-all"
                    required
                >
                <label for="phone" class="form-label absolute left-10 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none transition-all duration-200">Phone Number</label>
            </div>
            
            <!-- Password Field -->
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
                <span class="eye-icon absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 cursor-pointer" onclick="togglePassword()">
                    <i class="fa-regular fa-eye"></i>
                </span>
            </div>
            
            <!-- Confirm Password Field -->
            <div class="relative">
                <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input 
                    type="password" 
                    id="confirmPassword" 
                    name="confirmPassword" 
                    placeholder=" "
                    class="form-input w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary focus:ring-2 focus:ring-primary focus:ring-opacity-20 transition-all"
                    required
                >
                <label for="confirmPassword" class="form-label absolute left-10 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none transition-all duration-200">Confirm Password</label>
            </div>
            
            <!-- reCAPTCHA -->
            <div class="g-recaptcha" data-sitekey="6LdUtTgrAAAAAApdE3kz9yTZVtETc_1YJS3L-LVw"></div>
            
            <!-- Submit Button -->
            <button type="submit" class="w-full bg-gradient-to-r from-primary to-secondary text-white py-3 px-4 rounded-xl font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                Register
            </button>
        </form>
        
        <div class="mt-6 text-center text-gray-600">
            Already have an account? 
            <a href="login.php" class="text-primary font-semibold hover:underline">Login here</a>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const eyeIcon = document.querySelector('.eye-icon i');
            
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

        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const response = grecaptcha.getResponse();
            if (response.length === 0) {
                e.preventDefault();
                // Create error message element if it doesn't exist
                if (!document.querySelector('.recaptcha-error')) {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'mb-4 p-3 bg-danger-100 text-danger-700 rounded-lg text-center text-sm recaptcha-error';
                    document.querySelector('.g-recaptcha').after(errorDiv);
                }
                document.querySelector('.recaptcha-error').textContent = 'Please verify you are not a robot';
            }
        });
    </script>
</body>
</html>