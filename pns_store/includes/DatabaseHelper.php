<?php
/**
 * Database Helper Class
 * Provides standardized database operations and error handling
 */
class DatabaseHelper {
    private $pdo;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Execute a SELECT query with error handling
     * @param string $query The SQL query
     * @param array $params Parameters for the query
     * @param bool $single Return a single row if true
     * @return array|object|null Query results
     * @throws Exception
     */
    public function select($query, $params = [], $single = false) {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            
            if ($single) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error in select: " . $e->getMessage());
            throw new Exception("Database error occurred. Please try again.");
        }
    }
    
    /**
     * Execute an INSERT query with error handling
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @return int Last insert ID
     * @throws Exception
     */
    public function insert($table, $data) {
        try {
            $columns = implode(', ', array_keys($data));
            $values = implode(', ', array_fill(0, count($data), '?'));
            
            $query = "INSERT INTO {$table} ({$columns}) VALUES ({$values})";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(array_values($data));
            
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Database error in insert: " . $e->getMessage());
            throw new Exception("Database error occurred. Please try again.");
        }
    }
    
    /**
     * Execute an UPDATE query with error handling
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @param array $where Associative array of conditions
     * @return int Number of affected rows
     * @throws Exception
     */
    public function update($table, $data, $where) {
        try {
            $set = implode(' = ?, ', array_keys($data)) . ' = ?';
            $whereClause = implode(' = ? AND ', array_keys($where)) . ' = ?';
            
            $query = "UPDATE {$table} SET {$set} WHERE {$whereClause}";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(array_merge(array_values($data), array_values($where)));
            
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Database error in update: " . $e->getMessage());
            throw new Exception("Database error occurred. Please try again.");
        }
    }
    
    /**
     * Execute a DELETE query with error handling
     * @param string $table Table name
     * @param array $where Associative array of conditions
     * @return int Number of affected rows
     * @throws Exception
     */
    public function delete($table, $where) {
        try {
            $whereClause = implode(' = ? AND ', array_keys($where)) . ' = ?';
            
            $query = "DELETE FROM {$table} WHERE {$whereClause}";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(array_values($where));
            
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Database error in delete: " . $e->getMessage());
            throw new Exception("Database error occurred. Please try again.");
        }
    }
    
    /**
     * Begin a transaction
     */
    public function beginTransaction() {
        $this->pdo->beginTransaction();
    }
    
    /**
     * Commit a transaction
     */
    public function commit() {
        $this->pdo->commit();
    }
    
    /**
     * Rollback a transaction
     */
    public function rollBack() {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }
    
    /**
     * Execute a custom query with error handling
     * @param string $query The SQL query
     * @param array $params Parameters for the query
     * @return PDOStatement
     * @throws Exception
     */
    public function query($query, $params = []) {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database error in custom query: " . $e->getMessage());
            throw new Exception("Database error occurred. Please try again.");
        }
    }
    
    /**
     * Get the PDO instance
     * @return PDO
     */
    public function getPDO() {
        return $this->pdo;
    }
}