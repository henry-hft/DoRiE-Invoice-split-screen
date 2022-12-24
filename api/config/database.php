<?php
class Database
{
    private $dbFile  = "db/dorie.db";
    public $conn;

    // get the database connection
    public function getConnection()
    {
        $this->conn = null;
      
        try {
            $this->conn = new PDO("sqlite:" . $this->dbFile);
			$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>