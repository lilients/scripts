<!--
script to read data from http://wikicfp.com
-->
<?php

// read id from url
if($_GET['id']){
  $id = $_GET['id'];
}else{
  $id = '94994';
}

// get the data for this id from wikicfp
$dom = new DOMDocument();
$dom->loadHTML(file_get_contents('http://wikicfp.com/cfp/servlet/event.showcfp?eventid='.$id));

// list of metadata, we want to collect
$metadata = ['dc:title', 'dc:source', 'v:startDate', 'v:endDate', 'v:locality'];

// get metadata from the html elements (span)
$divs = $dom->getElementsByTagName('span');

// find the metadata in the attributes of the html elements
foreach ($divs as $div) {
  foreach ($metadata as $entry) {
    findAttribute($div, $entry);
  }
}


/*
* get metadata from attribute in div
* @param $div div html element
* @param $needle the metadata to look for
*/
function findAttribute($div, $needle){

  // flag to mark if the property with the needle has been found
  $flag = false;

  foreach ($div->attributes as $attr) {

    $name = $attr->nodeName;
    $value = $attr->nodeValue;

    // look for the needle and set to true, if found
    if(!$flag){
      if($needle == $value){
       $flag = true;
      }
    }
    elseif('content' == $name){
       echo "$needle: $value<br />"; // print data for this needle
       $flag = false; // reset flag to look for more metadata later
    }
  }
}

?>
