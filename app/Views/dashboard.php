<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard | SecureNote Pro</title>
  
  <!-- Theme CSS Variables -->
  <style>
    :root {
      --color-primary: #3b82f6;
      --color-secondary: #64748b;
      --color-background: #ffffff;
      --color-surface: #f8fafc;
      --color-text: #1e293b;
      --color-textSecondary: #64748b;
      --color-border: #e2e8f0;
      --color-success: #10b981;
      --color-warning: #f59e0b;
      --color-error: #ef4444;
      --color-info: #06b6d4;
    }
    
    .dark-mode {
      --color-primary: #60a5fa;
      --color-secondary: #94a3b8;
      --color-background: #0f172a;
      --color-surface: #1e293b;
      --color-text: #f1f5f9;
      --color-textSecondary: #94a3b8;
      --color-border: #334155;
      --color-success: #34d399;
      --color-warning: #fbbf24;
      --color-error: #f87171;
      --color-info: #22d3ee;
    }
    
    body {
      background-color: var(--color-background);
      color: var(--color-text);
      transition: background-color 0.3s ease, color 0.3s ease;
    }
    
    .glassmorphism {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .dark-mode .glassmorphism {
      background: rgba(30, 41, 59, 0.3);
      border: 1px solid rgba(51, 65, 85, 0.3);
    }
  </style>
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
          },
          animation: {
            'fade-in': 'fadeIn 0.5s ease-in-out',
            'slide-up': 'slideUp 0.3s ease-out',
            'bounce-gentle': 'bounceGentle 2s infinite',
            'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
          }
        }
      }
    }
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    
    * {
      font-family: 'Inter', sans-serif;
    }
    
    .glassmorphism {
      background: rgba(255, 255, 255, 0.8);
      backdrop-filter: blur(8px);
      border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    .card-3d {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(4px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      transform-style: preserve-3d;
    }
    
    .card-3d:hover {
      transform: translateY(-8px) rotateX(5deg);
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }
    
    .floating-animation {
      animation: float 6s ease-in-out infinite;
    }
    
    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes slideUp {
      from { transform: translateY(30px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }
    
    @keyframes bounceGentle {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-5px); }
    }
    
    .gradient-text {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    .glass-card {
      background: rgba(255, 255, 255, 0.8);
      backdrop-filter: blur(8px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
    }
    
    .modal-backdrop { 
      backdrop-filter: blur(8px); 
    }
    
    .modal-content {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    .neon-glow {
      box-shadow: 0 0 20px rgba(102, 126, 234, 0.5);
    }
    
    .hover-lift {
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .hover-lift:hover {
      transform: translateY(-5px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }
    
    .progress-ring {
      transform: rotate(-90deg);
    }
    
    .progress-ring-circle {
      stroke-dasharray: 283;
      stroke-dashoffset: 283;
      transition: stroke-dashoffset 0.5s ease-in-out;
    }
    
    .pulse-glow {
      animation: pulse-glow 2s ease-in-out infinite alternate;
    }
    
    @keyframes pulse-glow {
      from { box-shadow: 0 0 20px rgba(59, 130, 246, 0.4); }
      to { box-shadow: 0 0 30px rgba(59, 130, 246, 0.8); }
    }
    
    .btn-3d {
      position: relative;
      transform-style: preserve-3d;
      transition: all 0.3s ease;
    }
    
    .btn-3d:hover {
      transform: translateY(-2px) rotateX(5deg);
      box-shadow: 0 10px 25px rgba(59, 130, 246, 0.4);
    }
    
    .btn-3d:active {
      transform: translateY(0px) rotateX(2deg);
    }
    
    .gradient-bg {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .gradient-text {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    .stat-card {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.8) 0%, rgba(255, 255, 255, 0.6) 100%);
      backdrop-filter: blur(8px);
      border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    .activity-item {
      background: rgba(255, 255, 255, 0.8);
      backdrop-filter: blur(4px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      transition: all 0.3s ease;
    }
    
    .activity-item:hover {
      background: rgba(255, 255, 255, 0.1);
      transform: translateX(4px);
    }
    
    .chart-container {
      position: relative;
      height: 300px;
      background: rgba(255, 255, 255, 0.8);
      backdrop-filter: blur(4px);
      border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    .progress-ring {
      transform: rotate(-90deg);
    }
    
    .progress-ring-circle {
      stroke-dasharray: 283;
      stroke-dashoffset: 283;
      transition: stroke-dashoffset 0.5s ease-in-out;
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
    
    .toast {
      position: fixed;
      top: 1rem;
      right: 1rem;
      z-index: 9999;
      padding: 1rem 1.5rem;
      border-radius: 0.75rem;
      color: white;
      font-weight: 500;
      transform: translateX(100%);
      transition: transform 0.3s ease;
    }
    
    .toast.show {
      transform: translateX(0);
    }
    
    .toast.success {
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }
    
    .toast.error {
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }
    
    .toast.info {
      background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    }
    
    .weather-widget {
      background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
    }
    
    .quote-widget {
      background: linear-gradient(135deg, #fd79a8 0%, #e84393 100%);
    }
    
    .mood-widget {
      background: linear-gradient(135deg, #fdcb6e 0%, #e17055 100%);
    }
    
    .loading-skeleton {
      background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
      background-size: 200% 100%;
      animation: loading 1.5s infinite;
    }
    
    @keyframes loading {
      0% { background-position: 200% 0; }
      100% { background-position: -200% 0; }
    }
  </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 min-h-screen">
  
  <!-- CSRF Token -->
  <input type="hidden" name="csrf_token" value="<?= \Core\CSRF::generate() ?>">
  
  <!-- Dashboard Loader System -->
  <div id="dashboardLoader" class="fixed inset-0 bg-black bg-opacity-30 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen">
      <div class="text-center text-white">
        <div class="w-12 h-12 border-4 border-white border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
        <p id="dashboardLoaderMessage">Loading...</p>
      </div>
    </div>
  </div>

  <!-- Dashboard Toast Container -->
  <div id="dashboardToastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>

  <!-- Main Container -->
  <div class="flex h-screen">
    <!-- Sidebar -->
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Header -->
      <?php 
        $page_title = "Dashboard";
        include __DIR__ . '/partials/navbar.php'; 
      ?>

      <!-- Content -->
      <main class="flex-1 overflow-y-auto p-6">
        
        <!-- Welcome Section -->
        <div class="mb-8">
          <div class="flex items-center justify-between">
            <div class="animate-fade-in">
              <h1 class="text-4xl font-bold gradient-text mb-2 dark:text-white">Welcome back, <?= htmlspecialchars($user['first_name'] ?? $user['username']) ?>!</h1>
              <p class="text-gray-600 dark:text-gray-300">Here's what's happening with your productivity today</p>
              <div class="flex items-center gap-4 mt-4">
                <div class="flex items-center gap-2">
                  <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                  <span class="text-sm text-gray-600">System Online</span>
                </div>
                <div class="flex items-center gap-2">
                  <i class="fas fa-shield-alt text-green-500"></i>
                  <span class="text-sm text-gray-600">Secure Connection</span>
                </div>
              </div>
            </div>
            <div class="flex items-center gap-4">
              <div class="text-right animate-slide-up">
                <div class="text-sm text-gray-500">Current Time</div>
                <div id="currentTime" class="text-lg font-semibold text-gray-800"></div>
                <div id="currentDate" class="text-sm text-gray-500"></div>
              </div>
              <div class="floating-animation">
                <div class="relative">
                  <i class="fas fa-sun text-4xl text-yellow-500"></i>
                  <div class="absolute -top-1 -right-1 w-3 h-3 bg-yellow-400 rounded-full animate-bounce-gentle"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <!-- Notes Stat -->
          <div class="stat-card rounded-2xl p-6 slide-in hover-lift glass-card">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Total Notes</p>
                <p class="text-3xl font-bold text-gray-900" id="totalNotes"><?= $stats['total_notes'] ?? 0 ?></p>
                <p class="text-xs text-green-600 mt-1 flex items-center gap-1">
                  <i class="fas fa-arrow-up mr-1"></i>+<?= $stats['notes_this_week'] ?? 0 ?> this week
                </p>
              </div>
              <div class="p-3 bg-blue-100 rounded-full neon-glow">
                <i class="fas fa-sticky-note text-2xl text-blue-600"></i>
              </div>
            </div>
            <div class="mt-4">
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-blue-600 h-2 rounded-full transition-all duration-1000" style="width: 75%"></div>
              </div>
            </div>
          </div>

          <!-- Tasks Stat -->
          <div class="stat-card rounded-2xl p-6 slide-in hover-lift glass-card">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Active Tasks</p>
                <p class="text-3xl font-bold text-gray-900" id="activeTasks"><?= $stats['active_tasks'] ?? 0 ?></p>
                <p class="text-xs text-orange-600 mt-1 flex items-center gap-1">
                  <i class="fas fa-clock mr-1"></i><?= $stats['overdue_tasks'] ?? 0 ?> overdue
                </p>
              </div>
              <div class="p-3 bg-green-100 rounded-full neon-glow">
                <i class="fas fa-tasks text-2xl text-green-600"></i>
              </div>
            </div>
            <div class="mt-4">
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-green-600 h-2 rounded-full transition-all duration-1000" style="width: 60%"></div>
              </div>
            </div>
          </div>

          <!-- Productivity Score -->
          <div class="stat-card rounded-2xl p-6 slide-in hover-lift glass-card">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Productivity</p>
                <p class="text-3xl font-bold text-gray-900" id="productivityScore"><?= $stats['productivity_score'] ?? 0 ?>%</p>
                <p class="text-xs text-blue-600 mt-1 flex items-center gap-1">
                  <i class="fas fa-trending-up mr-1"></i>+5% from yesterday
                </p>
              </div>
              <div class="p-3 bg-purple-100 rounded-full neon-glow">
                <i class="fas fa-chart-line text-2xl text-purple-600"></i>
              </div>
            </div>
            <div class="mt-4">
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-purple-600 h-2 rounded-full transition-all duration-1000" style="width: <?= $stats['productivity_score'] ?? 0 ?>%"></div>
              </div>
            </div>
          </div>

          <!-- Streak -->
          <div class="stat-card rounded-2xl p-6 slide-in hover-lift glass-card">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Daily Streak</p>
                <p class="text-3xl font-bold text-gray-900" id="dailyStreak"><?= $stats['daily_streak'] ?? 0 ?></p>
                <p class="text-xs text-red-600 mt-1 flex items-center gap-1">
                  <i class="fas fa-fire mr-1"></i>Keep it up!
                </p>
              </div>
              <div class="p-3 bg-red-100 rounded-full neon-glow">
                <i class="fas fa-fire text-2xl text-red-600"></i>
              </div>
            </div>
            <div class="mt-4">
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-red-600 h-2 rounded-full transition-all duration-1000" style="width: 85%"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Dashboard Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
          
          <!-- Left Column -->
          <div class="lg:col-span-2 space-y-8">
            
            <!-- Productivity Chart -->
            <div class="card-3d rounded-2xl p-6">
              <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-800">Productivity Trends</h3>
                <div class="flex items-center gap-2">
                  <select id="chartPeriod" onchange="updateChart()" class="px-3 py-1 bg-gray-100 rounded-lg text-sm">
                    <option value="7">Last 7 days</option>
                    <option value="30">Last 30 days</option>
                    <option value="90">Last 3 months</option>
                  </select>
                </div>
              </div>
              <div class="chart-container rounded-xl p-4">
                <canvas id="productivityChart"></canvas>
              </div>
            </div>

            <!-- Task Progress -->
            <div class="card-3d rounded-2xl p-6">
              <h3 class="text-xl font-bold text-gray-800 mb-6">Task Progress Overview</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <h4 class="text-sm font-medium text-gray-600 mb-3">By Status</h4>
                  <div class="space-y-3">
                    <div class="flex items-center justify-between">
                      <span class="text-sm text-gray-600">Completed</span>
                      <span class="text-sm font-semibold text-green-600"><?= $stats['completed_tasks'] ?? 0 ?></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                      <div class="bg-green-500 h-2 rounded-full" style="width: <?= $stats['completion_rate'] ?? 0 ?>%"></div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                      <span class="text-sm text-gray-600">In Progress</span>
                      <span class="text-sm font-semibold text-blue-600"><?= $stats['in_progress_tasks'] ?? 0 ?></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                      <div class="bg-blue-500 h-2 rounded-full" style="width: <?= $stats['in_progress_rate'] ?? 0 ?>%"></div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                      <span class="text-sm text-gray-600">Pending</span>
                      <span class="text-sm font-semibold text-gray-600"><?= $stats['pending_tasks'] ?? 0 ?></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                      <div class="bg-gray-500 h-2 rounded-full" style="width: <?= $stats['pending_rate'] ?? 0 ?>%"></div>
                    </div>
                  </div>
                </div>
                
                <div>
                  <h4 class="text-sm font-medium text-gray-600 mb-3">By Priority</h4>
                  <div class="space-y-3">
                    <div class="flex items-center justify-between">
                      <span class="text-sm text-gray-600">Urgent</span>
                      <span class="text-sm font-semibold text-red-600"><?= $stats['urgent_tasks'] ?? 0 ?></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                      <div class="bg-red-500 h-2 rounded-full" style="width: <?= $stats['urgent_rate'] ?? 0 ?>%"></div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                      <span class="text-sm text-gray-600">High</span>
                      <span class="text-sm font-semibold text-orange-600"><?= $stats['high_tasks'] ?? 0 ?></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                      <div class="bg-orange-500 h-2 rounded-full" style="width: <?= $stats['high_rate'] ?? 0 ?>%"></div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                      <span class="text-sm text-gray-600">Medium</span>
                      <span class="text-sm font-semibold text-blue-600"><?= $stats['medium_tasks'] ?? 0 ?></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                      <div class="bg-blue-500 h-2 rounded-full" style="width: <?= $stats['medium_rate'] ?? 0 ?>%"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Recent Activity -->
            <div class="card-3d rounded-2xl p-6">
              <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-800">Recent Activity</h3>
                <button onclick="loadMoreActivity()" class="text-sm text-blue-600 hover:text-blue-800">View All</button>
              </div>
              <div id="activityList" class="space-y-4">
                <!-- Activity items will be loaded here -->
                <div class="loading-skeleton h-16 rounded-xl"></div>
                <div class="loading-skeleton h-16 rounded-xl"></div>
                <div class="loading-skeleton h-16 rounded-xl"></div>
              </div>
            </div>
          </div>

          <!-- Right Column -->
          <div class="space-y-8">
            
            <!-- Quick Actions -->
            <div class="card-3d rounded-2xl p-6">
              <h3 class="text-xl font-bold text-gray-800 mb-6">Quick Actions</h3>
              <div class="space-y-3">
                <button onclick="window.location.href='/notes'" class="w-full btn-3d p-4 bg-blue-50 hover:bg-blue-100 rounded-xl text-left transition-all duration-200">
                  <div class="flex items-center gap-3">
                    <i class="fas fa-sticky-note text-blue-600"></i>
                    <span class="font-medium text-gray-800">Create New Note</span>
                  </div>
                </button>

                <button onclick="window.location.href='/voice-notes'" class="w-full btn-3d p-4 bg-purple-50 hover:bg-purple-100 rounded-xl text-left transition-all duration-200">
                  <div class="flex items-center gap-3">
                    <i class="fas fa-microphone text-purple-600"></i>
                    <span class="font-medium text-gray-800">Voice Notes</span>
                  </div>
                </button>

                <button onclick="window.location.href='/ocr'" class="w-full btn-3d p-4 bg-orange-50 hover:bg-orange-100 rounded-xl text-left transition-all duration-200">
                  <div class="flex items-center gap-3">
                    <i class="fas fa-eye text-orange-600"></i>
                    <span class="font-medium text-gray-800">OCR</span>
                  </div>
                </button>
                
                <button onclick="window.location.href='/tasks'" class="w-full btn-3d p-4 bg-green-50 hover:bg-green-100 rounded-xl text-left transition-all duration-200">
                  <div class="flex items-center gap-3">
                    <i class="fas fa-plus-circle text-green-600"></i>
                    <span class="font-medium text-gray-800">Add New Task</span>
                  </div>
                </button>
                
                <button onclick="openQuickSearch()" class="w-full btn-3d p-4 bg-purple-50 hover:bg-purple-100 rounded-xl text-left transition-all duration-200">
                  <div class="flex items-center gap-3">
                    <i class="fas fa-search text-purple-600"></i>
                    <span class="font-medium text-gray-800">Quick Search</span>
                  </div>
                </button>
                
                <button onclick="window.location.href='/automation'" class="w-full btn-3d p-4 bg-indigo-50 hover:bg-indigo-100 rounded-xl text-left transition-all duration-200">
                  <div class="flex items-center gap-3">
                    <i class="fas fa-robot text-indigo-600"></i>
                    <span class="font-medium text-gray-800">Automation</span>
                  </div>
                </button>
                
                <button onclick="window.location.href='/integrations'" class="w-full btn-3d p-4 bg-teal-50 hover:bg-teal-100 rounded-xl text-left transition-all duration-200">
                  <div class="flex items-center gap-3">
                    <i class="fas fa-plug text-teal-600"></i>
                    <span class="font-medium text-gray-800">Integrations</span>
                  </div>
                </button>
                
                <button onclick="window.location.href='/analytics'" class="w-full btn-3d p-4 bg-indigo-50 hover:bg-indigo-100 rounded-xl text-left transition-all duration-200">
                  <div class="flex items-center gap-3">
                    <i class="fas fa-chart-line text-indigo-600"></i>
                    <span class="font-medium text-gray-800">Analytics</span>
                  </div>
                </button>
                
                <button onclick="window.location.href='/data-management'" class="w-full btn-3d p-4 bg-purple-50 hover:bg-purple-100 rounded-xl text-left transition-all duration-200">
                  <div class="flex items-center gap-3">
                    <i class="fas fa-database text-purple-600"></i>
                    <span class="font-medium text-gray-800">Data Management</span>
                  </div>
                </button>
                
                <button onclick="window.location.href='/database'" class="w-full btn-3d p-4 bg-indigo-50 hover:bg-indigo-100 rounded-xl text-left transition-all duration-200">
                  <div class="flex items-center gap-3">
                    <i class="fas fa-server text-indigo-600"></i>
                    <span class="font-medium text-gray-800">Database Management</span>
                  </div>
                </button>
                
                <button onclick="openBackupModal()" class="w-full btn-3d p-4 bg-orange-50 hover:bg-orange-100 rounded-xl text-left transition-all duration-200">
                  <div class="flex items-center gap-3">
                    <i class="fas fa-download text-orange-600"></i>
                    <span class="font-medium text-gray-800">Export Data</span>
                  </div>
                </button>
              </div>
            </div>

            <!-- Today's Focus -->
            <div class="card-3d rounded-2xl p-6">
              <h3 class="text-xl font-bold text-gray-800 mb-6">Today's Focus</h3>
              <div id="todaysFocus" class="space-y-4">
                <!-- Focus items will be loaded here -->
                <div class="loading-skeleton h-12 rounded-xl"></div>
                <div class="loading-skeleton h-12 rounded-xl"></div>
                <div class="loading-skeleton h-12 rounded-xl"></div>
              </div>
            </div>

            <!-- Weather Widget -->
            <div class="weather-widget rounded-2xl p-6 text-white">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Weather</h3>
                <i class="fas fa-cloud-sun text-2xl"></i>
              </div>
              <div class="text-center">
                <div class="text-3xl font-bold mb-2" id="temperature">22°C</div>
                <div class="text-sm opacity-90" id="weatherDescription">Partly Cloudy</div>
                <div class="text-xs opacity-75 mt-2" id="location">New York, NY</div>
              </div>
            </div>

            <!-- Motivational Quote -->
            <div class="quote-widget rounded-2xl p-6 text-white">
              <div class="text-center">
                <i class="fas fa-quote-left text-2xl opacity-50 mb-4"></i>
                <p class="text-sm italic mb-4" id="motivationalQuote">
                  "The way to get started is to quit talking and begin doing."
                </p>
                <p class="text-xs opacity-75" id="quoteAuthor">- Walt Disney</p>
              </div>
            </div>

            <!-- Mood Tracker -->
            <div class="mood-widget rounded-2xl p-6 text-white">
              <h3 class="text-lg font-semibold mb-4">How are you feeling?</h3>
              <div class="flex justify-center gap-3">
                <button onclick="setMood('happy')" class="mood-btn p-2 rounded-full bg-white bg-opacity-20 hover:bg-opacity-30 transition-all duration-200">
                  <i class="fas fa-smile text-xl"></i>
                </button>
                <button onclick="setMood('neutral')" class="mood-btn p-2 rounded-full bg-white bg-opacity-20 hover:bg-opacity-30 transition-all duration-200">
                  <i class="fas fa-meh text-xl"></i>
                </button>
                <button onclick="setMood('sad')" class="mood-btn p-2 rounded-full bg-white bg-opacity-20 hover:bg-opacity-30 transition-all duration-200">
                  <i class="fas fa-frown text-xl"></i>
                </button>
                <button onclick="setMood('excited')" class="mood-btn p-2 rounded-full bg-white bg-opacity-20 hover:bg-opacity-30 transition-all duration-200">
                  <i class="fas fa-grin text-xl"></i>
                </button>
              </div>
              <div class="text-center mt-4">
                <p class="text-xs opacity-75" id="moodStatus">Track your daily mood</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Bottom Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          
          <!-- Notes Summary -->
          <div class="card-3d rounded-2xl p-6">
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-xl font-bold text-gray-800">Recent Notes</h3>
              <button onclick="window.location.href='/notes'" class="text-sm text-blue-600 hover:text-blue-800">View All</button>
            </div>
            <div id="recentNotes" class="space-y-4">
              <!-- Recent notes will be loaded here -->
              <div class="loading-skeleton h-16 rounded-xl"></div>
              <div class="loading-skeleton h-16 rounded-xl"></div>
              <div class="loading-skeleton h-16 rounded-xl"></div>
            </div>
          </div>

          <!-- Upcoming Tasks -->
          <div class="card-3d rounded-2xl p-6">
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-xl font-bold text-gray-800">Upcoming Tasks</h3>
              <button onclick="window.location.href='/tasks'" class="text-sm text-blue-600 hover:text-blue-800">View All</button>
            </div>
            <div id="upcomingTasks" class="space-y-4">
              <!-- Upcoming tasks will be loaded here -->
              <div class="loading-skeleton h-16 rounded-xl"></div>
              <div class="loading-skeleton h-16 rounded-xl"></div>
              <div class="loading-skeleton h-16 rounded-xl"></div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <!-- Quick Search Modal -->
  <div id="quickSearchModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-2xl shadow-2xl w-11/12 max-w-2xl slide-in">
      <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-xl font-bold text-gray-800">Quick Search</h3>
          <button onclick="closeQuickSearch()" class="p-2 text-gray-400 hover:text-gray-600">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>
      
      <div class="p-6">
        <div class="relative mb-4">
          <input type="text" id="quickSearchInput" placeholder="Search notes, tasks, and more..." 
                 class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
          <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
        </div>
        
        <div id="searchResults" class="space-y-2 max-h-64 overflow-y-auto">
          <!-- Search results will appear here -->
        </div>
      </div>
    </div>
  </div>

  <script>
    // Dashboard Loader and Toast System
    function showDashboardLoader(message = 'Loading...') {
      const loader = document.getElementById('dashboardLoader');
      const messageEl = document.getElementById('dashboardLoaderMessage');
      if (messageEl) messageEl.textContent = message;
      if (loader) loader.classList.remove('hidden');
    }

    function hideDashboardLoader() {
      const loader = document.getElementById('dashboardLoader');
      if (loader) loader.classList.add('hidden');
    }

    function showDashboardToast(message, type = 'info') {
      const container = document.getElementById('dashboardToastContainer');
      const toast = document.createElement('div');
      
      const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500',
        info: 'bg-blue-500'
      };
      
      toast.className = `${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300`;
      toast.textContent = message;
      
      container.appendChild(toast);
      
      setTimeout(() => toast.classList.remove('translate-x-full'), 100);
      setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => toast.remove(), 300);
      }, 3000);
    }

    // Global variables
    let productivityChart;
    let currentMood = null;

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
      initializeDashboard();
      updateTime();
      setInterval(updateTime, 1000);
      initializeTheme();
      initializeKeyboardShortcuts();
    });

    function initializeDashboard() {
      loadActivity();
      loadTodaysFocus();
      loadRecentNotes();
      loadUpcomingTasks();
      loadWeather();
      loadMotivationalQuote();
      initializeProductivityChart();
    }

    function updateTime() {
      const now = new Date();
      const timeString = now.toLocaleTimeString('en-US', { 
        hour12: true, 
        hour: 'numeric', 
        minute: '2-digit',
        second: '2-digit'
      });
      const dateString = now.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
      
      document.getElementById('currentTime').textContent = timeString;
      document.getElementById('currentDate').textContent = dateString;
    }

    // Chart functions
    function initializeProductivityChart() {
      const ctx = document.getElementById('productivityChart').getContext('2d');
      
      productivityChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
          datasets: [{
            label: 'Productivity Score',
            data: [65, 78, 82, 75, 88, 92, 85],
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4
          }, {
            label: 'Tasks Completed',
            data: [3, 5, 7, 4, 8, 6, 9],
            borderColor: 'rgb(16, 185, 129)',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'top',
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              max: 100
            }
          }
        }
      });
    }

    function updateChart() {
      const period = document.getElementById('chartPeriod').value;
      // Update chart data based on selected period
      // This would typically fetch new data from the server
      showDashboardToast('Chart updated for ' + period + ' days', 'info');
    }

    // Data loading functions
    function loadActivity() {
      showDashboardLoader('Loading recent activity...');
      
      fetch('/dashboard/api/activity', {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          renderActivity(data.activities);
        }
      })
      .catch(error => {
        console.error('Error loading activity:', error);
        showDashboardToast('Failed to load recent activity', 'error');
        // Show placeholder activity
        renderActivity([
          { type: 'note', action: 'created', title: 'Meeting Notes', time: '2 hours ago' },
          { type: 'task', action: 'completed', title: 'Review project proposal', time: '3 hours ago' },
          { type: 'note', action: 'updated', title: 'Weekly planning', time: '5 hours ago' }
        ]);
      })
      .finally(() => {
        hideDashboardLoader();
      });
    }

    function renderActivity(activities) {
      const container = document.getElementById('activityList');
      container.innerHTML = '';
      
      // Ensure activities is an array
      if (!activities || !Array.isArray(activities)) {
        container.innerHTML = '<p class="text-gray-500 text-center py-4">No recent activity</p>';
        return;
      }
      
      activities.forEach(activity => {
        const item = document.createElement('div');
        item.className = 'activity-item rounded-xl p-4 bg-white shadow-sm hover:shadow-md transition-shadow cursor-pointer';
        item.onclick = () => openActivityModal(activity);
        
        const icon = activity.icon || getActivityIcon(activity.type, activity.action);
        const color = activity.color || getActivityColor(activity.type);
        
        item.innerHTML = `
          <div class="flex items-center gap-3">
            <div class="p-2 rounded-full ${color}">
              <i class="${icon} text-white text-sm"></i>
            </div>
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-800">${activity.title}</p>
              <p class="text-xs text-gray-500">${activity.time}</p>
              ${activity.description ? `<p class="text-xs text-gray-400 mt-1">${activity.description}</p>` : ''}
            </div>
            <i class="fas fa-chevron-right text-gray-400"></i>
          </div>
        `;
        
        container.appendChild(item);
      });
    }

    function getActivityIcon(type, action) {
      const icons = {
        note: {
          created: 'fas fa-plus',
          updated: 'fas fa-edit',
          deleted: 'fas fa-trash'
        },
        task: {
          created: 'fas fa-plus-circle',
          completed: 'fas fa-check-circle',
          updated: 'fas fa-edit'
        }
      };
      return icons[type]?.[action] || 'fas fa-circle';
    }

    function getActivityColor(type) {
      const colors = {
        note: 'bg-blue-500',
        task: 'bg-green-500'
      };
      return colors[type] || 'bg-gray-500';
    }

    function loadTodaysFocus() {
      fetch('/dashboard/api/todays-focus', {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          renderTodaysFocus(data.focusItems || data.focus || []);
        }
      })
      .catch(error => {
        console.error('Error loading focus:', error);
        // Show placeholder focus
        renderTodaysFocus([
          { title: 'Complete project proposal', priority: 'high', completed: false },
          { title: 'Review team feedback', priority: 'medium', completed: true },
          { title: 'Plan next week tasks', priority: 'low', completed: false }
        ]);
      });
    }

    function renderTodaysFocus(focusItems) {
      const container = document.getElementById('todaysFocus');
      container.innerHTML = '';
      
      // Ensure focusItems is an array
      if (!focusItems || !Array.isArray(focusItems)) {
        container.innerHTML = '<p class="text-gray-500 text-center py-4">No focus items for today</p>';
        return;
      }
      
      focusItems.forEach(item => {
        const focusElement = document.createElement('div');
        focusElement.className = 'flex items-center gap-3 p-3 bg-gray-50 rounded-xl';
        
        const priorityColor = {
          high: 'text-red-500',
          medium: 'text-yellow-500',
          low: 'text-green-500'
        }[item.priority] || 'text-gray-500';
        
        focusElement.innerHTML = `
          <input type="checkbox" ${item.completed ? 'checked' : ''} 
                 onchange="toggleFocusItem(this, '${item.id}')" 
                 class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
          <div class="flex-1">
            <p class="text-sm font-medium ${item.completed ? 'line-through text-gray-500' : 'text-gray-800'}">${item.title}</p>
            <p class="text-xs text-gray-500">Priority: <span class="${priorityColor}">${item.priority}</span></p>
          </div>
        `;
        
        container.appendChild(focusElement);
      });
    }

    function loadRecentNotes() {
      fetch('/dashboard/api/recent-notes', {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          renderRecentNotes(data.notes);
        }
      })
      .catch(error => {
        console.error('Error loading recent notes:', error);
      });
    }

    function renderRecentNotes(notes) {
      const container = document.getElementById('recentNotes');
      container.innerHTML = '';
      
      // Ensure notes is an array
      if (!notes || !Array.isArray(notes)) {
        container.innerHTML = '<p class="text-gray-500 text-center py-4">No recent notes</p>';
        return;
      }
      
      notes.forEach(note => {
        const noteElement = document.createElement('div');
        noteElement.className = 'flex items-center gap-3 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors cursor-pointer';
        noteElement.onclick = () => openNoteModal(note);
        
        noteElement.innerHTML = `
          <div class="p-2 bg-blue-100 rounded-lg">
            <i class="fas fa-sticky-note text-blue-600"></i>
          </div>
          <div class="flex-1">
            <p class="text-sm font-medium text-gray-800">${note.title}</p>
            <p class="text-xs text-gray-500">${note.updated_at}</p>
          </div>
          <i class="fas fa-chevron-right text-gray-400"></i>
        `;
        
        container.appendChild(noteElement);
      });
    }

    function loadUpcomingTasks() {
      fetch('/dashboard/api/upcoming-tasks', {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          renderUpcomingTasks(data.tasks);
        }
      })
      .catch(error => {
        console.error('Error loading upcoming tasks:', error);
      });
    }

    function renderUpcomingTasks(tasks) {
      const container = document.getElementById('upcomingTasks');
      container.innerHTML = '';
      
      // Ensure tasks is an array
      if (!tasks || !Array.isArray(tasks)) {
        container.innerHTML = '<p class="text-gray-500 text-center py-4">No upcoming tasks</p>';
        return;
      }
      
      tasks.forEach(task => {
        const taskElement = document.createElement('div');
        taskElement.className = 'flex items-center gap-3 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors cursor-pointer';
        taskElement.onclick = () => openTaskModal(task);
        
        const priorityColor = {
          urgent: 'bg-red-100 text-red-600',
          high: 'bg-orange-100 text-orange-600',
          medium: 'bg-blue-100 text-blue-600',
          low: 'bg-green-100 text-green-600'
        }[task.priority] || 'bg-gray-100 text-gray-600';
        
        // Format due date properly
        let dueDateText = 'No due date';
        if (task.due_date) {
          try {
            const dueDate = new Date(task.due_date);
            if (!isNaN(dueDate.getTime())) {
              dueDateText = dueDate.toLocaleDateString();
            }
          } catch (e) {
            dueDateText = 'Invalid date';
          }
        }
        
        taskElement.innerHTML = `
          <div class="p-2 bg-green-100 rounded-lg">
            <i class="fas fa-tasks text-green-600"></i>
          </div>
          <div class="flex-1">
            <p class="text-sm font-medium text-gray-800">${task.title}</p>
            <div class="flex items-center gap-2 mt-1">
              <span class="text-xs px-2 py-1 rounded-full ${priorityColor}">${task.priority}</span>
              <span class="text-xs text-gray-500">${dueDateText}</span>
            </div>
          </div>
          <i class="fas fa-chevron-right text-gray-400"></i>
        `;
        
        container.appendChild(taskElement);
      });
    }

    // Weather and external data
    function loadWeather() {
      // Use dynamic weather data from PHP
      <?php if (isset($dynamicContent['weather'])): ?>
        const weather = <?php echo json_encode($dynamicContent['weather']); ?>;
        document.getElementById('temperature').textContent = weather.temperature + '°C';
        document.getElementById('weatherDescription').textContent = weather.description;
        document.getElementById('location').textContent = weather.location;
      <?php else: ?>
        // Fallback to default weather
        document.getElementById('temperature').textContent = '22°C';
        document.getElementById('weatherDescription').textContent = 'Partly Cloudy';
        document.getElementById('location').textContent = 'New York, NY';
      <?php endif; ?>
    }

    function loadMotivationalQuote() {
      // Use dynamic quote data from PHP
      <?php if (isset($dynamicContent['quote'])): ?>
        const quote = <?php echo json_encode($dynamicContent['quote']); ?>;
        document.getElementById('motivationalQuote').textContent = `"${quote.text}"`;
        document.getElementById('quoteAuthor').textContent = `- ${quote.author}`;
      <?php else: ?>
        // Fallback to default quotes
        const quotes = [
          { text: "The way to get started is to quit talking and begin doing.", author: "Walt Disney" },
          { text: "Don't be pushed around by the fears in your mind. Be led by the dreams in your heart.", author: "Roy T. Bennett" },
          { text: "Success is not final, failure is not fatal: it is the courage to continue that counts.", author: "Winston Churchill" },
          { text: "The future belongs to those who believe in the beauty of their dreams.", author: "Eleanor Roosevelt" }
        ];
        
        const randomQuote = quotes[Math.floor(Math.random() * quotes.length)];
        document.getElementById('motivationalQuote').textContent = `"${randomQuote.text}"`;
        document.getElementById('quoteAuthor').textContent = `- ${randomQuote.author}`;
      <?php endif; ?>
    }

    // Mood tracking
    function setMood(mood) {
      currentMood = mood;
      
      // Update UI
      document.querySelectorAll('.mood-btn').forEach(btn => {
        btn.classList.remove('bg-opacity-40');
        btn.classList.add('bg-opacity-20');
      });
      event.target.closest('.mood-btn').classList.remove('bg-opacity-20');
      event.target.closest('.mood-btn').classList.add('bg-opacity-40');
      
      // Update status
      const statusText = {
        happy: 'Feeling great! Keep up the positive energy!',
        neutral: 'Feeling balanced. A good day for steady progress.',
        sad: 'It\'s okay to have tough days. Take care of yourself.',
        excited: 'Full of energy! Perfect time to tackle big projects!'
      };
      
      document.getElementById('moodStatus').textContent = statusText[mood];
      
      // Save mood (would typically send to server)
      fetch('/dashboard/api/set-mood', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          mood: mood,
          csrf_token: document.querySelector('input[name="csrf_token"]').value
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showToast('Mood saved successfully!', 'success');
        }
      })
      .catch(error => {
        console.error('Error saving mood:', error);
      });
    }

    // Quick search
    function openQuickSearch() {
      showModal('quickSearchModal');
      setTimeout(() => {
        document.getElementById('quickSearchInput').focus();
      }, 100);
    }

    function closeQuickSearch() {
      hideModal('quickSearchModal');
    }

    // Utility functions
    function showModal(modalId) {
      const modal = document.getElementById(modalId);
      modal.classList.remove('hidden');
    }

    function hideModal(modalId) {
      const modal = document.getElementById(modalId);
      modal.classList.add('hidden');
    }

    // Theme Management
    function initializeTheme() {
      const savedTheme = localStorage.getItem('theme') || 'light';
      applyTheme(savedTheme);
    }

    function toggleDarkMode() {
      const currentTheme = localStorage.getItem('theme') || 'light';
      const newTheme = currentTheme === 'light' ? 'dark' : 'light';
      applyTheme(newTheme);
      localStorage.setItem('theme', newTheme);
      
      // Save to server
      fetch('/settings/theme', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          theme: newTheme,
          csrf_token: document.querySelector('input[name="csrf_token"]').value
        })
      });
    }

    function applyTheme(theme) {
      document.documentElement.classList.toggle('dark-mode', theme === 'dark');
    }

    // Keyboard Shortcuts
    function initializeKeyboardShortcuts() {
      document.addEventListener('keydown', function(e) {
        // Global shortcuts
        if (e.ctrlKey || e.metaKey) {
          switch(e.key) {
            case 'n':
              e.preventDefault();
              window.location.href = '/notes/create';
              break;
            case 't':
              e.preventDefault();
              window.location.href = '/tasks/create';
              break;
            case 'k':
              e.preventDefault();
              openQuickSearch();
              break;
            case '/':
              e.preventDefault();
              showShortcuts();
              break;
            case ',':
              e.preventDefault();
              window.location.href = '/settings';
              break;
            case 'h':
              e.preventDefault();
              window.location.href = '/dashboard';
              break;
          }
        }
        
        // Toggle dark mode
        if (e.ctrlKey && e.shiftKey && e.key === 'D') {
          e.preventDefault();
          toggleDarkMode();
        }
        
        // Escape key
        if (e.key === 'Escape') {
          closeQuickSearch();
        }
      });
    }

    function showShortcuts() {
      const shortcuts = {
        'Global Shortcuts': [
          { key: 'Ctrl+N', action: 'Create new note' },
          { key: 'Ctrl+T', action: 'Create new task' },
          { key: 'Ctrl+K', action: 'Open quick search' },
          { key: 'Ctrl+/', action: 'Show keyboard shortcuts' },
          { key: 'Ctrl+,', action: 'Open settings' },
          { key: 'Ctrl+H', action: 'Go to dashboard' },
          { key: 'Ctrl+Shift+D', action: 'Toggle dark mode' },
          { key: 'Escape', action: 'Close modal/dialog' }
        ]
      };
      
      let html = '<div class="keyboard-shortcuts-modal">';
      html += '<h3 class="text-xl font-bold mb-4">Keyboard Shortcuts</h3>';
      
      for (const [category, shortcuts] of Object.entries(shortcuts)) {
        html += `<div class="mb-4"><h4 class="font-semibold mb-2">${category}</h4>`;
        shortcuts.forEach(shortcut => {
          html += `<div class="flex justify-between items-center py-1">`;
          html += `<kbd class="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded text-sm">${shortcut.key}</kbd>`;
          html += `<span class="text-sm">${shortcut.action}</span>`;
          html += `</div>`;
        });
        html += '</div>';
      }
      
      html += '</div>';
      
      showModal('shortcutsModal', html);
    }

    // Make functions globally available
    window.toggleDarkMode = toggleDarkMode;
    window.showShortcuts = showShortcuts;

    // Toast function uses local dashboard system
    function showToast(message, type = 'info') {
      showDashboardToast(message, type);
    }

    function toggleFocusItem(checkbox, itemId) {
      const completed = checkbox.checked;
      
      fetch('/dashboard/api/toggle-focus', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          item_id: itemId,
          completed: completed,
          csrf_token: document.querySelector('input[name="csrf_token"]').value
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showToast(completed ? 'Focus item completed!' : 'Focus item unchecked', 'success');
        }
      })
      .catch(error => {
        console.error('Error updating focus item:', error);
        checkbox.checked = !completed; // Revert on error
      });
    }

    function loadMoreActivity() {
      showToast('Loading more activity...', 'info');
      // This would load additional activity items
    }

    function openBackupModal() {
      showToast('Backup feature coming soon!', 'info');
      // This would open a backup/export modal
    }

    // Note Modal Functions
    function openNoteModal(note) {
      const modal = document.getElementById('noteModal');
      const modalTitle = document.getElementById('noteModalTitle');
      const modalContent = document.getElementById('noteModalContent');
      const openInAppBtn = document.getElementById('openNoteInAppBtn');
      
      // Set modal title
      modalTitle.textContent = note.title;
      
      // Set modal content
      const content = `
        <div class="space-y-4">
          <div class="flex items-center gap-4">
            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">Note</span>
            <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm">${note.priority || 'Medium'} Priority</span>
            ${note.is_pinned ? '<span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm">Pinned</span>' : ''}
          </div>
          <div>
            <h4 class="font-semibold text-gray-800 mb-2">Content:</h4>
            <div class="bg-gray-50 p-4 rounded-lg">
              <p class="text-gray-700 whitespace-pre-wrap">${note.content || 'No content available'}</p>
            </div>
          </div>
          ${note.tags && note.tags.length > 0 ? `
            <div>
              <h4 class="font-semibold text-gray-800 mb-2">Tags:</h4>
              <div class="flex flex-wrap gap-2">
                ${note.tags.map(tag => `<span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-sm">${tag}</span>`).join('')}
              </div>
            </div>
          ` : ''}
          <div class="text-sm text-gray-500">
            Created: ${new Date(note.created_at).toLocaleString()}
            ${note.updated_at ? `<br>Updated: ${new Date(note.updated_at).toLocaleString()}` : ''}
          </div>
        </div>
      `;
      
      modalContent.innerHTML = content;
      
      // Set up the "Open in Notes" button
      openInAppBtn.onclick = () => openNoteInApp(note);
      
      // Show modal
      modal.classList.remove('hidden');
    }
    
    function closeNoteModal() {
      const modal = document.getElementById('noteModal');
      modal.classList.add('hidden');
    }
    
    function openNoteInApp(note) {
      closeNoteModal();
      window.location.href = '/notes';
    }

    // Task Modal Functions
    function openTaskModal(task) {
      const modal = document.getElementById('taskModal');
      const modalTitle = document.getElementById('taskModalTitle');
      const modalContent = document.getElementById('taskModalContent');
      const openInAppBtn = document.getElementById('openTaskInAppBtn');
      
      // Set modal title
      modalTitle.textContent = task.title;
      
      // Format due date properly
      let dueDateText = 'No due date';
      if (task.due_date) {
        try {
          const dueDate = new Date(task.due_date);
          if (!isNaN(dueDate.getTime())) {
            dueDateText = dueDate.toLocaleString();
          }
        } catch (e) {
          dueDateText = 'Invalid date';
        }
      }
      
      // Set modal content
      const content = `
        <div class="space-y-4">
          <div class="flex items-center gap-4">
            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">Task</span>
            <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm">${task.status || 'Pending'}</span>
            <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm">${task.priority || 'Medium'} Priority</span>
          </div>
          <div>
            <h4 class="font-semibold text-gray-800 mb-2">Description:</h4>
            <div class="bg-gray-50 p-4 rounded-lg">
              <p class="text-gray-700 whitespace-pre-wrap">${task.description || 'No description available'}</p>
            </div>
          </div>
          ${task.due_date ? `
            <div>
              <h4 class="font-semibold text-gray-800 mb-2">Due Date:</h4>
              <p class="text-gray-700">${dueDateText}</p>
            </div>
          ` : ''}
          ${task.category ? `
            <div>
              <h4 class="font-semibold text-gray-800 mb-2">Category:</h4>
              <p class="text-gray-700">${task.category}</p>
            </div>
          ` : ''}
          ${task.tags && task.tags.length > 0 ? `
            <div>
              <h4 class="font-semibold text-gray-800 mb-2">Tags:</h4>
              <div class="flex flex-wrap gap-2">
                ${task.tags.map(tag => `<span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-sm">${tag}</span>`).join('')}
              </div>
            </div>
          ` : ''}
          <div class="text-sm text-gray-500">
            Created: ${new Date(task.created_at).toLocaleString()}
            ${task.updated_at ? `<br>Updated: ${new Date(task.updated_at).toLocaleString()}` : ''}
          </div>
        </div>
      `;
      
      modalContent.innerHTML = content;
      
      // Set up the "Open in Tasks" button
      openInAppBtn.onclick = () => openTaskInApp(task);
      
      // Show modal
      modal.classList.remove('hidden');
    }
    
    function closeTaskModal() {
      const modal = document.getElementById('taskModal');
      modal.classList.add('hidden');
    }
    
    function openTaskInApp(task) {
      closeTaskModal();
      window.location.href = '/tasks';
    }

    // Activity Modal Functions
    function openActivityModal(activity) {
      const modal = document.getElementById('activityModal');
      const modalTitle = document.getElementById('activityModalTitle');
      const modalContent = document.getElementById('activityModalContent');
      
      // Set modal title
      modalTitle.textContent = 'Activity Details';
      
      // Set modal content
      const content = `
        <div class="space-y-4">
          <div class="flex items-center gap-4">
            <div class="p-3 rounded-full ${activity.color || 'bg-gray-500'}">
              <i class="${activity.icon || 'fas fa-circle'} text-white text-lg"></i>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-800">${activity.title}</h3>
              <p class="text-sm text-gray-500">${activity.time}</p>
            </div>
          </div>
          
          <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="font-semibold text-gray-800 mb-2">Activity Information:</h4>
            <div class="space-y-2 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-600">Type:</span>
                <span class="text-gray-800 capitalize">${activity.type || 'Unknown'}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Action:</span>
                <span class="text-gray-800 capitalize">${activity.action || 'Unknown'}</span>
              </div>
              ${activity.description ? `
                <div class="flex justify-between">
                  <span class="text-gray-600">Description:</span>
                  <span class="text-gray-800">${activity.description}</span>
                </div>
              ` : ''}
            </div>
          </div>
          
          <div class="text-sm text-gray-500">
            Activity ID: ${activity.id}
          </div>
        </div>
      `;
      
      modalContent.innerHTML = content;
      
      // Show modal
      modal.classList.remove('hidden');
    }
    
    function closeActivityModal() {
      const modal = document.getElementById('activityModal');
      modal.classList.add('hidden');
    }

    // Close modal when clicking outside
    document.addEventListener('click', function(event) {
      const noteModal = document.getElementById('noteModal');
      const taskModal = document.getElementById('taskModal');
      const activityModal = document.getElementById('activityModal');
      if (event.target === noteModal) {
        closeNoteModal();
      }
      if (event.target === taskModal) {
        closeTaskModal();
      }
      if (event.target === activityModal) {
        closeActivityModal();
      }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        closeNoteModal();
        closeTaskModal();
        closeActivityModal();
      }
    });
  </script>

  <!-- Note Detail Modal -->
  <div id="noteModal" class="fixed inset-0 bg-black bg-opacity-50 modal-backdrop flex items-center justify-center hidden z-50">
    <div class="modal-content rounded-2xl shadow-2xl w-11/12 max-w-4xl max-h-[90vh] overflow-hidden">
      <div class="p-6">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-2xl font-semibold text-gray-800" id="noteModalTitle">Note Details</h3>
          <button onclick="closeNoteModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
            <i class="fas fa-times text-2xl"></i>
          </button>
        </div>
        
        <div class="overflow-y-auto max-h-[70vh]">
          <div id="noteModalContent" class="space-y-4">
            <!-- Note content will be populated here -->
          </div>
        </div>
        
        <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200">
          <button onclick="closeNoteModal()" class="px-6 py-3 bg-gray-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
            <i class="fas fa-times mr-2"></i>Close
          </button>
          <button onclick="openNoteInApp()" id="openNoteInAppBtn" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
            <i class="fas fa-external-link-alt mr-2"></i>Open in Notes
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Task Detail Modal -->
  <div id="taskModal" class="fixed inset-0 bg-black bg-opacity-50 modal-backdrop flex items-center justify-center hidden z-50">
    <div class="modal-content rounded-2xl shadow-2xl w-11/12 max-w-4xl max-h-[90vh] overflow-hidden">
      <div class="p-6">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-2xl font-semibold text-gray-800" id="taskModalTitle">Task Details</h3>
          <button onclick="closeTaskModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
            <i class="fas fa-times text-2xl"></i>
          </button>
        </div>
        
        <div class="overflow-y-auto max-h-[70vh]">
          <div id="taskModalContent" class="space-y-4">
            <!-- Task content will be populated here -->
          </div>
        </div>
        
        <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200">
          <button onclick="closeTaskModal()" class="px-6 py-3 bg-gray-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
            <i class="fas fa-times mr-2"></i>Close
          </button>
          <button onclick="openTaskInApp()" id="openTaskInAppBtn" class="px-6 py-3 bg-gradient-to-r from-green-600 to-blue-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
            <i class="fas fa-external-link-alt mr-2"></i>Open in Tasks
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Activity Detail Modal -->
  <div id="activityModal" class="fixed inset-0 bg-black bg-opacity-50 modal-backdrop flex items-center justify-center hidden z-50">
    <div class="modal-content rounded-2xl shadow-2xl w-11/12 max-w-2xl max-h-[90vh] overflow-hidden">
      <div class="p-6">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-2xl font-semibold text-gray-800" id="activityModalTitle">Activity Details</h3>
          <button onclick="closeActivityModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
            <i class="fas fa-times text-2xl"></i>
          </button>
        </div>
        
        <div class="overflow-y-auto max-h-[70vh]">
          <div id="activityModalContent" class="space-y-4">
            <!-- Activity content will be populated here -->
          </div>
        </div>
        
        <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200">
          <button onclick="closeActivityModal()" class="px-6 py-3 bg-gray-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
            <i class="fas fa-times mr-2"></i>Close
          </button>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
