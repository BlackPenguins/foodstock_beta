<head>
<meta name="viewport" content="width=device-width, initial-scale=1">

<?php
    $db = new SQLite3("db/item.db");
    if (!$db) die ($error);
        
    include("foodstock_functions.php");
    date_default_timezone_set('America/New_York');
        
    Login($db);

    $isLoggedIn = IsLoggedIn();
    $isLoggedInAdmin = IsAdminLoggedIn();
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
        
    echo "<title>Admin - Foodstock</title>";
    echo "<link rel='icon' type='image/png' href='soda_can_icon.png' />";
?>

<script
	src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>
<script src="js/jscolor.js"></script>

<?php
    if( !$isMobile) {
        echo "<script src='js/load_modals.js'></script>";
    }
?>

<link rel="stylesheet" type="text/css" href="colorPicker.css" />
<link rel="stylesheet" type="text/css" href="css/style.css" />
<link rel="stylesheet"
	href="//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css">

<script type="text/javascript">
    $( document ).ready( function() {
                
        <?php 
            if(!$isMobile && $isLoggedInAdmin) {
                echo "loadSingleModals();\n";
                echo "loadItemModals('Soda');\n";
                echo "loadItemModals('Snack');\n";
            }
        ?>           
    });

    function openPaymentModal( user, month, method, sodaAmount, snackAmount ) {
        $('#payment').dialog('open');
        $('#UserDropdown').val(user);
        $('#MonthDropdown').val(month);
        $('#SodaAmount').val(sodaAmount);
        $('#SnackAmount').val(snackAmount);
    }

    function notifyUsersOfPayments( month ) {
        $isAlert = confirm('Are you sure that you want to notify all users about their balances?');
        
        if ( $isAlert ) {
            alert("Notified all users.");

            $.post("sodastock_ajax.php", { 
                type:'NotifyUserOfPayment',
                month:month,
            },function(data) {
                // Do nothing right now
            });
        }
    }

    function cancelPayment(paymentID, name, month) {
        $isAlert = confirm('Are you sure that you want cancel payment for ' + name + ' - ' + month + '?');
        
        if ( $isAlert ) {
            alert("Payment cancelled.");

            $.post("sodastock_ajax.php", { 
                type:'CancelPayment',
                PaymentID:paymentID,
            },function(data) {
                // Do nothing right now
            });
        }
    }
    
</script>
</head>

<?php

    if( $isMobile ) {
        //Some magic that makes the top blue bar fill the width of the phone's screen
        echo "<body class='soda_body' style='display:inline-table;'>";
    } else {
        echo "<body class='soda_body'>";
    }
    
    include("build_admin_forms.php");
    include("login_bar.php");
    
    TrackVisit($db, 'Admin');
    
    if( !$isLoggedInAdmin ) {
        // Only admin is allowed on this page
        die;
    }

    DisplayUserMessage();
    
    echo "<span style='width:11%; vertical-align:top; display:inline-block; padding: 10px; background-color:#4d544e; border: 0px solid #000;'>";
    
    if( $isLoggedInAdmin ) {
        include "admin_nav_x25.php";
    }
    
    echo "</span>";
    
    echo "<span style='width:86%; display:inline-block; border-left: 3px #000 solid;'>";
        // ------------------------------------
        // USER TABLE
        // ------------------------------------
        echo "<span class='soda_popout' style='display:inline-block; width:100%; margin-left: 10px; padding:5px;'><span style='font-size:26px;'>User Payment Histories</span> <span style='font-size:0.8em;'></span></span>";
        echo "<button onClick='notifyUsersOfPayments()' style='margin: 10px 10px; padding: 5px;'>Notify All Users of Payments</button>";
        echo "<span id='users'>";
        echo "<table style='font-size:12; border-collapse:collapse; width:100%; margin-left: 10px;'>";
        echo "<thead><tr class='table_header'>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Name</th>";
        
        $monthsMaxAgo = 5;
        for( $monthsAgo = 0; $monthsAgo <= $monthsMaxAgo; $monthsAgo++ ) {
            $today = date('F Y');
            $newdate = date('F Y', strtotime('-' . $monthsAgo . ' months', strtotime($today)));
            echo "<th style='padding:5px; border:1px #000 solid;' align='left'>$newdate</th>";
        }
        echo "</tr></thead>";
        
        $rowClass = "odd";
        
        $results = $db->query('SELECT u.UserID, u.UserName, u.SlackID, u.FirstName, u.LastName, u.PhoneNumber, u.SodaBalance, u.SnackBalance, u.DateCreated, u.InActive FROM User u ORDER BY u.Inactive asc, lower(u.FirstName) ASC');
        while ($row = $results->fetchArray()) {
            if( $row['Inactive'] == 1 ) {
                $rowClass = "discontinued_row";
            }
            
            echo "<tr class='$rowClass'>";
            $fullName = $row['FirstName'] . " " . $row['LastName'];
            $userID = $row['UserID'];
            
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $fullName . "</td>";
           
            for( $monthsAgo = 0; $monthsAgo <= $monthsMaxAgo; $monthsAgo++ ) {
                $today = date('F Y');
                $month = date('m', strtotime('-' . $monthsAgo . ' months', strtotime($today)));
                $year = date('Y', strtotime('-' . $monthsAgo . ' months', strtotime($today)));
                $monthLabel = date('F Y', strtotime('-' . $monthsAgo . ' months', strtotime($today)));
                echo calculateMonth( $db, $userID, $month, $year, $monthLabel );
            }
            
            echo "</tr>";
            if( $rowClass == "odd" ) { $rowClass = "even"; } else { $rowClass = "odd"; }
        }
        
            echo "</table>";
        echo "</span>";
        
        // ------------------------------------
        // PAYMENT TABLE
        // ------------------------------------
        echo "<span class='soda_popout' style='display:inline-block; margin-left: 10px; width:100%; margin-top:15px; padding:5px;'><span style='font-size:26px;'>Payments</span></span>";
        echo "<table style='font-size:12; border-collapse:collapse; width:100%; margin-left: 10px;'>";
        echo "<thead><tr class='table_header'>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>&nbsp;</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>User Name</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Payment Month</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Amount</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Method</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Type</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Date</th>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Note</th>";
        
        echo "</tr></thead>";
        
        $rowClass = "odd";
        $previousDate = "";
        
        $results = $db->query("SELECT p.PaymentID, u.FirstName, u.LastName, p.Cancelled, p.Method, p.Amount, p.Date, p.Note, p.ItemType, p.MonthForPayment FROM Payments p LEFT JOIN User u on p.UserID = u.UserID WHERE p.Date >= date('now','-12 months') ORDER BY p.Date DESC");
        while ($row = $results->fetchArray()) {
        
            if( $previousDate != "" && $previousDate != $row['Date'] ) {
                if( $rowClass == "odd" ) { $rowClass = "even"; } else { $rowClass = "odd"; }
            }
        
            $name = $row['FirstName'] . " " . $row['LastName'];
        
            $paymentID = $row['PaymentID'];
            $method = $row['Method'];
            $amount = $row['Amount'];
            $date = $row['Date'];
            $note = $row['Note'];
            $cancelled = $row['Cancelled'];
            $itemType = $row['ItemType'];
            $paymentMonth = $row['MonthForPayment'];
            $date_object = DateTime::createFromFormat('Y-m-d H:i:s', $row['Date']);

            echo "<tr class='$rowClass'>";
            if( $cancelled !=  1 ) {
                echo "<td style='padding:5px; border:1px #000 solid;'><span onclick='cancelPayment($paymentID, \"$name\", \"$paymentMonth\");' class='nav_buttons nav_buttons_snack'>Cancel Payment</span></td>";
            } else {
                echo "<td style='padding:5px; border:1px #000 solid;'>Cancelled</td>";
            }
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $name . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $paymentMonth . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>$" . number_format( $amount, 2) . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $method . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $itemType . "</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>".$date_object->format('m/d/Y  [h:i:s A]')."</td>";
            echo "<td style='padding:5px; border:1px #000 solid;'>" . $note . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    echo "</span>";
    
    function calculateMonth( $db, $userID, $monthNumber, $year, $monthLabel ) {
        $currentMonthSodaTotal = 0.0;
        $currentMonthSnackTotal = 0.0;
        
        $currentMonthSodaCount = 0;
        $currentMonthSnackCount = 0;
        
        $startDate = $year . "-" . $monthNumber . "-01";
        
        if( $monthNumber == 12) {
            $monthNumber = 1;
            $year++;
        } else {
            $monthNumber++;
        }
        
        if( $monthNumber < 10 ) { $monthNumber = "0" . $monthNumber; } 
        
        $endDate = $year . "-" . $monthNumber . "-01";
        
        $query = "SELECT i.Name, i.Type, p.Cost, p.CashOnly, p.DiscountCost, p.Date, p.UserID FROM Purchase_History p JOIN Item i on p.itemID = i.ID WHERE p.UserID = $userID AND p.Date >= '$startDate' AND p.Date < '$endDate' AND p.Cancelled IS NULL ORDER BY p.Date DESC";
        $results = $db->query( $query );
        while ($row = $results->fetchArray()) {
            
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
            }
        }

        $results = $db->query("SELECT Sum(Amount) as 'TotalAmount' FROM Payments WHERE UserID = $userID AND MonthForPayment = '$monthLabel' AND Cancelled IS NULL");
        $totalPaid = $results->fetchArray()['TotalAmount'];
        $totalPurchases = $currentMonthSodaTotal + $currentMonthSnackTotal;
        $totalUnpaid = round( $totalPurchases - $totalPaid, 2);
        $totalBalanceColor = "";
        
        if( $totalUnpaid != 0.0 ) {
            $totalBalanceColor = "background-color:#fdff7a;";
        }
        
        
        $sodaAmount = number_format( $currentMonthSodaTotal, 2 );
        $snackAmount = number_format( $currentMonthSnackTotal, 2 );
        
        $onclick = "openPaymentModal(\"$userID\", \"$monthLabel\", \"None\", \"$sodaAmount\", \"$snackAmount\");";
        
        return "<td style='padding:5px; $totalBalanceColor border:1px #000 solid;'>"
                . "<div>"
                . "<span>Soda: $" . $sodaAmount . "</span>"
                . "<span style='float:right;'>Snack: $" . $snackAmount ."</span>" 
                . "</div>"
                . "<div style='padding:5px; text-align:center;'>"
                . "<span onclick='$onclick' style='cursor:pointer; padding:5px; border: 1px dashed #000;'>"
                . "Total: $". number_format( $totalPurchases, 2 )
                . "</span>"
                . "</div>"
                . "<div style='padding:5px; font-weight:bold; text-align:center;'>"
                . "Unpaid: $". number_format( $totalUnpaid, 2 )
                . "</div>"
                . "</td>";
    }
?>

</body>