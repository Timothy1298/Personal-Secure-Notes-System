<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Settings | SecureNote Pro</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
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
    
    .settings-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(4px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      transform-style: preserve-3d;
    }
    
    .settings-card:hover {
      transform: translateY(-2px) rotateX(1deg);
      box-shadow: 0 15px 30px -12px rgba(0, 0, 0, 0.1);
    }
    
    .settings-nav-item {
      background: rgba(255, 255, 255, 0.7);
      backdrop-filter: blur(5px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      transition: all 0.3s ease;
    }
    
    .settings-nav-item:hover {
      background: rgba(59, 130, 246, 0.1);
      transform: translateX(4px);
    }
    
    .settings-nav-item.active {
      background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
      color: white;
      border-color: #1d4ed8;
    }
    
    .toggle-switch {
      position: relative;
      display: inline-block;
      width: 60px;
      height: 34px;
    }
    
    .toggle-switch input {
      opacity: 0;
      width: 0;
      height: 0;
    }
    
    .slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #ccc;
      transition: .4s;
      border-radius: 34px;
    }
    
    .slider:before {
      position: absolute;
      content: "";
      height: 26px;
      width: 26px;
      left: 4px;
      bottom: 4px;
      background-color: white;
      transition: .4s;
      border-radius: 50%;
    }
    
    input:checked + .slider {
      background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    }
    
    input:checked + .slider:before {
      transform: translateX(26px);
    }
    
    .color-picker-grid {
      display: grid;
      grid-template-columns: repeat(8, 1fr);
      gap: 0.5rem;
      padding: 1rem;
    }
    
    .color-option {
      width: 2.5rem;
      height: 2.5rem;
      border-radius: 50%;
      border: 3px solid transparent;
      cursor: pointer;
      transition: all 0.2s ease;
      position: relative;
    }
    
    .color-option:hover {
      transform: scale(1.1);
      border-color: #374151;
    }
    
    .color-option.selected {
      border-color: #1f2937;
      transform: scale(1.2);
    }
    
    .color-option.selected::after {
      content: '✓';
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      color: white;
      font-weight: bold;
      text-shadow: 0 0 2px rgba(0, 0, 0, 0.5);
    }
    
    .theme-preview {
      width: 100%;
      height: 120px;
      border-radius: 0.75rem;
      border: 2px solid #e5e7eb;
      transition: all 0.3s ease;
      cursor: pointer;
      position: relative;
      overflow: hidden;
    }
    
    .theme-preview:hover {
      border-color: #3b82f6;
      transform: scale(1.02);
    }
    
    .theme-preview.selected {
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .font-preview {
      font-size: 1.5rem;
      font-weight: 500;
      color: #374151;
      text-align: center;
      padding: 1rem;
      border: 2px solid #e5e7eb;
      border-radius: 0.75rem;
      transition: all 0.3s ease;
      cursor: pointer;
    }
    
    .font-preview:hover {
      border-color: #3b82f6;
      transform: scale(1.02);
    }
    
    .font-preview.selected {
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
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
    
    .settings-section {
      display: none;
    }
    
    .settings-section.active {
      display: block;
    }
    
    .preview-container {
      background: rgba(255, 255, 255, 0.8);
      backdrop-filter: blur(5px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 0.75rem;
      padding: 1.5rem;
      margin-top: 1rem;
    }
    
    .security-indicator {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      display: inline-block;
      margin-right: 8px;
    }
    
    .security-strong {
      background: #10b981;
      box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
    }
    
    .security-medium {
      background: #f59e0b;
      box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2);
    }
    
    .security-weak {
      background: #ef4444;
      box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2);
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
  
  <!-- Settings Loader System -->
  <div id="settingsLoader" class="fixed inset-0 bg-black bg-opacity-30 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen">
      <div class="text-center text-white">
        <div class="w-12 h-12 border-4 border-white border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
        <p id="settingsLoaderMessage">Loading...</p>
      </div>
    </div>
  </div>

  <!-- Settings Toast Container -->
  <div id="settingsToastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>

  <!-- Main Container -->
  <div class="flex h-screen">
    <!-- Sidebar -->
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Header -->
      <?php 
        $page_title = "Settings";
        include __DIR__ . '/partials/navbar.php'; 
      ?>

      <!-- Content -->
      <main class="flex-1 overflow-y-auto p-6">
        
        <!-- Header Section -->
        <div class="flex items-center justify-between mb-8">
          <div>
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Settings</h1>
            <p class="text-gray-600">Customize your SecureNote Pro experience</p>
          </div>
          <div class="flex items-center gap-3">
            <button onclick="resetToDefaults()" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
              <i class="fas fa-undo mr-2"></i>Reset to Defaults
            </button>
            <button onclick="exportSettings()" class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors">
              <i class="fas fa-download mr-2"></i>Export Settings
            </button>
          </div>
        </div>

        <div class="flex gap-6">
          <!-- Settings Navigation -->
          <div class="w-64 flex-shrink-0">
            <div class="glassmorphism rounded-2xl p-4 sticky top-6">
              <h3 class="text-lg font-semibold text-gray-800 mb-4">Settings Categories</h3>
              <nav class="space-y-2">
                <button onclick="showSettingsSection('profile')" class="settings-nav-item active w-full text-left px-4 py-3 rounded-xl flex items-center gap-3">
                  <i class="fas fa-user text-lg"></i>
                  <span>Profile</span>
                </button>
                <button onclick="showSettingsSection('appearance')" class="settings-nav-item w-full text-left px-4 py-3 rounded-xl flex items-center gap-3">
                  <i class="fas fa-palette text-lg"></i>
                  <span>Appearance</span>
                </button>
                <button onclick="showSettingsSection('notifications')" class="settings-nav-item w-full text-left px-4 py-3 rounded-xl flex items-center gap-3">
                  <i class="fas fa-bell text-lg"></i>
                  <span>Notifications</span>
                </button>
                <button onclick="showSettingsSection('security')" class="settings-nav-item w-full text-left px-4 py-3 rounded-xl flex items-center gap-3">
                  <i class="fas fa-shield-alt text-lg"></i>
                  <span>Security</span>
                </button>
                <button onclick="showSettingsSection('privacy')" class="settings-nav-item w-full text-left px-4 py-3 rounded-xl flex items-center gap-3">
                  <i class="fas fa-lock text-lg"></i>
                  <span>Privacy</span>
                </button>
                <button onclick="showSettingsSection('data')" class="settings-nav-item w-full text-left px-4 py-3 rounded-xl flex items-center gap-3">
                  <i class="fas fa-database text-lg"></i>
                  <span>Data & Storage</span>
                </button>
                <button onclick="showSettingsSection('advanced')" class="settings-nav-item w-full text-left px-4 py-3 rounded-xl flex items-center gap-3">
                  <i class="fas fa-cog text-lg"></i>
                  <span>Advanced</span>
                </button>
                <button onclick="showSettingsSection('about')" class="settings-nav-item w-full text-left px-4 py-3 rounded-xl flex items-center gap-3">
                  <i class="fas fa-info-circle text-lg"></i>
                  <span>About</span>
                </button>
              </nav>
            </div>
          </div>

          <!-- Settings Content -->
          <div class="flex-1">
            
            <!-- Profile Settings -->
            <div id="profile-section" class="settings-section active">
              <div class="glassmorphism rounded-2xl p-6 mb-6">
                <h3 class="text-2xl font-bold text-gray-800 mb-6">Profile Settings</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Profile Picture -->
                  <div class="settings-card rounded-xl p-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Profile Picture</h4>
                    <div class="flex items-center gap-4">
                      <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white text-2xl font-bold">
                        <?= strtoupper(substr($_SESSION['user']['first_name'] ?? 'U', 0, 1)) ?>
                      </div>
                      <div>
                        <button onclick="changeProfilePicture()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                          <i class="fas fa-camera mr-2"></i>Change Picture
                        </button>
                        <p class="text-sm text-gray-500 mt-2">JPG, PNG up to 2MB</p>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Personal Information -->
                  <div class="settings-card rounded-xl p-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Personal Information</h4>
                    <form id="profileForm">
                      <div class="space-y-4">
                        <div>
                          <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                          <input type="text" id="firstName" value="<?= htmlspecialchars($_SESSION['user']['first_name'] ?? '') ?>" 
                                 class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                          <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                          <input type="text" id="lastName" value="<?= htmlspecialchars($_SESSION['user']['last_name'] ?? '') ?>" 
                                 class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                          <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                          <input type="email" id="email" value="<?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?>" 
                                 class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <button type="button" onclick="saveProfile()" class="w-full px-4 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl hover:shadow-lg transition-all duration-300">
                          <i class="fas fa-save mr-2"></i>Save Changes
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>

            <!-- Appearance Settings -->
            <div id="appearance-section" class="settings-section">
              <div class="glassmorphism rounded-2xl p-6 mb-6">
                <h3 class="text-2xl font-bold text-gray-800 mb-6">Appearance Settings</h3>
                
                <!-- Theme Selection -->
                <div class="settings-card rounded-xl p-6 mb-6">
                  <h4 class="text-lg font-semibold text-gray-800 mb-4">Theme</h4>
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="theme-preview selected" data-theme="light" onclick="selectTheme('light')">
                      <div class="w-full h-full bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center">
                        <div class="text-center">
                          <i class="fas fa-sun text-2xl text-yellow-500 mb-2"></i>
                          <p class="text-sm font-medium text-gray-700">Light</p>
                        </div>
                      </div>
                    </div>
                    <div class="theme-preview" data-theme="dark" onclick="selectTheme('dark')">
                      <div class="w-full h-full bg-gradient-to-br from-gray-800 to-gray-900 flex items-center justify-center">
                        <div class="text-center">
                          <i class="fas fa-moon text-2xl text-blue-400 mb-2"></i>
                          <p class="text-sm font-medium text-white">Dark</p>
                        </div>
                      </div>
                    </div>
                    <div class="theme-preview" data-theme="auto" onclick="selectTheme('auto')">
                      <div class="w-full h-full bg-gradient-to-br from-blue-200 to-purple-200 flex items-center justify-center">
                        <div class="text-center">
                          <i class="fas fa-adjust text-2xl text-purple-600 mb-2"></i>
                          <p class="text-sm font-medium text-gray-700">Auto</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Color Scheme -->
                <div class="settings-card rounded-xl p-6 mb-6">
                  <h4 class="text-lg font-semibold text-gray-800 mb-4">Accent Color</h4>
                  <div class="color-picker-grid">
                    <div class="color-option selected" style="background-color: #3b82f6;" data-color="#3b82f6" onclick="selectAccentColor('#3b82f6')"></div>
                    <div class="color-option" style="background-color: #10b981;" data-color="#10b981" onclick="selectAccentColor('#10b981')"></div>
                    <div class="color-option" style="background-color: #f59e0b;" data-color="#f59e0b" onclick="selectAccentColor('#f59e0b')"></div>
                    <div class="color-option" style="background-color: #ef4444;" data-color="#ef4444" onclick="selectAccentColor('#ef4444')"></div>
                    <div class="color-option" style="background-color: #8b5cf6;" data-color="#8b5cf6" onclick="selectAccentColor('#8b5cf6')"></div>
                    <div class="color-option" style="background-color: #06b6d4;" data-color="#06b6d4" onclick="selectAccentColor('#06b6d4')"></div>
                    <div class="color-option" style="background-color: #84cc16;" data-color="#84cc16" onclick="selectAccentColor('#84cc16')"></div>
                    <div class="color-option" style="background-color: #f97316;" data-color="#f97316" onclick="selectAccentColor('#f97316')"></div>
                    <div class="color-option" style="background-color: #ec4899;" data-color="#ec4899" onclick="selectAccentColor('#ec4899')"></div>
                    <div class="color-option" style="background-color: #6366f1;" data-color="#6366f1" onclick="selectAccentColor('#6366f1')"></div>
                    <div class="color-option" style="background-color: #14b8a6;" data-color="#14b8a6" onclick="selectAccentColor('#14b8a6')"></div>
                    <div class="color-option" style="background-color: #eab308;" data-color="#eab308" onclick="selectAccentColor('#eab308')"></div>
                    <div class="color-option" style="background-color: #dc2626;" data-color="#dc2626" onclick="selectAccentColor('#dc2626')"></div>
                    <div class="color-option" style="background-color: #7c3aed;" data-color="#7c3aed" onclick="selectAccentColor('#7c3aed')"></div>
                    <div class="color-option" style="background-color: #0891b2;" data-color="#0891b2" onclick="selectAccentColor('#0891b2')"></div>
                    <div class="color-option" style="background-color: #65a30d;" data-color="#65a30d" onclick="selectAccentColor('#65a30d')"></div>
                  </div>
                </div>
                
                <!-- Font Settings -->
                <div class="settings-card rounded-xl p-6 mb-6">
                  <h4 class="text-lg font-semibold text-gray-800 mb-4">Typography</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">Font Family</label>
                      <div class="space-y-2">
                        <div class="font-preview selected" data-font="Inter" onclick="selectFont('Inter')" style="font-family: Inter;">
                          Inter
                        </div>
                        <div class="font-preview" data-font="Poppins" onclick="selectFont('Poppins')" style="font-family: Poppins;">
                          Poppins
                        </div>
                        <div class="font-preview" data-font="Roboto" onclick="selectFont('Roboto')" style="font-family: Roboto;">
                          Roboto
                        </div>
                        <div class="font-preview" data-font="Open Sans" onclick="selectFont('Open Sans')" style="font-family: 'Open Sans';">
                          Open Sans
                        </div>
                      </div>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">Font Size</label>
                      <input type="range" id="fontSize" min="12" max="18" value="14" 
                             class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                             oninput="updateFontSize(this.value)">
                      <div class="flex justify-between text-xs text-gray-500 mt-1">
                        <span>Small</span>
                        <span>Medium</span>
                        <span>Large</span>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Layout Settings -->
                <div class="settings-card rounded-xl p-6">
                  <h4 class="text-lg font-semibold text-gray-800 mb-4">Layout & Display</h4>
                  <div class="space-y-4">
                    <div class="flex items-center justify-between">
                      <div>
                        <h5 class="font-medium text-gray-800">Compact Mode</h5>
                        <p class="text-sm text-gray-600">Reduce spacing for more content</p>
                      </div>
                      <label class="toggle-switch">
                        <input type="checkbox" id="compactMode" onchange="toggleCompactMode()">
                        <span class="slider"></span>
                      </label>
                    </div>
                    <div class="flex items-center justify-between">
                      <div>
                        <h5 class="font-medium text-gray-800">Show Animations</h5>
                        <p class="text-sm text-gray-600">Enable smooth transitions and effects</p>
                      </div>
                      <label class="toggle-switch">
                        <input type="checkbox" id="showAnimations" checked onchange="toggleAnimations()">
                        <span class="slider"></span>
                      </label>
                    </div>
                    <div class="flex items-center justify-between">
                      <div>
                        <h5 class="font-medium text-gray-800">Sidebar Collapsed</h5>
                        <p class="text-sm text-gray-600">Start with sidebar minimized</p>
                      </div>
                      <label class="toggle-switch">
                        <input type="checkbox" id="sidebarCollapsed" onchange="toggleSidebarCollapsed()">
                        <span class="slider"></span>
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Notifications Settings -->
            <div id="notifications-section" class="settings-section">
              <div class="glassmorphism rounded-2xl p-6 mb-6">
                <h3 class="text-2xl font-bold text-gray-800 mb-6">Notification Settings</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Email Notifications -->
                  <div class="settings-card rounded-xl p-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Email Notifications</h4>
                    <div class="space-y-4">
                      <div class="flex items-center justify-between">
                        <div>
                          <h5 class="font-medium text-gray-800">Task Reminders</h5>
                          <p class="text-sm text-gray-600">Get notified about upcoming tasks</p>
                        </div>
                        <label class="toggle-switch">
                          <input type="checkbox" id="emailTaskReminders" checked onchange="updateNotificationSettings()">
                          <span class="slider"></span>
                        </label>
                      </div>
                      <div class="flex items-center justify-between">
                        <div>
                          <h5 class="font-medium text-gray-800">Security Alerts</h5>
                          <p class="text-sm text-gray-600">Important security notifications</p>
                        </div>
                        <label class="toggle-switch">
                          <input type="checkbox" id="emailSecurityAlerts" checked onchange="updateNotificationSettings()">
                          <span class="slider"></span>
                        </label>
                      </div>
                      <div class="flex items-center justify-between">
                        <div>
                          <h5 class="font-medium text-gray-800">Weekly Summary</h5>
                          <p class="text-sm text-gray-600">Weekly activity summary</p>
                        </div>
                        <label class="toggle-switch">
                          <input type="checkbox" id="emailWeeklySummary" onchange="updateNotificationSettings()">
                          <span class="slider"></span>
                        </label>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Browser Notifications -->
                  <div class="settings-card rounded-xl p-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Browser Notifications</h4>
                    <div class="space-y-4">
                      <div class="flex items-center justify-between">
                        <div>
                          <h5 class="font-medium text-gray-800">Desktop Notifications</h5>
                          <p class="text-sm text-gray-600">Show browser notifications</p>
                        </div>
                        <label class="toggle-switch">
                          <input type="checkbox" id="browserNotifications" onchange="toggleBrowserNotifications()">
                          <span class="slider"></span>
                        </label>
                      </div>
                      <div class="flex items-center justify-between">
                        <div>
                          <h5 class="font-medium text-gray-800">Sound Alerts</h5>
                          <p class="text-sm text-gray-600">Play sound for notifications</p>
                        </div>
                        <label class="toggle-switch">
                          <input type="checkbox" id="soundAlerts" onchange="updateNotificationSettings()">
                          <span class="slider"></span>
                        </label>
                      </div>
                      <div class="flex items-center justify-between">
                        <div>
                          <h5 class="font-medium text-gray-800">Focus Mode</h5>
                          <p class="text-sm text-gray-600">Pause notifications during focus time</p>
                        </div>
                        <label class="toggle-switch">
                          <input type="checkbox" id="focusMode" onchange="updateNotificationSettings()">
                          <span class="slider"></span>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Notification Timing -->
                <div class="settings-card rounded-xl p-6 mt-6">
                  <h4 class="text-lg font-semibold text-gray-800 mb-4">Notification Timing</h4>
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">Task Reminder Time</label>
                      <select id="taskReminderTime" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="updateNotificationSettings()">
                        <option value="15">15 minutes before</option>
                        <option value="30" selected>30 minutes before</option>
                        <option value="60">1 hour before</option>
                        <option value="1440">1 day before</option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">Quiet Hours Start</label>
                      <input type="time" id="quietHoursStart" value="22:00" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="updateNotificationSettings()">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">Quiet Hours End</label>
                      <input type="time" id="quietHoursEnd" value="08:00" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="updateNotificationSettings()">
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Security Settings -->
            <div id="security-section" class="settings-section">
              <div class="glassmorphism rounded-2xl p-6 mb-6">
                <h3 class="text-2xl font-bold text-gray-800 mb-6">Security Settings</h3>
                
                <!-- Password Security -->
                <div class="settings-card rounded-xl p-6 mb-6">
                  <h4 class="text-lg font-semibold text-gray-800 mb-4">Password Security</h4>
                  <div class="space-y-4">
                    <div class="flex items-center justify-between">
                      <div>
                        <h5 class="font-medium text-gray-800">Current Password</h5>
                        <p class="text-sm text-gray-600">Change your account password</p>
                      </div>
                      <button onclick="changePassword()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-key mr-2"></i>Change Password
                      </button>
                    </div>
                    <div class="flex items-center justify-between">
                      <div>
                        <h5 class="font-medium text-gray-800">Two-Factor Authentication</h5>
                        <p class="text-sm text-gray-600">Add an extra layer of security</p>
                        <div class="flex items-center mt-1">
                          <div class="security-indicator security-medium"></div>
                          <span class="text-xs text-gray-500">2FA Disabled</span>
                        </div>
                      </div>
                      <button onclick="setup2FA()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-shield-alt mr-2"></i>Enable 2FA
                      </button>
                    </div>
                  </div>
                </div>
                
                <!-- Session Management -->
                <div class="settings-card rounded-xl p-6 mb-6">
                  <h4 class="text-lg font-semibold text-gray-800 mb-4">Session Management</h4>
                  <div class="space-y-4">
                    <div class="flex items-center justify-between">
                      <div>
                        <h5 class="font-medium text-gray-800">Active Sessions</h5>
                        <p class="text-sm text-gray-600">Manage your active login sessions</p>
                      </div>
                      <button onclick="viewActiveSessions()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-desktop mr-2"></i>View Sessions
                      </button>
                    </div>
                    <div class="flex items-center justify-between">
                      <div>
                        <h5 class="font-medium text-gray-800">Session Timeout</h5>
                        <p class="text-sm text-gray-600">Auto-logout after inactivity</p>
                      </div>
                      <select id="sessionTimeout" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="updateSecuritySettings()">
                        <option value="30">30 minutes</option>
                        <option value="60" selected>1 hour</option>
                        <option value="120">2 hours</option>
                        <option value="480">8 hours</option>
                        <option value="0">Never</option>
                      </select>
                    </div>
                  </div>
                </div>
                
                <!-- Login Security -->
                <div class="settings-card rounded-xl p-6">
                  <h4 class="text-lg font-semibold text-gray-800 mb-4">Login Security</h4>
                  <div class="space-y-4">
                    <div class="flex items-center justify-between">
                      <div>
                        <h5 class="font-medium text-gray-800">Remember Me</h5>
                        <p class="text-sm text-gray-600">Stay logged in on this device</p>
                      </div>
                      <label class="toggle-switch">
                        <input type="checkbox" id="rememberMe" checked onchange="updateSecuritySettings()">
                        <span class="slider"></span>
                      </label>
                    </div>
                    <div class="flex items-center justify-between">
                      <div>
                        <h5 class="font-medium text-gray-800">Login Notifications</h5>
                        <p class="text-sm text-gray-600">Get notified of new logins</p>
                      </div>
                      <label class="toggle-switch">
                        <input type="checkbox" id="loginNotifications" checked onchange="updateSecuritySettings()">
                        <span class="slider"></span>
                      </label>
                    </div>
                    <div class="flex items-center justify-between">
                      <div>
                        <h5 class="font-medium text-gray-800">IP Restrictions</h5>
                        <p class="text-sm text-gray-600">Restrict login to specific IPs</p>
                      </div>
                      <label class="toggle-switch">
                        <input type="checkbox" id="ipRestrictions" onchange="updateSecuritySettings()">
                        <span class="slider"></span>
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Privacy Settings -->
            <div id="privacy-section" class="settings-section">
              <div class="glassmorphism rounded-2xl p-6 mb-6">
                <h3 class="text-2xl font-bold text-gray-800 mb-6">Privacy Settings</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Data Collection -->
                  <div class="settings-card rounded-xl p-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Data Collection</h4>
                    <div class="space-y-4">
                      <div class="flex items-center justify-between">
                        <div>
                          <h5 class="font-medium text-gray-800">Usage Analytics</h5>
                          <p class="text-sm text-gray-600">Help improve the app</p>
                        </div>
                        <label class="toggle-switch">
                          <input type="checkbox" id="usageAnalytics" onchange="updatePrivacySettings()">
                          <span class="slider"></span>
                        </label>
                      </div>
                      <div class="flex items-center justify-between">
                        <div>
                          <h5 class="font-medium text-gray-800">Error Reporting</h5>
                          <p class="text-sm text-gray-600">Report crashes and errors</p>
                        </div>
                        <label class="toggle-switch">
                          <input type="checkbox" id="errorReporting" checked onchange="updatePrivacySettings()">
                          <span class="slider"></span>
                        </label>
                      </div>
                      <div class="flex items-center justify-between">
                        <div>
                          <h5 class="font-medium text-gray-800">Performance Monitoring</h5>
                          <p class="text-sm text-gray-600">Monitor app performance</p>
                        </div>
                        <label class="toggle-switch">
                          <input type="checkbox" id="performanceMonitoring" onchange="updatePrivacySettings()">
                          <span class="slider"></span>
                        </label>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Data Sharing -->
                  <div class="settings-card rounded-xl p-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Data Sharing</h4>
                    <div class="space-y-4">
                      <div class="flex items-center justify-between">
                        <div>
                          <h5 class="font-medium text-gray-800">Third-party Services</h5>
                          <p class="text-sm text-gray-600">Allow data sharing with partners</p>
                        </div>
                        <label class="toggle-switch">
                          <input type="checkbox" id="thirdPartySharing" onchange="updatePrivacySettings()">
                          <span class="slider"></span>
                        </label>
                      </div>
                      <div class="flex items-center justify-between">
                        <div>
                          <h5 class="font-medium text-gray-800">Marketing Communications</h5>
                          <p class="text-sm text-gray-600">Receive promotional emails</p>
                        </div>
                        <label class="toggle-switch">
                          <input type="checkbox" id="marketingEmails" onchange="updatePrivacySettings()">
                          <span class="slider"></span>
                        </label>
                      </div>
                      <div class="flex items-center justify-between">
                        <div>
                          <h5 class="font-medium text-gray-800">Data Export</h5>
                          <p class="text-sm text-gray-600">Allow data export requests</p>
                        </div>
                        <label class="toggle-switch">
                          <input type="checkbox" id="dataExport" checked onchange="updatePrivacySettings()">
                          <span class="slider"></span>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Data Retention -->
                <div class="settings-card rounded-xl p-6 mt-6">
                  <h4 class="text-lg font-semibold text-gray-800 mb-4">Data Retention</h4>
                  <div class="space-y-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">Activity Log Retention</label>
                      <select id="activityLogRetention" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="updatePrivacySettings()">
                        <option value="30">30 days</option>
                        <option value="90" selected>90 days</option>
                        <option value="365">1 year</option>
                        <option value="0">Forever</option>
                      </select>
                    </div>
                    <div class="flex items-center justify-between">
                      <div>
                        <h5 class="font-medium text-gray-800">Auto-delete Inactive Data</h5>
                        <p class="text-sm text-gray-600">Remove data after 2 years of inactivity</p>
                      </div>
                      <label class="toggle-switch">
                        <input type="checkbox" id="autoDeleteInactive" onchange="updatePrivacySettings()">
                        <span class="slider"></span>
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Data & Storage Settings -->
            <div id="data-section" class="settings-section">
              <div class="glassmorphism rounded-2xl p-6 mb-6">
                <h3 class="text-2xl font-bold text-gray-800 mb-6">Data & Storage</h3>
                
                <!-- Storage Overview -->
                <div class="settings-card rounded-xl p-6 mb-6">
                  <h4 class="text-lg font-semibold text-gray-800 mb-4">Storage Overview</h4>
                  <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="text-center">
                      <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-sticky-note text-blue-600 text-xl"></i>
                      </div>
                      <p class="text-sm text-gray-600">Notes</p>
                      <p class="font-semibold text-gray-800" id="notesStorage">12.5 MB</p>
                    </div>
                    <div class="text-center">
                      <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-tasks text-green-600 text-xl"></i>
                      </div>
                      <p class="text-sm text-gray-600">Tasks</p>
                      <p class="font-semibold text-gray-800" id="tasksStorage">3.2 MB</p>
                    </div>
                    <div class="text-center">
                      <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-paperclip text-purple-600 text-xl"></i>
                      </div>
                      <p class="text-sm text-gray-600">Attachments</p>
                      <p class="font-semibold text-gray-800" id="attachmentsStorage">45.8 MB</p>
                    </div>
                    <div class="text-center">
                      <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-database text-yellow-600 text-xl"></i>
                      </div>
                      <p class="text-sm text-gray-600">Total</p>
                      <p class="font-semibold text-gray-800" id="totalStorage">61.5 MB</p>
                    </div>
                  </div>
                </div>
                
                <!-- Storage Management -->
                <div class="settings-card rounded-xl p-6 mb-6">
                  <h4 class="text-lg font-semibold text-gray-800 mb-4">Storage Management</h4>
                  <div class="space-y-4">
                    <div class="flex items-center justify-between">
                      <div>
                        <h5 class="font-medium text-gray-800">Auto-cleanup Attachments</h5>
                        <p class="text-sm text-gray-600">Remove unused attachments after 90 days</p>
                      </div>
                      <label class="toggle-switch">
                        <input type="checkbox" id="autoCleanupAttachments" onchange="updateDataSettings()">
                        <span class="slider"></span>
                      </label>
                    </div>
                    <div class="flex items-center justify-between">
                      <div>
                        <h5 class="font-medium text-gray-800">Compress Old Notes</h5>
                        <p class="text-sm text-gray-600">Compress notes older than 1 year</p>
                      </div>
                      <label class="toggle-switch">
                        <input type="checkbox" id="compressOldNotes" onchange="updateDataSettings()">
                        <span class="slider"></span>
                      </label>
                    </div>
                    <div class="flex items-center justify-between">
                      <div>
                        <h5 class="font-medium text-gray-800">Local Cache</h5>
                        <p class="text-sm text-gray-600">Cache frequently accessed data locally</p>
                      </div>
                      <label class="toggle-switch">
                        <input type="checkbox" id="localCache" checked onchange="updateDataSettings()">
                        <span class="slider"></span>
                      </label>
                    </div>
                  </div>
                </div>
                
                <!-- Data Actions -->
                <div class="settings-card rounded-xl p-6">
                  <h4 class="text-lg font-semibold text-gray-800 mb-4">Data Actions</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <button onclick="exportAllData()" class="px-4 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors">
                      <i class="fas fa-download mr-2"></i>Export All Data
                    </button>
                    <button onclick="clearCache()" class="px-4 py-3 bg-yellow-600 text-white rounded-xl hover:bg-yellow-700 transition-colors">
                      <i class="fas fa-broom mr-2"></i>Clear Cache
                    </button>
                    <button onclick="optimizeStorage()" class="px-4 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors">
                      <i class="fas fa-compress mr-2"></i>Optimize Storage
                    </button>
                    <button onclick="deleteAllData()" class="px-4 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors">
                      <i class="fas fa-trash mr-2"></i>Delete All Data
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Advanced Settings -->
            <div id="advanced-section" class="settings-section">
              <div class="glassmorphism rounded-2xl p-6 mb-6">
                <h3 class="text-2xl font-bold text-gray-800 mb-6">Advanced Settings</h3>
                
                <!-- Developer Options -->
                <div class="settings-card rounded-xl p-6 mb-6">
                  <h4 class="text-lg font-semibold text-gray-800 mb-4">Developer Options</h4>
                  <div class="space-y-4">
                    <div class="flex items-center justify-between">
                      <div>
                        <h5 class="font-medium text-gray-800">Debug Mode</h5>
                        <p class="text-sm text-gray-600">Enable detailed logging and debugging</p>
                      </div>
                      <label class="toggle-switch">
                        <input type="checkbox" id="debugMode" onchange="updateAdvancedSettings()">
                        <span class="slider"></span>
                      </label>
                    </div>
                    <div class="flex items-center justify-between">
                      <div>
                        <h5 class="font-medium text-gray-800">API Access</h5>
                        <p class="text-sm text-gray-600">Enable API access for third-party apps</p>
                      </div>
                      <label class="toggle-switch">
                        <input type="checkbox" id="apiAccess" onchange="updateAdvancedSettings()">
                        <span class="slider"></span>
                      </label>
                    </div>
                    <div class="flex items-center justify-between">
                      <div>
                        <h5 class="font-medium text-gray-800">Experimental Features</h5>
                        <p class="text-sm text-gray-600">Enable beta and experimental features</p>
                      </div>
                      <label class="toggle-switch">
                        <input type="checkbox" id="experimentalFeatures" onchange="updateAdvancedSettings()">
                        <span class="slider"></span>
                      </label>
                    </div>
                  </div>
                </div>
                
                <!-- Performance Settings -->
                <div class="settings-card rounded-xl p-6 mb-6">
                  <h4 class="text-lg font-semibold text-gray-800 mb-4">Performance Settings</h4>
                  <div class="space-y-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">Auto-save Interval</label>
                      <select id="autoSaveInterval" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="updateAdvancedSettings()">
                        <option value="5">5 seconds</option>
                        <option value="10" selected>10 seconds</option>
                        <option value="30">30 seconds</option>
                        <option value="60">1 minute</option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">Search Index Update</label>
                      <select id="searchIndexUpdate" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="updateAdvancedSettings()">
                        <option value="realtime">Real-time</option>
                        <option value="periodic" selected>Every 5 minutes</option>
                        <option value="manual">Manual only</option>
                      </select>
                    </div>
                    <div class="flex items-center justify-between">
                      <div>
                        <h5 class="font-medium text-gray-800">Lazy Loading</h5>
                        <p class="text-sm text-gray-600">Load content as needed</p>
                      </div>
                      <label class="toggle-switch">
                        <input type="checkbox" id="lazyLoading" checked onchange="updateAdvancedSettings()">
                        <span class="slider"></span>
                      </label>
                    </div>
                  </div>
                </div>
                
                <!-- Integration Settings -->
                <div class="settings-card rounded-xl p-6">
                  <h4 class="text-lg font-semibold text-gray-800 mb-4">Integrations</h4>
                  <div class="space-y-4">
                    <div class="flex items-center justify-between">
                      <div>
                        <h5 class="font-medium text-gray-800">Google Calendar</h5>
                        <p class="text-sm text-gray-600">Sync tasks with Google Calendar</p>
                      </div>
                      <button onclick="connectGoogleCalendar()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-link mr-2"></i>Connect
                      </button>
                    </div>
                    <div class="flex items-center justify-between">
                      <div>
                        <h5 class="font-medium text-gray-800">Dropbox</h5>
                        <p class="text-sm text-gray-600">Backup to Dropbox</p>
                      </div>
                      <button onclick="connectDropbox()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-link mr-2"></i>Connect
                      </button>
                    </div>
                    <div class="flex items-center justify-between">
                      <div>
                        <h5 class="font-medium text-gray-800">Slack</h5>
                        <p class="text-sm text-gray-600">Send notifications to Slack</p>
                      </div>
                      <button onclick="connectSlack()" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                        <i class="fas fa-link mr-2"></i>Connect
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- About Section -->
            <div id="about-section" class="settings-section">
              <div class="glassmorphism rounded-2xl p-6 mb-6">
                <h3 class="text-2xl font-bold text-gray-800 mb-6">About SecureNote Pro</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- App Information -->
                  <div class="settings-card rounded-xl p-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Application Info</h4>
                    <div class="space-y-3">
                      <div class="flex justify-between">
                        <span class="text-gray-600">Version</span>
                        <span class="font-medium">2.1.0</span>
                      </div>
                      <div class="flex justify-between">
                        <span class="text-gray-600">Build</span>
                        
      </div>

      <div class="w-full md:w-auto mt-4 md:mt-0">
        <h3 class="text-sm font-semibold text-white mb-2 text-center md:text-left">
          Stay Updated
        </h3>
        <p class="text-xs text-gray-400 mb-2 text-center md:text-left">
          Subscribe to our newsletter for the latest updates.
        </p>
        <form action="#" method="post" class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
          <input type="email" placeholder="Enter your email" required class="w-full sm:w-auto flex-grow px-3 py-1.5 rounded-lg bg-slate-800 text-white placeholder-gray-500 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500">
          <button type="submit" class="flex-shrink-0 bg-blue-600 text-white font-bold text-xs px-3 py-1.5 rounded-lg hover:bg-blue-700 transition-colors duration-300">
            Subscribe
          </button>
        </form>
      </div>
    </div>

    <hr class="border-gray-700 my-6">

    <div class="flex flex-col md:flex-row justify-between items-center text-center md:text-left space-y-2 md:space-y-0">
      <p class="text-xs text-gray-500">
        &copy; 2025 Timothy Kuria. All Rights Reserved.
      </p>
      <div class="flex flex-col sm:flex-row justify-center space-y-1 sm:space-y-0 sm:space-x-3 text-xs text-gray-500">
        <a href="#" class="hover:text-white transition-colors duration-300">Privacy Policy</a>
        <a href="#" class="hover:text-white transition-colors duration-300">Terms of Service</a>
      </div>
    </div>
  </div>
</footer>
    </main>

  </div>


  <script>
    // Settings Loader and Toast System
    function showSettingsLoader(message = 'Loading...') {
      const loader = document.getElementById('settingsLoader');
      const messageEl = document.getElementById('settingsLoaderMessage');
      if (messageEl) messageEl.textContent = message;
      if (loader) loader.classList.remove('hidden');
    }

    function hideSettingsLoader() {
      const loader = document.getElementById('settingsLoader');
      if (loader) loader.classList.add('hidden');
    }

    function showSettingsToast(message, type = 'info') {
      const container = document.getElementById('settingsToastContainer');
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

   document.addEventListener('DOMContentLoaded', () => {

    const mainContent = document.querySelector('main');

    async function updateSetting(endpoint, data = {}) {
        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'An error occurred.');
            }

            const result = await response.json();
            return result;

        } catch (error) {
            console.error('API Error:', error);
            showSettingsToast('Failed to update setting: ' + error.message, 'error');
            return { success: false, message: error.message };
        }
    }


    // A function to display a temporary notification
    function showNotification(message, isSuccess = true) {
        const notification = document.createElement('div');
        notification.textContent = message;
        notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white shadow-lg transition-transform duration-300 transform translate-x-full ${isSuccess ? 'bg-green-500' : 'bg-red-500'}`;
        
        mainContent.appendChild(notification);
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
            notification.classList.add('translate-x-0');
        }, 100);

        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    const twoFaToggle = document.getElementById('2fa-toggle');
    if (twoFaToggle) {
        twoFaToggle.addEventListener('change', async (e) => {
            const isChecked = e.target.checked;
            const result = await updateSetting('/settings/security/2fa', { enable_2fa: isChecked });
            if (result.success) {
                showNotification(`Two-Factor Authentication ${isChecked ? 'enabled' : 'disabled'}.`);
            } else {
                showNotification(result.message, false);
                e.target.checked = !isChecked;
            }
        });
    }
    // Helper for password change modal
    function createPasswordModal() {
        const modalHtml = `
            <div id="passwordModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-xl shadow-2xl p-8 w-11/12 md:w-1/3 relative">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6 border-b border-gray-200 pb-2">Change Password</h3>
                    <form id="passwordForm">
                        <label for="current-password" class="block mb-2 text-sm font-semibold text-gray-700">Current Password</label>
                        <input type="password" id="current-password" name="current_password" class="w-full p-3 border border-gray-300 rounded-lg mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500" required>

                        <label for="new-password" class="block mb-2 text-sm font-semibold text-gray-700">New Password</label>
                        <input type="password" id="new-password" name="new_password" class="w-full p-3 border border-gray-300 rounded-lg mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500" required>

                        <label for="confirm-password" class="block mb-2 text-sm font-semibold text-gray-700">Confirm New Password</label>
                        <input type="password" id="confirm-password" name="confirm_password" class="w-full p-3 border border-gray-300 rounded-lg mb-6 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        
                        <div class="flex justify-end space-x-2">
                            <button type="button" class="px-6 py-2 rounded-lg bg-gray-300 text-gray-800 font-medium hover:bg-gray-400 transition-colors duration-200" onclick="document.getElementById('passwordModal').remove()">Cancel</button>
                            <button type="submit" class="px-6 py-2 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 transition-colors duration-200">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }

    //----------------------------------------------------------------------
    // Account & Security Functions
    //----------------------------------------------------------------------

    // Change Password
    const changePasswordButton = document.querySelector('.fa-user-shield')?.closest('.bg-white')?.querySelector('button.bg-gray-200');
    if (changePasswordButton) {
        changePasswordButton.addEventListener('click', () => {
            createPasswordModal();
            const passwordForm = document.getElementById('passwordForm');
        passwordForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const currentPassword = document.getElementById('current-password').value;
            const newPassword = document.getElementById('new-password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            
            if (newPassword !== confirmPassword) {
                showSettingsToast('New passwords do not match.', 'error');
                return;
            }

            const result = await updateSetting('/auth/password/update', { current_password: currentPassword, new_password: newPassword });
            if (result.success) {
                showNotification('Password updated successfully!');
                document.getElementById('passwordModal').remove();
            } else {
                showNotification(result.message, false);
            }
        });
        });
    }
 document.querySelectorAll('.fa-th-large, .fa-list, .fa-stream').forEach(button => {
        const buttonElement = button.closest('button');
        if (buttonElement) {
            buttonElement.addEventListener('click', async (e) => {
                document.querySelectorAll('.fa-th-large, .fa-list, .fa-stream').forEach(btn => {
                    const btnElement = btn.closest('button');
                    if (btnElement) {
                        btnElement.classList.replace('bg-blue-600', 'bg-gray-200');
                    }
                });
            e.currentTarget.classList.replace('bg-gray-200', 'bg-blue-600');
            
            let layout = 'grid';
            if (e.target.classList.contains('fa-list')) {
                layout = 'list';
            } else if (e.target.classList.contains('fa-stream')) {
                layout = 'compact';
            }

            const result = await updateSetting('/settings/appearance/note-layout', { layout: layout });
            if (result.success) {
                showNotification(`Note layout set to ${layout}.`);
            } else {
                showNotification(result.message, false);
            }
        });}
    });

    // Theme Selector
    const themeSelect = document.getElementById('theme-select');
    if (themeSelect) {
        themeSelect.addEventListener('change', async (e) => {
            const selectedTheme = e.target.value;
            const result = await updateSetting('/settings/appearance/theme', { theme: selectedTheme });
            if (result.success) {
                document.body.classList.remove('light', 'dark', 'system');
                document.body.classList.add(selectedTheme);
                showNotification(`Theme changed to ${selectedTheme}.`);
            } else {
                showNotification(result.message, false);
                e.target.value = 'light';
            }
        });
    }
    
    // Font Size
    const fontSizeInput = document.getElementById('font-size');
    if (fontSizeInput) {
        fontSizeInput.addEventListener('change', async (e) => {
            const fontSize = e.target.value + 'px';
            document.body.style.fontSize = fontSize;
            const result = await updateSetting('/settings/appearance/font-size', { font_size: fontSize });
            if (result.success) {
                showNotification(`Font size updated.`);
            } else {
                showNotification(result.message, false);
            }
        });
    }

    // Data Export
    const exportButton = document.querySelector('.fa-file-export');
    if (exportButton) {
        exportButton.closest('button').addEventListener('click', async () => {
            const result = await updateSetting('/settings/data/export');
            if (result.success) {
                window.location.href = result.file_url;
                showNotification('Data export started. Your download will begin shortly.');
            } else {
                showNotification(result.message, false);
            }
        });
    }

    // --- END Updated HTML & JavaScript Selectors ---

    // Password change modal function and form handler (no change needed here)
    function createPasswordModal() {
        const modalHtml = `
            <div id="passwordModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-xl shadow-2xl p-8 w-11/12 md:w-1/3 relative">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6 border-b border-gray-200 pb-2">Change Password</h3>
                    <form id="passwordForm">
                        <label for="current-password" class="block mb-2 text-sm font-semibold text-gray-700">Current Password</label>
                        <input type="password" id="current-password" name="current_password" class="w-full p-3 border border-gray-300 rounded-lg mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <label for="new-password" class="block mb-2 text-sm font-semibold text-gray-700">New Password</label>
                        <input type="password" id="new-password" name="new_password" class="w-full p-3 border border-gray-300 rounded-lg mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <label for="confirm-password" class="block mb-2 text-sm font-semibold text-gray-700">Confirm New Password</label>
                        <input type="password" id="confirm-password" name="confirm_password" class="w-full p-3 border border-gray-300 rounded-lg mb-6 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <div class="flex justify-end space-x-2">
                            <button type="button" class="px-6 py-2 rounded-lg bg-gray-300 text-gray-800 font-medium hover:bg-gray-400 transition-colors duration-200" onclick="document.getElementById('passwordModal').remove()">Cancel</button>
                            <button type="submit" class="px-6 py-2 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 transition-colors duration-200">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }

    document.querySelector('.fa-user-shield').closest('.bg-white').querySelector('button.bg-gray-200').addEventListener('click', () => {
        createPasswordModal();
        const passwordForm = document.getElementById('passwordForm');
        passwordForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const currentPassword = document.getElementById('current-password').value;
            const newPassword = document.getElementById('new-password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            if (newPassword !== confirmPassword) {
                showSettingsToast('New passwords do not match.', 'error');
                return;
            }
            const result = await updateSetting('/settings/account/password', { current_password: currentPassword, new_password: newPassword });
            if (result.success) {
                showNotification('Password updated successfully!');
                document.getElementById('passwordModal').remove();
            } else {
                showNotification(result.message, false);
            }
        });
    });

    // ... (rest of the JavaScript code is the same)
});
    // 2FA Toggle
    document.querySelector('.toggle-switch input[type="checkbox"]').addEventListener('change', async (e) => {
        const isChecked = e.target.checked;
        const result = await updateSetting('/settings/security/2fa', { enable_2fa: isChecked });
        if (result.success) {
            showNotification(`Two-Factor Authentication ${isChecked ? 'enabled' : 'disabled'}.`);
        } else {
            showNotification(result.message, false);
            e.target.checked = !isChecked; // Revert the toggle state on failure
        }
    });

    // Data Export
    document.querySelector('.fa-file-export').closest('button').addEventListener('click', async () => {
        const result = await updateSetting('/settings/data/export');
        if (result.success) {
            // Assuming the backend returns a URL to the file
            window.location.href = result.file_url;
            showNotification('Data export started. Your download will begin shortly.');
        } else {
            showNotification(result.message, false);
        }
    });

    // Data Import (requires a file input, which is not in the HTML)
    // For this, a modal with a file input would be needed.
    document.querySelector('.fa-file-import').closest('button').addEventListener('click', () => {
        showSettingsToast('This feature requires a file upload form. Functionality is not yet implemented in the UI.');
    });

    // Account Deletion
    document.querySelector('.fa-trash-alt').closest('button').addEventListener('click', async () => {
        // Show warning toast instead of confirm dialog
        showSettingsToast('Account deletion is a permanent action. Please contact support for assistance.', 'warning');
        // Note: Account deletion would require additional confirmation steps
        // const result = await updateSetting('/account/delete');
        // if (result.success) {
        //     showSettingsToast('Account successfully deleted.');
        //     window.location.href = '/logout';
        // } else {
        //     showSettingsToast(result.message);
        // }
    });

    //----------------------------------------------------------------------
    // Appearance & Personalization Functions
    //----------------------------------------------------------------------

    // Theme Selector
    document.getElementById('theme-select').addEventListener('change', async (e) => {
        const selectedTheme = e.target.value;
        const result = await updateSetting('/settings/appearance/theme', { theme: selectedTheme });
        if (result.success) {
            // Apply the theme immediately by adding a class to the body
            document.body.classList.remove('light', 'dark', 'system');
            document.body.classList.add(selectedTheme);
            showNotification(`Theme changed to ${selectedTheme}.`);
        } else {
            showNotification(result.message, false);
            // Revert the selection on failure
            e.target.value = 'light';
        }
    });

    // Font Size
    document.getElementById('font-size').addEventListener('change', async (e) => {
        const fontSize = e.target.value + 'px';
        document.body.style.fontSize = fontSize; // Apply immediately to the UI
        const result = await updateSetting('/settings/appearance/font-size', { font_size: fontSize });
        if (result.success) {
            showNotification(`Font size updated.`);
        } else {
            showNotification(result.message, false);
        }
    });

    // Note Layout
    document.querySelectorAll('.fa-th-large, .fa-list, .fa-stream').forEach(button => {
        button.closest('button').addEventListener('click', async (e) => {
            document.querySelectorAll('.fa-th-large, .fa-list, .fa-stream').forEach(btn => btn.closest('button').classList.replace('bg-blue-600', 'bg-gray-200'));
            e.currentTarget.classList.replace('bg-gray-200', 'bg-blue-600');
            
            let layout = 'grid';
            if (e.target.classList.contains('fa-list')) {
                layout = 'list';
            } else if (e.target.classList.contains('fa-stream')) {
                layout = 'compact';
            }

            const result = await updateSetting('/settings/appearance/note-layout', { layout: layout });
            if (result.success) {
                showNotification(`Note layout set to ${layout}.`);
            } else {
                showNotification(result.message, false);
            }
        });
    });

    // Sidebar Customization
    document.querySelector('.fa-cog').closest('.bg-white').querySelector('button.bg-gray-200').addEventListener('click', () => {
        showSettingsToast('Sidebar customization functionality would require a dedicated modal or view.');
    });

    //----------------------------------------------------------------------
    // Note Management Functions
    //----------------------------------------------------------------------

    // Default Note State
    document.getElementById('default-note-state').addEventListener('change', async (e) => {
        const defaultState = e.target.value;
        const result = await updateSetting('/settings/notes/default-state', { default_state: defaultState });
        if (result.success) {
            showNotification(`Default note state set to ${defaultState}.`);
        } else {
            showNotification(result.message, false);
        }
    });

    // Default Tags for New Notes
    document.getElementById('default-tags').addEventListener('change', async (e) => {
        const selectedTags = Array.from(e.target.selectedOptions).map(option => option.value);
        const result = await updateSetting('/settings/notes/default-tags', { default_tags: selectedTags });
        if (result.success) {
            showNotification('Default tags updated.');
        } else {
            showNotification(result.message, false);
        }
    });

    // Automatic Archiving Toggle
    document.querySelector('.fa-clipboard-check').closest('.bg-white').querySelector('.toggle-switch:nth-child(3) input').addEventListener('change', async (e) => {
        const isEnabled = e.target.checked;
        const result = await updateSetting('/settings/notes/auto-archive', { enabled: isEnabled });
        if (result.success) {
            showNotification(`Automatic archiving ${isEnabled ? 'enabled' : 'disabled'}.`);
        } else {
            showNotification(result.message, false);
            e.target.checked = !isEnabled;
        }
    });
    
    // Empty Trash Automatically Toggle
    document.querySelector('.fa-clipboard-check').closest('.bg-white').querySelector('.toggle-switch:nth-child(4) input').addEventListener('change', async (e) => {
        const isEnabled = e.target.checked;
        const result = await updateSetting('/settings/notes/auto-empty-trash', { enabled: isEnabled });
        if (result.success) {
            showNotification(`Automatic trash emptying ${isEnabled ? 'enabled' : 'disabled'}.`);
        } else {
            showNotification(result.message, false);
            e.target.checked = !isEnabled;
        }
    });

    //----------------------------------------------------------------------
    // Notifications Functions
    //----------------------------------------------------------------------

    // Email Notifications Toggle
    document.querySelector('.fa-bell').closest('.bg-white').querySelector('.toggle-switch:nth-child(1) input').addEventListener('change', async (e) => {
        const isEnabled = e.target.checked;
        const result = await updateSetting('/settings/notifications/email', { enabled: isEnabled });
        if (result.success) {
            showNotification(`Email notifications ${isEnabled ? 'enabled' : 'disabled'}.`);
        } else {
            showNotification(result.message, false);
            e.target.checked = !isEnabled;
        }
    });

    // Desktop Notifications Toggle
    document.querySelector('.fa-bell').closest('.bg-white').querySelector('.toggle-switch:nth-child(2) input').addEventListener('change', async (e) => {
        const isEnabled = e.target.checked;
        if (isEnabled && !("Notification" in window)) {
            showSettingsToast("This browser does not support desktop notifications.");
            e.target.checked = false;
            return;
        }
        if (isEnabled && Notification.permission !== "granted") {
            const permission = await Notification.requestPermission();
            if (permission !== "granted") {
                showNotification("Permission denied for desktop notifications.", false);
                e.target.checked = false;
                return;
            }
        }
        const result = await updateSetting('/settings/notifications/desktop', { enabled: isEnabled });
        if (result.success) {
            showNotification(`Desktop notifications ${isEnabled ? 'enabled' : 'disabled'}.`);
            if(isEnabled) {
                new Notification("Notifications Enabled", { body: "You will now receive desktop notifications." });
            }
        } else {
            showNotification(result.message, false);
            e.target.checked = !isEnabled;
        }
    });

    // Reminder Alerts Toggle
    document.querySelector('.fa-bell').closest('.bg-white').querySelector('.toggle-switch:nth-child(3) input').addEventListener('change', async (e) => {
        const isEnabled = e.target.checked;
        const result = await updateSetting('/settings/notifications/reminders', { enabled: isEnabled });
        if (result.success) {
            showNotification(`Reminder alerts ${isEnabled ? 'enabled' : 'disabled'}.`);
        } else {
            showNotification(result.message, false);
            e.target.checked = !isEnabled;
        }
    });

    //----------------------------------------------------------------------
    // Integrations Functions
    //----------------------------------------------------------------------

    // Google Calendar Integration
    document.querySelector('.fa-plug').closest('.bg-white').querySelector('.fa-link.mr-2').closest('button').addEventListener('click', () => {
        showSettingsToast('Initiating Google Calendar integration. This would redirect to Google\'s OAuth page.');
        // window.location.href = 'https://accounts.google.com/o/oauth2/v2/auth?...';
    });
    
    // Dropbox Integration
    document.querySelector('.fa-plug').closest('.bg-white').querySelector('li:nth-child(2) .fa-link').closest('button').addEventListener('click', () => {
        showSettingsToast('Initiating Dropbox integration. This would redirect to Dropbox\'s OAuth page.');
        // window.location.href = 'https://www.dropbox.com/oauth2/authorize?...';
    });

    //----------------------------------------------------------------------
    // Advanced Functions
    //----------------------------------------------------------------------

    // API Access
    const apiKeyButton = document.querySelector('.fa-cogs')?.closest('.bg-white')?.querySelector('.fa-key')?.closest('button');
    if (apiKeyButton) {
        apiKeyButton.addEventListener('click', () => {
            showSettingsToast('Generating a new API key would be a backend process. This would typically involve a confirmation step.');
        });
    }

    // Audit Log Retention
    document.getElementById('log-retention').addEventListener('change', async (e) => {
        const retentionPeriod = e.target.value;
        const result = await updateSetting('/settings/advanced/log-retention', { retention_period: retentionPeriod });
        if (result.success) {
            showNotification(`Audit log retention period set to ${retentionPeriod}.`);
        } else {
            showNotification(result.message, false);
        }
    });

    // Settings Section Navigation
    function showSettingsSection(sectionName) {
        // Hide all sections
        const sections = document.querySelectorAll('.settings-section');
        sections.forEach(section => {
            section.classList.add('hidden');
        });

        // Show selected section
        const targetSection = document.getElementById(sectionName + 'Section');
        if (targetSection) {
            targetSection.classList.remove('hidden');
        }

        // Update navigation active state
        const navItems = document.querySelectorAll('.settings-nav-item');
        navItems.forEach(item => {
            item.classList.remove('active');
        });

        // Find and activate the clicked nav item
        const activeNavItem = document.querySelector(`[onclick="showSettingsSection('${sectionName}')"]`);
        if (activeNavItem) {
            activeNavItem.classList.add('active');
        }
    }

    // Initialize settings page
    document.addEventListener('DOMContentLoaded', function() {
        // Show profile section by default
        showSettingsSection('profile');
    });
  </script>
</body>
</html>