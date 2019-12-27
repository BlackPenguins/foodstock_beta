<meta name="viewport" content="width=device-width, initial-scale=1">

<?php
    include(__DIR__ . "/../appendix.php" );
    $url = ADMIN_DEFECTIVES_LINK;
    include( HEADER_PATH );
    
    echo "<span class='admin_box'>";
        // ------------------------------------
        // DEFECTIVES TABLE
        // ------------------------------------
        echo "<div class='center_piece'>";
        echo "<div class='rounded_table'>";
        echo "<table>";
        echo "<thead><tr>";
        echo "<th class='admin_header_column' align='left'>Item</th>";
        echo "<th class='admin_header_column' align='left'>Amount</th>";
        echo "<th class='admin_header_column' align='left'>Price</th>";
        echo "<th class='admin_header_column' align='left'>Date</th>";
        echo "</tr></thead>";
        
        $rowClass = "odd";
        
        $statement = $db->prepare("SELECT i.Name, d.Amount, d.Date, d.Price " .
            "FROM Defectives d " .
            "JOIN Item i ON d.itemID = i.id " .
            "ORDER BY d.Date DESC");
        $results = $statement->execute();

        while ($row = $results->fetchArray()) {
            
            if( $rowClass == "odd" ) { $rowClass = "even"; } else { $rowClass = "odd"; }

            $itemName = $row['Name'];
            echo "<tr class='$rowClass'>";
            echo "<td>" . $itemName . "</td>";
            echo "<td>" . $row['Amount'] . "</td>";
            echo "<td>" . getPriceDisplayWithDollars( $row['Price'] ) . "</td>";
            $date_object = DateTime::createFromFormat('Y-m-d H:i:s', $row['Date']);
            echo "<td>".$date_object->format('m/d/Y  [h:i:s A]')."</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        echo "</div>";
        echo "</div>";
    echo "</span>";
?>

</body>