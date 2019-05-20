<head>
<meta name="viewport" content="width=device-width, initial-scale=1">

<?php
    include(__DIR__ . "/../appendix.php" );
    
    $url = ADMIN_LINK;
    include( HEADER_PATH );
    
    echo "<span class='admin_box'>";
        // ------------------------------------
        // USER TABLE
        // ------------------------------------
        
        openTable("Full-Timers");
        
        $rowClass = "odd";
        $startCoops = false;
        $startInactives = false;
        
        $results = $db->query('SELECT u.SodaBalance, u.SnackBalance, u.Credits, u.UserID, u.UserName, u.AnonName, u.SlackID, u.FirstName, u.LastName, u.PhoneNumber, u.DateCreated, u.InActive, u.IsCoop FROM User u ORDER BY u.Inactive asc, u.IsCoop, lower(u.FirstName) ASC');
        while ($row = $results->fetchArray()) {
            $isCoop = $row['IsCoop'] == 1;
            $isInactive = $row['Inactive'] == 1;
            
            if( $isInactive ) {
                $rowClass = "discontinued_row";
            }
            
            if( $isCoop && !$startCoops ) {
                echo "</table>";
                echo "</div>";
                echo "</div>";
                openTable("Co-ops");
                $startCoops = true;
            } else if( $isInactive && !$startInactives ) {
                echo "</table>";
                echo "</div>";
                echo "</div>";
                openTable("Inactives");
                $startInactives = true;
            }
            
            echo "<tr class='$rowClass'>";
            $fullName = $row['FirstName'] . " " . $row['LastName'];

            $phoneNumber = $row['PhoneNumber'];
            
            if( strlen( $phoneNumber ) == 10 ) {
                $phoneNumber = "(" . substr( $phoneNumber, 0, 3 ) . ") " . substr( $phoneNumber, 3, 3 ) . "-" . substr( $phoneNumber, 6, 4 ); 
            }
            

            $date_object = DateTime::createFromFormat('Y-m-d H:i:s', $row['DateCreated']);


            $sodaBalance = floatval( $row['SodaBalance'] );
            $snackBalance = floatval( $row['SnackBalance'] );
            $totalBalance = $sodaBalance + $snackBalance;
            $totalCredits = $row['Credits'];

            $creditColor = "";

            if( $totalCredits > 0 ) {
                $creditColor = "style='background-color:#fdff7a;'";
            }



            $purchaseHistoryURL = "<a href='" . PURCHASE_HISTORY_LINK . "?name=" . $fullName . "&userid=" . $row['UserID'] . "'>" .  getPriceDisplayWithDollars($totalBalance ) . "</a>";

            $balanceColor = "";

            if( $totalBalance > 0 ) {
                $balanceColor = "background-color:#fdff7a;";
            }

            echo "<td><a href='" . ADMIN_LINK . "?Proxy_x25=" . $row['UserName'] . "'>[Proxy]</a></td>";
            echo "<td>" . $fullName . "</td>";
            echo "<td class='hidden_mobile_column'>" . $row['UserName'] . "</td>";
            echo "<td class='hidden_mobile_column'>" . $row['AnonName'] . "</td>";
            echo "<td class='hidden_mobile_column'>" . $row['SlackID'] . "</td>";
            echo "<td class='hidden_mobile_column'>" . $phoneNumber . "</td>";
            echo "<td class='hidden_mobile_column'>" . $date_object->format('m/d/Y  [h:i:s A]') . "</td>";
            echo "<td $creditColor>" .  getPriceDisplayWithDollars( $totalCredits ) . "</td>";
            echo "<td style='padding:5px; $balanceColor border:1px #000 solid;'>" . $purchaseHistoryURL . "</td>";
            echo "</tr>";
            
            if( $rowClass == "odd" ) { $rowClass = "even"; } else { $rowClass = "odd"; }
        }
        
        echo "</table>";
        echo "</div>";
        echo "</div>";
        echo "</span>";
    echo "</span>";
    
    function openTable( $tableLabel ) {
        echo "<div class='rounded_header'><span id='$tableLabel' class='title'>$tableLabel</span></div>";
        
        echo "<div class='center_piece'>";
        echo "<div class='rounded_table'>";
        echo "<table>";
        echo "<thead><tr class='table_header'>";
        echo "<th class='admin_header_column'>&nbsp;</th>";
        echo "<th class='admin_header_column'>Name</th>";
        echo "<th class='admin_header_column hidden_mobile_column'>User Name</th>";
        echo "<th class='admin_header_column hidden_mobile_column'>Anon Name</th>";
        echo "<th class='admin_header_column hidden_mobile_column'>Slack ID</th>";
        echo "<th class='admin_header_column hidden_mobile_column'>Phone Number</th>";
        echo "<th class='admin_header_column hidden_mobile_column'>Date Created</th>";
        echo "<th class='admin_header_column'>Credits</th>";
        echo "<th class='admin_header_column'>Balance</th>";

        echo "</tr></thead>";
    }
?>

</body>