<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
        .login-container {
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
        .login-btn {
            background-color: #4C51BF;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 9999px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s, background-color 0.3s;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            cursor: pointer;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-btn:hover {
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
    <div class="login-container">
        <img src="/home/timothy/Desktop/secure-notes/src/images/AdobeStock_1253588313_Preview.jpeg" alt="Personal Tasks System" class="system-image">

        <h2 class="text-3xl font-bold mb-6 text-center text-gray-800">Welcome Back</h2>

        <?php if (!empty($_SESSION['errors'])): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded-xl mb-4 text-sm w-full">
                <?php foreach ($_SESSION['errors'] as $e): ?>
                    <p><?= $e ?></p>
                <?php endforeach; ?>
                <?php unset($_SESSION['errors']); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['success'])): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded-xl mb-4 text-sm w-full">
                <p><?= $_SESSION['success'] ?></p>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/login" class="flex flex-col w-full" onsubmit="showLoader(event)">
            <div class="input-group">
                <span class="icon"><i class="fas fa-user"></i></span>
                <input type="text" name="identifier" placeholder="Email or Username" class="w-full p-3 border rounded-full mb-3" required aria-label="Email or Username">
            </div>
            
            <div class="input-group">
                <span class="icon"><i class="fas fa-lock"></i></span>
                <input type="password" name="password" id="password-field" placeholder="Password" class="w-full p-3 border rounded-full mb-3" required aria-label="Password">
                <span class="password-toggle" onclick="togglePasswordVisibility()"><i class="fas fa-eye-slash" id="password-toggle-icon"></i></span>
            </div>

            <div class="flex items-center justify-between mb-4 mt-1 w-full">
                <div class="flex items-center">
                    <input type="checkbox" name="remember_me" id="remember-me" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <label for="remember-me" class="ml-2 block text-sm text-gray-700">Remember Me</label>
                </div>
                <p class="text-sm">
                    <a href="/password-reset" class="text-blue-500 hover:underline">Forgot Password?</a>
                </p>
            </div>
            
            <button type="submit" class="login-btn mt-3" id="login-button">
                <span id="button-text">Login</span>
                <span id="logging-in-text" class="hidden">Logging in...</span>
                <div class="loader ml-2" id="loader"></div>
            </button>
        </form>

        <p class="text-center text-gray-600 mt-6 mb-4">Or log in with</p>
        <div class="w-full flex flex-col space-y-3">
            <a href="/social/google" class="social-btn google-btn">
                <i class="fab fa-google"></i> Continue with Google
            </a>
            
            <a href="/social/github" class="social-btn github-btn">
                <i class="fab fa-github"></i> Continue with GitHub
            </a>

            <a href="/social/facebook" class="social-btn facebook-btn">
                <i class="fab fa-facebook"></i> Continue with Facebook
            </a>
        </div>

        <p class="mt-6 text-center text-gray-600">Don’t have an account? <a href="/register" class="text-blue-500 font-semibold">Register now</a></p>
    </div>

    <script>
        function showLoader(event) {
            // Prevent the form from submitting immediately
            event.preventDefault();
            
            const button = document.getElementById('login-button');
            const buttonText = document.getElementById('button-text');
            const loggingInText = document.getElementById('logging-in-text');
            const loader = document.getElementById('loader');
            const form = event.target;
            
            // Disable the button and show the loader
            button.disabled = true;
            buttonText.style.display = 'none';
            loggingInText.classList.remove('hidden');
            loader.style.display = 'block';

            // Wait for 1 second, then submit the form
            setTimeout(() => {
                form.submit();
            }, 1000); // 1000 milliseconds = 1 second
        }

        function togglePasswordVisibility() {
            const passwordField = document.getElementById('password-field');
            const icon = document.getElementById('password-toggle-icon');
            
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
