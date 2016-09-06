<?php

/**
* getEnabledPlugins.php
* get the enabled plugins from all our OJS users right out of the database
* 
* @argv path to directory where ojs installations are located
* @version 1.3
* @date 2016-09-06
* @author Svantje Lilienthal, Center for Digital Systems
*/

/*
* VARIABLES
*/ 

// default variables for the database connection
$host = 'localhost';
$username = 'root';
$password = '';

// output file
$outputFile = 'plugin-usage.html';

// variables to be filled with data
$pluginNames = array();
$plugins = array();
$databaseNames = array();

/*
* GET DATA
* get all folders, get credentials from config and ask database to get plugin settings
*/

if(isset($argv[1])){
	
	// get path to directory from user
	$dir = $argv[1];
	
	// get all subfolders of the directory 
	$files = scandir($dir);

	// go trough every folder = installation 
	foreach($files as $file){
		
		if(is_dir($dir.'/'.$file)){
			
			echo('Folder: '.$file.'<br>');
			
			if(file_exists($dir.'/'.$file.'/config.inc.php')){
				
				$config = parse_ini_file($dir.'/'.$file.'/config.inc.php');
			
				// read credentials from config
				$password = $config['password'];
				$database = $config['name'];
				$username = $config['username'];
				$password = $config['password'];
				
				// connect with database of this installation
				$db = new PDO("mysql:host=$host;dbname=$database", $username, $password);
				
				// sql query 
				$sql = 'SELECT ps.plugin_name, ps.setting_value, v.product FROM plugin_settings ps JOIN versions v ON (ps.plugin_name = CONCAT(v.product,"plugin") AND ps.setting_name = "enabled")'; //AND ps.setting_value = "1"
				$result = $db->prepare($sql);
				$result->execute();
				
				// store databaseNames in array
				array_push($databaseNames, $database);	
				
				// handle result of query
				foreach($result as $key=>$row){
								
					$pluginPath = $dir.'/'.$file.'/plugins/generic/'.$row['product'];
					
					// check if plugin folder exists
					if(is_dir($pluginPath)){
		
						// store plugin names in array
						array_push($pluginNames, $row['plugin_name']);
						
						// store plugin settings in associative array
						$plugins[$database][$row['plugin_name']] = $row['setting_value'];
						
					}
				
				}
				
			}
			
		}
		
	}
	
	/*
	* OUTPUT
	*/

	// makeTable($pluginNames, $databaseNames, $plugins, $outputFile);
	$outputString = makeReverseTable($pluginNames, $databaseNames, $plugins, $outputFile);

	// write to file
	$file = fopen($outputFile, 'w');
	fwrite($file, $outputString);
	
}else {
	
	echo("Please enter the folder path, where the ojs installations are located as an argument.");
	
}


/*
* FUNCTIONS
*/

/**
* function creates a html table  
* 
* $pluginName string
* $$databaseNames array
* $plugins array
* @returns string
*/
function makeTable($pluginNames, $databaseNames, $plugins, $outputFile){
	
	// remove double entries and sort names alphabetically
	$pluginNames = array_unique($pluginNames);
	sort($pluginNames);
	
	// output in table
	$outputString = '<table><tr><th></th>';

	// write plugin names 
	foreach($pluginNames as $pluginName){
		$outputString .= '<th>'.$pluginName.'</th>';
	}
	$outputString .= '</tr>';

	foreach($databaseNames as $database){
		
		// write database name
		$outputString .= '<tr><th>'.$database.'</th>';
		
		foreach($pluginNames as $pluginName){
			
			// write plugin settings
			if(isset($plugins[$database][$pluginName])){
				$outputString .= '<td><a href="" title="'.$database.'/'.$pluginName.'">'.$plugins[$database][$pluginName].'</td>';
			}else{
				$outputString .= '<td></td>';
			}
		
		}
		$outputString .= '</tr>';
	}

	$outputString .= '</table>';
	
	return $outputString;

}


/**
* function creates a html table   
* 
* $pluginName string
* $$databaseNames array
* $plugins array
*/
function makeReverseTable($pluginNames, $databaseNames, $plugins, $outputFile){
	
	// remove double entries and sort names alphabetically
	$pluginNames = array_unique($pluginNames);
	sort($pluginNames);
	
	// output in table
	$outputString = '<table><tr><th></th>';

	// write plugin names 
	foreach($databaseNames as $database){
		$outputString .= '<th>'.$database.'</th>';
	}
	$outputString .= '</tr>';

	foreach($pluginNames as $pluginName){
		
		// write database name
		$outputString .= '<tr><th>'.$pluginName.'</th>';
		
		foreach($databaseNames as $database){
			
			// write plugin settings
			if(isset($plugins[$database][$pluginName])){
				
				$setting = $plugins[$database][$pluginName]; 
				//	$outputString .= '<td><a href="" title="'.$database.'/'.$pluginName.'">'.$plugins[$database][$pluginName].'</td>';
				
				if($setting == 1){
					
					$outputString .= '<td>'.$pluginName.' enabled </td>';
					
				}else{
					$outputString .= '<td>'.$pluginName.' installed </td>';
				}
		
			}else{
				$outputString .= '<td></td>';
			}
		
		}
		$outputString .= '</tr>';
	}

	$outputString .= '</table>';

	return $outputString;

}


/**
* function that reads all databaseNames
* 
* $host string
* $username string
* $password string
* @returns array 
*/
function getDatabases($host, $username, $password){
	
	$databases = Array();
	
	// connect with database
	$db = new PDO("mysql:host=$host", $username, $password);
		
	// get names of databases and store them in an array
	// sql query to get plugin settings
	$sqlDB = 'SHOW DATABASES';
	foreach($db->query($sqlDB) as $key=>$row){
		
		array_push($databases, $row['Database']);
		
	}
	
	return $databases;
	
}
	

?>



