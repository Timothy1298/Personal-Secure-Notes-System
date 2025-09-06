<!DOCTYPE html>
<html>
<head>
    <title>Request Password Reset</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
<div class="bg-white p-8 rounded-2xl shadow-lg w-96">
    <h2 class="text-2xl font-bold mb-6 text-center">Request Password Reset</h2>

    <?php if(!empty($_SESSION['errors'])): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            <?php foreach($_SESSION['errors'] as $e) echo "<p>$e</p>"; unset($_SESSION['errors']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/password-reset-link">
        <input type="email" name="email" placeholder="Enter your email" class="w-full p-3 border rounded mb-3" required>
        <button type="submit" class="w-full bg-blue-500 text-white p-3 rounded hover:bg-blue-600">
            Send Reset Link
        </button>
    </form>
</div>
</body>
</html>