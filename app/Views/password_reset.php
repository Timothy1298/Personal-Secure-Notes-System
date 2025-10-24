<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Personal Notes System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-md">
        <div class="text-center mb-8">
            <div class="mx-auto w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-key text-blue-600 text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Reset Password</h1>
            <p class="text-gray-600 mt-2">Enter your details to reset your password</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-6">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Regular Password Reset Form -->
        <form method="POST" action="/password-reset" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-envelope mr-2"></i>Email Address
                </label>
                <input type="email" id="email" name="email" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="Enter your email address">
            </div>

            <div>
                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-lock mr-2"></i>New Password
                </label>
                <input type="password" id="new_password" name="new_password" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="Enter new password">
            </div>

            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-lock mr-2"></i>Confirm New Password
                </label>
                <input type="password" id="confirm_password" name="confirm_password" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="Confirm new password">
            </div>

            <button type="submit" 
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200">
                <i class="fas fa-key mr-2"></i>Reset Password
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Admin Direct Reset</h3>
            <p class="text-sm text-gray-600 mb-4">For direct password reset by username (admin use):</p>
            
            <form method="POST" action="/password-reset-direct" class="space-y-4">
                <div>
                    <label for="direct_username" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-2"></i>Username
                    </label>
                    <input type="text" id="direct_username" name="username" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Enter username">
                </div>

                <div>
                    <label for="direct_password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2"></i>New Password
                    </label>
                    <input type="password" id="direct_password" name="new_password" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Enter new password">
                </div>

                <button type="submit" 
                        class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition duration-200">
                    <i class="fas fa-user-shield mr-2"></i>Direct Reset
                </button>
            </form>
        </div>

        <div class="mt-6 text-center">
            <a href="/login" class="text-blue-600 hover:text-blue-800 text-sm">
                <i class="fas fa-arrow-left mr-2"></i>Back to Login
            </a>
        </div>

        <!-- Password Requirements -->
        <div class="mt-6 p-4 bg-gray-50 rounded-md">
            <h4 class="text-sm font-medium text-gray-900 mb-2">Password Requirements:</h4>
            <ul class="text-xs text-gray-600 space-y-1">
                <li><i class="fas fa-check text-green-500 mr-1"></i>At least 8 characters long</li>
                <li><i class="fas fa-check text-green-500 mr-1"></i>Contains at least one uppercase letter</li>
                <li><i class="fas fa-check text-green-500 mr-1"></i>Contains at least one lowercase letter</li>
                <li><i class="fas fa-check text-green-500 mr-1"></i>Contains at least one number</li>
                <li><i class="fas fa-check text-green-500 mr-1"></i>Contains at least one special character</li>
            </ul>
        </div>
    </div>

    <script>
        // Password strength indicator
        document.getElementById('new_password').addEventListener('input', function() {
            const password = this.value;
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /\d/.test(password),
                special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
            };
            
            // Update visual indicators
            const indicators = document.querySelectorAll('.fa-check');
            indicators.forEach((indicator, index) => {
                const requirementsArray = Object.values(requirements);
                if (requirementsArray[index]) {
                    indicator.className = 'fas fa-check text-green-500 mr-1';
                } else {
                    indicator.className = 'fas fa-times text-red-500 mr-1';
                }
            });
        });

        // Confirm password validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>