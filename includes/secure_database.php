<?php
/**
 * Secure Database Connection and Query Handler
 * Production-ready database security and SQL injection prevention
 */

class SecureDatabase {
    private PDO $pdo;
    private array $config;
    private int $queryCount = 0;
    private array $queryLog = [];
    
    public function __construct(array $config) {
        $this->config = $config;
        $this->connect();
    }
    
    private function connect(): void {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $this->config['host'],
                $this->config['port'] ?? '3306',
                $this->config['name'],
                $this->config['charset'] ?? 'utf8mb4'
            );
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false, // Critical for SQL injection prevention
                PDO::ATTR_PERSISTENT => false, // Don't use persistent connections in production
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true,
                PDO::MYSQL_ATTR_MULTI_STATEMENTS => false, // Prevent multiple statements
            ];
            
            $this->pdo = new PDO($dsn, $this->config['user'], $this->config['pass'], $options);
            
            // Set secure MySQL session variables
            $this->pdo->exec("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
            
        } catch (PDOException $e) {
            $this->handleConnectionError($e);
        }
    }
    
    private function handleConnectionError(PDOException $e): void {
        // Log the error with sensitive info removed
        $safeMessage = str_replace($this->config['pass'], '[REDACTED]', $e->getMessage());
        error_log("Database connection failed: {$safeMessage}");
        
        // Don't expose database details in production
        if (!($this->config['debug'] ?? false)) {
            throw new RuntimeException('Database service unavailable');
        } else {
            throw $e;
        }
    }
    
    /**
     * Execute a prepared statement with automatic parameter binding
     */
    public function execute(string $query, array $params = []): PDOStatement {
        $this->queryCount++;
        $startTime = microtime(true);
        
        try {
            $stmt = $this->pdo->prepare($query);
            
            // Bind parameters with proper type checking
            foreach ($params as $key => $value) {
                if (is_int($key)) {
                    // Positional parameter
                    $stmt->bindValue($key + 1, $value, $this->getParamType($value));
                } else {
                    // Named parameter
                    $stmt->bindValue($key, $value, $this->getParamType($value));
                }
            }
            
            $stmt->execute();
            
            // Log query for debugging (in development only)
            if ($this->config['debug'] ?? false) {
                $executionTime = microtime(true) - $startTime;
                $this->queryLog[] = [
                    'query' => $query,
                    'params' => $this->sanitizeParamsForLog($params),
                    'time' => $executionTime,
                    'rows' => $stmt->rowCount()
                ];
            }
            
            return $stmt;
            
        } catch (PDOException $e) {
            $this->handleQueryError($e, $query, $params);
        }
    }
    
    /**
     * Get a single row
     */
    public function fetch(string $query, array $params = []): ?array {
        $stmt = $this->execute($query, $params);
        $result = $stmt->fetch();
        return $result !== false ? $result : null;
    }
    
    /**
     * Get multiple rows
     */
    public function fetchAll(string $query, array $params = []): array {
        $stmt = $this->execute($query, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get a single value
     */
    public function fetchColumn(string $query, array $params = [], int $column = 0): mixed {
        $stmt = $this->execute($query, $params);
        return $stmt->fetchColumn($column);
    }
    
    /**
     * Insert data and return last insert ID
     */
    public function insert(string $table, array $data): int {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $query = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->quoteIdentifier($table),
            implode(', ', array_map([$this, 'quoteIdentifier'], $columns)),
            implode(', ', $placeholders)
        );
        
        $this->execute($query, array_values($data));
        return (int)$this->pdo->lastInsertId();
    }
    
    /**
     * Update data
     */
    public function update(string $table, array $data, array $where, array $whereParams = []): int {
        $setParts = [];
        $params = [];
        
        foreach ($data as $column => $value) {
            $setParts[] = $this->quoteIdentifier($column) . ' = ?';
            $params[] = $value;
        }
        
        $query = sprintf(
            "UPDATE %s SET %s WHERE %s",
            $this->quoteIdentifier($table),
            implode(', ', $setParts),
            $where
        );
        
        $params = array_merge($params, $whereParams);
        $stmt = $this->execute($query, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Delete data
     */
    public function delete(string $table, array $where, array $params = []): int {
        $query = sprintf("DELETE FROM %s WHERE %s", $this->quoteIdentifier($table), $where);
        $stmt = $this->execute($query, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction(): bool {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit(): bool {
        return $this->pdo->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback(): bool {
        return $this->pdo->rollBack();
    }
    
    /**
     * Check if table exists
     */
    public function tableExists(string $table): bool {
        $query = "SHOW TABLES LIKE ?";
        $result = $this->fetch($query, [$table]);
        return !empty($result);
    }
    
    /**
     * Get table structure
     */
    public function getTableStructure(string $table): array {
        $query = "DESCRIBE " . $this->quoteIdentifier($table);
        return $this->fetchAll($query);
    }
    
    /**
     * Escape identifiers (table names, column names)
     */
    private function quoteIdentifier(string $identifier): string {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
    
    /**
     * Determine PDO parameter type
     */
    private function getParamType(mixed $value): int {
        if (is_int($value)) {
            return PDO::PARAM_INT;
        } elseif (is_bool($value)) {
            return PDO::PARAM_BOOL;
        } elseif (is_null($value)) {
            return PDO::PARAM_NULL;
        } else {
            return PDO::PARAM_STR;
        }
    }
    
    /**
     * Handle query errors with proper logging
     */
    private function handleQueryError(PDOException $e, string $query, array $params): void {
        $errorInfo = [
            'query' => $query,
            'params' => $this->sanitizeParamsForLog($params),
            'error' => $e->getMessage(),
            'code' => $e->getCode()
        ];
        
        error_log("Database query error: " . json_encode($errorInfo));
        
        // Don't expose SQL details in production
        if (!($this->config['debug'] ?? false)) {
            throw new RuntimeException('Database operation failed');
        } else {
            throw $e;
        }
    }
    
    /**
     * Sanitize parameters for logging (remove sensitive data)
     */
    private function sanitizeParamsForLog(array $params): array {
        $sensitive = ['password', 'pass', 'secret', 'token', 'key'];
        $sanitized = [];
        
        foreach ($params as $key => $value) {
            if (is_string($key) && in_array(strtolower($key), $sensitive)) {
                $sanitized[$key] = '[REDACTED]';
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Get query statistics
     */
    public function getQueryStats(): array {
        return [
            'query_count' => $this->queryCount,
            'queries' => $this->queryLog,
            'total_time' => array_sum(array_column($this->queryLog, 'time'))
        ];
    }
    
    /**
     * Get the PDO instance (for advanced usage)
     */
    public function getPdo(): PDO {
        return $this->pdo;
    }
    
    /**
     * Validate user input for SQL injection attempts
     */
    public function validateInput(string $input): bool {
        $suspiciousPatterns = [
            '/(\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|UNION|SCRIPT)\b)/i',
            '/(--|\/\*|\*\/|;|\'|\"|`)/',
            '/(\b(OR|AND)\s+\d+\s*=\s*\d+)/i',
            '/(\b(OR|AND)\s+\'\w+\'\s*=\s*\'\w+\')/i'
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Escape string for safe SQL (use only when prepared statements aren't possible)
     */
    public function escape(string $string): string {
        return $this->pdo->quote($string);
    }
}

/**
 * Initialize secure database connection
 */
function init_secure_database(): SecureDatabase {
    $config = require __DIR__ . '/config.php';
    return new SecureDatabase($config['db']);
}

/**
 * Secure query builder for complex queries
 */
class QueryBuilder {
    private SecureDatabase $db;
    private string $table;
    private array $where = [];
    private array $orderBy = [];
    private ?int $limit = null;
    private ?int $offset = null;
    private array $params = [];
    
    public function __construct(SecureDatabase $db, string $table) {
        $this->db = $db;
        $this->table = $table;
    }
    
    public function where(string $column, string $operator, mixed $value): self {
        $this->where[] = "`{$column}` {$operator} ?";
        $this->params[] = $value;
        return $this;
    }
    
    public function whereIn(string $column, array $values): self {
        $placeholders = str_repeat('?,', count($values) - 1) . '?';
        $this->where[] = "`{$column}` IN ({$placeholders})";
        $this->params = array_merge($this->params, $values);
        return $this;
    }
    
    public function orderBy(string $column, string $direction = 'ASC'): self {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $this->orderBy[] = "`{$column}` {$direction}";
        return $this;
    }
    
    public function limit(int $limit, ?int $offset = null): self {
        $this->limit = $limit;
        $this->offset = $offset;
        return $this;
    }
    
    public function get(): array {
        $query = "SELECT * FROM `{$this->table}`";
        
        if (!empty($this->where)) {
            $query .= " WHERE " . implode(' AND ', $this->where);
        }
        
        if (!empty($this->orderBy)) {
            $query .= " ORDER BY " . implode(', ', $this->orderBy);
        }
        
        if ($this->limit !== null) {
            $query .= " LIMIT {$this->limit}";
            if ($this->offset !== null) {
                $query .= " OFFSET {$this->offset}";
            }
        }
        
        return $this->db->fetchAll($query, $this->params);
    }
    
    public function first(): ?array {
        $this->limit(1);
        $results = $this->get();
        return !empty($results) ? $results[0] : null;
    }
    
    public function count(): int {
        $query = "SELECT COUNT(*) as count FROM `{$this->table}`";
        
        if (!empty($this->where)) {
            $query .= " WHERE " . implode(' AND ', $this->where);
        }
        
        return (int)$this->db->fetchColumn($query, $this->params);
    }
}

// Initialize global database instance
$secure_db = init_secure_database();
