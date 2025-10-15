<footer class="bg-gray-800 text-gray-400 py-3 mt-auto border-t border-gray-700">
    <div class="container mx-auto px-6 flex flex-col sm:flex-row justify-between items-center text-xs space-y-2 sm:space-y-0">
        
        <!-- Branding/App Name and Version -->
        <div class="flex items-center space-x-2">
            <i class="fas fa-lock text-indigo-400"></i>
            <span class="font-semibold text-white">SecureNote Pro</span>
            <span class="text-gray-500">| v1.0.2</span>
        </div>

        <!-- Copyright Information -->
        <p class="text-gray-400 text-center">
            &copy; <?= date('Y') ?> Timothy Kuria. All Rights Reserved.
        </p>

        <!-- Minimal Links (Hidden on very small screens for cleanliness) -->
        <div class="hidden sm:flex space-x-4">
            <a href="/privacy" class="hover:text-white transition-colors">Privacy</a>
            <span class="text-gray-600">|</span>
            <a href="/terms" class="hover:text-white transition-colors">Terms</a>
            <span class="text-gray-600">|</span>
            <a href="/support" class="hover:text-white transition-colors">Support</a>
        </div>
    </div>
</footer>
