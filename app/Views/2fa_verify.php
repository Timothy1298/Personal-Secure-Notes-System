<!DOCTYPE html>
<html lang="en">
<head>
    <title>Two-Factor Authentication | SecureNote Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #3B4A6C;
            --secondary-color: #7F8DAE;
            --accent-color: #4C51BF;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--primary-color) 0%, #2A364C 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .glassmorphism {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .floating-animation {
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite alternate;
        }
        
        @keyframes pulse-glow {
            from { box-shadow: 0 0 20px rgba(76, 81, 191, 0.4); }
            to { box-shadow: 0 0 30px rgba(76, 81, 191, 0.8); }
        }

        .code-input {
            width: 50px;
            height: 60px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            border: 2px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .code-input:focus {
            outline: none;
            border-color: var(--accent-color);
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.05);
        }

        .code-input.filled {
            background: rgba(76, 81, 191, 0.3);
            border-color: var(--accent-color);
        }

        .btn-3d {
            position: relative;
            transform-style: preserve-3d;
            transition: all 0.3s ease;
        }
        
        .btn-3d:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(76, 81, 191, 0.4);
        }
        
        .btn-3d:active {
            transform: translateY(0px);
        }

        .loading-spinner {
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid #fff;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .slide-in {
            animation: slideIn 0.8s ease-out;
        }
        
        @keyframes slideIn {
            from { 
                opacity: 0; 
                transform: translateY(30px) scale(0.95); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0) scale(1); 
            }
        }
    </style>
</head>
<body>
    <div class="glassmorphism p-8 rounded-3xl shadow-2xl w-full max-w-md mx-4 slide-in">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="floating-animation mb-4">
                <i class="fas fa-shield-alt text-6xl text-white opacity-90"></i>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Two-Factor Authentication</h1>
            <p class="text-white opacity-80">Enter the 6-digit code sent to your email</p>
        </div>

        <!-- Error Messages -->
        <?php if (isset($_SESSION['errors'])): ?>
            <div class="bg-red-500 bg-opacity-20 border border-red-400 text-red-100 px-4 py-3 rounded-xl mb-6 backdrop-blur-sm">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <ul>
                        <?php foreach ($_SESSION['errors'] as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <!-- 2FA Form -->
        <form method="POST" action="/2fa-verify" class="space-y-6" id="2faForm">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            
            <div class="flex justify-center space-x-3 mb-6">
                <input type="text" name="code_1" class="code-input" maxlength="1" pattern="[0-9]" required>
                <input type="text" name="code_2" class="code-input" maxlength="1" pattern="[0-9]" required>
                <input type="text" name="code_3" class="code-input" maxlength="1" pattern="[0-9]" required>
                <input type="text" name="code_4" class="code-input" maxlength="1" pattern="[0-9]" required>
                <input type="text" name="code_5" class="code-input" maxlength="1" pattern="[0-9]" required>
                <input type="text" name="code_6" class="code-input" maxlength="1" pattern="[0-9]" required>
            </div>

            <input type="hidden" name="2fa_code" id="fullCode">

            <button type="submit" class="btn-3d w-full bg-white bg-opacity-20 text-white py-3 px-6 rounded-xl font-semibold hover:bg-opacity-30 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 transition-all duration-300 flex items-center justify-center">
                <span id="verifyText">Verify Code</span>
                <div id="verifySpinner" class="loading-spinner ml-2 hidden"></div>
            </button>
        </form>

        <!-- Resend Code -->
        <div class="mt-6 text-center">
            <p class="text-white opacity-80 text-sm mb-3">Didn't receive the code?</p>
            <button onclick="resendCode()" class="text-white opacity-80 hover:opacity-100 transition-opacity text-sm underline" id="resendBtn">
                Resend Code
            </button>
            <p class="text-white opacity-60 text-xs mt-2" id="resendTimer"></p>
        </div>

        <!-- Alternative Options -->
        <div class="mt-6 text-center">
            <p class="text-white opacity-60 text-sm">
                Having trouble? 
                <a href="/login" class="text-white opacity-80 hover:opacity-100 transition-opacity underline">
                    Back to Login
                </a>
            </p>
        </div>
    </div>

    <script>
        const codeInputs = document.querySelectorAll('.code-input');
        const fullCodeInput = document.getElementById('fullCode');
        const form = document.getElementById('2faForm');
        let resendTimer = 60;
        let timerInterval;

        // Auto-focus first input
        codeInputs[0].focus();

        // Handle input navigation
        codeInputs.forEach((input, index) => {
            input.addEventListener('input', function(e) {
                const value = e.target.value;
                
                // Only allow numbers
                if (!/^\d$/.test(value)) {
                    e.target.value = '';
                    return;
                }

                // Add filled class
                e.target.classList.add('filled');

                // Move to next input
                if (value && index < codeInputs.length - 1) {
                    codeInputs[index + 1].focus();
                }

                // Update hidden input
                updateFullCode();

                // Auto-submit when all fields are filled
                if (index === codeInputs.length - 1 && value) {
                    setTimeout(() => {
                        form.submit();
                    }, 500);
                }
            });

            input.addEventListener('keydown', function(e) {
                // Handle backspace
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    codeInputs[index - 1].focus();
                    codeInputs[index - 1].classList.remove('filled');
                }

                // Handle paste
                if (e.key === 'v' && (e.ctrlKey || e.metaKey)) {
                    e.preventDefault();
                    navigator.clipboard.readText().then(text => {
                        const digits = text.replace(/\D/g, '').slice(0, 6);
                        digits.split('').forEach((digit, i) => {
                            if (codeInputs[i]) {
                                codeInputs[i].value = digit;
                                codeInputs[i].classList.add('filled');
                            }
                        });
                        updateFullCode();
                        if (digits.length === 6) {
                            setTimeout(() => form.submit(), 500);
                        }
                    });
                }
            });
        });

        function updateFullCode() {
            const code = Array.from(codeInputs).map(input => input.value).join('');
            fullCodeInput.value = code;
        }

        // Form submission
        form.addEventListener('submit', function(e) {
            const verifyText = document.getElementById('verifyText');
            const verifySpinner = document.getElementById('verifySpinner');
            
            verifyText.textContent = 'Verifying...';
            verifySpinner.classList.remove('hidden');
        });

        // Resend code functionality
        function resendCode() {
            const resendBtn = document.getElementById('resendBtn');
            const resendTimerEl = document.getElementById('resendTimer');
            
            resendBtn.disabled = true;
            resendBtn.textContent = 'Sending...';
            
            // Simulate API call
            fetch('/2fa-resend', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    csrf_token: document.querySelector('input[name="csrf_token"]').value
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resendBtn.textContent = 'Code Sent!';
                    startResendTimer();
                } else {
                    resendBtn.textContent = 'Failed to send';
                    setTimeout(() => {
                        resendBtn.textContent = 'Resend Code';
                        resendBtn.disabled = false;
                    }, 2000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                resendBtn.textContent = 'Error occurred';
                setTimeout(() => {
                    resendBtn.textContent = 'Resend Code';
                    resendBtn.disabled = false;
                }, 2000);
            });
        }

        function startResendTimer() {
            resendTimer = 60;
            const resendBtn = document.getElementById('resendBtn');
            const resendTimerEl = document.getElementById('resendTimer');
            
            timerInterval = setInterval(() => {
                resendTimerEl.textContent = `Resend available in ${resendTimer} seconds`;
                resendTimer--;
                
                if (resendTimer < 0) {
                    clearInterval(timerInterval);
                    resendBtn.disabled = false;
                    resendBtn.textContent = 'Resend Code';
                    resendTimerEl.textContent = '';
                }
            }, 1000);
        }

        // Start initial timer
        startResendTimer();
    </script>
</body>
</html>
