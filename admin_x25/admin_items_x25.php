<head>
<meta name="viewport" content="width=device-width, initial-scale=1">

<?php
    include(__DIR__ . "/../appendix.php" );
    
    $url = ADMIN_ITEMS_LINK;
    include( HEADER_PATH );
    
    echo "<span class='admin_box'>";
        // ------------------------------------
        // ITEM TABLE
        // ------------------------------------
        echo "<div class='rounded_header'><span class='title'>Item Inventory</span></div>";
        
        echo "<div class='center_piece'>";
        echo "<span class='hidden_mobile_section'>Black = Sold Out.&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;Discounted price = Yellow.&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;Red Rows = Discontinued.</span>";
        echo "<div class='rounded_table_no_border'>";
        echo "<table>";
        echo "<thead><tr>";
        echo "<th class='hidden_mobile_column admin_header_column' align='left'>ID</th>";
        echo "<th class='hidden_mobile_column admin_header_column' align='left'>Type</th>";
        echo "<th class='admin_header_column' align='left'>Name</th>";
        echo "<th class='admin_header_column' align='left'>Price per Unit</th>";
        echo "<th class='admin_header_column' align='left'>Discount Price per Unit</th>";
        echo "<th class='hidden_mobile_column admin_header_column' align='left'>Date Created</th>";
        echo "<th class='hidden_mobile_column admin_header_column' align='left'>Date Modified</th>";
        echo "<th class='hidden_mobile_column admin_header_column' align='left'>Chart Color</th>";
        echo "<th class='admin_header_column' align='left'>Shelf Quantity</th>";
        echo "<th class='admin_header_column' align='left'>Backstock Quantity</th>";
        echo "<th class='hidden_mobile_column admin_header_column' align='left'>Total Units Bought</th>";
        echo "<th class='hidden_mobile_column admin_header_column' align='left'>Total Income</th>";
        echo "<th class='hidden_mobile_column admin_header_column' align='left'>Total Expenses</th>";
        echo "<th class='hidden_mobile_column admin_header_column' align='left'>Discontinued</th>";
        
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
            echo "<td class='hidden_mobile_column'>" . $row['ID'] . "</td>";
            echo "<td class='hidden_mobile_column'>" . $row['Type'] . "</td>";
            echo "<td $colorSoldOut>" . $row['Name'] . "</td>";
            echo "<td $colorPrice>" . getPriceDisplayWithDollars( $row['Price'] ) . "</td>";
            echo "<td $colorDiscount>" . getPriceDisplayWithDollars( $row['DiscountPrice'] ) . "</td>";
            echo "<td class='hidden_mobile_column'>" . $row['Date'] . "</td>";
            echo "<td class='hidden_mobile_column'>" . $row['DateModified'] . " (" . $row['ModifyType'] . ")</td>";
            echo "<td class='hidden_mobile_column'>" . $row['ChartColor'] . "</td>";
            echo "<td>" . $row['ShelfQuantity'] . "</td>";
            echo "<td>" . $row['BackstockQuantity'] . "</td>";
            echo "<td class='hidden_mobile_column'>" . $row['TotalCans'] . "</td>";
            echo "<td class='hidden_mobile_column'>" . getPriceDisplayWithDollars( $row['TotalIncome'] ) . "</td>";
            echo "<td class='hidden_mobile_column'>" . getPriceDisplayWithDollars( $row['TotalExpenses'] ) . "</td>";
            echo "<td class='hidden_mobile_column'>". ( $isDiscontinued ? ( "YES" ) : ( "NO" ) ) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        echo "</div>";
        echo "</div>";
    echo "</span>";
?>

</body>