<?php
/*
*   class.database.php
*   loosely based on a class by: MARCO VOEGELI (www.voegeli.li)
*
*   This class provides one central database-connection for
*   all your php applications. Define only once your connection
*   settings and use it in all your applications.
*/

class Database {
	public $host;           // Hostname / Server
	public $password;       // MySQL Password
	public $user;           // MySQL Username
    public $database;       // MySQL Database Name
	public $link;
	public $query;
	public $result;
	public $rows;
    public $debug;          // Whether to print debug (testing) info (default 0)
    private $logfile;       // Where to log errors (optional)
    public $persistentconn; // Whether to use persistent connections.
    public $lastinsertid;   // ID of last record inserted, if we ever did.

	public function __construct($strHost = "localhost", $strUser = "root", $strPassword = "", $strDatabase = "") {
		// Method : begin
		//Konstruktor
		// ********** ADJUST THESE VALUES HERE **********
		$this->host = $strHost;
		$this->password = $strPassword;
		$this->user = $strUser;
		$this->database = $strDatabase;
		$this->rows = 0;
        $this->link = NULL;
        $this->debug = TRUE;
        $this->persistentconn = FALSE;
        $this->lastinsertid = -1;
		// **********************************************
	}

    public function __destruct() {
        // Destroy the MySQL connection on unset, even if we are using mysql_pconnect().
        $this->CloseDB();
    }

    private function failureHandler($iError, $sQuery = "") {
        $sRet = "SQL Error: $iError";
        if($sQuery != "") {
            $sRet .= "\n... Executing Query: $sQuery\n";
        }

        // Log to file, if set.
        if($this->logfile) {
            error_log($sRet, 3, $this->logfile);
        }

        // Return full debug info if in debug mode.
        if($this->debug) {
            return("<hr>" . $sRet);
        }
        return("<hr>Requested page has encountered an error, please try again later.");
    }

    public function SetSettings($strHost, $strUser, $strPass, $strDatabase) {
        // Sets the connection settings.
        $this->host = $strHost;
        $this->user = $strUser;
        $this->password = $strPass;
        $this->database = $strDatabase;
    }

	public function OpenLink() {
		// Close the previous connection if we have one open.
        // We do this because if the server/user/pass change, the class will open a new link and never close the old one.
        if(!(($this->link == NULL) || ($this->link == FALSE))) {
            mysql_close($this->link);
        }

        // Open the connection, persistent or not.
        if($this->persistentconn) {
            $this->link = mysql_pconnect($this->host, $this->user, $this->password)
                            or die(print "class.database: Error connecting to database.\n" . $this->failureHandler(mysql_error()));
        } else {
            $this->link = mysql_connect($this->host, $this->user, $this->password)
                            or die(print "class.database: Error connecting to database.\n" . $this->failureHandler(mysql_error()));
        }

        // Return link value.
        return($this->link);
	}

	private function SelectDB() {
		mysql_select_db($this->database, $this->link) or die(print "class.database: Error selecting database \"$this->database\"\n" . $this->failureHandler(mysql_error()));
	}

	public function CloseDB() {
        // Closes our connection and resets the link variable if successful.

        // First check to see if we have a connection open.
        if($this->link > 0) {
            // Attempt to close link.
            if(mysql_close($this->link)) {
                $this->link = NULL;
            } else {
                print("class.database: Unable to free 'link' resource - #" . $this->link);
                return(FALSE);
            }
        }
        return(TRUE);
	}

	public function Query($query) {
        // Reset our rows/result variables.
        $this->rows = 0;
        $this->result = NULL;

        // Establish connection to the database.
        $this->OpenLink();
		$this->SelectDB();

        // Clean SQL to prevent attacks
        $query = stripslashes(mysql_real_escape_string($query));

        // Execute query.
		$this->query = $query;
		$this->result = mysql_query($query, $this->link)
                        or die(print "class.database: Error while executing Query." . $this->failureHandler(mysql_error(), $this->query));

        // Count the number of rows returned, if a SELECT query was made.
		if(stristr($query, "SELECT") != FALSE) {
			$this->rows = mysql_num_rows($this->result);
		}

        // Count the number of rows affected by an INSERT, UPDATE, REPLACE or DELETE query.
        if(((stristr($query, "INSERT") + stristr($query, "UPDATE") + stristr($query, "REPLACE") + stristr($query, "DELETE"))) != FALSE) {
            $this->rows = mysql_affected_rows();
		}

        if(stristr($query, "INSERT") != FALSE) {
            $this->lastinsertid = mysql_insert_id($this->link);
        }

        // Close the connection after we're done executing query.
		$this->CloseDB();
	}
}
?>
