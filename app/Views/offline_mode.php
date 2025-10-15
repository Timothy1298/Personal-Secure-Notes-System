<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Offline Mode | SecureNote Pro</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    // Suppress Tailwind production warning for development
    tailwind.config = {
      theme: {
        extend: {}
      }
    }
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    
    * {
      font-family: 'Inter', sans-serif;
    }
    
    .glassmorphism {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .offline-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      transform-style: preserve-3d;
    }
    
    .offline-card:hover {
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
    
    .status-online {
      background: #10b981;
      box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
    }
    
    .status-offline {
      background: #ef4444;
      box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2);
    }
    
    .status-syncing {
      background: #f59e0b;
      box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2);
      animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.5; }
    }
    
    .progress-bar {
      background: linear-gradient(90deg, #3b82f6 0%, #1d4ed8 100%);
      border-radius: 9999px;
      transition: width 0.3s ease;
      height: 0.5rem;
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
    
    .toast.warning {
      background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }
    
    .sync-item {
      background: rgba(255, 255, 255, 0.8);
      backdrop-filter: blur(5px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      transition: all 0.2s ease;
    }
    
    .sync-item:hover {
      background: rgba(59, 130, 246, 0.05);
      transform: translateX(2px);
    }
    
    .offline-banner {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 1000;
      background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
      color: white;
      padding: 0.75rem 1rem;
      text-align: center;
      transform: translateY(-100%);
      transition: transform 0.3s ease;
    }
    
    .offline-banner.show {
      transform: translateY(0);
    }
    
    .connection-indicator {
      position: fixed;
      bottom: 1rem;
      right: 1rem;
      z-index: 1000;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      border-radius: 0.75rem;
      padding: 0.75rem 1rem;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }
    
    .connection-indicator.offline {
      background: rgba(239, 68, 68, 0.95);
      color: white;
    }
    
    .connection-indicator.syncing {
      background: rgba(245, 158, 11, 0.95);
      color: white;
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
  
  <!-- Offline Banner -->
  <div id="offlineBanner" class="offline-banner">
    <div class="flex items-center justify-center gap-2">
      <i class="fas fa-wifi-slash"></i>
      <span class="font-medium">You're currently offline. Changes will be synced when connection is restored.</span>
    </div>
  </div>

  <!-- Connection Indicator -->
  <div id="connectionIndicator" class="connection-indicator">
    <div class="flex items-center gap-2">
      <div class="status-indicator status-online"></div>
      <span class="text-sm font-medium">Online</span>
    </div>
  </div>

  <!-- Toast Notifications -->
  <div id="toast-container"></div>

  <!-- Main Container -->
  <div class="flex h-screen">
    <!-- Sidebar -->
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Header -->
      <?php 
        $page_title = "Offline Mode";
        include __DIR__ . '/partials/navbar.php'; 
      ?>

      <!-- Content -->
      <main class="flex-1 overflow-y-auto p-6">
        
        <!-- Header Section -->
        <div class="flex items-center justify-between mb-8">
          <div>
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Offline Mode</h1>
            <p class="text-gray-600">Work seamlessly even without internet connection</p>
          </div>
          <div class="flex items-center gap-3">
            <button onclick="toggleOfflineMode()" id="offlineToggle" class="px-6 py-3 bg-gradient-to-r from-green-600 to-blue-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
              <i class="fas fa-wifi mr-2"></i>Enable Offline Mode
            </button>
          </div>
        </div>

        <!-- Connection Status -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <div class="offline-card rounded-xl p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Connection Status</p>
                <p class="text-2xl font-bold text-gray-900" id="connectionStatus">Online</p>
              </div>
              <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-wifi text-green-600 text-xl"></i>
              </div>
            </div>
          </div>
          
          <div class="offline-card rounded-xl p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Offline Data</p>
                <p class="text-2xl font-bold text-gray-900" id="offlineDataSize">0 MB</p>
              </div>
              <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-database text-blue-600 text-xl"></i>
              </div>
            </div>
          </div>
          
          <div class="offline-card rounded-xl p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Pending Sync</p>
                <p class="text-2xl font-bold text-gray-900" id="pendingSyncCount">0</p>
              </div>
              <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-sync text-yellow-600 text-xl"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Offline Settings -->
        <div class="glassmorphism rounded-2xl p-6 mb-8">
          <h3 class="text-xl font-semibold text-gray-800 mb-6">Offline Settings</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h4 class="text-lg font-semibold text-gray-800 mb-4">Data to Cache</h4>
              <div class="space-y-3">
                <label class="flex items-center">
                  <input type="checkbox" id="cacheNotes" checked class="mr-3">
                  <span class="text-sm text-gray-700">Notes and attachments</span>
                </label>
                <label class="flex items-center">
                  <input type="checkbox" id="cacheTasks" checked class="mr-3">
                  <span class="text-sm text-gray-700">Tasks and subtasks</span>
                </label>
                <label class="flex items-center">
                  <input type="checkbox" id="cacheTags" checked class="mr-3">
                  <span class="text-sm text-gray-700">Tags and categories</span>
                </label>
                <label class="flex items-center">
                  <input type="checkbox" id="cacheSettings" class="mr-3">
                  <span class="text-sm text-gray-700">User settings</span>
                </label>
              </div>
            </div>
            
            <div>
              <h4 class="text-lg font-semibold text-gray-800 mb-4">Sync Options</h4>
              <div class="space-y-3">
                <label class="flex items-center">
                  <input type="checkbox" id="autoSync" checked class="mr-3">
                  <span class="text-sm text-gray-700">Auto-sync when online</span>
                </label>
                <label class="flex items-center">
                  <input type="checkbox" id="syncOnStartup" checked class="mr-3">
                  <span class="text-sm text-gray-700">Sync on app startup</span>
                </label>
                <label class="flex items-center">
                  <input type="checkbox" id="conflictResolution" class="mr-3">
                  <span class="text-sm text-gray-700">Auto-resolve conflicts</span>
                </label>
                <label class="flex items-center">
                  <input type="checkbox" id="backgroundSync" checked class="mr-3">
                  <span class="text-sm text-gray-700">Background sync</span>
                </label>
              </div>
            </div>
          </div>
          
          <div class="mt-6">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">Storage Management</h4>
            <div class="flex items-center gap-4">
              <div class="flex-1">
                <div class="flex items-center justify-between text-sm text-gray-600 mb-1">
                  <span>Storage Used</span>
                  <span id="storageUsed">0 MB / 50 MB</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div id="storageBar" class="progress-bar h-2 rounded-full" style="width: 0%"></div>
                </div>
              </div>
              <button onclick="clearOfflineData()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                <i class="fas fa-trash mr-2"></i>Clear Data
              </button>
            </div>
          </div>
        </div>

        <!-- Sync Queue -->
        <div class="glassmorphism rounded-2xl p-6 mb-8">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-gray-800">Sync Queue</h3>
            <div class="flex items-center gap-3">
              <button onclick="syncNow()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-sync mr-2"></i>Sync Now
              </button>
              <button onclick="clearSyncQueue()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-trash mr-2"></i>Clear Queue
              </button>
            </div>
          </div>
          
          <div id="syncQueue" class="space-y-3">
            <!-- Sync queue items will be populated here -->
          </div>
        </div>

        <!-- Offline Actions -->
        <div class="glassmorphism rounded-2xl p-6 mb-8">
          <h3 class="text-xl font-semibold text-gray-800 mb-6">Offline Actions</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="offline-card rounded-xl p-6 cursor-pointer" onclick="downloadOfflineData()">
              <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center text-white">
                  <i class="fas fa-download text-xl"></i>
                </div>
                <i class="fas fa-arrow-right text-gray-400"></i>
              </div>
              <h4 class="text-lg font-semibold text-gray-800 mb-2">Download Data</h4>
              <p class="text-sm text-gray-600">Download all your data for offline access</p>
            </div>
            
            <div class="offline-card rounded-xl p-6 cursor-pointer" onclick="uploadOfflineData()">
              <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-teal-600 rounded-lg flex items-center justify-center text-white">
                  <i class="fas fa-upload text-xl"></i>
                </div>
                <i class="fas fa-arrow-right text-gray-400"></i>
              </div>
              <h4 class="text-lg font-semibold text-gray-800 mb-2">Upload Data</h4>
              <p class="text-sm text-gray-600">Upload offline changes to the server</p>
            </div>
            
            <div class="offline-card rounded-xl p-6 cursor-pointer" onclick="checkForUpdates()">
              <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-red-600 rounded-lg flex items-center justify-center text-white">
                  <i class="fas fa-sync-alt text-xl"></i>
                </div>
                <i class="fas fa-arrow-right text-gray-400"></i>
              </div>
              <h4 class="text-lg font-semibold text-gray-800 mb-2">Check Updates</h4>
              <p class="text-sm text-gray-600">Check for updates from the server</p>
            </div>
          </div>
        </div>

        <!-- Conflict Resolution -->
        <div class="glassmorphism rounded-2xl p-6">
          <h3 class="text-xl font-semibold text-gray-800 mb-6">Conflict Resolution</h3>
          
          <div id="conflictList" class="space-y-4">
            <!-- Conflict items will be populated here -->
          </div>
          
          <div id="noConflicts" class="text-center py-8">
            <i class="fas fa-check-circle text-4xl text-green-500 mb-4"></i>
            <p class="text-gray-500">No conflicts found</p>
          </div>
        </div>
      </main>
    </div>
  </div>

  <!-- Sync Progress Modal -->
  <div id="syncModal" class="fixed inset-0 bg-black bg-opacity-50 modal-backdrop flex items-center justify-center hidden z-50">
    <div class="modal-content rounded-2xl shadow-2xl w-11/12 max-w-md slide-in">
      <div class="p-6">
        <div class="text-center">
          <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-sync text-blue-600 text-2xl animate-spin"></i>
          </div>
          <h3 class="text-xl font-semibold text-gray-800 mb-2" id="syncTitle">Syncing...</h3>
          <p class="text-gray-600 mb-6" id="syncDescription">Please wait while we sync your data.</p>
          
          <div class="w-full bg-gray-200 rounded-full h-2 mb-4">
            <div id="syncProgressBar" class="progress-bar h-2 rounded-full" style="width: 0%"></div>
          </div>
          
          <p class="text-sm text-gray-500" id="syncProgressText">0%</p>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Global variables
    let isOfflineMode = false;
    let isOnline = navigator.onLine;
    let syncQueue = [];
    let conflicts = [];
    let offlineData = {};

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
      initializeOfflineMode();
      setupEventListeners();
      loadOfflineData();
      updateConnectionStatus();
    });

    function initializeOfflineMode() {
      // Check if offline mode is enabled
      isOfflineMode = localStorage.getItem('offlineMode') === 'true';
      updateOfflineModeUI();
      
      // Load offline settings
      loadOfflineSettings();
      
      // Initialize service worker for offline functionality
      if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js')
          .then(registration => {
            console.log('Service Worker registered:', registration);
          })
          .catch(error => {
            console.log('Service Worker registration failed:', error);
          });
      }
    }

    function setupEventListeners() {
      // Online/offline event listeners
      window.addEventListener('online', handleOnline);
      window.addEventListener('offline', handleOffline);
      
      // Visibility change (when tab becomes active)
      document.addEventListener('visibilitychange', handleVisibilityChange);
      
      // Before unload (save data before leaving)
      window.addEventListener('beforeunload', saveOfflineData);
      
      // Periodic sync check
      setInterval(checkSyncStatus, 30000); // Check every 30 seconds
    }

    function handleOnline() {
      isOnline = true;
      updateConnectionStatus();
      hideOfflineBanner();
      updateConnectionIndicator('online');
      
      if (isOfflineMode && syncQueue.length > 0) {
        showToast('Connection restored. Syncing changes...', 'info');
        syncNow();
      }
    }

    function handleOffline() {
      isOnline = false;
      updateConnectionStatus();
      showOfflineBanner();
      updateConnectionIndicator('offline');
      showToast('You are now offline. Changes will be synced when connection is restored.', 'warning');
    }

    function handleVisibilityChange() {
      if (!document.hidden && isOnline && isOfflineMode) {
        // Tab became active, check for updates
        checkForUpdates();
      }
    }

    function updateConnectionStatus() {
      const status = isOnline ? 'Online' : 'Offline';
      document.getElementById('connectionStatus').textContent = status;
      
      const icon = document.querySelector('.offline-card .fa-wifi').parentElement;
      if (isOnline) {
        icon.className = 'w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center';
        icon.querySelector('i').className = 'fas fa-wifi text-green-600 text-xl';
      } else {
        icon.className = 'w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center';
        icon.querySelector('i').className = 'fas fa-wifi-slash text-red-600 text-xl';
      }
    }

    function updateConnectionIndicator(status) {
      const indicator = document.getElementById('connectionIndicator');
      const statusElement = indicator.querySelector('.status-indicator');
      const textElement = indicator.querySelector('span');
      
      indicator.className = 'connection-indicator';
      statusElement.className = 'status-indicator';
      
      switch (status) {
        case 'online':
          statusElement.classList.add('status-online');
          textElement.textContent = 'Online';
          break;
        case 'offline':
          indicator.classList.add('offline');
          statusElement.classList.add('status-offline');
          textElement.textContent = 'Offline';
          break;
        case 'syncing':
          indicator.classList.add('syncing');
          statusElement.classList.add('status-syncing');
          textElement.textContent = 'Syncing...';
          break;
      }
    }

    function showOfflineBanner() {
      document.getElementById('offlineBanner').classList.add('show');
    }

    function hideOfflineBanner() {
      document.getElementById('offlineBanner').classList.remove('show');
    }

    function toggleOfflineMode() {
      isOfflineMode = !isOfflineMode;
      localStorage.setItem('offlineMode', isOfflineMode.toString());
      updateOfflineModeUI();
      
      if (isOfflineMode) {
        downloadOfflineData();
        showToast('Offline mode enabled. Downloading data...', 'info');
      } else {
        showToast('Offline mode disabled', 'info');
      }
    }

    function updateOfflineModeUI() {
      const toggle = document.getElementById('offlineToggle');
      if (isOfflineMode) {
        toggle.innerHTML = '<i class="fas fa-wifi-slash mr-2"></i>Disable Offline Mode';
        toggle.className = 'px-6 py-3 bg-gradient-to-r from-red-600 to-orange-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300';
      } else {
        toggle.innerHTML = '<i class="fas fa-wifi mr-2"></i>Enable Offline Mode';
        toggle.className = 'px-6 py-3 bg-gradient-to-r from-green-600 to-blue-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300';
      }
    }

    function loadOfflineSettings() {
      const settings = JSON.parse(localStorage.getItem('offlineSettings') || '{}');
      
      document.getElementById('cacheNotes').checked = settings.cacheNotes !== false;
      document.getElementById('cacheTasks').checked = settings.cacheTasks !== false;
      document.getElementById('cacheTags').checked = settings.cacheTags !== false;
      document.getElementById('cacheSettings').checked = settings.cacheSettings || false;
      document.getElementById('autoSync').checked = settings.autoSync !== false;
      document.getElementById('syncOnStartup').checked = settings.syncOnStartup !== false;
      document.getElementById('conflictResolution').checked = settings.conflictResolution || false;
      document.getElementById('backgroundSync').checked = settings.backgroundSync !== false;
    }

    function saveOfflineSettings() {
      const settings = {
        cacheNotes: document.getElementById('cacheNotes').checked,
        cacheTasks: document.getElementById('cacheTasks').checked,
        cacheTags: document.getElementById('cacheTags').checked,
        cacheSettings: document.getElementById('cacheSettings').checked,
        autoSync: document.getElementById('autoSync').checked,
        syncOnStartup: document.getElementById('syncOnStartup').checked,
        conflictResolution: document.getElementById('conflictResolution').checked,
        backgroundSync: document.getElementById('backgroundSync').checked
      };
      
      localStorage.setItem('offlineSettings', JSON.stringify(settings));
      showToast('Offline settings saved', 'success');
    }

    function loadOfflineData() {
      const data = localStorage.getItem('offlineData');
      if (data) {
        offlineData = JSON.parse(data);
        updateOfflineDataStats();
      }
      
      const queue = localStorage.getItem('syncQueue');
      if (queue) {
        syncQueue = JSON.parse(queue);
        displaySyncQueue();
      }
      
      const conflictData = localStorage.getItem('conflicts');
      if (conflictData) {
        conflicts = JSON.parse(conflictData);
        displayConflicts();
      }
    }

    function saveOfflineData() {
      localStorage.setItem('offlineData', JSON.stringify(offlineData));
      localStorage.setItem('syncQueue', JSON.stringify(syncQueue));
      localStorage.setItem('conflicts', JSON.stringify(conflicts));
    }

    function updateOfflineDataStats() {
      const dataSize = JSON.stringify(offlineData).length;
      const sizeInMB = (dataSize / 1024 / 1024).toFixed(2);
      document.getElementById('offlineDataSize').textContent = sizeInMB + ' MB';
      
      document.getElementById('pendingSyncCount').textContent = syncQueue.length;
      
      // Update storage bar
      const maxStorage = 50 * 1024 * 1024; // 50MB
      const usedPercentage = (dataSize / maxStorage) * 100;
      document.getElementById('storageUsed').textContent = `${sizeInMB} MB / 50 MB`;
      document.getElementById('storageBar').style.width = Math.min(usedPercentage, 100) + '%';
    }

    function downloadOfflineData() {
      if (!isOnline) {
        showToast('Cannot download data while offline', 'error');
        return;
      }
      
      showSyncModal('Downloading Data', 'Fetching your data for offline access...', 0);
      
      fetch('/offline/download', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          cache_notes: document.getElementById('cacheNotes').checked,
          cache_tasks: document.getElementById('cacheTasks').checked,
          cache_tags: document.getElementById('cacheTags').checked,
          cache_settings: document.getElementById('cacheSettings').checked,
          csrf_token: document.querySelector('input[name="csrf_token"]')?.value || ''
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          offlineData = data.data;
          saveOfflineData();
          updateOfflineDataStats();
          updateSyncProgress(100, 'Download completed!');
          setTimeout(() => {
            hideSyncModal();
            showToast('Offline data downloaded successfully', 'success');
          }, 1000);
        } else {
          hideSyncModal();
          showToast(data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Download error:', error);
        hideSyncModal();
        showToast('An error occurred while downloading data', 'error');
      });
    }

    function uploadOfflineData() {
      if (!isOnline) {
        showToast('Cannot upload data while offline', 'error');
        return;
      }
      
      if (syncQueue.length === 0) {
        showToast('No changes to upload', 'info');
        return;
      }
      
      showSyncModal('Uploading Changes', 'Syncing your offline changes...', 0);
      updateConnectionIndicator('syncing');
      
      fetch('/offline/upload', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          changes: syncQueue,
          csrf_token: document.querySelector('input[name="csrf_token"]')?.value || ''
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          syncQueue = [];
          conflicts = data.conflicts || [];
          saveOfflineData();
          updateOfflineDataStats();
          displaySyncQueue();
          displayConflicts();
          updateSyncProgress(100, 'Upload completed!');
          setTimeout(() => {
            hideSyncModal();
            updateConnectionIndicator('online');
            showToast('Changes uploaded successfully', 'success');
          }, 1000);
        } else {
          hideSyncModal();
          updateConnectionIndicator('online');
          showToast(data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Upload error:', error);
        hideSyncModal();
        updateConnectionIndicator('online');
        showToast('An error occurred while uploading data', 'error');
      });
    }

    function syncNow() {
      if (!isOnline) {
        showToast('Cannot sync while offline', 'error');
        return;
      }
      
      if (syncQueue.length === 0) {
        showToast('No changes to sync', 'info');
        return;
      }
      
      uploadOfflineData();
    }

    function checkForUpdates() {
      if (!isOnline) {
        showToast('Cannot check for updates while offline', 'error');
        return;
      }
      
      showSyncModal('Checking Updates', 'Looking for updates from server...', 0);
      
      fetch('/offline/check-updates', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          last_sync: localStorage.getItem('lastSync') || null,
          csrf_token: document.querySelector('input[name="csrf_token"]')?.value || ''
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          if (data.has_updates) {
            updateSyncProgress(100, 'Updates found!');
            setTimeout(() => {
              hideSyncModal();
              showToast('Updates available. Downloading...', 'info');
              downloadOfflineData();
            }, 1000);
          } else {
            updateSyncProgress(100, 'No updates available');
            setTimeout(() => {
              hideSyncModal();
              showToast('You are up to date', 'success');
            }, 1000);
          }
        } else {
          hideSyncModal();
          showToast(data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Update check error:', error);
        hideSyncModal();
        showToast('An error occurred while checking for updates', 'error');
      });
    }

    function displaySyncQueue() {
      const queueContainer = document.getElementById('syncQueue');
      
      if (syncQueue.length === 0) {
        queueContainer.innerHTML = `
          <div class="text-center py-8">
            <i class="fas fa-check-circle text-4xl text-green-500 mb-4"></i>
            <p class="text-gray-500">No pending changes</p>
          </div>
        `;
        return;
      }
      
      queueContainer.innerHTML = syncQueue.map(item => createSyncQueueItem(item)).join('');
    }

    function createSyncQueueItem(item) {
      const actionIcon = getActionIcon(item.action);
      const actionColor = getActionColor(item.action);
      
      return `
        <div class="sync-item rounded-xl p-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg ${actionColor} flex items-center justify-center text-white">
                <i class="${actionIcon} text-sm"></i>
              </div>
              <div>
                <h4 class="font-semibold text-gray-800">${item.title}</h4>
                <p class="text-sm text-gray-600">${item.type} • ${item.action} • ${new Date(item.timestamp).toLocaleString()}</p>
              </div>
            </div>
            <button onclick="removeFromSyncQueue('${item.id}')" class="p-2 text-red-600 hover:text-red-800">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
      `;
    }

    function getActionIcon(action) {
      const icons = {
        'create': 'fas fa-plus',
        'update': 'fas fa-edit',
        'delete': 'fas fa-trash',
        'sync': 'fas fa-sync'
      };
      return icons[action] || 'fas fa-file';
    }

    function getActionColor(action) {
      const colors = {
        'create': 'bg-green-500',
        'update': 'bg-blue-500',
        'delete': 'bg-red-500',
        'sync': 'bg-purple-500'
      };
      return colors[action] || 'bg-gray-500';
    }

    function removeFromSyncQueue(itemId) {
      syncQueue = syncQueue.filter(item => item.id !== itemId);
      saveOfflineData();
      updateOfflineDataStats();
      displaySyncQueue();
      showToast('Item removed from sync queue', 'info');
    }

    function clearSyncQueue() {
      if (syncQueue.length === 0) {
        showToast('Sync queue is already empty', 'info');
        return;
      }
      
      if (confirm('Are you sure you want to clear the sync queue? This will discard all pending changes.')) {
        syncQueue = [];
        saveOfflineData();
        updateOfflineDataStats();
        displaySyncQueue();
        showToast('Sync queue cleared', 'info');
      }
    }

    function displayConflicts() {
      const conflictContainer = document.getElementById('conflictList');
      const noConflicts = document.getElementById('noConflicts');
      
      if (conflicts.length === 0) {
        conflictContainer.innerHTML = '';
        noConflicts.classList.remove('hidden');
        return;
      }
      
      noConflicts.classList.add('hidden');
      conflictContainer.innerHTML = conflicts.map(conflict => createConflictItem(conflict)).join('');
    }

    function createConflictItem(conflict) {
      return `
        <div class="sync-item rounded-xl p-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-yellow-500 flex items-center justify-center text-white">
                <i class="fas fa-exclamation-triangle text-sm"></i>
              </div>
              <div>
                <h4 class="font-semibold text-gray-800">${conflict.title}</h4>
                <p class="text-sm text-gray-600">Conflict in ${conflict.type} • ${new Date(conflict.timestamp).toLocaleString()}</p>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <button onclick="resolveConflict('${conflict.id}', 'server')" class="px-3 py-1 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
                Use Server
              </button>
              <button onclick="resolveConflict('${conflict.id}', 'local')" class="px-3 py-1 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700">
                Use Local
              </button>
              <button onclick="resolveConflict('${conflict.id}', 'merge')" class="px-3 py-1 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700">
                Merge
              </button>
            </div>
          </div>
        </div>
      `;
    }

    function resolveConflict(conflictId, resolution) {
      fetch('/offline/resolve-conflict', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          conflict_id: conflictId,
          resolution: resolution,
          csrf_token: document.querySelector('input[name="csrf_token"]')?.value || ''
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          conflicts = conflicts.filter(conflict => conflict.id !== conflictId);
          saveOfflineData();
          displayConflicts();
          showToast('Conflict resolved successfully', 'success');
        } else {
          showToast(data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Conflict resolution error:', error);
        showToast('An error occurred while resolving conflict', 'error');
      });
    }

    function clearOfflineData() {
      if (confirm('Are you sure you want to clear all offline data? This action cannot be undone.')) {
        localStorage.removeItem('offlineData');
        localStorage.removeItem('syncQueue');
        localStorage.removeItem('conflicts');
        localStorage.removeItem('lastSync');
        
        offlineData = {};
        syncQueue = [];
        conflicts = [];
        
        updateOfflineDataStats();
        displaySyncQueue();
        displayConflicts();
        
        showToast('Offline data cleared successfully', 'success');
      }
    }

    function checkSyncStatus() {
      if (isOnline && isOfflineMode && document.getElementById('autoSync').checked) {
        // Auto-sync if enabled
        if (syncQueue.length > 0) {
          syncNow();
        }
      }
    }

    function showSyncModal(title, description, progress) {
      document.getElementById('syncTitle').textContent = title;
      document.getElementById('syncDescription').textContent = description;
      document.getElementById('syncProgressBar').style.width = progress + '%';
      document.getElementById('syncProgressText').textContent = progress + '%';
      document.getElementById('syncModal').classList.remove('hidden');
    }

    function updateSyncProgress(progress, description) {
      document.getElementById('syncProgressBar').style.width = progress + '%';
      document.getElementById('syncProgressText').textContent = progress + '%';
      if (description) {
        document.getElementById('syncDescription').textContent = description;
      }
    }

    function hideSyncModal() {
      document.getElementById('syncModal').classList.add('hidden');
    }

    function showToast(message, type = 'info') {
      const toast = document.createElement('div');
      toast.className = `toast ${type}`;
      toast.innerHTML = `
        <div class="flex items-center">
          <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'} mr-2"></i>
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

    // Save settings when checkboxes change
    document.addEventListener('change', function(e) {
      if (e.target.matches('input[type="checkbox"]')) {
        saveOfflineSettings();
      }
    });
  </script>
</body>
</html>
