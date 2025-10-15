<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Audit Logs | SecureNote Pro</title>
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
            'shimmer': 'shimmer 2s linear infinite',
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
    
    .audit-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      transform-style: preserve-3d;
    }
    
    .audit-card:hover {
      transform: translateY(-4px) rotateX(2deg);
      box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.15);
    }
    
    .status-indicator {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      display: inline-block;
      margin-right: 8px;
    }
    
    .status-success {
      background: #10b981;
      box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
    }
    
    .status-warning {
      background: #f59e0b;
      box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2);
    }
    
    .status-error {
      background: #ef4444;
      box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2);
    }
    
    .status-info {
      background: #3b82f6;
      box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
    }
    
    .modal-backdrop {
      backdrop-filter: blur(8px);
    }
    
    .modal-content {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.3);
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
    
    .loading-spinner {
      border: 3px solid rgba(59, 130, 246, 0.3);
      border-top: 3px solid #3b82f6;
      border-radius: 50%;
      width: 20px;
      height: 20px;
      animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
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
    
    .pulse-glow {
      animation: pulse-glow 2s ease-in-out infinite alternate;
    }
    
    @keyframes pulse-glow {
      from { box-shadow: 0 0 20px rgba(59, 130, 246, 0.4); }
      to { box-shadow: 0 0 30px rgba(59, 130, 246, 0.8); }
    }
  </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 min-h-screen">
  
  <!-- Toast Notifications -->
  <div id="toast-container"></div>
  
  <!-- CSRF Token -->
  <input type="hidden" name="csrf_token" value="<?= \Core\CSRF::generate() ?>">

  <!-- Main Container -->
  <div class="flex h-screen">
    <!-- Sidebar -->
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Header -->
      <?php 
        $page_title = "Audit Logs";
        include __DIR__ . '/partials/navbar.php'; 
      ?>

      <!-- Content -->
      <main class="flex-1 overflow-y-auto p-6">
        
        <!-- Quick Actions Header -->
        <div class="flex items-center justify-between mb-8">
          <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Audit Logs</h1>
            <p class="text-gray-600">Monitor system activity and security events</p>
          </div>
          <div class="flex items-center gap-3">
            <button onclick="exportLogs()" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
              <i class="fas fa-download mr-2"></i>Export
            </button>
            <button onclick="refreshLogs()" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
              <i class="fas fa-refresh mr-2"></i>Refresh
            </button>
          </div>
        </div>

        <!-- Audit Analytics Dashboard -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <div class="audit-card rounded-xl p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Total Events</p>
                <p class="text-2xl font-bold text-gray-900">1,247</p>
              </div>
              <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-list-alt text-blue-600 text-xl"></i>
              </div>
            </div>
          </div>
          
          <div class="audit-card rounded-xl p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Security Events</p>
                <p class="text-2xl font-bold text-red-600">23</p>
              </div>
              <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-shield-alt text-red-600 text-xl"></i>
              </div>
            </div>
          </div>
          
          <div class="audit-card rounded-xl p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Today's Activity</p>
                <p class="text-2xl font-bold text-gray-900">89</p>
              </div>
              <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-calendar-day text-green-600 text-xl"></i>
              </div>
            </div>
          </div>
          
          <div class="audit-card rounded-xl p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Unique IPs</p>
                <p class="text-2xl font-bold text-gray-900">12</p>
              </div>
              <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-globe text-purple-600 text-xl"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Activity Timeline Chart -->
        <div class="glassmorphism rounded-2xl p-6 mb-8">
          <h3 class="text-xl font-semibold text-gray-800 mb-6">Activity Timeline (Last 7 Days)</h3>
          <div class="bg-white rounded-xl p-4 h-64 flex items-center justify-center">
            <canvas id="activityChart" width="400" height="200"></canvas>
          </div>
        </div>

        <!-- Advanced Search and Filters -->
        <div class="glassmorphism rounded-2xl p-6 mb-8">
          <h3 class="text-xl font-semibold text-gray-800 mb-6">Search and Filter</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
              <input type="text" id="auditSearch" placeholder="Search logs..." 
                     class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Action Type</label>
              <select id="actionFilter" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Actions</option>
                <option value="login">Login</option>
                <option value="logout">Logout</option>
                <option value="create">Create</option>
                <option value="update">Update</option>
                <option value="delete">Delete</option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Resource Type</label>
              <select id="resourceFilter" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Resources</option>
                <option value="note">Notes</option>
                <option value="task">Tasks</option>
                <option value="user">Users</option>
                <option value="settings">Settings</option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Time Range</label>
              <select id="timeFilter" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Time</option>
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
              </select>
            </div>
          </div>
          
          <!-- Quick Filter Buttons -->
          <div class="flex flex-wrap gap-2 mb-4">
            <button onclick="filterByAction('login')" class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors">
              <i class="fas fa-sign-in-alt mr-2"></i>Login
            </button>
            <button onclick="filterByAction('logout')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
              <i class="fas fa-sign-out-alt mr-2"></i>Logout
            </button>
            <button onclick="filterByAction('create')" class="px-4 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors">
              <i class="fas fa-plus mr-2"></i>Create
            </button>
            <button onclick="filterByAction('delete')" class="px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors">
              <i class="fas fa-trash mr-2"></i>Delete
            </button>
            <button onclick="clearFilters()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
              <i class="fas fa-times mr-2"></i>Clear
            </button>
          </div>
        </div>

        <!-- Audit Logs List -->
        <div class="glassmorphism rounded-2xl p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-gray-800">Recent Activity</h3>
            <div class="flex items-center gap-4">
              <select id="pageSize" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="10">10 per page</option>
                <option value="25" selected>25 per page</option>
                <option value="50">50 per page</option>
                <option value="100">100 per page</option>
              </select>
            </div>
          </div>
          
          <div id="auditLogsList" class="space-y-4">
            <!-- Sample audit log entries -->
            <div class="audit-card rounded-xl p-4 cursor-pointer" onclick="viewLogDetails(1)">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                  <div class="status-indicator status-success"></div>
                  <div>
                    <h4 class="font-semibold text-gray-800">User Login</h4>
                    <p class="text-sm text-gray-600">User logged in successfully from IP 192.168.1.100</p>
                    <p class="text-xs text-gray-500 mt-1">2 minutes ago • ID: #12345</p>
                  </div>
                </div>
                <div class="text-right">
                  <span class="text-sm text-gray-500">Login</span>
                  <p class="text-xs text-gray-400">192.168.1.100</p>
                </div>
              </div>
            </div>
            
            <div class="audit-card rounded-xl p-4 cursor-pointer" onclick="viewLogDetails(2)">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                  <div class="status-indicator status-info"></div>
                  <div>
                    <h4 class="font-semibold text-gray-800">Note Created</h4>
                    <p class="text-sm text-gray-600">New note "Meeting Notes" was created</p>
                    <p class="text-xs text-gray-500 mt-1">15 minutes ago • ID: #12344</p>
                  </div>
                </div>
                <div class="text-right">
                  <span class="text-sm text-gray-500">Create</span>
                  <p class="text-xs text-gray-400">Note</p>
                </div>
              </div>
            </div>
            
            <div class="audit-card rounded-xl p-4 cursor-pointer" onclick="viewLogDetails(3)">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                  <div class="status-indicator status-warning"></div>
                  <div>
                    <h4 class="font-semibold text-gray-800">Failed Login Attempt</h4>
                    <p class="text-sm text-gray-600">Invalid credentials from IP 192.168.1.200</p>
                    <p class="text-xs text-gray-500 mt-1">1 hour ago • ID: #12343</p>
                  </div>
                </div>
                <div class="text-right">
                  <span class="text-sm text-gray-500">Security</span>
                  <p class="text-xs text-gray-400">192.168.1.200</p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Pagination -->
          <div class="flex items-center justify-between mt-6">
            <div class="text-sm text-gray-500">
              Showing 1-25 of 1,247 entries
            </div>
            <div class="flex items-center gap-2">
              <button onclick="previousPage()" class="px-3 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                <i class="fas fa-chevron-left"></i>
              </button>
              <span class="px-3 py-2 bg-blue-600 text-white rounded-lg">1</span>
              <button onclick="nextPage()" class="px-3 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                <i class="fas fa-chevron-right"></i>
              </button>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <!-- Log Details Modal -->
  <div id="logDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 modal-backdrop flex items-center justify-center hidden z-50">
    <div class="modal-content rounded-2xl shadow-2xl w-11/12 max-w-2xl slide-in">
      <div class="p-6">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-xl font-semibold text-gray-800">Log Details</h3>
          <button onclick="closeLogDetails()" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>
        
        <div id="logDetailsContent">
          <!-- Log details will be populated here -->
        </div>
      </div>
    </div>
  </div>

  <script>
    // Global variables
    let currentPage = 1;
    let pageSize = 25;
    let allLogs = [];

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
      initializeAuditLogs();
      setupEventListeners();
      initializeActivityChart();
    });

    function setupEventListeners() {
      // Search input
      document.getElementById('auditSearch').addEventListener('input', debounce(handleSearch, 300));
      
      // Filter dropdowns
      document.getElementById('actionFilter').addEventListener('change', applyFilters);
      document.getElementById('resourceFilter').addEventListener('change', applyFilters);
      document.getElementById('timeFilter').addEventListener('change', applyFilters);
      document.getElementById('pageSize').addEventListener('change', handlePageSizeChange);
    }

    function initializeAuditLogs() {
      // Simulate loading audit logs
      allLogs = generateSampleLogs();
      updateLogsDisplay();
    }

    function generateSampleLogs() {
      const actions = ['login', 'logout', 'create', 'update', 'delete'];
      const resources = ['note', 'task', 'user', 'settings'];
      const logs = [];
      
      for (let i = 0; i < 50; i++) {
        logs.push({
          id: 12345 - i,
          action: actions[Math.floor(Math.random() * actions.length)],
          resource: resources[Math.floor(Math.random() * resources.length)],
          description: `Sample log entry ${i + 1}`,
          ip: `192.168.1.${Math.floor(Math.random() * 255)}`,
          timestamp: new Date(Date.now() - Math.random() * 7 * 24 * 60 * 60 * 1000),
          status: Math.random() > 0.8 ? 'warning' : 'success'
        });
      }
      
      return logs;
    }

    function updateLogsDisplay() {
      const container = document.getElementById('auditLogsList');
      const startIndex = (currentPage - 1) * pageSize;
      const endIndex = startIndex + pageSize;
      const pageLogs = allLogs.slice(startIndex, endIndex);
      
      container.innerHTML = pageLogs.map(log => createLogCard(log)).join('');
    }

    function createLogCard(log) {
      const statusClass = getStatusClass(log.status);
      const timeAgo = getTimeAgo(log.timestamp);
      
      return `
        <div class="audit-card rounded-xl p-4 cursor-pointer" onclick="viewLogDetails(${log.id})">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
              <div class="status-indicator ${statusClass}"></div>
              <div>
                <h4 class="font-semibold text-gray-800">${log.action.charAt(0).toUpperCase() + log.action.slice(1)} ${log.resource}</h4>
                <p class="text-sm text-gray-600">${log.description}</p>
                <p class="text-xs text-gray-500 mt-1">${timeAgo} • ID: #${log.id}</p>
              </div>
            </div>
            <div class="text-right">
              <span class="text-sm text-gray-500">${log.action}</span>
              <p class="text-xs text-gray-400">${log.ip}</p>
            </div>
          </div>
        </div>
      `;
    }

    function getStatusClass(status) {
      const classes = {
        'success': 'status-success',
        'warning': 'status-warning',
        'error': 'status-error',
        'info': 'status-info'
      };
      return classes[status] || 'status-info';
    }

    function getTimeAgo(timestamp) {
      const now = new Date();
      const diff = now - timestamp;
      const minutes = Math.floor(diff / 60000);
      const hours = Math.floor(diff / 3600000);
      const days = Math.floor(diff / 86400000);
      
      if (minutes < 60) return `${minutes} minutes ago`;
      if (hours < 24) return `${hours} hours ago`;
      return `${days} days ago`;
    }

    function viewLogDetails(logId) {
      const log = allLogs.find(l => l.id === logId);
      if (!log) return;
      
      const content = document.getElementById('logDetailsContent');
      content.innerHTML = `
        <div class="space-y-4">
          <div>
            <h4 class="font-semibold text-gray-800 mb-2">Basic Information</h4>
            <div class="bg-gray-50 rounded-lg p-4 space-y-2">
              <div class="flex justify-between">
                <span class="text-gray-600">Action:</span>
                <span class="font-medium">${log.action}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Resource:</span>
                <span class="font-medium">${log.resource}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Timestamp:</span>
                <span class="font-medium">${log.timestamp.toLocaleString()}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Status:</span>
                <span class="font-medium">${log.status}</span>
              </div>
            </div>
          </div>
          
          <div>
            <h4 class="font-semibold text-gray-800 mb-2">Technical Details</h4>
            <div class="bg-gray-50 rounded-lg p-4 space-y-2">
              <div class="flex justify-between">
                <span class="text-gray-600">IP Address:</span>
                <span class="font-medium">${log.ip}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">User Agent:</span>
                <span class="font-medium">Mozilla/5.0 (Windows NT 10.0; Win64; x64)</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Log ID:</span>
                <span class="font-medium">#${log.id}</span>
              </div>
            </div>
          </div>
          
          <div>
            <h4 class="font-semibold text-gray-800 mb-2">Description</h4>
            <div class="bg-gray-50 rounded-lg p-4">
              <p class="text-gray-700">${log.description}</p>
            </div>
          </div>
        </div>
      `;
      
      document.getElementById('logDetailsModal').classList.remove('hidden');
    }

    function closeLogDetails() {
      document.getElementById('logDetailsModal').classList.add('hidden');
    }

    function handleSearch(event) {
      const query = event.target.value.toLowerCase();
      // Implement search logic here
      console.log('Searching for:', query);
    }

    function applyFilters() {
      // Implement filter logic here
      console.log('Applying filters');
    }

    function filterByAction(action) {
      document.getElementById('actionFilter').value = action;
      applyFilters();
    }

    function clearFilters() {
      document.getElementById('auditSearch').value = '';
      document.getElementById('actionFilter').value = '';
      document.getElementById('resourceFilter').value = '';
      document.getElementById('timeFilter').value = '';
      applyFilters();
    }

    function handlePageSizeChange(event) {
      pageSize = parseInt(event.target.value);
      currentPage = 1;
      updateLogsDisplay();
    }

    function previousPage() {
      if (currentPage > 1) {
        currentPage--;
        updateLogsDisplay();
      }
    }

    function nextPage() {
      const totalPages = Math.ceil(allLogs.length / pageSize);
      if (currentPage < totalPages) {
        currentPage++;
        updateLogsDisplay();
      }
    }

    function exportLogs() {
      showToast('Exporting audit logs...', 'info');
      // Implement export logic here
    }

    function refreshLogs() {
      showToast('Refreshing audit logs...', 'info');
      initializeAuditLogs();
    }

    function initializeActivityChart() {
      const ctx = document.getElementById('activityChart');
      if (!ctx) return;
      
      const data = {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [{
          label: 'Events',
          data: [12, 19, 3, 5, 2, 3, 8],
          backgroundColor: 'rgba(59, 130, 246, 0.5)',
          borderColor: 'rgba(59, 130, 246, 1)',
          borderWidth: 2
        }]
      };
      
      const config = {
        type: 'line',
        data: data,
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      };
      
      new Chart(ctx, config);
    }

    function showToast(message, type = 'info') {
      const toast = document.createElement('div');
      toast.className = `toast ${type}`;
      toast.innerHTML = `
        <div class="flex items-center">
          <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} mr-2"></i>
          ${message}
        </div>
      `;
      
      document.getElementById('toast-container').appendChild(toast);
      
      setTimeout(() => toast.classList.add('show'), 100);
      
      setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
      }, 3000);
    }

    function debounce(func, wait) {
      let timeout;
      return function executedFunction(...args) {
        const later = () => {
          clearTimeout(timeout);
          func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
      };
    }
  </script>
</body>
</html>