<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Tags | Secure Notes</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
</head>
<body class="bg-gray-100 h-screen flex">

  <?php include __DIR__ . '/partials/sidebar.php'; ?>

  <div class="flex-1 flex flex-col">
    <?php include __DIR__ . '/partials/navbar.php'; ?>

    <main class="p-6 flex-1 overflow-y-auto">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold">My Tags</h2>
        <button onclick="openModal()" class="mb-2 bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700">
          <i class="fas fa-plus mr-2"></i>Add Tag
        </button>
      </div>

      <?php
      // Display success or error messages
      if (isset($_SESSION['success'])): ?>
          <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
              <span class="block sm:inline"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
          </div>
      <?php endif; ?>
      <?php if (isset($_SESSION['errors'])): ?>
          <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
              <?php foreach ($_SESSION['errors'] as $error): ?>
                  <span class="block sm:inline"><?= htmlspecialchars($error); ?></span>
              <?php endforeach; unset($_SESSION['errors']); ?>
          </div>
      <?php endif; ?>

      <div class="flex flex-wrap gap-2">
        <?php if (!empty($tags)): ?>
          <?php foreach ($tags as $tag): ?>
            <span class="bg-gray-200 text-gray-800 text-sm font-semibold px-4 py-2 rounded-full shadow-md">
              <?= htmlspecialchars($tag['name']) ?>
            </span>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-gray-500">No tags yet. Click <strong>Add Tag</strong> to create one.</p>
        <?php endif; ?>
      </div>
    </main>
    <?php include __DIR__ . '/partials/footer.php'; ?>
  </div>

  <div id="tagModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-96 relative">
      <h3 class="text-xl font-bold mb-4">Add New Tag</h3>
      <form id="tagForm" method="POST" action="/tags/store">
        <div class="mb-4">
            <label for="name" class="block mb-1 font-semibold">Tag Name</label>
            <input type="text" id="name" name="name" class="border rounded p-2 w-full" required>
        </div>
        
        <div class="flex justify-end space-x-2">
          <button type="button" onclick="closeModal()" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400">Cancel</button>
          <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Save</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function openModal() {
      document.getElementById('tagModal').classList.remove('hidden');
      document.getElementById('tagModal').classList.add('flex');
    }
    function closeModal() {
      document.getElementById('tagModal').classList.add('hidden');
      document.getElementById('tagModal').classList.remove('flex');
    }
  </script>
</body>
</html>