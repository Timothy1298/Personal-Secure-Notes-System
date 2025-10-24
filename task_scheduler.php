<?php
/**
 * Task Scheduler Script
 * 
 * This script should be run as a cron job to execute scheduled tasks.
 * Example cron entry: * * * * * /usr/bin/php /path/to/task_scheduler.php
 */

require __DIR__ . '/vendor/autoload.php';

use Core\Database;
use Core\Automation\ScheduledTasks;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Create database connection
$db = Database::getInstance();

// Initialize scheduled tasks service
$scheduledTasks = new ScheduledTasks($db);

// Get tasks ready for execution
$tasks = $scheduledTasks->getTasksReadyForExecution();

echo "[" . date('Y-m-d H:i:s') . "] Found " . count($tasks) . " tasks ready for execution\n";

foreach ($tasks as $task) {
    echo "[" . date('Y-m-d H:i:s') . "] Executing task: {$task['name']} (ID: {$task['id']})\n";
    
    try {
        $result = $scheduledTasks->executeTask($task['id']);
        
        if ($result['success']) {
            echo "[" . date('Y-m-d H:i:s') . "] Task executed successfully: {$task['name']}\n";
        } else {
            echo "[" . date('Y-m-d H:i:s') . "] Task execution failed: {$task['name']} - {$result['error']}\n";
        }
    } catch (Exception $e) {
        echo "[" . date('Y-m-d H:i:s') . "] Task execution error: {$task['name']} - " . $e->getMessage() . "\n";
    }
}

echo "[" . date('Y-m-d H:i:s') . "] Task scheduler completed\n";
