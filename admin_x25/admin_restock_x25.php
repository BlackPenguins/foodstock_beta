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
        echo "<div class='center_piece'>";
        echo "Red = Lost Money on Purchase.&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;Red Rows = Discontinued.";
        echo "<div class='rounded_table_no_border'>";
        echo "<table>";
        echo "<thead><tr class='table_header'>";
        echo "<th align='left'>&nbsp;</th>";
        echo "<th align='left'>Item</th>";
        echo "<th align='left'>Date</th>";
        echo "<th align='left'>Number of Units</th>";
        echo "<th align='left'>Retail Total Cost</th>";
        echo "<th align='left'>Retail Cost Each</th>";
        echo "<th align='left'>Current Price</th>";
        echo "<th align='left'>Discount Price</th>";
        echo "<th align='left'>Margin per Unit</th>";
        
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

            echo "<td class='button_cell'>";
            if( $cancelled !=  1 ) {
                echo "<div onclick='cancelRestock($restockID, \"$name\");' class='nav_buttons nav_buttons_snack'>Cancel Restock</div>";
            } else {
                echo "<div style='font-weight:bold; text-align:center;'>Cancelled</div>";
            }
            echo "</td>";

            echo "<td>" . $name . "</td>";
            $date_object = DateTime::createFromFormat('Y-m-d H:i:s', $row['Date']);
            echo "<td>".$date_object->format('m/d/Y  [h:i:s A]')."</td>";
            echo "<td>" . $row['NumberOfCans'] . "</td>";
            echo "<td>$" . number_format( $row['Cost'], 2) . "</td>";
            $costEach = $row['CostEach'];
            
            $lowestPrice = $row['Price'];
            
            if( $row['DiscountPrice'] != 0 ) {
                $lowestPrice = $row['DiscountPrice'];    
            }
            
            $margin = $lowestPrice - $costEach;
            $marginColor = "";

            if($margin < 0 ) {
                $marginColor = "background-color: #8a3535; color: #000000;";
            }
            echo "<td style='$maxCostEach border:0;'>$" . number_format( $costEach, 2 )  . "</td>";
            echo "<td style='$maxCostEach border:0;'>$" . number_format( $row['Price'], 2 )  . "</td>";
            echo "<td style='$maxCostEach border:0;'>$" . number_format( $row['DiscountPrice'], 2 )  . "</td>";
            echo "<td style='$maxCostEach border:0; $marginColor'>$" . number_format( $margin, 2 )  . "</td>";
            echo "</tr>";
            
            $previousItem = $row['Name'];
        }
        
        echo "</table>";
        echo "</div>";
        echo "</div>";
    echo "</span>";
?>

</body>