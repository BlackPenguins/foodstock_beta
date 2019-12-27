<meta name="viewport" content="width=device-width, initial-scale=1">

<?php
    include(__DIR__ . "/../appendix.php" );
    
    $url = ADMIN_PAYMENTS_LINK;
    include( HEADER_PATH );
?>

<script type="text/javascript">
    function openPaymentModal( user, userID, month, method, sodaAmount, snackAmount ) {
        $('#payment').dialog('open');
        $('#UserIDLabel').html(user);
        $('#UserID').val(userID);
        $('#Month').val(month);
        $('#MonthLabel').html(month);
        $('#SodaAmount').val(sodaAmount);
        $('#SnackAmount').val(snackAmount);
        $('#SodaUnpaid').val(sodaAmount);
        $('#SnackUnpaid').val(snackAmount);

        $totalAmount = ( sodaAmount + snackAmount ).toFixed( 2 );
        console.log("So [" + sodaAmount + "] Sn [" + snackAmount + "] Tot [" + $totalAmount + "]" );
        $('#TotalAmount').val($totalAmount);
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
    echo "<span class='admin_box'>";

    echo "<span class='hidden_mobile_section' id='users'>";
        echo "<div class='rounded_header'><span class='title'>User Payments</span></div>";
        echo "<table style='font-size:12px; border-collapse:collapse; width:100%; margin-left: 10px;'>";
        echo "<thead><tr class='table_header'>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Name</th>";
        
        $monthsMaxAgo = 12;
        
        for( $monthsAgo = 0; $monthsAgo <= $monthsMaxAgo; $monthsAgo++ ) {
            
            $month = date('m', getDateOfPreviousMonth( $monthsAgo ) );
            $year = date('Y', getDateOfPreviousMonth( $monthsAgo ) );
            
            $monthDate = new DateTime();
            $monthDate->setDate($year, $month, 1 );
            
            $displayMonth = $monthDate->format( 'M Y' );
            
            echo "<th style='padding:5px; font-size:1.4em; border:1px #000 solid;' valign='middle' align='left'>";
            echo "<div style='font-size: 0.9em; text-align:center;'>$displayMonth</div>";
            echo "<div style='text-align:center;'><button onClick='notifyUsersOfPayments(\"$month\", \"$year\", \"$displayMonth\")'>Notify All</button></div>";
            echo "</th>";
        }
        echo "</tr></thead>";
        
        $rowClass = "odd";
        
        $statement = $db->prepare("SELECT u.UserID, u.UserName, u.SlackID, u.FirstName, u.LastName, u.PhoneNumber, u.SodaBalance, u.SnackBalance, u.DateCreated, u.InActive " .
          "FROM User u " .
          "ORDER BY u.Inactive asc, lower(u.FirstName) ASC");
        $results = $statement->execute();

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
                
                echo displayMonthForUser( $db, $fullName, $userID, $month, $year, $monthLabel );
            }
            
            echo "</tr>";
            if( $rowClass == "odd" ) { $rowClass = "even"; } else { $rowClass = "odd"; }
        }
        
            echo "</table>";
        echo "</span>";
        
        // ------------------------------------
        // PAYMENT TABLE
        // ------------------------------------
        echo "<div class='center_piece'>";
        echo "<div class='rounded_table_no_border'>";
        echo "<table>";
        echo "<thead><tr class='table_header'>";
        echo "<th class='admin_header_column hidden_mobile_column' align='left'>&nbsp;</th>";
        echo "<th class='admin_header_column' align='left'>User Name</th>";
        echo "<th class='admin_header_column' align='left'>Payment Month</th>";
        echo "<th class='admin_header_column' align='left'>Amount</th>";
        echo "<th class='admin_header_column hidden_mobile_column' align='left'>Method</th>";
        echo "<th class='admin_header_column' align='left'>Type</th>";
        echo "<th class='admin_header_column hidden_mobile_column' align='left'>Date</th>";
        echo "<th class='admin_header_column hidden_mobile_column' align='left'>Note</th>";
        echo "</tr></thead>";
        
        $rowClass = "odd";
        $previousDate = "";
        
        $statement = $db->prepare("SELECT p.PaymentID, u.FirstName, u.LastName, p.Cancelled, p.Method, p.Amount, p.Date, p.Note, p.ItemType, p.MonthForPayment " .
            "FROM Payments p " .
            "LEFT JOIN User u on p.UserID = u.UserID " .
            "WHERE p.Date >= date('now','-12 months') " .
            "ORDER BY p.Date DESC" );
        $results = $statement->execute();

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

            echo "<td class='button_cell hidden_mobile_column'>";
            if( $cancelled !=  1 ) {
                echo "<div onclick='cancelPayment($paymentID, \"$name\", \"$paymentMonth\");' class='nav_buttons nav_buttons_snack'>Cancel Payment</div>";
            } else {
                echo "<div style='font-weight:bold; text-align:center;'>Cancelled</div>";
            }
            echo "</td>";
            
            echo "<td>" . $name . "</td>";
            echo "<td>" . $paymentMonth . "</td>";
            echo "<td>" . getPriceDisplayWithDollars( $amount ) . "</td>";
            echo "<td class='hidden_mobile_column'>" . $method . "</td>";
            echo "<td>" . $itemType . "</td>";
            echo "<td class='hidden_mobile_column'>".$date_object->format('m/d/Y  [h:i:s A]')."</td>";
            echo "<td class='hidden_mobile_column'>" . $note . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        echo "</div>";
        echo "</div>";
    echo "</span>";
    
    function getDateOfPreviousMonth( $xMonthsAgo ) {
        $today = date('F Y');
        return strtotime( '-' . $xMonthsAgo . ' months', strtotime( $today ) );
    }
    
    function displayMonthForUser( $db, $userFullName, $userID, $monthNumber, $year, $monthLabel ) {
        $totalArray = getTotalsForUser( $db, $userID, $monthNumber, $year, $monthLabel );
        
        $currentMonthSodaTotal = $totalArray['SodaTotal'];
        $currentMonthSnackTotal = $totalArray['SnackTotal'];
        $currentMonthTotal = $currentMonthSodaTotal + $currentMonthSnackTotal;

        $sodaTotalPaid = $totalArray['SodaPaid'];
        $snackTotalPaid = $totalArray['SnackPaid'];

        $sodaTotalUnpaid = $currentMonthSodaTotal - $sodaTotalPaid;
        $snackTotalUnpaid = $currentMonthSnackTotal - $snackTotalPaid;
        $totalUnpaid = $sodaTotalUnpaid + $snackTotalUnpaid;
        
        $totalBalanceColor = "";
        
        if( $totalUnpaid != 0.0 ) {
            $totalBalanceColor = "background-color:#fdff7a;";
        }

        $onclick = "openPaymentModal(\"$userFullName\", \"$userID\", \"$monthLabel\", \"None\", " . getPriceDisplayWithDecimals( $sodaTotalUnpaid ) . ", " . getPriceDisplayWithDecimals( $snackTotalUnpaid ) . ");";
        
        return "<td style='padding:5px; $totalBalanceColor border:1px #000 solid; cursor:pointer;'>"
                . "<div onclick='$onclick' style='width:100%'>"
                . "<div style='padding:5px; border: 1px dashed #000; font-size: 1.1em; margin: 0px 20px; font-weight: bold; text-align: center;'>"
                . "Owed: ". getPriceDisplayWithDollars( $totalUnpaid )
                . "</div>"

                . "<div style='padding:5px; margin-top: 30px; font-size: 0.9em; text-align: center;'>"
                . "Total Amount: ". getPriceDisplayWithDollars( $currentMonthTotal )
                . "</div>"
                . "</td>";
    }
?>

</body>