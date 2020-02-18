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
        
        $statement = $db->prepare("SELECT u.SodaBalance, u.SnackBalance, u.Credits, u.UserID, u.UserName, u.AnonName, u.SlackID, u.FirstName, u.LastName, u.PhoneNumber, u.DateCreated, u.InActive, u.IsCoop, u.IsVendor " .
          "FROM User u " .
          "ORDER BY u.Inactive asc, u.IsCoop, lower(u.FirstName) ASC");
        $results = $statement->execute();

        while ($row = $results->fetchArray()) {
            $isCoop = $row['IsCoop'] == 1;
            $isVendor = $row['IsVendor'] == 1;
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
            $vendorHistoryURL = "NO";

            if( $isVendor ) {
                $vendorHistoryURL = "<a href='" . VENDOR_LINK . "?name=" . $fullName . "&userid=" . $row['UserID'] . "'>YES</a>";
            }

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
            echo "<td class='hidden_mobile_column'>" .  $vendorHistoryURL . "</td>";
            echo "<td class='hidden_mobile_column'>" . $date_object->format('m/d/Y  [h:i:s A]') . "</td>";
            echo "<td $creditColor>" .  getPriceDisplayWithDollars( $totalCredits ) . "</td>";
            echo "<td style='padding:5px; $balanceColor border:1px #000 solid;'>" . $purchaseHistoryURL . "</td>";
            echo "</tr>";
            
            if( $rowClass == "odd" ) { $rowClass = "even"; } else { $rowClass = "odd"; }
        }
        
        echo "</table>";
        echo "</div>";
        echo "</div>";

        echo "<div class='session_container'>";
        echo "<div class='rounded_header'><span id='Sessions' class='title'>SESSIONS</span></div>";

        $sessionLocation = session_save_path();

        if( $sessionLocation == "" ) {
            echo "Could not locate session information. Checking here instead: [" . sys_get_temp_dir() . "]<br><br>";
            $sessionLocation = sys_get_temp_dir();
        }

        $sessionNames = scandir($sessionLocation);

        foreach ($sessionNames as $sessionName) {

            $filepath = $sessionLocation . "/"  . $sessionName;

            if ( strpos($sessionName, "sess_") === 0 ) { //This skips temp files that aren't sessions
//                $sessionName = str_replace("sess_", "", $sessionName);

                echo "<form id='kill_session_form' enctype='multipart/form-data' action='" . HANDLE_FORMS_LINK . "' method='POST'>";
                echo "<input type='hidden' name='SessionID' value='$sessionName'/>";
                echo "<input type='hidden' name='KillSession' value='KillSession'/>";
                echo "<input type='hidden' name='redirectURL' value='" . ADMIN_LINK . "'/>";
                echo "<input style='padding:10px;' type='submit' name='Kill_Session' value='Kill Session'/>";
                echo "</form>";

                echo "<b>Session [$sessionName]</b><br>";
                $fh = fopen($filepath, 'r') or die("File does not exist or you lack permission to open it");
                $line = fgets($fh);
                $pieces = explode( ";", $line );

                foreach( $pieces as $piece ) {
                    if( $piece == "" ) {
                        continue;
                    }

                    $innerPieces = explode( ":", $piece );
                    $keyAndType = $innerPieces[0];
                    $keyAndTypePieces = explode( "|", $keyAndType );
                    $key = $keyAndTypePieces[0];
                    $type = $keyAndTypePieces[1];

                    if( count( $innerPieces ) == 3 ){
                         $typeLength = $innerPieces[1];
                         $value = $innerPieces[2];
                    } else {
                         $value = $innerPieces[1];
                    }

                    $colorUsername = $key == "UserName" ? " style='color:#fdff7a' " : "";
                    echo "<div $colorUsername>";
                    echo "<b>$key</b>: ";

                    if( $type == "b" ) {
                        if( $value == 1 ) {
                            echo "YES";
                        } else {
                            echo "NO";
                        }
                    } else {
                        echo $value;
                    }
                    echo "</div>";
                }
//                echo $line;
                fclose($fh);
                echo "<hr>";
            }
        }
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
        echo "<th class='admin_header_column hidden_mobile_column'>Vendor</th>";
        echo "<th class='admin_header_column hidden_mobile_column'>Date Created</th>";
        echo "<th class='admin_header_column'>Credits</th>";
        echo "<th class='admin_header_column'>Balance</th>";

        echo "</tr></thead>";
    }
?>

</body>