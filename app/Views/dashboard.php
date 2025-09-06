<?php
use Core\Session;

// Ensure user is logged in
if (!Session::get('user_id')) {
    header("Location: /login");
    exit;
}
$username = Session::get('username') ?? 'User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard | Secure Notes</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
</head>
<body class="bg-gray-100 h-screen flex">

  <!-- Sidebar -->
  <aside class="w-64 bg-gray-800 text-white flex flex-col">
    <div class="p-6 text-2xl font-bold border-b border-gray-700">TimothyKuria</div>
    <nav class="flex-1 px-4 py-6">
      <ul class="space-y-2">
        <li><a href="/dashboard" class="flex items-center p-3 rounded hover:bg-gray-700 bg-blue-600 text-white"><i class="fas fa-home mr-3"></i>Dashboard</a></li>
        <li><a href="/notes" class="flex items-center p-3 rounded hover:bg-gray-700"><i class="fas fa-sticky-note mr-3"></i>My Notes</a></li>
        <li><a href="/tasks" class="flex items-center p-3 rounded hover:bg-gray-700"><i class="fas fa-tasks mr-3"></i>Tasks</a></li>
        <li><a href="/categories" class="flex items-center p-3 rounded hover:bg-gray-700"><i class="fas fa-tags mr-3"></i>Categories</a></li>
        <li><a href="/reminders" class="flex items-center p-3 rounded hover:bg-gray-700"><i class="fas fa-bell mr-3"></i>Reminders</a></li>
        <li><a href="/archived" class="flex items-center p-3 rounded hover:bg-gray-700"><i class="fas fa-archive mr-3"></i>Archived</a></li>
        <li><a href="/settings" class="flex items-center p-3 rounded hover:bg-gray-700"><i class="fas fa-cog mr-3"></i>Settings</a></li>
      </ul>
    </nav>
  </aside>

  <!-- Main Content -->
  <div class="flex-1 flex flex-col">

    <!-- Navbar -->
    <header class="h-16 bg-white shadow-md flex items-center justify-between px-6">
      <div class="text-xl font-semibold">Dashboard</div>
      <div class="flex items-center space-x-4">
        <button class="relative">
          <i class="fas fa-bell text-gray-600 text-lg"></i>
          <span class="absolute -top-1 -right-1 text-xs bg-red-500 text-white rounded-full px-1">3</span>
        </button>
        <div class="relative group">
          <button class="flex items-center space-x-2">
           <i class="fas fa-user-circle text-2xl text-gray-600"></i>

            <span class="text-gray-700 font-medium"><?= $username ?></span>
            <i class="fas fa-chevron-down text-gray-600"></i>
          </button>
          <!-- Dropdown -->
          <div class="absolute right-0 mt-2 w-48 bg-white border rounded shadow-lg opacity-0 group-hover:opacity-100 transition-opacity">
            <a href="/profile" class="block px-4 py-2 hover:bg-gray-100">Profile</a>
            <a href="/settings" class="block px-4 py-2 hover:bg-gray-100">Settings</a>
            <a href="/logout" class="block px-4 py-2 hover:bg-gray-100">Logout</a>
          </div>
        </div>
      </div>
    </header>

    <!-- Dashboard Content -->
    <main class="p-6 flex-1 overflow-y-auto">
      <section id="dashboard">
        <h2 class="text-2xl font-bold mb-6">Dashboard Overview</h2>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
          <div class="bg-white shadow-lg rounded-2xl p-6 text-center hover:shadow-xl transition">
            <h3 class="text-gray-500 font-medium">Total Notes</h3>
            <p class="text-3xl font-bold text-blue-600 mt-2">12</p>
          </div>
          <div class="bg-white shadow-lg rounded-2xl p-6 text-center hover:shadow-xl transition">
            <h3 class="text-gray-500 font-medium">Pending Tasks</h3>
            <p class="text-3xl font-bold text-yellow-500 mt-2">5</p>
          </div>
          <div class="bg-white shadow-lg rounded-2xl p-6 text-center hover:shadow-xl transition">
            <h3 class="text-gray-500 font-medium">Completed Tasks</h3>
            <p class="text-3xl font-bold text-green-600 mt-2">18</p>
          </div>
          <div class="bg-white shadow-lg rounded-2xl p-6 text-center hover:shadow-xl transition">
            <h3 class="text-gray-500 font-medium">Upcoming Reminders</h3>
            <p class="text-3xl font-bold text-red-600 mt-2">3</p>
          </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
          <div class="bg-white shadow-lg rounded-2xl p-6 h-64">
            <h3 class="text-lg font-semibold mb-4">Notes Created per Month</h3>
            <canvas id="notesChart"></canvas>
          </div>
          <div class="bg-white shadow-lg rounded-2xl p-6 h-64">
            <h3 class="text-lg font-semibold mb-4">Task Completion Rate</h3>
            <canvas id="tasksChart"></canvas>
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white shadow-lg rounded-2xl p-6">
          <h3 class="text-lg font-semibold mb-4">Recent Activity</h3>
          <ul class="space-y-3 text-gray-700">
            <li>📝 You edited Note <strong>"Project Plan"</strong></li>
            <li>✅ Task <strong>"Finish Report"</strong> marked completed</li>
            <li>⏰ Reminder <strong>"Meeting Tomorrow"</strong> set for tomorrow</li>
          </ul>
        </div>
      </section>
    </main>
    <?php include __DIR__ . '/partials/footer.php'; ?>
  </div>

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    // Notes Chart
    const notesCtx = document.getElementById("notesChart").getContext("2d");
    new Chart(notesCtx, {
      type: "line",
      data: {
        labels: ["Jan","Feb","Mar","Apr","May","Jun","Jul"],
        datasets: [{
          label: "Notes Created",
          data: [3,7,4,6,5,9,8],
          borderColor: "#2563eb",
          backgroundColor: "rgba(37,99,235,0.2)",
          fill: true,
          tension: 0.4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false }},
        scales: { y: { beginAtZero: true }}
      }
    });

    // Tasks Chart
    const tasksCtx = document.getElementById("tasksChart").getContext("2d");
    new Chart(tasksCtx, {
      type: "pie",
      data: {
        labels: ["Completed","Pending","Overdue"],
        datasets: [{
          data: [18,5,2],
          backgroundColor: ["#16a34a","#facc15","#dc2626"]
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: "bottom" }}
      }
    });
  </script>

</body>
</html>
