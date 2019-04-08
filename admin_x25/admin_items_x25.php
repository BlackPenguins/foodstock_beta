<head>
<meta name="viewport" content="width=device-width, initial-scale=1">

<?php
    include(__DIR__ . "/../appendix.php" );
    
    $url = ADMIN_ITEMS_LINK;
    include( HEADER_PATH );
    
    echo "<span style='width:86%; display:inline-block; border-left: 3px #000 solid;'>";
        // ------------------------------------
        // ITEM TABLE
        // ------------------------------------
        echo "<div class='rounded_header'><span class='title'>Item Inventory</span></div>";
        
        echo "<div class='center_piece'>";
        echo "Black = Sold Out.&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;Discounted price = Yellow.&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;Red Rows = Discontinued.";
        echo "<div class='rounded_table_no_border'>";
        echo "<table>";
        echo "<thead><tr>";
        echo "<th align='left'>ID</th>";
        echo "<th align='left'>Type</th>";
        echo "<th align='left'>Name</th>";
        echo "<th align='left'>Price per Unit</th>";
        echo "<th align='left'>Discount Price per Unit</th>";
        echo "<th align='left'>Date Created</th>";
        echo "<th align='left'>Date Modified</th>";
        echo "<th align='left'>Chart Color</th>";
        echo "<th align='left'>Shelf Quantity</th>";
        echo "<th align='left'>Backstock Quantity</th>";
        echo "<th align='left'>Total Units Bought</th>";
        echo "<th align='left'>Total Income</th>";
        echo "<th align='left'>Total Expenses</th>";
        echo "<th align='left'>Discontinued</th>";
        
        echo "</tr></thead>";
        
        $rowClass = "";
        
        $results = $db->query("SELECT ID, Type, Name, RefillTrigger, Date, DateModified, ModifyType, ChartColor, TotalCans, BackstockQuantity, ShelfQuantity, Price, DiscountPrice, TotalIncome, TotalExpenses, Retired, Hidden, (ShelfQuantity + BackstockQuantity) as Total FROM Item WHERE Hidden != 1 ORDER BY Retired, Type DESC, Total ASC");
        while ($row = $results->fetchArray()) {
            $isDiscontinued = $row['Retired'] == 1;
            
            if( $isDiscontinued ) {
                $rowClass = "class='discontinued_row'";
            }
            
            $colorPrice = "";
            $colorDiscount = "";
            
            if( $row['DiscountPrice'] == 0 ) {
                $colorPrice = "style = 'background-color: #fdff80; color:#000000;'";
            } else {
                $colorDiscount = "style = 'background-color: #fdff80; color:#000000;'";
            }
            
            $colorSoldOut = "";
            if( ( $row['Total'] == 0 || $row['RefillTrigger'] == 1 ) && !$isDiscontinued ) {
                $colorSoldOut = "style = 'background-color: #3c3c3c; color: #FFFFFF'";
            }

            echo "<tr $rowClass>";
            echo "<td>" . $row['ID'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td $colorSoldOut>" . $row['Name'] . "</td>";
            echo "<td $colorPrice>$" . number_format( $row['Price'], 2) . "</td>";
            echo "<td $colorDiscount>$" . number_format( $row['DiscountPrice'], 2) . "</td>";
            echo "<td>" . $row['Date'] . "</td>";
            echo "<td>" . $row['DateModified'] . " (" . $row['ModifyType'] . ")</td>";
            echo "<td>" . $row['ChartColor'] . "</td>";
            echo "<td>" . $row['ShelfQuantity'] . "</td>";
            echo "<td>" . $row['BackstockQuantity'] . "</td>";
            echo "<td>" . $row['TotalCans'] . "</td>";
            echo "<td>$" . number_format( $row['TotalIncome'], 2) . "</td>";
            echo "<td>$" . number_format( $row['TotalExpenses'], 2) . "</td>";
            echo "<td>". ( $isDiscontinued ? ( "YES" ) : ( "NO" ) ) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        echo "</div>";
        echo "</div>";
    echo "</span>";
?>

</body>