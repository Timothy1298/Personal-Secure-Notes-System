<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts for better typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #7F8DAE 0%, #3B4A6C 100%);
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }

        /* Custom styles for the form container */
        .register-container {
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 2rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            padding: 3rem;
            width: 90%;
            max-width: 450px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Input field styling */
        .input-group {
            position: relative;
            width: 100%;
            margin-bottom: 1.5rem;
        }

        .input-group input {
            padding-left: 3rem;
            width: 100%;
            border-radius: 9999px; /* Full pill shape */
            border: 1px solid #d1d5db;
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
            transition: all 0.3s ease;
        }

        .input-group input:focus {
            outline: none;
            border-color: #4C51BF; /* Deeper purple */
            box-shadow: 0 0 0 3px rgba(76, 81, 191, 0.3);
        }

        .input-group .icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        /* Button styling */
        .register-btn {
            background-color: #4C51BF;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 9999px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s, background-color 0.3s;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            cursor: pointer;
            width: 100%;
        }

        .register-btn:hover {
            background-color: #343D9B;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        /* Loader styles */
        .loader {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #4C51BF;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
            display: none;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Image styling */
        .system-image {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }

        .text-blue-500 {
            color: #4C51BF;
            transition: color 0.2s;
        }

        .text-blue-500:hover {
            color: #343D9B;
        }

        /* Social Login Buttons */
        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem;
            border-radius: 9999px;
            font-weight: 500;
            transition: background-color 0.2s;
        }

        .social-btn i {
            margin-right: 0.75rem;
        }

        .google-btn {
            background-color: #DB4437;
            color: white;
        }
        .google-btn:hover { background-color: #C1352A; }

        .github-btn {
            background-color: #24292E;
            color: white;
        }
        .github-btn:hover { background-color: #1B1E22; }

        .facebook-btn {
            background-color: #1877F2;
            color: white;
        }
        .facebook-btn:hover { background-color: #145CB2; }

        /* Password toggle icon */
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <!-- System Image -->
        <img src="https://images.unsplash.com/photo-1549692520-acc6669f9976?q=80&w=2070&auto=format&fit=crop" alt="Personal Tasks System" class="system-image">

        <h2 class="text-3xl font-bold mb-6 text-center text-gray-800">Create Account</h2>

        <?php if (!empty($_SESSION['errors'])): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded-xl mb-4 text-sm w-full">
                <?php foreach ($_SESSION['errors'] as $e): ?>
                    <p><?= $e ?></p>
                <?php endforeach; ?>
                <?php unset($_SESSION['errors']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/register" class="flex flex-col w-full" onsubmit="showLoader()">
            <div class="input-group">
                <span class="icon"><i class="fas fa-user"></i></span>
                <input type="text" name="username" placeholder="Username" class="w-full p-3 border rounded-full mb-3" required aria-label="Username">
            </div>

            <div class="input-group">
                <span class="icon"><i class="fas fa-envelope"></i></span>
                <input type="email" name="email" placeholder="Email" class="w-full p-3 border rounded-full mb-3" required aria-label="Email">
            </div>

            <div class="input-group">
                <span class="icon"><i class="fas fa-lock"></i></span>
                <input type="password" name="password" id="password-field-register" placeholder="Password" class="w-full p-3 border rounded-full mb-3" required aria-label="Password">
                <span class="password-toggle" onclick="togglePasswordVisibility('password-field-register', 'password-toggle-icon-register')"><i class="fas fa-eye-slash" id="password-toggle-icon-register"></i></span>
            </div>

            <div class="input-group">
                <span class="icon"><i class="fas fa-lock"></i></span>
                <input type="password" name="confirm_password" id="confirm-password-field" placeholder="Confirm Password" class="w-full p-3 border rounded-full mb-3" required aria-label="Confirm Password">
                <span class="password-toggle" onclick="togglePasswordVisibility('confirm-password-field', 'password-toggle-icon-confirm')"><i class="fas fa-eye-slash" id="password-toggle-icon-confirm"></i></span>
            </div>

            <button type="submit" class="register-btn mt-3" id="register-button">
                <span id="button-text">Register</span>
                <div class="loader" id="loader"></div>
            </button>
        </form>

        <p class="text-center text-gray-600 mt-6 mb-4">Or sign up with</p>
        <div class="w-full flex flex-col space-y-3">
            <a href="/social/google" class="social-btn google-btn"><i class="fab fa-google"></i> Continue with Google</a>
            <a href="/social/github" class="social-btn github-btn"><i class="fab fa-github"></i> Continue with GitHub</a>
            <a href="/social/facebook" class="social-btn facebook-btn"><i class="fab fa-facebook"></i> Continue with Facebook</a>
        </div>

        <p class="mt-6 text-center text-gray-600">Already have an account? <a href="/login" class="text-blue-500 font-semibold">Login</a></p>
    </div>
    <script>
        function showLoader() {
            const button = document.getElementById('register-button');
            const buttonText = document.getElementById('button-text');
            const loader = document.getElementById('loader');

            button.disabled = true;
            buttonText.style.display = 'none';
            loader.style.display = 'block';
        }

        function togglePasswordVisibility(fieldId, iconId) {
            const passwordField = document.getElementById(fieldId);
            const icon = document.getElementById(iconId);

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        }
    </script>
</body>
</html>
