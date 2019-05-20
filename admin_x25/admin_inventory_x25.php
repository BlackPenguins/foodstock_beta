
<?php
    include(__DIR__ . "/../appendix.php" );
    
    $url = ADMIN_INVENTORY_LINK;
    include( HEADER_PATH );

    ?>
    <script type="text/javascript">
        function cancelInventory(dailyAmountID, name) {
            $isAlert = confirm('Are you sure that you want cancel inventory for ' + name + '?');
            
            if ( $isAlert ) {
                alert("Inventory cancelled.");
    
                $.post("<?php echo AJAX_LINK; ?>", { 
                    type:'CancelInventory',
                    DailyAmountID:dailyAmountID,
                },function(data) {
                    // Do nothing right now
                });
            }
        }
    
        function cancelPurchase(dailyAmountID, name) {
            $isAlert = confirm('Are you sure that you want cancel purchase for ' + name + '?');
            
            if ( $isAlert ) {
                alert("Purchase cancelled.");
    
                $.post("<?php echo AJAX_LINK; ?>", { 
                    type:'CancelPurchase',
                    DailyAmountID:dailyAmountID,
                },function(data) {
                    // Do nothing right now
                });
            }
        }
    </script>
    
    <?php 
    echo "<span class='admin_box'>";
        // ------------------------------------
        // INVENTORY TABLE
        // ------------------------------------
        echo "<div class='center_piece'>";
        echo "<span class='hidden_mobile_section'>Yellow = Inventory.&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;Blue = Refill.</span>";
        echo "<div class='rounded_table_no_border'>";
        echo "<table>";
        echo "<thead><tr>";
        echo "<th class='hidden_mobile_column admin_header_column' align='left'>&nbsp;</th>";
        echo "<th class='admin_header_column' align='left'>Item</th>";
        echo "<th class='admin_header_column' align='left'>User Name</th>";
        echo "<th class='admin_header_column' align='left'>Date</th>";
        echo "<th class='hidden_mobile_column admin_header_column' align='left'>Shelf Quantity</th>";
        echo "<th class='hidden_mobile_column admin_header_column' align='left'>Backstock Quantity</th>";
        echo "<th class='hidden_mobile_column admin_header_column' align='left'>Price</th>";
        
        echo "</tr></thead>";
        
        $rowClass = "odd_manual";
        $previousDate = "";
        
        $results = $db->query("SELECT p.CashOnly, i.Name, u.FirstName, u.LastName, r.Cancelled, r.ID, r.Date, r.BackstockQuantityBefore, r.BackstockQuantity, r.ShelfQuantityBefore, r.ShelfQuantity, r.Price FROM Daily_Amount r JOIN Item i ON r.itemID = i.id LEFT JOIN Purchase_History p ON r.ID = p.DailyAmountID LEFT JOIN User u on p.UserID = u.UserID WHERE r.Date >= date('now','-2 months') ORDER BY r.Date DESC");
        while ($row = $results->fetchArray()) {

            if( $previousDate != "" && $previousDate != $row['Date'] ) {
                if( $rowClass == "odd_manual" ) {
                    $rowClass = "even_manual";
                } else {
                    $rowClass = "odd_manual";
                }
            }

            $name = $row['FirstName'] . " " . $row['LastName'];
            
            $backstockQuantityBefore = $row['BackstockQuantityBefore'];
            $backstockQuantityAfter = $row['BackstockQuantity'];
            
            $shelfQuantityBefore = $row['ShelfQuantityBefore'];
            $shelfQuantityAfter = $row['ShelfQuantity'];
            $cancelled = $row['Cancelled'];
            $cashOnly = $row['CashOnly'];
            $dailyAmountID = $row['ID'];
            $itemName = $row['Name'];
            
            $shelfQuantityDelta = ( $shelfQuantityAfter - $shelfQuantityBefore );
            $backstockQuantityDelta = ( $backstockQuantityAfter - $backstockQuantityBefore );
            
            
            if( $shelfQuantityDelta != 0 || $backstockQuantityDelta != 0 ) {
                if( $shelfQuantityDelta == ($backstockQuantityDelta * -1 ) ) {
                    $rowClass = "restock_row";
                } else if( trim($name) == "" ) {
                    $rowClass = "refill_row";
                }
                
                echo "<tr class='$rowClass'>";

                $date_object = DateTime::createFromFormat('Y-m-d H:i:s', $row['Date']);

                $shelfQuantityDisplay = "&nbsp;";
                $backstockQuantityDisplay = "&nbsp;";

                if( $shelfQuantityDelta != 0) {
                    $sign = $shelfQuantityDelta > 0 ? "+" : "";
                    $shelfQuantityDelta = $sign . $shelfQuantityDelta;
                    $shelfQuantityDisplay = "$shelfQuantityBefore --> $shelfQuantityAfter <span style='float:right; font-size:1.5em;'>" . $shelfQuantityDelta . "</span>";
                }
                if( $backstockQuantityDelta != 0) {
                    $sign = $backstockQuantityDelta > 0 ? "+" : "";
                    $backstockQuantityDelta = $sign . $backstockQuantityDelta;
                    $backstockQuantityDisplay = "$backstockQuantityBefore --> $backstockQuantityAfter <span style='float:right; font-size:1.5em;'>" . $backstockQuantityDelta . "</span>";
                }

                echo "<td class='hidden_mobile_column' class='button_cell'>";
                if( $cashOnly == 1 ) {
                    echo "Cash Only";
                } else if( $cancelled != 1 ) {
                    if( trim($name) == "" ) {
                        echo "<div onclick='cancelInventory($dailyAmountID, \"$itemName\");' class='nav_buttons nav_buttons_snack'>Cancel Inventory</div>";
                    } else {
                        echo "<div onclick='cancelPurchase($dailyAmountID, \"$name - $itemName\");' class='nav_buttons nav_buttons_billing'>Cancel Purchase</div>";
                    }
                } else {
                    if( trim($name) == "" ) {
                        echo "<div style='font-weight:bold; text-align:center;'>Inventory Cancelled</div>";
                    } else {
                        echo "<div style='font-weight:bold; text-align:center;'>Purchase Cancelled</div>";
                    }
                }
                echo "</td>";

                echo "<td>" . $itemName . "</td>";
                echo "<td>" . $name . "</td>";
                echo "<td>" . $date_object->format('m/d/Y  [h:i:s A]')."</td>";
                echo "<td class='hidden_mobile_column'>" . $shelfQuantityDisplay . "</td>";
                echo "<td class='hidden_mobile_column'>" . $backstockQuantityDisplay . "</td>";
                echo "<td class='hidden_mobile_column'>" . getPriceDisplayWithDollars( $row['Price'] ) . "</td>";
                echo "</tr>";
            }
            
            $previousDate = $row['Date'];
        }
        
        echo "</table>";
        echo "</div>";
        echo "</div>";
    echo "</span>";
?>

</body>