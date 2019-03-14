<?php
/**
* @file deduplicate.php
* merge elements with same entry in first column in csv file
* @$argv1 input file
* @$argv2 output file
*/

// variables
$metadata = []; // array for metadata
(isset($argv[1]) && $argv[0]!==null) ? $input = $argv[1] : $input = 'input.csv'; // filename for input from commandline
(isset($argv[2]) && $argv[1]!==null) ? $output = $argv[2] : $output = 'output.csv'; // filename for output from commandline

// open input file
$inputfile = fopen($input, 'r'); // metadata in csv

// get rows with fgetcsv and store them in the array
while (($row = fgetcsv($inputfile)) !== FALSE){
  array_push($metadata, $row);
}

// merge metadata into new array
$merged = [];

foreach ($metadata as $entry) {

  $id = $entry[0];

  // add entry if not existent
  if(!isset($merged[$id])){

    $merged[$id] = $entry;

  }else{

      // merge all metadata
      foreach($entry as $key => $element){

        if($element != $merged[$id][$key] && !empty($merged[$id][$key]) && !empty($element)){

            $merged[$id][$key] .= " |".$element;

        }
      }
  }
}

// open output file
$outputfile = fopen($output, 'w');

// write reduplicated array to output file
foreach ($merged as $row) {
    fputcsv($outputfile, $row);
}

// close files
fclose($inputfile);
fclose($outputfile);

?>
