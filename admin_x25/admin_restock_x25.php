<head>
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
    echo "<span style='width:86%; display:inline-block; border-left: 3px #000 solid;'>";
        // ------------------------------------
        // RESTOCK TABLE
        // ------------------------------------
        echo "<span class='soda_popout' style='display:inline-block; margin-left: 10px; width:100%; margin-top:15px; padding:5px;'><span style='font-size:26px;'>Restock Schedule</span></span>";
        echo "<table style='font-size:12; border-collapse:collapse; width:100%; margin-left: 10px;'>";
        echo "<thead><tr class='table_header'>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>&nbsp;</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Item</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Date</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Number of Units</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Retail Total Cost</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Retail Cost Each</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Current Price</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Discount Price</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Margin per Unit</th>";
        
        echo "</tr></thead>";
        
        $rowClass = "odd";
        $previousItem = "";
        
        $results = $db->query("SELECT s.Name, r.RestockID, r.Cancelled, r.ItemID, r.Date, r.NumberOfCans, r.Cost, (r.Cost/r.NumberOfCans) as 'CostEach', s.Price, s.DiscountPrice, s.Retired FROM Restock r JOIN Item s ON r.itemID = s.id  ORDER BY r.Date DESC");
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
            if( $cancelled !=  1 ) {
                echo "<td style='padding:5px; border:1px #000 solid;'><span onclick='cancelRestock($restockID, \"$name\");' class='nav_buttons nav_buttons_snack'>Cancel Restock</span></td>";
            } else {
                echo "<td style='padding:5px; border:1px #000 solid;'>Cancelled</td>";
            }
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $name . "</td>";
            $date_object = DateTime::createFromFormat('Y-m-d H:i:s', $row['Date']);
            echo "<td style='padding:5px; border:1px #000 solid;'>".$date_object->format('m/d/Y  [h:i:s A]')."</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $row['NumberOfCans'] . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>$" . number_format( $row['Cost'], 2) . "</td>";
            $costEach = $row['CostEach'];
            
            $lowestPrice = $row['Price'];
            
            if( $row['DiscountPrice'] != 0 ) {
                $lowestPrice = $row['DiscountPrice'];    
            }
            
            $margin = $lowestPrice - $costEach;
            $marginColor = "";
            
            if($margin < 0 ) {
                $marginColor = "background-color: #8a3535;";
            }
            echo "<td style='padding:5px; $maxCostEach border:1px #000 solid;'>$" . number_format( $costEach, 2 )  . "</td>";
            echo "<td style='padding:5px; $maxCostEach border:1px #000 solid;'>$" . number_format( $row['Price'], 2 )  . "</td>";
            echo "<td style='padding:5px; $maxCostEach border:1px #000 solid;'>$" . number_format( $row['DiscountPrice'], 2 )  . "</td>";
            echo "<td style='padding:5px; $maxCostEach border:1px #000 solid; $marginColor'>$" . number_format( $margin, 2 )  . "</td>";
            echo "</tr>";
            
            $previousItem = $row['Name'];
        }
        
        echo "</table>";
    echo "</span>";
?>

</body>