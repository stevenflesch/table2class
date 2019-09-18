<?php
// Attempt to list databases with supplied credentials.
$oLink = @mysqli_connect($_POST["serveraddress"], $_POST["serverusername"], $_POST["serverpassword"]) or die("Error: Could not connect to server.");
$oResult = mysqli_query($oLink, "SHOW TABLES FROM " . $_POST["database"] . ";");

// Check for valid results.
if(mysqli_affected_rows($oLink) == 0) {
    echo "Error: No tables returned from database \"" . $_POST["database"] . "\".";
    exit;
}

// Output first option
echo "<option value=\"\"></option>";

// Output table names
while($oRow = mysqli_fetch_row($oResult)) {
    echo "<option value=\"" . $oRow[0] . "\">" . $oRow[0] . "</option>\n";
}
?>