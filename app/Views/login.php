<!DOCTYPE html>
<html lang="en">
<head>
    <title>Secure Notes | Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Suppress Tailwind production warning for development
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #3B4A6C; /* Deep Blue/Slate */
            --secondary-color: #7F8DAE; /* Lighter Slate */
            --accent-color: #4C51BF; /* Deeper Purple/Indigo for buttons/focus */
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--primary-color); /* Uniform dark background */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        /* Main Grid Container */
        .main-grid-container {
            display: grid;
            grid-template-columns: 1fr;
            max-width: 1200px;
            width: 100%;
            border-radius: 2rem;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            min-height: 600px; /* Ensure a decent height */
        }

        @media (min-width: 1024px) {
            .main-grid-container {
                grid-template-columns: 1fr 1fr; /* Two columns on large screens */
            }
        }

        /* Left Panel - Animation/Info Side */
        .info-panel {
            background: linear-gradient(145deg, var(--primary-color) 0%, #2A364C 100%);
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        /* Right Panel - Login Form */
        .login-form-panel {
            background-color: white;
            padding: 3rem 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
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
            border-radius: 0.75rem; /* Slightly rounded corners */
            border: 1px solid #e5e7eb;
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
            transition: all 0.3s ease;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        
        .input-group input:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(76, 81, 191, 0.1);
        }
        
        .input-group .icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-color);
        }
        
        /* Button styling */
        .login-btn {
            background-color: var(--accent-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem; /* Match input styling */
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s, background-color 0.3s;
            box-shadow: 0 4px 6px -1px rgba(76, 81, 191, 0.3), 0 2px 4px -1px rgba(76, 81, 191, 0.06);
            cursor: pointer;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
        }
        
        .login-btn:hover {
            background-color: #343D9B;
            transform: translateY(-1px);
            box-shadow: 0 10px 15px -3px rgba(76, 81, 191, 0.3), 0 4px 6px -2px rgba(76, 81, 191, 0.05);
        }
        
        /* Loader styles */
        .loader {
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid white;
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
        
        @keyframes pulse {
            0%, 100% { 
                transform: scale(1);
                opacity: 0.8;
            }
            50% { 
                transform: scale(1.05);
                opacity: 1;
            }
        }

        .text-accent {
            color: var(--accent-color);
            transition: color 0.2s;
        }
        
        .text-accent:hover {
            color: #343D9B;
        }

        /* Password toggle icon */
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            cursor: pointer;
            z-index: 10;
        }

        /* Hide the info panel on small screens */
        @media (max-width: 1023px) {
            .info-panel {
                display: none;
            }
            .login-form-panel {
                border-radius: 2rem; /* Keep rounding on small screens */
            }
        }
    </style>
</head>
<body>

<div class="main-grid-container">
    
    <div class="info-panel">
        
        <div class="security-animation" style="width: 250px; height: 250px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%); border-radius: 50%; margin: 0 auto;">
            <i class="fas fa-shield-alt text-8xl text-white opacity-80" style="animation: pulse 2s ease-in-out infinite;"></i>
        </div>
        
        <h1 class="text-4xl font-extrabold mt-6 mb-2">
            Your Notes. Truly Private.
        </h1>
        <p class="text-lg font-light text-gray-300 mb-6">
            Securely capture your thoughts with zero-knowledge encryption.
        </p>

        <ul class="text-left space-y-3 w-full max-w-xs mt-4">
            <li class="flex items-center text-gray-200">
                <i class="fas fa-check-circle mr-3 text-green-400"></i>
                <span class="font-medium">End-to-End Encryption</span>
            </li>
            <li class="flex items-center text-gray-200">
                <i class="fas fa-check-circle mr-3 text-green-400"></i>
                <span class="font-medium">Self-Contained Security</span>
            </li>
            <li class="flex items-center text-gray-200">
                <i class="fas fa-check-circle mr-3 text-green-400"></i>
                <span class="font-medium">No Social Dependencies</span>
            </li>
        </ul>
    </div>

    <div class="login-form-panel">
        <h2 class="text-4xl font-bold mb-8 text-center text-gray-900">Sign In</h2>
        
        <i class="fas fa-lock text-5xl text-accent mb-6"></i>

        <?php if (!empty($_SESSION['errors'])): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 text-sm w-full border border-red-300">
                <?php foreach ($_SESSION['errors'] as $e): ?>
                    <p><?= $e ?></p>
                <?php endforeach; ?>
                <?php unset($_SESSION['errors']); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['success'])): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded-lg mb-4 text-sm w-full border border-green-300">
                <p><?= $_SESSION['success'] ?></p>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/login" class="flex flex-col w-full" onsubmit="showLoader(event)">
            <input type="hidden" name="csrf_token" value="<?= \Core\CSRF::generate() ?>">
            
            <div class="input-group">
                <span class="icon"><i class="fas fa-envelope"></i></span>
                <input type="text" name="identifier" placeholder="Email or Username" class="w-full" required aria-label="Email or Username">
            </div>
            
            <div class="input-group">
                <span class="icon"><i class="fas fa-key"></i></span>
                <input type="password" name="password" id="password-field" placeholder="Password" class="w-full" required aria-label="Password">
                <span class="password-toggle" onclick="togglePasswordVisibility()"><i class="fas fa-eye-slash" id="password-toggle-icon"></i></span>
            </div>

            <div class="flex items-center justify-between mb-8 mt-1 w-full">
                <div class="flex items-center">
                    <input type="checkbox" name="remember_me" id="remember-me" class="rounded border-gray-300 text-accent shadow-sm focus:ring-accent focus:ring-opacity-50">
                    <label for="remember-me" class="ml-2 block text-sm text-gray-700">Remember Me</label>
                </div>
                <p class="text-sm">
                    <a href="/password-reset" class="text-accent hover:underline">Forgot Password?</a>
                </p>
            </div>
            
            <button type="submit" class="login-btn" id="login-button">
                <span id="button-text">Login Securely</span>
                <span id="logging-in-text" class="hidden">Verifying...</span>
                <div class="loader ml-3" id="loader"></div>
            </button>
        </form>

        <!-- Social Login Options -->
        <div class="mt-6 w-full">
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">Or continue with</span>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-3 gap-3">
                <a href="/social/google" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-lg bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 transition-colors duration-200">
                    <i class="fab fa-google text-lg"></i>
                </a>
                <a href="/social/github" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-lg bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 transition-colors duration-200">
                    <i class="fab fa-github text-lg"></i>
                </a>
                <a href="/social/facebook" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-lg bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 transition-colors duration-200">
                    <i class="fab fa-facebook text-lg"></i>
                </a>
            </div>
        </div>

        <p class="mt-8 text-center text-gray-600">
            Don’t have an account? 
            <a href="/register" class="text-accent font-semibold hover:underline">Create a free account</a>
        </p>
    </div>
</div>

<script>
    function showLoader(event) {
        // Simple form validation check (though server-side is necessary)
        const identifier = document.querySelector('input[name="identifier"]').value;
        const password = document.querySelector('input[name="password"]').value;

        if (!identifier || !password) {
            return; // Allow browser/required attribute to handle missing fields
        }

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

        // Submit the form after a short delay (for visual effect)
        setTimeout(() => {
            form.submit();
        }, 1200); 
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