<?php 
    echo "<div style='color:#FFF; background-color:#46465f;  border-bottom: 3px solid #000; width:100%;  padding: 5px 0px;'>";
    echo "<div id='container_top' style='margin:10px;'>";

    if( $isMobile ) {
        echo "<span style='float:right; padding:0px 5px; display:inline-block'>";
        DisplayLoggedInUser($isLoggedIn, $isLoggedInAdmin, $loginPassword, $url);
        echo "</span>";
        echo "<div style='clear: both;'></div>";
    } else {
        echo "<a style='text-decoration:none;' href='sodastock.php'><span class='nav_buttons nav_buttons_soda'>Soda Home</span></a>";
        echo "<a style='text-decoration:none;' href='snackstock.php'><span class='nav_buttons nav_buttons_snack'>Snack Home</span></a>";
        
        if(!$isLoggedIn) {
            $results = $db->query("SELECT Count(*) as Active, Sum(SnackSavings) as TotalSnackSavings, Sum(SodaSavings) as TotalSodaSavings FROM User WHERE SnackBalance != 0.0 OR SodaBalance != 0");
            $row = $results->fetchArray();
            $totalActiveUsers = $row['Active'];
            $totalSnackSavings = $row['TotalSnackSavings'];
            $totalSodaSavings = $row['TotalSodaSavings'];
            $totalSavings = $totalSnackSavings + $totalSodaSavings;
            
            $results = $db->query("SELECT Count(*) as Total FROM Purchase_History");
            $row = $results->fetchArray();
            $totalPurchases = $row['Total'];
            
            echo "<span style='padding:5px;'><a class='register' href='register.php'>Register for a Discount!<a/> (We have $totalActiveUsers active users with a total of $" . number_format($totalSavings, 2) . " in savings and " . $totalPurchases . " total purchases)</span>";
        } else {
            
            echo "<a style='text-decoration:none;' href='requests.php'><span class='nav_buttons nav_buttons_requests'>Requests</span></a>";
            echo "<a style='text-decoration:none;' href='stats.php'><span class='nav_buttons nav_buttons_stats'>Stats</span></a>";
            
            if( $isLoggedInAdmin ) {
                echo "<a style='text-decoration:none;' href='admin_x25.php'><span class='nav_buttons nav_buttons_admin'>Administration</span></a>";
            }
            echo "<span style='margin-left:25px;'>";
            echo "<a href='purchase_history.php?type=Soda'><span class='nav_buttons nav_buttons_soda'>Soda Balance: $" .  number_format($_SESSION['SodaBalance'], 2) . "</span></a>";
            echo "&nbsp;";
            echo "<a href='purchase_history.php?type=Snack'><span class='nav_buttons nav_buttons_snack'>Snack Balance: $" .  number_format($_SESSION['SnackBalance'], 2) . "</span></a>";
            echo "&nbsp;";
            echo "<a href='billing.php'><span class='nav_buttons nav_buttons_billing'>Billing</span></a>";
            echo "</span>";
        }
        
        echo "<span style='float:right;'>";
        DisplayLoggedInUser($isLoggedIn, $isLoggedInAdmin, $loginPassword, $url);
        echo "</span>";
    }

    echo "</div>";
    echo "</div>";

    function DisplayLoggedInUser($isLoggedIn, $isLoggedInAdmin, $loginPassword, $url) {
        if($isLoggedIn)
        {    
            echo "Logged in: <b><font color ='#FFFF00'>[" . $_SESSION['UserName'] . "]" . ( $isLoggedInAdmin ? " - Administrator" : "" ) . "</font></b>";
            echo "<span title='" . $_SESSION['SlackID'] . "' style='padding:0px 10px 0px 10px;'>";
            echo "<b><a style='color:white;' href='logout.php'>[Logout]</a></b>";
            echo "</span>";
            echo "</span>";
        }
        else
        {
            if( $loginPassword != false ) {
                echo "<span style='float:right; margin:0px 5px;'>";
                echo "<span>";
                echo "<b><font color ='#FF0000'>Incorrect Password: [".$loginPassword."]</font></b>";
                echo "</span>";
                echo "</span>";
            
            }
                
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
