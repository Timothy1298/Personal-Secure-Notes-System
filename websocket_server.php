<?php
require __DIR__ . '/vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Core\WebSocketServer;
use Core\Database;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Get database connection
$db = Database::getInstance();

// Create WebSocket server
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new WebSocketServer($db)
        )
    ),
    8080
);

echo "WebSocket server started on port 8080\n";
echo "Press Ctrl+C to stop the server\n";

$server->run();
