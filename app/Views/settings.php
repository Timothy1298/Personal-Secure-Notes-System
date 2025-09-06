<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | Secure Notes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <style>
      .toggle-switch input:checked + .slider {
          background-color: #2563eb; /* Blue-600 */
      }
      .toggle-switch input:checked + .slider::before {
          transform: translateX(20px);
      }
      .slider {
          background-color: #ccc;
          transition: .4s;
          border-radius: 34px;
      }
      .slider::before {
          content: '';
          height: 16px;
          width: 16px;
          left: 4px;
          bottom: 4px;
          background-color: white;
          transition: .4s;
          border-radius: 50%;
      }
      .modal-active {
          overflow: hidden;
      }
    </style>
</head>
<body class="bg-gray-100 h-screen flex font-sans antialiased">
  
  <aside class="w-64 bg-gray-800 text-white flex flex-col">
    <div class="p-6 text-2xl font-bold border-b border-gray-700">SecureNotes</div>
    <nav class="flex-1 px-4 py-6">
        <ul class="space-y-2">
            <li><a href="/dashboard" class="flex items-center p-3 rounded hover:bg-gray-700"><i class="fas fa-home mr-3"></i>Dashboard</a></li>
            <li><a href="/notes" class="flex items-center p-3 rounded hover:bg-gray-700"><i class="fas fa-sticky-note mr-3"></i>My Notes</a></li>
            <li><a href="/tasks" class="flex items-center p-3 rounded hover:bg-gray-700"><i class="fas fa-check-circle mr-3"></i>Tasks</a></li>
            <li><a href="/tags" class="flex items-center p-3 rounded hover:bg-gray-700"><i class="fas fa-tags mr-3"></i>Tags</a></li>
            <li><a href="/audit-logs" class="flex items-center p-3 rounded hover:bg-gray-700"><i class="fas fa-history mr-3"></i>Audit Logs</a></li>
            <li><a href="/archived" class="flex items-center p-3 rounded hover:bg-gray-700"><i class="fas fa-archive mr-3"></i>Archived</a></li>
            <li><a href="/settings" class="flex items-center p-3 rounded bg-gray-700"><i class="fas fa-cog mr-3"></i>Settings</a></li>
        </ul>
    </nav>
  </aside>

  <div class="flex-1 flex flex-col">
    <nav class="bg-white p-6 shadow-sm border-b border-gray-200">
      <div class="flex justify-between items-center">
        <div class="flex items-center space-x-4">
          <h1 class="text-2xl font-semibold text-gray-800">Hello, User</h1>
        </div>
        <div class="relative">
          <button class="flex items-center space-x-2 text-gray-700 hover:text-gray-900 focus:outline-none">
            <span class="text-lg font-medium">User</span>
            <img class="w-8 h-8 rounded-full" src="https://placehold.co/100x100" alt="User Profile">
          </button>
        </div>
      </div>
    </nav>

    <main class="p-8 flex-1 overflow-y-auto">
        <div class="flex items-center justify-between mb-8 pb-4 border-b border-gray-200">
            <h2 class="text-3xl font-extrabold text-gray-800 tracking-tight">Settings</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            
            <div class="bg-white p-6 rounded-xl shadow-md">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center"><i class="fas fa-user-shield text-blue-600 mr-2"></i>Account & Security</h3>
                <ul class="space-y-4">
                    <li>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700 font-medium">Change Password</span>
                            <button class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg text-sm font-medium hover:bg-gray-300 transition-colors duration-200"><i class="fas fa-edit mr-2"></i>Update</button>
                        </div>
                    </li>
                   <li>
    <div class="flex justify-between items-center">
        <span class="text-gray-700 font-medium">Two-Factor Authentication (2FA)</span>
        <label class="toggle-switch relative inline-flex items-center cursor-pointer">
            <input id="2fa-toggle" type="checkbox" value="" class="sr-only peer">
            <span class="slider block h-6 w-11 rounded-full peer-checked:bg-blue-600 transition-all duration-300"></span>
            <span class="absolute left-[2px] top-[2px] h-5 w-5 bg-white rounded-full transition-all duration-300 peer-checked:translate-x-5"></span>
        </label>
    </div>
</li>
                    <li>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700 font-medium">Session Management</span>
                            <button class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg text-sm font-medium hover:bg-gray-300 transition-colors duration-200"><i class="fas fa-list mr-2"></i>View Sessions</button>
                        </div>
                    </li>
                    <li>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700 font-medium">Export Data</span>
                            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors duration-200"><i class="fas fa-file-export mr-2"></i>Export</button>
                        </div>
                    </li>
                    <li>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700 font-medium">Import Data</span>
                            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors duration-200"><i class="fas fa-file-import mr-2"></i>Import</button>
                        </div>
                    </li>
                    <li>
                        <div class="flex justify-between items-center pt-2 mt-4 border-t border-gray-200">
                            <span class="text-red-600 font-bold">Account Deletion</span>
                            <button class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors duration-200"><i class="fas fa-trash-alt mr-2"></i>Delete</button>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-md">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center"><i class="fas fa-paint-brush text-blue-600 mr-2"></i>Appearance & Personalization</h3>
                <ul class="space-y-4">
                    <li>
                        <label for="theme-select" class="block text-gray-700 font-medium mb-1">Theme</label>
                        <select id="theme-select" class="w-full p-2 border border-gray-300 rounded-lg">
                            <option value="light">Light Mode</option>
                            <option value="dark">Dark Mode</option>
                            <option value="system">System Default</option>
                        </select>
                    </li>
                    <li>
                        <label for="font-size" class="block text-gray-700 font-medium mb-1">Font Size</label>
                        <input type="range" id="font-size" min="12" max="20" value="16" class="w-full">
                    </li>
                    <li>
                        <div class="flex justify-between items-center mb-1">
                          <span class="text-gray-700 font-medium">Note Layout</span>
                          <div class="flex space-x-2">
                              <button class="px-3 py-2 bg-blue-600 text-white rounded-lg transition-colors duration-200" title="Grid View"><i class="fas fa-th-large"></i></button>
                              <button class="px-3 py-2 bg-gray-200 text-gray-800 rounded-lg transition-colors duration-200" title="List View"><i class="fas fa-list"></i></button>
                              <button class="px-3 py-2 bg-gray-200 text-gray-800 rounded-lg transition-colors duration-200" title="Compact View"><i class="fas fa-stream"></i></button>
                          </div>
                        </div>
                    </li>
                    <li>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700 font-medium">Sidebar Customization</span>
                            <button class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg text-sm font-medium hover:bg-gray-300 transition-colors duration-200"><i class="fas fa-cog mr-2"></i>Edit</button>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-md">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center"><i class="fas fa-clipboard-check text-blue-600 mr-2"></i>Note Management</h3>
                <ul class="space-y-4">
                    <li>
                        <label for="default-note-state" class="block text-gray-700 font-medium mb-1">Default Note State</label>
                        <select id="default-note-state" class="w-full p-2 border border-gray-300 rounded-lg">
                            <option value="active">Active</option>
                            <option value="archived">Archived</option>
                        </select>
                    </li>
                    <li>
                        <label for="default-tags" class="block text-gray-700 font-medium mb-1">Default Tags for New Notes</label>
                        <select id="default-tags" multiple class="w-full p-2 border border-gray-300 rounded-lg">
                            <option value="work">Work</option>
                            <option value="personal">Personal</option>
                            <option value="important">Important</option>
                        </select>
                    </li>
                    <li>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700 font-medium">Automatic Archiving</span>
                            <label class="toggle-switch relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" value="" class="sr-only peer">
                                <span class="slider block h-6 w-11 rounded-full peer-checked:bg-blue-600 transition-all duration-300"></span>
                                <span class="absolute left-[2px] top-[2px] h-5 w-5 bg-white rounded-full transition-all duration-300 peer-checked:translate-x-5"></span>
                            </label>
                        </div>
                    </li>
                    <li>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700 font-medium">Empty Trash Automatically</span>
                            <label class="toggle-switch relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" value="" class="sr-only peer">
                                <span class="slider block h-6 w-11 rounded-full peer-checked:bg-blue-600 transition-all duration-300"></span>
                                <span class="absolute left-[2px] top-[2px] h-5 w-5 bg-white rounded-full transition-all duration-300 peer-checked:translate-x-5"></span>
                            </label>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-md">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center"><i class="fas fa-bell text-blue-600 mr-2"></i>Notifications</h3>
                <ul class="space-y-4">
                    <li>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700 font-medium">Email Notifications</span>
                            <label class="toggle-switch relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" value="" class="sr-only peer">
                                <span class="slider block h-6 w-11 rounded-full peer-checked:bg-blue-600 transition-all duration-300"></span>
                                <span class="absolute left-[2px] top-[2px] h-5 w-5 bg-white rounded-full transition-all duration-300 peer-checked:translate-x-5"></span>
                            </label>
                        </div>
                    </li>
                    <li>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700 font-medium">Desktop Notifications</span>
                            <label class="toggle-switch relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" value="" class="sr-only peer">
                                <span class="slider block h-6 w-11 rounded-full peer-checked:bg-blue-600 transition-all duration-300"></span>
                                <span class="absolute left-[2px] top-[2px] h-5 w-5 bg-white rounded-full transition-all duration-300 peer-checked:translate-x-5"></span>
                            </label>
                        </div>
                    </li>
                    <li>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700 font-medium">Reminder Alerts</span>
                            <label class="toggle-switch relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" value="" class="sr-only peer">
                                <span class="slider block h-6 w-11 rounded-full peer-checked:bg-blue-600 transition-all duration-300"></span>
                                <span class="absolute left-[2px] top-[2px] h-5 w-5 bg-white rounded-full transition-all duration-300 peer-checked:translate-x-5"></span>
                            </label>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-md">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center"><i class="fas fa-plug text-blue-600 mr-2"></i>Integrations</h3>
                <ul class="space-y-4">
                    <li>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700 font-medium">Google Calendar</span>
                            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors duration-200"><i class="fas fa-link mr-2"></i>Connect</button>
                        </div>
                    </li>
                    <li>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700 font-medium">Dropbox</span>
                            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors duration-200"><i class="fas fa-link mr-2"></i>Connect</button>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-md">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center"><i class="fas fa-cogs text-blue-600 mr-2"></i>Advanced</h3>
                <ul class="space-y-4">
                    <li>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700 font-medium">API Access</span>
                            <button class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg text-sm font-medium hover:bg-gray-300 transition-colors duration-200"><i class="fas fa-key mr-2"></i>Generate Key</button>
                        </div>
                    </li>
                    <li>
                        <label for="log-retention" class="block text-gray-700 font-medium mb-1">Audit Log Retention</label>
                        <select id="log-retention" class="w-full p-2 border border-gray-300 rounded-lg">
                            <option value="90">90 Days</option>
                            <option value="180">180 Days</option>
                            <option value="365">1 Year</option>
                            <option value="forever">Forever</option>
                        </select>
                    </li>
                </ul>
            </div>
            

        </div>
        <footer class="bg-slate-900 text-gray-300 py-6">
  <div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-6 md:space-y-0">
      <div class="flex flex-col md:flex-row items-center md:items-start space-y-4 md:space-x-12 md:space-y-0">
        <div class="flex-shrink-0 text-center md:text-left">
          <h2 class="text-xl font-extrabold text-white tracking-wide">
            Timothy Kuria
          </h2>
          <p class="text-xs text-gray-400 max-w-xs mt-1">
            Building amazing things with a passion for excellence.
          </p>
        </div>

        <div class="hidden md:block">
          <h3 class="text-sm font-semibold text-white mb-2">
            Quick Links
          </h3>
          <ul class="flex space-x-4 text-xs">
            <li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">Home</a></li>
            <li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">About Us</a></li>
            <li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">Services</a></li>
            <li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">Contact</a></li>
          </ul>
        </div>

        <div class="flex space-x-3 mt-2 md:mt-0">
          <a href="#" class="text-gray-400 hover:text-blue-500 transition-colors duration-300 rounded-full p-1 hover:bg-slate-800">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.35c0 .732.593 1.325 1.325 1.325h11.495v-9.294h-3.117v-3.616h3.117v-2.671c0-3.09 1.87-4.785 4.655-4.785.874 0 1.946.156 2.213.225v3.125h-1.921c-1.5 0-1.8.712-1.8 1.76v2.313h3.454l-.587 3.616h-2.867v9.293h6.115c.732 0 1.325-.593 1.325-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z" /></svg>
          </a>
          <a href="#" class="text-gray-400 hover:text-sky-400 transition-colors duration-300 rounded-full p-1 hover:bg-slate-800">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10.027 10.027 0 01-2.825.775 4.932 4.932 0 002.16-2.072 9.944 9.944 0 01-3.13.91 4.92 4.92 0 00-8.41 4.49 13.94 13.94 0 01-10.14-5.145 4.92 4.92 0 001.52 6.57 4.912 4.912 0 01-2.22-.615v.062a4.92 4.92 0 003.95 4.825 4.914 4.914 0 01-2.21.085c.67 2.053 2.607 3.55 4.918 3.593-3.955 3.093-8.948 4.91-14.332 4.91-1.076 0-2.138-.063-3.19-.187a13.99 13.99 0 007.545 2.21c9.056 0 14.04-7.535 14.04-14.04 0-.214-.005-.427-.015-.638a10.022 10.022 0 002.46-2.545z" /></svg>
          </a>
          <a href="#" class="text-gray-400 hover:text-pink-500 transition-colors duration-300 rounded-full p-1 hover:bg-slate-800">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.58.012 4.847.07 1.258.058 1.92.247 2.628.517.653.25 1.19.643 1.733 1.187.545.545.938 1.08 1.187 1.733.27.708.458 1.37.517 2.628.058 1.267.07 1.643.07 4.847s-.012 3.58-.07 4.847c-.058 1.258-.247 1.92-.517 2.628-.25.653-.643 1.19-1.187 1.733-.545.545-1.08 1.08-1.187 1.733-.27-.708-.458-1.37-.517-2.628-.058-1.267-.07-1.643-.07-4.847s.012-3.58.07-4.847c.058-1.258.247-1.92.517-2.628.25-.653.643-1.19 1.187-1.733.545-.545 1.08-1.08 1.733-1.187.708-.27 1.37-.458 2.628-.517 1.267-.058 1.643-.07 4.847-.07zm0-1.847c-3.262 0-3.67.012-4.945.07c-1.318.06-2.228.25-3.01.545-.783.295-1.46.732-2.11 1.382-.65.65-1.087 1.327-1.382 2.11-.295.782-.485 1.692-.545 3.01-.058 1.275-.07 1.683-.07 4.945s.012 3.67.07 4.945c.06 1.318.25 2.228.545 3.01.295.783.732 1.46 1.382 2.11.65.65 1.327 1.087 2.11 1.382.782.295 1.692.485 3.01.545 1.275.058 1.683.07 4.945.07s3.67-.012 4.945-.07c1.318-.06 2.228-.25 3.01-.545.783-.295 1.46-.732 2.11-1.382.65-.65 1.327-1.087 2.11-1.382.782-.295 1.692-.485-3.01-.545-1.275-.058-1.683-.07-4.945-.07zm0 9.215a4.215 4.215 0 100-8.43 4.215 4.215 0 000 8.43zm0-6.84a2.625 2.625 0 110 5.25 2.625 2.625 0 010-5.25z"/></svg>
          </a>
        </div>
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
            alert('Failed to update setting: ' + error.message);
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
    document.querySelector('.fa-user-shield').closest('.bg-white').querySelector('button.bg-gray-200').addEventListener('click', () => {
        createPasswordModal();
        const passwordForm = document.getElementById('passwordForm');
        passwordForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const currentPassword = document.getElementById('current-password').value;
            const newPassword = document.getElementById('new-password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            
            if (newPassword !== confirmPassword) {
                alert('New passwords do not match.');
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
                alert('New passwords do not match.');
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
        alert('This feature requires a file upload form. Functionality is not yet implemented in the UI.');
    });

    // Account Deletion
    document.querySelector('.fa-trash-alt').closest('button').addEventListener('click', async () => {
        if (confirm('Are you sure you want to permanently delete your account? This action cannot be undone.')) {
            const result = await updateSetting('/account/delete');
            if (result.success) {
                showNotification('Account successfully deleted.', true);
                window.location.href = '/logout'; // Redirect to logout or home page
            } else {
                showNotification(result.message, false);
            }
        }
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
        alert('Sidebar customization functionality would require a dedicated modal or view.');
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
            alert("This browser does not support desktop notifications.");
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
        alert('Initiating Google Calendar integration. This would redirect to Google\'s OAuth page.');
        // window.location.href = 'https://accounts.google.com/o/oauth2/v2/auth?...';
    });
    
    // Dropbox Integration
    document.querySelector('.fa-plug').closest('.bg-white').querySelector('li:nth-child(2) .fa-link').closest('button').addEventListener('click', () => {
        alert('Initiating Dropbox integration. This would redirect to Dropbox\'s OAuth page.');
        // window.location.href = 'https://www.dropbox.com/oauth2/authorize?...';
    });

    //----------------------------------------------------------------------
    // Advanced Functions
    //----------------------------------------------------------------------

    // API Access
    document.querySelector('.fa-cogs').closest('.bg-white').querySelector('.fa-key').closest('button').addEventListener('click', () => {
        alert('Generating a new API key would be a backend process. This would typically involve a confirmation step.');
    });

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
  </script>
</body>
</html>