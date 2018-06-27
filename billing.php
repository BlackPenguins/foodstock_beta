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
	
	echo "<div id='restock_all'>";
	echo "<table style='font-size:12; border: 3px solid; border-collapse:collapse; margin:10px; width:98%'>";
	
	echo "<thead><tr style='background-color: #1f7943; border-top: 3px solid; border-right: 3px solid; border-left: 3px solid; border-bottom: none;'>";
	echo "<th style='padding:5px; border-right:1px #000 solid;' align='left'>Billing Month</th>";
	echo "<th style='padding:5px; border-right:1px #000 solid;' align='left'>Total Owed</th>";
	echo "<th style='padding:5px; border-right:1px #000 solid;' align='left'>Amount Unpaid</th>";
	echo "</tr></thead>";
	
	echo "<thead><tr style='background-color: #742a92; border-top: none; border-right: 3px solid; border-left: 3px solid; border-bottom: 3px solid;'>";
	echo "<th style='padding:5px; border-right:1px #000 solid; text-align:right;' align='left'>Payment Date</th>";
	echo "<th style='padding:5px; border-right:1px #000 solid; text-align:right;' align='left'>Amount</th>";
	echo "<th style='padding:5px; border-right:1px #000 solid; text-align:right;' align='left'>Method</th>";
	echo "</tr></thead>";
	
	$rowClass = "odd";
	
	$results = $db->query("SELECT Sum(Amount) as Total FROM Payments WHERE ItemType='$itemType' AND UserID = $userID");
	$totalPayments = $results->fetchArray()['Total'];
	$currentMonthLabel = "";
	$currentMonthTotal = 0;
	
	$currentMonth = 0;
	$currentYear = 0;
	
	$results = $db->query("SELECT i.Name, p.Cost, p.DiscountCost, p.Date, p.UserID FROM Purchase_History p JOIN Item i on p.itemID = i.ID WHERE p.UserID = $userID AND i.Type='$itemType' ORDER BY p.Date ASC");
	while ($row = $results->fetchArray()) {
		$date_object = DateTime::createFromFormat( 'Y-m-d H:i:s', $row['Date'] );
		$purchaseMonth = $date_object->format('F Y');
		
		if( $currentMonthLabel == "" ) {
			$currentMonthLabel = $purchaseMonth;
			$currentMonth = $date_object->format('m');
			$currentYear = $date_object->format('Y');
			
			// PRINT ANY PAYMENTS THIS MONTH OR BEFORE
			
		}

		if( $purchaseMonth != $currentMonthLabel ) {
			
			$currentMonth = $date_object->format('m');
			$currentYear = $date_object->format('Y');
			
			$totalPayments = printBillMonth( $db, $itemType, $userID, $currentMonthLabel, $currentMonthTotal, $currentMonth, $currentYear, $totalPayments, false );
			
			$currentMonthLabel = $purchaseMonth;
			
			$currentMonthTotal = 0;
		}
		
		if( $row['DiscountCost'] != "" ) {
			$currentMonthTotal += $row['DiscountCost'];
		} else {
			$currentMonthTotal += $row['Cost'];
		}
	}
	$totalPayments = printBillMonth( $db, $itemType, $userID, $currentMonthLabel, $currentMonthTotal, $currentMonth, $currentYear, $totalPayments, true );
	
		echo "</table>";
	echo "</div>";
	
	function printBillMonth( $db, $itemType, $userID, $currentMonthLabel, $currentMonthTotal, $currentMonth, $currentYear, $totalPayments, $restOfPayments ) {
		// We're in a new month, print our totals
		
		$paymentStartMonth = $currentYear . "-" . $currentMonth . "-01";
		$paymentsWhere = "";
		
		if( $restOfPayments ) {
			$paymentMonthStart = $currentMonth + 1;
			$paymentYearStart = $currentYear;
				
			if( $paymentMonthStart > 12 ) {
				$paymentMonthStart = 1;
				$paymentYearStart = $paymentYearEnd + 1;
			}
			
			if( $paymentMonthStart < 10 ) {
				$paymentMonthStart = 0 . $paymentMonthStart;
			}
			
			$paymentsWhere = " Date >= '$paymentYearStart-$paymentMonthStart-01'";
		} else {
			$paymentMonthEnd = $currentMonth + 1;
			$paymentYearEnd = $currentYear;
			
			if( $paymentMonthEnd > 12 ) {
				$paymentMonthEnd = 1;
				$paymentYearEnd = $paymentYearEnd + 1;
			}
			
			if( $paymentMonthEnd < 10 ) {
				$paymentMonthEnd = 0 . $paymentMonthEnd;
			}
			
			$paymentsWhere = " Date between '$paymentStartMonth' AND '$paymentYearEnd-$paymentMonthEnd-01'";
		}
		
		
		echo "<tr class='billing_row'>";
		echo "<td style='padding:5px; border:1px #000 solid;'>" . $currentMonthLabel . "</td>";
		echo "<td style='padding:5px; border:1px #000 solid;'>$" . number_format( $currentMonthTotal, 2 ) . "</td>";
		
		if( $currentMonthTotal > $totalPayments ) {
		$amountNotPaid = $currentMonthTotal - $totalPayments;
		echo "<td style='padding:5px; border:1px #000 solid;'>$" . number_format( $amountNotPaid, 2) . "</td>";
			$totalPayments = 0;
		} else {
		echo "<td style='padding:5px; border:1px #000 solid; font-weight: bold;'>ALL PAID</td>";
			$totalPayments = $totalPayments - $currentMonthTotal;
		}
		echo "</tr>";
		
		// But now let's print any payments made in this current month, that are paying off last month
		$paymentResults = $db->query("SELECT Date, Amount, Method FROM Payments WHERE $paymentsWhere AND ItemType='$itemType' AND UserID = $userID");
		while ($paymentRow = $paymentResults->fetchArray()) {
			$paymentDate = DateTime::createFromFormat( 'Y-m-d H:i:s', $paymentRow['Date'] );

			echo "<tr class='payment_row'>";
			echo "<td style='padding:5px; border:1px #000 solid; text-align:right;'>" . $paymentDate->format('F j, Y') . "</td>";
			echo "<td style='padding:5px; border:1px #000 solid; text-align:right;'>$" . number_format( $paymentRow['Amount'], 2 ) . "</td>";
			echo "<td style='padding:5px; border:1px #000 solid; text-align:right;'>" . $paymentRow['Method'] . "</td>";
			echo "</tr>";
		}
		
		return $totalPayments;
	}
?>

</body>