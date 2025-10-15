<?php
// config/config.php

// Use the JWT secret key from environment variables
define('APP_ENCRYPTION_KEY', $_ENV['APP_ENCRYPTION_KEY'] ?? '3mLYL++bEuVmW6LyjbxVmc4+/Ll7WXqyiwZTgpKr+8o='); 

return [
    'APP_ENCRYPTION_KEY' => APP_ENCRYPTION_KEY,
    // ... other config settings
];