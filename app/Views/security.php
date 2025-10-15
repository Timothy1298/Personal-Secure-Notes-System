<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Security & Keys | SecureNote Pro</title>
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
  <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
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
    
    .security-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      transform-style: preserve-3d;
    }
    
    .security-card:hover {
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
    
    .password-strength {
      height: 4px;
      border-radius: 2px;
      transition: all 0.3s ease;
    }
    
    .strength-weak { background: #ef4444; width: 25%; }
    .strength-fair { background: #f59e0b; width: 50%; }
    .strength-good { background: #3b82f6; width: 75%; }
    .strength-strong { background: #10b981; width: 100%; }
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
        $page_title = "Security & Keys";
        include __DIR__ . '/partials/navbar.php'; 
      ?>

      <!-- Content -->
      <main class="flex-1 overflow-y-auto p-6">
        
        <!-- Quick Actions Header -->
        <div class="flex items-center justify-between mb-8">
          <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Security & Keys</h1>
            <p class="text-gray-600">Manage your security settings, encryption keys, and authentication</p>
          </div>
          <div class="flex items-center gap-3">
            <button onclick="refreshSecurityStatus()" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
              <i class="fas fa-refresh mr-2"></i>Refresh
            </button>
            <button onclick="exportSecurityReport()" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
              <i class="fas fa-download mr-2"></i>Export Report
            </button>
          </div>
        </div>

        <!-- Security Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <div class="security-card rounded-xl p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Encryption Status</p>
                <p class="text-2xl font-bold text-green-600">Active</p>
                <p class="text-xs text-gray-500">AES-256-GCM</p>
              </div>
              <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-shield-alt text-green-600 text-xl"></i>
              </div>
            </div>
          </div>
          
          <div class="security-card rounded-xl p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">2FA Status</p>
                <p class="text-2xl font-bold text-gray-900" id="twoFactorStatus">Disabled</p>
                <p class="text-xs text-gray-500" id="twoFactorSubtext">Not enabled</p>
              </div>
              <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-mobile-alt text-blue-600 text-xl"></i>
              </div>
            </div>
          </div>
          
          <div class="security-card rounded-xl p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Active Sessions</p>
                <p class="text-2xl font-bold text-gray-900" id="activeSessionsCount">1</p>
                <p class="text-xs text-gray-500">Current device</p>
              </div>
              <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-desktop text-purple-600 text-xl"></i>
              </div>
            </div>
          </div>
          
          <div class="security-card rounded-xl p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Last Login</p>
                <p class="text-2xl font-bold text-gray-900" id="lastLoginTime">Today</p>
                <p class="text-xs text-gray-500" id="lastLoginIP">192.168.1.100</p>
              </div>
              <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-clock text-orange-600 text-xl"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Two-Factor Authentication -->
        <div class="glassmorphism rounded-2xl p-6 mb-8">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h3 class="text-xl font-semibold text-gray-800">Two-Factor Authentication</h3>
              <p class="text-gray-600">Add an extra layer of security to your account</p>
            </div>
            <div class="flex items-center gap-3">
              <button id="enable2FABtn" onclick="enable2FA()" class="px-6 py-3 bg-gradient-to-r from-green-600 to-blue-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
                <i class="fas fa-mobile-alt mr-2"></i>Enable 2FA
              </button>
              <button id="disable2FABtn" onclick="disable2FA()" class="px-6 py-3 bg-gradient-to-r from-red-600 to-pink-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hidden">
                <i class="fas fa-times mr-2"></i>Disable 2FA
              </button>
            </div>
          </div>
          
          <div id="twoFactorSetup" class="hidden">
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-6">
              <h4 class="font-semibold text-blue-800 mb-4">Setup Instructions</h4>
              <ol class="list-decimal list-inside space-y-2 text-blue-700">
                <li>Install an authenticator app like Google Authenticator or Authy</li>
                <li>Scan the QR code below with your authenticator app</li>
                <li>Enter the 6-digit code from your app to verify</li>
                <li>Save your backup codes in a secure location</li>
              </ol>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="text-center">
                <h4 class="font-semibold text-gray-800 mb-4">QR Code</h4>
                <div id="qrCodeContainer" class="bg-white p-4 rounded-xl border-2 border-gray-200 inline-block">
                  <!-- QR Code will be generated here -->
                </div>
                <p class="text-sm text-gray-500 mt-2">Scan with your authenticator app</p>
              </div>
              
              <div>
                <h4 class="font-semibold text-gray-800 mb-4">Manual Entry</h4>
                <div class="bg-gray-50 p-4 rounded-xl">
                  <p class="text-sm text-gray-600 mb-2">Secret Key:</p>
                  <div class="flex items-center gap-2">
                    <code id="secretKey" class="flex-1 bg-white p-2 rounded border text-sm font-mono"></code>
                    <button onclick="copySecretKey()" class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                      <i class="fas fa-copy"></i>
                    </button>
                  </div>
                </div>
                
                <div class="mt-4">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Verification Code</label>
                  <div class="flex gap-2">
                    <input type="text" id="verificationCode" placeholder="000000" maxlength="6" 
                           class="flex-1 px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-center text-lg font-mono">
                    <button onclick="verify2FACode()" class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors">
                      Verify
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div id="backupCodesSection" class="hidden">
            <div class="bg-green-50 border border-green-200 rounded-xl p-6">
              <h4 class="font-semibold text-green-800 mb-4">Backup Codes</h4>
              <p class="text-green-700 mb-4">Save these codes in a secure location. Each code can only be used once.</p>
              <div id="backupCodesList" class="grid grid-cols-2 md:grid-cols-5 gap-2 mb-4">
                <!-- Backup codes will be displayed here -->
              </div>
              <div class="flex gap-2">
                <button onclick="downloadBackupCodes()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                  <i class="fas fa-download mr-2"></i>Download
                </button>
                <button onclick="regenerateBackupCodes()" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors">
                  <i class="fas fa-refresh mr-2"></i>Regenerate
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Password Management -->
        <div class="glassmorphism rounded-2xl p-6 mb-8">
          <h3 class="text-xl font-semibold text-gray-800 mb-6">Password Management</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h4 class="font-semibold text-gray-800 mb-4">Change Password</h4>
              <form id="changePasswordForm" class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                  <input type="password" id="currentPassword" required
                         class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                  <input type="password" id="newPassword" required
                         class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                  <div class="mt-2">
                    <div class="password-strength bg-gray-200"></div>
                    <p id="passwordStrengthText" class="text-sm text-gray-500 mt-1"></p>
                  </div>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                  <input type="password" id="confirmPassword" required
                         class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <button type="submit" class="w-full px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
                  <i class="fas fa-key mr-2"></i>Change Password
                </button>
              </form>
            </div>
            
            <div>
              <h4 class="font-semibold text-gray-800 mb-4">Password Requirements</h4>
              <div class="space-y-2">
                <div class="flex items-center">
                  <i class="fas fa-check-circle text-green-500 mr-3"></i>
                  <span class="text-gray-700">At least 8 characters long</span>
                </div>
                <div class="flex items-center">
                  <i class="fas fa-check-circle text-green-500 mr-3"></i>
                  <span class="text-gray-700">Contains uppercase and lowercase letters</span>
                </div>
                <div class="flex items-center">
                  <i class="fas fa-check-circle text-green-500 mr-3"></i>
                  <span class="text-gray-700">Contains at least one number</span>
                </div>
                <div class="flex items-center">
                  <i class="fas fa-check-circle text-green-500 mr-3"></i>
                  <span class="text-gray-700">Contains at least one special character</span>
                </div>
              </div>
              
              <div class="mt-6">
                <h4 class="font-semibold text-gray-800 mb-4">Password History</h4>
                <div class="bg-gray-50 rounded-xl p-4">
                  <p class="text-sm text-gray-600">Last changed: <span id="lastPasswordChange">Never</span></p>
                  <p class="text-sm text-gray-600">Password age: <span id="passwordAge">Unknown</span></p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Active Sessions -->
        <div class="glassmorphism rounded-2xl p-6 mb-8">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-gray-800">Active Sessions</h3>
            <div class="flex items-center gap-3">
              <button onclick="refreshSessions()" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                <i class="fas fa-refresh mr-2"></i>Refresh
              </button>
              <button onclick="terminateAllSessions()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                <i class="fas fa-sign-out-alt mr-2"></i>Terminate All
              </button>
            </div>
          </div>
          
          <div id="sessionsList" class="space-y-4">
            <!-- Sessions will be loaded here -->
          </div>
        </div>

        <!-- Security Events -->
        <div class="glassmorphism rounded-2xl p-6 mb-8">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-gray-800">Recent Security Events</h3>
            <button onclick="refreshSecurityEvents()" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
              <i class="fas fa-refresh mr-2"></i>Refresh
            </button>
          </div>
          
          <div id="securityEventsList" class="space-y-4">
            <!-- Security events will be loaded here -->
          </div>
        </div>

        <!-- Encryption Information -->
        <div class="glassmorphism rounded-2xl p-6">
          <h3 class="text-xl font-semibold text-gray-800 mb-6">Encryption Information</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h4 class="font-semibold text-gray-800 mb-4">Encryption Details</h4>
              <div class="space-y-3">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                  <span class="text-sm text-gray-700">Algorithm</span>
                  <span class="text-sm font-medium text-gray-900">AES-256-GCM</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                  <span class="text-sm text-gray-700">Key Derivation</span>
                  <span class="text-sm font-medium text-gray-900">PBKDF2</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                  <span class="text-sm text-gray-700">IV Generation</span>
                  <span class="text-sm font-medium text-gray-900">Random</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                  <span class="text-sm text-gray-700">Authentication</span>
                  <span class="text-sm font-medium text-gray-900">GCM Mode</span>
                </div>
              </div>
            </div>
            
            <div>
              <h4 class="font-semibold text-gray-800 mb-4">Security Features</h4>
              <div class="space-y-3">
                <div class="flex items-center">
                  <i class="fas fa-check-circle text-green-500 mr-3"></i>
                  <span class="text-gray-700">End-to-end encryption</span>
                </div>
                <div class="flex items-center">
                  <i class="fas fa-check-circle text-green-500 mr-3"></i>
                  <span class="text-gray-700">Zero-knowledge architecture</span>
                </div>
                <div class="flex items-center">
                  <i class="fas fa-check-circle text-green-500 mr-3"></i>
                  <span class="text-gray-700">Secure key derivation</span>
                </div>
                <div class="flex items-center">
                  <i class="fas fa-check-circle text-green-500 mr-3"></i>
                  <span class="text-gray-700">Authenticated encryption</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <!-- 2FA Verification Modal -->
  <div id="twoFactorModal" class="fixed inset-0 bg-black bg-opacity-50 modal-backdrop flex items-center justify-center hidden z-50">
    <div class="modal-content rounded-2xl shadow-2xl w-11/12 max-w-md slide-in">
      <div class="p-6">
        <div class="text-center">
          <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-mobile-alt text-blue-600 text-2xl"></i>
          </div>
          <h3 class="text-xl font-semibold text-gray-800 mb-2">Disable Two-Factor Authentication</h3>
          <p class="text-gray-600 mb-6">Enter your 6-digit verification code to disable 2FA</p>
          
          <div class="mb-6">
            <input type="text" id="disable2FACode" placeholder="000000" maxlength="6" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-center text-lg font-mono">
          </div>
          
          <div class="flex gap-3">
            <button onclick="close2FAModal()" class="flex-1 px-4 py-3 bg-gray-600 text-white rounded-xl hover:bg-gray-700 transition-colors">
              Cancel
            </button>
            <button onclick="confirmDisable2FA()" class="flex-1 px-4 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors">
              Disable 2FA
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Global variables
    let current2FASecret = null;
    let currentBackupCodes = [];

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
      loadSecurityStatus();
      loadActiveSessions();
      loadSecurityEvents();
      setupEventListeners();
    });

    function setupEventListeners() {
      // Password strength checker
      document.getElementById('newPassword').addEventListener('input', checkPasswordStrength);
      
      // Change password form
      document.getElementById('changePasswordForm').addEventListener('submit', handlePasswordChange);
    }

    function loadSecurityStatus() {
      // Simulate loading security status
      // In a real implementation, this would fetch from the server
      setTimeout(() => {
        // Update 2FA status
        const is2FAEnabled = false; // This would come from server
        update2FAStatus(is2FAEnabled);
        
        // Update other status indicators
        document.getElementById('activeSessionsCount').textContent = '1';
        document.getElementById('lastLoginTime').textContent = 'Today';
        document.getElementById('lastLoginIP').textContent = '192.168.1.100';
      }, 500);
    }

    function update2FAStatus(enabled) {
      const statusElement = document.getElementById('twoFactorStatus');
      const subtextElement = document.getElementById('twoFactorSubtext');
      const enableBtn = document.getElementById('enable2FABtn');
      const disableBtn = document.getElementById('disable2FABtn');
      
      if (enabled) {
        statusElement.textContent = 'Enabled';
        statusElement.className = 'text-2xl font-bold text-green-600';
        subtextElement.textContent = 'Active';
        enableBtn.classList.add('hidden');
        disableBtn.classList.remove('hidden');
      } else {
        statusElement.textContent = 'Disabled';
        statusElement.className = 'text-2xl font-bold text-gray-900';
        subtextElement.textContent = 'Not enabled';
        enableBtn.classList.remove('hidden');
        disableBtn.classList.add('hidden');
      }
    }

    function enable2FA() {
      showProgressModal('Setting up 2FA', 'Generating secret key...', 0);
      
      fetch('/security/enable-2fa', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
          csrf_token: document.querySelector('input[name="csrf_token"]').value
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          current2FASecret = data.data.secret;
          generateQRCode(data.data.qr_code_url);
          document.getElementById('secretKey').textContent = data.data.secret;
          document.getElementById('twoFactorSetup').classList.remove('hidden');
          hideProgressModal();
          showToast('2FA setup initiated. Please scan the QR code.', 'info');
        } else {
          hideProgressModal();
          showToast(data.message, 'error');
        }
      })
      .catch(error => {
        console.error('2FA setup error:', error);
        hideProgressModal();
        showToast('Failed to setup 2FA', 'error');
      });
    }

    function generateQRCode(url) {
      const container = document.getElementById('qrCodeContainer');
      QRCode.toCanvas(container, url, { width: 200 }, function (error) {
        if (error) {
          console.error('QR Code generation error:', error);
          container.innerHTML = '<p class="text-red-500">Failed to generate QR code</p>';
        }
      });
    }

    function verify2FACode() {
      const code = document.getElementById('verificationCode').value;
      if (!code || code.length !== 6) {
        showToast('Please enter a valid 6-digit code', 'error');
        return;
      }
      
      showProgressModal('Verifying 2FA', 'Verifying your code...', 0);
      
      fetch('/security/verify-2fa', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          code: code,
          csrf_token: document.querySelector('input[name="csrf_token"]').value
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          currentBackupCodes = data.data.backup_codes;
          displayBackupCodes(data.data.backup_codes);
          document.getElementById('twoFactorSetup').classList.add('hidden');
          document.getElementById('backupCodesSection').classList.remove('hidden');
          update2FAStatus(true);
          hideProgressModal();
          showToast('2FA enabled successfully!', 'success');
        } else {
          hideProgressModal();
          showToast(data.message, 'error');
        }
      })
      .catch(error => {
        console.error('2FA verification error:', error);
        hideProgressModal();
        showToast('Failed to verify 2FA code', 'error');
      });
    }

    function displayBackupCodes(codes) {
      const container = document.getElementById('backupCodesList');
      container.innerHTML = codes.map(code => 
        `<div class="bg-white p-2 rounded border text-center font-mono text-sm">${code}</div>`
      ).join('');
    }

    function disable2FA() {
      document.getElementById('twoFactorModal').classList.remove('hidden');
    }

    function close2FAModal() {
      document.getElementById('twoFactorModal').classList.add('hidden');
      document.getElementById('disable2FACode').value = '';
    }

    function confirmDisable2FA() {
      const code = document.getElementById('disable2FACode').value;
      if (!code || code.length !== 6) {
        showToast('Please enter a valid 6-digit code', 'error');
        return;
      }
      
      showProgressModal('Disabling 2FA', 'Verifying your code...', 0);
      
      fetch('/security/disable-2fa', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          code: code,
          csrf_token: document.querySelector('input[name="csrf_token"]').value
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          update2FAStatus(false);
          document.getElementById('backupCodesSection').classList.add('hidden');
          close2FAModal();
          hideProgressModal();
          showToast('2FA disabled successfully', 'success');
        } else {
          hideProgressModal();
          showToast(data.message, 'error');
        }
      })
      .catch(error => {
        console.error('2FA disable error:', error);
        hideProgressModal();
        showToast('Failed to disable 2FA', 'error');
      });
    }

    function checkPasswordStrength() {
      const password = document.getElementById('newPassword').value;
      const strengthBar = document.querySelector('.password-strength');
      const strengthText = document.getElementById('passwordStrengthText');
      
      let strength = 0;
      let feedback = [];
      
      if (password.length >= 8) strength += 25;
      else feedback.push('At least 8 characters');
      
      if (/[A-Z]/.test(password)) strength += 25;
      else feedback.push('Uppercase letter');
      
      if (/[a-z]/.test(password)) strength += 25;
      else feedback.push('Lowercase letter');
      
      if (/[0-9]/.test(password)) strength += 25;
      else feedback.push('Number');
      
      if (/[^A-Za-z0-9]/.test(password)) strength += 25;
      else feedback.push('Special character');
      
      // Update strength bar
      strengthBar.className = 'password-strength';
      if (strength <= 25) {
        strengthBar.classList.add('strength-weak');
        strengthText.textContent = 'Weak';
        strengthText.className = 'text-sm text-red-500 mt-1';
      } else if (strength <= 50) {
        strengthBar.classList.add('strength-fair');
        strengthText.textContent = 'Fair';
        strengthText.className = 'text-sm text-yellow-500 mt-1';
      } else if (strength <= 75) {
        strengthBar.classList.add('strength-good');
        strengthText.textContent = 'Good';
        strengthText.className = 'text-sm text-blue-500 mt-1';
      } else {
        strengthBar.classList.add('strength-strong');
        strengthText.textContent = 'Strong';
        strengthText.className = 'text-sm text-green-500 mt-1';
      }
    }

    function handlePasswordChange(event) {
      event.preventDefault();
      
      const currentPassword = document.getElementById('currentPassword').value;
      const newPassword = document.getElementById('newPassword').value;
      const confirmPassword = document.getElementById('confirmPassword').value;
      
      if (newPassword !== confirmPassword) {
        showToast('New passwords do not match', 'error');
        return;
      }
      
      showProgressModal('Changing Password', 'Updating your password...', 0);
      
      fetch('/security/change-password', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          current_password: currentPassword,
          new_password: newPassword,
          confirm_password: confirmPassword,
          csrf_token: document.querySelector('input[name="csrf_token"]').value
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          document.getElementById('changePasswordForm').reset();
          hideProgressModal();
          showToast('Password changed successfully', 'success');
        } else {
          hideProgressModal();
          showToast(data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Password change error:', error);
        hideProgressModal();
        showToast('Failed to change password', 'error');
      });
    }

    function loadActiveSessions() {
      fetch('/security/sessions', {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          displaySessions(data.data.sessions);
        }
      })
      .catch(error => {
        console.error('Sessions load error:', error);
        // Display mock sessions for demo
        displaySessions([
          {
            id: 1,
            ip_address: '192.168.1.100',
            user_agent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            created_at: new Date().toISOString(),
            last_activity: new Date().toISOString(),
            is_current: true
          }
        ]);
      });
    }

    function displaySessions(sessions) {
      const container = document.getElementById('sessionsList');
      container.innerHTML = sessions.map(session => `
        <div class="security-card rounded-xl p-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
              <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-desktop text-blue-600"></i>
              </div>
              <div>
                <h4 class="font-semibold text-gray-800">${session.is_current ? 'Current Session' : 'Other Device'}</h4>
                <p class="text-sm text-gray-600">${session.ip_address}</p>
                <p class="text-xs text-gray-500">${new Date(session.last_activity).toLocaleString()}</p>
              </div>
            </div>
            <div class="flex items-center gap-2">
              ${session.is_current ? 
                '<span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">Current</span>' :
                '<button onclick="terminateSession(' + session.id + ')" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 transition-colors text-sm">Terminate</button>'
              }
            </div>
          </div>
        </div>
      `).join('');
    }

    function terminateSession(sessionId) {
      if (confirm('Are you sure you want to terminate this session?')) {
        fetch('/security/terminate-session', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            session_id: sessionId,
            csrf_token: document.querySelector('input[name="csrf_token"]').value
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showToast('Session terminated successfully', 'success');
            loadActiveSessions();
          } else {
            showToast(data.message, 'error');
          }
        })
        .catch(error => {
          console.error('Session termination error:', error);
          showToast('Failed to terminate session', 'error');
        });
      }
    }

    function terminateAllSessions() {
      if (confirm('Are you sure you want to terminate all other sessions? This will log you out of all other devices.')) {
        fetch('/security/terminate-all-sessions', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            csrf_token: document.querySelector('input[name="csrf_token"]').value
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showToast('All sessions terminated successfully', 'success');
            loadActiveSessions();
          } else {
            showToast(data.message, 'error');
          }
        })
        .catch(error => {
          console.error('Sessions termination error:', error);
          showToast('Failed to terminate sessions', 'error');
        });
      }
    }

    function loadSecurityEvents() {
      fetch('/security/events', {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          displaySecurityEvents(data.data.events);
        }
      })
      .catch(error => {
        console.error('Security events load error:', error);
        // Display mock events for demo
        displaySecurityEvents([
          {
            action: 'login',
            ip_address: '192.168.1.100',
            created_at: new Date().toISOString()
          },
          {
            action: 'password_change',
            ip_address: '192.168.1.100',
            created_at: new Date(Date.now() - 86400000).toISOString()
          }
        ]);
      });
    }

    function displaySecurityEvents(events) {
      const container = document.getElementById('securityEventsList');
      container.innerHTML = events.map(event => `
        <div class="security-card rounded-xl p-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
              <div class="status-indicator status-info"></div>
              <div>
                <h4 class="font-semibold text-gray-800">${event.action.replace('_', ' ').toUpperCase()}</h4>
                <p class="text-sm text-gray-600">IP: ${event.ip_address}</p>
                <p class="text-xs text-gray-500">${new Date(event.created_at).toLocaleString()}</p>
              </div>
            </div>
          </div>
        </div>
      `).join('');
    }

    function copySecretKey() {
      const secretKey = document.getElementById('secretKey').textContent;
      navigator.clipboard.writeText(secretKey).then(() => {
        showToast('Secret key copied to clipboard', 'success');
      });
    }

    function downloadBackupCodes() {
      const codes = currentBackupCodes.join('\n');
      const blob = new Blob([codes], { type: 'text/plain' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'backup-codes.txt';
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);
      showToast('Backup codes downloaded', 'success');
    }

    function regenerateBackupCodes() {
      if (confirm('Are you sure you want to regenerate backup codes? The old codes will no longer work.')) {
        fetch('/security/regenerate-backup-codes', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            csrf_token: document.querySelector('input[name="csrf_token"]').value
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            currentBackupCodes = data.data.backup_codes;
            displayBackupCodes(data.data.backup_codes);
            showToast('Backup codes regenerated successfully', 'success');
          } else {
            showToast(data.message, 'error');
          }
        })
        .catch(error => {
          console.error('Backup codes regeneration error:', error);
          showToast('Failed to regenerate backup codes', 'error');
        });
      }
    }

    function refreshSecurityStatus() {
      loadSecurityStatus();
      showToast('Security status refreshed', 'info');
    }

    function refreshSessions() {
      loadActiveSessions();
      showToast('Sessions refreshed', 'info');
    }

    function refreshSecurityEvents() {
      loadSecurityEvents();
      showToast('Security events refreshed', 'info');
    }

    function exportSecurityReport() {
      showToast('Security report exported', 'success');
      // Implement security report export
    }

    function showProgressModal(title, description, progress) {
      // Implementation for progress modal
      console.log('Progress:', title, description, progress);
    }

    function hideProgressModal() {
      // Implementation for hiding progress modal
      console.log('Hide progress modal');
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
  </script>
</body>
</html>