<?php
use Core\Session;
$username = Session::get('username') ?? 'User';
?>

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
            <li><a href="/settings" class="flex items-center p-3 rounded hover:bg-gray-700"><i class="fas fa-cog mr-3"></i>Settings</a></li>
        </ul>
    </nav>
</aside>