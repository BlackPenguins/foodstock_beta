<?php
    include(__DIR__ . "/../appendix.php" );
    
    $url = ADMIN_WEEKLY_AUDIT_LINK;
    include( HEADER_PATH );

    echo "<span style='width:86%; display:inline-block; border-left: 3px #000 solid;'>";

    // ------------------------------------
    // INVENTORY MODAL - ALL ITEMS
    // ------------------------------------
    echo "<div id='inventory_audit'>";
    echo "<div class='center_piece'>";
    echo "<div class='rounded_table_no_border'>";
    echo "<table>";
    echo "<thead><tr>";
    echo "<th>AuditID</th><th>Date</th><th>Money in Mug</th><th>Calculated Money</th><th>Missing Money</th>";
    echo "</tr>";

    $results = $db->query("SELECT a.AuditID, a.Date, a.MissingMoney, a.ItemType, p.Amount from Audit a JOIN Payments p ON a.auditID = p.auditID ORDER BY a.AuditID DESC");
//    $results = $db->query("SELECT i.Name, i.Price, i.DiscountPrice, i.BackstockQuantity, i.ShelfQuantity, i.ID, (select count(*) from Purchase_History p where p.Date > '2019-02-13 19:01:34' and p.ItemID = i.ID) SitePurchases, (select sum(d.ShelfQuantity - d.ShelfQuantityBefore) AddedAmount from Daily_Amount d where d.Date > '2019-02-13 19:01:34' and d.ItemID = i.ID and (d.BackstockQuantityBefore - d.BackstockQuantity > 0) ) AddedItems FROM Item i WHERE i.Hidden != 1 AND i.Type ='" . $itemType . "' AND (i.BackstockQuantity + i.ShelfQuantity) > 0 ORDER BY i.ShelfQuantity DESC, Name asc, i.Retired");
    $tabIndex = 1;
    while ($row = $results->fetchArray()) {
        $auditID = $row['AuditID'];
        $date = $row['Date'];
        $missingMoney = $row['MissingMoney'];
        $income = $row['Amount'];
        $itemType = $row['ItemType'];

        $resultsPreviousAudit = $db->query("SELECT AuditID, Date from Audit WHERE AuditID < $auditID and ItemType ='$itemType' ORDER BY AuditID DESC");
        $previousAuditArray = $resultsPreviousAudit->fetchArray();
        $previousAuditID = $previousAuditArray['AuditID'];
        $previousAuditDate = $previousAuditArray['Date'];

        if( $previousAuditID == "" ) {
            // No previous audit to compare to
            continue;
        }
        $breakdownTable = "";
        $totalIncomeForAudit = 0.0;

        $shelfBeforeSQL = "SELECT before.ShelfQuantity as BeforeQuantity, before.Date, before.Price as BeforePrice, after.ShelfQuantity as AfterQuantity, after.Date, after.AuditID, item.price as AfterPrice, item.Name, (select count(*) from Purchase_History p where p.Date > before.Date and p.ItemID = item.ID AND p.Date < after.Date) SitePurchases, (SELECT sum(d.ShelfQuantity) from Daily_Amount d WHERE d.ShelfQuantity > d.ShelfQuantityBefore AND d.ItemID = item.ID AND d.Date > before.Date AND d.Date < after.Date) Refills from Daily_Amount before JOIN Daily_Amount after ON before.ItemID = after.ItemID AND after.AuditID = $auditID JOIN Item item on before.ItemID = item.ID WHERE before.AuditID = $previousAuditID ORDER BY before.ItemID DESC";
        $resultsShelfBefore = $db->query( $shelfBeforeSQL );

        $breakdownTable .= "<div class='rounded_inner_table'>";
        $breakdownTable .= "<table>";
        $breakdownTable .= "<thead><tr>";
        $breakdownTable .= "<th>Item</th><th>Amount Before</th><th>Total Refilled</th><th>Total Site Purchases</th><th>Amount After</th><th>Total <u>Non-Site</u> Purchases</th><th>Full Price</th><th>Total</th>";
        $breakdownTable .= "</tr>";

        while ($rowShelfBefore = $resultsShelfBefore->fetchArray()) {
            $beforeQuantity = $rowShelfBefore['BeforeQuantity'];
            $afterQuantity = $rowShelfBefore['AfterQuantity'];
            $itemName = $rowShelfBefore['Name'];
            $beforePrice = $rowShelfBefore['BeforePrice'];
            $afterPrice = $rowShelfBefore['AfterPrice'];
            $sitePurchases = $rowShelfBefore['SitePurchases'];
            $refills = $rowShelfBefore['Refills'];

            if( $refills == "" ) { $refills = 0; }

            $differentPriceWarning = ( $beforePrice != $afterPrice ) ? "WARNING: DIFFERENT PRICE FOUND" : "";
            if( $beforeQuantity == $afterQuantity ) {
                continue;
            }

            $totalNonSitePurchases = $beforeQuantity - ($afterQuantity - $refills ) - $sitePurchases;
            $totalNonSitePurchasesIncome = number_format($totalNonSitePurchases * $beforePrice, 2);
            $rowType = $totalNonSitePurchases != 0 ? "" : "class='discontinued_row'";
            $refillColor = $refills > 0 ? "style='color:#07b91d; font-weight:bold;'" : "";
            $breakdownTable .= "<tr $rowType>";
            $breakdownTable .= "<td><b>$itemName</b></td>";
            $breakdownTable .= "<td>$beforeQuantity</td>";
            $breakdownTable .= "<td $refillColor>$refills</td>";
            $breakdownTable .= "<td>$sitePurchases</td>";
            $breakdownTable .= "<td>$afterQuantity</td>";
            $breakdownTable .= "<td><b>$totalNonSitePurchases<b></td>";
            $breakdownTable .= "<td>$" . number_format($beforePrice, 2) . "$differentPriceWarning</td>";
            $breakdownTable .= "<td>$$totalNonSitePurchasesIncome</td>";
            $breakdownTable .= "</tr>";

            $totalIncomeForAudit += $totalNonSitePurchasesIncome;
        }
        $breakdownTable .= "</table>";

        $missingMoney = $totalIncomeForAudit - $income;
        $misingMoneyColor = $missingMoney > 0 ? "#b10505" : "#07b91d";

        echo "<tr>";
        echo "<td><b>Audit Week #$auditID<br>Audit Week #$previousAuditID</b></td>";
        echo "<td><span>$date<br>$previousAuditDate</span></td>";
        echo "<td><span>$" . number_format($income, 2) . "</span></td>";
        echo "<td><span>$" . number_format($totalIncomeForAudit, 2) . "</span></td>";
        echo "<td><span style='color:$misingMoneyColor; font-weight:bold; font-size: 2em;'>$" . number_format($missingMoney, 2) . "</span></td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td colspan='7'>$breakdownTable</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    echo "</div>";
    echo "</div>";


    echo "</span>";
?>

</body>