// Script to suppress Tailwind CSS production warning
// This script can be included in your HTML files to suppress the CDN warning

(function() {
    // Override console.warn to filter out Tailwind CSS production warnings
    const originalWarn = console.warn;
    console.warn = function(...args) {
        const message = args.join(' ');
        if (message.includes('cdn.tailwindcss.com should not be used in production')) {
            return; // Suppress this specific warning
        }
        originalWarn.apply(console, args);
    };
    
    // Alternative: Configure Tailwind to suppress warnings
    if (typeof tailwind !== 'undefined') {
        tailwind.config = tailwind.config || {};
        tailwind.config.safelist = [];
    }
})();
