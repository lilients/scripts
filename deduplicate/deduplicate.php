<?php
/**
* @file deduplicate.php
* merge elements with same entry in first column in csv file
* @$argv1 input file
* @$argv2 output file
*/

// variables
$projects = []; // array for projects
(isset($argv[1]) && $argv[0]!==null) ? $input = $argv[1] : $input = 'input.csv'; // filename for input from commandline
(isset($argv[2]) && $argv[1]!==null) ? $output = $argv[2] : $output = 'output.csv'; // filename for output from commandline

// open input file
$inputfile = fopen($input, 'r'); // metadata in csv

// get rows with fgetcsv and store them in the array
while (($row = fgetcsv($inputfile)) !== FALSE){
  array_push($projects, $row);
}

// read rows and compare reach element to a copy of the array
foreach ($projects as $i => $project) {
  foreach ($projects as $j => $copy) {

    // merge projects with identical id (but skip same element)
    if($project[0] == $copy[0] && $i != $j){

        // merge all metadata of the dublicates
        foreach($project as $key => $element){

            // combine elements, if not identical
            if ($element != $copy[$key]){
                $projects[$j][$key] = $element.$copy[$key];
            }
        }

        // remove dublicate entry
        unset($projects[$i]);
    }
  }
}

// open output file
$outputfile = fopen($output, 'w');

// write reduplicated array to output file
foreach ($projects as $row) {
  fputcsv($outputfile, $row);
}

// close files
fclose($inputfile);
fclose($outputfile);

?>
