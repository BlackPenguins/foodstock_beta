<meta name="viewport" content="width=device-width, initial-scale=1">

<?php
    include(__DIR__ . "/../appendix.php" );
    
    $url = ADMIN_ITEMS_LINK;
    include( HEADER_PATH );
    
    echo "<span class='admin_box'>";
        echo "<div class='page_header'><span class='title'>Item Information</span></div>";
        
        echo "<div class='center_piece'>";
        echo "<div style='margin-right: 20px;'><a href = 'admin_download_csv_x25.php'>Download Inventory Spreadsheet</a></div>";

        echo "<div class='hidden_mobile_section'>Black Row = Sold Out.&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;Yellow Cell = Discounted price.&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;Red Row = Discontinued.</div>";
        echo "<div class='rounded_table_no_border'>";
        echo "<table>";
        echo "<thead><tr>";
        echo "<th class='hidden_mobile_column admin_header_column' align='left'>ID</th>";
        echo "<th class='hidden_mobile_column admin_header_column' align='left'>Type</th>";
        echo "<th class='admin_header_column' align='left'>Name</th>";
        echo "<th class='admin_header_column' align='left'>Price per Unit</th>";
        echo "<th class='admin_header_column' align='left'>Discount Price per Unit</th>";
        echo "<th class='hidden_mobile_column admin_header_column' align='left'>Date Created</th>";
        echo "<th class='admin_header_column' align='left'>Shelf Quantity</th>";
        echo "<th class='admin_header_column' align='left'>Backstock Quantity</th>";
        echo "<th class='hidden_mobile_column admin_header_column' align='left'>Total Units Bought</th>";
        echo "<th class='hidden_mobile_column admin_header_column' align='left'>Total Income</th>";
        echo "<th class='hidden_mobile_column admin_header_column' align='left'>Total Expenses</th>";
        echo "<th class='hidden_mobile_column admin_header_column' align='left'>Discontinued</th>";
        
        echo "</tr></thead>";
        
        $rowClass = "";

        $andVendorIDClause = "";

        if( IsVendor() ) {
            $andVendorIDClause = " AND VendorID = " .  $_SESSION['UserID'];
        }

        $statement = $db->prepare("SELECT ID, Type, Name, RefillTrigger, Date, TotalCans, " . getQuantityQuery() .
            ",Price, DiscountPrice, ItemIncome, ItemExpenses, Retired, Hidden FROM Item i WHERE Hidden != 1 $andVendorIDClause ORDER BY Retired, Type DESC, TotalAmount DESC");
        $results = $statement->execute();

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
            if( ( $row['TotalAmount'] == 0 || $row['RefillTrigger'] == 1 ) && !$isDiscontinued ) {
                $colorSoldOut = "style = 'background-color: #3c3c3c; color: #FFFFFF'";
            }

            echo "<tr $rowClass>";
            echo "<td class='hidden_mobile_column'>" . $row['ID'] . "</td>";
            echo "<td class='hidden_mobile_column'>" . $row['Type'] . "</td>";
            echo "<td $colorSoldOut>" . $row['Name'] . "</td>";
            echo "<td $colorPrice>" . getPriceDisplayWithDollars( $row['Price'] ) . "</td>";
            echo "<td $colorDiscount>" . getPriceDisplayWithDollars( $row['DiscountPrice'] ) . "</td>";
            echo "<td class='hidden_mobile_column'>" . $row['Date'] . "</td>";
            echo "<td>" . $row['ShelfAmount'] . "</td>";
            echo "<td>" . $row['BackstockAmount'] . "</td>";
            echo "<td class='hidden_mobile_column'>" . $row['TotalCans'] . "</td>";
            echo "<td class='hidden_mobile_column'>" . getPriceDisplayWithDollars( $row['ItemIncome'] ) . "</td>";
            echo "<td class='hidden_mobile_column'>" . getPriceDisplayWithDollars( $row['ItemExpenses'] ) . "</td>";
            echo "<td class='hidden_mobile_column'>". ( $isDiscontinued ? ( "YES" ) : ( "NO" ) ) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        echo "</div>";
        echo "</div>";
    echo "</span>";
?>

</body>