<?php

class DB
{
    public static $instance = null;
    private $connection;
    private $driver;
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $charset;
    private $options;

    private function __construct()
    {
        $config = require('config.php');

        $this->driver   = $config['driver'];
        $this->host     = $config['host'];
        $this->db_name  = $config['db_name'];
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->charset  = $config['charset'];
        $this->options  = $config['options'];

        try {
           $this->connection = new PDO("$this->driver:host=$this->host;dbname=$this->db_name;charset=$this->charset", $this->username, $this->password, $this->options);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    private function queryErrCheck($query)
    {
        $error = $query->errorInfo();
        if ($error[0] !== PDO::ERR_NONE)
        {
            echo "Error query: " . $error[2] . "\n";
            exit();
        }

        return TRUE;
    }

    public static function getInstance()
    {
        if (self::$instance == null) { self::$instance = new DB(); }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    // ------------------FETCHES-------------------
    public function fetch($table_name, $mode = PDO::FETCH_ASSOC)
    {
        $query = "SELECT * FROM `$table_name`";
        $statement = $this->connection->prepare($query);
        $statement->execute();
        $this->queryErrCheck($statement);

        return $statement->fetch($mode);
    }
    public function fetchAll($table_name, $mode = PDO::FETCH_ASSOC)
    {
        $query = "SELECT * FROM `$table_name`";
        $statement = $this->connection->prepare($query);
        $statement->execute();
        $this->queryErrCheck($statement);

        return $statement->fetchAll($mode);
    }

    //fetch rows by features. For example: $kevin = fetchBy("users", ["username" => "kevin"], PDO::FETCH_ASSOC);
    public function fetchBy($table_name, $features = [], $mode = PDO::FETCH_ASSOC)
    {
        $query = "SELECT * FROM `$table_name` WHERE ";

        if (!empty($features))
        {
            $placeholders = [];
            foreach ($features as $key => $value)
            {
                $placeholders[] = "`$key` = :$key";
            }
            $query .= implode(' AND ', $placeholders);
        }

        $statement = $this->connection->prepare($query);
        $statement->execute($features);
        $this->queryErrCheck($statement);

        return $statement->fetchAll($mode);
    }

    public function fetchById($table_name, $id, $mode = PDO::FETCH_ASSOC)
    {
        $query = "SELECT * FROM `$table_name` WHERE `id` = '$id'";
        $statement = $this->connection->prepare($query);
        $statement->execute();
        $this->queryErrCheck($statement);

        return $statement->fetch($mode);
    }

    public function fetchOneBy($table_name, $features = [], $mode = PDO::FETCH_ASSOC)
    {
        $query = "SELECT * FROM `$table_name` WHERE ";

        if (!empty($features))
        {
            $placeholders = [];
            foreach ($features as $key => $value)
            {
                $placeholders[] = "`$key` = :$key";
            }
            $query .= implode(' AND ', $placeholders);
        }
        /*$query .= " LIMIT 1";*/

        $statement = $this->connection->prepare($query);
        $statement->execute($features);
        $this->queryErrCheck($statement);

        return $statement->fetch($mode);
    }

    //------------------ FETCHES END----------------

    // ----------------- INSERTES ------------------------

    public function insert($table_name, $data, $mode = PDO::FETCH_ASSOC)
    {
        $query = "INSERT INTO `$table_name` (";

        if (!empty($data))
        {
            $keys = [];
            $values = [];
            foreach ($data as $key => $value)
            {
                $keys[] = "`$key`";
                $values[] = "'$value'";
            }

            $query.= implode(',', $keys).") ";
            $query.= "VALUES (".implode(',', $values).") ";
        }

        $statement = $this->connection->prepare($query);
        $statement->execute();
        $this->queryErrCheck($statement);

        return TRUE;
    }
    // ------------------- INSERTES END -----------------------

    // ----------------------- UPDATES ----------------------
    public function update($table_name, $data, $features = [], $mode = PDO::FETCH_ASSOC)
    {
        if (!empty($data) && !empty($features))
        {
            $query = "UPDATE `$table_name` SET ";

            $placeholdersData = [];
            foreach ($data as $key => $value)
            {
                $placeholdersData[] = "`$key` = '$value'";
            }

            $placeholdersFeatures = [];
            foreach ($features as $key => $value)
            {
                $placeholdersFeatures[] = "`$key` = '$value'";
            }

            $query .= implode(',', $placeholdersData);
            $query .= " WHERE ".implode(' AND ', $placeholdersFeatures);
        }

        $statement = $this->connection->prepare($query);
        $statement->execute();
        $this->queryErrCheck($statement);

        return TRUE;
    }
    // ----------------------- UPDATES END ----------------------

    //---------------------------- DELETES---------------------
    public function delete($table_name, $features = [], $mode = PDO::FETCH_ASSOC)
    {
        $query = "DELETE FROM `$table_name` WHERE ";
        if (!empty($features))
        {
            $placeholders = [];
            foreach ($features as $key => $value)
            {
                $placeholders[] = "`$key` = '$value'";
            }

            $query .= implode(' AND ', $placeholders);
        }

        $statement = $this->connection->prepare($query);
        $statement->execute();
        $this->queryErrCheck($statement);

        return TRUE;
    }

    public function deleteById($table_name, $id, $mode = PDO::FETCH_ASSOC)
    {
        $query = "DELETE FROM `$table_name` WHERE `id` = '$id'";

        $statement = $this->connection->prepare($query);
        $statement->execute();
        $this->queryErrCheck($statement);

        return TRUE;
    }
    // --------------------------DELETES END -----------------------

/*    public function test()
    {
        $statement = $this->connection->prepare("SELECT * FROM `users`");
        $statement->execute();

        $error = $statement->errorInfo();
        if ($error[0] !== PDO::ERR_NONE) { die("Error query: " . $error[2] . "\n"); }

        $rows = $statement->fetchAll();

        echo "<pre>";
        print_r($rows);
        echo "</pre>";
    }*/
}