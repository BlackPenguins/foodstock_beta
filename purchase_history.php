<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<script>
    function showHideMonths() {
        $(".not_this_payment_month").toggle();

        if( $("#showHideMonths").html() == "Show all Months" ) {
            $("#showHideMonths").html( "Hide all Non-Owed Payment Months" );
        } else {
            $("#showHideMonths").html( "Hide all Non-Owed Payment Months" );
        }
    }

</script>
<?php
    $trackingName =  "Purchase History - Soda and Snack";
    include( "appendix.php" );
    $url = PURCHASE_HISTORY_LINK;
    include( HEADER_PATH );
    
   
    if( $isLoggedInAdmin && isset($_GET['userid'] ) && isset($_GET['name'] )  ) {
        $userID = $_GET['userid'];
        $name = $_GET['name'];
    } else {
        $userID = $_SESSION['UserID'];
        $name = $_SESSION['FirstName'] . " " . $_SESSION['LastName'];
    }

    $month = date('m_Y' );

    if( isset( $_GET['month'] ) ) {
        $month = $_GET['month'];
    }

    $monthPieces = explode("_", $month );

    if( count( $monthPieces ) != 2 ) {
        echo "You are trying to do some weird stuff with this month you selected: <b>$month</b>";
        die();
    }

    $monthNumber = $monthPieces[0];
    $year = $monthPieces[1];

    $monthHeader = date('F Y', mktime(0, 0, 0, $monthNumber, 1, $year));

    // ------------------------------------
    // PURCHASE HISTORY TABLE
    // ------------------------------------
    echo "<div id= 'container'>";

    echo "<div class='rounded_header'><span class='title'>Purchase/Payment History for <b>$name</b></span>";
    $totalSodaSavings = 0.0;
    $totalSodaBalance = 0.0;
    $totalSnackSavings = 0.0;
    $totalSnackBalance = 0.0;

    $results = $db->query("SELECT p.Cost, i.Type, p.DiscountCost FROM Purchase_History p JOIN Item i on p.itemID = i.ID WHERE p.UserID = $userID AND Cancelled IS NULL");
    while ($row = $results->fetchArray()) {
        $itemType = $row['Type'];

        if( $row['DiscountCost'] != "" ) {
            if( $itemType == "Soda" ) {
                $totalSodaSavings += ($row['Cost'] - $row['DiscountCost']);
                $totalSodaBalance += $row['DiscountCost'];
            } else if( $itemType == "Snack" ) {
                $totalSnackSavings += ($row['Cost'] - $row['DiscountCost']);
                $totalSnackBalance += $row['DiscountCost'];
            }
        } else {
            if( $itemType == "Soda" ) {
                $totalSodaBalance += $row['Cost'];
            } else if( $itemType == "Snack" ) {
                $totalSnackBalance += $row['Cost'];
            }
        }
    }
    
    echo  "<span id='total_details_box'><b>Total Soda Spent:</b> ". getPriceDisplayWithDollars( $totalSodaBalance ) . "&nbsp;&nbsp;|&nbsp;&nbsp;<b>Total Soda Savings:</b> " . getPriceDisplayWithDollars( $totalSodaSavings ) . "</span>";

    echo "<div>";
    echo "<span style='color:#03af10'>The <b>Billing</b> section has been removed and condensed into this page.</span>";
    echo  "<span id='total_details_box'><b>Total Snack Spent:</b> ". getPriceDisplayWithDollars( $totalSnackBalance ) . "&nbsp;&nbsp;|&nbsp;&nbsp;<b>Total Snack Savings:</b> " . getPriceDisplayWithDollars( $totalSnackSavings ) . "</span>";
    echo "</div>";
    
    echo "</div>";

echo "<div class='center_piece'>";

displayPaymentMethods();

echo "<div class='rounded_table_no_border'>";
echo "<table>";

echo "<thead>";

echo "<tr>";
echo "<td style='text-align:center; background-color: #255420; color:#d8e41d; font-weight:bold; text-transform: uppercase;' colspan='4'>PAYMENT HISTORY</td>";
echo "</tr>";

echo "<tr class='table_header'>";
echo "<th>Month</th>";
echo "<th>Balance</th>";
echo "<th>Payments</th>";
echo "<th>Money Owed</th>";

echo "</tr>";

echo "</thead>";

$currentMonthLabel = "";
$currentMonthLink = "";
$currentMonthSodaTotal = 0.0;
$currentMonthSnackTotal = 0.0;
$currentMonthSodaCashOnlyTotal = 0.0;
$currentMonthSnackCashOnlyTotal = 0.0;

$currentMonthSodaCount = 0;
$currentMonthSnackCount = 0;
$currentMonthSodaCashOnlyCount = 0;
$currentMonthSnackCashOnlyCount = 0;

$currentMonth = 0;
$currentYear = 0;

$results = $db->query("SELECT i.Name, i.Type, p.Cost, p.CashOnly, p.DiscountCost, p.Date, p.UserID, p.UseCredits FROM Purchase_History p JOIN Item i on p.itemID = i.ID WHERE p.Cancelled IS NULL AND p.UserID = $userID ORDER BY p.Date DESC");
while ($row = $results->fetchArray()) {
    $purchaseDateObject = DateTime::createFromFormat( 'Y-m-d H:i:s', $row['Date'] );
    $purchaseMonthLabel = $purchaseDateObject->format('F Y');
    $purchaseMonthLink = $purchaseDateObject->format('m_Y');

    // First month
    if( $currentMonthLabel == "" ) {
        $currentMonthLabel = $purchaseMonthLabel;
        $currentMonthLink= $purchaseMonthLink;
        $currentMonth = $purchaseDateObject->format('m');
        $currentYear = $purchaseDateObject->format('Y');
    }

    // New Month
    if( $purchaseMonthLabel != $currentMonthLabel ) {

        // Print the last month
        printNewBillMonth( $db, $month, $currentMonthLink, $userID, $currentMonthLabel,
            $currentMonthSodaTotal, $currentMonthSnackTotal, $currentMonthSodaCashOnlyTotal, $currentMonthSnackCashOnlyTotal,
            $currentMonthSodaCount, $currentMonthSnackCount, $currentMonthSodaCashOnlyCount, $currentMonthSnackCashOnlyCount );

        $currentMonthLabel = $purchaseMonthLabel;
        $currentMonthLink = $purchaseMonthLink;
        $currentMonth = $purchaseDateObject->format('m');
        $currentYear = $purchaseDateObject->format('Y');

        $currentMonthSodaTotal = 0.0;
        $currentMonthSnackTotal = 0.0;
        $currentMonthSodaCashOnlyTotal = 0.0;
        $currentMonthSnackCashOnlyTotal = 0.0;

        $currentMonthSodaCount = 0;
        $currentMonthSnackCount = 0;
        $currentMonthSodaCashOnlyCount = 0;
        $currentMonthSnackCashOnlyCount = 0;
    }

    $cost = 0.0;
    if( $row['DiscountCost'] != "" && $row['DiscountCost'] != 0 ) {
        $cost = $row['DiscountCost'];
    } else {
        $cost = $row['Cost'];
    }

    // Only purchases that WERE NOT cash-only go towards the total - because they already paid in cash
    if( $row['CashOnly'] != 1 && $row['UseCredits'] == 0 ) {
        if( $row['Type'] == "Snack" ) {
            $currentMonthSnackTotal += $cost;
            $currentMonthSnackCount++;
        } else if( $row['Type'] == "Soda" ) {
            $currentMonthSodaTotal += $cost;
            $currentMonthSodaCount++;
        }
    } else {
        if( $row['Type'] == "Snack" ) {
            $currentMonthSnackCashOnlyTotal += $cost;
            $currentMonthSnackCashOnlyCount++;
        } else if( $row['Type'] == "Soda" ) {
            $currentMonthSodaCashOnlyTotal += $cost;
            $currentMonthSodaCashOnlyCount++;
        }
    }
}

// Print the last month (usually the current one)
printNewBillMonth( $db, $month, $currentMonthLink, $userID, $currentMonthLabel,
    $currentMonthSodaTotal, $currentMonthSnackTotal, $currentMonthSodaCashOnlyTotal, $currentMonthSnackCashOnlyTotal,
    $currentMonthSodaCount, $currentMonthSnackCount, $currentMonthSodaCashOnlyCount, $currentMonthSnackCashOnlyCount );

echo "<tr>";
echo "<td id='showHideMonths' colspan='4' style='text-align: center; cursor:pointer;' onclick='showHideMonths();' >Show all Months</td>";
echo "</tr>";

echo "</table>";
echo "</div>";


    echo "<div style='margin-top:50px;' class='rounded_table_no_border'>";
    echo "<table>";

    echo "<thead>";

    echo "<tr>";
    echo "<td style='text-align:center; background-color: #255420; color:#d8e41d; font-weight:bold; text-transform: uppercase;' colspan='3'>PURCHASE HISTORY - $monthHeader</td>";
    echo "</tr>";

    echo "<tr class='table_header'>";
    echo "<th width='20%'>Date Purchased</th>";
    echo "<th>Item</th>";
    echo "<th>Cost</th>";
    echo "</tr>";

    echo "</thead>";
    
    $rowClass = "odd";

    $startDate = $year . "-" . $monthNumber . "-01";

    if( $monthNumber == 12) {
        $monthNumber = 1;
        $year++;
    } else {
        $monthNumber++;
    }

    if( $monthNumber < 10 ) { $monthNumber = "0" . $monthNumber; }

    $endDate = $year . "-" . $monthNumber . "-01";

    // LEFT JOIN Item because Credits are ID 4000, and there is no corresponding Item in the Item table with that ID
    $results = $db->query("SELECT p.itemID, i.Name, i.Type, p.Cancelled, p.Cost, p.DiscountCost, p.Date, p.UserID, p.CashOnly, p.UseCredits FROM Purchase_History p LEFT JOIN Item i on p.itemID = i.ID WHERE p.UserID = $userID AND p.Date >= '$startDate' AND p.Date < '$endDate' ORDER BY p.ID DESC");
    $currentWeek = "";
    while ($row = $results->fetchArray()) {
        $date_object = DateTime::createFromFormat('Y-m-d H:i:s', $row['Date']);
        
        $weekOfPurchase = $date_object->format('\W\e\e\k W - \Y\e\a\r Y');
        
        if( $currentWeek == "" ) {
            $currentWeek = $weekOfPurchase;
        } else {
            if( $currentWeek != $weekOfPurchase ) {
                // New week
                echo "<tr>";
                echo "<td class='section' colspan='3'>";
                echo $weekOfPurchase;
                echo "</td>";
                echo "</tr>";
            
                $currentWeek = $weekOfPurchase;
            }
        }
        $isCancelled = $row['Cancelled'] === 1;
        $itemName = $row['Name'];
        $discountAmountDisplay = getPriceDisplayWithDollars( $row['DiscountCost'] );
        $costAmountDisplay = getPriceDisplayWithDollars( $row['Cost'] );
        
        if( $isCancelled ) {
            $rowClass = "discontinued_row";
            $discountAmountDisplay .= " (REFUNDED)";
            $costAmountDisplay .= " (REFUNDED)";
        }

        $costDisplay = "";

        if( $row['DiscountCost'] != "" ) {
            $costDisplay = "<span class='red_price'>" . getPriceDisplayWithDollars( $row['Cost'] ) . "</span>" . $discountAmountDisplay;
        } else {
            $costDisplay = $costAmountDisplay;
        }

        if( $row['CashOnly'] == 1 ) {
            $costDisplay = $costDisplay . "<span style='float:right; font-weight: bold; color:#023e0c;'>(CASH - ONLY)</span>";
        }

        if( $row['UseCredits'] > 0 ) {
            $partialCreditsLabel = "";

            if( ( $row['DiscountCost'] != 0 && $row['UseCredits'] < $row['DiscountCost'] )
                || ( $row['DiscountCost'] == 0 &&  $row['Cost'] != 0 &&  $row['UseCredits'] < $row['Cost'] ) ) {
                $partialCreditsLabel = " PARTIAL";
            }



            $costDisplay = $costDisplay . "<span style='float:right; font-weight: bold; color:#716e08;'>(WITH " . getPriceDisplayWithDollars( $row['UseCredits'] ) . "$partialCreditsLabel CREDITS)</span>";
        }

        $rowClass = "";
        $itemType = $row['Type'];

        if( $itemType == "Soda" ) {
            $rowClass = "class='soda_row'";
        } else if( $itemType == "Snack" ) {
            $rowClass = "class='snack_row'";
        } else {
            $itemID = $row['ItemID'];

            if( $itemID == CREDIT_ID ) {
                if( $row['Cost'] > 0 ) {
                    $rowClass = "class='add_credit_row'";
                    $itemName = "Added Credits&nbsp;&nbsp;($costDisplay)";
                } else {
                    $rowClass = "class='remove_credit_row'";
                    $itemName = "Returned Credits&nbsp;&nbsp;($costDisplay)";
                }

                $costDisplay = "---";
            }
        }

        echo "<tr $rowClass>";
        echo "<td>" . $date_object->format('l m/d/Y  [h:i A]') . "</td>";
        echo "<td>" . $itemName . "</td>";
        echo "<td>" . $costDisplay ."</td>";
        echo "</tr>";
        
        if( $rowClass == "odd" ) { $rowClass = "even"; } else { $rowClass = "odd"; }
    }
    
        echo "</table>";
    echo "</div>";
    echo "</div>";
    echo "</div>";


function printNewBillMonth( $db, $selectedMonthLink, $currentMonthLink, $userID, $currentMonthLabel,
                            $currentMonthSodaTotal, $currentMonthSnackTotal, $currentMonthSodaCashOnlyTotal, $currentMonthSnackCashOnlyTotal,
                            $currentMonthSodaCount, $currentMonthSnackCount, $currentMonthSodaCashOnlyCount, $currentMonthSnackCashOnlyCount ) {

    $totalPurchased = $currentMonthSodaTotal + $currentMonthSnackTotal;

    $results = $db->query("SELECT Amount, Date, ItemType, Method FROM Payments WHERE UserID = $userID AND MonthForPayment = '$currentMonthLabel'  AND Cancelled is NULL ORDER BY Date DESC, ItemType ASC ");

    $totalPaid = 0.0;
    $paymentDetails = "";
    $currentDate = "";
    $currentDateForTotal = "";
    $currentPaymentMethod = "";

    while ($row = $results->fetchArray()) {
        $paymentAmount = $row['Amount'];
        $paymentMethod = $row['Method'];

        if( $paymentMethod != "None" && $paymentMethod != "" ) {
            $paymentMethod = "&nbsp;&nbsp; [$paymentMethod]";
        } else {
            $paymentMethod = "";
        }



        $paymentDate = DateTime::createFromFormat( 'Y-m-d H:i:s', $row['Date'] )->format('F j, Y');

        if( $currentDate == "" ) {
            $currentDate = $paymentDate;
            $currentPaymentMethod = $paymentMethod;
            $currentDateForTotal = 0;
        } else if( $currentDate != $paymentDate ) {
            $paymentDetails .= "<div style='font-size:0.8em; font-weight:normal; padding: 5px 0px;'><b>" . $currentDate . ":</b> " . getPriceDisplayWithDollars( $currentDateForTotal ) . "$currentPaymentMethod</div>";
            $currentDate = $paymentDate;
            $currentPaymentMethod = $paymentMethod;
            $currentDateForTotal = 0;
        }

        $currentDateForTotal += $paymentAmount;

        if( $paymentMethod != "None" && $paymentMethod != "" ) {
            $currentPaymentMethod = $paymentMethod;
        }


        $totalPaid += $paymentAmount;
    }

    if( $totalPaid != 0 ) {
        $paymentDetails .= "<div style='font-size:0.8em; font-weight:normal; padding: 5px 0px;'><b>" . $currentDate . ":</b> " . getPriceDisplayWithDollars( $currentDateForTotal ) . "$currentPaymentMethod</div>";
    }


    if( $paymentDetails == "" ) {
        $paymentDetails = "No Payments";
    }

    $totalOwed = $totalPurchased - $totalPaid;

    $hideRowClass = "";
    $hideRowStyle = "";
    $selectedCellStyle = "";

    if( $currentMonthLink != $selectedMonthLink && $totalOwed == 0 ) {
        $hideRowClass = "not_this_payment_month";
        $hideRowStyle ="style='display:none;'";
    }

    if( $currentMonthLink == $selectedMonthLink) {
        $selectedCellStyle ="style='color:#000000; font-weight:bold; font-size: 1.1em; vertical-align:top;'";
    }

    $owedColor = "paid_row";
    $owedLabel = "<b>ALL PAID</b>";

    if( $totalOwed > 0 ) {
        $owedColor = "owed_row";
        $owedLabel = getPriceDisplayWithDollars( $totalOwed );
    }

    echo "<tr  class='$owedColor $hideRowClass' $hideRowStyle>";
    $adminParameters = "";

    if( isset( $_GET['name'] ) ) {
        $adminParameters .= "&name=" . $_GET['name'];
    }

    if( isset( $_GET['userid'] ) ) {
        $adminParameters .= "&userid=" . $_GET['userid'];
    }

    echo "<td $selectedCellStyle><a href = 'purchase_history.php?month=$currentMonthLink$adminParameters'>$currentMonthLabel</a></td>";

    echo "<td $selectedCellStyle>";
    echo getPriceDisplayWithDollars( $totalPurchased );

    if( $currentMonthLink == $selectedMonthLink) {
        echo "<hr>";
        echo "<div style='font-size:0.8em; font-weight:normal; padding: 5px 0px;'><b>$currentMonthSodaCount Sodas:</b> " . getPriceDisplayWithDollars( $currentMonthSodaTotal ) . "</div>";
        echo "<div style='font-size:0.8em; font-weight:normal; padding: 5px 0px;'><b>$currentMonthSnackCount Snacks:</b> " . getPriceDisplayWithDollars( $currentMonthSnackTotal ) . "</div>";

        if( $currentMonthSodaCashOnlyCount > 0 ) {
            echo "<div style='font-size:0.8em; font-weight:normal; padding: 5px 0px;'><b>Cash-Only/Credited Soda ($currentMonthSodaCashOnlyCount items):</b> " . getPriceDisplayWithDollars( $currentMonthSodaCashOnlyTotal ) . " (already paid)</div>";
        }

        if( $currentMonthSnackCashOnlyCount > 0 ) {
            echo "<div style='font-size:0.8em; font-weight:normal; padding: 5px 0px;'><b>Cash-Only/Credited Snacks ($currentMonthSnackCashOnlyCount items):</b> " . getPriceDisplayWithDollars( $currentMonthSnackCashOnlyTotal ) . " (already paid)</div>";
        }
    }

    echo "</td>";


    echo "<td $selectedCellStyle>";
    echo getPriceDisplayWithDollars( $totalPaid );

    if( $currentMonthLink == $selectedMonthLink) {
        echo "<hr>";
        echo $paymentDetails;
    }

    echo "</td>";

    echo "<td $selectedCellStyle>" .  $owedLabel . "</td>";
    echo "</tr>";
}
?>

</body>