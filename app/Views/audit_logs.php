<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Audit Logs | Secure Notes</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
</head>
<body class="bg-gray-100 h-screen flex">

  <?php include __DIR__ . '/partials/sidebar.php'; ?>

  <div class="flex-1 flex flex-col">
    <?php include __DIR__ . '/partials/navbar.php'; ?>

    <main class="p-6 flex-1 overflow-y-auto">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Audit Logs</h2>
      </div>

      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
          <h3 class="text-xl font-bold text-gray-700 mb-4">
            <i class="fas fa-search-plus mr-2 text-blue-500"></i>Filter Logs by Time
          </h3>
          <form action="/audit-logs/filter" method="GET" class="flex flex-col md:flex-row items-end md:items-center space-y-4 md:space-y-0 md:space-x-4">
              <div class="flex-1 w-full">
                  <label for="start_time" class="block text-sm text-gray-600 font-medium mb-1">From:</label>
                  <input type="datetime-local" id="start_time" name="start_time" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 ease-in-out">
              </div>
              <div class="flex-1 w-full">
                  <label for="end_time" class="block text-sm text-gray-600 font-medium mb-1">To:</label>
                  <input type="datetime-local" id="end_time" name="end_time" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 ease-in-out">
              </div>
              <button type="submit" class="w-full md:w-auto px-6 py-2.5 bg-blue-600 text-white font-semibold rounded-md shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200 ease-in-out">
                  <i class="fas fa-filter mr-2"></i>Filter
              </button>
              <a href="/audit-logs" class="w-full md:w-auto px-6 py-2.5 text-center text-gray-600 font-semibold rounded-md border border-gray-300 shadow-sm hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 transition duration-200 ease-in-out">
                Reset
              </a>
          </form>
      </div>
      
      <?php if (!isset($isFiltered) || !$isFiltered): ?>
        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 rounded-md mb-6" role="alert">
            <p class="font-bold">By default, we show the last 10 log entries.</p>
            <p class="text-sm">Use the filter above to find older logs within a specific time period. 🔍</p>
        </div>
      <?php endif; ?>

      <div class="space-y-4">
        <?php if (!empty($logs)): ?>
          <?php foreach ($logs as $log): ?>
            <div class="bg-white shadow-md hover:shadow-lg transition-shadow duration-300 rounded-lg p-6 border-l-4 border-blue-500">
                <div class="flex justify-between items-center mb-2">
                    <p class="text-lg text-gray-700 font-semibold">
                        You **<?= htmlspecialchars($log['action']) ?>**
                    </p>
                    <span class="text-sm text-gray-500 font-medium">
                        <i class="fas fa-clock mr-1"></i><?= date('F j, Y, g:i a', strtotime($log['created_at'])) ?>
                    </span>
                </div>
                <p class="text-gray-600 text-sm">
                    <span class="font-bold">Log ID:</span> <?= htmlspecialchars($log['id']) ?> | 
                    <span class="font-bold">User ID:</span> <?= htmlspecialchars($log['user_id']) ?>
                </p>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="bg-white rounded-lg shadow-md p-6 text-center">
            <p class="text-gray-500 text-lg">
                <i class="fas fa-search text-2xl mb-2"></i><br>
                No recent actions found.
            </p>
          </div>
        <?php endif; ?>
      </div>
    </main>
    <?php include __DIR__ . '/partials/footer.php'; ?>
  </div>
</body>
</html>