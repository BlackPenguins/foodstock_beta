<meta name="viewport" content="width=device-width, initial-scale=1">

<?php
    include(__DIR__ . "/../appendix.php" );
    
    $url = ADMIN_ITEMS_IN_STOCK_LINK;
    include( HEADER_PATH );
    include_once(ACTION_FUNCTIONS_PATH);
    
    echo "<span class='admin_box'>";
        // ------------------------------------
        // ITEM TABLE
        // ------------------------------------
        echo "<div class='rounded_header'><span class='title'>Item In Stock</span></div>";
        
        echo "<div class='center_piece'>";
        echo "<span class='hidden_mobile_section'>Black = Sold Out.&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;Discounted price = Yellow.&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;Red Rows = Discontinued.</span>";
        echo "<div class='rounded_table_no_border'>";
        echo "<table>";
        echo "<thead><tr>";
        echo "<th class='admin_header_column' align='left'>&nbsp;</th>";
        echo "<th class='admin_header_column' align='left'>Name</th>";
        echo "<th class='hidden_mobile_column admin_header_column' align='left'>Type</th>";
        echo "<th class='admin_header_column' align='left'>Price per Unit</th>";
        echo "<th class='admin_header_column' align='left'>Discount Price per Unit</th>";
        echo "<th class='hidden_mobile_column admin_header_column' align='left'>Retail Price per Unit</th>";
        echo "<th class='hidden_mobile_column admin_header_column' align='left'>Profit per Unit</th>";
        echo "<th class='hidden_mobile_column admin_header_column' align='left'>Exp Date.</th>";
        echo "</tr></thead>";
        
        $rowClass = "";
        
        $statement = $db->prepare("SELECT COUNT(s.ItemDetailsID) as Count, s.ItemID, s.IsBackstock, i.Name, i.Type, d.Price, d.DiscountPrice, d.RetailPrice, d.ExpDate, s.Date " .
            "FROM Items_In_Stock s " .
            "JOIN ITEM i ON s.ItemID = i.ID " .
            "JOIN Item_Details d ON s.ItemDetailsID = d.ItemDetailsID " .
            "GROUP BY s.ItemDetailsID, s.IsBackstock " .
            "ORDER BY s.ItemID");
        $results = $statement->execute();

        while ($row = $results->fetchArray()) {
            $isDiscontinued = $row['IsBackstock'] == 1;

            if( $isDiscontinued ) {
                $rowClass = "class='backstock_row'";
            } else {
                $rowClass = "";
            }

            $retailPrice = $row['RetailPrice'];
            $price = $row['Price'];
            $discountPrice = $row['DiscountPrice'];
            $lowestPrice = getSitePurchasePrice( $discountPrice, $price );
            $profit = $lowestPrice - $retailPrice;

            $losingMoneyClass = "";

            if( $profit <= 0 ) {
                $losingMoneyClass = "style='color: #ff0000; font-weight: bold;'";
            }
            echo "<tr $rowClass>";
            echo "<td $losingMoneyClass>" . $row['Count'] . "</td>";
            echo "<td $losingMoneyClass>" . $row['Name'] . "</td>";
            echo "<td $losingMoneyClass class='hidden_mobile_column'>" . $row['Type'] . "</td>";
            echo "<td $losingMoneyClass>" . getPriceDisplayWithDollars( $price ) . "</td>";
            echo "<td $losingMoneyClass>" . getPriceDisplayWithDollars( $discountPrice ) . "</td>";
            echo "<td $losingMoneyClass>" . getPriceDisplayWithDollars( $retailPrice ) . "</td>";
            echo "<td $losingMoneyClass>" . getPriceDisplayWithDollars( $profit ) . "</td>";
            echo "<td $losingMoneyClass class='hidden_mobile_column'>" . $row['ExpDate'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        echo "</div>";
        echo "</div>";
    echo "</span>";
?>

</body>