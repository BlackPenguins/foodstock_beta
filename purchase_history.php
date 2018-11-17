<head>
<meta name="viewport" content="width=device-width, initial-scale=1">

<?php
    include( "appendix.php" );
    $url = PURCHASE_HISTORY_LINK;
    include( HEADER_PATH );
    
    $itemType = $_GET['type'];
    
   
    if( $isLoggedInAdmin && isset($_GET['userid'] ) && isset($_GET['name'] )  ) {
        $userID = $_GET['userid'];
        $name = $_GET['name'];
    } else {
        $userID = $_SESSION['UserID'];
        $name = $_SESSION['FirstName'];
    }
    
    // ------------------------------------
    // PURCHASE HISTORY TABLE
    // ------------------------------------
    echo "<div class='soda_popout'  style='margin:10px; padding:5px;'><span style='font-size:26px;'>Purchase History for '$name'</span> <span style='font-size:0.8em;'></span>";
    $totalSavings = 0.0;
    $totalBalance = 0.0;
    
    $results = $db->query("SELECT p.Cost, p.DiscountCost FROM Purchase_History p JOIN Item i on p.itemID = i.ID WHERE p.UserID = $userID AND i.Type='$itemType' AND Cancelled IS NULL");
    while ($row = $results->fetchArray()) {
        
        if( $row['DiscountCost'] != "" ) {
            $totalSavings += ($row['Cost'] - $row['DiscountCost']);
            $totalBalance += $row['DiscountCost'];
        } else {
            $totalBalance += $row['Cost'];
        }
    }
    
    echo  "<span style='float:right;'><b>Total Spent:</b> $". number_format($totalBalance,2) . "&nbsp;&nbsp;|&nbsp;&nbsp;<b>Total Savings:</b> $" . number_format($totalSavings, 2) . "</span>";
    
    echo "</div>";
    
    echo "<div style='margin:10px; padding:5px;'>";
    echo "View monthly statements in the <b>Billing</b> section (now at the top).";
    
    echo "</div>";
    echo "<div id='restock_all'>";
    echo "<table style='font-size:12; border-collapse:collapse; margin:10px; width:98%'>";
    echo "<thead><tr class='table_header'>";
    echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Item</th>";
    echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Cost</th>";
    echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Date Purchased</th>";
    
    echo "</tr></thead>";
    
    $rowClass = "odd";
    
    $results = $db->query("SELECT i.Name, p.Cancelled, p.Cost, p.DiscountCost, p.Date, p.UserID, p.CashOnly FROM Purchase_History p JOIN Item i on p.itemID = i.ID WHERE p.UserID = $userID AND i.Type='$itemType' ORDER BY p.Date DESC");
    $currentWeek = "";
    while ($row = $results->fetchArray()) {
        $date_object = DateTime::createFromFormat('Y-m-d H:i:s', $row['Date']);
        
        $weekOfPurchase = $date_object->format('W');
        
        if( $currentWeek == "" ) {
            $currentWeek = $weekOfPurchase;
        } else {
            if( $currentWeek != $weekOfPurchase ) {
                // New week
                echo "<tr>";
                echo "<td style='padding:5px; font-style:italic; padding-left: 20px; border:1px #000 solid; background-color:#7a8020;' colspan='3'>";
                echo "Week " . $weekOfPurchase;
                echo "</td>";
                echo "</tr>";
            
                $currentWeek = $weekOfPurchase;
            }
        }
        $isCancelled = $row['Cancelled'] === 1;
        $itemName = $row['Name'];
        $discountAmountDisplay = "$" . number_format($row['DiscountCost'],2);
        $costAmountDisplay = "$" . number_format($row['Cost'], 2);
        
        if( $isCancelled ) {
            $rowClass = "discontinued_row";
            $discountAmountDisplay .= " (REFUNDED)";
            $costAmountDisplay .= " (REFUNDED)";
        }
        
        echo "<tr class='$rowClass'>";
        echo "<td style='padding:5px; border:1px #000 solid;'>" . $itemName . "</td>";
        
        $costDisplay = "";
        
        if( $row['DiscountCost'] != "" ) {
            $costDisplay = "<span class='red_price'>$" . number_format($row['Cost'], 2) . "</span>" . $discountAmountDisplay;
        } else {
            $costDisplay = $costAmountDisplay;
        }
        
        if( $row['CashOnly'] == 1 ) {
            $costDisplay = $costDisplay . "<span style='float:right; font-weight: bold; color:#023e0c;'>(CASH - ONLY)</span>";
        }
        
        echo "<td style='padding:5px; border:1px #000 solid;'>" . $costDisplay ."</td>";
        echo "<td style='padding:5px; border:1px #000 solid;'>" . $date_object->format('l m/d/Y  [h:i:s A]') . "</td>";
        echo "</tr>";
        
        if( $rowClass == "odd" ) { $rowClass = "even"; } else { $rowClass = "odd"; }
    }
    
        echo "</table>";
    echo "</div>";
?>

</body>