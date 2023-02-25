<?php
    include(__DIR__ . "/../appendix.php" );
    
    $url = ADMIN_AUDIT_REPORT_LINK;
    include( HEADER_PATH );
    
    echo "<span class='admin_box'>";
        // ------------------------------------
        // ITEM TABLE
        // ------------------------------------
        echo "<span  style='display:inline-block; margin-left: 10px; width:100%; margin-top:15px; padding:5px;'><span style='font-size:26px;'>Item Inventory</span></span>";
        echo "<table style='font-size:12; border-collapse:collapse; width:100%; margin-bottom: 20px; margin-left: 10px;'>";
        echo "<thead><tr class='table_header'>";
        echo "<th align='left'>ID</th>";
        echo "<th align='left'>Type</th>";
        echo "<th align='left'>Name</th>";
        echo "<th align='left'>Total Sold</th>";
        echo "<th align='left'>Total Sold Through Site</th>";
        echo "<th align='left'>Total Sold Through Non-Site</th>";
        echo "<th align='left'>Income (Purchase History)</th>";
        echo "<th align='left'>Income (Non-Site)</th>";
        echo "<th align='left'>Income (Total Income)</th>";
        echo "<th align='left'>Income (on Card)</th>";
        echo "<th align='left'>Expenses (Restock)</th>";
        echo "<th align='left'>Expenses (on Card)</th>";
        echo "<th align='left'>Profit (Restock)</th>";
        echo "<th align='left'>Profit (on Card)</th>";
        
        echo "</tr></thead>";
        
        $rowClass = "odd";
        $previousType = "";
        
        $allIncome = 0.0;
        $allIncomeCard = 0.0;
        
        $allExpenses = 0.0;
        $allExpensesCard = 0.0;
        $statement = $db->prepare("SELECT ID, Type, Name, RefillTrigger, Date, TotalCans, " . getQuantityQuery() .
            ",Price, DiscountPrice, TotalIncome, TotalExpenses, Retired, Hidden " .
            "FROM Item i " .
            "WHERE hidden != 1 " .
            "ORDER BY Hidden, Type DESC, Name ASC");
        $results = $statement->execute();

        while ($row = $results->fetchArray()) {
            $type = $row['Type'];
            
            if( $previousType != "" && $previousType != $type ) {
                summary( $db, $previousType );
            }
            $previousType = $type;
            
            $totalUnits = ($row['TotalCans'] - ($row['BackstockAmount'] + $row['ShelfAmount']));
            
            $inSiteIncome = 0.0;
            $inSiteCount = 0;
            $statementPurchases = $db->prepare("SELECT * from Purchase_History p WHERE p.ItemID = :itemID" );
            $statementPurchases->bindValue( ":itemID", $row['ID'] );
            $resultsPurchases = $statementPurchases->execute();

            while ($rowPurchases = $resultsPurchases->fetchArray()) {
                $discountPrice = $rowPurchases['DiscountCost'];
                $regularPrice = $rowPurchases['Cost'];
                
                if( $discountPrice == "" || $discountPrice == 0 ) {
                    $inSiteIncome += $regularPrice;
                } else {
                    $inSiteIncome += $discountPrice;
                }
                $inSiteCount++;
            }
            
            $offSiteCount = $totalUnits - $inSiteCount;
            $offSiteIncome = $offSiteCount * $row['Price'];
            
            $totalIncome = getPriceDisplayWithDollars( $offSiteIncome + $inSiteIncome );
            $totalIncomeCard = getPriceDisplayWithDollars( $row['TotalIncome'] );
            
            $allIncome += $totalIncome;
            $allIncomeCard += $totalIncomeCard;
            $totalIncomeColor = "#e2ff42";
            
            if( $totalIncome - $totalIncomeCard != 0) {
                $totalIncomeColor = "#ff4242";
            }
            
            $totalExpenses = 0.0;
            $totalExpensesCount = 0;
            
            $statementRestock = $db->prepare("SELECT * from Restock p WHERE p.ItemID = :itemID" );
            $statementRestock->bindValue( ":itemID", $row['ID'] );
            $resultsRestock = $statementRestock->execute();

            while ($rowRestock = $resultsRestock->fetchArray()) {
                $totalExpenses += $rowRestock['Cost'];
                $totalExpensesCount++;
            }
            
            $totalExpensesColor = "#42c2ff";
            $totalExpensesCard = getPriceDisplayWithDollars( $row['TotalExpenses'] );
            
            $allExpenses += $totalExpenses;
            $allExpensesCard += $totalExpensesCard;
            
            if( $totalExpenses - $totalExpensesCard != 0) {
                $totalExpensesColor = "#ff4242";
            }
            
            echo "<tr class='$rowClass'>";
            echo "<td>" . $row['ID'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Name'] . "</td>";
            echo "<td>" . $totalUnits . "</td>";
            echo "<td>" . $inSiteCount . "</td>";
            echo "<td>" . $offSiteCount . "</td>";
            echo "<td>$" . $inSiteIncome . "</td>";
            echo "<td>$" . $offSiteIncome . "</td>";
            echo "<td style='background-color: $totalIncomeColor;'>$" . $totalIncome . "</td>";
            echo "<td style='background-color: $totalIncomeColor;'>$" . $totalIncomeCard . "</td>";
            echo "<td style='background-color: $totalExpensesColor;'>$" . $totalExpenses . "</td>";
            echo "<td style='background-color: $totalExpensesColor;'>$" . $totalExpensesCard . "</td>";
            echo "<td>&nbsp;</td>";
            echo "<td>&nbsp;</td>";
            echo "</tr>";
            if( $rowClass == "odd" ) { $rowClass = "even"; } else { $rowClass = "odd"; }
        }
        
        summary( $db, $previousType );
        
        echo "</table>";
    echo "</span>";

    /**
     * @param $db SQLite3
     * @param $previousType
     */
    function summary( $db, $previousType ) {
        global $allIncome, $allIncomeCard, $allExpenses, $allExpensesCard;
        $allProfit = $allIncome - $allExpenses;
        $allProfitCard = $allIncomeCard - $allExpensesCard;
        echo "<tr class='odd'>";
        echo "<td colspan='8'>TOTAL OVERALL</td>";
        echo "<td>$" . $allIncome . "</td>";
        echo "<td>$" . $allIncomeCard . "</td>";
        echo "<td>$" . $allExpenses . "</td>";
        echo "<td>$" . $allExpensesCard . "</td>";
        echo "<td>$" . $allProfit . "</td>";
        echo "<td>$" . $allProfitCard . "</td>";
        echo "</tr>";

        $allIncome = 0.0;
        $allIncomeCard = 0.0;
        $allExpenses = 0.0;
        $allExpensesCard = 0.0;

        $totalPayment = 0.0;
        $statementPayment = $db->prepare("SELECT sum(amount) as 'amount' FROM Payments p WHERE p.ItemType = :previousType" );
        $statementPayment->bindValue( ":previousType", $previousType );
        $resultsPayment = $statementPayment->execute();

        while ($rowPayment = $resultsPayment->fetchArray()) {
            $totalPayment = $rowPayment['amount'];
        }

        echo "<tr class='odd'>";
        echo "<td colspan='2'>&nbsp;</td>";
        echo "<td>Payments: $ $totalPayment</td>";
        echo "<td colspan='11'>&nbsp;</td>";
        echo "</tr>";
    }
?>

</body>