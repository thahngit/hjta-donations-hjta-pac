<?php

$_root = $_SERVER['DOCUMENT_ROOT'];
require_once($_root."/common/utils.php");
require_once($_root."/common/ezMysql_ntw.php");

spl_autoload_register(function ($class) { require_once"{$class}.class.php"; });

require_once($_root."/common/Z_HJTAPACglobals.php");
require_once($_root."/common/connect_REAL.php");



$ez    =new ezMysql_ntw();

$already_condition = "visitdate > '2024-10-05' AND visitdate < '2024-11-06' AND cc_authorization = 'Y' " ;
$result=$ez->ezSelect_i("*", "don2hjtapac" , $already_condition );

$numb_rows = mysqli_num_rows($result);

$filename = "report.csv";
header('Content-Type: application/csv');
header('Content-Disposition: attachment; filename="'.$filename.'";');

$out = fopen('php://output', 'w');
fputcsv($out, array('Visit Date','First Name','Last Name','Contribution Amount'));

while ($obj = $result->fetch_object()) {
    fputcsv($out, array($obj->visitdate,$obj->fname,$obj->lname,number_format($obj->contrib, 2)));
}
fclose($out);

/*
$filename = "report.csv";
header('Content-Type: application/csv');
header('Content-Disposition: attachment; filename="'.$filename.'";');

$out = fopen('php://output', 'w');
fputcsv($out, array('this','is some', 'csv "stuff", you know.'));
fputcsv($out, array('here','is some other', 'csv "stuff", you should know.'));
fclose($out);
*/

////////

/*
function array_to_csv_download($array, $filename = "export.csv", $delimiter=",") {
    // open raw memory as file so no temp files needed, you might run out of memory though
    $f = fopen('php://memory', 'w'); 
    // loop over the input array
    foreach ($array as $line) { 
        // generate csv lines from the inner arrays
        fputcsv($f, $line, $delimiter); 
    }
    // reset the file pointer to the start of the file
    fseek($f, 0);
    // tell the browser it's going to be a csv file
    header('Content-Type: text/csv');
    // tell the browser we want to save it instead of displaying it
    header('Content-Disposition: attachment; filename="'.$filename.'";');
    // make php send the generated csv lines to the browser
    fpassthru($f);
}

array_to_csv_download(array(
  array(1,2,3,4), // this array is going to be the first row
  array(1,2,3,4)), // this array is going to be the second row
  "numbers.csv"
);
*/

/*
require_once($_root."/email_groups/email_groups.php");
require_once($_root."/common/email_funcs.php");

$file = fopen('yourFile.csv', 'w');
fputcsv($file, array('this','is some', 'csv "stuff", you know.'));
fclose($file);
*/

//zEmailAttachment("tysons_music@yahoo.com", "test report", "Attached is the report", $file, "yourFile.csv");

//zMail("tysons_music@yahoo.com", "test report", "Attached is the report");

?>