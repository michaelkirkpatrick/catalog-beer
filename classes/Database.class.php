<?php
class Database {

    // Properties
    private mysqli $mysqli;

    // Error Handling
    public bool $error = false;
    public string $errorMsg = '';

    // Recursion Guard
    private static bool $loggingError = false;

    function __construct(){
        // Use exception-based error handling so transient MySQL failures
        // (server gone away, connection refused, greeting packet errors)
        // surface as catchable mysqli_sql_exception instead of uncaught
        // fatal errors. PHP 8.1+ default; set explicitly for clarity.
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        // Establish Environment
        if(!defined('ENVIRONMENT')){
            $this->error = true;
            $this->errorMsg = 'Environment not set.';
            return;
        }

        if(ENVIRONMENT === 'staging'){
            $password = DB_PASSWORD_STAGING;
        }elseif(ENVIRONMENT === 'production'){
            $password = DB_PASSWORD_PRODUCTION;
        }else{
            $this->error = true;
            $this->errorMsg = 'Unknown environment.';
            return;
        }

        // Connect to Server
        try {
            $this->mysqli = new mysqli(DB_HOST, DB_USER, $password, DB_NAME);
            $this->mysqli->set_charset("utf8mb4");
        } catch (mysqli_sql_exception $e) {
            $this->error = true;
            $this->errorMsg = 'Database connection error.';
            $this->logQueryError('Database Connection Error: ' . $e->getCode() . ': ' . $e->getMessage(), '');
        }
    }

    // ----- Query -----
    public function query(string $sql, array $params = []): ?mysqli_result {
        if($this->error){
            return null;
        }

        // Prepare Statement
        try {
            $stmt = $this->mysqli->prepare($sql);
        } catch (mysqli_sql_exception $e) {
            $this->error = true;
            $this->errorMsg = 'Sorry, there was an internal error querying our database. I\'ve logged the error for our support team so they can diagnose and fix the issue.';
            $this->logQueryError('SQL Prepare Error: ' . $e->getMessage(), $sql);
            return null;
        }

        // Bind Parameters
        if(!empty($params)){
            $types = '';
            foreach($params as $param){
                if(is_int($param)){
                    $types .= 'i';
                }elseif(is_float($param)){
                    $types .= 'd';
                }elseif(is_string($param)){
                    $types .= 's';
                }else{
                    $types .= 'b';
                }
            }
            $stmt->bind_param($types, ...$params);
        }

        // Execute
        try {
            $stmt->execute();
        } catch (mysqli_sql_exception $e) {
            $this->error = true;
            $this->errorMsg = 'Sorry, there was an internal error querying our database. I\'ve logged the error for our support team so they can diagnose and fix the issue.';
            $this->logQueryError('SQL Execution Error: ' . $e->getMessage(), $sql);
            $stmt->close();
            return null;
        }

        // Get Result
        $result = $stmt->get_result();
        $stmt->close();

        // SELECT returns mysqli_result; INSERT/UPDATE/DELETE return false
        return $result !== false ? $result : null;
    }

    // ----- Get Insert ID -----
    public function getInsertId(): int {
        return $this->mysqli->insert_id;
    }

    // ----- Get Num Rows -----
    public function getNumRows(mysqli_result $result): int {
        return $result->num_rows;
    }

    // ----- Close -----
    public function close(): void {
        // Skip if connection never succeeded — $this->mysqli may be unusable
        if($this->error){
            return;
        }
        try {
            $this->mysqli->close();
        } catch (mysqli_sql_exception $e) {
            // Silently swallow close errors; nothing to recover here
        }
    }

    // ----- Log Query Error -----
    private function logQueryError(string $errorDetail, string $sql): void {
        // Prevent infinite recursion: Database error → LogError → Database error → ...
        if(self::$loggingError){
            return;
        }
        self::$loggingError = true;

        $errorLog = new LogError();
        $errorLog->errorNumber = 'C5';
        $errorLog->filename = 'Database.class.php';
        $errorLog->errorMsg = 'Query Error';
        $errorLog->badData = $errorDetail . ' | Query: ' . $sql;
        $errorLog->write();

        self::$loggingError = false;
    }
}
?>
