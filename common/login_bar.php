<?php 
    include(__DIR__ . "/../appendix.php" );
    echo "<div style='color:#FFF; background-color:#46465f;  border-bottom: 3px solid #000; width:100%;  padding: 5px 0px;'>";
    echo "<div id='container_top' style='margin:10px;'>";

    if( $isMobile ) {
        echo "<span style='float:right; padding:0px 5px; display:inline-block'>";
        DisplayLoggedInUser($isLoggedIn, $isLoggedInAdmin, $url);
        echo "</span>";
        echo "<div style='clear: both;'></div>";
    } else {
        echo "<a style='text-decoration:none;' href='" . SODASTOCK_LINK . "'><span class='nav_buttons nav_buttons_soda'>Soda Home</span></a>";
        echo "<a style='text-decoration:none;' href='" . SNACKSTOCK_LINK . "'><span class='nav_buttons nav_buttons_snack'>Snack Home</span></a>";
        
        if(!$isLoggedIn) {
            $results = $db->query("SELECT Count(*) as Active, Sum(SnackSavings) as TotalSnackSavings, Sum(SodaSavings) as TotalSodaSavings FROM User WHERE SnackBalance != 0.0 OR SodaBalance != 0");
            $row = $results->fetchArray();
            $totalActiveUsers = $row['Active'];
            $totalSnackSavings = $row['TotalSnackSavings'];
            $totalSodaSavings = $row['TotalSodaSavings'];
            $totalSavings = $totalSnackSavings + $totalSodaSavings;
            
            $results = $db->query("SELECT Count(*) as Total FROM Purchase_History WHERE Cancelled IS NULL");
            $row = $results->fetchArray();
            $totalPurchases = $row['Total'];
            
            echo "<span style='padding:5px;'><a class='register' href='" . REGISTER_LINK . "'>Register for a Discount!<a/> (We have $totalActiveUsers active users with a total of $" . number_format($totalSavings, 2) . " in savings and " . $totalPurchases . " total purchases)</span>";
        } else {
            echo "<a style='text-decoration:none;' href='" . REQUESTS_LINK . "'><span class='nav_buttons nav_buttons_requests'>Requests</span></a>";
            echo "<a style='text-decoration:none;' href='" . STATS_LINK . "'><span class='nav_buttons nav_buttons_stats'>Graphs</span></a>";
            
            if( $isLoggedInAdmin ) {
                echo "<a style='text-decoration:none;' href='" . ADMIN_LINK . "'><span class='nav_buttons nav_buttons_admin'>Administration</span></a>";
            }
            echo "<span style='margin-left:25px;'>";
            echo "<a style='text-decoration:none;' href='" . PURCHASE_HISTORY_LINK . "?type=Soda'><span class='nav_buttons nav_buttons_soda'>Soda Balance: $" .  number_format($_SESSION['SodaBalance'], 2) . "</span></a>";
            echo "&nbsp;";
            echo "<a style='text-decoration:none;' href='" . PURCHASE_HISTORY_LINK . "?type=Snack'><span class='nav_buttons nav_buttons_snack'>Snack Balance: $" .  number_format($_SESSION['SnackBalance'], 2) . "</span></a>";
            echo "&nbsp;";
            echo "<a style='text-decoration:none;' href='" . BILLING_LINK . "'><span class='nav_buttons nav_buttons_billing'>Billing</span></a>";
            echo "</span>";
        }
        
        echo "<span style='float:right;'>";
        DisplayLoggedInUser($isLoggedIn, $isLoggedInAdmin, $url);
        echo "</span>";
    }

    echo "</div>";
    echo "</div>";

    function DisplayLoggedInUser($isLoggedIn, $isLoggedInAdmin, $url) {
        if($isLoggedIn)
        {    
            echo "Logged in: <b><font color ='#FFFF00'>[" . $_SESSION['UserName'] . "]" . ( $isLoggedInAdmin ? " - Administrator" : "" ) . "</font></b>";
            echo "<span title='" . $_SESSION['SlackID'] . "' style='padding:0px 10px 0px 10px;'>";
            echo "<b><a style='color:white;' href='" . LOGOUT_LINK . "'>[Logout]</a></b>";
            echo "</span>";
            echo "</span>";
        }
        else
        {  
            //PHP_SELF = this current php page name (take you back to the current page)
            echo "<form style='margin:0px 5px; padding:0px;' action='$url' method='post' accept-charset='UTF-8'>"; 
            echo "Username: ";
            echo "<input type='text' name='login_username' id='username' size='15' maxlength='40' />";
            echo "&nbsp;&nbsp;&nbsp;Password: ";
            echo "<input type='password' name='login_password' id='password' size='15' maxlength='40' />";
            echo "<input style='margin: 0px 10px;' type='submit' id='submit2' name='Submit' value='Login' />";
            echo "</label>";
            echo "</form>";
        }
    }
?>
