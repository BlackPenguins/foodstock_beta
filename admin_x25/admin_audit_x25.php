<?php
    include(__DIR__ . "/../appendix.php" );
    
    $url = ADMIN_AUDIT_LINK;
    include( HEADER_PATH );
    
    echo "<span style='width:86%; display:inline-block; border-left: 3px #000 solid;'>";
        // ------------------------------------
        // ITEM TABLE
        // ------------------------------------
        echo "<span class='soda_popout' style='display:inline-block; margin-left: 10px; width:100%; margin-top:15px; padding:5px;'><span style='font-size:26px;'>Item Inventory</span></span>";
        echo "<table style='font-size:12; border-collapse:collapse; width:100%; margin-bottom: 20px; margin-left: 10px;'>";
        echo "<thead><tr class='table_header'>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>ID</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Type</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Name</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Total Sold</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Total Sold Through Site</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Total Sold Through Non-Site</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Income (Purchase History)</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Income (Non-Site)</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Income (Total Income)</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Income (on Card)</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Expenses (Restock)</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Expenses (on Card)</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Profit (Restock)</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Profit (on Card)</th>";
        
        echo "</tr></thead>";
        
        $rowClass = "odd";
        $previousType = "";
        
        $allIncome = 0.0;
        $allIncomeCard = 0.0;
        
        $allExpenses = 0.0;
        $allExpensesCard = 0.0;
        $results = $db->query("SELECT ID, Type, Name, OutOfStock, Date, DateModified, ModifyType, ChartColor, TotalCans, BackstockQuantity, ShelfQuantity, Price, DiscountPrice, TotalIncome, TotalExpenses, Retired, Hidden, (ShelfQuantity + BackstockQuantity) as Total FROM Item where hidden != 1 ORDER BY Hidden, Type DESC, Name ASC");
        while ($row = $results->fetchArray()) {
            $type = $row['Type'];
            
            if( $previousType != "" && $previousType != $type ) {
                summary( $db, $previousType );
            }
            $previousType = $type;
            
            $totalUnits = ($row['TotalCans'] - ($row['BackstockQuantity'] + $row['ShelfQuantity']));
            
            $inSiteIncome = 0.0;
            $inSiteCount = 0;
            $resultsPurchases = $db->query("SELECT * from Purchase_History p where p.ItemID = " . $row['ID'] );
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
            
            $totalIncome = number_format( $offSiteIncome + $inSiteIncome, 2);
            $totalIncomeCard = number_format( $row['TotalIncome'], 2);
            
            $allIncome += $totalIncome;
            $allIncomeCard += $totalIncomeCard;
            $totalIncomeColor = "#e2ff42";
            
            if( $totalIncome - $totalIncomeCard != 0) {
                $totalIncomeColor = "#ff4242";
            }
            
            $totalExpenses = 0.0;
            $totalExpensesCount = 0;
            
            $resultsRestock = $db->query("SELECT * from Restock p where p.ItemID = " . $row['ID'] );
            while ($rowRestock = $resultsRestock->fetchArray()) {
                $totalExpenses += $rowRestock['Cost'];
                $totalExpensesCount++;
            }
            
            $totalExpensesColor = "#42c2ff";
            $totalExpensesCard = number_format( $row['TotalExpenses'], 2);
            
            $allExpenses += $totalExpenses;
            $allExpensesCard += $totalExpensesCard;
            
            if( round( $totalExpenses - $totalExpensesCard ) != 0) {
                $totalExpensesColor = "#ff4242";
            }
            
            echo "<tr class='$rowClass'>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $row['ID'] . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $row['Type'] . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $row['Name'] . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $totalUnits . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $inSiteCount . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $offSiteCount . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>$" . $inSiteIncome . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>$" . $offSiteIncome . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid; background-color: $totalIncomeColor;'>$" . $totalIncome . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid; background-color: $totalIncomeColor;'>$" . $totalIncomeCard . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid; background-color: $totalExpensesColor;'>$" . $totalExpenses . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid; background-color: $totalExpensesColor;'>$" . $totalExpensesCard . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>&nbsp;</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>&nbsp;</td>";
            echo "</tr>";
            if( $rowClass == "odd" ) { $rowClass = "even"; } else { $rowClass = "odd"; }
        }
        
        summary( $db, $previousType );
        
        echo "</table>";
    echo "</span>";
    
    function summary( $db, $previousType ) {
        global $allIncome, $allIncomeCard, $allExpenses, $allExpensesCard;
        $allProfit = $allIncome - $allExpenses;
        $allProfitCard = $allIncomeCard - $allExpensesCard;
        echo "<tr class='odd'>";
        echo "<td colspan='8' style='padding:5px; border:1px #000 solid;'>TOTAL OVERALL</td>";
        echo "<td style='padding:5px; border:1px #000 solid;'>$" . $allIncome . "</td>";
        echo "<td style='padding:5px; border:1px #000 solid;'>$" . $allIncomeCard . "</td>";
        echo "<td style='padding:5px; border:1px #000 solid;'>$" . $allExpenses . "</td>";
        echo "<td style='padding:5px; border:1px #000 solid;'>$" . $allExpensesCard . "</td>";
        echo "<td style='padding:5px; border:1px #000 solid;'>$" . $allProfit . "</td>";
        echo "<td style='padding:5px; border:1px #000 solid;'>$" . $allProfitCard . "</td>";
        echo "</tr>";

        $allIncome = 0.0;
        $allIncomeCard = 0.0;
        $allExpenses = 0.0;
        $allExpensesCard = 0.0;

        $totalPayment = 0.0;
        $resultsPayment = $db->query("SELECT sum(amount) as 'amount' from Payments p where p.ItemType = '$previousType'" );
        while ($rowPayment = $resultsPayment->fetchArray()) {
            $totalPayment = $rowPayment['amount'];
        }

        echo "<tr class='odd'>";
        echo "<td colspan='2' style='padding:5px; border:1px #000 solid;'>&nbsp;</td>";
        echo "<td style='padding:5px; border:1px #000 solid;'>Payments: $ $totalPayment</td>";
        echo "<td colspan='11' style='padding:5px; border:1px #000 solid;'>&nbsp;</td>";
        echo "</tr>";
    }
?>

</body>