<?php
namespace Core\Database;

use PDO;
use Exception;

class ReplicationManager {
    private $masterDb;
    private $slaveConfigs;
    private $slaveConnections = [];
    private $currentSlaveIndex = 0;

    public function __construct(PDO $masterDb, array $slaveConfigs) {
        $this->masterDb = $masterDb;
        $this->slaveConfigs = $slaveConfigs;
        $this->initializeSlaveConnections();
    }

    private function initializeSlaveConnections(): void {
        foreach ($this->slaveConfigs as $config) {
            try {
                $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
                $pdo = new PDO($dsn, $config['username'], $config['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
                $this->slaveConnections[] = $pdo;
            } catch (Exception $e) {
                error_log("Failed to connect to slave database: " . $e->getMessage());
            }
        }
    }

    public function getMasterConnection(): PDO {
        return $this->masterDb;
    }

    public function getSlaveConnection(): ?PDO {
        if (empty($this->slaveConnections)) {
            return $this->masterDb; // Fallback to master if no slaves
        }

        // Round-robin selection
        $connection = $this->slaveConnections[$this->currentSlaveIndex];
        $this->currentSlaveIndex = ($this->currentSlaveIndex + 1) % count($this->slaveConnections);
        
        return $connection;
    }

    public function getReplicationStatus(): array {
        $status = [];
        
        try {
            // Get master status
            $stmt = $this->masterDb->query("SHOW MASTER STATUS");
            $masterStatus = $stmt->fetch(PDO::FETCH_ASSOC);
            $status['master'] = $masterStatus;

            // Get slave status for each slave
            foreach ($this->slaveConnections as $index => $slave) {
                try {
                    $stmt = $slave->query("SHOW SLAVE STATUS");
                    $slaveStatus = $stmt->fetch(PDO::FETCH_ASSOC);
                    $status['slaves'][$index] = $slaveStatus;
                } catch (Exception $e) {
                    $status['slaves'][$index] = ['error' => $e->getMessage()];
                }
            }
        } catch (Exception $e) {
            $status['error'] = $e->getMessage();
        }

        return $status;
    }

    public function checkSlaveLag(): array {
        $lagInfo = [];
        
        foreach ($this->slaveConnections as $index => $slave) {
            try {
                $stmt = $slave->query("SHOW SLAVE STATUS");
                $status = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($status) {
                    $lagInfo[$index] = [
                        'seconds_behind_master' => $status['Seconds_Behind_Master'] ?? null,
                        'slave_io_running' => $status['Slave_IO_Running'] ?? 'No',
                        'slave_sql_running' => $status['Slave_SQL_Running'] ?? 'No',
                        'last_error' => $status['Last_Error'] ?? null,
                    ];
                }
            } catch (Exception $e) {
                $lagInfo[$index] = ['error' => $e->getMessage()];
            }
        }

        return $lagInfo;
    }

    public function promoteSlaveToMaster(int $slaveIndex): bool {
        if (!isset($this->slaveConnections[$slaveIndex])) {
            return false;
        }

        try {
            $slave = $this->slaveConnections[$slaveIndex];
            
            // Stop slave
            $slave->exec("STOP SLAVE");
            
            // Reset slave
            $slave->exec("RESET SLAVE ALL");
            
            return true;
        } catch (Exception $e) {
            error_log("Failed to promote slave to master: " . $e->getMessage());
            return false;
        }
    }
}