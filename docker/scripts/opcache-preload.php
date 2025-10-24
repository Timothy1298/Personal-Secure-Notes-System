<?php
/**
 * OPcache Preload Script for Personal Notes System
 * This script preloads commonly used classes to improve performance
 */

// Preload core classes
opcache_compile_file(__DIR__ . '/../../core/Database.php');
opcache_compile_file(__DIR__ . '/../../core/Session.php');
opcache_compile_file(__DIR__ . '/../../core/Security.php');
opcache_compile_file(__DIR__ . '/../../core/CSRF.php');
opcache_compile_file(__DIR__ . '/../../core/Logger.php');
opcache_compile_file(__DIR__ . '/../../core/Cache.php');
opcache_compile_file(__DIR__ . '/../../core/ThemeManager.php');
opcache_compile_file(__DIR__ . '/../../core/KeyboardShortcuts.php');
opcache_compile_file(__DIR__ . '/../../core/RichTextEditor.php');

// Preload models
opcache_compile_file(__DIR__ . '/../../app/Models/User.php');
opcache_compile_file(__DIR__ . '/../../app/Models/NotesModel.php');
opcache_compile_file(__DIR__ . '/../../app/Models/TasksModel.php');
opcache_compile_file(__DIR__ . '/../../app/Models/TagsModel.php');
opcache_compile_file(__DIR__ . '/../../app/Models/SettingsModel.php');

// Preload controllers
opcache_compile_file(__DIR__ . '/../../app/Controllers/AuthController.php');
opcache_compile_file(__DIR__ . '/../../app/Controllers/DashboardController.php');
opcache_compile_file(__DIR__ . '/../../app/Controllers/NotesController.php');
opcache_compile_file(__DIR__ . '/../../app/Controllers/TasksController.php');
opcache_compile_file(__DIR__ . '/../../app/Controllers/TagsController.php');
opcache_compile_file(__DIR__ . '/../../app/Controllers/SettingsController.php');
opcache_compile_file(__DIR__ . '/../../app/Controllers/SearchController.php');
opcache_compile_file(__DIR__ . '/../../app/Controllers/ApiController.php');

// Preload view class
opcache_compile_file(__DIR__ . '/../../app/Views/View.php');

// Preload vendor autoloader
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    opcache_compile_file(__DIR__ . '/../../vendor/autoload.php');
}
