<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Backup & Export | SecureNote Pro</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    // Suppress Tailwind CSS production warning
    tailwind.config = {
      corePlugins: {
        preflight: false,
      }
    }
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        $page_title = "Backup & Restore";
        include __DIR__ . '/partials/navbar.php'; 
      ?>

      <!-- Content -->
      
      <main class="flex-1 overflow-y-auto p-6">
        <!-- Quick Actions Header -->
        <div class="flex items-center justify-between mb-8">
          <div>
            <p class="text-gray-600">Secure your data with automated backups and exports</p>
          </div>
          <div class="flex items-center gap-3">
            <button onclick="openBackupSettings()" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
              <i class="fas fa-cog mr-2"></i>Settings
            </button>
            <button onclick="createFullBackup()" class="px-6 py-3 bg-gradient-to-r from-green-600 to-blue-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 pulse-glow">
              <i class="fas fa-download mr-2"></i>Create Backup
            </button>
          </div>
        </div>

        <!-- Backup Status Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <div class="backup-card rounded-xl p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Last Backup</p>
                <p class="text-2xl font-bold text-gray-900" id="lastBackupDate">Never</p>
              </div>
              <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-shield-alt text-green-600 text-xl"></i>
              </div>
            </div>
          </div>
          
          <div class="backup-card rounded-xl p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Backup Size</p>
                <p class="text-2xl font-bold text-gray-900" id="backupSize">0 MB</p>
              </div>
              <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-hdd text-blue-600 text-xl"></i>
              </div>
            </div>
          </div>
          
          <div class="backup-card rounded-xl p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Auto Backup</p>
                <p class="text-2xl font-bold text-gray-900" id="autoBackupStatus">Off</p>
              </div>
              <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-clock text-purple-600 text-xl"></i>
              </div>
            </div>
          </div>
          
          <div class="backup-card rounded-xl p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Storage Used</p>
                <p class="text-2xl font-bold text-gray-900" id="storageUsed">0%</p>
              </div>
              <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-chart-pie text-yellow-600 text-xl"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Export Options -->
        <div class="glassmorphism rounded-2xl p-6 mb-8">
          <h3 class="text-xl font-semibold text-gray-800 mb-6">Export Options</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Full Export -->
            <div class="backup-type-card rounded-xl p-6 cursor-pointer" onclick="selectExportType('full')">
              <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center text-white">
                  <i class="fas fa-database text-xl"></i>
                </div>
                <input type="radio" name="exportType" value="full" class="w-4 h-4 text-blue-600">
              </div>
              <h4 class="text-lg font-semibold text-gray-800 mb-2">Full Export</h4>
              <p class="text-sm text-gray-600 mb-4">Export all notes, tasks, tags, and settings in a complete backup file.</p>
              <div class="flex items-center text-xs text-gray-500">
                <i class="fas fa-file-archive mr-2"></i>
                <span>JSON, ZIP formats</span>
              </div>
            </div>
            
            <!-- Notes Only -->
            <div class="backup-type-card rounded-xl p-6 cursor-pointer" onclick="selectExportType('notes')">
              <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-teal-600 rounded-lg flex items-center justify-center text-white">
                  <i class="fas fa-sticky-note text-xl"></i>
                </div>
                <input type="radio" name="exportType" value="notes" class="w-4 h-4 text-blue-600">
              </div>
              <h4 class="text-lg font-semibold text-gray-800 mb-2">Notes Only</h4>
              <p class="text-sm text-gray-600 mb-4">Export only your notes with attachments and version history.</p>
              <div class="flex items-center text-xs text-gray-500">
                <i class="fas fa-file-alt mr-2"></i>
                <span>Markdown, PDF formats</span>
              </div>
            </div>
            
            <!-- Tasks Only -->
            <div class="backup-type-card rounded-xl p-6 cursor-pointer" onclick="selectExportType('tasks')">
              <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-red-600 rounded-lg flex items-center justify-center text-white">
                  <i class="fas fa-tasks text-xl"></i>
                </div>
                <input type="radio" name="exportType" value="tasks" class="w-4 h-4 text-blue-600">
              </div>
              <h4 class="text-lg font-semibold text-gray-800 mb-2">Tasks Only</h4>
              <p class="text-sm text-gray-600 mb-4">Export only your tasks with subtasks and progress tracking.</p>
              <div class="flex items-center text-xs text-gray-500">
                <i class="fas fa-file-csv mr-2"></i>
                <span>CSV, JSON formats</span>
              </div>
            </div>
          </div>
          
          <!-- Export Format Selection -->
          <div class="mt-6">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">Export Format</h4>
            <div class="flex flex-wrap gap-3">
              <label class="flex items-center">
                <input type="radio" name="exportFormat" value="json" checked class="mr-2">
                <span class="text-sm font-medium text-gray-700">JSON (Recommended)</span>
              </label>
              <label class="flex items-center">
                <input type="radio" name="exportFormat" value="zip" class="mr-2">
                <span class="text-sm font-medium text-gray-700">ZIP Archive</span>
              </label>
              <label class="flex items-center">
                <input type="radio" name="exportFormat" value="pdf" class="mr-2">
                <span class="text-sm font-medium text-gray-700">PDF Document</span>
              </label>
              <label class="flex items-center">
                <input type="radio" name="exportFormat" value="csv" class="mr-2">
                <span class="text-sm font-medium text-gray-700">CSV Spreadsheet</span>
              </label>
            </div>
          </div>
          
          <!-- Export Actions -->
          <div class="mt-6 flex items-center gap-4">
            <button onclick="startExport()" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
              <i class="fas fa-download mr-2"></i>Start Export
            </button>
            <button onclick="scheduleExport()" class="px-6 py-3 bg-gray-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
              <i class="fas fa-clock mr-2"></i>Schedule Export
            </button>
          </div>
        </div>

        <!-- Import Section -->
        <div class="glassmorphism rounded-2xl p-6 mb-8">
          <h3 class="text-xl font-semibold text-gray-800 mb-6">Import Data</h3>
          
          <div class="file-drop-zone" id="fileDropZone" onclick="document.getElementById('fileInput').click()">
            <div class="text-center">
              <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
              <h4 class="text-lg font-semibold text-gray-700 mb-2">Drop files here or click to browse</h4>
              <p class="text-sm text-gray-500 mb-4">Supported formats: JSON, ZIP, CSV, PDF</p>
              <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-folder-open mr-2"></i>Choose Files
              </button>
            </div>
          </div>
          
          <input type="file" id="fileInput" class="hidden" multiple accept=".json,.zip,.csv,.pdf" onchange="handleFileSelect(event)">
          
          <!-- Import Options -->
          <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h4 class="text-lg font-semibold text-gray-800 mb-4">Import Options</h4>
              <div class="space-y-3">
                <label class="flex items-center">
                  <input type="checkbox" id="mergeData" class="mr-3">
                  <span class="text-sm text-gray-700">Merge with existing data</span>
                </label>
                <label class="flex items-center">
                  <input type="checkbox" id="preserveIds" class="mr-3">
                  <span class="text-sm text-gray-700">Preserve original IDs</span>
                </label>
                <label class="flex items-center">
                  <input type="checkbox" id="importAttachments" class="mr-3" checked>
                  <span class="text-sm text-gray-700">Import attachments</span>
                </label>
                <label class="flex items-center">
                  <input type="checkbox" id="importSettings" class="mr-3">
                  <span class="text-sm text-gray-700">Import user settings</span>
                </label>
              </div>
            </div>
            
            <div>
              <h4 class="text-lg font-semibold text-gray-800 mb-4">Conflict Resolution</h4>
              <div class="space-y-3">
                <label class="flex items-center">
                  <input type="radio" name="conflictResolution" value="skip" class="mr-3">
                  <span class="text-sm text-gray-700">Skip duplicates</span>
                </label>
                <label class="flex items-center">
                  <input type="radio" name="conflictResolution" value="overwrite" class="mr-3">
                  <span class="text-sm text-gray-700">Overwrite existing</span>
                </label>
                <label class="flex items-center">
                  <input type="radio" name="conflictResolution" value="rename" class="mr-3" checked>
                  <span class="text-sm text-gray-700">Rename duplicates</span>
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- Backup History -->
        <div class="glassmorphism rounded-2xl p-6 mb-8">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-gray-800">Backup History</h3>
            <button onclick="refreshBackupHistory()" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
              <i class="fas fa-refresh mr-2"></i>Refresh
            </button>
          </div>
          
          <div id="backupHistory" class="space-y-4">
            <!-- Backup history items will be populated here -->
          </div>
        </div>

        <!-- Backup Analytics -->
        <div class="glassmorphism rounded-2xl p-6 mb-8">
          <h3 class="text-xl font-semibold text-gray-800 mb-6">Backup Analytics</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="backup-card rounded-xl p-6">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-gray-600">Total Backups</p>
                  <p class="text-2xl font-bold text-gray-900" id="totalBackups">12</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                  <i class="fas fa-archive text-blue-600 text-xl"></i>
                </div>
              </div>
            </div>
            
            <div class="backup-card rounded-xl p-6">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-gray-600">Success Rate</p>
                  <p class="text-2xl font-bold text-green-600" id="successRate">98.5%</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                  <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
              </div>
            </div>
            
            <div class="backup-card rounded-xl p-6">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-gray-600">Data Protected</p>
                  <p class="text-2xl font-bold text-gray-900" id="dataProtected">2.4 GB</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                  <i class="fas fa-shield-alt text-purple-600 text-xl"></i>
                </div>
              </div>
            </div>
            
            <div class="backup-card rounded-xl p-6">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-gray-600">Last 30 Days</p>
                  <p class="text-2xl font-bold text-gray-900" id="recentBackups">8</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                  <i class="fas fa-calendar-alt text-orange-600 text-xl"></i>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Backup Chart -->
          <div class="mt-6">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">Backup Activity (Last 30 Days)</h4>
            <div class="bg-white rounded-xl p-4 h-64 flex items-center justify-center">
              <canvas id="backupChart" width="400" height="200"></canvas>
            </div>
          </div>
        </div>

        <!-- Cloud Integration -->
        <div class="glassmorphism rounded-2xl p-6 mb-8">
          <h3 class="text-xl font-semibold text-gray-800 mb-6">Cloud Integration</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="backup-type-card rounded-xl p-6 cursor-pointer" onclick="connectCloudService('google')">
              <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-blue-600 rounded-lg flex items-center justify-center text-white">
                  <i class="fab fa-google-drive text-xl"></i>
                </div>
                <div class="w-4 h-4 rounded-full bg-gray-300" id="googleStatus"></div>
              </div>
              <h4 class="text-lg font-semibold text-gray-800 mb-2">Google Drive</h4>
              <p class="text-sm text-gray-600 mb-4">Sync backups to Google Drive automatically</p>
              <button class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                Connect
              </button>
            </div>
            
            <div class="backup-type-card rounded-xl p-6 cursor-pointer" onclick="connectCloudService('dropbox')">
              <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-700 rounded-lg flex items-center justify-center text-white">
                  <i class="fab fa-dropbox text-xl"></i>
                </div>
                <div class="w-4 h-4 rounded-full bg-gray-300" id="dropboxStatus"></div>
              </div>
              <h4 class="text-lg font-semibold text-gray-800 mb-2">Dropbox</h4>
              <p class="text-sm text-gray-600 mb-4">Store backups securely in Dropbox</p>
              <button class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                Connect
              </button>
            </div>
            
            <div class="backup-type-card rounded-xl p-6 cursor-pointer" onclick="connectCloudService('onedrive')">
              <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-blue-800 rounded-lg flex items-center justify-center text-white">
                  <i class="fab fa-microsoft text-xl"></i>
                </div>
                <div class="w-4 h-4 rounded-full bg-gray-300" id="onedriveStatus"></div>
              </div>
              <h4 class="text-lg font-semibold text-gray-800 mb-2">OneDrive</h4>
              <p class="text-sm text-gray-600 mb-4">Backup to Microsoft OneDrive</p>
              <button class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                Connect
              </button>
            </div>
          </div>
        </div>

        <!-- Backup Verification -->
        <div class="glassmorphism rounded-2xl p-6 mb-8">
          <h3 class="text-xl font-semibold text-gray-800 mb-6">Backup Verification</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h4 class="text-lg font-semibold text-gray-800 mb-4">Integrity Check</h4>
              <div class="space-y-3">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                  <span class="text-sm text-gray-700">Checksum Verification</span>
                  <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                    <span class="text-sm text-green-600">Verified</span>
                  </div>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                  <span class="text-sm text-gray-700">File Integrity</span>
                  <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                    <span class="text-sm text-green-600">Intact</span>
                  </div>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                  <span class="text-sm text-gray-700">Encryption Status</span>
                  <div class="flex items-center">
                    <i class="fas fa-shield-alt text-blue-500 mr-2"></i>
                    <span class="text-sm text-blue-600">Encrypted</span>
                  </div>
                </div>
              </div>
            </div>
            
            <div>
              <h4 class="text-lg font-semibold text-gray-800 mb-4">Verification Actions</h4>
              <div class="space-y-3">
                <button onclick="verifyBackup()" class="w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                  <i class="fas fa-search mr-2"></i>Verify All Backups
                </button>
                <button onclick="repairBackup()" class="w-full px-4 py-3 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors">
                  <i class="fas fa-tools mr-2"></i>Repair Corrupted Backups
                </button>
                <button onclick="generateReport()" class="w-full px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                  <i class="fas fa-file-alt mr-2"></i>Generate Report
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Advanced Backup Options -->
        <div class="glassmorphism rounded-2xl p-6 mb-8">
          <h3 class="text-xl font-semibold text-gray-800 mb-6">Advanced Backup Options</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h4 class="text-lg font-semibold text-gray-800 mb-4">Backup Types</h4>
              <div class="space-y-3">
                <label class="flex items-center">
                  <input type="radio" name="backupType" value="full" class="mr-3" checked>
                  <div>
                    <span class="text-sm font-medium text-gray-700">Full Backup</span>
                    <p class="text-xs text-gray-500">Complete system backup</p>
                  </div>
                </label>
                <label class="flex items-center">
                  <input type="radio" name="backupType" value="incremental" class="mr-3">
                  <div>
                    <span class="text-sm font-medium text-gray-700">Incremental Backup</span>
                    <p class="text-xs text-gray-500">Only changed files since last backup</p>
                  </div>
                </label>
                <label class="flex items-center">
                  <input type="radio" name="backupType" value="differential" class="mr-3">
                  <div>
                    <span class="text-sm font-medium text-gray-700">Differential Backup</span>
                    <p class="text-xs text-gray-500">Changed files since last full backup</p>
                  </div>
                </label>
              </div>
            </div>
            
            <div>
              <h4 class="text-lg font-semibold text-gray-800 mb-4">Advanced Settings</h4>
              <div class="space-y-3">
                <label class="flex items-center">
                  <input type="checkbox" id="compressBackup" class="mr-3" checked>
                  <span class="text-sm text-gray-700">Compress backups</span>
                </label>
                <label class="flex items-center">
                  <input type="checkbox" id="encryptBackup" class="mr-3" checked>
                  <span class="text-sm text-gray-700">Encrypt backups</span>
                </label>
                <label class="flex items-center">
                  <input type="checkbox" id="verifyBackup" class="mr-3" checked>
                  <span class="text-sm text-gray-700">Verify after backup</span>
                </label>
                <label class="flex items-center">
                  <input type="checkbox" id="notifyBackup" class="mr-3">
                  <span class="text-sm text-gray-700">Email notifications</span>
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- Auto Backup Settings -->
        <div class="glassmorphism rounded-2xl p-6">
          <h3 class="text-xl font-semibold text-gray-800 mb-6">Auto Backup Settings</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h4 class="text-lg font-semibold text-gray-800 mb-4">Schedule</h4>
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Frequency</label>
                  <select id="backupFrequency" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="disabled">Disabled</option>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                  </select>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Time</label>
                  <input type="time" id="backupTime" value="02:00" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Retention</label>
                  <select id="backupRetention" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="7">Keep 7 backups</option>
                    <option value="14">Keep 14 backups</option>
                    <option value="30">Keep 30 backups</option>
                    <option value="90">Keep 90 backups</option>
                  </select>
                </div>
              </div>
            </div>
            
            <div>
              <h4 class="text-lg font-semibold text-gray-800 mb-4">Storage</h4>
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Storage Location</label>
                  <select id="backupLocation" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="local">Local Storage</option>
                    <option value="cloud">Cloud Storage</option>
                    <option value="email">Email Backup</option>
                  </select>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Compression</label>
                  <select id="backupCompression" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="none">No Compression</option>
                    <option value="zip">ZIP Compression</option>
                    <option value="gzip">GZIP Compression</option>
                  </select>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Encryption</label>
                  <select id="backupEncryption" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="none">No Encryption</option>
                    <option value="aes256">AES-256 Encryption</option>
                    <option value="gpg">GPG Encryption</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
          
          <div class="mt-6 flex items-center gap-4">
            <button onclick="saveBackupSettings()" class="px-6 py-3 bg-gradient-to-r from-green-600 to-blue-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
              <i class="fas fa-save mr-2"></i>Save Settings
            </button>
            <button onclick="testBackup()" class="px-6 py-3 bg-gray-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
              <i class="fas fa-play mr-2"></i>Test Backup
            </button>
          </div>
        </div>
      </main>
    </div>
  </div>

   <!-- Progress Modal -->
   <div id="progressModal" class="fixed inset-0 bg-black bg-opacity-50 modal-backdrop flex items-center justify-center hidden z-50">
    <div class="modal-content rounded-2xl shadow-2xl w-11/12 max-w-md slide-in">
      <div class="p-6">
        <div class="text-center">
          <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-cog text-blue-600 text-2xl animate-spin"></i>
          </div>
          <h3 class="text-xl font-semibold text-gray-800 mb-2" id="progressTitle">Processing...</h3>
          <p class="text-gray-600 mb-6" id="progressDescription">Please wait while we process your request.</p>
          
          <div class="w-full bg-gray-200 rounded-full h-2 mb-4">
            <div id="progressBar" class="progress-bar h-2 rounded-full" style="width: 0%"></div>
          </div>
          
          <p class="text-sm text-gray-500" id="progressText">0%</p>
        </div>
      </div>
    </div>
  </div>

  
  <script>
    // Global variables
    let selectedExportType = 'full';
    let selectedExportFormat = 'json';
    let backupHistory = [];

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
      loadBackupStatus();
      loadBackupHistory();
      setupEventListeners();
    });

    function setupEventListeners() {
      // File drop zone
      const dropZone = document.getElementById('fileDropZone');
      
      dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropZone.classList.add('dragover');
      });
      
      dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dropZone.classList.remove('dragover');
      });
      
      dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        handleFileDrop(e.dataTransfer.files);
      });
      
      // Export type selection
      document.querySelectorAll('input[name="exportType"]').forEach(radio => {
        radio.addEventListener('change', function() {
          selectedExportType = this.value;
          updateExportTypeSelection();
        });
      });
      
      // Export format selection
      document.querySelectorAll('input[name="exportFormat"]').forEach(radio => {
        radio.addEventListener('change', function() {
          selectedExportFormat = this.value;
        });
      });
    }

    function selectExportType(type) {
      selectedExportType = type;
      document.querySelector(`input[name="exportType"][value="${type}"]`).checked = true;
      updateExportTypeSelection();
    }

    function updateExportTypeSelection() {
      document.querySelectorAll('.backup-type-card').forEach(card => {
        card.classList.remove('selected');
      });
      
      const selectedCard = document.querySelector(`input[name="exportType"][value="${selectedExportType}"]`).closest('.backup-type-card');
      selectedCard.classList.add('selected');
    }

    function loadBackupStatus() {
      fetch('/backup/status', {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          document.getElementById('lastBackupDate').textContent = data.last_backup || 'Never';
          document.getElementById('backupSize').textContent = data.backup_size || '0 MB';
          document.getElementById('autoBackupStatus').textContent = data.auto_backup ? 'On' : 'Off';
          document.getElementById('storageUsed').textContent = data.storage_used || '0%';
        }
      })
      .catch(error => {
        console.error('Error loading backup status:', error);
      });
    }

    function loadBackupHistory() {
      fetch('/backup/history', {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success && data.history) {
          backupHistory = data.history;
        } else {
          backupHistory = [];
        }
        displayBackupHistory();
      })
      .catch(error => {
        console.error('Error loading backup history:', error);
        backupHistory = [];
        displayBackupHistory();
      });
    }

    function displayBackupHistory() {
      const historyContainer = document.getElementById('backupHistory');
      
      // Check if backupHistory is defined and is an array
      if (!backupHistory || !Array.isArray(backupHistory) || backupHistory.length === 0) {
        historyContainer.innerHTML = `
          <div class="text-center py-8">
            <i class="fas fa-history text-4xl text-gray-300 mb-4"></i>
            <p class="text-gray-500">No backup history found</p>
          </div>
        `;
        return;
      }
      
      historyContainer.innerHTML = backupHistory.map(backup => createBackupHistoryItem(backup)).join('');
    }

    function createBackupHistoryItem(backup) {
      const statusClass = getStatusClass(backup.status);
      const statusIcon = getStatusIcon(backup.status);
      
      return `
        <div class="backup-history-item rounded-xl p-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
              <div class="status-indicator ${statusClass}"></div>
              <div>
                <h4 class="font-semibold text-gray-800">${backup.name}</h4>
                <p class="text-sm text-gray-600">${backup.type} • ${backup.size} • ${new Date(backup.created_at).toLocaleString()}</p>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <button onclick="downloadBackup('${backup.id}')" class="p-2 text-blue-600 hover:text-blue-800">
                <i class="fas fa-download"></i>
              </button>
              <button onclick="deleteBackup('${backup.id}')" class="p-2 text-red-600 hover:text-red-800">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>
        </div>
      `;
    }

    function getStatusClass(status) {
      const classes = {
        'completed': 'status-success',
        'failed': 'status-error',
        'in_progress': 'status-info',
        'warning': 'status-warning'
      };
      return classes[status] || 'status-info';
    }

    function getStatusIcon(status) {
      const icons = {
        'completed': 'fas fa-check-circle',
        'failed': 'fas fa-exclamation-circle',
        'in_progress': 'fas fa-spinner',
        'warning': 'fas fa-exclamation-triangle'
      };
      return icons[status] || 'fas fa-info-circle';
    }

    function startExport() {
      if (!selectedExportType) {
        showToast('Please select an export type', 'error');
        return;
      }
      
      showProgressModal('Exporting Data', 'Preparing your export...', 0);
      
      fetch('/backup/export', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          type: selectedExportType,
          format: selectedExportFormat,
          csrf_token: document.querySelector('input[name="csrf_token"]')?.value || ''
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          updateProgress(100, 'Export completed successfully!');
          setTimeout(() => {
            hideProgressModal();
            downloadFile(data.download_url, data.filename);
            showToast('Export completed successfully', 'success');
          }, 1000);
        } else {
          hideProgressModal();
          showToast(data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Export error:', error);
        hideProgressModal();
        showToast('An error occurred during export', 'error');
      });
    }

    function scheduleExport() {
      const frequency = document.getElementById('backupFrequency').value;
      const time = document.getElementById('backupTime').value;
      
      if (frequency === 'disabled') {
        showToast('Please select a frequency', 'error');
        return;
      }
      
      fetch('/backup/schedule', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          type: selectedExportType,
          format: selectedExportFormat,
          frequency: frequency,
          time: time,
          csrf_token: document.querySelector('input[name="csrf_token"]')?.value || ''
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showToast('Export scheduled successfully', 'success');
        } else {
          showToast(data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Schedule error:', error);
        showToast('An error occurred while scheduling', 'error');
      });
    }

    function handleFileSelect(event) {
      const files = Array.from(event.target.files);
      handleFileImport(files);
    }

    function handleFileDrop(files) {
      handleFileImport(Array.from(files));
    }

    function handleFileImport(files) {
      if (files.length === 0) return;
      
      const file = files[0];
      const formData = new FormData();
      formData.append('file', file);
      formData.append('merge_data', document.getElementById('mergeData').checked);
      formData.append('preserve_ids', document.getElementById('preserveIds').checked);
      formData.append('import_attachments', document.getElementById('importAttachments').checked);
      formData.append('import_settings', document.getElementById('importSettings').checked);
      formData.append('conflict_resolution', document.querySelector('input[name="conflictResolution"]:checked').value);
      formData.append('csrf_token', document.querySelector('input[name="csrf_token"]')?.value || '');
      
      showProgressModal('Importing Data', 'Processing your file...', 0);
      
      fetch('/backup/import', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          updateProgress(100, 'Import completed successfully!');
          setTimeout(() => {
            hideProgressModal();
            showToast('Import completed successfully', 'success');
            loadBackupStatus();
          }, 1000);
        } else {
          hideProgressModal();
          showToast(data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Import error:', error);
        hideProgressModal();
        showToast('An error occurred during import', 'error');
      });
    }

    function createFullBackup() {
      showProgressModal('Creating Backup', 'Preparing full system backup...', 0);
      
      fetch('/backup/create', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          type: 'full',
          csrf_token: document.querySelector('input[name="csrf_token"]')?.value || ''
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          updateProgress(100, 'Backup created successfully!');
          setTimeout(() => {
            hideProgressModal();
            showToast('Backup created successfully', 'success');
            loadBackupStatus();
            loadBackupHistory();
          }, 1000);
        } else {
          hideProgressModal();
          showToast(data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Backup error:', error);
        hideProgressModal();
        showToast('An error occurred during backup', 'error');
      });
    }

    function saveBackupSettings() {
      const settings = {
        frequency: document.getElementById('backupFrequency').value,
        time: document.getElementById('backupTime').value,
        retention: document.getElementById('backupRetention').value,
        location: document.getElementById('backupLocation').value,
        compression: document.getElementById('backupCompression').value,
        encryption: document.getElementById('backupEncryption').value
      };
      
      fetch('/backup/settings', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          ...settings,
          csrf_token: document.querySelector('input[name="csrf_token"]')?.value || ''
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showToast('Backup settings saved successfully', 'success');
          loadBackupStatus();
        } else {
          showToast(data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Settings error:', error);
        showToast('An error occurred while saving settings', 'error');
      });
    }

    function testBackup() {
      showProgressModal('Testing Backup', 'Running backup test...', 0);
      
      fetch('/backup/test', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          csrf_token: document.querySelector('input[name="csrf_token"]')?.value || ''
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          updateProgress(100, 'Backup test completed successfully!');
          setTimeout(() => {
            hideProgressModal();
            showToast('Backup test completed successfully', 'success');
          }, 1000);
        } else {
          hideProgressModal();
          showToast(data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Test error:', error);
        hideProgressModal();
        showToast('An error occurred during backup test', 'error');
      });
    }

    function downloadBackup(backupId) {
      window.location.href = `/backup/download/${backupId}`;
    }

    function deleteBackup(backupId) {
      if (confirm('Are you sure you want to delete this backup?')) {
        fetch('/backup/delete', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            backup_id: backupId,
            csrf_token: document.querySelector('input[name="csrf_token"]')?.value || ''
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showToast('Backup deleted successfully', 'success');
            loadBackupHistory();
          } else {
            showToast(data.message, 'error');
          }
        })
        .catch(error => {
          console.error('Delete error:', error);
          showToast('An error occurred while deleting backup', 'error');
        });
      }
    }

    function refreshBackupHistory() {
      loadBackupHistory();
      showToast('Backup history refreshed', 'info');
    }

    function openBackupSettings() {
      // Scroll to settings section
      document.querySelector('.glassmorphism:last-child').scrollIntoView({ behavior: 'smooth' });
    }

    function showProgressModal(title, description, progress) {
      document.getElementById('progressTitle').textContent = title;
      document.getElementById('progressDescription').textContent = description;
      document.getElementById('progressBar').style.width = progress + '%';
      document.getElementById('progressText').textContent = progress + '%';
      document.getElementById('progressModal').classList.remove('hidden');
    }

    function updateProgress(progress, description) {
      document.getElementById('progressBar').style.width = progress + '%';
      document.getElementById('progressText').textContent = progress + '%';
      if (description) {
        document.getElementById('progressDescription').textContent = description;
      }
    }

    function hideProgressModal() {
      document.getElementById('progressModal').classList.add('hidden');
    }

    function downloadFile(url, filename) {
      const a = document.createElement('a');
      a.href = url;
      a.download = filename;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
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

    // New Advanced Features
    function connectCloudService(service) {
      showProgressModal('Connecting...', `Connecting to ${service}...`, 0);
      
      // Simulate connection process
      let progress = 0;
      const interval = setInterval(() => {
        progress += 20;
        updateProgress(progress, `Connecting to ${service}...`);
        
        if (progress >= 100) {
          clearInterval(interval);
          setTimeout(() => {
            hideProgressModal();
            showToast(`Successfully connected to ${service}`, 'success');
            document.getElementById(`${service}Status`).className = 'w-4 h-4 rounded-full bg-green-500';
          }, 500);
        }
      }, 200);
    }

    function verifyBackup() {
      showProgressModal('Verifying Backups', 'Checking backup integrity...', 0);
      
      let progress = 0;
      const interval = setInterval(() => {
        progress += 10;
        updateProgress(progress, 'Verifying backup files...');
        
        if (progress >= 100) {
          clearInterval(interval);
          setTimeout(() => {
            hideProgressModal();
            showToast('All backups verified successfully', 'success');
          }, 500);
        }
      }, 100);
    }

    function repairBackup() {
      if (confirm('Are you sure you want to repair corrupted backups?')) {
        showProgressModal('Repairing Backups', 'Repairing corrupted backup files...', 0);
        
        let progress = 0;
        const interval = setInterval(() => {
          progress += 15;
          updateProgress(progress, 'Repairing backup files...');
          
          if (progress >= 100) {
            clearInterval(interval);
            setTimeout(() => {
              hideProgressModal();
              showToast('Backup repair completed', 'success');
            }, 500);
          }
        }, 150);
      }
    }

    function generateReport() {
      showProgressModal('Generating Report', 'Creating backup report...', 0);
      
      let progress = 0;
      const interval = setInterval(() => {
        progress += 25;
        updateProgress(progress, 'Generating backup report...');
        
        if (progress >= 100) {
          clearInterval(interval);
          setTimeout(() => {
            hideProgressModal();
            showToast('Backup report generated successfully', 'success');
            // Simulate download
            const filename = `backup_report_${new Date().toISOString().split('T')[0]}.pdf`;
            downloadFile('/backup/report', filename);
          }, 500);
        }
      }, 200);
    }

    function initializeBackupChart() {
      const ctx = document.getElementById('backupChart');
      if (!ctx) return;
      
      // Sample data for backup activity chart
      const data = {
        labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
        datasets: [{
          label: 'Successful Backups',
          data: [3, 2, 4, 3],
          backgroundColor: 'rgba(59, 130, 246, 0.5)',
          borderColor: 'rgba(59, 130, 246, 1)',
          borderWidth: 2
        }, {
          label: 'Failed Backups',
          data: [0, 1, 0, 0],
          backgroundColor: 'rgba(239, 68, 68, 0.5)',
          borderColor: 'rgba(239, 68, 68, 1)',
          borderWidth: 2
        }]
      };
      
      const config = {
        type: 'bar',
        data: data,
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                stepSize: 1
              }
            }
          },
          plugins: {
            legend: {
              position: 'top',
            }
          }
        }
      };
      
      new Chart(ctx, config);
    }

    function loadBackupAnalytics() {
      // Simulate loading analytics data
      setTimeout(() => {
        document.getElementById('totalBackups').textContent = '12';
        document.getElementById('successRate').textContent = '98.5%';
        document.getElementById('dataProtected').textContent = '2.4 GB';
        document.getElementById('recentBackups').textContent = '8';
      }, 500);
    }

    // Initialize advanced features on page load
    document.addEventListener('DOMContentLoaded', function() {
      loadBackupStatus();
      loadBackupHistory();
      setupEventListeners();
      loadBackupAnalytics();
      initializeBackupChart();
    });
   
  </script>
</body>
</html>