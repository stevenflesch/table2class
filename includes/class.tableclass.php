<?php
/*
*   class.class.php
*
*   This class provides methods to construct and write a class file.
*/
require_once(dirname(__FILE__) . '/class.code.php');

class tableClass {
	public $classname;          // Name of our class.
    public $serveraddress;      // IP Address for MySQL connection.
    public $serverusername;     // Username for MySQL connection.
    public $serverpassword;     // Password for MySQL connection.
    public $databasename;       // Name of database.
    public $tablename;          // Name of table within database.
    public $variables;
    public $primarykey;         // Field/publiciable set to primary key.
    private $filename;          // Path to file we're going to write.
    private $filepath;          // Directory+filename to save file to.
    private $filedate;          // Today's date.
    private $output;            // Text to write to file.
    public $filesrequired;      // Any files required. (default: class.database.php)

	public function __construct($sName = "newclass", $sDatabase = "", $sTable = "", $sPrimaryKey = "", $sServerAddress = "localhost", $sServerUsername = "root", $sServerPassword = "") {
		// Construction of class
        $this->classname = $sName;
        $this->variables = Array();
        $this->filedate = date("l, M j, Y - G:i:s T");
        $this->filesrequired = array("class.database.php");                     // Add any other required files here.
        $this->filename = "class.$this->classname.php";
        $this->filepath = realpath(dirname(__FILE__) . "/../output/") . "/$this->filename";
        $this->databasename = $sDatabase;
        $this->serveraddress = $sServerAddress;
        $this->serverusername = $sServerUsername;
        $this->serverpassword = $sServerPassword;
        $this->tablename = $sTable;
        $this->primarykey = $sPrimaryKey;
	}

    private function formatCode($sCode) {
        // Returns formatted code string.
        $oCode = new codeObject($sCode, FALSE);
        $oCode->process();
        return($oCode->code);
    }

    public function setFile($sPath = "", $sFilename = "") {
        // Sets the path and/or the filename to use for the class.
        if($sPath != "") {
            $this->filepath = $sPath;
        }

        if($sFilename != "") {
            $this->filename = $sFilename;
        }
    }

    public function setRequired($aFiles) {
        // Sets the required files to passed array.
        $this->filesrequired = $aFiles;
    }

    public function getRequired() {
        // Returns text to require all files in filesrequired array.
        $sRet = "// Files required by class:\n";
        if(!empty($this->filesrequired)) {
            foreach($this->filesrequired as $file) {
                $sRet .= "require_once(\"$file\");\n";
            }
        } else {
            $sRet .= "// No files required.\n";
        }

        $sRet .= "\n";

        return($sRet);
    }

    public function getHeader() {
        // Returns text for a header for our class file.
        $sRet  = "<?php\n";
        $sRet .= "/*******************************************************************************
* Class Name:       $this->classname
* File Name:        $this->filename
* Generated:        $this->filedate
*  - for Table:     $this->tablename
*   - in Database:  $this->databasename
* Created by: table2class (http://www.stevenflesch.com/projects/table2class/)
********************************************************************************/\n\n";
        $sRet .= $this->getRequired();
        $sRet .= "// Begin Class \"$this->classname\"\n";
        $sRet .= "class $this->classname {\n";

        return($sRet);
    }

    public function getFooter() {
        // Returns text for a footer for our class file.
        $sRet = "}\n";
        $sRet .= "// End Class \"$this->classname\"\n?>";

        return($sRet);
    }

    public function getVariables() {
        // public function to return text to declare all the variables in the class.
        $sRet =     "// Variable declaration\n";
        $sRet .=    "public \$$this->primarykey; // Primary Key\n";
        foreach($this->variables as $variable) {
            // Loop through variables and declare them.
            if($variable != $this->primarykey) {
                // Variable is not primary key, so we'll add it.
                $sRet .= "public \$$variable;\n";
            }
        }
        // Add variable for connection to database.
        $sRet .= "public \$database;\n\n";

        return($sRet);
    }

    public function getConstructorDestructor() {
        // public function to create the class constructor and destructor.
        $sRet  = "// Class Constructor\npublic function __construct() {\n";
        $sRet .= "\$this->database = new Database();\n\$this->database->SetSettings(\"$this->serveraddress\", \"$this->serverusername\", \"$this->serverpassword\", \"$this->databasename\");\n}\n\n";
        $sRet .=  "// Class Destructor\npublic function __destruct() {\n";
        $sRet .= "unset(\$this->database);\n}\n\n";

        return($sRet);
    }

    public function getGetters() {
        // public function to create all the GET methods for the class.
        $sRet =  "// GET Functions\n";

        // Create the primary key function.
        $sRet .= "public function get$this->primarykey() {\n";
        $sRet .= "return(\$this->$this->primarykey);\n}\n\n";

        // Loop through variables to create the functions.
        foreach($this->variables as $variable) {
            // Loop through variables and declare them.
            if($variable != $this->primarykey) {
                // Variable is not primary key, so we'll add it.
                $sRet .= "public function get$variable() {\n";
                $sRet .= "return(\$this->$variable);\n}\n\n";
            }
        }

        return($sRet);
    }

    public function getSetters() {
        // public function to create all the SET methods for the class.
        $sRet =  "// SET Functions\n";

        // Create the primary key function.
        $sRet .= "public function set$this->primarykey(\$mValue) {\n";
        $sRet .= "\$this->$this->primarykey = \$mValue;\n}\n\n";

        // Loop through variables to create the functions.
        foreach($this->variables as $variable) {
            // Loop through variables and declare them.
            if($variable != $this->primarykey) {
                // Variable is not primary key, so we'll add it.
                $sRet .= "public function set$variable(\$mValue) {\n";
                $sRet .= "\$this->$variable = \$mValue;\n}\n\n";
            }
        }

        return($sRet);
    }

    public function getSelect() {
        $sRet  = "public function select(\$mID) { // SELECT Function\n// Execute SQL Query to get record.\n";
        $sRet .= "\$sSQL =  \"SELECT * FROM $this->tablename WHERE $this->primarykey = \$mID;\";\n";
        $sRet .= "\$oResult =  \$this->database->query(\$sSQL);\n\$oResult = \$this->database->result;\n\$oRow = mysqli_fetch_object(\$oResult);\n\n";
        $sRet .= "// Assign results to class.\n";
        $sRet .= "\$this->$this->primarykey = \$oRow->$this->primarykey; // Primary Key\n";
        // Loop through variables.
        foreach($this->variables as $variable) {
            $sRet .= "\$this->$variable = \$oRow->$variable;\n";
        }
        $sRet .= "}\n\n";

        return($sRet);
    }

    public function getInsert() {
        $sRet  = "public function insert() {\n";
        $sRet .= "\$this->$this->primarykey = NULL; // Remove primary key value for insert\n";
        $sRet .= "\$sSQL = \"INSERT INTO $this->tablename (";
        $i = "";
        foreach($this->variables as $variable) {
            $sRet .= "$i$variable";
            $i = ", ";
        }
        $i = "";
        $sRet .= ") VALUES (";
        foreach($this->variables as $variable) {
            $sRet .= "$i'\$this->$variable'";
            $i = ", ";
        }
        $sRet .= ");\";\n";
        $sRet .= "\$oResult = \$this->database->query(\$sSQL);\n";
        $sRet .= "\$this->$this->primarykey = \$this->database->lastinsertid;\n}\n\n";

        return($sRet);
    }

    public function getUpdate() {
        $sRet  = "function update(\$mID) {\n";
        $sRet .= "\$sSQL = \"UPDATE $this->tablename SET ($this->primarykey = '\$this->$this->primarykey'";
        // Loop through variables.
        foreach($this->variables as $variable) {
            //$sRet .= ", $variable = '\" . mysqli_real_escape_string(\$this->$variable, \$this->database->link) . \"'";
            $sRet .= ", $variable = '\$this->$variable'";
        }
        $sRet .= ") WHERE $this->primarykey = \$mID;\";\n";
        $sRet .= "\$oResult = \$this->database->Query(\$sSQL);\n}\n\n";

        return($sRet);
    }

    public function getDelete() {
        // Creates the delete function.
        $sRet = "public function delete(\$mID) {\n\$sSQL = \"DELETE FROM $this->tablename WHERE $this->primarykey = \$mID;\";\n\$oResult = \$this->database->Query(\$sSQL);\n}\n\n";

        return($sRet);
    }

    public function createClass($bEcho = 0, $bWrite = 1) {
        // Creates class file.

        // Generate the file text.
        $sFile  =   $this->getHeader() .        $this->getVariables() .
                    $this->getConstructorDestructor() .   $this->getGetters() .
                    $this->getSetters() .       $this->getSelect() .
                    $this->getInsert() .        $this->getUpdate() .
                    $this->getDelete() .        $this->getFooter();
        // Format the code.
        $sFile = $this->formatCode($sFile);

        // If we are to display the file contents to the browser, we do so here.
        if($bEcho) {
            echo "";
            highlight_string($sFile);
            echo "<br><br><br>Output save path: $this->filepath";
        }

        // If we are to write the file (default=TRUE) then we do so here.
        if($bWrite) {
            // Check to see if file already exists, and if so, delete it.
            if(file_exists($this->filename)) {
                unlink($this->filename);
            }

            // Open file (insert mode), set the file date, and write the contents.
            $oFile = fopen($this->filepath, "w+");
            fwrite($oFile, $sFile);
        }

        // Exit the function
        return($this->filepath);
    }
}
?>
