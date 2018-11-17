
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
    echo "<span style='width:86%; display:inline-block; border-left: 3px #000 solid;'>";
        // ------------------------------------
        // INVENTORY TABLE
        // ------------------------------------
        echo "<span class='soda_popout' style='display:inline-block; margin-left: 10px; width:100%; margin-top:15px; padding:5px;'><span style='font-size:26px;'>Inventory/Purchases Schedule</span></span>";
        echo "<table style='font-size:12; border-collapse:collapse; width:100%; margin-left: 10px;'>";
        echo "<thead><tr class='table_header'>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>&nbsp;</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Item</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>User Name</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Date</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Shelf Quantity</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Backstock Quantity</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Price</th>";
        
        echo "</tr></thead>";
        
        $rowClass = "odd";
        $previousDate = "";
        
        $results = $db->query("SELECT p.CashOnly, i.Name, u.FirstName, u.LastName, r.Cancelled, r.ID, r.Date, r.BackstockQuantityBefore, r.BackstockQuantity, r.ShelfQuantityBefore, r.ShelfQuantity, r.Price FROM Daily_Amount r JOIN Item i ON r.itemID = i.id LEFT JOIN Purchase_History p ON r.ID = p.DailyAmountID LEFT JOIN User u on p.UserID = u.UserID WHERE r.Date >= date('now','-2 months') ORDER BY r.Date DESC");
        while ($row = $results->fetchArray()) {
            
            if( $previousDate != "" && $previousDate != $row['Date'] ) {
                if( $rowClass == "odd" ) { $rowClass = "even"; } else { $rowClass = "odd"; }
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
                }
                
                echo "<tr class='$rowClass'>";
                if( $cashOnly == 1 ) {
                    echo "<td style='padding:5px; border:1px #000 solid;'>Cash Only</td>";
                } else if( $cancelled != 1 ) {
                    if( trim($name) == "" ) {
                        echo "<td style='padding:5px; border:1px #000 solid;'><span onclick='cancelInventory($dailyAmountID, \"$itemName\");' class='nav_buttons nav_buttons_snack'>Cancel Inventory</span></td>";
                    } else {
                        echo "<td style='padding:5px; border:1px #000 solid;'><span onclick='cancelPurchase($dailyAmountID, \"$name - $itemName\");' class='nav_buttons nav_buttons_billing'>Cancel Purchase</span></td>";
                    }
                } else {
                    if( trim($name) == "" ) {
                        echo "<td style='padding:5px; border:1px #000 solid;'>Inventory Cancelled</td>";
                    } else {
                        echo "<td style='padding:5px; border:1px #000 solid;'>Purchase Cancelled</td>";
                    }
                }
                echo "<td style='padding:5px; border:1px #000 solid;'>" . $itemName . "</td>";
                echo "<td style='padding:5px; border:1px #000 solid;'>" . $name . "</td>";
                $date_object = DateTime::createFromFormat('Y-m-d H:i:s', $row['Date']);
                echo "<td style='padding:5px; border:1px #000 solid;'>".$date_object->format('m/d/Y  [h:i:s A]')."</td>";
                
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
                
                echo "<td style='padding:5px; border:1px #000 solid;'>" . $shelfQuantityDisplay . "</td>";
                echo "<td style='padding:5px; border:1px #000 solid;'>" . $backstockQuantityDisplay . "</td>";
                echo "<td style='padding:5px; border:1px #000 solid;'>$" . number_format( $row['Price'], 2) . "</td>";
                echo "</tr>";
            }
            
            $previousDate = $row['Date'];
        }
        
        echo "</table>";
    echo "</span>";
?>

</body>