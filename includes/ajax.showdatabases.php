<?php
// Attempt to list databases with supplied credentials.
$oLink = @mysql_connect($_POST["serveraddress"], $_POST["serverusername"], $_POST["serverpassword"]) or die("Error: Could not connect to server.");
$oResult = mysql_list_dbs($oLink);

// Check for valid results
if(mysql_affected_rows($oLink) == 0) {
    echo "Error: No databases returned from server \"" . $_POST["serveraddress"] . "\" (" . $_POST["serverusername"] . "@" . $_POST["serverpassword"] . ")";
    exit;
}

// Output first option
echo "<option value=\"\"></option>";

// Output Database Names
while($oRow = mysql_fetch_object($oResult)) {
    echo "<option value=\"" . $oRow->Database . "\">" . $oRow->Database . "</option>\n";
}
?>