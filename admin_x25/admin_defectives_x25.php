<head>
<meta name="viewport" content="width=device-width, initial-scale=1">

<?php
    include(__DIR__ . "/../appendix.php" );
    $url = ADMIN_DEFECTIVES_LINK;
    include( HEADER_PATH );
    
    echo "<span style='width:86%; display:inline-block; border-left: 3px #000 solid;'>";
        // ------------------------------------
        // DEFECTIVES TABLE
        // ------------------------------------
        echo "<div class='center_piece'>";
        echo "<div class='rounded_table'>";
        echo "<table>";
        echo "<thead><tr>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Item</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Amount</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Price</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Date</th>";
        echo "</tr></thead>";
        
        $rowClass = "odd";
        
        $results = $db->query("SELECT i.Name, d.Amount, d.Date, d.Price FROM Defectives d JOIN Item i ON d.itemID = i.id ORDER BY d.Date DESC");
        while ($row = $results->fetchArray()) {
            
            if( $rowClass == "odd" ) { $rowClass = "even"; } else { $rowClass = "odd"; }

            $itemName = $row['Name'];
            echo "<tr class='$rowClass'>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $itemName . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $row['Amount'] . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . getPriceDisplayWithDollars( $row['Price'] ) . "</td>";
            $date_object = DateTime::createFromFormat('Y-m-d H:i:s', $row['Date']);
            echo "<td style='padding:5px; border:1px #000 solid;'>".$date_object->format('m/d/Y  [h:i:s A]')."</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        echo "</div>";
        echo "</div>";
    echo "</span>";
?>

</body>