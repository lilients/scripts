<?php

/**
* getEnabledPlugins.php
* get the enabled plugins from all our OJS users right out of the database
* @argv password string
* @version 1.0
* @date 2016-07-28
* @author Svantje Lilienthal, Center for Digital Systems
*/

/*
* VARIABLES
*/ 

// variables for the database connection
$host = 'localhost';
$username = 'root';

// variables to be filles with data
$pluginNames = array();
$plugins = array();
$databaseNames = array();
//$databaseNames = array("ojs", "master", "montreal", "heidelberg");

/*
* GET DATA
*/

if(isset($argv[1])){
	
	$password = $argv[1];

	// connect with database
	$db = new PDO("mysql:host=$host", $username, $password);
		
	// get names of databases and store them in an array
	// sql query to get plugin settings
	$sqlDB = 'SHOW DATABASES';
	foreach($db->query($sqlDB) as $key=>$row){
		
		$database = $row['Database'];
		
		// connect with each database
		$db = new PDO("mysql:host=$host;dbname=$database", $username, $password);
		
		// sql query 
		$sql = 'SELECT ps.plugin_name, ps.setting_value FROM plugin_settings ps JOIN versions v ON (ps.plugin_name = CONCAT(v.product,"plugin") AND ps.setting_name = "enabled")'; //AND ps.setting_value = "1"
		$result = $db->prepare($sql);
		$result->execute();
		
		// store databaseNames in array
		if(($result->rowCount())!=0){
			echo($database.'<br>');
			array_push($databaseNames, $row['Database']);	
		}
		
		// handle result of query
		foreach($result as $key=>$row){
			
			// store plugin names in array
			array_push($pluginNames, $row['plugin_name']);
			
			// store plugin settings in associative array
			$plugins[$database][$row['plugin_name']] = $row['setting_value'];
		
		}
		
	}

	/*
	* OUTPUT
	*/

	// remove double entries
	$pluginNames = array_unique($pluginNames);

	// output in table
	$outputString = "<table><tr><th></th>";

	// write plugin names 
	foreach($pluginNames as $pluginName){
		$outputString .= "<th>".$pluginName."</th>";
	}
	$outputString .= "</tr>";

	foreach($databaseNames as $database){
		
		// write database name
		$outputString .= "<tr><th>".$database."</th>";
		
		foreach($pluginNames as $pluginName){
			
			// write plugin settings
			if(isset($plugins[$database][$pluginName])){
				$outputString .= "<td>".$plugins[$database][$pluginName]."</td>";
			}else{
				$outputString .= "<td></td>";
			}
		
		}
		$outputString .= "</tr>";
	}

	$outputString .= "</table>";

	// write to file
	$file = fopen("plugin-usage.html", "w");
	fwrite($file, $outputString);

}
else{
	echo('Please enter root password for this database.');
}
?>



