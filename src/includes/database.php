<?php
/**
 * Edutrack computer training college
 * Database Connection Handler
 */

class Database {
    
    private static $instance = null;
    private $pdo;
    private $config;
    
    /**
     * Constructor - Private to prevent direct instantiation
     */
    private function __construct() {
        $this->loadConfig();
        $this->connect();
    }
    
    /**
     * Get database instance (Singleton pattern)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Load database configuration
     */
    private function loadConfig() {
        $configFile = dirname(__DIR__, 2) . '/config/database.php';
        
        if (!file_exists($configFile)) {
            throw new Exception("Database configuration file not found");
        }
        
        $this->config = require $configFile;
    }
    
    /**
     * Connect to database
     */
    private function connect() {
        try {
            $connection = $this->config['connections'][$this->config['default']];
            
            $dsn = sprintf(
                "%s:host=%s;port=%s;dbname=%s;charset=%s",
                $connection['driver'],
                $connection['host'],
                $connection['port'],
                $connection['database'],
                $connection['charset']
            );
            
            $this->pdo = new PDO(
                $dsn,
                $connection['username'],
                $connection['password'],
                $connection['options']
            );
            
        } catch (PDOException $e) {
            // Log error
            $this->logError('Database connection failed: ' . $e->getMessage());
            
            // Show friendly error in development, hide in production
            if (getenv('APP_ENV') === 'development' || getenv('APP_DEBUG') === 'true') {
                die("Database Connection Error: " . $e->getMessage());
            } else {
                die("Unable to connect to database. Please contact support.");
            }
        }
    }
    
    /**
     * Get PDO instance
     */
    public function getConnection() {
        return $this->pdo;
    }
    
    /**
     * Execute a query and return results
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $this->logError('Query Error: ' . $e->getMessage() . ' | SQL: ' . $sql);
            throw $e;
        }
    }
    
    /**
     * Fetch all records
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Fetch single record
     */
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Fetch single column value
     */
    public function fetchColumn($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn();
    }
    
    /**
     * Insert record and return last insert ID
     */
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        $this->query($sql, $data);
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Update records
     */
    public function update($table, $data, $where, $whereParams = []) {
    $setParts = [];
    $params = [];
    
    // Build SET clause with positional parameters
    $index = 1;
    foreach ($data as $column => $value) {
        $setParts[] = "{$column} = ?";
        $params[] = $value;
    }
    $setClause = implode(', ', $setParts);
    
    // Add WHERE parameters
    foreach ($whereParams as $param) {
        $params[] = $param;
    }
    
    $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
    $stmt = $this->query($sql, $params);
    
    return $stmt->rowCount();
}
    
    /**
     * Delete records
     */
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Count records
     */
    public function count($table, $where = '1=1', $params = []) {
        $sql = "SELECT COUNT(*) FROM {$table} WHERE {$where}";
        return (int) $this->fetchColumn($sql, $params);
    }
    
    /**
     * Check if record exists
     */
    public function exists($table, $where, $params = []) {
        return $this->count($table, $where, $params) > 0;
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->pdo->rollBack();
    }
    
    /**
     * Get last insert ID
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Escape string for LIKE queries
     */
    public function escapeLike($string) {
        return str_replace(['%', '_'], ['\\%', '\\_'], $string);
    }
    
    /**
     * Log database errors
     */
    private function logError($message) {
        $logDir = dirname(__DIR__, 2) . '/storage/logs';
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . '/database.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}\n";
        
        error_log($logMessage, 3, $logFile);
    }
    
    /**
     * Test database connection
     */
    public static function testConnection() {
        try {
            $db = self::getInstance();
            $db->query("SELECT 1");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get database statistics
     */
    public function getStats() {
        $stats = [
            'tables' => [],
            'total_records' => 0,
        ];
        
        // Get all tables
        $tables = $this->fetchAll("SHOW TABLES");
        
        foreach ($tables as $table) {
            $tableName = array_values($table)[0];
            $count = $this->count($tableName);
            
            $stats['tables'][$tableName] = $count;
            $stats['total_records'] += $count;
        }
        
        return $stats;
    }
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Initialize database connection
try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
} catch (Exception $e) {
    die("Database initialization failed: " . $e->getMessage());
}