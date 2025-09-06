<?php
use Core\Session;
$username = Session::get('username') ?? 'User';
?>

<header class="h-16 bg-white shadow-md flex items-center justify-between px-6">
    <div class="text-xl font-semibold">Secure Notes</div>
    <div class="flex items-center space-x-4">
        <button class="relative">
            <i class="fas fa-bell text-gray-600 text-lg"></i>
            <span class="absolute -top-1 -right-1 text-xs bg-red-500 text-white rounded-full px-1">3</span>
        </button>
        <div class="relative group">
  <button id="profileBtn" class="flex items-center space-x-2">
    <i class="fas fa-user-circle text-2xl text-gray-600"></i>
    <span class="text-gray-700 font-medium"><?= $username ?></span>
    <i class="fas fa-chevron-down text-gray-600"></i>
  </button>

  <!-- Dropdown -->
  <div id="profileMenu"
       class="absolute right-0 mt-2 w-48 bg-white border rounded shadow-lg 
              opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto 
              transition-opacity">
    <a href="/profile" class="block px-4 py-2 hover:bg-gray-100">Profile</a>
    <a href="/settings" class="block px-4 py-2 hover:bg-gray-100">Settings</a>
    <a href="/logout" class="block px-4 py-2 hover:bg-gray-100">Logout</a>
  </div>
</div>

    </div>
</header>
