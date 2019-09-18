<?php
// Attempt to list databases with supplied credentials.
$oLink = @mysqli_connect($_POST["serveraddress"], $_POST["serverusername"], $_POST["serverpassword"]) or die("Error: Could not connect to server.");
mysqli_select_db($oLink, $_POST["database"]);
$oResult = mysqli_query($oLink, "SHOW COLUMNS FROM " . $_POST["table"] . ";");

// Check for valid results
if(mysqli_affected_rows($oLink) == 0) {
    echo "Error: No columns returned from table \"" . $_POST["table"] . "\" in database \"" . $_POST["database"] . "\".";
    exit;
}

// Output first option
echo "<option value=\"\"></option>";

// Ouput column names
while($oRow = mysqli_fetch_object($oResult)) {
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