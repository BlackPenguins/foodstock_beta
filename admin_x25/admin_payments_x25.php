<meta name="viewport" content="width=device-width, initial-scale=1">

<?php
    include(__DIR__ . "/../appendix.php" );
    
    $url = ADMIN_PAYMENTS_LINK;
    include( HEADER_PATH );
    include PURCHASE_MONTH_OBJ;
    include USER_PAYMENT_PROFILE;
    echo "<script src='" . SETUP_MODALS_LINK . "'></script>";
?>

<script type="text/javascript">
    function notifyUsersOfPayments() {
        $isAlert = confirm('Are you sure that you want to notify all users about their balances?');
        
        if ( $isAlert ) {
            alert("Notified all users.");

            $.post("<?php echo AJAX_LINK; ?>", { 
                type:'NotifyAllUsersOfPayment',
            },function(data) {
                // Do nothing right now
            });
        }
    }

    function notifySingleUserOfPayments( userID ) {
        $isAlert = confirm('Are you sure that you want to notify this user about their balances?');

        if ( $isAlert ) {
            alert("Notified user.");

            $.post("<?php echo AJAX_LINK; ?>", {
                type:'NotifySingleUserOfPayment',
                userID, userID
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
        echo "<div class='page_header'><span class='title'>User Payments</span></div>";
        echo "<table style='font-size:12px; border-collapse:collapse; width:100%; margin-left: 10px;'>";
        echo "<thead><tr class='table_header'>";
        echo "<th style='padding:5px; border:1px #000 solid;' align='left'>Name <button onClick='notifyUsersOfPayments()'>Notify All</button></th>";
        
        $monthsMaxAgo = 60;
        
        for( $monthsAgo = 0; $monthsAgo <= $monthsMaxAgo; $monthsAgo++ ) {
            
            $month = date('m', UserPaymentProfile::getDateOfPreviousMonth( $monthsAgo ) );
            $year = date('Y', UserPaymentProfile::getDateOfPreviousMonth( $monthsAgo ) );
            
            $monthDate = new DateTime();
            $monthDate->setDate($year, $month, 1 );
            
            $displayMonth = $monthDate->format( 'M Y' );
            
            echo "<th style='padding:5px; font-size:1.4em; border:1px #000 solid;' valign='middle' align='left'>";
            echo "<div style='font-size: 0.9em; text-align:center;'>$displayMonth</div>";
            echo "<div style='text-align:center;'></div>";
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
            
            echo "<td style='padding:5px; border:1px #000 solid;'>$fullName<br>" .
                "<div style='text-align:center;'><button onClick='notifySingleUserOfPayments( $userID )'>Remind</button></div>" .
                "</td>";


            $userProfile = new UserPaymentProfile();
            $userProfile->storePastYearPayments( $db, $userID, $monthsMaxAgo );

            foreach( $userProfile->getMonths() as $purchaseMonth ) {
                echo displayMonthForUser($db, $fullName, $userID, $purchaseMonth );
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
    
    /**
     * @param $db
     * @param $userFullName
     * @param $userID
     * @param $purchaseMonth PurchaseMonthObj
     * @return string
     */
    function displayMonthForUser( $db, $userFullName, $userID, $purchaseMonth ) {

        $currentMonthTotal = $purchaseMonth->getTotal();
        $currentMonthCreditTotal = $purchaseMonth->getTotalCredit();

        $sodaTotalUnpaid = $purchaseMonth->getSodaUnpaid();
        $snackTotalUnpaid = $purchaseMonth->getSnackUnpaid();
        $totalUnpaid = $purchaseMonth->getTotalUnpaid();

        $monthLabel = $purchaseMonth->monthLabel;
        
        $totalBalanceColor = "";
        $monthHadBalance = "";
        $creditAmountDisplay = "";

        if( $totalUnpaid != 0.0 ) {
            $totalBalanceColor = "background-color:#fdff7a;";
        } else if( $currentMonthTotal > 0 ) {
            $monthHadBalance = "color: #edff58";
        }

        if( $currentMonthCreditTotal > 0 ) {
            $creditsColor = "#ffffff";
            if( $totalUnpaid != 0.0 ) {
                $creditsColor = "#000000";
            }
            $creditAmountDisplay = "<div style='padding:5px; font-size: 0.9em; text-align: center; color: $creditsColor'>"
                . "Total Credits:<br>". getPriceDisplayWithDollars( $currentMonthCreditTotal )
                . "</div>";
        }

        $onclick = "openPaymentModal(\"$userFullName\", \"$userID\", \"$monthLabel\", \"None\", " . getPriceDisplayWithDecimals( $sodaTotalUnpaid ) . ", " . getPriceDisplayWithDecimals( $snackTotalUnpaid ) . ", 0, 0);";

        return "<td style='padding:5px; $totalBalanceColor border:1px #000 solid; cursor:pointer;'>"
                . "<div onclick='$onclick' style='width:100%'>"
                . "<div style='padding:5px; border: 1px dashed #000; font-size: 1.1em; margin: 0px 20px; font-weight: bold; text-align: center;'>"
                . "Owed: ". getPriceDisplayWithDollars( $totalUnpaid )
                . "</div>"

                . "<div style='padding:5px; margin-top: 30px; font-size: 0.9em; text-align: center; $monthHadBalance'>"
                . "Total Amount:<br>". getPriceDisplayWithDollars( $currentMonthTotal )
                . "</div>" . $creditAmountDisplay
                . "</td>";
    }
?>

</body>