<!DOCTYPE html>
<html lang="en">
<head>
    <title>Secure Notes | Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Suppress Tailwind production warning for development
        tailwind.config = {
            theme: {
                extend: {}
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
            --success-color: #10B981; /* Green for success checks */
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--primary-color);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        /* Main Grid Container (Wrapper for 2 columns) */
        .main-grid-container {
            display: grid;
            grid-template-columns: 1fr;
            max-width: 1200px;
            width: 100%;
            border-radius: 2rem;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            min-height: 600px; 
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

        /* Right Panel - Registration Form */
        .register-form-panel {
            background-color: white;
            padding: 2rem 4rem; /* Adjusted vertical padding slightly for more fields */
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
            border-radius: 0.75rem; 
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
        .register-btn {
            background-color: var(--accent-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem; 
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
        
        .register-btn:hover {
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
            .register-form-panel {
                border-radius: 2rem; 
            }
        }
    </style>
</head>
<body>

<div class="main-grid-container">
    
    <div class="info-panel">
        
        <!-- Security Icon Animation -->
        <div class="security-animation" style="width: 250px; height: 250px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%); border-radius: 50%; margin-bottom: 2rem;">
            <i class="fas fa-shield-alt" style="font-size: 120px; color: #10B981; animation: pulse 2s infinite;"></i>
        </div>
        
        <style>
            @keyframes pulse {
                0% { transform: scale(1); opacity: 1; }
                50% { transform: scale(1.05); opacity: 0.8; }
                100% { transform: scale(1); opacity: 1; }
            }
        </style>
        
        <h1 class="text-4xl font-extrabold mt-6 mb-2">
            Start Your Private Vault.
        </h1>
        <p class="text-lg font-light text-gray-300 mb-6">
            Join the secure way to manage your most sensitive notes.
        </p>

        <ul class="text-left space-y-3 w-full max-w-xs mt-4">
            <li class="flex items-center text-gray-200">
                <i class="fas fa-key mr-3 text-success-color"></i>
                <span class="font-medium">Direct Access Only</span>
            </li>
            <li class="flex items-center text-gray-200">
                <i class="fas fa-shield-alt mr-3 text-success-color"></i>
                <span class="font-medium">Maximum Data Control</span>
            </li>
            <li class="flex items-center text-gray-200">
                <i class="fas fa-user-lock mr-3 text-success-color"></i>
                <span class="font-medium">Your Credentials. Your Key.</span>
            </li>
        </ul>
    </div>

    <div class="register-form-panel">
        <h2 class="text-4xl font-bold mb-8 text-center text-gray-900">Register</h2>
        
        <i class="fas fa-clipboard-check text-5xl text-accent mb-6"></i>

        <?php if (!empty($_SESSION['errors'])): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 text-sm w-full border border-red-300">
                <?php foreach ($_SESSION['errors'] as $e): ?>
                    <p><?= $e ?></p>
                <?php endforeach; ?>
                <?php unset($_SESSION['errors']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/register" class="flex flex-col w-full" onsubmit="showLoader(event)">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= \Core\CSRF::generate() ?>">
            
            <div class="input-group">
                <span class="icon"><i class="fas fa-user"></i></span>
                <input type="text" name="username" placeholder="Username" class="w-full" required aria-label="Username">
            </div>

            <div class="input-group">
                <span class="icon"><i class="fas fa-envelope"></i></span>
                <input type="email" name="email" placeholder="Email" class="w-full" required aria-label="Email">
            </div>

            <div class="input-group">
                <span class="icon"><i class="fas fa-user-circle"></i></span>
                <input type="text" name="first_name" placeholder="First Name" class="w-full" aria-label="First Name">
            </div>

            <div class="input-group">
                <span class="icon"><i class="fas fa-user-circle"></i></span>
                <input type="text" name="last_name" placeholder="Last Name" class="w-full" aria-label="Last Name">
            </div>

            <div class="input-group">
                <span class="icon"><i class="fas fa-lock"></i></span>
                <input type="password" name="password" id="password-field-register" placeholder="Password" class="w-full" required aria-label="Password">
                <span class="password-toggle" onclick="togglePasswordVisibility('password-field-register', 'password-toggle-icon-register')"><i class="fas fa-eye-slash" id="password-toggle-icon-register"></i></span>
            </div>

            <div class="input-group">
                <span class="icon"><i class="fas fa-lock"></i></span>
                <input type="password" name="confirm_password" id="confirm-password-field" placeholder="Confirm Password" class="w-full" required aria-label="Confirm Password">
                <span class="password-toggle" onclick="togglePasswordVisibility('confirm-password-field', 'password-toggle-icon-confirm')"><i class="fas fa-eye-slash" id="password-toggle-icon-confirm"></i></span>
            </div>

            <button type="submit" class="register-btn mt-4" id="register-button">
                <span id="button-text">Create Account</span>
                <span id="registering-text" class="hidden">Creating...</span>
                <div class="loader ml-3" id="loader"></div>
            </button>
        </form>

        <p class="mt-8 text-center text-gray-600">
            Already have an account? 
            <a href="/login" class="text-accent font-semibold hover:underline">Login here</a>
        </p>
    </div>
</div>

<script>
    function showLoader(event) {
        // Prevent form submission if fields are empty
        const form = event.target;
        if (!form.checkValidity()) {
            return; 
        }

        event.preventDefault();
        
        const button = document.getElementById('register-button');
        const buttonText = document.getElementById('button-text');
        const registeringText = document.getElementById('registering-text');
        const loader = document.getElementById('loader');
        
        // Disable the button and show the loader
        button.disabled = true;
        buttonText.style.display = 'none';
        registeringText.classList.remove('hidden');
        loader.style.display = 'block';

        // Submit the form after a short delay (for visual effect)
        setTimeout(() => {
            form.submit();
        }, 1200); 
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