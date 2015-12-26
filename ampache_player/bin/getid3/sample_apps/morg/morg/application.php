<?php

// +----------------------------------------------------------------------+
// | PHP version 5.1.4                                                    |
// +----------------------------------------------------------------------+
// | Placed in public domain by Allan Hansen, 2003-2006.                  |
// | Share and enjoy!                                                     |
// +----------------------------------------------------------------------+
// | Updates and other scripts by Allan Hansen here:                      |  
// |     http://www.artemis.dk/php/                                       |
// +----------------------------------------------------------------------+
// | application.php                                                      |
// | Web Application Framework                                            |
// +----------------------------------------------------------------------+
// | Authors: Allan Hansen <ah@artemis.dk>                                |
// +----------------------------------------------------------------------+
//
// $Id: application.php,v 1.4 2007/01/03 16:15:59 ah Exp $




/**
* Web Application Framework (superclass).
*/

class application
{
    // HTML header values
    protected $doctype = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
    protected $charset = "ISO-8859-1";
    protected $css     = "/main.css";           // array also supported
    protected $meta    = array ();              // array of array (type, key, value) - type is "http-equiv" or "name"
    protected $add_head;                        // additional html for <head>...</head>
    protected $body_id;                         // id of <body>
    
    // Database parameters
    protected $db_handler   = "mysql";          // mysql, postgresql or firebird
    protected $db_server    = "localhost";
    protected $db_database;
    protected $db_username;
    protected $db_password;
    protected $db_persistent = true;
    
    // Error handling
    protected $error;

    // Public database handle
    public $dbh;
        
    
    
    
    /**
    * Constructor
    */
    
    public function __construct()
    {
        // Initialise
        error_reporting(E_ALL);
        ignore_user_abort(true);
        set_magic_quotes_runtime(0);
        
        // Load dependecies
        require_once 'abstraction.php';
        
        // Default meta tags
        $this->meta[] = array ('http-equiv', 'Content-Style-Type', 'text/css');
    }
    


    
    /**
    * HTML Header.
    *
    * @param    string          <title>...</title>
    * @return   string
    */
    
    protected function head($title)
    {
        // Set charset 
        header("Content-Type: text/html; charset=$this->charset");    
        
        // Support both $this->css as string and array
        if (is_string($this->css)) {
            $this->css = array ($this->css);
        }
        
        // Output HTML header
        $result = $this->doctype ."\n<html>\n\n<head>\n<title>$title</title>";
        
        // Output meta tags
        foreach ($this->meta as $meta) { 
            list($type, $key, $value) = $meta;
            $result .= "\n<meta $type='$key' content='$value' />";
        }
        
        // Output links to style sheets
        foreach ($this->css as $css) {
            $result .= "\n<link href='$css' type='text/css' rel='stylesheet' />";                    
        }
        $result .= "\n" . $this->add_head . "\n</head>\n\n<body" . ($this->body_id ? "id='$this->body_id'" : '') . ">";
        
        return $result;                
    }
    
    
    
    
    /**
    * HTML Footer.
    *
    * @return   string
    */
    
    protected function foot()
    {
        return "\n\n</body>\n</html>";
    }
    
    
    
    
    /**
    * Read language file and define constants.
    *
    * File is a tab seperated csv file with no quotes. Header row should contain    
    * LABEL<tab>EN<tab>DA<ta>LANG3
    * Empty lines are allowed
    *
    * @param    string      filename        Filename
    * @param    string      language        Language column to use
    */
    
    protected function process_language_file($filename, $language)
    {
        // Read file into array
        $constants = file($filename);
        
        // Remove first array entry - contains languages
        $header = explode("\t", trim(array_shift($constants)));
        $offset = array_search($language, $header);
            
        // Loop thru array and define labels
        foreach ($constants as $line) {
            
            // Trim - remove possible \r\n only
            $line = trim($line, "\r\n");
            
            // Skip empty lines and comments
            if (trim($line) && $line{0} != '#') {
                
                // Extact values
                $values = explode("\t", $line);
                
                // Die if file is broken - better nok be broken
                if (sizeof($values) <= $offset) {
                    die("Error in language file, $filename - $line");
                }
                
                // Define constant
                define($values[0], $values[$offset]);
            }
        }
    }
    
    
    
    
    /**
    * Read constants file and define.
    *
    * File is a tab seperated csv file with no quotes. Header row should contain    
    * Empty lines and comments starting with # are allowed
    *
    * @param    string      filename        Filename
    */
    
    protected function process_constants_file($filename)
    {
        // Read file into array
        $constants = file($filename);
        
        // Loop thru array and define labels
        foreach ($constants as $line) {
            
            // Trim - remove possible \r\n only
            $line = trim($line, "\r\n");
            
            // Skip empty lines and comments
            if (trim($line) && $line{0} != '#') {
                
                // Extact values
                $values = explode("\t", $line);
                
                // Die if file is broken - better not be broken!
                if (sizeof($values) != 2) {
                    die("Error in constantse file, $filename - $line");
                }
                
                // Define constant
                define($values[0], $values[1]);
            }
        }
    }

    
    
    
    /**
    * Format message - typically defined by process_language_file()
    *
    * @param    string      string      String to format. Contains %Code%.
    * @param    array       values      Array ("Code" => $value, "Code2" => $value2)
    * @return   string
    */
    
    public static function format_string($string, $values)
    {
        foreach ($values as $code => $value) {
            $string = str_replace("%$code%", $value, $string);
        }
        
        return $string;        
    }
    
    
    
    
    /**
    * Redirect to new url.
    *
    * @param    string      url     New url to redirect to.
    */
    
    public static function redirect($url)
    { 
        // Replace relative urls with absolute to adhere to standard.
        if ($url[0] == "/") {
            if ($_SERVER["SERVER_PORT"] == 443) {
                $url= "https://$_SERVER[HTTP_HOST]$url";    
            }            
            else {
                $url= "http://$_SERVER[HTTP_HOST]$url";    
            }
        }        
        
        // Send redirect header
        header("Location: $url");
        
        // Close connection on HTTP 1.1 to avoid browser hang.
        if ($_SERVER["SERVER_PROTOCOL"] == "HTTP/1.1") {
            header("Connection: close");
        }
        
        // Stop script
        die();
    }




    // Global Error handler - adds error message from string or object to internal list of errors
    protected function error($error)
    {
        $this->rollback();
        
        if (empty($this->error)) {
            $this->error = array ();
        }

        if (is_object($error) && isset($error->error)) {
            $this->error($error->error);
        }
        
        if (is_string($error)) {
            $this->error[] = $error;
            return;
        }
        
        if (is_array($error)) {
            $this->error = array_merge($this->error, $error);
        }

    }
    
    
    
    
    /**
    * Error handler, internal errors.
    */
    
    public function internal_error()
    {
        @$this->rollback();
        
        header("HTTP/1.0 500 Internal Error");
        
        if (@include($_SERVER["DOCUMENT_ROOT"] . "/templates/500.php")) {
            die();
        }
        
        echo $this->head("500 Internal Error");
        echo $this->foot();
        
        die();
    }
    
    
    
    
    /**
    * Error handler, data errors.
    */
    
    public function bad_request()
    {
        @$this->rollback();
        
        header("HTTP/1.0 400 Bad Request");
        
        if (@include($_SERVER["DOCUMENT_ROOT"] . "/templates/400.php")) {
            die();
        }
        
        echo $this->head("400 Bad Request");
        echo $this->foot();
        
        die();
    }




    /**
    * Error handler, authentication errors.
    */
    
    public function unauthorized()
    {
        @$this->rollback();
    
        header("HTTP/1.0 401 Unauthorized");
        
        if (@include($_SERVER["DOCUMENT_ROOT"] . "/templates/401.php")) {
            die();
        }
        
        echo $this->head("401 Unauthorized");
        echo $this->foot();
        
        die();
    }




    /**
    * Error handler, payment required errors.
    */
    
    public function payment_required()
    {
        @$this->rollback();
    
        header("HTTP/1.0 402 Payment Required");
        
        if (@include($_SERVER["DOCUMENT_ROOT"] . "/templates/402.php")) {
            die();
        }
        
        echo $this->head("402 Payment Required");
        echo $this->foot();
        
        die();
    }
    
    
    
    
    /**
    * Error handler, forbidden errors.
    */
    
    public function forbidden()
    {
        $this->rollback();
    
        header("HTTP/1.0 403 Forbidden");
        
        if (@include($_SERVER["DOCUMENT_ROOT"] . "/templates/403.php")) {
            die();
        }
        
        echo $this->head("403 Forbidden");
        echo $this->foot();
        
        die();
    }




    /**
    * Error handler, not_found errors.
    */
    
    public function not_found()
    {
        @$this->rollback();
        
        header("HTTP/1.0 404 Forbidden");
        
        if (@include($_SERVER["DOCUMENT_ROOT"] . "/templates/404.php")) {
            die();
        }
        
        echo $this->head("404 Not Found");
        echo $this->foot();
        
        die();
    }




    /**
    * Generate random numbers
    */
    
    public static function random($min, $max)
    {
        if (!isset($this->random_generator_initialsed)) {
            srand((double)microtime()*1000000);
            $this->random_generator_initialsed = true;
        }
        
        return ceil(rand() / getrandmax() * (1 + $max - $min)) - 1 + $min;
    }
    
    
    
    
    /**
    * Connect to database.
    */
    
    protected function db_connect()
    {
        // Load dependency
        require_once $this->db_handler.".php";
        
        // Connect to database
        if (!isset($this->dbh)) {
            $this->dbh = new $this->db_handler;
            
            $this->dbh->database = $this->db_database;
            $this->dbh->host     = $this->db_server;
            $this->dbh->username = $this->db_username;
            $this->dbh->password = $this->db_password;
            
            return $this->dbh->connect();
        }
    }
    
    
    
    
    /**
    * Spawn a new $dbObject properly connected to database.
    *
    * @access   private (protected)
    */
    
    protected function dbObject_spawn($dbObject)
    {
        if (!isset($this->dbh)) {
            $this->db_connect();
        }
        
        return new $dbObject($this);
    }
    
    
    
    
    // Transaction control and logging
    protected function begin()
    {
        $this->db_connect();
        return $this->dbh->begin();
    }
    
    
    
    // Transaction control and logging - commit refused if error() has been called!!
    protected function commit()
    {
        return empty($this->error) && $this->dbh->commit();
    }
    
    
    
    // Transaction control and logging
    protected function rollback()
    {
        if ($this->dbh) {
            return $this->dbh->rollback();
        }
    }
        
}

?>