<?php
$pageTitle = "Access Shared Content";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
        <div class="text-center mb-8">
            <i class="fas fa-lock text-4xl text-blue-600 mb-4"></i>
            <h1 class="text-2xl font-bold text-gray-900">Protected Content</h1>
            <p class="text-gray-600 mt-2">This content is password protected</p>
        </div>

        <form id="auth-form" class="space-y-6">
            <input type="hidden" id="token" value="<?= htmlspecialchars($token) ?>">
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <input type="password" id="password" name="password" required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                       placeholder="Enter password">
            </div>

            <button type="submit" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                <i class="fas fa-unlock mr-2"></i>Access Content
            </button>
        </form>

        <div id="error-message" class="hidden mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex">
                <i class="fas fa-exclamation-triangle text-red-400 mt-1"></i>
                <div class="ml-3">
                    <p class="text-red-800 text-sm" id="error-text"></p>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('auth-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const token = document.getElementById('token').value;
        const password = document.getElementById('password').value;
        const errorDiv = document.getElementById('error-message');
        const errorText = document.getElementById('error-text');
        
        fetch('/shared/authenticate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `token=${encodeURIComponent(token)}&password=${encodeURIComponent(password)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                errorText.textContent = data.message;
                errorDiv.classList.remove('hidden');
            }
        })
        .catch(error => {
            errorText.textContent = 'An error occurred. Please try again.';
            errorDiv.classList.remove('hidden');
        });
    });
    </script>
</body>
</html>
