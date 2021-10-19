<?php
class ERS_Database
{
    /**
     * connect to db and return mysqli db-connection object
     */
    static function dbConnect()
    {
        $mysqli = new mysqli('192.168.xx.xx', 'xxxx', 'xxxx', 'xxxx', 3306);


        /* check connection */
        if ($mysqli->connect_errno) {
            printf("Connect failed: %s\n", $mysqli->connect_error);
            throw new Exception("Connot connect to db");
            exit();
        }
        mysqli_set_charset($mysqli, "utf8");

        return $mysqli;
    }

    /**
     * Run any query on db-connection, returns error or true
     */
    static function run_simple_query($db, $query)
    {
        if ($db->query($query) === TRUE) {
        } else {
            error_log("Error in query $query");
            return "Error: " . $query . "<br>" . $db->error;
        }
        return true;
    }

    /**
     * run query and return single datarow as object
     * 
     */
    static function run_queryresult($db, $query)
    {
        $dbResult = $db->query($query);
        $data = $dbResult->fetch_object();

        return $data;
    }

    /**
     * run query and return all datarows as array
     */
    static function run_queryarray($db, $query)
    {
        $dbResult = $db->query($query);

        $data = $dbResult->fetch_all(MYSQLI_ASSOC);

        return $data;
    }
}
