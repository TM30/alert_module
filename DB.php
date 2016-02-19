<?php

class DB {
    // The database connection
    protected static $connection;
    private  $localhost = '127.0.0.1';
    private  $username = 'root2';
    private  $password = '123456789';
    private $database = 'api';
    private static $queryResult;

    /**
     * Connect to the database
     *
     * @return bool false on failure / mysqli MySQLi object instance on success
     */
    public function connect() {
        // Try and connect to the database
        if(!isset(self::$connection)) {
            // Load configuration as an array. Use the actual location of your configuration file
            self::$connection = new \mysqli($this->localhost, $this->username, $this->password, $this->database);
        }
        if (self::$connection->connect_errno) {
            printf("Connect failed: %s\n", self::$connection->connect_error);
            exit();
        }
        return self::$connection;
    }

    /**
     * Query the database
     * @param $query (The query string)
     * @return mixed The result of the mysqli::query() function
     */
    public function query($query) {
        // Connect to the database
        $connection = $this ->connect();
        // Query the database
        $result = $connection->query($query);
        self::$queryResult = $result;
        return $this;
    }

    public function lastInsertId()
    {
        if(self::$queryResult)
            return $lastId = mysqli_insert_id(self::$connection);
    }

    /**
     * Fetch the last error from the database
     * @return string Database Query error message
     */
    public function error() {
        $connection = $this -> connect();
        return $connection -> error;
    }

    /**
     * This function accepts a query and fetches  its associated rows.
     * @return array
     */
    public function fetchAssoc()
    {
        $results = array();
        if($this->numberOfResult()){
            while($row = self::$queryResult->fetch_assoc()){
                $results[] = $row;
            }
        }
        return $results;
    }

    public function fetchAll()
    {
        if($this->numberOfResult())
            return self::$queryResult->fetch_all();
    }

    /**
     * This function fetches each row as an object.
     * @return ArrayObject
     */
    public function fetchArrayAsObject()
    {
        $arrayObject = new \ArrayObject();
        if($this->numberOfResult()){
            while($row = self::$queryResult->fetch_object()){
                $arrayObject->append($row);
            }
            return $arrayObject->getIterator();
        }
    }


    /**
     * This function fetches a single row
     * @return array
     */
    public function fetchObject()
    {
        if($this->numberOfResult()) {
            return self::$queryResult->fetch_object();
        }
    }

    /**
     * This function fetches a single row
     * @return array
     */
    public function fetchRow()
    {
        if($this->numberOfResult()) {
            return self::$queryResult->fetch_assoc();
        }
    }

    /**
     * @param string $column
     */
    public function fetchFlagColumnWithId($column = "flag")
    {
        if($this->numberOfResult()){
            while($row = self::$queryResult->fetch_assoc()){
                $result[] = $row["id"];
                $result[] = $row[$column];
            }
            return $result;
        }
        return null;
    }

    /**
     * Checks if there are results for this  query;
     * @return bool
     */
    public function numberOfResult()
    {
        $numResult = false;
        if(isset(self::$queryResult)) {
            if (self::$queryResult->num_rows > 0) {
                $numResult = true;
            }
        }
        return $numResult;
    }

    /**
     * Quote and escape value for use in a database query
     * @param string $value The value to be quoted and escaped
     * @return string The quoted and escaped string
     */
    public function escapeString($value) {
        $connection = $this -> connect();
        return $connection ->real_escape_string($value);
    }

    public function getConnection()
    {
        return self::$connection;
    }

    public function getDatabaseName()
    {
        return $this->database;
    }

    public function getUsername()
    {
        return $this->username;
    }
}
