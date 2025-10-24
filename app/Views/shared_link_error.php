<?php
$pageTitle = "Link Not Found";
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
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
        <div class="mb-8">
            <i class="fas fa-exclamation-triangle text-6xl text-red-500 mb-4"></i>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Link Not Found</h1>
            <p class="text-gray-600">This shared link is either invalid, expired, or has been removed.</p>
        </div>

        <div class="space-y-4">
            <a href="/" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                <i class="fas fa-home mr-2"></i>Go to Homepage
            </a>
            
            <p class="text-sm text-gray-500">
                If you believe this is an error, please contact the person who shared this link with you.
            </p>
        </div>
    </div>
</body>
</html>
