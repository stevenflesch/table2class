<?php
require_once(dirname(__FILE__) . '/class.tableclass.php');

// Create object for class file.
$oClass = new tableClass($_POST["classname"], $_POST["database"], $_POST["table"], $_POST["keyfield"], $_POST["serveraddress"], $_POST["serverusername"], $_POST["serverpassword"]);

// Save the class to a file.
$strPath = realpath($oClass->createClass());
echo "File saved as <strong>" . $strPath . "</strong>";

/*
if(isset($_GET["displayclass"]) && $_GET["displayclass"] > 0) {
    // Display the class, do not save.
    $oClass->createClass(TRUE, FALSE);
} else {
    // Save the class to a file.
    $oClass->createClass();
    echo "Class created successfully.";
}
*/
?>