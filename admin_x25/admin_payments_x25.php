<head>
<meta name="viewport" content="width=device-width, initial-scale=1">

<?php
    include(__DIR__ . "/../appendix.php" );
    
    $url = ADMIN_PAYMENTS_LINK;
    include( HEADER_PATH );
?>

<script type="text/javascript">
    function openPaymentModal( user, month, method, sodaAmount, snackAmount ) {
        $('#payment').dialog('open');
        $('#UserDropdown').val(user);
        $('#MonthDropdown').val(month);
        $('#SodaAmount').val(sodaAmount);
        $('#SnackAmount').val(snackAmount);
    }

    function notifyUsersOfPayments( month, year, displayMonth ) {
        $isAlert = confirm('Are you sure that you want to notify all users about their balances?');
        
        if ( $isAlert ) {
            alert("Notified all users.");

            $.post("<?php echo AJAX_LINK; ?>", { 
                type:'NotifyUserOfPayment',
                month:month,
                year:year,
                displayMonth:displayMonth,
            },function(data) {
                // Do nothing right now
            });
        }
    }

    function cancelPayment(paymentID, name, month) {
        $isAlert = confirm('Are you sure that you want cancel payment for ' + name + ' - ' + month + '?');
        
        if ( $isAlert ) {
            alert("Payment cancelled.");

            $.post("<?php echo AJAX_LINK; ?>", { 
                type:'CancelPayment',
                PaymentID:paymentID,
            },function(data) {
                // Do nothing right now
            });
        }
    }
    
</script>

<?php 
    echo "<span style='width:86%; display:inline-block; border-left: 3px #000 solid;'>";
        // ------------------------------------
        // USER TABLE
        // ------------------------------------
        echo "<span class='soda_popout' style='display:inline-block; width:100%; margin-left: 10px; padding:5px;'><span style='font-size:26px;'>User Payment Histories</span> <span style='font-size:0.8em;'></span></span>";
        echo "<span id='users'>";
        echo "<table style='font-size:12; border-collapse:collapse; width:100%; margin-left: 10px;'>";
        echo "<thead><tr class='table_header'>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Name</th>";
        
        $monthsMaxAgo = 5;
        
        for( $monthsAgo = 0; $monthsAgo <= $monthsMaxAgo; $monthsAgo++ ) {
            
            $month = date('m', getDateOfPreviousMonth( $monthsAgo ) );
            $year = date('Y', getDateOfPreviousMonth( $monthsAgo ) );
            
            $monthDate = new DateTime();
            $monthDate->setDate($year, $month, 1 );
            
            $displayMonth = $monthDate->format( 'F Y' );
            
            echo "<th style='padding:5px; font-size:1.4em; border:1px #000 solid;' valign='middle' align='left'>";
            echo $displayMonth; 
            echo "<span style='float:right;'><button onClick='notifyUsersOfPayments(\"$month\", \"$year\", \"$displayMonth\")'>Notify All</button></span>";
            echo "</th>";
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
                $month = date('m', getDateOfPreviousMonth( $monthsAgo ) );
                $year = date('Y', getDateOfPreviousMonth( $monthsAgo ) );
                
                $monthDate = new DateTime();
                $monthDate->setDate($year, $month, 1 );
                
                $monthLabel = $monthDate->format( 'F Y' );
                
                echo displayMonthForUser( $db, $userID, $month, $year, $monthLabel );
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
    
    function getDateOfPreviousMonth( $xMonthsAgo ) {
        $today = date('F Y');
        return strtotime( '-' . $xMonthsAgo . ' months', strtotime( $today ) );
    }
    
    function displayMonthForUser( $db, $userID, $monthNumber, $year, $monthLabel ) {
        $totalArray = getTotalsForUser( $db, $userID, $monthNumber, $year, $monthLabel );
        
        $currentMonthSodaTotal = $totalArray['SodaTotal'];
        $currentMonthSnackTotal = $totalArray['SnackTotal'];
        $sodaTotalPaid = $totalArray['SodaPaid'];
        $snackTotalPaid = $totalArray['SnackPaid'];
        
        $sodaTotalUnpaid = round( $currentMonthSodaTotal - $sodaTotalPaid, 2);
        $snackTotalUnpaid = round( $currentMonthSnackTotal - $snackTotalPaid, 2);
        
        $totalPurchases = $currentMonthSodaTotal + $currentMonthSnackTotal;
        $totalBalanceColor = "";
        
        if( $sodaTotalUnpaid != 0.0 || $snackTotalUnpaid != 0.0 ) {
            $totalBalanceColor = "background-color:#fdff7a;";
        }
        
        
        $sodaAmount = number_format( $currentMonthSodaTotal, 2 );
        $snackAmount = number_format( $currentMonthSnackTotal, 2 );
        
        $onclick = "openPaymentModal(\"$userID\", \"$monthLabel\", \"None\", \"$sodaTotalUnpaid\", \"$snackTotalUnpaid\");";
        
        return "<td style='padding:5px; $totalBalanceColor border:1px #000 solid; cursor:pointer;'>"
                . "<table onclick='$onclick' style='width:100%'>"
                    
                . "<tr>"
                . "<td style='text-align:center; padding-bottom:15px; padding-top:10px;' colspan='3'>"
                . "<span style='padding:5px; border: 1px dashed #000;'>"
                . "Total: $". number_format( $totalPurchases, 2 )
                . "</span>"
                . "</td>"
                . "</tr>"
                
                . "<tr>"
                . "<td>"
                . "</td>"
                . "<td>Soda</td>"
                . "<td>Snack</td>"
                . "</tr>"
                    
                . "<tr>"
                . "<td>Total</td>"
                . "<td>$" . $sodaAmount . "</td>"
                . "<td>$" . $snackAmount ."</td>"
                . "</tr>"
                        
                . "<tr style='font-weight:bold; '>"
                . "<td style='border-top: dashed 1px #000; padding-top:10px;'>Unpaid</td>"
                . "<td style='border-top: dashed 1px #000; padding-top:10px;'> $". number_format( $sodaTotalUnpaid, 2 ) . "</td>"
                . "<td style='border-top: dashed 1px #000; padding-top:10px;'> $". number_format( $snackTotalUnpaid, 2 ) . "</td>"
                . "</tr>"
        
                . "</table>"
                . "</td>";
    }
?>

</body>