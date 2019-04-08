<head>
<meta name="viewport" content="width=device-width, initial-scale=1">

<?php
    include(__DIR__ . "/../appendix.php" );

    $url = ADMIN_CHECKLIST_LINK;
    include( HEADER_PATH );
    
    echo "<span style='width:86%; display:inline-block; border-left: 3px #000 solid;'>";
        DrawTable( $db, "RefillTrigger", "Needs Refill from Desk" );
        DrawTable( $db, "RestockTrigger", "Needs Restock from Store" );
    echo "</span>";

    function DrawTable( $db, $checklistType, $title ) {
        echo "<div class='rounded_header'><span class='title'>$title</span></div>";

        echo "<div class='center_piece'>";
        echo "<div class='rounded_table_no_border'>";
        echo "<table>";
        echo "<thead><tr>";
        echo "<th align='left'>Name</th>";
        echo "<th align='left'>Type</th>";
        echo "<th align='left'>Shelf Quantity</th>";
        echo "<th align='left'>Backstock Quantity</th>";

        echo "</tr></thead>";

        $rowClass = "";

        $results = $db->query("SELECT Type, Name, RefillTrigger, RestockTrigger, BackstockQuantity, ShelfQuantity, Price, Retired, Hidden FROM Item WHERE Hidden != 1 AND $checklistType = 1 ORDER BY Retired, Type DESC");
        while ($row = $results->fetchArray()) {
            $isDiscontinued = $row['Retired'] == 1;

            if( $isDiscontinued ) {
                $rowClass = "class='discontinued_row'";
            }

            echo "<tr $rowClass>";
            echo "<td>" . $row['Name'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['ShelfQuantity'] . "</td>";
            echo "<td>" . $row['BackstockQuantity'] . "</td>";
            echo "</tr>";
        }

        echo "</table>";
        echo "</div>";
        echo "</div>";
    }
?>

</body>