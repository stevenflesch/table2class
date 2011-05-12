<!DOCTYPE html>
<head>
    <!-- *** METADATA *** -->
    <meta name="copyright" content="&copy; Copyright 2011 StevenFlesch.com" />
    <meta charset="UTF-8">
    <title>table2class - Automatic Class Generator - brought to you by StevenFlesch.com</title>
    <!-- *** CSS *** -->
    <link rel="stylesheet" href="css/table2class.css" type="text/css" media="screen">
    <!-- *** JAVASCRIPT *** -->
    <script type="text/javascript" src="js/jquery.js"></script>
	<script type="text/javascript">
        // Bind form elements to actions
        $(document).ready(function() {
            // Connect button
            $("#serverconnect").click(function(){
                // Reset form
                $("#database").attr("disabled", "disabled");
                $("#database").html("<option>... Please Connect ...</option>");
                $("#table").attr("disabled", "disabled");
                $("#table").html("<option>... Please Connect ...</option>");
                $("#keyfield").attr("disabled", "disabled");
                $("#keyfield").html("<option>... Please Connect ...</option>");
                $("#classname").attr("disabled", "disabled");
                $("#classname").attr("value", "");
                $("#classgenerate").attr("disabled", "disabled");
                // Output message
                $("#output").html("Attempting to connect to MySQL server...");

                // Submit form
                $.post("includes/ajax.showdatabases.php", $("#form-connect").serialize(),
                function(data){
                    if(data.indexOf("Error:") < 0) {
                        // Load output into select box for databases.
                        $("#database").html(data);
                        $("#database").removeAttr("disabled").focus();    // enable form
                        $("#output").html($("#output").html() + " done.");
                    } else {
                        // No data outputted, no valid databases.
                        $("#output").html(data);
                    }
                }, "html");
                return false;   // prevent traditional submit
            });

            // Database selectbox
            $("#database").change(function() {
                // Disable lower parts of form.
                $("#table").attr("disabled", "disabled");
                $("#table").html("<option>... Please Connect ...</option>");
                $("#keyfield").attr("disabled", "disabled");
                $("#keyfield").html("<option>... Please Connect ...</option>");
                $("#classname").attr("disabled", "disabled");
                $("#classname").attr("value", "");
                $("#classgenerate").attr("disabled", "disabled");

                // Output text
                var strDatabase = $("#database option:selected").text();
                if(strDatabase) {
                    $("#output").html("Retrieving tables for database \"" + strDatabase + "\"...");
                    // Populate selectbox
                    $.post("includes/ajax.showtables.php", $("#form-connect").serialize(),
                    function(data){
                        if(data.indexOf("Error:") < 0) {
                            // Load output into select box for databases.
                            $("#table").html(data);
                            $("#table").removeAttr("disabled").focus();    // enable form
                            $("#output").html($("#output").html() + " done.");
                        } else {
                            // No data outputted, no valid databases.
                            $("#output").html(data);
                        }
                    }, "html");
                    return false;   // prevent traditional submit
                }
            });

            // Table selectbox
            $("#table").change(function() {
                // Disable lower parts of the form.
                $("#keyfield").attr("disabled", "disabled");
                $("#keyfield").html("<option>... Please Connect ...</option>");
                $("#classname").attr("disabled", "disabled");
                $("#classname").attr("value", "");
                $("#classgenerate").attr("disabled", "disabled");

                // Output text
                var strTable = $("#table option:selected").text();
                if(strTable) {
                    $("#output").html("Retrieving columns for table \"" + strTable + "\"...");
                    // Populate selectbox
                    $.post("includes/ajax.showcolumns.php", $("#form-connect").serialize(),
                    function(data){
                        if(data.indexOf("Error:") < 0) {
                            // Load output into select box for databases.
                            $("#keyfield").html(data);
                            $("#keyfield").removeAttr("disabled").focus();    // enable form
                            $("#output").html($("#output").html() + " done.");
                        } else {
                            // No data outputted, no valid databases.
                            $("#output").html(data);
                        }
                    }, "html");
                    return false;   // prevent traditional submit
                    }
            });

            // Keyfield selectbox
            $("#keyfield").change(function() {
                // Output text
                var strClassname = $("#table option:selected").text();
                // Populate classname field.
                $("#classname").attr("value", strClassname);
                //$("#classname").text(strKeyfield);
                $("#classgenerate").removeAttr("disabled");
                $("#classname").removeAttr("disabled").focus();
            });

            // Generate Class Button
            $("#classgenerate").click(function() {
                // Output text
                var strClassname = $("#classname").attr("value");
                var strFilename = "class." + strClassname + ".php";
                $("#output").html("Generating class file for class \"" + strClassname + "\"...");
                // Post results to createfile page.
                $.post("includes/ajax.createfile.php", $("#form-connect").serialize(),
                    function(data){
                        if(data.indexOf("Error:") < 0) {
                            $("#output").html($("#output").html() + " done.<br /><br />" + data);
                        } else {
                            // No data outputted, no valid databases.
                            $("#output").html(data);
                        }
                    }, "html");
                return false;
            });
        });
	</script>
</head>

<body>
	<div id="wrapper">
		<div id="header" class="center clear bold">
			table2Class
		</div>
		
		<div id="column-outer">
			<div id="column-inner">
				<div id="content-left">
                    <form id="form-connect" method="post"><!-- Encase all options in this form -->
                    <h2>Connect To Your Server</h2>
                    <div id="div-connect">
                            <label>Server Address</label><br />
                    		<input name="serveraddress" type="text" size="25" value="localhost" /><br />
                            <label>Server Username</label><br />
                    		<input name="serverusername" type="text" size="25" value="root" /><br />
                            <label>Server Password</label><br/>
                    		<input name="serverpassword" type="password" size="25" /><br />
                            <button name="serverconnect" id="serverconnect">Connect</button>
                    </div>

                    <h2>Select Database</h2>
                    <div id="div-database">
                        <select name="database" id="database" size="1" disabled="disabled">
                            <option>... Please Connect ...</option>
                        </select>
                    </div>

                    <h2>Select Table</h2>
                    <div id="div-table">
                        <select name="table" id="table" size="1" disabled="disabled">
                            <option>... Please Select Database ...</option>
                        </select>
                    </div>

                    <h2>Select Primary Key</h2>
                    <p>
                        <strong>Note: </strong>Your table must have a <span class="primarykey">unique primary key field</span>, and must be set to
                        <strong>auto-increment</strong>.
                    </p>
                    <div id="div-key">
                        <select name="keyfield" id="keyfield" size="1" disabled="disabled">
                            <option>... Please Select Primary Key ...</option>
                        </select>
                    </div>

                    <h2>Select Class Options</h2>
                    <div id="div-class">
                        <label for="classname">Class Name</label><br />
                        class.<input name="classname" id="classname" class="bold center" type="text" size="20" disabled="disabled" />.php<br />
                        <button name="classgenerate" id="classgenerate" disabled="disabled">Generate Class</button>
                    </div>
                    </form>
				</div>
				<div id="content-right">
					<h2>Script Output</h2>
					<div id="output">
						<p>
                            <strong>table2class</strong> is a lightweight PHP script that creates class definitions from MySQL tables automatically.
                            It is released under the GPLv3 license and the source code is available on GitHub.  More information about this script can
                            be found at the project homepage.
                        </p>
                        <h3>Contributions</h3>
                        <p>
                            Code formatting functions use a class provided under a
                            <a href="http://creativecommons.org/licenses/by/3.0/us/" target="_blank">Creative Commons Attribution 3.0 United States License</a>
                            from Daniel Lubarov at <a href="http://indented.net/" target="_blank">indented.net</a>.  The database class and project idea
                            is based loosely on an idea from Marco Voegeli at <a href="http://www.voegeli.li/" target="_blank">www.voegeli.li</a>.
                        </p>
                        <h3>Get Started</h3>
						<p>To begin, type your MySQL server credentials to the left and press connect.</p>
                        <p>Please note: Your MySQL user must have LIST DATABASES permissions.</p>
					</div>
				</div>				
			</div>
		</div>

	
	    <div id="footer" class="center clear">
	        Source: <a href="http://github.com/stevenflesch/table2class/" target="_blank">http://github.com/stevenflesch/table2class/</a> &nbsp;&bull;&nbsp; Homepage: <a href="http://www.stevenflesch.com/projects/table2class/" target="_blank">http://www.stevenflesch.com/projects/table2class/</a><br />
			<span xmlns:dct="http://purl.org/dc/terms/" property="dct:title">table2class</span> by <a xmlns:cc="http://creativecommons.org/ns#" href="http://www.stevenflesch.com/projects/table2class/" property="cc:attributionName" rel="cc:attributionURL">Steven Flesch</a> is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/3.0/">Creative Commons Attribution-NonCommercial-ShareAlike 3.0 Unported License</a>.
	    </div>
	</div>

</body>
</html>