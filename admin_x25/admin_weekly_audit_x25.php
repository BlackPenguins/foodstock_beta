<?php
    include(__DIR__ . "/../appendix.php" );
    
    $url = ADMIN_WEEKLY_AUDIT_LINK;
    include( HEADER_PATH );
    include_once( ACTION_FUNCTIONS_PATH );

    echo "<span class='admin_box'>";

    // ------------------------------------
    // INVENTORY MODAL - ALL ITEMS
    // ------------------------------------
    echo "<div id='inventory_audit'>";

    echo "Money that is missing are missing items from the blue rows and it can be caused by:";
    echo "<ul>";
    echo "<li>Money that was stolen from the mugs</li>";
    echo "<li>Items that were stolen from the cabinet or fridge</li>";
    echo "<li>User forgot to purchase the item on the site</li>";
    echo "<li>Matt miscounted the inventory</li>";
    echo "<li><b>New issue:</b> They did a delayed purchase. So they took 4 sodas. I did the audit and saw them missing. But they then purchased them the week after the audit.</li>";
    echo "<li><b>Newest issue:</b> A bug happens during purchasing so the entire thing is never logged in the site. (database locked with Matt on May 23rd)</li>";
    echo "</ul>";

    echo "<div class='center_piece'>";
    echo "<div class='rounded_table_no_border'>";
    echo "<table>";
    echo "<thead><tr>";
    echo "<th>AuditID</th><th>Date</th><th>Actual Money in Mug</th><th>Expected Money</th><th>Missing Money</th><th>Profit</th>";
    echo "</tr>";
    printAudits( $db );
    echo "</table>";
    echo "</div>";
    echo "</div>";
    echo "</div>";


    echo "</span>";

    /**
     * @param $db SQLite3
     */
    function printAudits( $db ) {
        $statement = $db->prepare("SELECT a.AuditID, a.Date, a.MissingMoney, a.ItemType, p.Amount from Audit a JOIN Payments p ON a.auditID = p.auditID ORDER BY a.AuditID DESC");
        $results = $statement->execute();

//    $results = $db->query("SELECT i.Name, i.Price, i.DiscountPrice, i.BackstockQuantity, i.ShelfQuantity, i.ID, (select count(*) from Purchase_History p where p.Date > '2019-02-13 19:01:34' and p.ItemID = i.ID) SitePurchases, (select sum(d.ShelfQuantity - d.ShelfQuantityBefore) AddedAmount from Daily_Amount d where d.Date > '2019-02-13 19:01:34' and d.ItemID = i.ID and (d.BackstockQuantityBefore - d.BackstockQuantity > 0) ) AddedItems FROM Item i WHERE i.Hidden != 1 AND i.Type ='" . $itemType . "' AND (i.BackstockQuantity + i.ShelfQuantity) > 0 ORDER BY i.ShelfQuantity DESC, Name asc, i.Retired");
        while ($row = $results->fetchArray()) {
            $auditID = $row['AuditID'];
            $date = $row['Date'];
            $moneyInMug = $row['Amount'];
            $itemType = $row['ItemType'];

            $auditDetails = getAuditDetails( $db, $auditID, $itemType );

            if( $auditDetails != null ) {
                $missingMoney = $auditDetails->getTotalIncomeForAudit() - $moneyInMug;
                $misingMoneyColor = $missingMoney > 0 ? "#b10505" : "#07b91d";
                $profitColor = $auditDetails->getTotalProfitForAudit() < 0 ? "#b10505" : "#07b91d";

                echo "<tr>";
                echo "<td><b>Audit Week #$auditID<br>Audit Week #" . $auditDetails->getPreviousAuditID() . "</b></td>";
                echo "<td><span>$date<br>" . $auditDetails->getPreviousAuditDate() . "</span></td>";
                echo "<td><span>" . getPriceDisplayWithDollars($moneyInMug) . "</span></td>";
                echo "<td><span>" . getPriceDisplayWithDollars( $auditDetails->getTotalIncomeForAudit() ) . "</span></td>";
                echo "<td><span style='color:$misingMoneyColor; font-weight:bold; font-size: 2em;'>" . getPriceDisplayWithDollars($missingMoney) . "</span></td>";
                echo "<td><span style='color:$profitColor; font-weight:bold; font-size: 2em;'>" . getPriceDisplayWithDollars($auditDetails->getTotalProfitForAudit()) . "</span></td>";
                echo "</tr>";
                echo "<tr>";
                echo "<td colspan='7'>" . $auditDetails->getBreakdownTable() . "</td>";
                echo "</tr>";
            }
        }
    }
?>
</body>