<?php
include(__DIR__ . "/../appendix.php" );

$db = getDB();

include_once(QUANTITY_FUNCTIONS_PATH);

$statement = $db->prepare("SELECT Type, Name," . getQuantityQuery() .
            ",Price, DiscountPrice FROM Item i WHERE Hidden != 1 AND ( Retired == 0 OR (Retired == 1 AND TotalAmount > 0) ) ORDER BY Type DESC, Name ASC ");
$results = $statement->execute();

$csvLines = array();

while ($row = $results->fetchArray()) {
    $itemName = $row['Name'];
    $itemType = $row['Type'];

    $csvLine = array();
    $csvLine[] = $itemName;
    $csvLines[] = $csvLine;
}

/**
 * Created by PhpStorm.
 * User: Matt
 * Date: 12/14/2019
 * Time: 5:13 PM
 */
// output headers so that the file is downloaded rather than displayed
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=item_inventory.csv');

// create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// output the column headings
fputcsv($output, array('Date'));

foreach( $csvLines as $csvLine ) {
    fputcsv($output, $csvLine);
}

fputcsv($output, array('MUG AMOUNT') );

fclose( $output );