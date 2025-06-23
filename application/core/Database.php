<?php
/**
 * Database Class - Singleton Pattern for MySQL Connection
 */

class Database
{
    private static $instance = null;
    private $connection;
    private $config;

    private function __construct($config)
    {
        $this->config = $config;
        $this->connect();
    }

    public static function getInstance($config)
    {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    private function connect()
    {
        try {
            $this->connection = new mysqli(
                $this->config['hostname'],
                $this->config['username'],
                $this->config['password'],
                $this->config['database']
            );

            if ($this->connection->connect_error) {
                throw new Exception("Connection failed: " . $this->connection->connect_error);
            }

            $this->connection->set_charset($this->config['charset']);
            
        } catch (Exception $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function query($sql, $params = [])
    {
        $stmt = $this->connection->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->connection->error);
        }

        if (!empty($params)) {
            $types = str_repeat('s', count($params)); // Assuming all strings for simplicity
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $stmt;
    }

    public function fetchAll($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }

    public function fetchOne($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        return $data;
    }

    public function execute($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        $affectedRows = $stmt->affected_rows;
        $insertId = $this->connection->insert_id;
        $stmt->close();
        
        return [
            'affected_rows' => $affectedRows,
            'insert_id' => $insertId
        ];
    }

    public function beginTransaction()
    {
        $this->connection->autocommit(false);
    }

    public function commit()
    {
        $this->connection->commit();
        $this->connection->autocommit(true);
    }    public function rollback()
    {
        $this->connection->rollback();
        $this->connection->autocommit(true);
    }

    public function escape($string)
    {
        return $this->connection->real_escape_string($string);
    }

    // Prevent cloning
    private function __clone() {}

    // Prevent unserialization
    public function __wakeup() {}
}
