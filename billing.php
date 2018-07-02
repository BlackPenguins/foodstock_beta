<head>
<meta name="viewport" content="width=device-width, initial-scale=1">

<?php
    $db = new SQLite3("db/item.db");
    if (!$db) die ($error);
        
    include("foodstock_functions.php");
    date_default_timezone_set('America/New_York');
        
    Login($db);
        
    $loggedIn = IsLoggedIn();
    $loggedInAdmin = IsAdminLoggedIn();
    $loginPassword = false;
    
    $itemType = "Soda";
    $url = "sodastock.php";
        
    require_once 'Mobile_Detect.php';
 
    $detect = new Mobile_Detect;
    $device_type = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
    $isMobile = $device_type == 'phone';

    if(isset($_GET['mobile'])) {
        $isMobile = true;
    }
        
    echo "<title>Billing - Foodstock</title>";
    echo "<link rel='icon' type='image/png' href='soda_can_icon.png' />";
?>

<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>
<script src="jscolor.js"></script>

<?php
    if( !$isMobile) {
        echo "<script src='load_modals.js'></script>";
    }
?>

<link rel="stylesheet" type="text/css" href="colorPicker.css"/>
<link rel="stylesheet" type="text/css" href="style.css"/>
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css">

</head>


<?php

    if( $isMobile ) {
        //Some magic that makes the top blue bar fill the width of the phone's screen
        echo "<body class='soda_body' style='display:inline-table;'>";
    } else {
        echo "<body class='soda_body'>";
    }
    
    include("login_bar.php");
    $itemType = $_GET['type'];
    
    if( !$loggedInAdmin ) {
        TrackVisit($db, "Billing-" .  $itemType, $loggedIn);
    }
    
    if( $loggedInAdmin && isset($_GET['userid'] ) && isset($_GET['name'] )  ) {
        $userID = $_GET['userid'];
        $name = $_GET['name'];
    } else {
        $userID = $_SESSION['userID'];
        $name = $_SESSION['firstname'];
    }
    
    // ------------------------------------
    // PURCHASE HISTORY TABLE
    // ------------------------------------
    echo "<div class='soda_popout'  style='margin:10px; padding:5px;'><span style='font-size:26px;'>Billing for '$name'</span></div>";
    
    displayPaymentMethods();
    
    echo "<div id='billing' style='margin-top:50px;'>";

    
    $currentMonthLabel = "";
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
    
    $results = $db->query("SELECT i.Name, i.Type, p.Cost, p.CashOnly, p.DiscountCost, p.Date, p.UserID FROM Purchase_History p JOIN Item i on p.itemID = i.ID WHERE p.UserID = $userID ORDER BY p.Date DESC");
    while ($row = $results->fetchArray()) {
        $purchaseDateObject = DateTime::createFromFormat( 'Y-m-d H:i:s', $row['Date'] );
        $purchaseMonthLabel = $purchaseDateObject->format('F Y');
        
        // First month
        if( $currentMonthLabel == "" ) {
            $currentMonthLabel = $purchaseMonthLabel;
            $currentMonth = $purchaseDateObject->format('m');
            $currentYear = $purchaseDateObject->format('Y');
        }

        // New Month
        if( $purchaseMonthLabel != $currentMonthLabel ) {

            // Print the last month
            printNewBillMonth( $db, $itemType, $userID, $currentMonthLabel,
                $currentMonthSodaTotal, $currentMonthSnackTotal, $currentMonthSodaCashOnlyTotal, $currentMonthSnackCashOnlyTotal,
                $currentMonthSodaCount, $currentMonthSnackCount, $currentMonthSodaCashOnlyCount, $currentMonthSnackCashOnlyCount );
            
            $currentMonthLabel = $purchaseMonthLabel;
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
        if( $row['CashOnly'] != 1 ) {
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
    printNewBillMonth( $db, $itemType, $userID, $currentMonthLabel,
                $currentMonthSodaTotal, $currentMonthSnackTotal, $currentMonthSodaCashOnlyTotal, $currentMonthSnackCashOnlyTotal,
                $currentMonthSodaCount, $currentMonthSnackCount, $currentMonthSodaCashOnlyCount, $currentMonthSnackCashOnlyCount );
    
    echo "</div>";
    
    function printNewBillMonth( $db, $itemType, $userID, $currentMonthLabel,
                $currentMonthSodaTotal, $currentMonthSnackTotal, $currentMonthSodaCashOnlyTotal, $currentMonthSnackCashOnlyTotal,
                $currentMonthSodaCount, $currentMonthSnackCount, $currentMonthSodaCashOnlyCount, $currentMonthSnackCashOnlyCount ) {
        
        echo "<table style='width: 95%; border: #000 solid 3px; margin: 10px 20px; border-collapse: collapse;'>";
        echo "<tr style='font-weight:bold; background-color: #98941a; border-bottom: 2px dashed #000;'>";
        echo "<td colspan='2' style='padding:5px;'>";
        echo $currentMonthLabel;
        echo "</td>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td style='padding:5px; background-color: #1f7943; font-weight:bold;'>Purchases</td>";
        echo "<td style='padding:5px; background-color: #742a92; font-weight:bold;'>Payments</td>";
        echo "</tr>";
        
        echo "<tr>";
        
        echo "<td style='background-color: #39ad67; width:50%; vertical-align: top;'>";
        
        if( $currentMonthSodaCount > 0 ) {
            echo "<div style='padding:10px;'><b>Site Purchased Soda ($currentMonthSodaCount items):</b> $" . number_format($currentMonthSodaTotal, 2) . "</div>";
        }
        
        if( $currentMonthSnackCount > 0 ) {
            echo "<div style='padding:10px;'><b>Site Purchased Snacks ($currentMonthSnackCount items):</b> $" . number_format($currentMonthSnackTotal, 2) . "</div>";
        }
        
        if( $currentMonthSodaCashOnlyCount > 0 ) {
            echo "<div style='padding:10px;'><b>Cash-Only Soda ($currentMonthSodaCashOnlyCount items):</b> $" . number_format($currentMonthSodaCashOnlyTotal, 2) . " (already paid)</div>";
        }
        
        if( $currentMonthSnackCashOnlyCount > 0 ) {
            echo "<div style='padding:10px;'><b>Cash-Only Snacks ($currentMonthSnackCashOnlyCount items):</b> $" . number_format($currentMonthSnackCashOnlyTotal, 2) . " (already paid)</div>";
        }
        
        $totalPurchased = $currentMonthSodaTotal + $currentMonthSnackTotal;
        
        echo "<div style='margin-top:20px; padding:5px; font-size:1.1em;'><b>Total Balance:</b> $" . number_format($totalPurchased, 2) . "</div>";
        
        echo "</td>";
        
        echo "<td style='background-color: #a359c1; width:50%; vertical-align: top;'>";
        
        $results = $db->query("SELECT Amount, Date, ItemType FROM Payments WHERE UserID = $userID AND MonthForPayment = '$currentMonthLabel'");
        
        $totalPaid = 0.0;
        
        while ($row = $results->fetchArray()) {
            $paymentAmount = $row['Amount'];
            $paymentDate = DateTime::createFromFormat( 'Y-m-d H:i:s', $row['Date'] );
            
            echo "<div style='padding:10px;'><b>" . $paymentDate->format('F j, Y') . ": (" . $row['ItemType'] . ")</b> $" . number_format($paymentAmount, 2) . "</div>";
            $totalPaid += $paymentAmount;
        }
        
        echo "<div style='margin-top:20px; padding: 5px; font-size:1.1em;'><b>Total Paid:</b> $" . number_format($totalPaid, 2) . "</div>";
        
        $totalOwed = $totalPurchased - $totalPaid;
        
        echo "</td>";
         
        echo "</tr>";
        
        echo "<tr>";
         
        echo "<td style='background-color: #39ad67;'>&nbsp;</td>";
        
        $owedColor = "#1f7943";
        
        if( $totalOwed > 0 ) {
            $owedColor = "#791f1f";
        }
        echo "<td style='padding:10px; background-color: #a359c1; font-size:1.1em; text-align:right; vertical-align:bottom;'>";
        echo "<span style='padding: 5px; border:2px dashed #000; background-color: $owedColor; color: #FFFFFF;'><b>Total Owed:</b> $" . number_format($totalOwed, 2) . "</span>";
        echo "</td>";
         
        echo "</tr>";
         
        echo "</table>";
    }
?>

</body>