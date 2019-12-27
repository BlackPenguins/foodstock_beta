<meta name="viewport" content="width=device-width, initial-scale=1">

<?php
    include(__DIR__ . "/../appendix.php" );
    
    $url = ADMIN_RESTOCK_LINK;
    include( HEADER_PATH );
?>  

<script type="text/javascript">
    function cancelRestock(restockID, name) {
        $isAlert = confirm('Are you sure that you want cancel restock for ' + name + '?');
        
        if ( $isAlert ) {
            alert("Restock cancelled.");

            $.post("<?php echo AJAX_LINK; ?>", { 
                type:'CancelRestock',
                RestockID:restockID,
            },function(data) {
                // Do nothing right now
            });
        }
    }
</script>

<?php
    echo "<span class='admin_box'>";
        // ------------------------------------
        // RESTOCK TABLE
        // ------------------------------------
        echo "<div class='center_piece'>";
        echo "<span class='hidden_mobile_section'>Red = Lost Money on Purchase.&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;Red Rows = Discontinued.</span>";
        echo "<div class='rounded_table_no_border'>";
        echo "<table>";
        echo "<thead><tr class='table_header'>";
        echo "<th class='hidden_mobile_column admin_header_column' align='left'>&nbsp;</th>";
        echo "<th class='admin_header_column' align='left'>Item</th>";
        echo "<th class='hidden_mobile_column admin_header_column' align='left'>Date</th>";
        echo "<th class='admin_header_column' align='left'>Number of Units</th>";
        echo "<th class='admin_header_column' align='left'>Retail Total Cost</th>";
        echo "<th class='hidden_mobile_column admin_header_column' align='left'>Retail Cost Each</th>";
        echo "<th class='hidden_mobile_column admin_header_column' align='left'>Current Price</th>";
        echo "<th class='hidden_mobile_column admin_header_column' align='left'>Discount Price</th>";
        echo "<th class='hidden_mobile_column admin_header_column' align='left'>Margin per Unit</th>";
        
        echo "</tr></thead>";
        
        $rowClass = "odd";
        $previousItem = "";
        
        $statement = $db->prepare("SELECT s.Name, r.RestockID, r.Cancelled, r.ItemID, r.Date, r.NumberOfCans, r.Cost, (r.Cost/r.NumberOfCans) as 'CostEach', s.Price, s.DiscountPrice, s.Retired " .
            "FROM Restock r " .
            "JOIN Item s ON r.itemID = s.id " .
            "ORDER BY r.Date DESC");
        $results = $statement->execute();

        while ($row = $results->fetchArray()) {
            $maxCostEach = "";
            if( $previousItem != "" && $previousItem != $row['Name'] ) {
                if( $rowClass == "odd" ) { $rowClass = "even"; } else { $rowClass = "odd"; }
                $maxCostEach = "font-weight:bold; font-size:1.1em;";
            }
            
            if( $row['Retired'] == 1) {
                $rowClass = "discontinued_row";
            }
            
            $cancelled = $row['Cancelled'];
            $restockID = $row['RestockID'];
            $name = $row['Name'];
            
            
            echo "<tr class='$rowClass'>";

            echo "<td class='button_cell hidden_mobile_column'>";
            if( $cancelled !=  1 ) {
                echo "<div onclick='cancelRestock($restockID, \"$name\");' class='nav_buttons nav_buttons_snack'>Cancel Restock</div>";
            } else {
                echo "<div style='font-weight:bold; text-align:center;'>Cancelled</div>";
            }
            echo "</td>";

            echo "<td >" . $name . "</td>";
            $date_object = DateTime::createFromFormat('Y-m-d H:i:s', $row['Date']);
            echo "<td class='hidden_mobile_column'>".$date_object->format('m/d/Y  [h:i:s A]')."</td>";
            echo "<td >" . $row['NumberOfCans'] . "</td>";
            echo "<td >" . getPriceDisplayWithDollars( $row['Cost'] ) . "</td>";
            $costEach = round( $row['CostEach'] );
            
            $lowestPrice = $row['Price'];
            
            if( $row['DiscountPrice'] != 0 ) {
                $lowestPrice = $row['DiscountPrice'];    
            }
            
            $margin = $lowestPrice - $costEach;
            $marginColor = "";

            if($margin < 0 ) {
                $marginColor = "background-color: #8a3535; color: #000000;";
            }
            echo "<td class='hidden_mobile_column' style='$maxCostEach border:0;'>" . getPriceDisplayWithDollars( $costEach )  . "</td>";
            echo "<td class='hidden_mobile_column' style='$maxCostEach border:0;'>" . getPriceDisplayWithDollars( $row['Price'] )  . "</td>";
            echo "<td class='hidden_mobile_column'style='$maxCostEach border:0;'>" . getPriceDisplayWithDollars( $row['DiscountPrice'] )  . "</td>";
            echo "<td class='hidden_mobile_column'style='$maxCostEach border:0; $marginColor'>" . getPriceDisplayWithDollars( $margin  )  . "</td>";
            echo "</tr>";
            
            $previousItem = $row['Name'];
        }
        
        echo "</table>";
        echo "</div>";
        echo "</div>";
    echo "</span>";
?>

</body>