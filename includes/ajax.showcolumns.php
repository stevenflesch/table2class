<?php
// Attempt to list databases with supplied credentials.
$oLink = @mysql_connect($_POST["serveraddress"], $_POST["serverusername"], $_POST["serverpassword"]) or die("Error: Could not connect to server.");
mysql_select_db($_POST["database"], $oLink);
$oResult = mysql_query("SHOW COLUMNS FROM " . $_POST["table"] . ";");

// Check for valid results
if(mysql_affected_rows($oLink) == 0) {
    echo "Error: No columns returned from table \"" . $_POST["table"] . "\" in database \"" . $_POST["database"] . "\".";
    exit;
}

// Output first option
echo "<option value=\"\"></option>";

// Ouput column names
while($oRow = mysql_fetch_object($oResult)) {
    // Attempt to detect primary key column.
    if($oRow->Key == "PRI") {
        // Primary key column.
        //echo "<option value=\"" . $oRow->Field . "\" selected=\"selected\">" . $oRow->Field . "</option>\n";
        echo "<option value=\"" . $oRow->Field . "\" class=\"primarykey\">" . $oRow->Field . "</option>\n";
    } else {
        // Non-primary key column.
        echo "<option value=\"" . $oRow->Field . "\">" . $oRow->Field . "</option>\n";
    }
}
?>